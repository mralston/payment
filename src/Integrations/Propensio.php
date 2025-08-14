<?php

namespace Mralston\Payment\Integrations;

use App\Address;
use App\FinanceApplication;
use App\Mail\FinanceApplicationCancelManually;
use App\Mail\FinanceApplicationSatNoteUpload;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Mralston\Payment\Data\PrequalData;
use Mralston\Payment\Data\PrequalPromiseData;
use Mralston\Payment\Events\OffersReceived;
use Mralston\Payment\Interfaces\FinanceGateway;
use Mralston\Payment\Interfaces\PaymentGateway;
use Mralston\Payment\Interfaces\PaymentHelper;
use Mralston\Payment\Interfaces\PrequalifiesCustomer;
use Mralston\Payment\Interfaces\Signable;
use Mralston\Payment\Models\Payment;
use Mralston\Payment\Models\PaymentProvider;
use Mralston\Payment\Models\PaymentSurvey;
use Mralston\Payment\Services\PaymentCalculator;
use Spatie\ArrayToXml\ArrayToXml;

class Propensio implements PaymentGateway, FinanceGateway, PrequalifiesCustomer, Signable
{
    public const UNEXPECTED_ERROR = 0; // Any error that we were not expecting. i.e. an Exception
    public const TARGET_REFERENCE_UNKNOWN = 1; // Target reference not recognised
    public const PRE_BUSINESS_PROCESSING = 2; // Any point within the import processing that is not an error
    public const RECEIVED_INTO_BUSINESS_OK = 3; // Pre processing and import completed successfully
    public const PRE_BUSINESS_PROCESSING_ERROR = 4; // An error occurred whilst pre processing, i.e. an error occurred within the import system
    public const IN_BUSINESS_PROCESSING = 5; // This is at any non error state that is not terminal, within the business process
    public const IN_BUSINESS_PROCESSING_ERROR = 6; // An error occurred at some point during the business processing
    public const IN_BUSINESS_PROCESSING_COMPLETE = 7; // The business process completed, or reached a terminal state that was not an error successfully

    private $addressIncrement;

    private array $okStatuses = [
        self::PRE_BUSINESS_PROCESSING,
        self::RECEIVED_INTO_BUSINESS_OK,
        self::IN_BUSINESS_PROCESSING,
        self::IN_BUSINESS_PROCESSING_COMPLETE,
    ];

    private array $failStatuses = [
        self::UNEXPECTED_ERROR,
        self::TARGET_REFERENCE_UNKNOWN,
        self::PRE_BUSINESS_PROCESSING_ERROR,
        self::IN_BUSINESS_PROCESSING_ERROR
    ];

    /**
     * Endpoints to be used based on environment.
     *
     * @var array|string[]
     */
    private array $endpoints = [
        'local' => 'https://propensiouat.085celestial.co.uk:9445/FunderXMLWebService/FunderXMLWebService.asmx',
        'dev' => 'https://propensiouat.085celestial.co.uk:9445/FunderXMLWebService/FunderXMLWebService.asmx',
        'testing' => 'https://propensiouat.085celestial.co.uk:9445/FunderXMLWebService/FunderXMLWebService.asmx',
        'production' => 'https://propensio.085celestial.co.uk:8445/FunderXMLWebService/FunderXMLWebService.asmx',
    ];

    private $guzzleClient;

    /**
     * API endpoint to send POST requests to.
     *
     * @var string
     */
    private string $endpoint;

    public function __construct(
        private string $ibcRef,
        string $endpoint,
    ) {
        $this->endpoint = $this->endpoints[$endpoint];

        $this->guzzleClient = new Client([
            'base_uri' => $this->endpoint . '/',
            'http_errors' => false,
            'timeout' => 60
        ]);
    }

    /**
     * Checks whether the API is functional.
     *
     * @return bool
     */
    public function healthCheck()
    {
        $response = $this->guzzleClient->request('POST', 'Test', []);

        if ($response->getStatusCode() == 200) {
            $output = $this->xmlToArray($response->getBody()->getContents());
            return ($output[0] == 'Welcome to the Propensio Funder XML Server');
        }

        return false;
    }

