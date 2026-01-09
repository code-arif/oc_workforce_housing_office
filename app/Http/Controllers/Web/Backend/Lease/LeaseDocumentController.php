<?php

namespace App\Http\Controllers\Web\Backend\Lease;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Models\Lease\LeaseDocument;
use App\Models\Lease\LeaseTemplate;
use App\Http\Controllers\Controller;

class LeaseDocumentController extends Controller
{
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

        return response()->json([
            'success' => true,
            'message' => 'Tenant signature saved successfully'
        ]);
    }

    public function downloadPdf($id)
    {
        $document = LeaseDocument::with(['leaseTemplate', 'tenant'])->findOrFail($id);
        
        $content = $document->rendered_content;
        
        // Replace signature placeholders with actual signatures
        $content = $this->insertSignatures($content, $document);
        
        // Generate PDF
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