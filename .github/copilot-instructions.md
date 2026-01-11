# Copilot Instructions for OC Workforce Laravel

## Project Overview
This is a **Laravel 11** property management and workforce tracking system combining:
- Multi-tenant property/lease management with dynamic document generation
- Real-time team location tracking with Google Maps integration
- Bidirectional Google Calendar synchronization
- Digital signature workflows for lease agreements
- Stripe payment integration (Cashier)

## Critical Architecture Patterns

### Multi-Stage Lease Creation Flow
Lease creation uses a **5-step wizard pattern** (`resources/views/backend/layouts/leases/lease/create.blade.php`):
1. Property Detail → 2. Set Rental Terms → 3. Add Tenants → 4. Renter's Insurance → 5. Finalize Lease

**Key relationships:**
- `Lease` → `Property` → `Unit` → `Room` → `Bed` (hierarchical property structure)
- `Lease` → `LeaseAssignment` (many tenants per lease)
- `Lease` → `LeaseDocument` (generated contracts with signatures)
- `Lease` → `Season` (pricing based on seasonal rates)

### Dynamic Document System
Located in `app/Services/`:
- **DocumentParsingService**: Converts PDF/DOCX uploads to HTML with field extraction
- **LeaseTemplateFieldMappingService**: Maps placeholders like `{{TENANT_NAME}}` to database fields
- **DynamicDocumentGenerationService**: Generates final lease PDFs with tenant data

Templates support signature capture with audit trails (`SignatureAuditLog` model).

### Google Calendar Bi-Directional Sync
`GoogleCalendarService` (app/Services/) + `LocationUpdated` event:
- Dashboard work creation → automatic Google Calendar event
- Google Calendar changes → webhook updates dashboard
- Uses Spatie's `laravel-google-calendar` package

### Real-Time Location Tracking
- WebSocket via **Laravel Reverb** (or Pusher fallback)
- `TeamLocation` model stores GPS coordinates
- Google Maps Polyline visualizes team travel paths
- `LocationUpdated` event broadcasts to dashboard

## Project-Specific Conventions

### Route Organization
Routes split by concern (check `routes/` directory):
- `backend.php` - Admin panel routes (property, lease, CMS management)
- `web.php` - Public routes + **artisan command shortcuts** (`/run-migrate`, `/run-optimize-clear`)
- `api.php` - API endpoints
- `auth.php` - Breeze authentication routes

**Admin routes require `['auth', 'admin']` middleware.**

### File Upload Pattern
Use `Helper::uploadImage($file, 'folder')` from `app/Helper/Helper.php`:
- Saves to `public/uploads/{folder}/`
- Auto-generates unique filenames with timestamps
- Pair with `Helper::deleteImage($imageUrl)` for cleanup

For lease documents, use storage: `storage/app/public/lease-templates/`

### Controller Structure
Controllers organized hierarchically:
```
app/Http/Controllers/
  ├── Web/Backend/          # Admin panel controllers
  │   ├── Lease/            # Lease management
  │   ├── CMS/              # Content management (Home, About, Gallery, Pricing pages)
  │   ├── Settings/         # System settings
  │   └── UserManagement/   # Roles & permissions
  ├── Api/Backend/          # Backend API endpoints
  └── GoogleCalendarController.php  # Calendar sync logic
```

### Database Query Patterns
- Models use **SoftDeletes** trait (check `deleted_at` columns)
- Use `select3` class on select elements for Select2 integration
- Yajra DataTables for server-side pagination (`yajra/laravel-datatables-oracle`)

### Frontend Stack
- **Vite** for asset bundling (`vite.config.js`, `postcss.config.js`)
- **TailwindCSS** + Bootstrap hybrid (legacy admin panels use Bootstrap)
- **Alpine.js** for reactive components
- **FullCalendar** for calendar UI (`@fullcalendar/*` packages)

## Essential Commands

```bash
# Development server (runs concurrently: serve + queue + logs + vite)
composer dev

# Individual services
php artisan serve               # Start Laravel server
php artisan queue:listen        # Process background jobs
php artisan reverb:start        # Start WebSocket server
npm run dev                     # Vite development mode

# Database
php artisan migrate --seed      # Run migrations with seeders
php artisan migrate:fresh       # Reset database (use /run-migrate-fresh route in dev)

# Production build
npm run build
php artisan optimize
```

## Key Dependencies & Use Cases

| Package | Purpose | Usage Example |
|---------|---------|---------------|
| `spatie/laravel-google-calendar` | Calendar sync | `GoogleCalendarService` |
| `spatie/laravel-permission` | Role-based access | Check `app/Models/User.php` |
| `smalot/pdfparser` + `phpoffice/phpword` | Document parsing | `DocumentParsingService` |
| `mpdf/mpdf` + `setasign/fpdi` | PDF generation | Lease document creation |
| `tymon/jwt-auth` | API authentication | JWT tokens for API routes |
| `stripe/stripe-php` + `laravel/cashier` | Payments | Subscription/invoice handling |
| `yajra/laravel-datatables-oracle` | DataTables | All admin list pages |

## Important Gotchas

1. **Enum Usage**: PHP 8.2+ enums in `app/Enums/` (`PageEnum`, `SectionEnum`)
2. **Public Storage**: Run `php artisan storage:link` after setup
3. **XAMPP Development**: Project runs on Windows XAMPP (`C:/xampp/htdocs/`)
4. **Composer Scripts**: Use `composer dev` (defined in composer.json) for multi-process dev server
5. **Artisan Shortcuts**: Routes like `/run-migrate-fresh` exist for browser-based artisan commands (dev only!)
6. **CMS System**: Page content managed via `CMS` model with section-based architecture (see `LEASE_TEMPLATE_SYSTEM_GUIDE.md`)

## Documentation References

- Lease template implementation: `LEASE_TEMPLATE_SYSTEM_GUIDE.md`
- Dynamic document architecture: `DYNAMIC_LEASE_DOCUMENT_PLAN.md`
- Quick start guide: `LEASE_TEMPLATE_QUICK_START.md`
- System redesign notes: `REDESIGN_SUMMARY.md`

## Testing & Debugging

- Run tests: `php artisan test`
- Check logs: `php artisan pail` (real-time log viewer)
- Queue monitoring: `php artisan queue:listen --tries=1`
- Cache clearing: Use `/run-optimize-clear` route or `php artisan optimize:clear`
