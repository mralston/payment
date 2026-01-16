<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use Mralston\Payment\Services\PropensioService;

/**
 * Returns a mock Propensio API response for application requests.
 */
function validPropensioApplicationResponse(): array
{
    return [
        'code' => 200,
        'message' => 'OK',
        'results' => [
            'applicationId' => '43602192',
            'applicationNumber' => '0000000003',
            'loan' => [
                'applicationFormCode' => 'S03_RETAIL',
                'applicationStatusCode' => 'WSFsanction',
                'applicationStatusDesc' => 'Sanction',
                'applicationReceivedDate' => '2023-06-12T14:33:03.000Z',
                'productCode' => '100',
                'loanPurposeCode' => 'DEBTCONS',
                'loanPurposeDesc' => 'Debt consolidation',
                'introducerUniqueCode' => 'XYZWEBSITE',
                'introducerExternalReference' => '48A28FF02B224B1C9DE5AE03F6F93AAD',
                'cashPriceAmount' => 5500,
                'depositAmount' => 500,
                'additionalContribution' => 0,
                'optionFee' => 0,
                'loanAmount' => 5000,
                'repaymentTermInMonths' => 36,
                'partExchangeAmount' => 4000,
                'settlementValueAmount' => 5000,
                'monthlyInterestRate' => 1.1642,
                'monthlyRepaymentAmount' => 170.82,
                'finalRepaymentAmount' => 170.82,
                'totalPayableAmount' => 6149.31,
                'totalChargeForCreditAmount' => 1149.31,
                'aprRate' => 14.9,
                'docusignEnvelopeCreationDate' => '2023-06-15T12:35:16.000Z',
                'docusignDocumentOpenedDate' => '2023-06-15T12:37:20.000Z',
                'docusignSignedDate' => '2023-06-15T12:43:22.000Z',
                'executedDate' => '2023-06-15T12:43:21.000Z',
                'actualPayoutDate' => '2023-06-02',
                'firstRepaymentDate' => '2023-06-02',
                'preferredPaymentDay' => 8,
                'consentToCreditSearch' => true,
                'consentToTerms' => true,
                'portalConfirmationUrl' => 'https://qa.aryzasoftware.com/prod1/portal/portal.jsp?c=43458989&p=43601581&g=43601583&pcode=YjJlNjI1MjUzNy1iZjQyMDQtNDQ5ODIzLTE4MjYxMi1iODc3MTlhN2FhNTM5ODVkMDAyOTA5Njk4NmkydXRjYWVuZWRl&app1id=43602683',
                'notes' => 'Additional notes',
            ],
            'applicant' => [
                'titleCode' => 'MR',
                'firstName' => 'Citlalli',
                'middleName' => 'Lois',
                'lastName' => 'Hill',
                'dateOfBirth' => '1976-03-11',
                'email' => 'a@a.com',
                'mobilePhone' => '07777777777',
                'homePhone' => '02222222222',
                'maritalStatusCode' => 'M',
                'maritalStatusDesc' => 'Married',
                'residentialStatusCode' => 'H',
                'residentialStatusDesc' => 'Home Owner (no mortgage)',
                'employmentStatusCode' => 'E',
                'employmentStatusDesc' => 'Full Time Employed',
                'employmentEmployerName' => 'Aryza',
                'employmentTimeYears' => '5',
                'employmentTimeMonths' => '2',
                'allowMarketingByEmail' => true,
                'allowMarketingByMail' => true,
                'allowMarketingByPhone' => true,
                'allowMarketingBySms' => true,
                'isVulnerable' => true,
                'VulnerableReason' => 'reason',
            ],
            'bankAccount' => [
                'bankAccountName' => 'Mr A Franks',
                'bankAccountNumber' => '29250380',
                'bankSortCode' => '999998',
                'bankName' => 'Llloyds Bank',
            ],
            'currentAddress' => [
                'buildingSubName' => 'Flat 2A',
                'buildingName' => 'Trident House',
                'buildingNo' => '25',
                'addressLine1' => '28 Alley Cat Lane',
                'addressLine2' => 'Test Town',
                'city' => 'Test Town',
                'county' => 'South Glamorgan',
                'postcode' => 'X9 9AA',
                'timeYears' => '5',
                'timeMonths' => '2',
            ],
            'previousAddress1' => [
                'buildingSubName' => 'Flat 2A',
                'buildingName' => 'Trident House',
                'buildingNo' => '25',
                'addressLine1' => '28 Alley Cat Lane',
                'addressLine2' => 'Test Town',
                'city' => 'Test Town',
                'county' => 'South Glamorgan',
                'postcode' => 'X9 9AA',
                'timeYears' => '5',
                'timeMonths' => '2',
            ],
            'previousAddress2' => [
                'buildingSubName' => 'Flat 2A',
                'buildingName' => 'Trident House',
                'buildingNo' => '25',
                'addressLine1' => '28 Alley Cat Lane',
                'addressLine2' => 'Test Town',
                'city' => 'Test Town',
                'county' => 'South Glamorgan',
                'postcode' => 'X9 9AA',
                'timeYears' => '5',
                'timeMonths' => '2',
            ],
            'incomeExpenditure' => [
                'grossAnnualSalary' => 2000,
                'grossHouseholdIncome' => 2500,
                'netMonthlySalary' => 1500,
                'netMonthlyIncomeBenefits' => 0,
                'netMonthlyIncomePension' => 0,
                'netMonthlyIncomeOtherSources' => 0,
                'monthlyExpensesRentOrMortgage' => 550,
                'monthlyExpensesOther' => 250.89,
                'expectAnySignificantIncomeChg' => false,
                'expectAnySignificantIncomeChgReason' => 'reason',
            ],
            'checklistItems' => [
                [
                    'documentCode' => 'BANK',
                    'documentId' => '161701898',
                    'description' => 'Bank Account Verification',
                    'lastUpdated' => '2025-03-11T11:46:34.000Z',
                    'statusCode' => 'OUTSTANDING',
                    'fileUploadRequired' => true,
                    'hasDocument' => false,
                    'isComplete' => false,
                    'notes' => '',
                ],
            ],
            'retailer' => [
                'retailerName' => 'Heatable',
                'retailerId' => 44376047,
                'salespersonName' => 'Heatable Test',
                'salespersonId' => 44376048,
                'salespersonEmail' => 'a@a',
                'goodsType' => 'Boiler',
                'goodsTypeCode' => 'BOIL',
                'retailerCommission' => 0,
                'brokerCommission' => 0,
                'subsidy' => 0,
                'introducersReference' => 'ABC473',
                'depositAmount' => 500,
                'installerName' => 'Simon Shaw',
                'assetType' => 'STATIC_CARAVAN',
                'assetMake' => 'Willerby',
                'assetModel' => 'Rio',
                'assetSerialNumber' => '1234567890ABCDEFG',
                'assetCondition' => 'NEW',
            ],
        ],
    ];
}

