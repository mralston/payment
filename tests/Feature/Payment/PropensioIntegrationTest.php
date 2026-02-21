<?php

use Mralston\Payment\Services\PropensioService;
use Mralston\Payment\Integrations\Propensio;
use Illuminate\Support\Str;

global $appId;
global $appNumber;

beforeEach(function () {
    $this->service = new PropensioService(
        endpoint: 'testing',
        username: config('payment.propensio.username'),
        password: config('payment.propensio.password'),
    );
});

it('can authenticate with API', function () {
    // Skip if credentials not configured
    if (!env('PROPENSIO_RUN_INTEGRATION_TESTS', false)) {
        $this->markTestSkipped('Propensio test credentials not configured');
    }

    $this->service->authenticate();

    expect($this->service->getJwtToken())->toBeString();
});

it('returns an 401 when unauthenticated', function () {
    // Skip if credentials not configured
    if (!env('PROPENSIO_RUN_INTEGRATION_TESTS', false)) {
        $this->markTestSkipped('Propensio test credentials not configured');
    }

    $this->service = new PropensioService(
        endpoint: 'testing',
        username: "incorrect",
        password: "incorrect",
    );

    $this->service->authenticate();

    expect($this->service->getLastResponse())->toHaveKey('code')
        ->toHaveKey('message')
        ->and($this->service->getLastResponse()['code'])->toBe(401)
        ->and($this->service->getLastResponse()['message'])->toBe('Unauthorized');

    expect($this->service->getJwtToken())->toBeNull();
});

it('can send an application', function () {

    // Skip if credentials not configured
    if (!env('PROPENSIO_RUN_INTEGRATION_TESTS', false)) {
        $this->markTestSkipped('Propensio test credentials not configured');
    }

    global $appId;

    $this->service->sendApplicationRequest(
        payloadForSendingApplicationRequest(
            config('payment.propensio.media_code')
        )
    );

    $appId = $this->service->getLastResponse()['results']['data']['applicationId'];

    expect($this->service->getLastResponse())->toHaveKey('code')
        ->toHaveKey('message')
        ->and($this->service->getLastResponse()['code'])->toBe(200)
        ->and($this->service->getLastResponse()['message'])->toBe('OK');
});

it('can get an application', function () {

    // Skip if credentials not configured
    if (!env('PROPENSIO_RUN_INTEGRATION_TESTS', false)) {
        $this->markTestSkipped('Propensio test credentials not configured');
    }

    global $appId;
    global $appNumber;

    $this->service->getApplicationRequest($appId);

    $appNumber = $this->service->getLastResponse()['results']['applicationNumber'];

    expect($this->service->getLastResponse())->toHaveKey('code')
        ->toHaveKey('message')
        ->and($this->service->getLastResponse()['code'])->toBe(200)
        ->and($this->service->getLastResponse()['message'])->toBe('OK');
});

it('can upload a file to an application', function () {
    
    // Skip if credentials not configured
    if (!env('PROPENSIO_RUN_INTEGRATION_TESTS', false)) {
        $this->markTestSkipped('Propensio test credentials not configured');
    }

    global $appId;
    global $appNumber;

    $fileContent = file_get_contents('tests/Feature/Payment/test.pdf');

    $fileContent = base64_encode($fileContent);

    $this->service->uploadSatisactionNote(
        $appNumber,
        'test.pdf',
        $fileContent
    );

    expect($this->service->getLastResponse())->toHaveKey('code')
        ->toHaveKey('message');
});

it('can cancel an application', function () {
    // Skip if credentials not configured
    if (!env('PROPENSIO_RUN_INTEGRATION_TESTS', false)) {
        $this->markTestSkipped('Propensio test credentials not configured');
    }

    global $appId;
    global $appNumber;

    $result = $this->service->cancelApplicationRequest(
        $appNumber,
    );

    expect($result)->toBeTrue();
});
