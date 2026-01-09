<?php

namespace App\Services;

use App\Models\LeaseTemplate;
use App\Models\Lease;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mpdf\Mpdf;
use Exception;

class DynamicDocumentGenerationService
{
    protected LeaseTemplateFieldMappingService $mappingService;

    public function __construct(LeaseTemplateFieldMappingService $mappingService)
    {
        $this->mappingService = $mappingService;
    }

    /**
     * Generate a document for a lease using a template
     *
     * @param int $leaseId
     * @param int $templateId
     * @param bool $generatePDF
     * @return array
     * @throws Exception
     */
    public function generateDocumentForLease(
        int $leaseId,
        int $templateId,
        bool $generatePDF = false
    ): array {
        $template = LeaseTemplate::findOrFail($templateId);
        $lease = Lease::findOrFail($leaseId);

        // Extract data needed for template
        $data = $this->extractDataForTemplate($lease, $template);

        // Validate required fields are present
        $validation = $this->validateRequiredFields($template, $data);
        if (!$validation['is_valid']) {
            throw new Exception('Missing required data: ' . implode(', ', $validation['missing']));
        }

        // Populate template with data
        $htmlContent = $this->populateTemplate($template, $data);

        // Generate PDF if requested
        $pdfPath = null;
        if ($generatePDF) {
            $pdfPath = $this->convertToPDF($htmlContent, $lease->id, $templateId);
        }

        return [
            'html_content' => $htmlContent,
            'pdf_path' => $pdfPath,
            'data_used' => [
                'has_tenant_data' => !empty($data['tenant']),
                'has_property_data' => !empty($data['property']),
                'has_lease_data' => !empty($data['lease']),
            ],
            'generated_at' => now(),
        ];
    }

    /**
     * Populate template with data by replacing placeholders
     *
     * @param LeaseTemplate $template
     * @param array $data
     * @return string
     */
    public function populateTemplate(LeaseTemplate $template, array $data): string
    {
        $content = $template->content;
        $mappings = $template->fieldMappings;

        foreach ($mappings as $mapping) {
            $placeholder = $mapping->field_placeholder;
            $source = $mapping->tenant_data_source;

            $value = $this->resolveDataValue($source, $data);

            // Format value based on field type
            $formattedValue = $this->formatValue($value, $mapping->field_type);

            // Replace placeholder
            $content = str_replace($placeholder, $formattedValue, $content);
        }

        // Replace any remaining unmapped placeholders with empty string
        $content = preg_replace('/\{\{[A-Z_]+(\|.*?)?\}\}/', '', $content);

        return $content;
    }

    /**
     * Format a value based on field type
     *
     * @param mixed $value
     * @param string $fieldType
     * @return string
     */
    private function formatValue($value, string $fieldType): string
    {
        if ($value === null) {
            return '';
        }

        return match ($fieldType) {
            'DATE' => $this->formatDate($value),
            'AMOUNT' => $this->formatAmount($value),
            'TEXT' => (string) $value,
            'SIGNATURE' => '', // Signatures filled during signing
            default => (string) $value,
        };
    }

    /**
     * Format date value
     */
    private function formatDate($value): string
    {
        try {
            return \Carbon\Carbon::parse($value)->format('m/d/Y');
        } catch (\Exception) {
            return (string) $value;
        }
    }

    /**
     * Format amount as currency
     */
    private function formatAmount($value): string
    {
        if (is_numeric($value)) {
            return '$' . number_format($value, 2);
        }
        return (string) $value;
    }

