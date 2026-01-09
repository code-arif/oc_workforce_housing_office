# Lease Template System - Implementation Guide

## Overview
The redesigned Lease Template system allows administrators to upload pre-formatted lease documents (PDF or DOCX), convert them to editable HTML, add dynamic placeholders, and use them to generate customized lease agreements for tenants.

## Features

### 1. **Document Upload & Conversion**
- Upload lease templates in PDF or DOCX format
- Automatic conversion to editable HTML
- Preserve original document formatting
- Store original files for reference

### 2. **Dynamic Placeholder System**
- Drag-and-drop placeholder management
- Visual placeholder editor
- Support for multiple data types:
  - Text fields (names, addresses, emails)
  - Date fields (lease start/end dates)
  - Currency fields (rent, deposits)
  - Signature fields (landlord & tenant signatures)

### 3. **Template Management**
- Create, edit, and delete templates
- Activate/deactivate templates
- Duplicate existing templates
- Preview templates before use
- Track template statistics

### 4. **User-Friendly Interface**
- Modern, responsive design
- Real-time editing
- Zoom controls for better viewing
- Search functionality for placeholders
- Organized placeholder categories

## Installation Steps

### Step 1: Run Migration
```bash
php artisan migrate
```

This will add the new columns to the `lease_templates` table:
- `document_path` - stores original file path
- `thumbnail_path` - stores preview thumbnail
- `description` - template description
- `metadata` - additional file information

### Step 2: Update Routes (Already in place)
The routes are already configured in `routes/backend.php`:
```php
Route::prefix('lease-templates')->name('lease-templates.')->group(function() {
    Route::get('/', [LeaseTemplateController::class, 'index'])->name('index');
    Route::get('/create', [LeaseTemplateController::class, 'create'])->name('create');
    Route::post('/', [LeaseTemplateController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [LeaseTemplateController::class, 'edit'])->name('edit');
    Route::put('/{id}', [LeaseTemplateController::class, 'update'])->name('update');
    Route::delete('/{id}', [LeaseTemplateController::class, 'destroy'])->name('destroy');
    Route::get('/{id}/preview', [LeaseTemplateController::class, 'preview'])->name('preview');
    Route::post('/{id}/toggle-status', [LeaseTemplateController::class, 'toggleStatus'])->name('toggle-status');
    Route::get('/{id}/duplicate', [LeaseTemplateController::class, 'duplicate'])->name('duplicate');
});
```

### Step 3: Create Storage Directory
```bash
php artisan storage:link
```

Ensure the storage directory exists for lease templates:
```bash
mkdir -p storage/app/public/lease-templates
```

### Step 4: Use New Views

Replace the old views with the new ones:

#### Option A: Replace Existing Files
```bash
# Backup old files
mv resources/views/backend/lease/lease-templates/create.blade.php resources/views/backend/lease/lease-templates/create.old.blade.php
mv resources/views/backend/lease/lease-templates/edit.blade.php resources/views/backend/lease/lease-templates/edit.old.blade.php
mv resources/views/backend/lease/lease-templates/index.blade.php resources/views/backend/lease/lease-templates/index.old.blade.php

# Rename new files
mv resources/views/backend/lease/lease-templates/create-new.blade.php resources/views/backend/lease/lease-templates/create.blade.php
mv resources/views/backend/lease/lease-templates/edit-new.blade.php resources/views/backend/lease/lease-templates/edit.blade.php
mv resources/views/backend/lease/lease-templates/index-new.blade.php resources/views/backend/lease/lease-templates/index.blade.php
```

#### Option B: Test First
You can temporarily update the controller to use the new views:
- `create-new.blade.php` → `create.blade.php`
- `edit-new.blade.php` → `edit.blade.php`
- `index-new.blade.php` → `index.blade.php`

## Usage Guide

### For Administrators

#### 1. Creating a New Template

1. Navigate to **Lease Templates** section
2. Click **"Upload New Template"**
3. Fill in the template details:
   - **Name**: Give it a descriptive name (e.g., "Standard Residential Lease 2024")
   - **Description** (optional): Brief description of when to use this template
4. Upload your document:
   - Drag and drop or click to browse
   - Supported formats: PDF (.pdf) or DOCX (.docx)
   - Maximum size: 10MB
5. Click **"Upload & Continue to Editor"**

#### 2. Adding Placeholders

After uploading, you'll be taken to the template editor:

1. **Placeholder Categories** (left sidebar):
   - Tenant Information
   - Property Details
   - Lease Terms
   - Landlord Information
   - Signatures
   - Other

2. **Adding Placeholders**:
   - Drag a placeholder from the left panel
   - Drop it at the desired location in the document
   - The placeholder will appear as a colored badge

3. **Removing Placeholders**:
   - Click on any placeholder in the document
   - Confirm deletion

4. **Zoom Controls**:
   - Use zoom in/out buttons to adjust view
   - Click percentage to reset to 100%

5. **Save Template**:
   - Click **"Save Template"** button
   - Template is now ready to use

#### 3. Managing Templates

From the templates list, you can:

- **Edit**: Modify template content and placeholders
- **Preview**: View template without editing
- **Duplicate**: Create a copy of existing template
- **Activate/Deactivate**: Toggle template availability
- **Delete**: Remove template permanently
- **Use Template**: Create a new lease using this template

