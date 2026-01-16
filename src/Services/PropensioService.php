<?php

namespace Mralston\Payment\Services;

use GuzzleHttp\Client;
use Mralston\Payment\Enums\FileRequestType;
use Mralston\Payment\Enums\ApiAction;

class PropensioService
{
    private $guzzleClient;
    
    private string|null $jwtToken = null;

    private array $response = [];

    /**
     * Endpoints to be used based on environment.
     *
     * @var array|string[]
     */
    private array $endpoints = [
        'local' => 'https://qa.aryzasoftware.com',
        'dev' => 'https://qa.aryzasoftware.com',
        'testing' => 'https://qa.aryzasoftware.com',
        'production' => 'https://qa.aryzasoftware.com',
    ];

    /**
     * API endpoint to send POST requests to.
     *
     * @var string
     */
    private string $endpoint;

    public function __construct(
        string $endpoint,
        private string $username,
        private string $password,
    )
    {
        $this->endpoint = $this->endpoints[$endpoint];

        $this->guzzleClient = new Client([
            'base_uri' => $this->endpoint . '/',
            'http_errors' => false,
            'timeout' => 60
        ]);
    }

    public function authenticate(): void
    {
        $response = $this->guzzleClient->request('POST', '/rest/api2/login', [
            'auth' => [$this->username, $this->password],
            'headers' => [
                'Accept' => 'application/json',
            ],
            // If the API still requires an empty body to be sent
            'body' => '' 
        ]);

        $responseData = json_decode($response->getBody()->getContents(), true);

        if ($response->getStatusCode() == 401) {
            $this->setLastResponse($responseData);
            return;
        }

        if ($response->getStatusCode() == 200) {

            if (!isset($responseData['results'][0]['JWT'])) {
                throw new \Exception('Failed to authenticate with Propensio');
            }

            $this->jwtToken = $responseData['results'][0]['JWT'];
            
            $this->setLastResponse($responseData);
        }
        else {
            throw new \Exception('Failed to authenticate with Propensio');
        }
    }

    public function sendApplicationRequest(array $requestData): void
    {
        $this->authenticate();

        $response = $this->guzzleClient->request(
            'POST',
            '/prod1/api2/custom/s03applications', [
                'headers' => [
                    'accept' => 'application/json',
                    'jwt' => $this->jwtToken,
                ],
                'json' => $requestData,
            ]);

        $this->setLastResponse(json_decode(
            $response->getBody()->getContents(),
            true
        ));
    }

    public function getApplicationRequest(string $applicationId): void
    {
        $this->authenticate();

        $response = $this->guzzleClient->request('GET',
            '/prod1/api2/custom/s03applications?apiFormCode=' . config('payment.propensio.api_from_code') . '&applicationId=' . $applicationId, [
                'headers' => [
                    'accept' => 'application/json',
                    'jwt' => $this->jwtToken,
                ],
            ]);

        $this->setLastResponse(json_decode(
            $response->getBody()->getContents(),
            true
        ));
    }

    public function cancelApplicationRequest(string $applicationNumber): bool
    {
        $this->updateApplicationRequest(
            [
                'applicationNumber' => $applicationNumber,
                'functionCode' => ApiAction::PROPENSIO_CANCEL_APPLICATION->value,
                'mediaCode' => config('payment.propensio.media_code')
            ]
        );

        if (
            isset($this->getLastResponse()['results']['functionCallStatus']) &&
            $this->getLastResponse()['results']['functionCallStatus'] == 'SUCCESS'
        ) {
            return true;
        } else {
            return false;
        }
    }

    private function updateApplicationRequest(array $requestData): void
    {
        $this->authenticate();

        $queryString = http_build_query($requestData);

        $response = $this->guzzleClient->request('POST',
            '/prod1/api2/custom/applicationWebhook?' . $queryString, [
                'headers' => [
                    'accept' => 'application/json',
                    'jwt' => $this->jwtToken,
                ],
                //'json' => $requestData,
            ]);

        $this->setLastResponse(json_decode(
            $response->getBody()->getContents(),
            true
        ));
    }

    public function uploadSatisactionNote(
        string $applicationNumber,
        string $fileName,
        string $fileContent
    ): void {
        $this->uploadFileToApplication(
            $applicationNumber,
            $fileName,
            $fileContent,
            FileRequestType::PROPENSIO_UPLOAD_SAT_NOTE->value
        );
    }

    private function uploadFileToApplication(
        string $applicationNumber,
        string $fileName,
        string $fileContent,
        string $instruction
    ): void {
        $this->authenticate();

        $queryString = http_build_query([
            'applicationNumber' => $applicationNumber,
            'apiCode' => $instruction
        ]);

        $response = $this->guzzleClient->request('POST',
            '/prod1/api2/custom/uploadFile?' . $queryString, [
                'headers' => [
                    'accept' => 'application/json',
                    'jwt' => $this->jwtToken,
                ],
                'json' => [
                    'filename' => $fileName,
                    'base64Content' => $fileContent,
                ]
            ]);

        $this->setLastResponse(json_decode(
            $response->getBody()->getContents(),
            true
        ));
    }

    public function setLastResponse(array $response): void
    {
        $this->response = $response;
    }

    public function getLastResponse(): array
    {
        return $this->response;
    }

    public function getJwtToken(): string|null
    {
        return $this->jwtToken;
    }
}