function validPropensioUpdateApplicationResponse(): array
{
    return [
        "code" => 200,
        "message" => "OK",
        "results" => [
          "functionCallStatus" => "SUCCESS",
          "functionCode" => "setApplicationToNpw",
          "functionCallStatusMsg" => "Application status successfully changed from 'WSFmanualrefer' to 'WSFcustomernpw'",
          "mediaCode" => "AMBER123",
          "applicationNumber" => "0000010359",
          "environment" => "DEV"
        ]
    ];
}

beforeEach(function () {
    // Create a mock handler that will return predefined responses
    $this->mockHandler = new MockHandler();
    
    // Create a handler stack with the mock handler
    $handlerStack = HandlerStack::create($this->mockHandler);
    
    // Create a Guzzle client with the mock handler
    $this->mockClient = new Client(['handler' => $handlerStack]);
    
    // Create service instance with test credentials
    $this->service = new PropensioService(
        endpoint: 'testing',
        username: 'test-user',
        password: 'test-pass'
    );
    
    // Use reflection to inject the mock client
    $reflection = new \ReflectionClass($this->service);
    $property = $reflection->getProperty('guzzleClient');
    $property->setAccessible(true);
    $property->setValue($this->service, $this->mockClient);
    
    // Set a JWT token for authenticated requests
    $jwtProperty = $reflection->getProperty('jwtToken');
    $jwtProperty->setAccessible(true);
    $jwtProperty->setValue($this->service, 'test-jwt-token');
});

