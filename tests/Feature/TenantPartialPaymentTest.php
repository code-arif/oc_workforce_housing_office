<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Invoice;
use App\Services\Stripe\V2\V2StripePartialPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class TenantPartialPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected $stripeServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mock for the partial payment service
        $this->stripeServiceMock = Mockery::mock(V2StripePartialPaymentService::class);
        $this->app->instance(V2StripePartialPaymentService::class, $this->stripeServiceMock);
    }

    /**
     * Helper to authenticate as a tenant using JWT
     */
    protected function authenticateTenant()
    {
        $tenant = Tenant::factory()->create();
        $token = JWTAuth::fromUser($tenant);
        return [$tenant, $token];
    }

    /**
     * Helper to create a fully set up invoice satisfying DB integrity constraints
     */
    protected function createTestInvoice($tenant, $invoiceNumber = 'INV-TEST-01')
    {
        $propertyType = \App\Models\PropertyType::create([
            'name' => 'Apartment',
            'slug' => 'apartment-' . Str::random(5),
        ]);
        
        $property = \App\Models\Property::create([
            'name' => 'Test Property',
            'slug' => 'test-property-' . Str::random(5),
            'property_type_id' => $propertyType->id,
        ]);
        
        $season = \App\Models\Season::create([
            'name' => 'Summer 2026',
            'blanket_start_date' => now()->toDateString(),
            'blanket_end_date' => now()->addYear()->toDateString(),
        ]);
        
        $lease = \App\Models\Lease::create([
            'tenant_id' => $tenant->id,
            'property_id' => $property->id,
            'season_id' => $season->id,
            'status' => 'ACTIVE',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'rent_amount' => 1000.00,
            'deposit_amount' => 1000.00,
            'payment_frequency' => 'MONTHLY',
        ]);
        
        return Invoice::create([
            'lease_id' => $lease->id,
            'tenant_id' => $tenant->id,
            'invoice_number' => $invoiceNumber,
            'amount' => 1000.00,
            'total_amount' => 1000.00,
            'paid_amount' => 0,
            'balance_due' => 1000.00,
            'due_date' => now()->addDays(30),
            'type' => 'RENT',
            'status' => 'UNPAID',
        ]);
    }

    public function test_guest_can_access_webhook_but_requires_valid_payload()
    {
        $this->stripeServiceMock
            ->shouldReceive('handleWebhook')
            ->once()
            ->andReturn(['success' => true]);

        $response = $this->postJson('/api/stripe/partial-webhook', [
            'id' => 'evt_test',
            'type' => 'payment_intent.succeeded',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_unauthenticated_user_cannot_access_partial_payment_details()
    {
        $response = $this->getJson('/api/v1/tenant/partial-payments/invoice/1/details');
        $response->assertStatus(401);
    }

    public function test_tenant_can_get_partial_payment_details()
    {
        [$tenant, $token] = $this->authenticateTenant();

        $this->stripeServiceMock
            ->shouldReceive('getPaymentDetails')
            ->once()
            ->with('123', $tenant->id)
            ->andReturn([
                'success' => true,
                'data' => [
                    'invoice' => ['id' => 123, 'balance_due' => 1000.00],
                ]
            ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/tenant/partial-payments/invoice/123/details');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Payment details retrieved successfully',
        ]);
    }

    public function test_tenant_can_create_partial_checkout_session()
    {
        [$tenant, $token] = $this->authenticateTenant();
        $invoice = $this->createTestInvoice($tenant, 'INV-TEST-01');

        $this->stripeServiceMock
            ->shouldReceive('createCheckoutSession')
            ->once()
            ->with($invoice->id, $tenant->id, 250.00, 'card')
            ->andReturn([
                'success' => true,
                'session_id' => 'cs_test_123',
                'checkout_url' => 'https://checkout.stripe.com/pay/cs_test_123',
                'invoice' => ['id' => $invoice->id, 'base_amount' => 250.00],
                'tenant' => ['name' => 'John Doe', 'email' => $tenant->email],
                'lease' => ['property' => 'Test Property', 'unit' => 'A1'],
            ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/tenant/partial-payments/checkout/create', [
                'invoice_id' => $invoice->id,
                'amount_to_pay' => 250.00,
                'payment_method_type' => 'card',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'session_id' => 'cs_test_123',
            ]
        ]);
    }

    public function test_tenant_can_create_partial_payment_intent()
    {
        [$tenant, $token] = $this->authenticateTenant();
        $invoice = $this->createTestInvoice($tenant, 'INV-TEST-02');

        $this->stripeServiceMock
            ->shouldReceive('createPaymentIntent')
            ->once()
            ->with($invoice->id, $tenant->id, 500.00, 'card')
            ->andReturn([
                'success' => true,
                'client_secret' => 'pi_test_secret',
                'payment_intent_id' => 'pi_test_123',
                'payment_method_type' => 'card',
                'base_amount' => 500.00,
                'processing_fee' => 14.80,
                'total_charge' => 514.80,
                'invoice' => ['id' => $invoice->id, 'balance_due' => 1000.00],
                'tenant' => ['name' => 'John Doe', 'email' => $tenant->email],
                'lease' => ['property' => 'Test Property'],
                '_frontend_hint' => ['is_async' => false],
            ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/tenant/partial-payments/intent/create', [
                'invoice_id' => $invoice->id,
                'amount_to_pay' => 500.00,
                'payment_method_type' => 'card',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'client_secret' => 'pi_test_secret',
            ]
        ]);
    }

    public function test_tenant_can_verify_payment()
    {
        [$tenant, $token] = $this->authenticateTenant();

        $this->stripeServiceMock
            ->shouldReceive('verifyPayment')
            ->once()
            ->with('cs_test_123')
            ->andReturn([
                'success' => true,
                'payment' => ['id' => 1, 'amount' => 250.00],
                'invoice' => ['id' => 123, 'status' => 'PARTIAL'],
            ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/tenant/partial-payments/verify', [
                'session_id' => 'cs_test_123',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Payment verified and processed successfully',
        ]);
    }

    public function test_tenant_can_retrieve_payment_history()
    {
        [$tenant, $token] = $this->authenticateTenant();

        $this->stripeServiceMock
            ->shouldReceive('getPaymentHistory')
            ->once()
            ->with($tenant->id)
            ->andReturn([
                ['id' => 1, 'amount' => 250.00, 'payment_number' => 'PAY-001'],
            ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/tenant/partial-payments/history');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                ['payment_number' => 'PAY-001']
            ]
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
