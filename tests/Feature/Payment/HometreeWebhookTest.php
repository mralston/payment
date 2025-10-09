<?php

use App\User;
use Illuminate\Support\Collection;
use Laravel\Sanctum\Sanctum;
use Mralston\Payment\Services\HometreeService;

/**
 * Feature tests for the Hometree webhook endpoint provided by the payment package.
 */
function validHometreeRecordPayload(): array
{
    return [
        'application-id' => 'APP-123',
        'customer-full-name' => 'Jane Doe',
        'application-status' => 'approved',
        // Optional fields (present to mirror realistic payloads)
        'customer-applied-at-timestamp' => now()->toIso8601String(),
        'htf-quote-id' => 'Q-456',
        'client-application-reference' => 'REF-789',
        'application-price' => '9999.00',
        'upfront-payment-amount' => '99.00',
        'monthly-payment-amount' => '199.00',
        'total-payable' => '12000.00',
    ];
}

it('rejects unauthenticated requests with 401', function () {
    // When: posting to the webhook without Sanctum auth
    $response = postJson(route('payment.webhook.hometree'), [validHometreeRecordPayload()]);

    // Then
    $response->assertUnauthorized();
});

it('accepts a valid payload and delegates to HometreeService', function () {
    // Given: an authenticated user
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    // And: a mock for the HometreeService expecting handleWebhook with a collection of our records
    $records = [validHometreeRecordPayload()];

    $this->mock(HometreeService::class, function ($mock) use ($records) {
        $mock->shouldReceive('handleWebhook')
            ->once()
            ->withArgs(function (Collection $arg) use ($records) {
                // Ensure it received a collection with the same data we sent
                return $arg->count() === 1 && $arg->first() == $records[0];
            })
            ->andReturnTrue();
    });

    // When: posting JSON array (as the request class normalizes to `records`)
    $response = postJson(route('payment.webhook.hometree'), $records);

    // Then: response OK with the expected body
    $response->assertOk()
        ->assertJson(['message' => 'success']);
});

it('returns 422 when payload is invalid and does not call the service', function () {
    // Given: an authenticated user
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    // And: ensure the service is NOT called
    $this->mock(HometreeService::class, function ($mock) {
        $mock->shouldNotReceive('handleWebhook');
    });

    // When: posting an invalid payload (missing required keys like application-id, customer-full-name, application-status)
    $invalid = [['foo' => 'bar']];
    $response = postJson(route('payment.webhook.hometree'), $invalid);

    // Then
    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'records',
            'records.0.application-id',
            'records.0.customer-full-name',
            'records.0.application-status',
        ]);
});
