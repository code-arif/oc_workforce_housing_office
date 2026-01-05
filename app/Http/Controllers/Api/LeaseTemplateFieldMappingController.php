<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaseTemplate;
use App\Models\LeaseTemplateFieldMapping;
use App\Services\LeaseTemplateFieldMappingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Exception;

class LeaseTemplateFieldMappingController extends Controller
{
    protected LeaseTemplateFieldMappingService $mappingService;

    public function __construct(LeaseTemplateFieldMappingService $mappingService)
    {
        $this->mappingService = $mappingService;
    }

    /**
     * Get all field mappings for a template
     *
     * @param LeaseTemplate $template
     * @return JsonResponse
     */
    public function index(LeaseTemplate $template): JsonResponse
    {
        try {
            $mappings = $this->mappingService->getFieldMappings($template);

            return response()->json([
                'success' => true,
                'data' => $mappings,
                'summary' => [
                    'total' => $mappings->count(),
                    'required' => $mappings->where('is_required', true)->count(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve field mappings',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Create field mappings for a template
     *
     * @param Request $request
     * @param LeaseTemplate $template
     * @return JsonResponse
     */
    public function store(Request $request, LeaseTemplate $template): JsonResponse
    {
        try {
            $validated = $request->validate([
                'mappings' => 'required|array|min:1',
                'mappings.*.field_placeholder' => 'required|string|regex:/^\{\{[A-Z_]+(\|.*?)?\}\}$/',
                'mappings.*.field_label' => 'required|string|max:255',
                'mappings.*.field_type' => 'sometimes|in:TEXT,DATE,AMOUNT,SIGNATURE,CUSTOM',
                'mappings.*.tenant_data_source' => 'required|string',
                'mappings.*.is_required' => 'sometimes|boolean',
                'mappings.*.placeholder_position' => 'sometimes|array',
            ]);

            $mappings = $this->mappingService->createFieldMappings($template, $validated['mappings']);

            return response()->json([
                'success' => true,
                'message' => count($mappings) . ' field mappings created successfully',
                'data' => $mappings,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create field mappings',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get a specific field mapping
     *
     * @param LeaseTemplate $template
     * @param LeaseTemplateFieldMapping $fieldMapping
     * @return JsonResponse
     */
    public function show(LeaseTemplate $template, LeaseTemplateFieldMapping $fieldMapping): JsonResponse
    {
        try {
            // Verify the field mapping belongs to this template
            if ($fieldMapping->template_id !== $template->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field mapping not found for this template',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $fieldMapping,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve field mapping',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update a field mapping
     *
     * @param Request $request
     * @param LeaseTemplate $template
     * @param LeaseTemplateFieldMapping $fieldMapping
     * @return JsonResponse
     */
    public function update(Request $request, LeaseTemplate $template, LeaseTemplateFieldMapping $fieldMapping): JsonResponse
    {
        try {
            // Verify the field mapping belongs to this template
            if ($fieldMapping->template_id !== $template->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field mapping not found for this template',
                ], 404);
            }

            $validated = $request->validate([
                'field_placeholder' => 'sometimes|string|regex:/^\{\{[A-Z_]+(\|.*?)?\}\}$/',
                'field_label' => 'sometimes|string|max:255',
                'field_type' => 'sometimes|in:TEXT,DATE,AMOUNT,SIGNATURE,CUSTOM',
                'tenant_data_source' => 'sometimes|string',
                'is_required' => 'sometimes|boolean',
                'placeholder_position' => 'sometimes|array',
            ]);

            $updated = $this->mappingService->updateFieldMapping($template, $fieldMapping->id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Field mapping updated successfully',
                'data' => $updated,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update field mapping',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete a field mapping
     *
     * @param LeaseTemplate $template
     * @param LeaseTemplateFieldMapping $fieldMapping
     * @return JsonResponse
     */
    public function destroy(LeaseTemplate $template, LeaseTemplateFieldMapping $fieldMapping): JsonResponse
    {
        try {
            // Verify the field mapping belongs to this template
            if ($fieldMapping->template_id !== $template->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Field mapping not found for this template',
                ], 404);
            }

            $this->mappingService->deleteFieldMapping($template, $fieldMapping->id);

            return response()->json([
                'success' => true,
                'message' => 'Field mapping deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete field mapping',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete all field mappings for a template
     *
     * @param LeaseTemplate $template
     * @return JsonResponse
     */
    public function deleteAll(LeaseTemplate $template): JsonResponse
    {
        try {
            $count = $this->mappingService->deleteAllFieldMappings($template);

            return response()->json([
                'success' => true,
                'message' => $count . ' field mappings deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete field mappings',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get suggestions for field mappings based on template content
     *
     * @param LeaseTemplate $template
     * @return JsonResponse
     */
    public function suggestions(LeaseTemplate $template): JsonResponse
    {
        try {
            $suggestions = $this->mappingService->suggestFieldMappings($template->content);

            return response()->json([
                'success' => true,
                'data' => $suggestions,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate suggestions',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Extract all placeholders from template content
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
     * Validate field mapping completeness
     *
     * @param LeaseTemplate $template
     * @return JsonResponse
     */
    public function validateCompleteness(LeaseTemplate $template): JsonResponse
    {
        try {
            $validation = $this->mappingService->validateMappingCompleteness($template);

            return response()->json([
                'success' => true,
                'data' => $validation,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to validate mappings',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get available data sources
     *
     * @return JsonResponse
     */
    public function availableDataSources(): JsonResponse
    {
        try {
            $sources = $this->mappingService->getAvailableDataSources();

            return response()->json([
                'success' => true,
                'data' => $sources,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve available data sources',
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