    public function apply(FinanceApplication $application)
    {
        // Temporary workaround. Can be removed pretty sharpish
        if (empty($application->reference)) {
            $application->update([
                'reference' => $application->quote_id . '-' . $application->id
            ]);
        }

        $this->addressIncrement = -1;

        $bankAccountPK = $this->getNewRef();
        $incomePK = $this->getNewRef();
        $expenditureMortgagePK = $this->getNewRef();
        $expenditureRentPK = $this->getNewRef();
        $incomeHouseholdPK = $incomeIndividualPK = $this->getNewRef();
        $employerPK = $this->getNewRef();
        $assetPK = $this->getNewRef();

        $agreement = [
            'REF' => $this->getNewRef(),
            'CODE' => $this->getNewRef(),
            'DOCUMENT_TYPE' => 'UNSECURED_LOAN',
        ];

        $quotation = [
            'REF' => $this->getNewRef(),
        ];

        $application->employer_ibc_ref = null;
        if (
            $this->convertEmploymentStatus($application->employment_status) != 'Unemployed' &&
            $application->employment_status != 'retired'
        ) {
            $application->employer_ibc_ref = $this->getNewRef();
        }

        $application->save();

        $expenditure = [];

        if ($application->mortgage_monthly > 0 && $application->rent_monthly > 0) {
            $expenditure[] =  [
                '_attributes' => [
                    'CHECKS_MADE_REF' => $expenditureMortgagePK,
                    'IBC_REF' => $application->ibc_ref,
                    'EXPENDITURE_CHECK_CODE' => 'MORT',
                    'LOAN_AMOUNT' => round($application->mortgage_monthly + $application->rent_monthly, 2)
                ]
            ];
        } elseif ($application->mortgage_monthly > 0) {
            $expenditure[] =  [
                '_attributes' => [
                    'CHECKS_MADE_REF' => $expenditureMortgagePK,
                    'IBC_REF' => $application->ibc_ref,
                    'EXPENDITURE_CHECK_CODE' => 'MORT',
                    'LOAN_AMOUNT' => round($application->mortgage_monthly, 2)
                ]
            ];
        } elseif ($application->rent_monthly > 0) {
            $expenditure[] = [
                '_attributes' => [
                    'CHECKS_MADE_REF' => $expenditureRentPK,
                    'IBC_REF' => $application->ibc_ref,
                    'EXPENDITURE_CHECK_CODE' => 'MORT',
                    'LOAN_AMOUNT' =>  round($application->rent_monthly, 2)
                ]
            ];
        }

        // Prepare employer
        $employer = [
            'ASSOCIATION_ID' => $employerPK,
            'ASSOCIATION_TYPE' => 'EMP',
            'IBC_REF_FROM' => $application->ibc_ref,
            'YEARS_AT' => floor($application->time_with_employer / 12),
            'MONTHS_AT' => 0,
            'DESIGNATION_NAME_AT_BANK' => $application->occupation,
            'ACTIVE_FULLTIME' => $this->convertFulltime($application->employment_status),
            'EMPLOYER_CATEGORY' => 'CUR',
            'GROSS_SALARY' => $application->gross_income_household,
            'JOB_CATEGORY' => $this->convertEmploymentStatus($application->employment_status),
        ];

        // Add employer IBC ref ONLY if it is non-empty
        if (!empty($application->employer_ibc_ref)) {
            $employer['IBC_REF_TO'] = $application->employer_ibc_ref;
        }

        $ibcApplicant = [
            '_attributes' => [
                'IBC_REF' => $application->ibc_ref,
                'IBC_TYPE' => 'I',
                'TELEPHONE_1' => $application->landline ?? 0,
                'MOBILE_PHONE' => $application->mobile ?? 0,
                'E_MAIL' => $application->email_address ?? '',
                'TITLE' => $application->title,
                'SURNAME_REGISTERED_NAME' => $application->last_name,
                'FORENAMES' => implode(" ", array_filter([$application->first_name, $application->middle_name])),
                'SHORT_NAME' => implode(
                    " ",
                    array_filter([
                        $application->title,
                        $application->first_name,
                        $application->middle_name,
                        $application->last_name
                    ])
                ),
                'DOB_REG_DATE' => $application->date_of_birth->toDateTimeLocalString(),
                'SEX' => 'X',
                'MARITAL_STATUS' => $this->convertMaritalStatus($application->marital_status),
                'BANKRUPT_OR_IN_IVA' => empty($application->bankrupt_or_iva) ? 0 : 1
            ],
            'ADDRESS' => $application->addresses
                ->values()
                ->map(function ($address) use ($application) {
                    return $this->convertAddress($address, $application);
                })
                ->toArray(),
            'ASSOCIATION' => [
                [
                    //bank account
                    '_attributes' => [
                        'ASSOCIATION_ID' => $bankAccountPK,
                        'ASSOCIATION_TYPE' => 'BNK',
                        'IBC_REF_FROM' => $application->ibc_ref,
                        'IBC_REF_TO' => str_replace("-", "", $application->bank_account_sort_code),
                        'ACCOUNT_NUMBER_JOB_CODE' => $application->bank_account_number,
                        'ACCOUNT_NAME' => $application->bank_account_holder_name
                    ]
                ],
                [
                    //employer
                    '_attributes' => $employer,
                ]
            ],
            'INCOME' => [
                [
                    '_attributes' => [
                        'CHECKS_MADE_REF' => $incomeHouseholdPK,
                        'IBC_REF' => $application->ibc_ref,
                        'INCOME_CHECK_CODE' => 'WA',
                        'PERIOD1AMOUNT' => $application->net_monthly_income_individual,
                    ],
                ]
            ],
        ];

        // Add expenditure element ONLY if there is any, otherise the API gets upset
        if (count($expenditure) > 0) {
            $ibcApplicant['EXPENDITURE'] = $expenditure;
        }

        $ibcEmployer = [
            '_attributes' => [
                'IBC_REF' => $application->employer_ibc_ref,
                'IBC_TYPE' => 'C',
                'TELEPHONE_1' => $application->employer_telephone ?? 0,
                'SURNAME_REGISTERED_NAME' => $application->employer_name ?? ($application->employment_status == 'self-employed' ? $application->first_name . ' ' . $application->last_name : null),
                'DOB_REG_DATE' => ($application->employer_company_reg_date ?? Carbon::parse('1970-01-01 00:00:00'))->toDateTimeLocalString(),
                'COMPANY_TYPE' => $this->convertCompanyType($application->employer_company_type),
                'SHORT_NAME' => $application->employer_name,
                'BANKRUPT_OR_IN_IVA' => 0,
            ],
            'ADDRESS' => $application->employer_address
                ->values()
                ->map(function ($address) use ($application) {
                    return $this->convertEmployerAddress($address, $application);
                })
                ->toArray()
        ];

        $data = [
            'IBC' => array_merge([$ibcApplicant], (!empty($application->employer_ibc_ref)) ? [$ibcEmployer] : []),
            'AGREEMENT' => [
                '_attributes' => [
                    'AGREEMENT_REF' => $agreement['REF'],
                    'DOCUMENT_TYPE' => $agreement['DOCUMENT_TYPE'],
                    'DEALER_ADVISED' => 0,
                    'AGREEMENT_CODE' => $agreement['CODE'],
                    'SIGNED_ON_CUSTOMER_PREMISES' => 0,
                    'DOC_TYPE_GROUP' => 'PL',
                    'REGULATED' => 1,
                    'INTRODUCTION_METHOD' => 'DEALER',
                    'FINCO_BRANCH' => 'UK',
                    'AGREEMENT_LOAD_METHOD_CODE' => 'INTERFACE',
                    'BOOK' => 'MAIN_BOOK',
                    'AGREEMENT_CATEGORY_CODE' => 'ON_BS',
                    'PRIMARILY_FOR_BUSINESS_PURPOSES' => 0,
                    'INTRODUCED_BY' => 'NOT_DECLARED',
                    'BRANCH_LOCATION' => 'UK',
                    'PAYMENT_METHOD' => 'DD',
                    'REP_ADVISED' => 0,
                    'CV_FLAG' => 0
                ],
                'CONTACT_LINK_AGREEMENT' => [
                    [
                        '_attributes' => [
                            'IBC_REF' => $application->ibc_ref, //our ibc
                            'RELATIONSHIP' => 'MAIN',
                            'AGREEMENT_REF' => $agreement['REF'],
                        ]
                    ],
                    [
                        '_attributes' => [
                            'IBC_REF' => $this->ibcRef, //our ibc
                            'RELATIONSHIP' => 'DEAL',
                            'AGREEMENT_REF' => $agreement['REF'],
                        ]
                    ],
                    [
                        '_attributes' => [
                            'IBC_REF' => $this->ibcRef, //our ibc
                            'RELATIONSHIP' => 'SUP',
                            'AGREEMENT_REF' => $agreement['REF'],
                        ]
                    ],
                    [
                        '_attributes' => [
                            'IBC_REF' => $this->ibcRef, //our ibc
                            'RELATIONSHIP' => 'INTRO',
                            'AGREEMENT_REF' => $agreement['REF'],
                        ]
                    ],
                ]
            ],
            'ASSET' => [
                '_attributes' => [
                    'ASSET_REF' => $assetPK,
                    'AGREEMENT_REF' => $agreement['REF'],
                    'ASSET_TYPE' => 'NO ASSET',
                    'COST' => round($application->loan_amount - $application->deposit, 2), #loan advance
                    'DESCRIPTION' => substr($application->quote->products_description, 0, 100) ?? 'Various products',
                    'VEHICLE_COST' => round($application->quote->gross, 2),
                    'CASH_DEPOSIT' => round($application->deposit, 2),
                ]
            ],
            'QUOTATION' => [
                '_attributes' => [
                    'QUOTATION_REF' => $quotation['REF'],
                    'AGREEMENT_REF' => $agreement['REF'],
                    'CHARGES' => 0,
                    'DEFERMENT_PERIOD' => $application->deferred_period ?? 1,
                    'DOCUMENTATION_FEE' => 0,
                    'DOCUMENT_TYPE' => $agreement['DOCUMENT_TYPE'],
                    'NUMBER_REGULAR_RENTALS' => $application->loan_term,
                    'PAYMENT' => $application->monthly_payment,
                    'PAYMENT_FREQUENCY' => 1, #monthly
                    'RATE' => $application->apr,
                    'APR' => $application->apr,
                    'DEPOSIT' => $application->deposit ?? 0,
                    'TERM' => $application->loan_term,
                    'TOTAL_CASH_PRICE' => round($application->quote->gross, 2),
                    'TOTAL_PAYABLE' => round($application->total_payable, 2),
                    'QUOTATION_TARGET' => 'PAYMENT',
                    'DOC_FEE_COMPONENT' => 'ARRANGEMENT_FEE',
                    'SUBSIDY' => 0,
                    'QUOTATION_CAMPAIGN_REF' => $application->lender_product_code,
                    'AMOUNT_FINANCED' => round($application->quote->gross - $application->deposit, 2),
                ]
            ],
            'IMPORT_CONTROL' => [
                '_attributes' => [
                    'AGREEMENT_REF' => $agreement['REF']
                ],
            ],
        ];

        #Log::channel('finance')->info($data);

        $response = $this->validateAndSend(
            $data,
            '../schemas/Propensio.xsd' /*schema file*/,
            'SubmitXML', /*method*/
            'proposalXML' /*request key*/
        );

        #Log::channel('finance')->info($response);

        if (is_array($response) && isset($response['xml'])) {
            $application->lender_request_data = $response['xml'];
            $application->save();
        }

        // See if we got a response from Propensio
        if (isset($response['response'])) {
            // Yes, check it was okay
            if ($this->isStatusOk($response['response']['STATUS'] ?? null)) {
                // Application submitted successfully
                $application->lender_application_id = $response['response']['RETURN_MESSAGE']['AGREEMENT_CODE'];
                $application->status = 'pending';
                //$application->offer_expiration_date = $json['offerExpirationDate'];
                $application->lender_response_data = json_encode($response['response']['RETURN_MESSAGE']);
                $application->submitted_at = Carbon::now();

                $application->save();
            } elseif (isset($response['response']['STATUS'])) {
                // Known error
                $application->status = 'error';
                $application->lender_response_data = 'Error #' . $response['response']['STATUS'] . ': ' .
                    $response['response']['RETURN_MESSAGE']['MESSAGE_TEXT'];
                $application->save();
            } else {
                // Unknown error (from Propensio)
                $application->status = 'error';
                $application->lender_response_data = json_encode($response);
                $application->save();
            }
        } else {
            // Unexpected error (probably not a Propensio response)
            $application->status = 'error';
            $application->lender_response_data = json_encode($response);
            $application->save();
        }
        return $application;
    }

