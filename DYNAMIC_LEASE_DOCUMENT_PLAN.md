# Dynamic Lease Document System - Implementation Plan

## Overview
A comprehensive system allowing admins to upload lease contract documents (PDF/Word), convert them to editable format, map document fields to dynamic tenant data, and enable digital signatures from both tenants and admins.

---

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                    DOCUMENT UPLOAD & PARSING                     │
│  (PDF/DOC → HTML/JSON with Field Recognition)                   │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│              FIELD MAPPING & TEMPLATE CREATION                   │
│  (Admin marks fields for dynamic data injection)                 │
└─────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────┐
│           DYNAMIC DOCUMENT GENERATION & SIGNING                  │
│  (Populate with tenant data, digital signature flow)             │
└─────────────────────────────────────────────────────────────────┘
```

---

## Database Schema Modifications

### 1. Migrate LeaseTemplate to Store Document File
```sql
ALTER TABLE lease_templates ADD COLUMN document_file_path VARCHAR(255) AFTER content;
ALTER TABLE lease_templates ADD COLUMN document_type ENUM('HTML', 'JSON', 'PDF') DEFAULT 'HTML';
ALTER TABLE lease_templates ADD COLUMN uploaded_file_original_name VARCHAR(255) AFTER document_type;
ALTER TABLE lease_templates ADD COLUMN field_mappings JSON COMMENT 'Field mapping configuration';
ALTER TABLE lease_templates ADD COLUMN is_editable BOOLEAN DEFAULT true;
```

### 2. Create New Migration: lease_template_field_mappings
Store field mapping metadata separately for better query performance:

```sql
CREATE TABLE lease_template_field_mappings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT
    template_id BIGINT FOREIGN KEY (points to lease_templates)
    field_placeholder VARCHAR(255) -- e.g., {{TENANT_NAME}}, {{PROPERTY_ADDRESS}}
    field_label VARCHAR(255) -- Human-readable label
    field_type ENUM('TEXT', 'DATE', 'AMOUNT', 'SIGNATURE', 'CUSTOM')
    tenant_data_source VARCHAR(255) -- e.g., 'tenant.full_name', 'property.address', 'lease.rent_amount'
    is_required BOOLEAN
    placeholder_position JSON -- {page: 1, x: 100, y: 200} for positioning
    created_at TIMESTAMP
    updated_at TIMESTAMP
)
```

### 3. Enhanced LeaseDocument Model
```sql
ALTER TABLE lease_documents ADD COLUMN generated_document_content LONGTEXT AFTER document_url;
ALTER TABLE lease_documents ADD COLUMN field_values JSON COMMENT 'Captured field values at signing time';
ALTER TABLE lease_documents ADD COLUMN tenant_signature_image LONGTEXT AFTER tenant_signature_token;
ALTER TABLE lease_documents ADD COLUMN admin_signature_image LONGTEXT AFTER admin_signature_token;
ALTER TABLE lease_documents ADD COLUMN signature_metadata JSON COMMENT 'Device, location, timestamp data';
```

---

## Required Packages

### Backend (PHP/Laravel)
```bash
composer require spatie/pdf-to-image  # PDF to image conversion
composer require smalot/pdfparser      # PDF parsing
composer require phpoffice/phpword      # Word document handling
composer require mpdf/mpdf              # Generates PDFs from HTML
composer require intervention/image     # Image manipulation for signatures
```

### Frontend (JavaScript)
```bash
npm install pdf.js              # PDF viewing & manipulation
npm install pdfkit              # PDF generation
npm install signature_pad        # Canvas-based signature capture
npm install html2canvas         # Convert HTML to image
npm install form-data           # Multipart form handling
```

---

## System Components

### 1. Document Upload & Parsing Service
**File:** `app/Services/DocumentParsingService.php`

**Responsibilities:**
- Validate uploaded document (PDF, DOCX, XLSX)
- Extract text content and structure
- Convert to HTML format preserving layout
- Identify fillable fields/blanks
- Generate JSON representation of document

**Key Methods:**
```php
- parseDocument(UploadedFile $file): DocumentStructure
- extractTextWithPositions(string $filePath): array
- convertToHTML(string $filePath): string
- identifyPlaceholders(string $content): array
- generateDocumentJSON(UploadedFile $file): array
```

### 2. Field Mapping Service
**File:** `app/Services/LeaseTemplateFieldMappingService.php`

**Responsibilities:**
- Save admin's field-to-data mappings
- Validate mapping configuration
- Store placeholder positions and metadata
- Manage dynamic field injection

**Key Methods:**
```php
- createFieldMapping(template_id, field_mappings): FieldMapping
- updateFieldMapping(mapping_id, data): FieldMapping
- validateMappingConfiguration(array $mappings): bool
- injectFieldPlaceholders(template, mappings): string
```

### 3. Document Generation Service
**File:** `app/Services/DynamicDocumentGenerationService.php`

**Responsibilities:**
- Populate template with tenant/lease data
- Generate final document version
- Convert to various formats (HTML, PDF)
- Prepare for signing

**Key Methods:**
```php
- generateDocumentForLease(lease_id, template_id): string
- populateTemplate(template, data): string
- generatePDF(html_content): pdf
- applyDataToTemplate(template, tenant_data, lease_data): string
```

### 4. Digital Signature Service
**File:** `app/Services/DigitalSignatureService.php`

**Responsibilities:**
- Generate unique signature tokens
- Validate signature requests
- Store signature images & metadata
- Manage signature workflow (tenant → admin)
- Verify signature authenticity

**Key Methods:**
```php
- generateSignatureToken(lease_document_id, user_id): string
- validateSignatureRequest(token): LeaseDocument
- storeSignature(token, signature_image, metadata): bool
- completeSignatureFlow(lease_document_id): bool
- getSignatureStatus(lease_document_id): array
```

### 5. Signature Verification Service
**File:** `app/Services/SignatureVerificationService.php`

**Responsibilities:**
- Log signature event (IP, timestamp, device)
- Generate tamper-proof signature certificate
- Verify signatures before finalizing
- Audit trail management

**Key Methods:**
```php
- logSignatureEvent(lease_document_id, user, metadata): void
- generateSignatureCertificate(lease_document_id): string
- verifySignatureIntegrity(lease_document_id): bool
- getAuditTrail(lease_document_id): Collection
```

---

## Controllers & API Routes

### 1. LeaseTemplateController Enhancements
```php
- store(Request $request)          // Upload and parse document
- updateFieldMappings()             // Save field mappings
- preview()                         // Preview template with sample data
- manageMappings()                  // Manage field-to-data mappings
```

### 2. LeaseDocumentController
```php
- generate(lease_id)               // Generate document from template
- requestSignature()               // Send signature request to tenant
- sign(SignatureRequest)           // Process signature submission
- getSigning Status()               // Check current signature status
- download()                        // Download finalized document
```

---

## Models & Relationships

### Enhanced LeaseTemplate
```php
class LeaseTemplate extends Model
{
    public function fieldMappings() 
    {
        return $this->hasMany(LeaseTemplateFieldMapping::class);
    }
    