    /**
     * Extract data needed for template population
     *
     * @param Lease $lease
     * @param LeaseTemplate $template
     * @return array
     */
    public function extractDataForTemplate(Lease $lease, LeaseTemplate $template): array
    {
        $tenant = $lease->tenant;
        $property = $lease->property;
        $leaseData = $lease;

        return [
            'tenant' => [
                'full_name' => $tenant?->full_name ?? '',
                'first_name' => $tenant?->first_name ?? '',
                'last_name' => $tenant?->last_name ?? '',
                'email' => $tenant?->email ?? '',
                'phone' => $tenant?->phone_number ?? '',
                'address' => $tenant?->address ?? '',
                'city' => $tenant?->city ?? '',
                'state' => $tenant?->state ?? '',
                'zip' => $tenant?->zip ?? '',
                'country' => $tenant?->country ?? '',
            ],
            'property' => [
                'name' => $property?->title ?? '',
                'address' => $property?->address ?? '',
                'city' => $property?->city ?? '',
                'state' => $property?->state ?? '',
                'zip' => $property?->zip ?? '',
                'country' => $property?->country ?? '',
                'unit_number' => $property?->unit_number ?? '',
            ],
            'lease' => [
                'start_date' => $leaseData?->start_date ?? '',
                'end_date' => $leaseData?->end_date ?? '',
                'rent_amount' => $leaseData?->rent_amount ?? '',
                'deposit_amount' => $leaseData?->security_deposit ?? '',
                'payment_frequency' => $leaseData?->payment_frequency ?? 'Monthly',
                'status' => $leaseData?->status ?? '',
            ],
        ];
    }

    /**
     * Resolve a value from data using dot notation
     *
     * @param string $source e.g., "tenant.full_name"
     * @param array $data
     * @return mixed
     */
    public function resolveDataValue(string $source, array $data): mixed
    {
        $parts = explode('.', $source);
        $value = $data;

        foreach ($parts as $part) {
            if (is_array($value) && isset($value[$part])) {
                $value = $value[$part];
            } else {
                return null;
            }
        }

        return $value;
    }

    /**
     * Validate required fields are present
     *
     * @param LeaseTemplate $template
     * @param array $data
     * @return array
     */
    public function validateRequiredFields(LeaseTemplate $template, array $data): array
    {
        $missing = [];
        $mappings = $template->fieldMappings;

        foreach ($mappings as $mapping) {
            if (!$mapping->is_required) {
                continue;
            }

            $value = $this->resolveDataValue($mapping->tenant_data_source, $data);

            if ($value === null || $value === '') {
                $missing[] = $mapping->field_label;
            }
        }

        return [
            'is_valid' => count($missing) === 0,
            'missing' => $missing,
        ];
    }

    /**
     * Convert HTML to PDF
     *
     * @param string $htmlContent
     * @param int $leaseId
     * @param int $templateId
     * @return string Path to generated PDF
     * @throws Exception
     */
    public function convertToPDF(string $htmlContent, int $leaseId, int $templateId): string
    {
        try {
            // Ensure temp directory exists
            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $mpdf = new Mpdf([
                'tempDir' => $tempDir,
                'format' => 'A4',
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
            ]);

            $mpdf->WriteHTML($htmlContent);

            $fileName = "lease_{$leaseId}_template_{$templateId}_" . time() . '.pdf';
            $path = "lease-documents/{$leaseId}/" . $fileName;

            $tempPath = $tempDir . '/' . $fileName;
            $mpdf->Output($tempPath, 'F');

            // Ensure directory exists in storage
            Storage::makeDirectory("lease-documents/{$leaseId}", recursive: true);
            
            Storage::put($path, file_get_contents($tempPath));
            @unlink($tempPath);

            return $path;
        } catch (Exception $e) {
            throw new Exception('Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Get document generation status
     */
    public function getGenerationStatus(int $leaseId, int $templateId): array
    {
        try {
            $template = LeaseTemplate::findOrFail($templateId);
            $lease = Lease::findOrFail($leaseId);

            // Check if document already generated
            $document = $lease->documents()
                ->where('template_id', $templateId)
                ->first();

            return [
                'template_ready' => $template->fieldMappings()->count() > 0,
                'document_exists' => $document !== null,
                'template_mappings_count' => $template->fieldMappings()->count(),
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }
}