    public function validateAndSend(
        $data,
        $schemaFile,
        $methodName,
        $formParamsSubmitKey,
        $responseKey = 'RETURN_MESSAGE'
    ) {
        if (!empty($data)) {
            $rootKey = 'CONTRACT';

//            $xml = ArrayToXml::convert(
//                $data,
//                [
//                    'rootElementName' => $rootKey
//                ]
//            );

            $xml = new ArrayToXml($data, $rootKey);
            $xmlOutput = $xml->prettify()->toXml();

            if ($schemaFile) {
                // Enable user error handling
                libxml_use_internal_errors(true);

                $xml = new \DOMDocument();
                $xml->loadXML($xmlOutput);

                try {
                    if (!$xml->schemaValidate($schemaFile)) {
                        Log::channel('finance')->info('failed internal validation');
                        return libxml_get_errors();
                    }
                } catch (\Exception $e) {
                    dd(libxml_get_errors());
                }
            }

            Log::channel('finance')->info('Propensio Request XML [' . $methodName . '] :');
            Log::channel('finance')->info($xmlOutput);
        } else {
            Log::channel('finance')->info('Propensio Request (no XML) [' . $methodName . ']');
        }

        $response = $this->guzzleClient->request(
            'POST',
            $methodName,
            [
                'form_params' => is_array($formParamsSubmitKey) ? $formParamsSubmitKey : [
                    $formParamsSubmitKey => $xmlOutput
                ]
            ]
        );
        $status = $response->getStatusCode();

        if ($status == 200) {
            $responseXml = $response->getBody()->getContents();

            if ($methodName != 'GetDocument') {
                Log::channel('finance')->info('Propensio Response XML (raw) [' . $methodName . '] :');
                Log::channel('finance')->info($responseXml);
            } else {
                Log::channel('finance')->info('Propensio Response to GetDocument received');
            }

            // Propensio's XML response is messed up. They wrap their response XML with a <string> tag
            // and encoding everything inside it (the actual response we're expecting)
            if (preg_match('/<string xmlns="http:\/\/www.oysterbaysystems.com\/FunderXMLWebService\/">(.*)<\/string>/si', $responseXml, $matches)) {
                $responseXml = '<?xml version="1.0" encoding="utf-8"?>' . "\n" . html_entity_decode($matches[1]);

                if ($methodName != 'GetDocument') {
                    Log::channel('finance')->info('Propensio Response XML (fixed) [' . $methodName . '] :');
                    Log::channel('finance')->info($responseXml);
                }
            }

            // Parse XML into array
            $output = $this->xmlToArray($responseXml);

            // Parse RETURN_MESSAGE into an array
            if ($methodName != 'GetDocument') {
                $output['RETURN_MESSAGE'] = $this->parseReturnMessage($output['RETURN_MESSAGE'] ?? '')
                    ->toArray();

//                Log::channel('finance')->info('Parsed return_message:');
//                Log::channel('finance')->info(print_r($output['RETURN_MESSAGE'], true));


                Log::channel('finance')->info('Propensio Response XML (as array) [' . $methodName . '] :');
                Log::channel('finance')->info($output);
            }

            if ($responseKey == 'DOCUMENT') {
                return [ 'response' => base64_decode($output['DOCUMENT_DATA']) ];
            }

            return [ 'response' => $output, 'xml' => $xmlOutput ?? null];
        } else {
            Log::channel('finance')->info('Propensio response (HTTP STATUS=' . $status . ':');
            Log::channel('finance')->info(optional($response->getBody())->getContents() ?? 'EMPTY RESPONSE');
        }

        return false;
    }

