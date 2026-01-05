<?php

namespace App\Services;

use App\Models\LeaseTemplate;
use App\Models\LeaseTemplateFieldMapping;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Exception;

class LeaseTemplateFieldMappingService
{
    /**
     * Create field mappings for a template
     *
     * @param LeaseTemplate $template
     * @param array $mappings
     * @return Collection
     * @throws Exception
     */
    public function createFieldMappings(LeaseTemplate $template, array $mappings): Collection
    {
        $this->validateMappingConfiguration($mappings);

        $createdMappings = collect();

        foreach ($mappings as $mapping) {
            $fieldMapping = LeaseTemplateFieldMapping::create([
                'template_id' => $template->id,
                'field_placeholder' => $mapping['field_placeholder'],
                'field_label' => $mapping['field_label'],
                'field_type' => $mapping['field_type'] ?? 'TEXT',
                'tenant_data_source' => $mapping['tenant_data_source'],
                'is_required' => $mapping['is_required'] ?? true,
                'placeholder_position' => $mapping['placeholder_position'] ?? null,
            ]);

            $createdMappings->push($fieldMapping);
        }

        // Update the field_mappings JSON column on the template for quick access
        $this->updateTemplateFieldMappingsJSON($template);

        return $createdMappings;
    }

    /**
     * Update field mappings for a template
     *
     * @param LeaseTemplate $template
     * @param int $mappingId
     * @param array $data
     * @return LeaseTemplateFieldMapping
     * @throws Exception
     */
    public function updateFieldMapping(LeaseTemplate $template, int $mappingId, array $data): LeaseTemplateFieldMapping
    {
        $mapping = $template->fieldMappings()->findOrFail($mappingId);

        $updateData = array_intersect_key($data, array_flip([
            'field_placeholder',
            'field_label',
            'field_type',
            'tenant_data_source',
            'is_required',
            'placeholder_position',
        ]));

        $mapping->update($updateData);

        // Refresh the template's field_mappings JSON
        $this->updateTemplateFieldMappingsJSON($template);

        return $mapping;
    }

    /**
     * Delete a field mapping
     *
     * @param LeaseTemplate $template
     * @param int $mappingId
     * @return bool
     */
    public function deleteFieldMapping(LeaseTemplate $template, int $mappingId): bool
    {
        $mapping = $template->fieldMappings()->findOrFail($mappingId);
        $result = $mapping->delete();

        // Refresh the template's field_mappings JSON
        if ($result) {
            $this->updateTemplateFieldMappingsJSON($template);
        }

        return (bool) $result;
    }

    /**
     * Delete all field mappings for a template
     *
     * @param LeaseTemplate $template
     * @return int
     */
    public function deleteAllFieldMappings(LeaseTemplate $template): int
    {
        $count = $template->fieldMappings()->delete();
        $this->updateTemplateFieldMappingsJSON($template);

        return $count;
    }

