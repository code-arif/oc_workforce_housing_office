<?php

namespace App\Http\Controllers\Api\Tenants;

use Exception;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Services\Tenants\StripePaymentService;

class TenantPaymentController extends Controller
{
    use ApiResponse;

    protected $stripeService;

    public function __construct(StripePaymentService $stripeService)
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
            'rent_amount' => 'nullable|numeric|min:0',
            'include_deposit' => 'nullable|boolean',
            'success_url' => 'required|url',
            'cancel_url' => 'required|url',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $tenant = $request->user();

            $result = $this->stripeService->createCheckoutSession(
                $request->invoice_id,
                $tenant->id,
                $request->rent_amount,
                $request->boolean('include_deposit', true),
                $request->success_url,
                $request->cancel_url
            );

            if (!$result['success']) {
                return $this->error([], $result['message'], 400);
            }

            return $this->success([
                'session_id' => $result['session_id'],
                'checkout_url' => $result['checkout_url'],
                'total_amount' => $result['total_amount'],
            ], 'Checkout session created successfully');
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Verify payment after Stripe redirect
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
            $tenant = $request->user();

            $result = $this->stripeService->verifyPayment(
                $request->session_id,
                $tenant->id
            );

            if (!$result['success']) {
                return $this->error([], $result['message'], 400);
            }

            return $this->success([
                'payment' => $result['payment'],
                'invoice' => $result['invoice'],
            ], 'Payment verified successfully');
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
    public function paymentHistory(Request $request)
    {
        try {
            $tenant = $request->user();

            $payments = $this->stripeService->getPaymentHistory($tenant->id);

            return $this->success([
                'payments' => $payments
            ], 'Payment history retrieved successfully');
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Calculate payment amount (with optional adjustments)
     */
    public function calculatePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_id' => 'required|exists:invoices,id',
            'rent_amount' => 'nullable|numeric|min:0',
            'include_deposit' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $tenant = $request->user();

            $calculation = $this->stripeService->calculatePaymentAmount(
                $request->invoice_id,
                $tenant->id,
                $request->rent_amount,
                $request->boolean('include_deposit', true)
            );

            if (!$calculation['success']) {
                return $this->error([], $calculation['message'], 400);
            }

            return $this->success($calculation['data'], 'Payment calculated successfully');
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
