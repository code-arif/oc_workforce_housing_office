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
            'payment_method_type' => 'nullable|in:card,us_bank_account',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $tenant = $request->user();
            $paymentMethodType = $request->input('payment_method_type', 'card');

            $result = $this->stripeService->createCheckoutSession(
                $request->invoice_id,
                $tenant->id,
                $paymentMethodType
            );

            if (!$result['success']) {
                return $this->error([], $result['message'], 400);
            }

            return $this->success([
                'session_id' => $result['session_id'],
                'checkout_url' => $result['checkout_url'],
                'payment_method_type' => $paymentMethodType,
                'invoice' => $result['invoice'],
                'tenant' => $result['tenant'],
                'lease' => $result['lease'],
            ], 'Checkout session created successfully');
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Create Stripe Payment Intent for custom Payment Element
     */
    // public function createPaymentIntent(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'invoice_id' => 'required|exists:invoices,id',
    //         'amount_to_pay' => 'nullable|numeric|min:0.01',
    //         'payment_method_type' => 'required|in:card,us_bank_account', // card or ACH
    //     ]);

    //     if ($validator->fails()) {
    //         return $this->validationError($validator->errors());
    //     }

    //     try {
    //         $tenant = $request->user();

    //         $result = $this->stripeService->createPaymentIntent(
    //             $request->invoice_id,
    //             $tenant->id,
    //             $request->amount_to_pay,
    //             $request->payment_method_type
    //         );

    //         if (!$result['success']) {
    //             return $this->error([], $result['message'], 400);
    //         }

    //         return $this->success([
    //             'client_secret' => $result['client_secret'],
    //             'base_amount' => $result['base_amount'],
    //             'processing_fee' => $result['processing_fee'],
    //             'total_charge' => $result['total_charge'],
    //             'invoice' => $result['invoice'],
    //             'tenant' => $result['tenant'],
    //             'lease' => $result['lease'],
    //         ], 'Payment intent created successfully');
    //     } catch (Exception $e) {
    //         return $this->error([], $e->getMessage(), 500);
    //     }
    // }


    /**
 * Create Stripe Payment Intent for custom Payment Element (Card + ACH)
 */
public function createPaymentIntent(Request $request)
{
    $validator = Validator::make($request->all(), [
        'invoice_id'          => 'required|exists:invoices,id',
        'amount_to_pay'       => 'nullable|numeric|min:0.01',
        'payment_method_type' => 'required|in:card,us_bank_account',
    ]);

    if ($validator->fails()) {
        return $this->validationError($validator->errors());
    }

    try {
        $tenant = $request->user();

        $result = $this->stripeService->createPaymentIntent(
            $request->invoice_id,
            $tenant->id,
            $request->amount_to_pay,
            $request->payment_method_type
        );

        if (!$result['success']) {
            return $this->error([], $result['message'], 400);
        }

        // Return ALL fields from the service
        return $this->success([
            'client_secret'       => $result['client_secret'],
            'payment_intent_id'   => $result['payment_intent_id'],
            'payment_method_type' => $result['payment_method_type'],
            'base_amount'         => $result['base_amount'],
            'processing_fee'      => $result['processing_fee'],
            'total_charge'        => $result['total_charge'],
            'invoice'             => $result['invoice'],
            'tenant'              => $result['tenant'],
            'lease'               => $result['lease'],
            '_frontend_hint'      => $result['_frontend_hint'],
        ], 'Payment intent created successfully');

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
