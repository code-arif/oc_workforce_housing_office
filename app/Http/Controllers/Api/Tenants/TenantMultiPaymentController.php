<?php

namespace App\Http\Controllers\Api\Tenants;

use Exception;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Stripe\V2\V2StripeMultiPaymentService;
use Illuminate\Support\Facades\Validator;

class TenantMultiPaymentController extends Controller
{
    use ApiResponse;

    protected V2StripeMultiPaymentService $multiPaymentService;

    public function __construct(V2StripeMultiPaymentService $multiPaymentService)
    {
        $this->multiPaymentService = $multiPaymentService;
    }

    /**
     * Get payment details (summary + fees) for multiple invoices before checkout.
     *
     * POST /multi-payments/details
     * Body: { invoice_ids: [1,2,3], payment_method_type: "card" }
     */
    public function getPaymentDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_ids' => 'required|array|min:1|max:20',
            'invoice_ids.*' => 'required|integer|exists:invoices,id',
            'payment_method_type' => 'nullable|in:card,us_bank_account',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $tenant = $request->user();
            $invoiceIds = array_map('intval', $request->input('invoice_ids'));
            $paymentMethodType = $request->input('payment_method_type', 'card');

            $result = $this->multiPaymentService->getMultiPaymentDetails($invoiceIds, $tenant->id, $paymentMethodType);

            if (!$result['success']) {
                return $this->error([], $result['message'], 422);
            }

            return $this->success($result['data'], 'Multi-payment details retrieved successfully.');
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Create a Stripe Checkout session that covers multiple invoices in one payment.
     *
     * POST /multi-payments/checkout/create
     * Body: { invoice_ids: [1,2,3], payment_method_type: "card" }
     */
    public function createCheckoutSession(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_ids'         => 'required|array|min:1|max:20',
            'invoice_ids.*'       => 'required|integer|exists:invoices,id',
            'payment_method_type' => 'nullable|in:card,us_bank_account',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $tenant            = $request->user();
            $invoiceIds        = array_map('intval', $request->input('invoice_ids'));
            $paymentMethodType = $request->input('payment_method_type', 'card');

            $result = $this->multiPaymentService->createCheckoutSession($invoiceIds, $tenant->id, $paymentMethodType);

            if (!$result['success']) {
                return $this->error([], $result['message'], 422);
            }

            return $this->success([
                'session_id'   => $result['session_id'],
                'checkout_url' => $result['checkout_url'],
                'summary'      => $result['summary'],
                'tenant'       => $result['tenant'],
                'lease'        => $result['lease'],
            ], 'Checkout session created successfully.');
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Verify a completed Stripe checkout session for a multi-invoice payment.
     * Called after Stripe redirects the user back to the success URL.
     *
     * POST /multi-payments/verify
     * Body: { session_id: "cs_xxx" }
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
            $sessionId = $request->input('session_id');

            $result = $this->multiPaymentService->verifyPayment($sessionId);

            if (!$result['success']) {
                return $this->error([], $result['message'] ?? 'Payment verification failed.', 422);
            }

            return $this->success([
                'payments'       => $result['payments'] ?? [],
                'invoices'       => $result['invoices'] ?? [],
                'total_charged'  => $result['total_charged'] ?? 0,
                'processing_fee' => $result['processing_fee'] ?? 0,
            ], 'Multi-invoice payment verified and processed successfully.');
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