    /**
     * Get all field mappings for a template with their current values
     *
     * @param LeaseTemplate $template
     * @return Collection
     */
    public function getFieldMappings(LeaseTemplate $template): Collection
    {
        return $template->fieldMappings()
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Validate field mapping configuration
     *
     * @param array $mappings
     * @return bool
     * @throws Exception
     */
    public function validateMappingConfiguration(array $mappings): bool
    {
        foreach ($mappings as $index => $mapping) {
            $this->validateSingleMapping($mapping, $index);
        }

        return true;
    }

    /**
     * Validate a single field mapping
     *
     * @param array $mapping
     * @param int|string $index
     * @return void
     * @throws Exception
     */
    private function validateSingleMapping(array $mapping, $index = 0): void
    {
        if (empty($mapping['field_placeholder'])) {
            throw new Exception("Mapping {$index}: field_placeholder is required");
        }

        if (empty($mapping['field_label'])) {
            throw new Exception("Mapping {$index}: field_label is required");
        }

        if (empty($mapping['tenant_data_source'])) {
            throw new Exception("Mapping {$index}: tenant_data_source is required");
        }

        $validFieldTypes = ['TEXT', 'DATE', 'AMOUNT', 'SIGNATURE', 'CUSTOM'];
        if (!empty($mapping['field_type']) && !in_array($mapping['field_type'], $validFieldTypes)) {
            throw new Exception("Mapping {$index}: invalid field_type. Must be one of: " . implode(', ', $validFieldTypes));
        }

        // Validate placeholder format
        if (!$this->isValidPlaceholder($mapping['field_placeholder'])) {
            throw new Exception("Mapping {$index}: field_placeholder must be in format {{PLACEHOLDER_NAME}}");
        }
    }

    /**
     * Check if placeholder is in valid format
     *
     * @param string $placeholder
     * @return bool
     */
    private function isValidPlaceholder(string $placeholder): bool
    {
        return preg_match('/^\{\{[A-Z_]+(\|.*?)?\}\}$/', $placeholder) === 1;
    }

    /**
     * Generate placeholder from field name
     *
     * @param string $fieldName
     * @return string
     */
    public function generatePlaceholder(string $fieldName): string
    {
        return '{{' . Str::upper(Str::snake($fieldName)) . '}}';
    }

    /**
     * Update the field_mappings JSON column on the template
     *
     * @param LeaseTemplate $template
     * @return void
     */
    private function updateTemplateFieldMappingsJSON(LeaseTemplate $template): void
    {
        $mappings = $template->fieldMappings()
            ->select('field_placeholder', 'field_label', 'field_type', 'tenant_data_source', 'is_required', 'placeholder_position')
            ->get()
            ->toArray();

        $template->update([
            'field_mappings' => $mappings ?: null,
        ]);
    }

    /**
     * Get available data sources grouped by entity
     *
     * @return array
     */
    public function getAvailableDataSources(): array
    {
        return LeaseTemplateFieldMapping::getAvailableDataSources();
    }

    /**
     * Resolve data source to get actual value
     *
     * @param string $source
     * @param $tenant
     * @param $property
     * @param $lease
     * @return mixed
     */
    public function resolveDataSource(string $source, $tenant = null, $property = null, $lease = null)
    {
        return LeaseTemplateFieldMapping::resolveDataSource($source, $tenant, $property, $lease);
    }

    /**
     * Inject field mappings into template content
     * Replaces placeholders with actual values or editable markers
     *
     * @param LeaseTemplate $template
     * @param string $content
     * @param $tenant
     * @param $property
     * @param $lease
     * @param bool $populateWithData
     * @return string
     */
    public function injectFieldsIntoTemplate(
        LeaseTemplate $template,
        string $content,
        $tenant = null,
        $property = null,
        $lease = null,
        bool $populateWithData = false
    ): string {
        $mappings = $this->getFieldMappings($template);

        foreach ($mappings as $mapping) {
            $placeholder = $mapping->field_placeholder;

            if ($populateWithData) {
                // Resolve and inject actual data
                $value = $this->resolveDataSource(
                    $mapping->tenant_data_source,
                    $tenant,
                    $property,
                    $lease
                );

                // Format value based on field type
                $formattedValue = $this->formatFieldValue($value, $mapping->field_type);

                $content = str_replace($placeholder, $formattedValue ?? $placeholder, $content);
            } else {
                // Replace with editable field marker
                $fieldHTML = $this->generateEditableFieldMarker($mapping);
                $content = str_replace($placeholder, $fieldHTML, $content);
            }
        }

        return $content;
    }

    /**
     * Format field value based on field type
     *
     * @param mixed $value
     * @param string $fieldType
     * @return string|null
     */
    private function formatFieldValue($value, string $fieldType): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($fieldType) {
            'DATE' => is_object($value) ? $value->format('Y-m-d') : $value,
            'AMOUNT' => is_numeric($value) ? number_format((float)$value, 2) : $value,
            'TEXT' => (string)$value,
            default => (string)$value,
        };
    }

    /**
     * Generate HTML marker for editable field
     *
     * @param LeaseTemplateFieldMapping $mapping
     * @return string
     */
    private function generateEditableFieldMarker(LeaseTemplateFieldMapping $mapping): string
    {
        $inputType = $this->getHTMLInputType($mapping->field_type);
        $placeholder = htmlspecialchars($mapping->field_label);

        return <<<HTML
<input 
    type="{$inputType}" 
    class="field-placeholder" 
    data-placeholder="{$mapping->field_placeholder}"
    data-field-type="{$mapping->field_type}"
    data-data-source="{$mapping->tenant_data_source}"
    placeholder="{$placeholder}"
    {$this->getInputAttributes($mapping->field_type)}
/>
HTML;
    }

