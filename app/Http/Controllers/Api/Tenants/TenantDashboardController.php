<?php

namespace App\Http\Controllers\Api\Tenants;

use Exception;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\TenantDocument;
use App\Http\Controllers\Controller;
use App\Services\Tenants\TenantLeaseService;
use Illuminate\Support\Facades\Validator;

class TenantDashboardController extends Controller
{
    use ApiResponse;

    /**
     * Inject service class
     */
    protected $leaseService;

    public function __construct(TenantLeaseService $leaseService)
    {
        $this->leaseService = $leaseService;
    }


    /**
     * Tenant Dashboard Stats and data
     */
    public function dashboard(Request $request)
    {
        try {
            $tenant = $request->user();
            $tenant->load('profile');

            // Get dashboard data from service
            $dashboardData = $this->leaseService->getDashboardData($tenant->id);

            return $this->success([
                'tenant' => [
                    'id' => $tenant->id,
                    'email' => $tenant->email,
                    'status' => $tenant->status,
                    'move_in_date' => $tenant->move_in_date,
                    'profile' => $tenant->profile,
                ],
                'statistics' => [
                    'documents_count' => $tenant->documents()->count(),
                    'emergency_contacts_count' => $tenant->emergencyContacts()->count(),
                    'active_leases_count' => $dashboardData['statistics']['active_leases_count'],
                    'pending_leases_count' => $dashboardData['statistics']['pending_leases_count'],
                    'total_unpaid_amount' => $dashboardData['statistics']['total_unpaid_amount'],
                ],
                'unsigned_leases' => $dashboardData['unsigned_leases'],
                'signed_leases' => $dashboardData['signed_leases'],
                'invoices' => $dashboardData['invoices'],
                'recent_payments' => $dashboardData['recent_payments'],
            ], 'Dashboard data retrieved successfully');
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Tenant documents
     */
    public function documents(Request $request)
    {
        $tenant = $request->user();
        $documents = $tenant->documents;

        return $this->success([
            'documents' => $documents
        ], 'Tenant documents', 200);
    }


    /**
     * Documents upload
     */
    public function uploadDocument(Request $request)
    {
        $tenant = $request->user();

        $validator = Validator::make($request->all(), [
            'document_type' => 'required|in:passport,visa,id_front,id_back,other',
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('file');
            $path = $file->store("tenant_documents/{$tenant->id}", 'private');

            $document = TenantDocument::create([
                'tenant_id' => $tenant->id,
                'document_type' => $request->document_type,
                'file_path' => $path,
                'file_original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'data' => [
                    'document' => $document
                ]
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all tenant leases
     */
    public function leases(Request $request)
    {
        try {
            $tenant = $request->user();
            $leases = $this->leaseService->getTenantLeases($tenant->id);

            return $this->success([
                'leases' => $leases
            ], 'Leases retrieved successfully');
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Get specific lease details
     */
    public function leaseDetails(Request $request, $leaseId)
    {
        try {
            $tenant = $request->user();
            $lease = $this->leaseService->getLeaseDetails($leaseId, $tenant->id);

            if (!$lease) {
                return $this->error([], 'Lease not found or unauthorized', 404);
            }

            return $this->success([
                'lease' => $lease
            ], 'Lease details retrieved successfully');
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Get all tenant invoices
     */
    public function invoices(Request $request)
    {
        try {
            $tenant = $request->user();
            $status = $request->query('status'); // optional filter

            $invoices = $this->leaseService->getTenantInvoices($tenant->id, $status);

            return $this->success([
                'invoices' => $invoices
            ], 'Invoices retrieved successfully');
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Get specific invoice details
     */
    public function invoiceDetails(Request $request, $invoiceId)
    {
        try {
            $tenant = $request->user();
            $invoice = $this->leaseService->getInvoiceDetails($invoiceId, $tenant->id);

            if (!$invoice) {
                return $this->error([], 'Invoice not found or unauthorized', 404);
            }

            return $this->success([
                'invoice' => $invoice
            ], 'Invoice details retrieved successfully');
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Get payment history
     */
    public function paymentHistory(Request $request)
    {
        try {
            $tenant = $request->user();
            $payments = $this->leaseService->getPaymentHistory($tenant->id);

            return $this->success([
                'payments' => $payments
            ], 'Payment history retrieved successfully');
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Get transaction history
     */
    public function transactions(Request $request)
    {
        try {
            $tenant = $request->user();
            $transactions = $this->leaseService->getTransactions($tenant->id);

            return $this->success([
                'transactions' => $transactions
            ], 'Transactions retrieved successfully');
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }
}
