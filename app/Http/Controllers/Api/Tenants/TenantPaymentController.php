<?php

namespace App\Http\Controllers\Api\Tenants;

use Exception;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Stripe\V2\V2StripePaymentService;
use Illuminate\Support\Facades\Validator;

class TenantPaymentController extends Controller
{
    use ApiResponse;

    protected $stripeService;

    public function __construct(V2StripePaymentService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Get payment details for an invoice
     */
    public function getPaymentDetails(Request $request, $invoiceId)
    {
        try {
            $tenant = $request->user();

            $paymentDetails = $this->stripeService->getPaymentDetails($invoiceId, $tenant->id);

            if (!$paymentDetails['success']) {
                return $this->error([], $paymentDetails['message'], 400);
            }

            return $this->success($paymentDetails['data'], 'Payment details retrieved successfully');
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Create Stripe checkout session for payment
     */
    public function createCheckoutSession(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_id' => 'required|exists:invoices,id',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $tenant = $request->user();

            $result = $this->stripeService->createCheckoutSession(
                $request->invoice_id,
                $tenant->id
            );

            if (!$result['success']) {
                return $this->error([], $result['message'], 400);
            }

            return $this->success([
                'session_id' => $result['session_id'],
                'checkout_url' => $result['checkout_url'],
                'invoice' => $result['invoice'],
                'tenant' => $result['tenant'],
                'lease' => $result['lease'],
            ], 'Checkout session created successfully');
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Verify payment after Stripe redirect (for localhost testing)
     */
    public function verifyPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $result = $this->stripeService->verifyPayment($request->session_id);

            if (!$result['success']) {
                return $this->error([], $result['message'], 400);
            }

            return $this->success([
                'payment' => $result['payment'],
                'invoice' => $result['invoice'],
            ], 'Payment verified and processed successfully');
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Webhook handler for Stripe events
     */
    public function handleWebhook(Request $request)
    {
        try {
            $result = $this->stripeService->handleWebhook($request);

            if (!$result['success']) {
                return response()->json(['error' => $result['message']], 400);
            }

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get payment history
     */
    public function getPaymentHistory(Request $request)
    {
        try {
            $tenant = $request->user();
            $payments = $this->stripeService->getPaymentHistory($tenant->id);

            return $this->success($payments, 'Payment history retrieved successfully');
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