    public function xmlToArray(string $xml, bool $toArray = true)
    {
        $obj = simplexml_load_string($xml, null, LIBXML_NOCDATA);
        return json_decode(json_encode($obj), $toArray);
    }

    public function signingMethod(): string
    {
        return 'online_non_interactive';
    }

    public function getSigningUrl(Payment $payment): string
    {
        return false;
    }

    public function pollStatus(Payment $payment): array
    {
        // Some applications don't have a lender ID (it was a bug), so they can't be polled.
        if (empty($payment->provider_foreign_id)) {
            return [
                'status' => $payment->paymentStatus->name,
                'lender_response_data' => null,
                'offer_expiration_date' => null
            ];
        }

        $data = [
            'AGREEMENT' => [
                '_attributes' => [
                    'AGREEMENT_CODE' => $payment->provider_foreign_id
                ]
            ]
        ];

        $response = $this->validateAndSend(
            $data,
            null,
            'RequestStatus', /*method*/
            'statusRequestXML', /*request key*/
        );

        #Log::channel('finance')->info($response);


        if (!is_array($response) || !isset($response['response']['RETURN_MESSAGE']['STATUS_CODE'])) {
            $status = 'error';
        } elseif ($this->isStatusOk($response['response']['STATUS'])) {
            if (!empty($response['response']['RETURN_MESSAGE']['STATUS_CODE'])) {
                $status = $this->convertStatusCode($response['response']['RETURN_MESSAGE']['STATUS_CODE']);
            }

            if (!empty($response['response']['AVAILABLE_DOCUMENTS'])) {
                $i = 0;

                // Sometimes ['AVAILABLE_DOCUMENTS']['DOCUMENT'] is an array of documents,
                // sometimes it's a single document. Normalise it to an array
                if (isset($response['response']['AVAILABLE_DOCUMENTS']['DOCUMENT']['DOCUMENT_ID'])) {
                    $response['response']['AVAILABLE_DOCUMENTS']['DOCUMENT'] = [
                        $response['response']['AVAILABLE_DOCUMENTS']['DOCUMENT']
                    ];
                }

                foreach ($response['response']['AVAILABLE_DOCUMENTS']['DOCUMENT'] as $document) {
                    if (preg_match("/^Automatically generated document due to object entering status Proposal accepted/", $document['DESCRIPTION'])) {
                        $documentResponse = $this->validateAndSend(
                            null,
                            null,
                            'GetDocument', /*method*/
                            [
                                'documentId' => $document['DOCUMENT_ID'],
                                'agreementCode' => $payment->provider_foreign_id
                            ], /*form params*/
                            'DOCUMENT' /*response key*/
                        );

                        $filename = 'propensio_credit_agreement.pdf';
                        $full_path = $payment->quote->getDir(null, true) . '/' . $filename;
                        $url = '/' . $payment->quote->getDir(null, true, true) . '/' . $filename;
                        file_put_contents($full_path, $documentResponse['response']);

                        // Add record to files table
                        if (file_exists($full_path)) {
                            $file = $payment->quote->file()->updateOrCreate([
                                'name' => 'credit_agreement',
                                'url' => $url,
                                'dir' => $full_path
                            ]);

                            // Store reference in finance application
                            $payment->update([
                                'credit_agreement_file_id' => $file->id
                            ]);
                        }
                    }
                }
            }

            if ($response['response']['STATUS'] == 3) {
                $status = 'pending';
            }
        } else {
            $status = $this->convertStatus($response['response']['STATUS']);
        }

        return [
            'status' => $status ?? $payment->paymentStatus->name,
            'lender_response_data' => $response['response'],
            'offer_expiration_date' => null
        ];
    }