    public function documents() 
    {
        return $this->hasMany(LeaseDocument::class);
    }
}
```

### New LeaseTemplateFieldMapping
```php
class LeaseTemplateFieldMapping extends Model
{
    protected $fillable = [
        'template_id', 'field_placeholder', 'field_label',
        'field_type', 'tenant_data_source', 'is_required',
        'placeholder_position'
    ];
    
    public function template() 
    {
        return $this->belongsTo(LeaseTemplate::class);
    }
}
```

### Enhanced LeaseDocument
```php
class LeaseDocument extends Model
{
    protected $fillable = [
        'lease_id', 'template_id', 'document_url',
        'generated_document_content',
        'field_values',
        'tenant_signature_token', 'tenant_signed_at',
        'tenant_signature_image', 'tenant_ip_address',
        'admin_signature_token', 'admin_signed_at',
        'admin_signature_image', 'signature_metadata'
    ];
    
    protected $casts = [
        'field_values' => 'json',
        'signature_metadata' => 'json',
        'tenant_signed_at' => 'datetime',
        'admin_signed_at' => 'datetime'
    ];
    
    public function signatureAuditLog() 
    {
        return $this->hasMany(SignatureAuditLog::class);
    }
}
```

---

## File Processing Workflow

### Step 1: Document Upload & Analysis
```
1. Admin uploads document file (PDF/DOCX)
2. DocumentParsingService validates file
3. Extract text and structure
4. Convert to HTML/JSON format
5. Identify potential fillable areas
6. Return preview to admin
```

### Step 2: Field Mapping Configuration
```
1. Admin views document preview
2. Selects blank areas/fields in document
3. Maps each field to data source:
   - Tenant fields (full_name, email, phone)
   - Property fields (address, city, state, zip)
   - Lease fields (rent_amount, start_date, end_date)
   - Custom fields
4. Saves configuration to database
```

### Step 3: Document Generation
```
1. When lease created/ready for signing
2. DynamicDocumentGenerationService retrieves:
   - Template with field mappings
   - Tenant/Lease/Property data
