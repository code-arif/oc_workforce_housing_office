<?php

namespace App\Http\Controllers\Web\Backend\Lease;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\Lease;
use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Models\Lease\LeaseDocument;
use App\Models\Lease\LeaseTemplate;
use App\Http\Controllers\Controller;
use App\Services\DynamicDocumentGenerationService;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Log;

class LeaseDocumentController extends Controller
{
    protected $documentService;

    public function __construct(DynamicDocumentGenerationService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Preview document for a specific lease
     */
    public function previewForLease($leaseId, $documentId = null)
    {
        $lease = Lease::with(['property', 'tenant.profile', 'assignments.bed.room.unit', 'documents.leaseTemplate'])
            ->findOrFail($leaseId);

        // Get the document - either specific one or first available
        if ($documentId) {
            $document = $lease->documents()->with('leaseTemplate')->findOrFail($documentId);
        } else {
            $document = $lease->documents()->with('leaseTemplate')->first();
        }

        if (!$document) {
            return redirect()->route('leases.show', $leaseId)
                ->with('error', 'No document found for this lease');
        }

        $template = $document->leaseTemplate;

        if (!$template) {
            return redirect()->route('leases.show', $leaseId)
                ->with('error', 'No template associated with this document');
        }

        // Extract actual lease data for placeholders
        $leaseData = $this->extractPlaceholderData($lease);

        // Get PDF URL
        $pdfPath = $template->pdf_path ?? $template->document_path ?? null;
        $pdfUrl = $pdfPath ? asset('storage/' . $pdfPath) : null;

        // Get placeholders and signatures from template
        $placeholders = is_array($template->placeholders) ? $template->placeholders : (json_decode($template->placeholders, true) ?? []);
        $signatures = is_array($template->signatures) ? $template->signatures : (json_decode($template->signatures, true) ?? []);

        // Return view for full page preview with PDF.js support
        return view('backend.layouts.leases.lease.document-preview', compact(
            'lease', 
            'document', 
            'template', 
            'pdfUrl',
            'placeholders',
            'signatures',
            'leaseData'
        ));
    }

    /**
     * Generate preview content with actual lease data
     */
    private function generatePreviewContent($lease, $template, $document)
    {
        // If the document already has rendered content, use it
        if ($document->rendered_content && !str_starts_with($document->rendered_content, 'lease-templates/')) {
            return $document->rendered_content;
        }

        // Otherwise, generate from template with lease data
        $placeholderData = $this->extractPlaceholderData($lease);
        
        // Get template content
        $content = $template->content ?? '';
        
        // If content is empty but we have a PDF path, return a PDF viewer
        if (empty($content) && ($template->pdf_path || $template->document_path)) {
            $pdfPath = $template->pdf_path ?? $template->document_path;
            return $this->generatePdfPreviewHtml($pdfPath, $placeholderData);
        }

        // Replace placeholders in content
        $renderedContent = $this->replacePlaceholders($content, $placeholderData);

        // Update the document with rendered content
        if ($document->rendered_content !== $renderedContent) {
            $document->update(['rendered_content' => $renderedContent]);
        }

        return $renderedContent;
    }

    /**
     * Extract placeholder data from lease
     */
    private function extractPlaceholderData($lease)
    {
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
            'unit_number' => $unit?->unit_number ?? '',
            'room_number' => $room?->room_number ?? '',
            'bed_label' => $bed?->bed_label ?? '',
            
            // Lease Info
            'lease_start_date' => $lease->start_date ? date(' d M, Y', strtotime($lease->start_date)) : '',
            'lease_end_date' => $lease->end_date ? date(' d M, Y', strtotime($lease->end_date)) : '',
            'monthly_rent' => $lease->rent_amount ? '$' . number_format($lease->rent_amount, 2) : '',
            'rent_amount' => $lease->rent_amount ? '$' . number_format($lease->rent_amount, 2) : '',
            'security_deposit' => $lease->deposit_amount ? '$' . number_format($lease->deposit_amount, 2) : '',
            'deposit_amount' => $lease->deposit_amount ? '$' . number_format($lease->deposit_amount, 2) : '',
            'payment_frequency' => ucwords(strtolower(str_replace('_', ' ', $lease->payment_frequency ?? ''))),
            'lease_term' => $this->calculateLeaseTerm($lease->start_date, $lease->end_date),
            
            // Other
            'current_date' => date(' d M, Y'),
            'admin_name' => auth()->user()?->name ?? 'Property Manager',
            'admin_email' => auth()->user()?->email ?? '',
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
        $months = $start->diffInMonths($end);
        
        if ($months == 12) return '1 Year';
        if ($months == 6) return '6 Months';
        if ($months == 1) return '1 Month';
        
        return $months . ' Months';
    }

    /**
     * Generate HTML for PDF preview with overlay data
     */
    private function generatePdfPreviewHtml($pdfPath, $placeholderData)
    {
        $pdfUrl = asset('storage/' . $pdfPath);
        
        // Build data display
        $dataHtml = '<div class="placeholder-data-overlay">';
        $dataHtml .= '<h5>Lease Data Applied:</h5>';
        $dataHtml .= '<ul>';
        foreach ($placeholderData as $key => $value) {
            if (!empty($value)) {
                $label = ucwords(str_replace('_', ' ', $key));
                $dataHtml .= "<li><strong>{$label}:</strong> {$value}</li>";
            }
        }
        $dataHtml .= '</ul></div>';

        return '
            <div class="pdf-preview-container">
                <div class="pdf-data-summary">
                    ' . $dataHtml . '
                </div>
                <div class="pdf-embed-container">
                    <iframe src="' . $pdfUrl . '#toolbar=1&navpanes=0" 
                            width="100%" 
                            height="800px" 
                            style="border: none;"></iframe>
                </div>
            </div>
        ';
    }

    public function create()
    {
        $templates = LeaseTemplate::where('is_active', true)->get();

        $tenants = Tenant::all();
        
        return view('backend.lease.lease_doc.create', compact('templates', 'tenants'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'lease_template_id' => 'required|exists:lease_templates,id',
            'tenant_id' => 'required',
            'tenant_name' => 'nullable|string',
            'tenant_email' => 'nullable|email',
            'tenant_phone' => 'nullable|string',
            'property_address' => 'nullable|string',
            'lease_start_date' => 'nullable|date',
            'lease_end_date' => 'nullable|date',
            'monthly_rent' => 'nullable|numeric',
            'security_deposit' => 'nullable|numeric',
            'lease_term' => 'nullable|string',
            'admin_name' => 'nullable|string',
            'admin_email' => 'nullable|email'
        ]);

        $template = LeaseTemplate::findOrFail($request->lease_template_id);
        
        // Prepare data for placeholder replacement
        $placeholderData = [
            'tenant_name' => $request->tenant_name,
            'tenant_email' => $request->tenant_email,
            'tenant_phone' => $request->tenant_phone,
            'property_address' => $request->property_address,
            'lease_start_date' => $request->lease_start_date,
            'lease_end_date' => $request->lease_end_date,
            'monthly_rent' => $request->monthly_rent ? '$' . number_format($request->monthly_rent, 2) : '',
            'security_deposit' => $request->security_deposit ? '$' . number_format($request->security_deposit, 2) : '',
            'lease_term' => $request->lease_term,
            'admin_name' => $request->admin_name,
            'admin_email' => $request->admin_email,
            'current_date' => date('F d, Y')
        ];

        // Replace placeholders in template content
        $renderedContent = $this->replacePlaceholders($template->content, $placeholderData);

        $document = LeaseDocument::create([
            'lease_template_id' => $template->id,
            'tenant_id' => $request->tenant_id,
            'rendered_content' => $renderedContent,
            'status' => 'draft'
        ]);

        return redirect()->route('lease_doc.lease_document_show', $document->id)
            ->with('success', 'Lease document created successfully');
    }

    private function replacePlaceholders($content, $data)
    {
        foreach ($data as $key => $value) {
            // Replace {{field_name}} with actual value
            $content = str_replace('{{' . $key . '}}', $value ?? '', $content);
            
            // Also handle placeholders in span tags
            $pattern = '/<span[^>]*data-field="' . $key . '"[^>]*>.*?<\/span>/';
            $replacement = '<span class="filled-placeholder">' . ($value ?? '') . '</span>';
            $content = preg_replace($pattern, $replacement, $content);
        }
        
        return $content;
    }

    public function show($id)
    {
        $document = LeaseDocument::with(['leaseTemplate', 'tenant'])->findOrFail($id);
        
        return view('backend.lease.lease-documents.lease_document_show', compact('document'));
    }

    public function edit($id)
    {
        $document = LeaseDocument::with(['leaseTemplate', 'tenant'])->findOrFail($id);
        
        return view('backend.lease.lease-documents.edit', compact('document'));
    }

    public function signAdmin(Request $request, $id)
    {
        $request->validate([
            'signature' => 'required|string'
        ]);

        $document = LeaseDocument::findOrFail($id);
        
        // Save signature as base64 image
        $document->update([
            'admin_signature' => $request->signature,
            'admin_signed_at' => now(),
            'status' => $document->tenant_signature ? 'signed' : 'pending_signatures'
        ]);

        // If tenant has also signed, mark lease as ACTIVE
        if ($document->tenant_signature) {
            $document->lease->update(['status' => 'ACTIVE']);
        } else {
            $document->lease->update(['status' => 'PENDING_TENANT_SIGN']);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Admin signature saved successfully'
        ]);
    }