    public function cancel(Payment $payment)
    {
        $data = [
            'AGREEMENT' => [
                '_attributes' => [
                    'AGREEMENT_CODE' => $payment->provider_foreign_id,
                    'STATUS_CODE' => 'AG_NOT_TAKEN_UP_CLOSED'
                ]
            ]
        ];

        $response = $this->validateAndSend(
            $data,
            null,
            'UpdateStatus', /*method*/
            'statusUpdateXML' /*request key*/
        );

        if (is_array($response) && $response['response']['STATUS'] == 0 && preg_match("/Failed to retrieve the Agreement Reference from the funding system based on the Agreement Code/", $response['response']['RETURN_MESSAGE']['MESSAGE_TEXT'])) {
            $payment->status = 'NotFound';
            $payment->save();

            return false;
        }

        if (is_array($response) && $response['response']['RETURN_MESSAGE']['MESSAGE_REF'] == 1) {
            Log::channel('finance')->info('Cancellation request for ' . $application->reference . ' rejected (403)');

            // Poll the status of the application to see where it's genuinely up to
            $result = $this->pollStatus($payment);

            Log::channel('finance')->info('Application status: ' . $result['status']);

            // If it isn't 'expired' then e-mail them for manual cancellation
            if ($result['status'] != 'expired') {
                Log::channel('finance')->info('Sending cancellation request e-mail');
                Mail::to($payment->finance_lender->underwriter_email)
                    ->send(new FinanceApplicationCancelManually($payment));
            } else {
                Log::channel('finance')->info('Application was already cancelled successfully');
            }

            return true;
        }

        return true;
    }

