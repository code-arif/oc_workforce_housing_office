<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaseTemplate;
use App\Services\DocumentParsingService;
use App\Services\LeaseTemplateFieldMappingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class LeaseTemplateController extends Controller
{
    protected DocumentParsingService $documentParser;
    protected LeaseTemplateFieldMappingService $mappingService;

    public function __construct(
        DocumentParsingService $documentParser,
        LeaseTemplateFieldMappingService $mappingService
    ) {
        $this->documentParser = $documentParser;
        $this->mappingService = $mappingService;
    }

    /**
     * Store a new lease template with document upload
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'document' => 'required|file|mimes:pdf,doc,docx,xlsx|max:51200',
                // 'is_active' => 'sometimes|boolean',
                'is_editable' => 'sometimes|boolean',
            ]);

            $validated['is_active'] = $request->input('is_active') ? true : false;
            // Parse the uploaded document
            $parseResult = $this->documentParser->parseDocument($request->file('document'));

            // Create the lease template
            $template = LeaseTemplate::create([
                'title' => $validated['title'],
                'document_file_path' => $parseResult['file_path'],
                'document_type' => strtoupper($parseResult['file_type']),
                'uploaded_file_original_name' => $parseResult['original_name'],
                'content' => $parseResult['content'],
                'is_active' => $validated['is_active'] ?? true,
                'is_editable' => $validated['is_editable'] ?? true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lease template created successfully',
                'data' => [
                    'template' => $template,
                    'parsing_result' => [
                        'file_path' => $parseResult['file_path'],
                        'file_type' => $parseResult['file_type'],
                        'original_name' => $parseResult['original_name'],
                        'structure' => $parseResult['structure'],
                        'placeholders' => $parseResult['placeholders'],
                    ],
                ],
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create lease template',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get lease template by ID
     *
     * @param LeaseTemplate $template
     * @return JsonResponse
     */
    public function show(LeaseTemplate $template): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $template->load('fieldMappings'),
        ]);
    }

    /**
     * Update lease template
     *
     * @param Request $request
     * @param LeaseTemplate $template
     * @return JsonResponse
     */
    public function update(Request $request, LeaseTemplate $template): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'is_active' => 'sometimes|boolean',
                'is_editable' => 'sometimes|boolean',
            ]);

            $template->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Lease template updated successfully',
                'data' => $template,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update lease template',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete lease template
     *
     * @param LeaseTemplate $template
     * @return JsonResponse
     */
    public function destroy(LeaseTemplate $template): JsonResponse
    {
        try {
            // Delete the document file
            if ($template->document_file_path) {
                $this->documentParser->deleteDocument($template->document_file_path);
            }

            // Soft delete the template
            $template->delete();

            return response()->json([
                'success' => true,
                'message' => 'Lease template deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete lease template',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * List all lease templates
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $templates = LeaseTemplate::query()
            ->when($request->boolean('active_only'), function ($query) {
                return $query->active();
            })
            ->with('fieldMappings')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $templates,
        ]);
    }

    /**
     * Get document preview
     *
     * @param LeaseTemplate $template
     * @param Request $request
     * @return JsonResponse
     */
    public function preview(LeaseTemplate $template, Request $request): JsonResponse
    {
        try {
            $page = $request->integer('page', 1);

            $preview = $this->documentParser->getDocumentPreview(
                $template->document_file_path,
                $page
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'preview' => $preview,
                    'file_type' => $template->document_type,
                    'original_name' => $template->uploaded_file_original_name,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate preview',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Re-upload and re-parse document
     *
     * @param Request $request
     * @param LeaseTemplate $template
     * @return JsonResponse
     */
    public function uploadDocument(Request $request, LeaseTemplate $template): JsonResponse
    {
        try {
            $validated = $request->validate([
                'document' => 'required|file|mimes:pdf,doc,docx,xlsx|max:51200',
            ]);

            // Delete old document file
            if ($template->document_file_path) {
                $this->documentParser->deleteDocument($template->document_file_path);
            }

            // Parse new document
            $parseResult = $this->documentParser->parseDocument($request->file('document'));

            // Update template
            $template->update([
                'document_file_path' => $parseResult['file_path'],
                'document_type' => strtoupper($parseResult['file_type']),
                'uploaded_file_original_name' => $parseResult['original_name'],
                'content' => $parseResult['content'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded and parsed successfully',
                'data' => [
                    'template' => $template,
                    'parsing_result' => [
                        'file_path' => $parseResult['file_path'],
                        'file_type' => $parseResult['file_type'],
                        'original_name' => $parseResult['original_name'],
                        'structure' => $parseResult['structure'],
                    ],
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get available data sources for field mapping
     *
     * @return JsonResponse
     */
    public function getAvailableDataSources(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => \App\Models\LeaseTemplateFieldMapping::getAvailableDataSources(),
        ]);
    }

    /**
     * Extract placeholders from template content
     *
     * @param LeaseTemplate $template
     * @return JsonResponse
     */
    public function extractPlaceholders(LeaseTemplate $template): JsonResponse
    {
        try {
            $placeholders = $this->mappingService->extractPlaceholders($template->content);

            return response()->json([
                'success' => true,
                'data' => [
                    'placeholders' => $placeholders,
                    'count' => count($placeholders),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to extract placeholders',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Preview template with sample/actual data populated
     *
     * @param LeaseTemplate $template
     * @param Request $request
     * @return JsonResponse
     */
    public function previewWithData(LeaseTemplate $template, Request $request): JsonResponse
    {
        // dd($request->all());
        try {
            $validated = $request->validate([
                'tenant_id' => 'nullable|integer|exists:tenants,id',
                'property_id' => 'nullable|integer|exists:properties,id',
                'lease_id' => 'nullable|integer|exists:leases,id',
                // 'use_sample_data' => 'sometimes|boolean',
            ]);

            // Get data entities
            $tenant = null;
            $property = null;
            $lease = null;

            if ($validated['use_sample_data'] ?? false) {
                // Use sample/dummy data
                $tenant = $this->getSampleTenant();
                $property = $this->getSampleProperty();
                $lease = $this->getSampleLease();
            } else {
                if ($validated['tenant_id'] ?? null) {
                    $tenant = \App\Models\Tenant::find($validated['tenant_id']);
                }
                if ($validated['property_id'] ?? null) {
                    $property = \App\Models\Property::find($validated['property_id']);
                }
                if ($validated['lease_id'] ?? null) {
                    $lease = \App\Models\Lease::find($validated['lease_id']);
                }
            }

            // Inject fields into template with actual data
            $populatedContent = $this->mappingService->injectFieldsIntoTemplate(
                $template,
                $template->content,
                $tenant,
                $property,
                $lease,
                true
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'content' => $populatedContent,
                    'file_type' => $template->document_type,
                    'original_name' => $template->uploaded_file_original_name,
                    'data_used' => [
                        'has_tenant_data' => $tenant !== null,
                        'has_property_data' => $property !== null,
                        'has_lease_data' => $lease !== null,
                    ],
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to preview with data',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get sample tenant data for preview
     *
     * @return object
     */
    private function getSampleTenant(): object
    {
        return (object)[
            'id' => 1,
            'full_name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '555-123-4567',
            'ssn' => '123-45-6789',
            'date_of_birth' => now()->subYears(30),
        ];
    }

    /**
     * Get sample property data for preview
     *
     * @return object
     */
    private function getSampleProperty(): object
    {
        return (object)[
            'id' => 1,
            'address' => '123 Main Street',
            'city' => 'Springfield',
            'state' => 'IL',
            'zip' => '62701',
            'country' => 'USA',
            'unit_number' => 'Apt 101',
        ];
    }

    /**
     * Get sample lease data for preview
     *
     * @return object
     */
    private function getSampleLease(): object
    {
        return (object)[
            'id' => 1,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'rent_amount' => 1500.00,
            'deposit_amount' => 3000.00,
            'payment_frequency' => 'MONTHLY',
            'status' => 'DRAFT',
        ];
    }
}
