# Lease Template UI - Admin Panel Documentation

## Overview
Complete admin panel UI for managing lease templates with field mapping integration using Alpine.js and Blade templates.

---

## Features

### ✅ Template Management
- **List Templates** - Browse all lease templates with filters
- **Create Template** - Upload documents with drag-and-drop
- **View Details** - See template info, content, and mappings
- **Edit Template** - Modify template properties
- **Delete Template** - Remove templates with confirmation
- **Search & Filter** - Find templates by name or status

### ✅ Field Mapping UI
- **Auto-Suggest** - AI-like suggestions based on placeholders
- **Extract Placeholders** - Automatically find all {{PLACEHOLDER}} formats
- **Manual Mapping** - Add mappings one by one
- **Bulk Management** - Edit, delete, save multiple mappings
- **Data Source Selection** - Dropdown with all available sources
- **Field Type Selection** - TEXT, DATE, AMOUNT, SIGNATURE, CUSTOM

### ✅ Template Preview
- **Live Preview** - See rendered document
- **Sample Data** - Preview with pre-filled sample data
- **Print Support** - Print preview directly from browser
- **Data Summary** - Shows what data is populated

### ✅ User Experience
- **Responsive Design** - Works on all screen sizes
- **Dark Mode** - Professional dark theme
- **Loading States** - Visual feedback during operations
- **Error Handling** - Clear error messages
- **Success Messages** - Confirmation of actions
- **Smooth Animations** - Professional transitions

---

## File Structure

```
resources/views/backend/lease/templates/
├── index.blade.php                    # Main template list page
└── modals/
    ├── create-template.blade.php      # Create new template modal
    ├── view-template.blade.php        # View template details modal
    ├── field-mapping.blade.php        # Field mapping editor modal
    └── preview-template.blade.php     # Template preview modal

app/Http/Controllers/Backend/
└── LeaseTemplateController.php        # Web controller

routes/
└── web.php                            # Template routes
```

---

## Routes

### Web Routes
```
GET  /admin/lease-templates          # List all templates (requires auth)
```

### API Routes (Used by UI)
```
GET    /api/lease-templates                                    # List templates
POST   /api/lease-templates                                    # Create template
GET    /api/lease-templates/{id}                               # Get template
PUT    /api/lease-templates/{id}                               # Update template
DELETE /api/lease-templates/{id}                               # Delete template

GET    /api/lease-templates/{id}/field-mappings               # List mappings
POST   /api/lease-templates/{id}/field-mappings               # Create mappings
GET    /api/lease-templates/{id}/field-mappings/{id}          # Get mapping
PUT    /api/lease-templates/{id}/field-mappings/{id}          # Update mapping
DELETE /api/lease-templates/{id}/field-mappings/{id}          # Delete mapping

GET    /api/lease-templates/{id}/field-mappings/suggestions   # Get suggestions
GET    /api/lease-templates/{id}/extract-placeholders         # Extract placeholders
GET    /api/lease-templates/{id}/preview-with-data            # Preview with data
```

---

## Component Breakdown

### Main Index Page (`index.blade.php`)

**Alpine Data Object: `leaseTemplatesApp()`**

**State Variables:**
```javascript
templates: []                    // All templates from API
filteredTemplates: []           // Filtered by search/status
search: ''                      // Search input
filterActive: ''                // Status filter

showCreateModal: false          // Modal visibility flags
showViewModal: false
showMappingModal: false
showPreviewModal: false

selectedTemplate: null          // Currently selected template
fieldMappings: []              // Current field mappings

isLoading: false               // Loading states
isSaving: false
error: null                    // Error messages
success: null                  // Success messages
```

**Key Methods:**
```javascript
init()                          // Initialize on page load
loadTemplates()                 // Fetch all templates from API
filterTemplates()              // Apply search and status filters
viewTemplate(template)         // Open view modal
editFieldMappings(template)    // Load and open mapping modal
previewTemplate(template)      // Open preview modal
loadFieldMappings(templateId)  // Fetch field mappings
deleteTemplate(templateId)     // Delete with confirmation
getFieldMappingCount()         // Count mappings
formatDate(dateString)         // Format dates nicely
getToken()                     // Get auth token
```