    public function sendSatNote(FinanceApplication $application)
    {
        Log::channel('finance')->info('Sending sat note by e-mail');

        Mail::to($application->finance_lender->sat_note_email)
            ->send(new FinanceApplicationSatNoteUpload($application));
    }

    public function convertStatusCode($statusCode)
    {
        switch ($statusCode) {
            case 'AG_AUTO_DECLINED':
            case 'AG_DECLINED':
                return 'declined';
                break;
            case 'AG_CONDITIONAL_ACCEPTANCE':
                return 'conditional_accept';
                break;
            case 'AG_NEW':
                return 'pending';
                break;
            case 'AG_NOT_TAKEN_UP':
            case 'AG_NOT_TAKEN_UP_CLOSED':
                return 'cancelled';
                break;
            case 'AG_NOT_TAKEN_UP_EXPIRED':
                return 'expired';
                break;
            case 'AG_PAYOUT_APPROVED':
            case 'AG_PENDING_PAYOUT':
                return 'parked';
                break;
            case 'AG_AUTO_ACCEPT':
            case 'AG_AUTO_ACCEPTED':
            case 'AG_PROPOSAL_ACCEPTED':
            case 'AG_ESCALATED_ACCEPTANCE':
                return 'accepted';
                break;
            case 'AG_READY_FOR_CREDIT_SEARCH':
                return 'pending';
                break;
            case 'PAID_OUT':
            case 'AG_LIVE_PRIMARY':
                return 'live';
                break;
            case 'REFER_TO_RISK':
            case 'READY_TO_UNDERWRITE':
            case 'AG_PENDING_UNDERWRITER':
            case 'AG_CAMPAIGN_REFERRAL':
            case 'AG_CREDIT_LINE_REFERRAL':
            case 'AG_ACCEPTED_IN_PRINCIPLE':
            case 'AG_CREDIT_SEARCH_ERROR':
            case 'CREDIT_SEARCHING_ERROR':
                return 'referred';
                break;
            default:
                break;
        }
    }