    public function signTenant(Request $request, $id)
    {
        $request->validate([
            'signature' => 'required|string'
        ]);

        $document = LeaseDocument::findOrFail($id);
        
        $document->update([
            'tenant_signature' => $request->signature,
            'tenant_signed_at' => now(),
            'status' => $document->admin_signature ? 'signed' : 'pending_signatures'
        ]);

        if ($document->admin_signature) {
            $document->lease->update(['status' => 'ACTIVE']);
        } else {
            $document->update(['status' => 'pending_signatures']);
            $document->lease->update(['status' => 'PENDING_ADMIN_SIGN']);
        }
        

        return response()->json([
            'success' => true,
            'message' => 'Tenant signature saved successfully'
        ]);
    }

    /**
     * Download PDF with dynamic data overlayed on the original template
     */
    public function downloadPdf($id)
    {
        $document = LeaseDocument::with(['leaseTemplate', 'tenant', 'lease.property', 'lease.tenant.profile', 'lease.assignments.bed.room.unit'])->findOrFail($id);
        
        $template = $document->leaseTemplate;
        
        if (!$template) {
            return redirect()->back()->with('error', 'No template associated with this document');
        }

        // Get the PDF path from template
        $pdfPath = $template->pdf_path ?? $template->document_path ?? null;
        
        if (!$pdfPath) {
            // Fallback to old method if no PDF template exists
            return $this->downloadPdfFromContent($document);
        }

        $fullPdfPath = storage_path('app/public/' . $pdfPath);
        
        if (!file_exists($fullPdfPath)) {
            Log::error("PDF template not found: {$fullPdfPath}");
            return redirect()->back()->with('error', 'PDF template file not found');
        }

        // Get placeholders and signatures from template
        $placeholders = is_array($template->placeholders) ? $template->placeholders : (json_decode($template->placeholders, true) ?? []);
        $signatures = is_array($template->signatures) ? $template->signatures : (json_decode($template->signatures, true) ?? []);

        // Get actual lease data
        $lease = $document->lease;
        $leaseData = $lease ? $this->extractPlaceholderData($lease) : [];

        try {
            // Generate PDF with overlays using FPDI
            $outputPath = $this->generatePdfWithOverlays($fullPdfPath, $placeholders, $signatures, $leaseData, $document);
            
            $filename = 'lease_document_' . $document->id . '_' . date('Y-m-d') . '.pdf';
            
            return response()->download($outputPath, $filename)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error("Failed to generate PDF with overlays: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
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
        
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            // Import the page from source PDF
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);
            
            // Add page with same size as template
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
            
            // PDF.js at scale 1.0 renders 1 PDF point = 1 pixel
            // PDF uses 72 points per inch, FPDI uses mm
            // Conversion: 1 point = 25.4/72 mm = 0.352778 mm
            $pointToMm = 25.4 / 72;
            
            // Filter and add placeholders for this page
            $pagePlaceholders = array_filter($placeholders, fn($p) => (int)($p['page'] ?? 1) === $pageNo);
            
            foreach ($pagePlaceholders as $placeholder) {
                $fieldName = $placeholder['field'] ?? '';
                $value = $leaseData[$fieldName] ?? '';
                
                if (empty($value)) continue;
                
                // Convert PDF point coordinates to mm
                $x = (float)($placeholder['x'] ?? 0) * $pointToMm;
                $y = (float)($placeholder['y'] ?? 0) * $pointToMm;
                $width = (float)($placeholder['width'] ?? 150) * $pointToMm;
                $height = (float)($placeholder['height'] ?? 20) * $pointToMm;
                
                // Set font - use a reasonable size
                $fontSize = isset($placeholder['fontSize']) ? (float)$placeholder['fontSize'] : 10;
                $pdf->SetFont('Helvetica', '', $fontSize);
                $pdf->SetTextColor(0, 0, 0);
                
                // Position and write text
                $pdf->SetXY($x, $y);
                
                // Use Cell for better text positioning
                $pdf->Cell($width, $height, $value, 0, 0, 'L');
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
                    // Save base64 to temp file
                    $imgData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureData));
                    $tempImgPath = $outputDir . '/sig_temp_' . uniqid() . '.png';
                    file_put_contents($tempImgPath, $imgData);
                    
                    try {
                        // Add signature image
                        $pdf->Image($tempImgPath, $x, $y, $width, $height, 'PNG');
                    } catch (\Exception $e) {
                        Log::warning("Failed to add signature image: " . $e->getMessage());
                    }
                    
                    // Clean up temp file
                    @unlink($tempImgPath);
                }
                