**Template Structure:**
- Header with title and "New Template" button
- Filter section (search + status dropdown)
- Template cards grid showing:
  - Title and file type
  - Created date
  - Content preview
  - Status badge
  - Action buttons (View, Map, Preview, Delete)
- Empty state when no templates

---

### Create Template Modal

**Features:**
- Title input field
- File upload with drag-and-drop
- Active status checkbox
- Form validation
- Loading state on submit

**File Upload Handling:**
```javascript
handleFileUpload(event)         // Handle drop or file input
newTemplate.file               // Store selected file
```

**Submit Process:**
1. Validate required fields
2. Create FormData with file
3. POST to `/api/lease-templates`
4. Show success/error message
5. Reload templates list
6. Close modal

---

### View Template Modal

**Displays:**
- Template metadata (file type, status, created date)
- Full document content in scrollable area
- Field mappings list with:
  - Placeholder name
  - Field label
  - Field type badge
  - Required indicator
  - Data source

**Actions:**
- Edit Field Mappings button
- Close button

---

### Field Mapping Modal

**Alpine Data Object: `fieldMappingForm()`**

**Features:**

#### Auto-Suggest
```javascript
suggestMappings()
```
- Calls `/api/lease-templates/{id}/field-mappings/suggestions`
- Analyzes template content for placeholders
- Suggests field types based on name patterns
- Adds to current mappings list

#### Extract Placeholders
```javascript
extractPlaceholders()
```
- Calls `/api/lease-templates/{id}/extract-placeholders`
- Displays all found {{PLACEHOLDER}} formats
- Allows copy-to-clipboard for each

#### Manual Add
```javascript
addMapping()
```
- Form with fields:
  - Placeholder Format ({{FIELD_NAME}})
  - Field Label
  - Field Type (dropdown)
  - Data Source (dropdown with all available)
  - Is Required (checkbox)
- Validates before adding
- Resets form after adding

#### Delete Mapping
```javascript
deleteMapping(id)
```
- Removes from list
- Not saved until bulk save

#### Save All
```javascript
saveMappings()
```
- POST all mappings to API
- Creates/updates field mappings
- Shows success message
- Reloads field mappings
- Closes modal

**Data Sources Available:**
```
Tenant:
  - tenant.full_name
  - tenant.email
  - tenant.phone
  - tenant.ssn
  - tenant.date_of_birth

Property:
  - property.address
  - property.city
  - property.state
  - property.zip
  - property.country
  - property.unit_number

Lease:
  - lease.start_date
  - lease.end_date
  - lease.rent_amount
  - lease.deposit_amount
  - lease.payment_frequency
  - lease.status

Custom:
  - custom.field
```

---

### Preview Template Modal

**Alpine Data Object: `previewForm()`**

**Features:**

#### Options
- Use Sample Data checkbox
- Toggle loads fresh preview

#### Loading States
- Shows spinner during fetch
- Content area displays during load

#### Preview Content
- Shows populated document HTML
- Formatted as white document on dark background
- Fully scrollable

#### Data Summary
- Shows which data types were populated
- Visual indicators (✓ or ✗)

#### Actions
- Print button (opens print dialog)
- Close button

**Preview Logic:**
```javascript
loadPreview()
```
- Calls `/api/lease-templates/{id}/preview-with-data?use_sample_data=true`
- Receives HTML with placeholders replaced
- Displays formatted document

---

## Styling

### Color Scheme
```css
Background: slate-900, slate-800
Borders: slate-700, slate-600
Text: white, slate-300, slate-400
Accents: blue, purple, green, red
```

### Tailwind Classes Used
- Grid layouts for responsive design
- Gradient backgrounds (`from-*-600 to-*-700`)
- Hover states for interactivity
- Border and shadow effects
- Transition animations

### Custom CSS Classes
```css
.animate-fade-in          /* Modal/card entrance */
.animate-slide-down       /* Header animations */
.animate-slide-up         /* Content animations */
.card                     /* Template cards */
.btn-*                    /* Styled buttons */
.badge-*                  /* Status badges */
.alert-*                  /* Alert messages */
```

---

## API Integration

### Authentication
All API calls include Authorization header:
```javascript
headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
}
```

Token is retrieved from:
1. Meta tag: `document.querySelector('meta[name="csrf-token"]')`
2. localStorage: `localStorage.getItem('auth_token')`
3. sessionStorage: `sessionStorage.getItem('auth_token')`

