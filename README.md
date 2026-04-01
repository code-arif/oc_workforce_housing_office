# Home Green Valley Landscaping — Backend System

Welcome to **Home Green Valley Landscaping**, a powerful and fully automated backend system built with **Laravel**.  
This platform is designed to simplify the management of landscaping operations — from employee coordination to work scheduling and live team tracking — with seamless Google integration.

---

## Key Features

### Employee Management
- Add, edit, and manage employees easily.  
- Each employee has role-based access and activity tracking.
- Maintain complete employment history, contact info, and assigned work logs.

### Team Management
- Create and organize multiple teams dynamically.  
- Assign an **unlimited number of employees** to any team.  
- Promote or reassign **Team Leaders** with just a few clicks.  
- View team performance and productivity insights directly in the dashboard.

### Work Management
- Create, update, and delete work schedules effortlessly.  
- Each work is linked to a team, employees, and calendar events.  
- Works can be color-coded and filtered by team, priority, or date.  
- Work reports and completion tracking integrated into the dashboard.

### Google Calendar Clone (Real-Time Sync)
> One of the most advanced features of this system — a **fully functional clone of Google Calendar**.

- When a work is created in the dashboard, it **automatically syncs** with Google Calendar in real time.  
- When an event is created directly in Google Calendar, it **instantly syncs back** to the dashboard.  
- Supports **bi-directional synchronization**, so both systems stay up-to-date always.  
- Real-world implementation of Google API integration and calendar event management.

### Real-Time Team Location Tracking
- Track the live location of each team member directly on the dashboard using **Google Maps API**.  
- Integrated with **Geolocation services** to ensure accurate movement data.  
- Real-time updates with **WebSocket** and **Laravel Reverb (or Pusher)**.

### Work Tracking with Google Maps Polyline
- View each team's **entire travel path** visualized on the map using **Google Maps Polyline**.  
- Automatically records and displays the team’s route during a work session.  
- Useful for performance analysis, logistics optimization, and transparency.

---

## Tech Stack

| Layer | Technology |
|:------|:------------|
| **Backend** | Laravel 11 (PHP 8+) |
| **Database** | MySQL / MariaDB |
| **Frontend Assets** | Bootstrap CSS, Vite |
| **Realtime Services** | Laravel Reverb / Pusher |
| **APIs** | Google Maps API, Google Calendar API |
| **Authentication** | JWT |

---

## 📂 Project Structure
```bash
app/ → Core logic (Models, Controllers, Services)
routes/ → API and Web routes
database/ → Migrations, Seeders, Factories
resources/ → Views, CSS, JS (Vite + Tailwind)
public/ → Publicly accessible assets
config/ → Configuration files
```

---
# 🏠 OC Workforce - Property Management & Tenant Onboarding System

Welcome to **OC Workforce**, an enterprise-grade property management and workforce tracking system built with **Laravel 11**. This comprehensive platform combines property/lease management, tenant onboarding, payment processing, digital signatures, real-time team tracking, and advanced document automation into one powerful solution.

---