                // Add signed date below signature if available
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

    /**
     * Fallback method - download PDF from rendered HTML content (legacy)
     */
    private function downloadPdfFromContent($document)
    {
        $content = $document->rendered_content ?? '';
        
        if (empty($content)) {
            return redirect()->back()->with('error', 'No content available for this document');
        }
        
        // Replace signature placeholders with actual signatures
        $content = $this->insertSignatures($content, $document);
        
        // Generate PDF using Dompdf
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($this->wrapHtmlForPdf($content));
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        
        $filename = 'lease_document_' . $document->id . '_' . date('Y-m-d') . '.pdf';
        
        return $dompdf->stream($filename);
    }

    private function insertSignatures($content, $document)
    {
        // Replace admin signature box
        if ($document->admin_signature) {
            $adminSigHtml = '<div class="signature-filled"><img src="' . $document->admin_signature . '" style="max-width: 200px;"><br><small>Signed on: ' . $document->admin_signed_at->format('F d, Y') . '</small></div>';
            $content = preg_replace('/<div class="signature-box admin-signature"[^>]*>.*?<\/div>/s', $adminSigHtml, $content);
        }
        
        // Replace tenant signature box
        if ($document->tenant_signature) {
            $tenantSigHtml = '<div class="signature-filled"><img src="' . $document->tenant_signature . '" style="max-width: 200px;"><br><small>Signed on: ' . $document->tenant_signed_at->format('F d, Y') . '</small></div>';
            $content = preg_replace('/<div class="signature-box tenant-signature"[^>]*>.*?<\/div>/s', $tenantSigHtml, $content);
        }
        
        return $content;
    }

    private function wrapHtmlForPdf($content)
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <style>
                body { font-family: Arial, sans-serif; font-size: 12pt; line-height: 1.6; }
                .filled-placeholder { font-weight: bold; color: #000; }
                .signature-filled { margin: 20px 0; text-align: center; }
                .signature-filled img { border-bottom: 2px solid #000; }
            </style>
        </head>
        <body>
            ' . $content . '
        </body>
        </html>';
    }
}