### For Users (Lease Creation)

When creating a lease:

1. Select a template from available templates
2. System automatically fills in placeholders with:
   - Selected tenant information
   - Property details
   - Lease terms from input form
   - Current date
3. Review the generated document
4. Add signatures (digital signature fields)
5. Finalize and send to tenant

## Available Placeholders

### Tenant Information
- `tenant_name` - Full name of the tenant
- `tenant_email` - Tenant's email address
- `tenant_phone` - Tenant's phone number
- `tenant_address` - Tenant's current address

### Property Details
- `property_address` - Full property address
- `property_type` - Type of property (apartment, house, etc.)
- `room_number` - Room or unit number

### Lease Terms
- `lease_start_date` - Lease start date
- `lease_end_date` - Lease end date
- `lease_term` - Lease duration (e.g., "12 months")
- `monthly_rent` - Monthly rental amount
- `security_deposit` - Security deposit amount

### Landlord/Admin Information
- `landlord_name` - Property owner/manager name
- `landlord_email` - Landlord's email
- `landlord_phone` - Landlord's phone number

### Signatures
- `landlord_signature` - Digital signature field for landlord
- `tenant_signature` - Digital signature field for tenant

### Other
- `current_date` - Current date when document is generated

## Technical Details

### Database Schema

**lease_templates** table:
```sql
- id (bigint)
- name (string) - Template name
- description (text) - Template description
- original_filename (string) - Original file name
- file_type (string) - pdf or docx
- document_path (string) - Path to original file
- thumbnail_path (string) - Path to preview thumbnail
- content (longtext) - Converted HTML content
- placeholders (json) - Array of placeholder definitions
- signatures (json) - Array of signature field definitions
- metadata (json) - File metadata (size, mime_type, etc.)
- is_active (boolean) - Template status
- created_at (timestamp)
- updated_at (timestamp)
```

### File Structure
```
app/
├── Models/
│   └── Lease/
│       └── LeaseTemplate.php
├── Http/
│   └── Controllers/
│       └── Web/
│           └── Backend/
│               └── Lease/
│                   └── LeaseTemplateController.php
resources/
└── views/
    └── backend/
        └── lease/
            └── lease-templates/
                ├── index.blade.php      (List all templates)
                ├── create.blade.php     (Upload new template)
                └── edit.blade.php       (Edit template with placeholders)
database/
└── migrations/
    ├── 2026_01_07_224144_create_lease.php
    └── 2026_01_09_051952_add_document_file_path_to_lease_templates_table.php
```

### Dependencies

Required packages (already included in Laravel):
- `phpoffice/phpword` - DOCX to HTML conversion
- `smalot/pdfparser` - PDF parsing (if using PHP-based PDF conversion)
- `pdftohtml` binary - For PDF to HTML conversion (external tool)

## Troubleshooting

### PDF Conversion Issues

If PDF conversion fails:

1. **Check pdftohtml installation**:
   ```bash
   which pdftohtml  # Linux/Mac
   where pdftohtml  # Windows
   ```

2. **Update path in controller** if needed:
   ```php
   $binary = 'C:\poppler\Library\bin\pdftohtml.exe'; // Windows
   $binary = '/usr/bin/pdftohtml'; // Linux
   ```

3. **Alternative**: Use DOCX format which has better support

### Placeholder Not Saving

1. Check browser console for JavaScript errors
2. Verify CSRF token is valid
3. Check file permissions on storage directory

### Styling Issues

1. Clear browser cache
2. Run `php artisan view:clear`
3. Check if CSS files are properly loaded

## Best Practices

### For Template Documents

1. **Use DOCX format** when possible (better conversion)
2. **Remove all signatures** and dates before uploading
3. **Use standard fonts** (Arial, Times New Roman, Calibri)
4. **Keep formatting simple** (avoid complex tables/layouts)
5. **Test with sample data** before using in production

### For Placeholders

1. **Strategic placement**: Place placeholders where data will change
2. **Don't over-use**: Too many placeholders can be confusing
3. **Logical grouping**: Keep related placeholders together
4. **Test thoroughly**: Generate test documents to verify

### Security

1. **Restrict access**: Only admins should manage templates
2. **Validate uploads**: System already validates file types
3. **Regular backups**: Keep backups of template documents
4. **Audit trail**: Track who creates/modifies templates

## Future Enhancements

Potential features for future versions:

1. **Conditional Content**: Show/hide sections based on data
2. **Rich Text Editor**: More formatting options
3. **Version Control**: Track template changes over time
4. **Template Categories**: Organize by property type/lease type
5. **Multi-language Support**: Templates in different languages
6. **Bulk Operations**: Apply changes to multiple templates
7. **Template Analytics**: Track template usage statistics
8. **PDF Generation**: Generate final PDFs from HTML templates

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Enable debug mode: Set `APP_DEBUG=true` in `.env`
3. Check database migrations: `php artisan migrate:status`
4. Review controller logic in `LeaseTemplateController.php`

## Summary

The redesigned Lease Template system provides a comprehensive solution for:
- Uploading and converting lease documents
- Adding dynamic placeholders visually
- Managing multiple templates
- Generating customized lease agreements

The system is designed to be user-friendly, maintainable, and extensible for future enhancements.