    public function convertStatus($status)
    {
        switch ($status) {
            case self::UNEXPECTED_ERROR: // Any error that we were not expecting. i.e. an Exception
                return 'error';
                break;
            case self::TARGET_REFERENCE_UNKNOWN: // Target reference not recognised
                return 'error';
                break;
            case self::PRE_BUSINESS_PROCESSING: // Any point within the import processing that is not an error
                return 'pending';
                break;
            case self::RECEIVED_INTO_BUSINESS_OK: // Pre processing and import completed successfully
                return 'pending';
                break;
            case self::PRE_BUSINESS_PROCESSING_ERROR: // An error occurred whilst pre processing, i.e. an error occurred within the import system
                return 'error';
                break;
            case self::IN_BUSINESS_PROCESSING: // This is at any non error state that is not terminal, within the business process
                return 'pending';
                break;
            case self::IN_BUSINESS_PROCESSING_ERROR: // An error occurred at some point during the business processing
                return 'error';
                break;
            case self::IN_BUSINESS_PROCESSING_COMPLETE: // The business process completed, or reached a terminal state that was not an error successfully
                return 'pending';
                break;
            default:
                break;
        }
    }

    private function isStatusOk($status)
    {
        return in_array($status, $this->okStatuses);
    }

    public function getNewRef(int $length = 32)
    {
        return substr(str_replace('-', '', Str::uuid()), 0, $length);
    }

    private function convertResidentialStatus(string $homeowner_status, bool $has_mortgage)
    {
        if ($homeowner_status == 1 && $has_mortgage) {
            return 'OWN';
        } elseif ($homeowner_status == 1 && !$has_mortgage) {
            return 'OWO';
        } else {
            return 'TEN';
        }
    }

    private function convertAddress($address, $application)
    {
        $this->addressIncrement++;

        return [
            '_attributes' => array_merge(
                [
                    'ADDRESS_ID' => $this->getNewRef(),
                    'IBC_REF' => $application->ibc_ref,
                    'STREET1' => $address['street'] ?? '',
                    'STREET2' => $address['address2'] ?? '',
                    'DISTRICT' => $address['address3'] ?? '',
                    'POSTTOWN' => $address['town'] ?? '',
                    'COUNTY' => $address['county'] ?? '',
                    'POSTCODE' => $address['post_code'] ?? '',
                    'YEARS_AT' => floor(($address['time_at_address'] ?? 0) / 12),
                    'MONTHS_AT' => ($address['time_at_address'] ?? 0) % 12,
                    'OCCUPANCY_STATUS' => $this->convertResidentialStatus(
                        $application->homeowner_status,
                        $application->has_mortgage
                    ),
                    'ADDRESS_CATEGORY' => $this->addressIncrement == 0 ? 'CUR' : 'PRE',
                ],
                Address::isHouseName($address['house_number']) ?
                    ['NAME' => $address['house_number']] :
                    (
                    !empty($address['house_number']) ?
                        ['HOUSE_NUMBER' => $address['house_number']] :
                        []
                    )
            )
        ];
    }

    private function convertEmployerAddress($address, $application)
    {
        return [
            '_attributes' => /*array_merge(*/[
                'ADDRESS_ID' => $this->getNewRef(),
                'IBC_REF' => $application->employer_ibc_ref,
                'STREET1' => $address['address1'] ?? '',
                'STREET2' => $address['address2'] ?? '',
                'DISTRICT' => $address['address3'] ?? '',
                'POSTTOWN' => $address['town'] ?? '',
                'COUNTY' => $address['county'] ?? '',
                'POSTCODE' => $address['post_code'] ?? '',
                'YEARS_AT' => floor($application->time_with_employer / 12),
                'MONTHS_AT' => 0,
                'ADDRESS_CATEGORY' => 'CUR',
            ]/*,
                (!empty($address['house_name'])) ? ['NAME' => $address['house_name']] : (!empty($address['house_number']) ? ['HOUSE_NUMBER' => $address['house_number']] : [])
            )*/
        ];
    }

