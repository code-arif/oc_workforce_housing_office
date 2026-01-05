# Phase 2 - Field Mapping API Documentation

## Overview
Phase 2 implements field mapping functionality, allowing admins to map document placeholders to tenant, property, and lease data sources.

---

## Services Implemented

### LeaseTemplateFieldMappingService
**Location:** `app/Services/LeaseTemplateFieldMappingService.php`

**Key Methods:**
- `createFieldMappings()` - Create multiple field mappings
- `updateFieldMapping()` - Update a single field mapping
- `deleteFieldMapping()` - Delete a single field mapping
- `deleteAllFieldMappings()` - Delete all mappings for a template
- `getFieldMappings()` - Retrieve all mappings for a template
- `validateMappingConfiguration()` - Validate mapping configuration
- `injectFieldsIntoTemplate()` - Replace placeholders with data
- `extractPlaceholders()` - Extract all placeholders from content
- `suggestFieldMappings()` - Auto-generate mapping suggestions
- `resolveDataSource()` - Get actual value from data source
- `getAvailableDataSources()` - Get list of available data sources

---

## Controllers Implemented

### LeaseTemplateFieldMappingController
**Location:** `app/Http/Controllers/Api/LeaseTemplateFieldMappingController.php`

All endpoints require `auth:api` middleware.

---

## API Endpoints

### 1. List Field Mappings
```
GET /api/lease-templates/{template}/field-mappings
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "template_id": 1,
      "field_placeholder": "{{TENANT_FULL_NAME}}",
      "field_label": "Tenant Full Name",
      "field_type": "TEXT",
      "tenant_data_source": "tenant.full_name",
      "is_required": true,
      "placeholder_position": null,
      "created_at": "2026-01-03T12:00:00Z",
      "updated_at": "2026-01-03T12:00:00Z"
    }
  ],
  "summary": {
    "total": 1,
    "required": 1
  }
}
```

---

### 2. Create Field Mappings
```
POST /api/lease-templates/{template}/field-mappings
```

**Request:**
```json
{
  "mappings": [
    {
      "field_placeholder": "{{TENANT_FULL_NAME}}",
      "field_label": "Tenant Full Name",
      "field_type": "TEXT",
      "tenant_data_source": "tenant.full_name",
      "is_required": true,
      "placeholder_position": {
        "page": 1,
        "x": 150,
        "y": 200
      }
    },
    {
      "field_placeholder": "{{LEASE_START_DATE}}",
      "field_label": "Lease Start Date",
      "field_type": "DATE",
      "tenant_data_source": "lease.start_date",
      "is_required": true
    },
    {
      "field_placeholder": "{{RENT_AMOUNT}}",
      "field_label": "Monthly Rent",
      "field_type": "AMOUNT",
      "tenant_data_source": "lease.rent_amount",
      "is_required": true
    }
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "3 field mappings created successfully",
  "data": [...]
}
```

---

### 3. Get Field Mapping Suggestions
```
GET /api/lease-templates/{template}/field-mappings/suggestions
```