## 📋 Table of Contents
- [Overview](#-overview)
- [Core Features](#-core-features)
- [System Architecture](#-system-architecture)
- [Key Workflows](#-key-workflows)
- [Tech Stack](#-tech-stack)
- [Getting Started](#-getting-started)
- [Development](#-development)
- [API Documentation](#-api-documentation)
- [Contributing](#-contributing)

---

## 🎯 Overview

**OC Workforce** is a multi-tenant property management system designed for:
- **Property Managers**: Manage properties, units, rooms, beds, and leases with hierarchical organization
- **Tenants**: Apply for housing, sign leases digitally, track payments, and submit maintenance requests
- **Teams**: Track workforce location in real-time with Google Maps integration
- **Administrators**: Generate dynamic lease documents, process payments via Stripe, and monitor operations

### **What Makes This System Unique?**

✅ **Hierarchical Property Structure**: Property → Unit → Room → Bed (supports 100K+ records with optimized queries)  
✅ **Dynamic Document Generation**: Upload templates (PDF/DOCX), extract fields, map to tenant data, generate contracts  
✅ **Digital Signature Workflow**: Full audit trail with IP tracking, user agents, and timestamp verification  
✅ **Stripe Connect Integration**: Property-level payment processing with Stripe Connected Accounts  
✅ **Bi-Directional Calendar Sync**: Dashboard ↔ Google Calendar real-time synchronization  
✅ **Real-Time Location Tracking**: GPS-based team tracking with WebSocket updates  
✅ **Advanced IMAP Email Sync**: Sync Gmail accounts with folder mapping and attachment storage  

---

## 🚀 Core Features

### 1. 🏢 Property Management

**Hierarchical Structure:**
```
Property (Building)
├── Units (Floors/Sections - gender-designated)
│   ├── Rooms (Individual spaces with room numbers)
│   │   └── Beds (Housing units with occupancy tracking)
├── Amenities (Shared facilities: gym, pool, parking)
├── Seasons (Rental periods: Fall 2024, Spring 2025)
└── Leases (Tenant agreements with payment schedules)
```

**Key Capabilities:**
- Multi-property management with customizable attributes
- Gender-designated units and rooms (e.g., male/female floors)
- Base rent per bed with amenity-based pricing
- Occupancy tracking with `is_occupied` flags
- Optimized queries for large-scale operations (100K+ beds)
- Stripe Connect integration for property-level payment processing

**Models:** `Property`, `Unit`, `Room`, `Bed`, `Amenities`, `Season`

---

### 2. 👥 Tenant Management & Applications

**Tenant Onboarding Workflow:**
1. **Application Submission**: Prospective tenants submit applications with personal/employment info
2. **Document Upload**: Passport, visa, national ID, and sponsor documents
3. **Application Review**: Admin approves/rejects applications
4. **Tenant Creation**: Approved applications create tenant profiles with authentication
5. **Lease Assignment**: Tenants assigned to specific beds with move-in dates

**Features:**
- **JWT Authentication**: Separate tenant login system
- **Multi-Document Upload**: Passport copies, visa documents, ID proofs
- **Sponsor Tracking**: J1 visa sponsor information (for international tenants)
- **Emergency Contacts**: Store emergency contact details
- **Employment History**: Track tenant employment records
- **Profile Management**: Comprehensive tenant profiles with avatars
- **OTP/Password Recovery**: Secure account recovery

**Models:** `Tenant`, `TenantProfile`, `TenantAddress`, `TenantDocument`, `TenantEmergencyContact`, `TenantEmploymentHistory`, `Application`

---

### 3. 📄 Dynamic Document System

**The Most Advanced Feature**: Upload lease templates, extract fields, map to data sources, generate contracts.

#### **Document Processing Pipeline:**

**Step 1: Template Upload & Parsing** (`DocumentParsingService`)
- Supports: PDF, DOCX, DOC, XLSX
- Extracts content, structure (pages, paragraphs, tables), and placeholders
- Converts to HTML with styled field placeholders
- Generates JSON representation for quick access
- Creates thumbnail previews

**Step 2: Field Mapping** (`LeaseTemplateFieldMappingService`)
- Identifies placeholders like `{{TENANT_NAME}}`, `{{MOVE_IN_DATE}}`, `{{MONTHLY_RENT}}`
- Maps placeholders to data sources (tenant fields, lease fields, property fields)
- Defines field types: TEXT, DATE, NUMBER, EMAIL, PHONE
- Marks required fields for validation
- Stores mappings in `lease_template_field_mappings` table

**Step 3: Document Generation** (`DynamicDocumentGenerationService`)
- Retrieves template and field mappings
- Validates required fields are populated
- Replaces placeholders with actual tenant/lease/property data
- Generates HTML/PDF using mPDF
- Creates `LeaseDocument` record linked to lease

**Step 4: Digital Signatures**
- Admin signs first → Tenant signs second (or vice versa)
- Signature data stored with timestamps
- Full audit trail in `SignatureAuditLog` (IP address, user agent, metadata)
- Document status: draft → pending_signatures → signed → cancelled

**Libraries Used:**
- `smalot/pdfparser` - Extract text from PDFs
- `phpoffice/phpword` - Parse Word documents
- `mpdf/mpdf` - Generate PDF contracts
- `setasign/fpdi` - Merge/manipulate PDFs

**Models:** `LeaseTemplate`, `LeaseTemplateFieldMapping`, `LeaseDocument`, `SignatureAuditLog`

---

### 4. 💳 Payment Processing (Stripe Integration)

**Stripe Connect Architecture:**
- Each property has its own Stripe Connected Account (`stripe_account_id`)
- Property owners receive direct deposits minus platform fees
- Supports onboarding, account verification, and status tracking
- Badge indicators: Active, Pending, Restricted, Disabled

**Payment Features:**
- **Invoice Management**: Auto-generated invoices for rent, deposits, fees
- **Recurring Payments**: Monthly/quarterly rent schedules via `LeasePaymentSchedule`
- **Payment Gateway**: Stripe Checkout integration
- **Payment Review**: Two-stage approval (recorded → reviewed/confirmed)
- **Transaction Tracking**: Full audit trail with `gateway_transaction_id`
- **Overdue Detection**: Automatic overdue status on missed due dates
- **Laravel Cashier**: Billable trait on User model for subscriptions

**Payment Workflow:**
1. Lease created → Payment schedule generated
2. Invoice issued on due date with amount and line items
3. Tenant pays via Stripe → Payment recorded with transaction ID
4. Admin reviews/confirms payment
5. Invoice status updated (pending → paid → overdue if missed)
6. Balance tracking: `paid_amount`, `balance_due`

**Models:** `Invoice`, `Payment`, `Transaction`, `LeasePaymentSchedule`

---

### 5. 🗓️ Google Calendar Integration

**Bi-Directional Synchronization:**
- Dashboard work creation → Auto-creates Google Calendar event
- Google Calendar changes → Webhook updates dashboard
- OAuth2 authentication with token refresh
- Color-coded events with team assignments

**Features:**
- Full CRUD operations synced across both platforms
- User-level calendar integration (each user connects their own Google account)
- Token storage: `google_access_token`, `google_refresh_token`, `google_token_expires_at`
- Uses `spatie/laravel-google-calendar` package

**Implementation:** `GoogleCalendarController`, `GoogleCalendarService`

---

### 6. 👷 Workforce & Team Management

**Team Features:**
- Create unlimited teams with custom names/descriptions
- Assign multiple employees per team (many-to-many relationship)
- Designate team leaders with `is_leader` flag
- Track team performance and productivity

**Real-Time Location Tracking:**
- GPS coordinates (latitude, longitude, accuracy)
- Speed, bearing, altitude tracking
- Battery level monitoring
- Mock location detection (fraud prevention)
- Network type and signal strength
- Activity type classification
- Recent location scopes (`recent($minutes)`)

**Use Cases:**
- Field service teams (landscaping, maintenance)
- Delivery/logistics tracking
- Attendance verification
- Route optimization
- Performance analytics

**Models:** `Team`, `TeamUser` (pivot), `TeamLocation`

---

### 7. 📧 Email Management (IMAP Sync)

**Email Features:**
- Connect Gmail/Outlook accounts via IMAP
- Folder synchronization (Inbox, Sent, Drafts, Trash)
- Message deduplication by `message_id`
- Attachment storage with mime type detection
- Compose drafts and send emails
- Unified inbox per user

**Configuration:**
- `EmailAccount`: IMAP/SMTP credentials per user
- `EmailMessage`: Received messages with metadata
- `EmailDraft`: Compose drafts before sending
- `EmailAttachment`: File storage with download URLs

**Libraries:** `webklex/laravel-imap`, `webklex/php-imap`

---

### 8. 🏗️ Maintenance Requests

**Tenant-Initiated Maintenance:**
- Title, category, description fields
- Mark as urgent (priority handling)
- Grant property access permission
- Upload images/videos of issues (`MaintenanceRequestAttachment`)
- Link to tenant, property, unit, room, bed
- Status tracking: pending → in_progress → resolved → cancelled

**Admin Dashboard:**
- View all maintenance requests by property
- Assign to maintenance teams
- Update status and add notes
- Track resolution time

**Models:** `MaintenanceRequest`, `MaintenanceRequestAttachment`

---

### 9. 🌐 CMS (Content Management System)

**Customizable Website Pages:**
- Homepage (banners, sliders, hero sections)
- Properties page (listings, filters)
- About Us page
- Amenities showcase
- Pricing plans
- Reservation form
- Gallery (property images)
- FAQs
- Contact form

**Features:**
- Editable text content (titles, descriptions, slogans)
- Image/background uploads
- Button customization (text, links, colors)
- Contact info management (email, phone, address)
- Video embeds (`HomeVideo` model)
- Social media links (`SocialLink` model)
- Metadata storage for SEO

**Models:** `CMS`, `Slider`, `Gallery`, `Faq`, `Amenities`, `HomeVideo`, `SocialLink`

---

### 10. 📊 Reporting & Analytics

**Dashboard Reports:**
- **Income Tracking**: Revenue by property, unit, month
- **Property Reports**: Occupancy rates, available beds, revenue per property
- **Rent Collection**: Payment status, overdue tenants, collection rates
- **Tenant Statistics**: Active leases, move-ins/move-outs, demographics

**Data Tables:**
- Yajra DataTables for server-side pagination
- Export to Excel/CSV via `maatwebsite/excel`
- Filterable by date range, property, status

**Controllers:** `IncomeController`, `PropertyReportController`, `RentCollectionReportController`, `TenantReportController`

---

### 11. 🔐 Authentication & Authorization

**Multi-Level Authentication:**
- **Admin Users**: Laravel Breeze authentication with role-based access
- **Tenants**: Separate JWT authentication for tenant portal
- **Spatie Permission**: Role and permission management

**Security Features:**
- JWT token generation and validation
- Password hashing with bcrypt
- OTP-based password recovery
- Session management
- CSRF protection
- XSS prevention

**Models:** `User` (HasRoles, Billable, JWTSubject), `Tenant` (JWTSubject)

---

### 12. ⚡ Real-Time Features

**WebSocket Integration:**
- Laravel Reverb (or Pusher fallback)
- Real-time location updates broadcast to dashboard
- Live calendar sync notifications
- Payment status updates
- Team activity feeds

**Event Broadcasting:**
- `LocationUpdated` event for team tracking
- Calendar event changes
- Payment confirmations
- Lease status updates

---

## 🏗️ System Architecture

### **Database Schema Highlights**

**50+ Database Tables:**
- Property hierarchy: `properties`, `units`, `rooms`, `beds`
- Leasing: `leases`, `lease_assignments`, `lease_documents`, `lease_templates`
- Payments: `invoices`, `payments`, `transactions`, `lease_payment_schedules`
- Tenants: `tenants`, `tenant_profiles`, `tenant_addresses`, `tenant_documents`, `applications`
- Teams: `teams`, `team_users`, `team_locations`
- Documents: `lease_templates`, `lease_template_field_mappings`, `lease_documents`, `signature_audit_logs`
- Email: `email_accounts`, `email_messages`, `email_drafts`, `email_attachments`
- CMS: `c_m_s`, `sliders`, `galleries`, `faqs`, `amenities`, `home_videos`
- Maintenance: `maintenance_requests`, `maintenance_request_attachments`

**Design Patterns:**
- Soft deletes on critical models (Properties, Leases, Invoices)
- JSON columns for flexible metadata storage
- Timestamps for audit trails (created_at, updated_at, deleted_at)
- Eager loading for optimized queries (100K+ record support)

### **Service Layer Architecture**

Located in `app/Services/`:
- `DocumentParsingService` - PDF/DOCX parsing
- `LeaseTemplateFieldMappingService` - Field mapping
- `DynamicDocumentGenerationService` - Contract generation
- `GoogleCalendarService` - Calendar synchronization
- `EmailService` - IMAP email sync

### **Controller Organization**

```
app/Http/Controllers/
├── Web/Backend/              # Admin panel
│   ├── Lease/                # Lease management
│   ├── Property/             # Property CRUD
│   ├── UserManagement/       # Roles, permissions
│   ├── CMS/                  # Content management
│   └── Settings/             # System settings
├── Api/Backend/              # Backend APIs
└── GoogleCalendarController.php
```

---

## 📋 Key Workflows

### **1. Tenant Application → Lease → Payment**

**Steps:**
1. Tenant submits application with documents
2. Admin reviews and approves
3. System creates tenant account with JWT authentication
4. Lease created and assigned to specific bed
5. Lease document generated from template with tenant data
6. Admin and tenant sign document digitally
7. Payment schedule and invoices generated
8. Tenant pays via Stripe
9. Move-in confirmed, bed marked as occupied

---

### **2. Dynamic Document Generation**

**Workflow:**
1. Upload Template PDF/DOCX
2. DocumentParsingService Extracts Fields
3. LeaseTemplateFieldMappingService Maps Data Sources
4. Admin Creates Lease for Tenant
5. DynamicDocumentGenerationService Populates Template
6. LeaseDocument Generated as PDF
7. Send for Digital Signatures
8. SignatureAuditLog Tracks Activity
9. Document Status: Signed

---

### **3. Payment Processing**

**Workflow:**
1. Lease Created
2. LeasePaymentSchedule Generated
3. Invoice Issued on Due Date
4. Tenant Pays via Stripe
5. Payment Recorded with Transaction ID
6. Admin Reviews Payment
7. Payment Confirmed
8. Invoice Status: Paid

---

### **4. Real-Time Team Tracking**

**Workflow:**
1. Team Member Opens App
2. GPS Location Captured
3. TeamLocation Record Created
4. WebSocket Broadcast via Reverb
5. Dashboard Updates in Real-Time
6. Google Maps Polyline Rendered

---

## 🛠️ Tech Stack

| Category | Technologies | Purpose |
|----------|--------------|---------|
| **Backend Framework** | Laravel 11, PHP 8.2+ | Core application logic |
| **Database** | MySQL / MariaDB | Primary data storage |
| **Authentication** | Laravel Breeze, Tymon JWT | User & tenant authentication |
| **Authorization** | Spatie Permission | Role-based access control |
| **Payment Processing** | Stripe Connect, Laravel Cashier | Payment gateway, subscriptions |
| **Document Processing** | mPDF, PhpWord, PdfParser, FPDI | PDF/DOCX parsing and generation |
| **Google Integration** | Google Calendar API, Google Maps API | Calendar sync, location tracking |
| **Email** | Webklex IMAP, Gmail IMAP | Email synchronization |
| **Real-Time** | Laravel Reverb, Pusher | WebSocket broadcasting |
| **Frontend** | Bootstrap 5, Tailwind CSS, Alpine.js, Vite | UI components and asset bundling |
| **Calendar UI** | FullCalendar.js | Interactive calendar interface |
| **Data Tables** | Yajra DataTables | Server-side pagination |
| **File Exports** | Maatwebsite Excel | Export reports to Excel/CSV |

---

## 📦 Key Dependencies

```json
"require": {
    "laravel/framework": "^11.31",
    "laravel/breeze": "^2.3",
    "laravel/cashier": "^15.0",
    "laravel/reverb": "^1.5",
    "stripe/stripe-php": "^16.4",
    "spatie/laravel-permission": "^6.24",
    "spatie/laravel-google-calendar": "^3.8",
    "tymon/jwt-auth": "^2.1",
    "yajra/laravel-datatables-oracle": "^11.0",
    "mpdf/mpdf": "^8.2",
    "phpoffice/phpword": "^1.4",
    "smalot/pdfparser": "^2.12",
    "setasign/fpdi": "^2.6",
    "webklex/laravel-imap": "^6.2",
    "maatwebsite/excel": "*",
    "google/apiclient": "^2.0"
}
```

---

## 🚀 Getting Started

### **Prerequisites**

- PHP 8.2 or higher
- Composer 2.x
- Node.js 18+ and npm
- MySQL 8.0+ or MariaDB 10.5+
- Git

### **Installation Steps**

1. **Clone the Repository**
   ```bash
   git clone https://github.com/your-username/oc-workforce-laravel.git
   cd oc-workforce-laravel
   ```

2. **Install PHP Dependencies**
   ```bash
   composer install
   ```

3. **Install Node Dependencies**
   ```bash
   npm install
   ```

4. **Environment Configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure Database** (Edit `.env`)
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=oc_workforce
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Configure Stripe** (Get keys from https://dashboard.stripe.com/apikeys)
   ```env
   STRIPE_KEY=pk_test_xxxxxxxxxxxxx
   STRIPE_SECRET=sk_test_xxxxxxxxxxxxx
   STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxx
   ```

7. **Configure Google APIs** (Get credentials from https://console.cloud.google.com/)
   ```env
   GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
   GOOGLE_CLIENT_SECRET=your-client-secret
   GOOGLE_REDIRECT_URI=http://localhost:8000/google/callback
   ```

8. **Run Database Migrations**
   ```bash
   php artisan migrate --seed
   ```

9. **Link Storage**
   ```bash
   php artisan storage:link
   ```

10. **Install Broadcasting (for WebSocket)**
    ```bash
    php artisan install:broadcasting
    ```

11. **Build Frontend Assets**
    ```bash
    npm run build
    ```

---

## 💻 Development

### **Start Development Server**

Use the convenient Composer script that runs all services concurrently:

```bash
composer dev
```

This runs:
- ✅ Laravel development server (`php artisan serve`)
- ✅ Queue listener (`php artisan queue:listen`)
- ✅ Real-time logs (`php artisan pail`)
- ✅ Vite dev server (`npm run dev`)

### **Manual Service Startup**

If you prefer to run services individually:

```bash
# Terminal 1: Laravel Server
php artisan serve

# Terminal 2: Queue Worker
php artisan queue:listen --tries=1

# Terminal 3: WebSocket Server
php artisan reverb:start

# Terminal 4: Vite (Hot Module Replacement)
npm run dev

# Terminal 5: Real-Time Logs (Optional)
php artisan pail
```

### **Quick Artisan Commands (Browser-Based)**

For development convenience, these routes run artisan commands via browser:

- `/run-migrate-fresh` - Reset database with fresh migrations
- `/run-optimize-clear` - Clear all caches

**⚠️ WARNING: These routes should be disabled in production!**

---

## 🧪 Testing

Run the test suite:

```bash
php artisan test
```

Run specific test file:

```bash
php artisan test --filter=LeaseTest
```

---

## 📖 API Documentation

### **Authentication**

**Admin Login:**
```http
POST /api/login
Content-Type: application/json

{
    "email": "admin@example.com",
    "password": "password"
}
```

**Tenant Login (JWT):**
```http
POST /api/tenant/login
Content-Type: application/json

{
    "email": "tenant@example.com",
    "password": "password"
}
```

### **Key API Endpoints**

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/properties` | GET | List all properties |
| `/api/properties/{id}/available-beds` | GET | Get available beds |
| `/api/applications` | POST | Submit tenant application |
| `/api/leases` | GET | Get tenant leases |
| `/api/invoices` | GET | Get invoices |
| `/api/payments` | POST | Process payment |
| `/api/maintenance-requests` | POST | Submit maintenance request |
| `/api/team-locations` | POST | Update team location |

---

## 📂 Project Structure

```
oc_workforce_laravel/
├── app/
│   ├── Enums/              # PHP 8.2+ Enums
│   ├── Helper/             # Helper functions
│   ├── Http/
│   │   ├── Controllers/    # All controllers
│   │   │   ├── Web/Backend/    # Admin panel
│   │   │   └── Api/Backend/    # API endpoints
│   │   └── Middleware/     # Custom middleware
│   ├── Models/             # Eloquent models
│   └── Services/           # Business logic services
├── config/                 # Configuration files
├── database/
│   ├── migrations/         # Database migrations
│   └── seeders/            # Database seeders
├── public/
│   └── uploads/            # User uploaded files
├── resources/
│   ├── views/              # Blade templates
│   ├── js/                 # JavaScript files
│   └── css/                # CSS files
├── routes/
│   ├── web.php             # Web routes + artisan shortcuts
│   ├── api.php             # API routes
│   ├── auth.php            # Authentication routes
│   └── backend.php         # Admin panel routes
├── storage/
│   ├── app/public/         # Publicly accessible storage
│   └── logs/               # Application logs
├── .env.example            # Environment configuration template
├── composer.json           # PHP dependencies
├── package.json            # Node dependencies
├── vite.config.js          # Vite configuration
└── tailwind.config.js      # Tailwind CSS configuration
```

---

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📄 License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

---

## 📞 Support

For questions or issues:
- **Email**: support@ocworkforce.com
- **GitHub Issues**: [Create an issue](https://github.com/your-username/oc-workforce-laravel/issues)
- **Documentation**: See `LEASE_TEMPLATE_SYSTEM_GUIDE.md` for lease template implementation details

---

## 🙏 Acknowledgments

Built with:
- [Laravel Framework](https://laravel.com/)
- [Stripe](https://stripe.com/) for payment processing
- [Google APIs](https://developers.google.com/) for calendar and maps integration
- [Spatie](https://spatie.be/) for calendar and permission packages
- [Yajra DataTables](https://yajrabox.com/docs/laravel-datatables) for data tables

---

**Made with ❤️ by the OC Workforce Team**