    public function convertMaritalStatus($marital_status)
    {
        switch ($marital_status) {
            case 'single':
                return 'S';
                break;
            case 'married':
                return 'M';
                break;
            case 'divorced':
                return 'D';
                break;
            case 'widowed':
                return 'W';
                break;
            case 'separated':
                return 'P';
                break;
            case 'cohabiting':
                return 'N'; // defacto
                break;
            default:
                return 'X'; // unknown
                break;

        }
    }

    public function convertFulltime($employment_status)
    {
        switch ($employment_status) {
            case 'employed':
                return 1; #full time;
                break;
            default:
                return 0;
                break;
        }
    }

    public function convertEmploymentStatus($employment_status)
    {
        switch ($employment_status) {
            case 'employed':
                return 'Employed';
                break;
            case 'self-employed':
                return 'Self-Employed';
                break;
            case 'retired':
                return 'Retired';
                break;
            case 'unemployed':
                return 'Unemployed';
                break;
            default:
                return 'Unemployed';
                break;

        }
    }

    public function convertCompanyType($company_type)
    {
        switch ($company_type) {
            case 'regulated_charity':
                return 'C';
                break;
            case 'government':
                return 'G';
                break;
            case 'non_regulated_charity':
                return 'H';
                break;
            case 'consumer':
                return 'M';
                break;
            case 'professional_partnership_less_four':
                return 'N';
                break;
            case 'other':
                return 'O';
                break;
            case 'plc':
                return 'P';
                break;
            case 'sole':
                return 'S';
                break;
            case 'professional_partnership_more_four':
                return 'T';
                break;
            case 'partnership_more_four':
                return 'V';
                break;
            default:
                return 'L'; #limited
                break;
        }
    }

    public function parseReturnMessage($returnMessage): Collection
    {
        $matches = Str::of($returnMessage)
            ->matchAllFull('/([A-Z_]+)=[\'"]([^\'"]*)[\'"]/');

        return collect($matches[0])
            ->combine($matches[1]);
    }

    public function uploadEpvsCertificate(FinanceApplication $application, string $encodedFile)
    {
        // TODO: E-mail it to them?
        return false;
    }

    public function prequal(PaymentSurvey $survey): PrequalPromiseData|PrequalData
    {
        dispatch(function () use ($survey) {
            $helper = app(PaymentHelper::class)
                ->setParentModel($survey->parentable);

            $amount = $helper->getTotalCost() - $helper->getDeposit();

            $paymentProvider = PaymentProvider::byIdentifier('propensio');

            // See if there are already offers
            $offers = $survey
                ->paymentOffers()
                ->where('payment_provider_id', $paymentProvider->id)
                ->where('amount', $amount)
                ->where('selected', false)
                ->get();

            // If there aren't any offers...
            if ($offers->isEmpty()) {
                // Fetch products available from lender
                $products = $paymentProvider->paymentProducts;

                $reference = $helper->getReference() . '-' . Str::of(Str::random(5))->upper();

                $calculator = app(PaymentCalculator::class);

                // Store products to offers
                $offers = $products->map(function ($product) use ($survey, $paymentProvider, $reference, $amount, $calculator) {

                    $payments = $calculator->calculate($amount, $product->apr, $product->term, $product->deferred);

                    return $survey->paymentOffers()
                        ->create([
                            'name' => $product->name,
                            'type' => 'finance',
                            'reference' => $reference,
                            'amount' => $amount,
                            'payment_provider_id' => $paymentProvider->id,
                            'payment_product_id' => $product->id,
                            'apr' => $product->apr,
                            'term' => $product->term,
                            'deferred' => $product->deferred,
                            'first_payment' => $payments['firstPayment'],
                            'monthly_payment' => $payments['monthlyPayment'],
                            'final_payment' => $payments['finalPayment'],
                            'total_payable' => $payments['total'],
                            'status' => 'final',
                        ]);
                });
            }

            event(new OffersReceived(
                gateway: static::class,
                surveyId: $survey->id,
                offers: $offers,
            ));
        });

        return new PrequalPromiseData(
            gateway: static::class,
            surveyId: $survey->id,
        );
    }
}
