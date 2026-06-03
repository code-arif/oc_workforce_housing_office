<?php

namespace App\Http\Controllers\Web\Backend\Lease;

use Illuminate\Http\Request;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Models\Lease\LeaseTemplate;

class LeaseTemplateController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:lease.template.list')->only(['index', 'show']);
        $this->middleware('permission:lease.template.create')->only(['create', 'store']);
        $this->middleware('permission:lease.template.edit')->only(['edit', 'update', 'toggleStatus']);
        $this->middleware('permission:lease.template.delete')->only(['destroy']);
        // $this->middleware('permission:lease.template.view')->only(['getPdf', 'generateLease']);
        // $this->middleware('permission:lease.template.duplicate')->only(['duplicate']);
        // $this->middleware('permission:lease.template.export')->only(['export']);
    }

    /**
     * Preview the template with placeholders filled with sample dummy data.
     */
    public function preview($id)
    {
        $template = LeaseTemplate::findOrFail($id);

        $pdfPath = $template->pdf_path ?? $template->document_path;

        if (!$pdfPath || !Storage::disk('public')->exists($pdfPath)) {
            return redirect()->route('lease-templates.index')
                ->with('error', 'Preview not available: PDF file not found.');
        }

        $placeholders = is_array($template->placeholders) ? $template->placeholders : [];
        $signatures = is_array($template->signatures) ? $template->signatures : [];

        // Generate dummy data for each placeholder field
        $dummyData = $this->generateDummyData($placeholders);

        $pdfUrl = asset('storage/' . $pdfPath);

        return view('backend.layouts.leases.template.lease-templates.preview', compact(
            'template',
            'placeholders',
            'signatures',
            'dummyData',
            'pdfUrl'
        ));
    }

    /**
     * Generate dummy/sample data for placeholders based on field names.
     */
    private function generateDummyData(array $placeholders): array
    {
        $dummyValues = [
            // Tenant info
            'tenant_name' => 'John Michael Doe',
            'tenant_first_name' => 'John',
            'tenant_last_name' => 'Doe',
            'tenant_email' => 'john.doe@example.com',
            'tenant_phone' => '(555) 123-4567',
            'tenant_address' => '123 Main Street, Apt 4B',
            'tenant_city' => 'New York',
            'tenant_state' => 'NY',
            'tenant_zip' => '10001',
            'tenant_ssn' => 'XXX-XX-1234',
            'tenant_dob' => '01/15/1985',
            'tenant_id' => 'DL-123456789',

            // Property info
            'property_name' => 'Sunrise Apartments',
            'property_address' => '456 Oak Avenue, Suite 100',
            'property_city' => 'Los Angeles',
            'property_state' => 'CA',
            'property_zip' => '90001',
            'unit_number' => 'Unit 205',
            'room_number' => 'Room B',
            'bed_number' => 'Bed 2',

            // Lease terms
            'lease_start_date' => '02/01/2026',
            'lease_end_date' => '01/31/2027',
            'move_in_date' => '02/01/2026',
            'move_out_date' => '01/31/2027',
            'lease_term' => '12 months',

            // Financial
            'monthly_rent' => '$1,500.00',
            'security_deposit' => '$1,500.00',
            'first_month_rent' => '$1,500.00',
            'last_month_rent' => '$1,500.00',
            'total_due' => '$4,500.00',
            'late_fee' => '$50.00',
            'pet_deposit' => '$300.00',
            'parking_fee' => '$100.00',

            // Dates
            'current_date' => date('m/d/Y'),
            'signature_date' => date('m/d/Y'),
            'effective_date' => date('m/d/Y'),

            // Landlord info
            'landlord_name' => 'ABC Property Management LLC',
            'landlord_address' => '789 Business Blvd',
            'landlord_phone' => '(555) 987-6543',
            'landlord_email' => 'leasing@abcproperties.com',
            'manager_name' => 'Jane Smith',

            // Emergency contact
            'emergency_contact' => 'Mary Doe',
            'emergency_phone' => '(555) 111-2222',
            'emergency_relation' => 'Mother',
        ];

        $result = [];

        foreach ($placeholders as $placeholder) {
            $field = $placeholder['field'] ?? '';
            if (empty($field)) continue;

            // Try exact match first
            if (isset($dummyValues[$field])) {
                $result[$field] = $dummyValues[$field];
                continue;
            }

            // Try partial match (case insensitive)
            $fieldLower = strtolower($field);
            $matched = false;
            foreach ($dummyValues as $key => $value) {
                if (str_contains($fieldLower, str_replace('_', '', strtolower($key))) ||
                    str_contains(str_replace('_', '', strtolower($key)), $fieldLower)) {
                    $result[$field] = $value;
                    $matched = true;
                    break;
                }
            }

            // Default fallback
            if (!$matched) {
                $result[$field] = '[' . ucwords(str_replace('_', ' ', $field)) . ']';
            }
        }

        return $result;
    }

    public function index()
    {
        $templates = LeaseTemplate::orderBy('created_at', 'desc')->get();
        return view('backend.layouts.leases.template.lease-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('backend.layouts.leases.template.lease-templates.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'template_file' => 'required|file|mimes:pdf|max:10240'
        ]);

        $file = $request->file('template_file');
        $extension = strtolower($file->getClientOriginalExtension());

        // Store the original file
        $documentPath = $file->store('lease-templates', 'public');
        $pdfPath = null;

        // Convert DOCX to PDF for viewing, or use PDF directly
        if ($extension === 'docx') {
            $pdfPath = $this->convertDocxToPdf($file, $documentPath);
        } else {
            // It's already a PDF
            $pdfPath = $documentPath;
        }

        // Get total pages from PDF
        $totalPages = $this->getPdfPageCount(storage_path('app/public/' . $pdfPath));

        // Get file metadata
        $metadata = [
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'uploaded_at' => now()->toDateTimeString()
        ];

        $template = LeaseTemplate::create([
            'name' => $request->name,
            'description' => $request->description,
            'original_filename' => $file->getClientOriginalName(),
            'file_type' => $extension,
            'document_path' => $documentPath,
            'pdf_path' => $pdfPath,
            'total_pages' => $totalPages,
            'metadata' => $metadata,
            'placeholders' => [],
            'signatures' => []
        ]);

        return redirect()->route('lease-templates.edit', $template->id)
            ->with('success', 'Template uploaded successfully. Now add placeholders to your document.');
    }

    /**
     * Convert DOCX to PDF for viewing
     */
    private function convertDocxToPdf($file, $documentPath)
    {
        try {
            $sourceFile = storage_path('app/public/' . $documentPath);
            $outputDir = storage_path('app/public/lease-templates/pdf');

            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            $basename = pathinfo($documentPath, PATHINFO_FILENAME);
            $pdfFilename = $basename . '.pdf';
            $pdfPath = 'lease-templates/pdf/' . $pdfFilename;
            $outputFile = $outputDir . DIRECTORY_SEPARATOR . $pdfFilename;

            // Try using LibreOffice for conversion (cross-platform)
            $libreOffice = $this->getLibreOfficePath();

            if ($libreOffice) {
                $command = sprintf(
                    '"%s" --headless --convert-to pdf --outdir "%s" "%s" 2>&1',
                    $libreOffice,
                    $outputDir,
                    $sourceFile
                );

                exec($command, $output, $returnCode);

                if ($returnCode === 0 && file_exists($outputFile)) {
                    Log::info("DOCX converted to PDF successfully: {$outputFile}");
                    return $pdfPath;
                }
            }

            // Fallback: Use PhpWord to convert (basic conversion)
            $phpWord = IOFactory::load($sourceFile);

            // Create PDF using dompdf via PhpWord
            $pdfWriter = IOFactory::createWriter($phpWord, 'PDF');
            $pdfWriter->save($outputFile);

            if (file_exists($outputFile)) {
                return $pdfPath;
            }

            // If all else fails, return the original path (will need manual handling)
            Log::warning("Could not convert DOCX to PDF, storing original");
            return $documentPath;

        } catch (\Exception $e) {
            Log::error("DOCX to PDF conversion failed: " . $e->getMessage());
            return $documentPath;
        }
    }

    /**
     * Get LibreOffice path based on OS
     */
    private function getLibreOfficePath()
    {
        // Windows paths
        $windowsPaths = [
            'C:\Program Files\LibreOffice\program\soffice.exe',
            'C:\Program Files (x86)\LibreOffice\program\soffice.exe',
        ];

        // Linux/Mac paths
        $unixPaths = [
            '/usr/bin/libreoffice',
            '/usr/bin/soffice',
            '/Applications/LibreOffice.app/Contents/MacOS/soffice',
        ];

        $paths = PHP_OS_FAMILY === 'Windows' ? $windowsPaths : $unixPaths;

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Get page count from PDF
     */
    private function getPdfPageCount($pdfPath)
    {
        try {
            if (!file_exists($pdfPath)) {
                return 1;
            }

            // Simple page count using file content
            $content = file_get_contents($pdfPath);
            $pageCount = preg_match_all("/\/Type\s*\/Page[^s]/", $content);

            return max(1, $pageCount);
        } catch (\Exception $e) {
            Log::error("Error counting PDF pages: " . $e->getMessage());
            return 1;
        }
    }

    private function convertToHtml($file, $extension)
    {
        if ($extension === 'docx') {
            return $this->docxToHtml($file);
        } else {
            return $this->pdfToHtml($file);
        }
    }

    private function docxToHtml($file)
    {
        $phpWord = IOFactory::load($file->getRealPath());
        $htmlWriter = IOFactory::createWriter($phpWord, 'HTML');

        ob_start();
        $htmlWriter->save('php://output');
        $html = ob_get_clean();

        return $html;
    }

    public function edit($id)
    {
        $template = LeaseTemplate::findOrFail($id);

        return view('backend.layouts.leases.template.lease-templates.editor', compact('template'));
    }

    public function update(Request $request, $id)
    {
        $template = LeaseTemplate::findOrFail($id);

        $updateData = [];

        if ($request->has('name')) {
            $updateData['name'] = $request->name;
        }

        if ($request->has('placeholders')) {
            $placeholders = is_string($request->placeholders)
                ? json_decode($request->placeholders, true)
                : $request->placeholders;
            $this->validateCustomFieldLabels($placeholders);
            $updateData['placeholders'] = $placeholders;
        }

        if ($request->has('signatures')) {
            $signatures = is_string($request->signatures)
                ? json_decode($request->signatures, true)
                : $request->signatures;
            $updateData['signatures'] = $signatures;
        }

        $template->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Template saved successfully'
        ]);
    }

    /**
     * Validate custom text field labels before saving.
     */
    private function validateCustomFieldLabels(array $placeholders)
    {
        foreach ($placeholders as $placeholder) {
            if (($placeholder['type'] ?? '') === 'text_input') {
                $label = trim($placeholder['label'] ?? '');
                if ($label === '' || $label === 'Custom Text Input') {
                    throw ValidationException::withMessages([
                        'placeholders' => ['Each custom text field must have a specific label.']
                    ]);
                }
            }
        }
    }

    public function destroy($id)
    {
        $template = LeaseTemplate::findOrFail($id);

        // Delete associated files
        if ($template->document_path) {
            Storage::disk('public')->delete($template->document_path);
        }
        if ($template->pdf_path && $template->pdf_path !== $template->document_path) {
            Storage::disk('public')->delete($template->pdf_path);
        }

        $template->delete();

        return redirect()->route('lease-templates.index')
            ->with('success', 'Template deleted successfully');
    }

    /**
     * Get PDF file for viewing
     */
    public function getPdf($id)
    {
        $template = LeaseTemplate::findOrFail($id);
        $pdfPath = $template->pdf_path ?? $template->document_path;

        if (!$pdfPath || !Storage::disk('public')->exists($pdfPath)) {
            abort(404, 'PDF not found');
        }

        return response()->file(storage_path('app/public/' . $pdfPath));
    }

    /**
     * Generate final lease document with filled placeholders
     */
    public function generateLease(Request $request, $id)
    {
        $template = LeaseTemplate::findOrFail($id);

        $data = $request->validate([
            'tenant_name' => 'required|string',
            'tenant_email' => 'nullable|email',
            'tenant_phone' => 'nullable|string',
            'property_address' => 'nullable|string',
            'lease_start_date' => 'nullable|date',
            'lease_end_date' => 'nullable|date',
            'monthly_rent' => 'nullable|numeric',
            'security_deposit' => 'nullable|numeric',
            // Add more fields as needed
        ]);

        // Get the PDF path
        $pdfPath = storage_path('app/public/' . ($template->pdf_path ?? $template->document_path));

        if (!file_exists($pdfPath)) {
            return response()->json(['error' => 'Template PDF not found'], 404);
        }

        // Generate PDF with placeholder values overlaid
        $outputPdf = $this->overlayPlaceholderValues($pdfPath, $template->placeholders ?? [], $data, $template->total_pages);

        return response()->download($outputPdf, 'lease_' . time() . '.pdf');
    }

    /**
     * Overlay placeholder values on PDF
     * Note: For production, install setasign/fpdi for best results
     * This is a fallback using mPDF for basic overlay
     */
    private function overlayPlaceholderValues($pdfPath, $placeholders, $data, $totalPages)
    {
        $outputPath = storage_path('app/public/generated-leases/lease_' . time() . '.pdf');

        $outputDir = dirname($outputPath);
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // For the best PDF overlay, install setasign/fpdi package:
        // composer require setasign/fpdi
        // Then use the FPDI approach below:

        // Check if FPDI is available
        if (class_exists('\setasign\Fpdi\Fpdi')) {
            return $this->overlayWithFpdi($pdfPath, $placeholders, $data, $outputPath);
        }

        // Fallback: Copy original and add instructions
        // This is a simplified approach - for production use FPDI
        copy($pdfPath, $outputPath);

        Log::warning("FPDI not installed. For proper PDF overlay, run: composer require setasign/fpdi");

        return $outputPath;
    }

    /**
     * Overlay using FPDI (when installed)
     */
    private function overlayWithFpdi($pdfPath, $placeholders, $data, $outputPath)
    {
        $pdf = new \setasign\Fpdi\Fpdi();

        // Get page count
        $pageCount = $pdf->setSourceFile($pdfPath);

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);

            // Filter placeholders for this page
            $pagePlaceholders = array_filter($placeholders, fn($p) => ($p['page'] ?? 1) == $pageNo);

            foreach ($pagePlaceholders as $placeholder) {
                $fieldName = $placeholder['field'] ?? '';
                $value = $data[$fieldName] ?? '';

                if (empty($value)) continue;

                // Convert coordinates (PDF.js uses different coordinate system)
                $x = ($placeholder['x'] ?? 0) * 0.264583; // Convert px to mm
                $y = ($placeholder['y'] ?? 0) * 0.264583;
                $fontSize = $placeholder['fontSize'] ?? 12;

                $pdf->SetFont('Helvetica', '', $fontSize * 0.75);
                $pdf->SetXY($x, $y);
                $pdf->Write(0, $value);
            }
        }

        $pdf->Output($outputPath, 'F');

        return $outputPath;
    }

    /**
     * Toggle template active status
     */
    public function toggleStatus(Request $request, $id)
    {
        $request->validate([
            'is_active' => 'required|boolean'
        ]);

        $template = LeaseTemplate::findOrFail($id);
        $template->update([
            'is_active' => $request->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Template status updated successfully'
        ]);
    }

    /**
     * Duplicate a template
     */
    public function duplicate($id)
    {
        $template = LeaseTemplate::findOrFail($id);

        $newTemplate = $template->replicate();
        $newTemplate->name = $template->name . ' (Copy)';
        $newTemplate->is_active = false;
        $newTemplate->save();

        return redirect()->route('lease-templates.edit', $newTemplate->id)
            ->with('success', 'Template duplicated successfully');
    }

    /**
     * Export template as JSON for backup
     */
    public function export($id)
    {
        $template = LeaseTemplate::findOrFail($id);

        $exportData = [
            'name' => $template->name,
            'content' => $template->content,
            'placeholders' => $template->placeholders,
            'signatures' => $template->signatures,
            'file_type' => $template->file_type,
            'exported_at' => now()->toDateTimeString()
        ];

        $fileName = 'lease_template_' . $template->id . '_' . date('Y-m-d') . '.json';

        return response()->json($exportData)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }
}