it('successfully retrieves an application request', function () {
    // Arrange: Mock the API response
    $this->mockHandler->append(
        // First: Authentication response
        new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode(['results' => [['JWT' => 'test-jwt-token']]])
        ),
        // Second: Application submission response
        new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode(validPropensioApplicationResponse())
        )
    );
    
    // Act: Call the method
    $this->service->getApplicationRequest('43602192');
    
    // Assert: Check that the response was stored correctly
    $lastResponse = $this->service->getLastResponse();
    
    expect($lastResponse)
        ->toBeArray()
        ->toHaveKey('code')
        ->toHaveKey('message')
        ->toHaveKey('results')
        ->and($lastResponse['code'])->toBe(200)
        ->and($lastResponse['results'])->toBeArray()
        ->and($lastResponse['results']['loan']['applicationStatusCode'])->toBeString()
        ->and($lastResponse['message'])->toBe('OK')
        ->and($lastResponse['results']['applicationId'])->toBe('43602192')
        ->and($lastResponse['results']['applicationNumber'])->toBe('0000000003');
});

it('successfully sends an application request', function () {
    // Arrange: Mock the API response
    $this->mockHandler->append(
        // First: Authentication response
        new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode(['results' => [['JWT' => 'test-jwt-token']]])
        ),
        // Second: Application submission response
        new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode(validPropensioApplicationResponse())
        )
    );

    // Act: Call the method
    $this->service->sendApplicationRequest(['test' => 'test']);

    // Assert: Check that the response was stored correctly
    $lastResponse = $this->service->getLastResponse();

    expect($lastResponse)
        ->toBeArray()
        ->toHaveKey('code')
        ->toHaveKey('message')
        ->toHaveKey('results')
        ->and($lastResponse['code'])->toBe(200)
        ->and($lastResponse['results'])->toBeArray()
        ->and($lastResponse['results']['loan']['applicationFormCode'])->toBeString()
        ->and($lastResponse['message'])->toBe('OK')
        ->and($lastResponse['results']['applicationId'])->toBe('43602192')
        ->and($lastResponse['results']['applicationNumber'])->toBe('0000000003');
});

it('successfully updates an application', function () {
    // Arrange: Mock the API response
    $this->mockHandler->append(
        // First: Authentication response
        new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode(['results' => [['JWT' => 'test-jwt-token']]])
        ),
        // Second: Application cancellation response
        new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode(validPropensioUpdateApplicationResponse())
        )
    );

    // Act: Call the method
    $this->service->updateApplicationRequest('43602192', ['test' => 'test']);

    // Assert: Check that the response was stored correctly
    $lastResponse = $this->service->getLastResponse();

    expect($lastResponse)
        ->toBeArray()
        ->toHaveKey('code')
        ->toHaveKey('message')
        ->toHaveKey('results')
        ->and($lastResponse['code'])->toBe(200)
        ->and($lastResponse['results'])->toBeArray()
        ->and($lastResponse['results']['functionCallStatus'])->toBeString()
        ->and($lastResponse['results']['functionCode'])->toBeString()
        ->and($lastResponse['results']['functionCallStatusMsg'])->toBeString()
        ->and($lastResponse['results']['mediaCode'])->toBeString()
        ->and($lastResponse['results']['applicationNumber'])->toBeString()
        ->and($lastResponse['results']['environment'])->toBeString();
});