### Error Handling
- Try-catch blocks on all API calls
- Display error messages in UI
- Console logging for debugging
- User-friendly error text

### Loading States
- `isLoading` flag shows spinners
- `isSaving` flag disables buttons during save
- Prevents duplicate requests

---

## Browser Compatibility

- Modern browsers with Alpine.js support
- ES6+ JavaScript features
- CSS Grid and Flexbox
- Fetch API (no IE support)

---

## Security Features

- CSRF token validation
- Authorization header on all requests
- Confirmation dialogs for destructive actions
- Secure file upload handling
- XSS protection via x-html directive

---

## Performance Optimization

- Lazy loading of modals
- Filtered lists (client-side filtering)
- Debounced search (can be added)
- Efficient DOM updates with Alpine
- CSS animations using transforms

---

## Known Limitations & Future Enhancements

### Current Limitations
- Document preview is HTML-only (not image-based)
- Manual placeholder format validation required
- Single document upload per template

### Potential Enhancements
- PDF preview rendering (spatie/pdf-to-image)
- Drag-and-drop field mapping on preview
- Batch template import/export
- Template versioning
- Real-time collaboration
- Field position mapping (x, y coordinates)
- Template publishing/approval workflow

---

## Usage Instructions for Admin

### 1. Create New Template
1. Click "New Template" button
2. Enter template title
3. Drag-drop PDF/DOCX or click to browse
4. Check "Make active" if needed
5. Click "Create Template"
6. Wait for processing to complete

### 2. Configure Field Mappings
1. Click "Map Fields" on template card
2. Choose one of:
   - **Auto-Suggest**: Let system suggest mappings
   - **Extract Placeholders**: See all {{PLACEHOLDERS}} found
   - **Manual Add**: Create individual mappings
3. Review and edit mappings as needed
4. Click "Save All Mappings"

### 3. Preview Document
1. Click "Preview" on template card
2. Check "Use Sample Data" to see populated values
3. Review the generated document
4. Click "Print" if needed
5. Close preview

### 4. Edit or Delete
1. Click "View Details" to see full info
2. Click "Edit Field Mappings" to change mappings
3. Click "Delete" to remove template (with confirmation)

---

## Troubleshooting

**Issue: Templates not loading**
- Check browser console for errors
- Verify API endpoint is accessible
- Check authentication token is present
- Ensure user has proper permissions

**Issue: Field mapping suggestions not appearing**
- Verify document has {{PLACEHOLDER}} format
- Check console for API errors
- Try manually extracting placeholders first

**Issue: Preview shows no content**
- Ensure field mappings are created
- Check that data sources are valid
- Try with sample data enabled

**Issue: Modal won't close**
- Check console for JavaScript errors
- Try refreshing the page
- Clear browser cache if persistent

---

## Code Examples

### Loading Templates
```javascript
async loadTemplates() {
    const response = await fetch('/api/lease-templates', {
        headers: {
            'Authorization': `Bearer ${this.getToken()}`,
            'Accept': 'application/json'
        }
    });
    const data = await response.json();
    this.templates = data.data.data || data.data;
}
```

### Creating Field Mappings
```javascript
async saveMappings() {
    const response = await fetch(
        `/api/lease-templates/${this.selectedTemplate.id}/field-mappings`,
        {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${this.getToken()}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                mappings: this.fieldMappings.map(m => ({
                    field_placeholder: m.field_placeholder,
                    field_label: m.field_label,
                    field_type: m.field_type,
                    tenant_data_source: m.tenant_data_source,
                    is_required: m.is_required
                }))
            })
        }
    );
}
```

### Previewing with Data
```javascript
async loadPreview() {
    const params = new URLSearchParams();
    if (this.useSampleData) {
        params.append('use_sample_data', 'true');
    }
    
    const response = await fetch(
        `/api/lease-templates/${this.selectedTemplate.id}/preview-with-data?${params}`,
        {
            headers: {
                'Authorization': `Bearer ${this.getToken()}`
            }
        }
    );
    const data = await response.json();
    this.previewContent = data.data.content;
}
```

---

## Next Steps

Ready for Phase 3 implementation:
- Document generation with actual lease data
- PDF conversion and signing flow
- Digital signature capture
- Audit trail logging