3. Injects data into placeholders
4. Generates final HTML/PDF document
5. Stores in lease_documents table
```

### Step 4: Signature Flow
```
1. Generate unique signature tokens for tenant & admin
2. Tenant receives signing link (token-based)
3. Tenant reviews document, captures digital signature
4. Signature stored with metadata (timestamp, IP, device)
5. Admin notified of signature completion
6. Admin reviews & digitally signs
7. Final document locked and archived
```

---

## Frontend Components

### 1. Document Upload Component
- File drag-and-drop
- File type validation
- Upload progress indicator
- Preview generated HTML/JSON

### 2. Field Mapping UI
- Interactive document preview
- Click-to-map interface
- Field configuration modal
- Field list management
- Data source selector dropdown

### 3. Document Review Component
- Display generated document
- Show current populated values
- Edit values before signing (if allowed)
- Print functionality

### 4. Digital Signature Capture
- Canvas-based signature pad
- Mobile-friendly touch support
- Clear/Redo buttons
- Signature preview
- Timestamp capture

### 5. Signature Status Dashboard
- Current signature progress
- Pending approvals
- Signature history/audit trail
- Document version control

---

## Security Considerations

1. **Document Storage**
   - Store uploaded files outside web root
   - Implement access control (signed URLs)
   - Encrypt sensitive document content

2. **Signature Security**
   - Generate tamper-proof signature tokens
   - Include IP address, timestamp, user-agent
   - Use Laravel's built-in encryption for sensitive data
   - Implement CSRF protection for signature endpoints

3. **Audit Trail**
   - Log all signature events
   - Track who viewed/signed and when
   - Store device/IP information
   - Immutable signature metadata

4. **Data Privacy**
   - Never store plain-text sensitive data
   - Implement field-level encryption
   - Comply with data retention policies
   - GDPR considerations for EU users

5. **Access Control**
   - Verify lease ownership before allowing signature
   - Role-based access (admin can manage, tenant can sign)
   - Prevent signature tampering/replay attacks

---

## Implementation Phases

### Phase 1: Foundation (Week 1)
- [ ] Database migrations
- [ ] DocumentParsingService
- [ ] LeaseTemplate model enhancements
- [ ] File upload endpoint

### Phase 2: Field Mapping (Week 2)
- [ ] LeaseTemplateFieldMapping model
- [ ] Field mapping UI
- [ ] Field mapping service
- [ ] Preview functionality

### Phase 3: Document Generation (Week 3)
- [ ] DynamicDocumentGenerationService
- [ ] Document generation endpoint
- [ ] PDF conversion
- [ ] Document preview/review

### Phase 4: Digital Signatures (Week 4)
- [ ] Signature capture component
- [ ] DigitalSignatureService
- [ ] Signature workflow endpoints
- [ ] Audit logging

### Phase 5: Polish & Testing (Week 5)
- [ ] UI/UX improvements
- [ ] Security hardening
- [ ] Performance optimization
- [ ] Comprehensive testing

---

## Technology Stack Recommendation

**Backend:**
- Laravel 11
- MPDF or Dompdf (PDF generation)
- Spatie/Pdf-to-Image (for PDF preview)
- Laravel Queue (for async processing)

**Frontend:**
- Vue 3 (or Livewire for simpler approach)
- PDF.js (for PDF viewing)
- SignaturePad.js (for signature capture)
- TailwindCSS (styling)

**Database:**
- MySQL 8.0+
- JSON column support

---

## API Endpoints Summary

```
POST   /api/lease-templates               - Create template with document upload
POST   /api/lease-templates/:id/upload    - Upload document file
GET    /api/lease-templates/:id/preview   - Preview parsed document
POST   /api/lease-templates/:id/field-mappings - Save field mappings
GET    /api/lease-templates/:id/field-mappings - Get field mappings

POST   /api/leases/:id/generate-document  - Generate document from template
GET    /api/lease-documents/:id           - Get lease document
GET    /api/lease-documents/:id/sign-link - Generate signature token
POST   /api/lease-documents/sign          - Submit signature
GET    /api/lease-documents/:id/status    - Get signature status
POST   /api/lease-documents/:id/download  - Download finalized document
```

---

## Example: Placeholder System

**Template Placeholders:**
```
{{TENANT_FULL_NAME}}
{{TENANT_EMAIL}}
{{TENANT_PHONE}}
{{PROPERTY_ADDRESS}}
{{PROPERTY_CITY}}, {{PROPERTY_STATE}} {{PROPERTY_ZIP}}
{{LEASE_START_DATE|format:Y-m-d}}
{{LEASE_END_DATE|format:Y-m-d}}
{{LEASE_RENT_AMOUNT|currency:USD}}
{{LEASE_DEPOSIT_AMOUNT|currency:USD}}
```

**Field Mapping Storage:**
```json
{
  "field_mappings": [
    {
      "placeholder": "{{TENANT_FULL_NAME}}",
      "label": "Tenant Full Name",
      "type": "TEXT",
      "source": "tenant.full_name",
      "position": {"page": 1, "x": 150, "y": 200}
    },
    {
      "placeholder": "{{LEASE_START_DATE|format:Y-m-d}}",
      "label": "Lease Start Date",
      "type": "DATE",
      "source": "lease.start_date",
      "position": {"page": 1, "x": 350, "y": 200}
    }
  ]
}
```

---

## Next Steps

1. Review this plan with stakeholders
2. Set up database migrations
3. Create DocumentParsingService
4. Build field mapping UI
5. Implement signature flow
6. Comprehensive testing & security audit
