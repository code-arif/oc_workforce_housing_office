<?php

namespace App\Http\Controllers\Web\Backend\Lease;

use Illuminate\Http\Request;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Lease\LeaseTemplate;
use Smalot\PdfParser\Parser as PdfParser;
use Ibnuhalimm\LaravelPdfToHtml\LaravelPdfToHtml;
use Ibnuhalimm\LaravelPdfToHtml\Facades\PdfToHtml;

class LeaseTemplateController extends Controller
{
    public function index()
    {
        $templates = LeaseTemplate::orderBy('created_at', 'desc')->get();
        return view('backend.lease.lease-templates.index-new', compact('templates'));
    }

    public function create()
    {
        return view('backend.lease.lease-templates.create-new');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'template_file' => 'required|file|mimes:pdf,docx|max:10240'
        ]);

        $file = $request->file('template_file');
        $extension = $file->getClientOriginalExtension();
        
        // Store the original file
        $documentPath = $file->store('lease-templates', 'public');
        
        // Convert document to HTML
        $htmlContent = $this->convertToHtml($file, $extension);
        
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
            'content' => $htmlContent,
            'metadata' => $metadata,
            'placeholders' => [],
            'signatures' => []
        ]);

        return redirect()->route('lease-templates.edit', $template->id)
            ->with('success', 'Template uploaded successfully. Now add placeholders and signatures.');
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
        
        // Clean up the HTML
        return $this->cleanHtml($html);
    }

    private function pdfToHtml($file)
    {
        try {
            $sourceFile = $file->getRealPath();
            
            if (!file_exists($sourceFile)) {
                throw new \Exception("PDF file not found: {$sourceFile}");
            }
            
            Log::info("Processing PDF: {$sourceFile}");
            
            $outputDir = storage_path('app/pdf-to-html');
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }
            
            // Generate unique output filename
            $basename = pathinfo($sourceFile, PATHINFO_FILENAME);
            $outputFile = $outputDir . DIRECTORY_SEPARATOR . $basename . '.html';
            
            // Build command with proper escaping for Windows
            $binary = 'C:\poppler\Library\bin\pdftohtml.exe';
            $command = sprintf(
                '"%s" -c -noframes -i "%s" "%s" 2>&1',
                $binary,
                $sourceFile,
                $outputFile
            );
            
            Log::info("Executing command: {$command}");
            
            // Execute command
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                $errorMsg = implode("\n", $output);
                Log::error("pdftohtml failed: {$errorMsg}");
                throw new \Exception("Conversion failed: {$errorMsg}");
            }
            
            // Check if HTML file was created
            if (!file_exists($outputFile)) {
                throw new \Exception("HTML file was not generated at: {$outputFile}");
            }
            
            // Read the generated HTML
            $html = file_get_contents($outputFile);
            
            Log::info("HTML generated successfully at: {$outputFile}");
            // Clean up the HTML
            return $this->cleanHtml($html);
            
        } catch (\Exception $e) {
            Log::error("PDF to HTML conversion failed: " . $e->getMessage());
            throw $e;
        }
    }
    

    private function cleanHtml($html)
    {
        // Extract body content if full HTML document
        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $matches)) {
            $html = $matches[1];
        }
        
        // Remove script tags
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        
        // Remove meta tags and head content
        $html = preg_replace('/<head\b[^>]*>(.*?)<\/head>/is', '', $html);
        $html = preg_replace('/<meta[^>]*>/is', '', $html);
        $html = preg_replace('/<link[^>]*>/is', '', $html);
        
        // Remove HTML, body, DOCTYPE tags
        $html = preg_replace('/<!DOCTYPE[^>]*>/is', '', $html);
        $html = preg_replace('/<\/?html[^>]*>/is', '', $html);
        $html = preg_replace('/<\/?body[^>]*>/is', '', $html);
        
        // Keep inline styles but remove external style blocks that might break layout
        // Only remove style blocks that have complex/problematic CSS
        $html = preg_replace('/<style\b[^>]*>(?!.*(?:font-family|font-size|text-align|margin|padding))(.*?)<\/style>/is', '', $html);
        
        // Clean up extra whitespace but preserve structure
        $html = preg_replace('/\s+/', ' ', $html);
        $html = str_replace('> <', '><', $html);
        
        // Wrap content in a div to ensure proper structure
        $html = '<div class="document-content">' . trim($html) . '</div>';
        
        return $html;
    }

    public function edit($id)
    {
        $template = LeaseTemplate::findOrFail($id);
        
        // Available placeholder types
        $placeholderTypes = [
            'tenant_name' => 'Tenant Name',
            'tenant_email' => 'Tenant Email',
            'tenant_phone' => 'Tenant Phone',
            'property_address' => 'Property Address',
            'lease_start_date' => 'Lease Start Date',
            'lease_end_date' => 'Lease End Date',
            'monthly_rent' => 'Monthly Rent',
            'security_deposit' => 'Security Deposit',
            'lease_term' => 'Lease Term',
            'admin_name' => 'Admin/Landlord Name',
            'admin_email' => 'Admin/Landlord Email',
            'current_date' => 'Current Date'
        ];

        return view('backend.lease.lease-templates.edit-new', compact('template', 'placeholderTypes'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required',
            'placeholders' => 'nullable|json',
            'signatures' => 'nullable|json'
        ]);

        $template = LeaseTemplate::findOrFail($id);
        $template->update([
            'name' => $request->name,
            'content' => $request->content,
            'placeholders' => json_decode($request->placeholders, true),
            'signatures' => json_decode($request->signatures, true)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Template updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $template = LeaseTemplate::findOrFail($id);
        $template->delete();

        return redirect()->route('lease-templates.index')
            ->with('success', 'Template deleted successfully');
    }

    /**
     * Preview template content
     */
    public function preview($id)
    {
        $template = LeaseTemplate::findOrFail($id);
        
        return response()->json([
            'success' => true,
            'content' => $template->content
        ]);
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