**Purpose:** Auto-analyze template content and suggest field mappings based on extracted placeholders.

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "field_placeholder": "{{TENANT_FULL_NAME}}",
      "field_label": "Tenant Full Name",
      "field_type": "TEXT",
      "tenant_data_source": "tenant.full_name",
      "is_required": true
    },
    {
      "field_placeholder": "{{LEASE_START_DATE}}",
      "field_label": "Lease Start Date",
      "field_type": "DATE",
      "tenant_data_source": "lease.start_date",
      "is_required": true
    }
  ]
}
```

---

### 4. Get Available Data Sources
```
GET /api/lease-templates/{template}/field-mappings/available-data-sources
```

**Response:**
```json
{
  "success": true,
  "data": {
    "tenant": {
      "tenant.full_name": "Tenant Full Name",
      "tenant.email": "Tenant Email",
      "tenant.phone": "Tenant Phone",
      "tenant.ssn": "Tenant SSN",
      "tenant.date_of_birth": "Tenant Date of Birth"
    },
    "property": {
      "property.address": "Property Address",
      "property.city": "Property City",
      "property.state": "Property State",
      "property.zip": "Property ZIP Code",
      "property.country": "Property Country",
      "property.unit_number": "Unit Number"
    },
    "lease": {
      "lease.start_date": "Lease Start Date",
      "lease.end_date": "Lease End Date",
      "lease.rent_amount": "Monthly Rent Amount",
      "lease.deposit_amount": "Security Deposit Amount",
      "lease.payment_frequency": "Payment Frequency",
      "lease.status": "Lease Status"
    },
    "custom": {
      "custom.field": "Custom Field (Manual Entry)"
    }
  }
}
```

---

### 5. Validate Mapping Completeness
```
GET /api/lease-templates/{template}/field-mappings/validate-completeness
```

**Response:**
```json
{
  "success": true,
  "data": {
    "is_complete": true,
    "required_count": 5,
    "total_count": 8,
    "missing_mappings": []
  }
}
```

---

### 6. Update Field Mapping
```
PUT /api/lease-templates/{template}/field-mappings/{fieldMapping}
```

**Request:**
```json
{
  "field_label": "Updated Label",
  "field_type": "TEXT",
  "is_required": false
}
```

**Response:**
```json
{
  "success": true,
  "message": "Field mapping updated successfully",
  "data": {...}
}
```

---

### 7. Delete Field Mapping
```
DELETE /api/lease-templates/{template}/field-mappings/{fieldMapping}
```

**Response:**
```json
{
  "success": true,
  "message": "Field mapping deleted successfully"
}
```

---

### 8. Delete All Field Mappings
```
DELETE /api/lease-templates/{template}/field-mappings/all
```

**Response:**
```json
{
  "success": true,
  "message": "5 field mappings deleted successfully"
}
```

---

### 9. Extract Placeholders from Template
```
GET /api/lease-templates/{template}/extract-placeholders
```

**Response:**
```json
{
  "success": true,
  "data": {
    "placeholders": [
      "{{TENANT_FULL_NAME}}",
      "{{TENANT_EMAIL}}",
      "{{PROPERTY_ADDRESS}}",
      "{{LEASE_START_DATE}}",
      "{{RENT_AMOUNT}}"
    ],
    "count": 5
  }
}
```

---

### 10. Preview Template with Sample Data
```
GET /api/lease-templates/{template}/preview-with-data?use_sample_data=true
```

**Query Parameters:**
- `use_sample_data` (bool): Use sample data for preview
- `tenant_id` (int): Actual tenant ID (alternative to sample data)
- `property_id` (int): Actual property ID
- `lease_id` (int): Actual lease ID

**Response:**
```json
{
  "success": true,
  "data": {
    "content": "<html>...populated document content...</html>",
    "file_type": "HTML",
    "original_name": "lease_agreement.pdf",
    "data_used": {
      "has_tenant_data": true,
      "has_property_data": true,
      "has_lease_data": true
    }
  }
}
```

---

## Field Types

### TEXT
Standard text input field for general information.
```
{{TENANT_FULL_NAME}}
{{TENANT_EMAIL}}
{{PROPERTY_ADDRESS}}
```

### DATE
Date-formatted fields for temporal data.
```
{{LEASE_START_DATE}}
{{LEASE_END_DATE}}
{{TENANT_DATE_OF_BIRTH}}
```
**Format:** YYYY-MM-DD

### AMOUNT
Numeric fields for monetary values, formatted with 2 decimal places.
```
{{RENT_AMOUNT}}
{{DEPOSIT_AMOUNT}}
```
**Format:** 1500.00

### SIGNATURE
Reserved for digital signature fields.
```
{{TENANT_SIGNATURE}}
{{ADMIN_SIGNATURE}}
```

### CUSTOM
Manual entry fields, not auto-populated from data sources.
```
{{CUSTOM_NOTES}}
{{SPECIAL_TERMS}}
```

---

## Placeholder Format Requirements

All placeholders must follow this format:
```
{{FIELD_NAME}}
{{FIELD_NAME|format:Y-m-d}}  // Optional formatting
{{FIELD_NAME|currency:USD}}  // Optional formatting
```

**Rules:**
- Enclosed in double curly braces `{{}}`
- Field name in UPPERCASE
- Underscores separate words
- Optional pipe-separated formatting directives

---

## Workflow Example

### Step 1: Upload Template Document
```bash
POST /api/lease-templates
Content-Type: multipart/form-data

{
  "title": "Standard Residential Lease",
  "document": [PDF file content],
  "is_active": true
}
```

### Step 2: Extract Placeholders
```bash
GET /api/lease-templates/1/extract-placeholders
```

### Step 3: Get Suggestions
```bash
GET /api/lease-templates/1/field-mappings/suggestions
```

### Step 4: Create Mappings (Auto or Manual)
```bash
POST /api/lease-templates/1/field-mappings

{
  "mappings": [
    {
      "field_placeholder": "{{TENANT_FULL_NAME}}",
      "field_label": "Tenant Full Name",
      "field_type": "TEXT",
      "tenant_data_source": "tenant.full_name",
      "is_required": true
    },
    ...
  ]
}
```

### Step 5: Validate Mappings
```bash
GET /api/lease-templates/1/field-mappings/validate-completeness
```

### Step 6: Preview with Sample Data
```bash
GET /api/lease-templates/1/preview-with-data?use_sample_data=true
```

---

## Error Responses

### Invalid Placeholder Format
```json
{
  "success": false,
  "message": "Failed to create field mappings",
  "error": "Mapping 0: field_placeholder must be in format {{PLACEHOLDER_NAME}}"
}
```

### Missing Required Field
```json
{
  "success": false,
  "message": "Failed to create field mappings",
  "error": "Mapping 1: tenant_data_source is required"
}
```

### Template Not Found
```json
{
  "success": false,
  "message": "Failed to retrieve field mappings",
  "error": "No query results found for model [App\\Models\\LeaseTemplate]"
}
```

---

## Database Relationships

### LeaseTemplate
- `hasMany` LeaseTemplateFieldMapping

### LeaseTemplateFieldMapping
- `belongsTo` LeaseTemplate
- Stores mapping configuration for dynamic field injection

---

## Next Steps (Phase 3)

- Document generation with populated data
- PDF conversion and signing flow
- Digital signature collection from tenants and admins