    /**
     * Get HTML input type for field type
     *
     * @param string $fieldType
     * @return string
     */
    private function getHTMLInputType(string $fieldType): string
    {
        return match ($fieldType) {
            'DATE' => 'date',
            'AMOUNT' => 'number',
            'SIGNATURE' => 'hidden',
            'CUSTOM' => 'text',
            default => 'text',
        };
    }

    /**
     * Get additional HTML input attributes
     *
     * @param string $fieldType
     * @return string
     */
    private function getInputAttributes(string $fieldType): string
    {
        return match ($fieldType) {
            'AMOUNT' => 'step="0.01" min="0"',
            'DATE' => '',
            default => '',
        };
    }

    /**
     * Extract all placeholders from template content
     *
     * @param string $content
     * @return array
     */
    public function extractPlaceholders(string $content): array
    {
        $placeholders = [];

        if (preg_match_all('/\{\{[A-Z_]+(\|.*?)?\}\}/', $content, $matches)) {
            $placeholders = array_unique($matches[0]);
        }

        return array_values($placeholders);
    }

    /**
     * Suggest field mappings based on extracted placeholders
     *
     * @param string $content
     * @return array
     */
    public function suggestFieldMappings(string $content): array
    {
        $placeholders = $this->extractPlaceholders($content);
        $suggestions = [];
        $dataSources = $this->getAvailableDataSources();

        foreach ($placeholders as $placeholder) {
            // Extract the base name from placeholder
            $baseName = preg_replace('/[\{\}|\s]/', '', $placeholder);

            // Try to match with available data sources
            $matchedSource = $this->findMatchingDataSource($baseName, $dataSources);

            $suggestions[] = [
                'field_placeholder' => $placeholder,
                'field_label' => $this->generateLabel($baseName),
                'field_type' => $this->guessFieldType($baseName),
                'tenant_data_source' => $matchedSource ?? 'custom.field',
                'is_required' => true,
            ];
        }

        return $suggestions;
    }

    /**
     * Find matching data source for a field name
     *
     * @param string $fieldName
     * @param array $dataSources
     * @return string|null
     */
    private function findMatchingDataSource(string $fieldName, array $dataSources): ?string
    {
        $fieldNameLower = strtolower($fieldName);

        foreach ($dataSources as $entity => $fields) {
            foreach ($fields as $source => $label) {
                if (stripos($label, $fieldName) !== false || stripos($source, $fieldNameLower) !== false) {
                    return $source;
                }
            }
        }

        return null;
    }

    /**
     * Generate human-readable label from field name
     *
     * @param string $fieldName
     * @return string
     */
    private function generateLabel(string $fieldName): string
    {
        return Str::title(Str::replace('_', ' ', Str::snake($fieldName)));
    }

    /**
     * Guess field type from field name
     *
     * @param string $fieldName
     * @return string
     */
    private function guessFieldType(string $fieldName): string
    {
        $fieldNameLower = strtolower($fieldName);

        if (stripos($fieldNameLower, 'signature') !== false) {
            return 'SIGNATURE';
        }

        if (stripos($fieldNameLower, 'date') !== false || stripos($fieldNameLower, 'at') !== false) {
            return 'DATE';
        }

        if (stripos($fieldNameLower, 'amount') !== false || stripos($fieldNameLower, 'price') !== false || stripos($fieldNameLower, 'rent') !== false) {
            return 'AMOUNT';
        }

        return 'TEXT';
    }

    /**
     * Check if all required fields have mappings
     *
     * @param LeaseTemplate $template
     * @return array
     */
    public function validateMappingCompleteness(LeaseTemplate $template): array
    {
        $mappings = $this->getFieldMappings($template);
        $requiredMappings = $mappings->where('is_required', true);

        return [
            'is_complete' => $requiredMappings->count() > 0,
            'required_count' => $requiredMappings->count(),
            'total_count' => $mappings->count(),
            'missing_mappings' => [],
        ];
    }
}
