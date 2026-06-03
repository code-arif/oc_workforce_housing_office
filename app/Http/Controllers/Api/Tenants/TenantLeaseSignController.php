<?php

namespace App\Http\Controllers\Api\Tenants;

use Exception;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Services\Tenants\LeaseSigningService;
use App\Http\Resources\Lease\LeaseDocumentResource;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Log;

class TenantLeaseSignController extends Controller
{
    use ApiResponse;

    protected $signingService;

    public function __construct(LeaseSigningService $signingService)
    {
        $this->signingService = $signingService;
    }

    /**
     * Get lease document for signing
     */
    public function getLeaseDocument(Request $request, $leaseId)
    {
        try {
            $tenant = $request->user();

            $document = $this->signingService->getLeaseDocument($leaseId, $tenant->id);

            if (!$document) {
                return $this->error([], 'Lease document not found or unauthorized', 404);
            }

            return $this->success([
                'document' => new LeaseDocumentResource($document)
            ], 'Lease document retrieved successfully');
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Sign lease document
     */
    public function signLease(Request $request, $leaseId)
    {
        $validator = Validator::make($request->all(), [
            'signature' => 'required|string', // Base64 signature or digital signature
            'signature_type' => 'nullable|in:digital,electronic,wet',
            'ip_address' => 'nullable|ip',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $tenant = $request->user();

            $result = $this->signingService->signLease(
                $leaseId,
                $tenant->id,
                $request->signature,
                $request->signature_type ?? 'digital',
                $request->ip_address ?? $request->ip()
            );

            if (!$result['success']) {
                return $this->error([], $result['message'], 400);
            }

            return $this->success([
                'lease' => $result['lease'],
                'document' => $result['document']
            ], 'Lease signed successfully');
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }


    /**
     * Sign lease document V2
     */
    public function signLeaseV2(Request $request, $leaseId)
    {
        $validator = Validator::make($request->all(), [
            'signature' => 'required|string', // Base64 signature or digital signature
            'signature_type' => 'nullable|in:digital,electronic,wet',
            'ip_address' => 'nullable|ip',
            'custom_fields' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $tenant = $request->user();

            if ($request->has('custom_fields')) {
                $document = $this->signingService->getLeaseDocument($leaseId, $tenant->id);
                if ($document) {
                    $existingCustomFields = is_array($document->custom_fields) ? $document->custom_fields : [];
                    $incomingCustomFields = is_array($request->custom_fields) ? $this->normalizeCustomFieldsInput($request->custom_fields) : [];
                    $mergedCustomFields = array_replace_recursive($existingCustomFields, $incomingCustomFields);

                    $document->update([
                        'custom_fields' => $mergedCustomFields
                    ]);
                }
            }

            $result = $this->signingService->signLease(
                $leaseId,
                $tenant->id,
                $request->signature,
                $request->signature_type ?? 'digital',
                $request->ip_address ?? $request->ip()
            );

            if (!$result['success']) {
                return $this->error([], $result['message'], 400);
            }

            return $this->success([
                'lease' => $result['lease'],
                'document' => $result['document']
            ], 'Lease signed successfully');
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Check if lease can be signed
     */
    public function checkSigningEligibility(Request $request, $leaseId)
    {
        try {
            $tenant = $request->user();

            $eligibility = $this->signingService->checkSigningEligibility($leaseId, $tenant->id);

            return $this->success([
                'can_sign' => $eligibility['can_sign'],
                'reason' => $eligibility['reason'] ?? null,
                'lease_status' => $eligibility['lease_status'],
                'document_status' => $eligibility['document_status'] ?? null,
            ], 'Eligibility checked successfully');
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Get lease document PDF for preview
     */
    public function previewDocument(Request $request, $leaseId)
    {
        try {
            $tenant = $request->user();

            $document = $this->signingService->getLeaseDocument($leaseId, $tenant->id);

            if (!$document) {
                return $this->error([], 'Document not found', 404);
            }

            $template = $document->leaseTemplate ?? $document->template;
            $pdfPath = $template?->pdf_path ?? $template?->document_path ?? null;

            // Get placeholders and signatures from template
            $placeholders = [];
            $signatures = [];

            if ($template) {
                $placeholders = is_array($template->placeholders)
                    ? $template->placeholders
                    : (json_decode($template->placeholders, true) ?? []);
                $signatures = is_array($template->signatures)
                    ? $template->signatures
                    : (json_decode($template->signatures, true) ?? []);
            }

            // Get lease data for placeholders
            $leaseData = $this->extractPlaceholderData($document->lease);

            return $this->success([
                'document_id' => $document->id,
                'pdf_url' => $pdfPath ? asset('storage/' . $pdfPath) : null,
                'total_pages' => $template?->total_pages ?? 1,
                'placeholders' => $placeholders,
                'signatures' => $signatures,
                'lease_data' => $leaseData,
                'custom_fields' => $document->custom_fields ?? [],
                'is_tenant_signed' => (bool) $document->tenant_signed_at,
                'is_admin_signed' => (bool) $document->admin_signed_at,
                'tenant_signature' => $document->tenant_signature,
                'admin_signature' => $document->admin_signature,
                'can_sign' => !$document->tenant_signed_at && $document->lease?->status === 'PENDING_TENANT_SIGN',
            ], 'Document preview data retrieved successfully');
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Get manual sign fields (Custom input fields and signature status)
     */
    public function getManualSignFields(Request $request, $leaseId)
    {
        try {
            $tenant = $request->user();

            $document = $this->signingService->getLeaseDocument($leaseId, $tenant->id);

            if (!$document) {
                return $this->error([], 'Document not found', 404);
            }

            $template = $document->leaseTemplate ?? $document->template;

            $placeholders = [];
            if ($template) {
                $placeholders = is_array($template->placeholders)
                    ? $template->placeholders
                    : (json_decode($template->placeholders, true) ?? []);
            }

            $customInputFields = [];
            $customTextCounter = 1;

            foreach ($placeholders as $placeholder) {
                if (isset($placeholder['type']) && $placeholder['type'] === 'text_input') {
                    $fieldId = $placeholder['id'] ?? $placeholder['field'] ?? '';
                    if ($fieldId) {
                        $label = $placeholder['label'] ?? 'Custom Field';

                        // If it's the default custom text label, append a number to make it unique and identifiable
                        if ($label === 'Custom Text Input' || $label === 'Custom Field') {
                            $label = $label . ' ' . $customTextCounter;
                            $customTextCounter++;
                        }

                        $customInputFields[] = [
                            'id' => $fieldId,
                            'name' => $placeholder['field'] ?? '',
                            'label' => $label,
                            'page' => $placeholder['page'] ?? 1,
                            'value' => $document->custom_fields[$fieldId] ?? '',
                            'width' => $placeholder['width'] ?? 150,
                            'height' => $placeholder['height'] ?? 20,
                        ];
                    }
                }
            }

            return $this->success([
                'document_id' => $document->id,
                'custom_fields' => $customInputFields,
                'is_tenant_signed' => (bool) $document->tenant_signed_at,
                'is_admin_signed' => (bool) $document->admin_signed_at,
                'tenant_signature' => $document->tenant_signature,
                'can_sign' => !$document->tenant_signed_at && $document->lease?->status === 'PENDING_TENANT_SIGN',
            ], 'Manual sign fields retrieved successfully');
        } catch (Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Download signed lease document as PDF
     */
    public function downloadDocument(Request $request, $leaseId)
    {
        try {
            $tenant = $request->user();

            $document = $this->signingService->getLeaseDocument($leaseId, $tenant->id);

            if (!$document) {
                return $this->error([], 'Document not found or unauthorized', 404);
            }

            $template = $document->leaseTemplate ?? $document->template;
            $pdfPath = $template?->pdf_path ?? $template?->document_path ?? null;

            if (!$pdfPath) {
                return $this->error([], 'PDF template not available', 404);
            }

            $fullPdfPath = storage_path('app/public/' . $pdfPath);

            if (!file_exists($fullPdfPath)) {
                return $this->error([], 'PDF file not found', 404);
            }

            // Get placeholders and signatures from template
            $placeholders = is_array($template->placeholders)
                ? $template->placeholders
                : (json_decode($template->placeholders, true) ?? []);
            $signatures = is_array($template->signatures)
                ? $template->signatures
                : (json_decode($template->signatures, true) ?? []);

            // Get lease data for placeholders
            $leaseData = $this->extractPlaceholderData($document->lease);

            // Generate PDF with overlays
            $outputPath = $this->generatePdfWithOverlays(
                $fullPdfPath,
                $placeholders,
                $signatures,
                $leaseData,
                $document
            );

            $filename = 'lease_document_' . $document->id . '_' . date('Y-m-d') . '.pdf';

            return response()->download($outputPath, $filename)->deleteFileAfterSend(true);
        } catch (Exception $e) {
            Log::error('Failed to download lease document: ' . $e->getMessage());
            return $this->error([], 'Failed to generate PDF: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Update custom text fields for the lease document
     */
    public function updateCustomFields(Request $request, $leaseId)
    {
        $validator = Validator::make($request->all(), [
            'custom_fields' => 'required|array',
            // 'document_id' => 'nullable|integer|exists:lease_documents,id'
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        try {
            $tenant = $request->user();

            // Get the lease document
            $document = $this->signingService->getLeaseDocument($leaseId, $tenant->id);

            if (!$document) {
                return $this->error([], 'Lease document not found or unauthorized', 404);
            }

            // If a specific document ID was provided, verify it matches
            // if ($request->has('document_id') && $request->document_id != $document->id) {
            //     return $this->error([], 'Document ID mismatch', 400);
            // }

            // Update custom fields
            $existingCustomFields = is_array($document->custom_fields) ? $document->custom_fields : [];
            $incomingCustomFields = is_array($request->custom_fields) ? $this->normalizeCustomFieldsInput($request->custom_fields) : [];
            $mergedCustomFields = array_replace_recursive($existingCustomFields, $incomingCustomFields);

            $document->update([
                'custom_fields' => $mergedCustomFields
            ]);

            return $this->success([
                // 'document_id' => $document->id,
                'custom_fields' => $document->custom_fields
            ], 'Custom fields saved successfully');
        } catch (Exception $e) {
            Log::error('Failed to update custom fields: ' . $e->getMessage());
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Normalize incoming custom field payloads for tenant signing.
     *
     * Accepts either a keyed map of field IDs => values or an array of objects
     * like [{ id, name, value }, ...]. Returns a flat map keyed by field id.
     */
    private function normalizeCustomFieldsInput(array $customFields): array
    {
        // If payload is already a keyed map, return it as-is.
        if (array_values($customFields) !== $customFields) {
            return $customFields;
        }

        $normalized = [];
        foreach ($customFields as $item) {
            if (!is_array($item)) {
                continue;
            }

            $fieldId = $item['id'] ?? $item['field'] ?? $item['name'] ?? null;
            $value = $item['value'] ?? $item['answer'] ?? null;
            if ($fieldId !== null) {
                $normalized[$fieldId] = $value;
            }
        }

        return $normalized;
    }

    /**
     * Extract placeholder data from lease
     */
    private function extractPlaceholderData($lease)
    {
        if (!$lease) return [];

        $lease->load(['tenant.profile', 'property', 'assignments.bed.room.unit']);

        $tenant = $lease->tenant;
        $profile = $tenant?->profile;
        $property = $lease->property;
        $assignment = $lease->assignments->where('is_current', true)->first();
        $bed = $assignment?->bed;
        $room = $bed?->room;
        $unit = $room?->unit;

        $tenantName = $profile ?
            trim($profile->first_name . ' ' . ($profile->middle_name ? $profile->middle_name . ' ' : '') . ($profile->last_name ?? '')) :
            'N/A';

        return [
            // Tenant Info
            'tenant_name' => $tenantName,
            'tenant_first_name' => $profile?->first_name ?? '',
            'tenant_last_name' => $profile?->last_name ?? '',
            'tenant_email' => $tenant?->email ?? '',
            'tenant_phone' => $profile?->phone ?? '',
            'tenant_address' => $profile?->address ?? '',
            'tenant_city' => $profile?->city ?? '',
            'tenant_state' => $profile?->state ?? '',
            'tenant_zip' => $profile?->zip ?? '',

            // Property Info
            'property_name' => $property?->name ?? '',
            'property_address' => $property?->address ?? '',
            'property_city' => $property?->city ?? '',
            'property_state' => $property?->state ?? '',
            'property_zip' => $property?->zip ?? '',
            'property_type' => $property?->property_type ?? '',
            'apartment_unit_number' => $unit?->unit_number ?? '',
            'unit_assigned_at_checkin' => 'Unit will be assigned at check-in',
            'unit_number' => $unit?->unit_number ?? '',
            'room_number' => $room?->room_number ?? '',
            'bed_label' => $bed?->bed_label ?? '',

            // Lease Info
            'lease_start_date' => $lease->start_date ? date('M d, Y', strtotime($lease->start_date)) : '',
            'lease_end_date' => $lease->end_date ? date('M d, Y', strtotime($lease->end_date)) : '',
            'monthly_rent' => $lease->rent_amount ? '$' . number_format($lease->rent_amount, 2) : '',
            'rent_amount' => $lease->rent_amount ? '$' . number_format($lease->rent_amount, 2) : '',
            'total_rent' => $this->calculateTotalRent($lease),
            'security_deposit' => $lease->deposit_amount ? '$' . number_format($lease->deposit_amount, 2) : '',
            'deposit_amount' => $lease->deposit_amount ? '$' . number_format($lease->deposit_amount, 2) : '',
            'payment_frequency' => ucwords(strtolower(str_replace('_', ' ', $lease->payment_frequency ?? ''))),
            'lease_term' => $this->calculateLeaseTerm($lease->start_date, $lease->end_date),

            // Other
            'current_date' => date('M d, Y'),
        ];
    }

    /**
     * Calculate lease term in months
     */
    private function calculateLeaseTerm($startDate, $endDate)
    {
        if (!$startDate || !$endDate) return 'Month-to-Month';

        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        $months = (int) round($start->diffInMonths($end));

        if ($months == 12) return '1 Year';
        if ($months == 6) return '6 Months';
        if ($months == 1) return '1 Month';

        return $months . ' Months';
    }

    /**
     * Calculate total rent for the lease period
     */
    private function calculateTotalRent($lease)
    {
        if (!$lease->rent_amount) return '';

        if (!$lease->start_date || !$lease->end_date) {
            return '$' . number_format($lease->rent_amount, 2);
        }

        $start = \Carbon\Carbon::parse($lease->start_date);
        $end = \Carbon\Carbon::parse($lease->end_date);
        $months = (int) round($start->diffInMonths($end));

        if ($months < 1) $months = 1;

        $totalRent = $lease->rent_amount * $months;
        return '$' . number_format($totalRent, 2);
    }

    /**
     * Generate PDF with placeholders and signatures overlayed using FPDI
     */
    private function generatePdfWithOverlays($pdfPath, $placeholders, $signatures, $leaseData, $document)
    {
        $pdf = new Fpdi();

        // Ensure output directory exists
        $outputDir = storage_path('app/public/generated-leases');
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $outputPath = $outputDir . '/lease_' . $document->id . '_' . time() . '.pdf';

        // Get page count from source PDF
        $pageCount = $pdf->setSourceFile($pdfPath);

        // Get custom fields data from document
        $customFields = $document->custom_fields ?? [];

        // PDF.js at scale 1.0 renders 1 PDF point = 1 pixel
        // PDF uses 72 points per inch, FPDI uses mm
        // Conversion: 1 point = 25.4/72 mm = 0.352778 mm
        $pointToMm = 25.4 / 72;

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            // Import the page from source PDF
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);

            // Add page with same size as template
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);

            // Filter and add placeholders for this page
            $pagePlaceholders = array_filter($placeholders, fn($p) => (int)($p['page'] ?? 1) === $pageNo);

            foreach ($pagePlaceholders as $placeholder) {
                $fieldName = $placeholder['field'] ?? '';
                $fieldType = $placeholder['type'] ?? 'text';
                $fieldId = $placeholder['id'] ?? '';

                // Determine value based on field type
                if ($fieldType === 'text_input') {
                    // For custom text input fields, get value from custom_fields
                    $value = $customFields[$fieldId] ?? '';
                } else {
                    // For standard placeholders, get value from lease data
                    $value = $leaseData[$fieldName] ?? '';
                }

                if (empty($value)) continue;

                // Convert PDF point coordinates to mm
                $x = (float)($placeholder['x'] ?? 0) * $pointToMm;
                $y = (float)($placeholder['y'] ?? 0) * $pointToMm;
                $width = (float)($placeholder['width'] ?? 150) * $pointToMm;
                $height = (float)($placeholder['height'] ?? 20) * $pointToMm;

                // Set font
                $fontSize = isset($placeholder['fontSize']) ? (float)$placeholder['fontSize'] : 10;
                $pdf->SetFont('Helvetica', '', $fontSize);
                $pdf->SetTextColor(0, 0, 0);

                // Position and write text
                $pdf->SetXY($x, $y);

                // Use MultiCell for long text to enable wrapping
                if (strlen($value) > 50) {
                    $pdf->MultiCell($width, 5, $value, 0, 'L');
                } else {
                    $pdf->Cell($width, $height, $value, 0, 0, 'L');
                }
            }

            // Filter and add signatures for this page
            $pageSignatures = array_filter($signatures, fn($s) => (int)($s['page'] ?? 1) === $pageNo);

            foreach ($pageSignatures as $signature) {
                $label = $signature['label'] ?? 'Signature';
                $isTenantSig = stripos($label, 'tenant') !== false;

                // Check if signature exists
                $signatureData = $isTenantSig ? $document->tenant_signature : $document->admin_signature;
                $signedAt = $isTenantSig ? $document->tenant_signed_at : $document->admin_signed_at;

                if (empty($signatureData)) continue;

                // Convert PDF point coordinates to mm
                $x = (float)($signature['x'] ?? 0) * $pointToMm;
                $y = (float)($signature['y'] ?? 0) * $pointToMm;
                $width = (float)($signature['width'] ?? 200) * $pointToMm;
                $height = (float)($signature['height'] ?? 60) * $pointToMm;

                // Handle base64 signature image
                if (strpos($signatureData, 'data:image') === 0) {
                    $imgData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureData));
                    $tempImgPath = $outputDir . '/sig_temp_' . uniqid() . '.png';
                    file_put_contents($tempImgPath, $imgData);

                    try {
                        $pdf->Image($tempImgPath, $x, $y, $width, $height, 'PNG');
                    } catch (Exception $e) {
                        Log::warning("Failed to add signature image: " . $e->getMessage());
                    }

                    @unlink($tempImgPath);
                } elseif (file_exists(storage_path('app/public/' . $signatureData))) {
                    // Signature stored as file path
                    try {
                        $pdf->Image(storage_path('app/public/' . $signatureData), $x, $y, $width, $height);
                    } catch (Exception $e) {
                        Log::warning("Failed to add signature image from path: " . $e->getMessage());
                    }
                }

                // Add signed date below signature
                if ($signedAt) {
                    $pdf->SetFont('Helvetica', '', 8);
                    $pdf->SetTextColor(100, 100, 100);
                    $pdf->SetXY($x, $y + $height + 1);
                    $pdf->Cell($width, 5, 'Signed: ' . $signedAt->format('M d, Y'), 0, 0, 'C');
                }
            }
        }

        // Save the PDF to file
        $pdf->Output($outputPath, 'F');

        return $outputPath;
    }
}
