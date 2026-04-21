# OC Workforce Laravel - API Documentation

## Table of Contents
1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Response Format](#response-format)
4. [CMS Routes](#cms-routes)
5. [Tenant Authentication Routes](#tenant-authentication-routes)
6. [Tenant Profile Routes](#tenant-profile-routes)
7. [Tenant Dashboard Routes](#tenant-dashboard-routes)
8. [Tenant Lease Routes](#tenant-lease-routes)
9. [Tenant Payment Routes](#tenant-payment-routes)
10. [Tenant Maintenance Routes](#tenant-maintenance-routes)
11. [Other Routes](#other-routes)
12. [Error Handling](#error-handling)

---

## Overview

**Base URL:** `http://localhost:8000/api`

**API Version:** v1

The OC Workforce Laravel API provides endpoints for:
- Content Management System (CMS) data
- Tenant authentication and account management
- Lease management and digital signature workflows
- Payment processing and invoice management
- Maintenance request handling
- FAQ and contact form submissions

---

## Authentication

### JWT Token Authentication

The API uses **JWT (JSON Web Token)** authentication for protected routes.

#### How to Get a Token

1. **Submit email** to `/api/v1/tenant/applications/submit-email`
2. **Submit application form** with token
3. **Set password** via `/api/v1/tenant/password/setup`
4. **Login** via `/api/v1/tenant/login` to receive JWT token

#### Using the Token

Include the token in the `Authorization` header for protected routes:

```
Authorization: Bearer <your_jwt_token>
```

#### Token Expiration

- Tokens are issued with a default expiration (check `.env` for `JWT_EXPIRATION`)
- Include refresh logic in frontend to handle expired tokens

---

## Response Format

All API responses follow a consistent JSON structure:

### Success Response (2xx)

```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    "id": 1,
    "name": "John Doe"
  },
  "code": 200
}
```

### Validation Error (422)

```json
{
  "success": false,
  "message": "Validation failed",
  "data": [],
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  },
  "code": 422
}
```

### Error Response (4xx, 5xx)

```json
{
  "status": false,
  "message": "Error description",
  "data": [],
  "code": 401
}
```

---

## CMS Routes

All CMS routes are **public** (no authentication required).

### Get Home Page Data

**Endpoint:** `GET /api/cms/home`

**Description:** Retrieves home page content including hero section, sliders, and featured content.

**Response:**
```json
{
  "success": true,
  "message": "Home page data fetched successfully",
  "data": {
    "hero": {
      "title": "Welcome to OC Workforce",
      "subtitle": "Find Your Perfect Housing",
      "image": "url"
    },
    "sliders": [],
    "testimonials": [],
    "sections": []
  },
  "code": 200
}
```

---

### Get Properties Page Data

**Endpoint:** `GET /api/cms/properties`

**Description:** Retrieves properties listing page content.

**Response:**
```json
{
  "success": true,
  "data": {
    "banner": {
      "title": "Our Properties",
      "image": "url"
    },
    "properties": []
  },
  "code": 200
}
```

---

### Get About Us Page Data

**Endpoint:** `GET /api/cms/about-us`

**Description:** Retrieves about us page content.

**Response:**
```json
{
  "success": true,
  "data": {
    "title": "About Us",
    "description": "...",
    "image": "url",
    "mission": "...",
    "vision": "..."
  },
  "code": 200
}
```

---

### Get Amenities Page Data

**Endpoint:** `GET /api/cms/amenities`

**Description:** Retrieves amenities listing with descriptions and icons.

**Response:**
```json
{
  "success": true,
  "data": {
    "amenities": [
      {
        "id": 1,
        "name": "WiFi",
        "icon": "url",
        "description": "High-speed internet"
      }
    ]
  },
  "code": 200
}
```

---

### Get Pricing Page Data

**Endpoint:** `GET /api/cms/pricing`

**Description:** Retrieves pricing plans and seasonal rates.

**Response:**
```json
{
  "success": true,
  "data": {
    "plans": [
      {
        "id": 1,
        "name": "Standard",
        "price": 1500,
        "features": []
      }
    ]
  },
  "code": 200
}
```

---

### Get Reservation Page Data

**Endpoint:** `GET /api/cms/reservation`

**Description:** Retrieves reservation page content and form fields.

**Response:**
```json
{
  "success": true,
  "data": {
    "title": "Make a Reservation",
    "form_fields": []
  },
  "code": 200
}
```

---

### Get Navigation Data

**Endpoint:** `GET /api/cms/navigation`

**Description:** Retrieves main navigation menu structure.

**Response:**
```json
{
  "success": true,
  "data": {
    "menu": [
      {
        "name": "Home",
        "url": "/",
        "children": []
      }
    ]
  },
  "code": 200
}
```

---

## Tenant Authentication Routes

### Submit Email (Application Start)

**Endpoint:** `POST /api/v1/tenant/applications/submit-email`

**Authentication:** No

**Description:** Initial step - tenant submits email to start application process.

**Request Body:**
```json
{
  "email": "tenant@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Email submitted successfully",
  "data": {
    "message": "Please check your email for next steps"
  },
  "code": 200
}
```

---

### Submit Reservation

**Endpoint:** `POST /api/v1/tenant/applications/submit-reservation`

**Authentication:** No

**Description:** Submit a reservation request with property and room preferences.

**Request Body:**
```json
{
  "email": "tenant@example.com",
  "property_id": 1,
  "unit_id": 2,
  "check_in_date": "2026-05-01",
  "check_out_date": "2026-08-31"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Reservation submitted successfully",
  "data": {
    "reservation_id": 123
  },
  "code": 200
}
```

---

### Submit Application Form (Token-based)

**Endpoint:** `POST /api/v1/tenant/application/form/{token}`

**Authentication:** No

**Description:** Submit complete tenant application form with personal, employment, and emergency contact details.

**Path Parameters:**
- `token` (string) - Application invitation token

**Request Body:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "date_of_birth": "1990-01-01",
  "employment_status": "employed",
  "employer_name": "ABC Corp",
  "emergency_contact_name": "Jane Doe",
  "emergency_contact_phone": "+0987654321"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Application submitted successfully",
  "data": {
    "tenant_id": 1,
    "status": "pending"
  },
  "code": 200
}
```

---

### Tenant Login

**Endpoint:** `POST /api/v1/tenant/login`

**Authentication:** No

**Description:** Login with email and password to receive JWT token.

**Request Body:**
```json
{
  "email": "tenant@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "tenant": {
      "id": 1,
      "email": "tenant@example.com",
      "status": "approved",
      "profile": {
        "first_name": "John",
        "last_name": "Doe"
      }
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer"
  },
  "code": 200
}
```

---

### Tenant Logout

**Endpoint:** `POST /api/v1/tenant/logout`

**Authentication:** Required (JWT)

**Description:** Logout and invalidate current JWT token.

**Response:**
```json
{
  "success": true,
  "message": "Logged out successfully",
  "data": [],
  "code": 200
}
```

---

## Tenant Password Routes

### Set Password After Approval

**Endpoint:** `POST /api/v1/tenant/password/setup`

**Authentication:** No

**Description:** Set password for the first time after admin approval. Requires approval token.

**Request Body:**
```json
{
  "email": "tenant@example.com",
  "token": "approval_token_from_email",
  "password": "newPassword123",
  "password_confirmation": "newPassword123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password set successfully",
  "data": [],
  "code": 200
}
```

---

### Send Forgot Password OTP

**Endpoint:** `POST /api/v1/tenant/password/forgot/send-otp`

**Authentication:** No

**Description:** Request OTP for password reset.

**Request Body:**
```json
{
  "email": "tenant@example.com"
}
```

**Response:**
```json
{
  "success": true,
  "message": "OTP sent to your email",
  "data": {
    "email": "tenant@example.com"
  },
  "code": 200
}
```

---

### Verify OTP

**Endpoint:** `POST /api/v1/tenant/password/forgot/verify-otp`

**Authentication:** No

**Description:** Verify the OTP received via email.

**Request Body:**
```json
{
  "email": "tenant@example.com",
  "otp": "123456"
}
```

**Response:**
```json
{
  "success": true,
  "message": "OTP verified successfully",
  "data": {
    "token": "reset_token_for_next_step"
  },
  "code": 200
}
```

---

### Reset Password

**Endpoint:** `POST /api/v1/tenant/password/reset`

**Authentication:** No

**Description:** Reset password using OTP verification token.

**Request Body:**
```json
{
  "email": "tenant@example.com",
  "token": "token_from_otp_verification",
  "password": "newPassword123",
  "password_confirmation": "newPassword123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password reset successfully",
  "data": [],
  "code": 200
}
```

---

## Tenant Profile Routes

### Get Tenant Profile

**Endpoint:** `GET /api/v1/tenant/profile`

**Authentication:** Required (JWT)

**Description:** Retrieve complete tenant profile with all related information.

**Response:**
```json
{
  "success": true,
  "message": "Tenant profile data fetched successfully",
  "data": {
    "tenant": {
      "id": 1,
      "email": "tenant@example.com",
      "profile": {
        "first_name": "John",
        "middle_name": "Michael",
        "last_name": "Doe",
        "phone": "+1234567890"
      },
      "address": {
        "street": "123 Main St",
        "city": "Los Angeles",
        "state": "CA",
        "zip": "90001"
      },
      "employments": [
        {
          "employer_name": "ABC Corp",
          "job_title": "Developer"
        }
      ],
      "emergency_contacts": [
        {
          "name": "Jane Doe",
          "phone": "+0987654321"
        }
      ],
      "documents": [
        {
          "id": 1,
          "type": "id_proof",
          "document_url": "url"
        }
      ]
    }
  },
  "code": 200
}
```

---

### Update Tenant Profile

**Endpoint:** `PUT /api/v1/tenant/update-profile`

**Authentication:** Required (JWT)

**Description:** Update tenant profile information.

**Request Body:**
```json
{
  "first_name": "John",
  "middle_name": "Michael",
  "last_name": "Doe",
  "phone": "+1234567890",
  "country_code": "+1"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    "profile": {
      "first_name": "John",
      "last_name": "Doe",
      "phone": "+1234567890"
    }
  },
  "code": 200
}
```

---

### Update Avatar

**Endpoint:** `POST /api/v1/tenant/update-avatar`

**Authentication:** Required (JWT)

**Description:** Upload and update tenant profile picture.

**Request Body:**
```
Form Data:
- avatar: File (image/jpeg, image/png)
```

**Response:**
```json
{
  "success": true,
  "message": "Avatar updated successfully",
  "data": {
    "avatar_url": "url_to_image"
  },
  "code": 200
}
```

---

### Change Password

**Endpoint:** `POST /api/v1/tenant/change-password`

**Authentication:** Required (JWT)

**Description:** Change tenant password (while logged in).

**Request Body:**
```json
{
  "current_password": "oldPassword123",
  "new_password": "newPassword123",
  "new_password_confirmation": "newPassword123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Password changed successfully",
  "data": [],
  "code": 200
}
```

---

### Delete Profile

**Endpoint:** `DELETE /api/v1/tenant/delete-profile`

**Authentication:** Required (JWT)

**Description:** Permanently delete tenant account and associated data.

**Response:**
```json
{
  "success": true,
  "message": "Profile deleted successfully",
  "data": [],
  "code": 200
}
```

---

## Tenant Dashboard Routes

### Check Active Lease

**Endpoint:** `GET /api/v1/tenant/has-active-lease`

**Authentication:** Required (JWT)

**Description:** Check if tenant has an active lease.

**Response:**
```json
{
  "success": true,
  "message": "Active lease check completed",
  "data": {
    "has_active_lease": true,
    "lease_id": 1
  },
  "code": 200
}
```

---

### Get Dashboard Data

**Endpoint:** `GET /api/v1/tenant/dashboard`

**Authentication:** Required (JWT)

**Description:** Get comprehensive dashboard data including leases, payments, and announcements.

**Response:**
```json
{
  "success": true,
  "message": "Dashboard data fetched successfully",
  "data": {
    "overview": {
      "active_leases": 1,
      "pending_invoices": 2,
      "total_rent_paid": 5000
    },
    "announcements": [],
    "quick_actions": []
  },
  "code": 200
}
```

---

### Get Documents

**Endpoint:** `GET /api/v1/tenant/documents`

**Authentication:** Required (JWT)

**Description:** Retrieve list of tenant documents (lease agreements, etc.).

**Response:**
```json
{
  "success": true,
  "message": "Documents fetched successfully",
  "data": {
    "documents": [
      {
        "id": 1,
        "type": "lease",
        "name": "Lease Agreement 2024",
        "document_url": "url",
        "created_at": "2024-01-01"
      }
    ]
  },
  "code": 200
}
```

---

### Download PDF Document

**Endpoint:** `GET /api/v1/tenant/0/{id}/download-pdf`

**Authentication:** Required (JWT)

**Description:** Download lease or other documents as PDF.

**Path Parameters:**
- `id` (integer) - Document ID

**Response:** Binary PDF file

---

## Tenant Lease Routes

### Get Leases List

**Endpoint:** `GET /api/v1/tenant/leases`

**Authentication:** Required (JWT)

**Description:** Get list of all tenant leases (active and inactive).

**Query Parameters:**
- `per_page` (integer, optional) - Items per page (default: 15)
- `page` (integer, optional) - Page number (default: 1)
- `status` (string, optional) - Filter by status (active, inactive, expired)

**Response:**
```json
{
  "success": true,
  "message": "Leases fetched successfully",
  "data": {
    "leases": [
      {
        "id": 1,
        "lease_number": "LEASE-2024-001",
        "property": {
          "name": "Downtown Apartments",
          "address": "123 Main St"
        },
        "start_date": "2024-01-01",
        "end_date": "2024-12-31",
        "status": "active",
        "monthly_rent": 1500
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 5
    }
  },
  "code": 200
}
```

---

### Get Lease Details

**Endpoint:** `GET /api/v1/tenant/leases/{leaseId}`

**Authentication:** Required (JWT)

**Description:** Get detailed information about a specific lease.

**Path Parameters:**
- `leaseId` (integer) - Lease ID

**Response:**
```json
{
  "success": true,
  "message": "Lease details fetched",
  "data": {
    "lease": {
      "id": 1,
      "lease_number": "LEASE-2024-001",
      "property": {
        "id": 1,
        "name": "Downtown Apartments"
      },
      "unit": {
        "id": 1,
        "name": "Unit A"
      },
      "room": {
        "id": 1,
        "name": "Bedroom 1"
      },
      "bed": {
        "id": 1,
        "bed_number": "A1"
      },
      "start_date": "2024-01-01",
      "end_date": "2024-12-31",
      "monthly_rent": 1500,
      "deposit": 3000,
      "tenants": [
        {
          "id": 1,
          "name": "John Doe",
          "email": "john@example.com"
        }
      ],
      "status": "active"
    }
  },
  "code": 200
}
```

---

## Tenant Invoice Routes

### Get Invoices List

**Endpoint:** `GET /api/v1/tenant/invoices`

**Authentication:** Required (JWT)

**Description:** Get list of tenant invoices/rent bills.

**Query Parameters:**
- `per_page` (integer, optional) - Items per page
- `page` (integer, optional) - Page number
- `status` (string, optional) - Filter by status (paid, pending, overdue)

**Response:**
```json
{
  "success": true,
  "message": "Invoices fetched successfully",
  "data": {
    "invoices": [
      {
        "id": 1,
        "invoice_number": "INV-2024-001",
        "lease_id": 1,
        "amount": 1500,
        "due_date": "2024-02-01",
        "status": "pending",
        "created_at": "2024-01-01"
      }
    ],
    "pagination": {}
  },
  "code": 200
}
```

---

### Get Invoice Details

**Endpoint:** `GET /api/v1/tenant/invoices/{invoiceId}`

**Authentication:** Required (JWT)

**Description:** Get detailed information about a specific invoice.

**Path Parameters:**
- `invoiceId` (integer) - Invoice ID

**Response:**
```json
{
  "success": true,
  "message": "Invoice details fetched",
  "data": {
    "invoice": {
      "id": 1,
      "invoice_number": "INV-2024-001",
      "lease_id": 1,
      "property_name": "Downtown Apartments",
      "amount": 1500,
      "due_date": "2024-02-01",
      "status": "pending",
      "items": [
        {
          "description": "Monthly Rent",
          "amount": 1500
        }
      ],
      "created_at": "2024-01-01"
    }
  },
  "code": 200
}
```

---

## Tenant Payment Routes

### Get Payment History

**Endpoint:** `GET /api/v1/tenant/payments`

**Authentication:** Required (JWT)

**Description:** Get payment history and transactions.

**Query Parameters:**
- `per_page` (integer, optional)
- `page` (integer, optional)

**Response:**
```json
{
  "success": true,
  "message": "Payment history fetched",
  "data": {
    "payments": [
      {
        "id": 1,
        "invoice_id": 1,
        "amount": 1500,
        "payment_date": "2024-02-01",
        "payment_method": "card",
        "status": "completed"
      }
    ]
  },
  "code": 200
}
```

---

### Get Transactions

**Endpoint:** `GET /api/v1/tenant/transactions`

**Authentication:** Required (JWT)

**Description:** Get detailed transaction history.

**Response:**
```json
{
  "success": true,
  "message": "Transactions fetched",
  "data": {
    "transactions": [
      {
        "id": 1,
        "type": "payment",
        "description": "Rent Payment",
        "amount": 1500,
        "transaction_date": "2024-02-01",
        "status": "completed"
      }
    ]
  },
  "code": 200
}
```

---

### Get Payment Details

**Endpoint:** `GET /api/v1/tenant/payments/invoice/{invoiceId}/details`

**Authentication:** Required (JWT)

**Description:** Get detailed payment information for an invoice.

**Path Parameters:**
- `invoiceId` (integer) - Invoice ID

**Response:**
```json
{
  "success": true,
  "message": "Payment details fetched",
  "data": {
    "invoice_id": 1,
    "invoice_number": "INV-2024-001",
    "amount": 1500,
    "paid_amount": 0,
    "remaining_amount": 1500,
    "due_date": "2024-02-01",
    "payment_link": "https://checkout.stripe.com/..."
  },
  "code": 200
}
```

---

### Create Checkout Session (Stripe)

**Endpoint:** `POST /api/v1/tenant/payments/checkout/create`

**Authentication:** Required (JWT)

**Description:** Create Stripe checkout session for payment.

**Request Body:**
```json
{
  "invoice_id": 1,
  "amount": 1500,
  "return_url": "https://app.example.com/payment-success"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Checkout session created",
  "data": {
    "session_id": "cs_test_...",
    "checkout_url": "https://checkout.stripe.com/..."
  },
  "code": 200
}
```

---

### Verify Payment

**Endpoint:** `POST /api/v1/tenant/payments/verify`

**Authentication:** Required (JWT)

**Description:** Verify payment completion (development only).

**Request Body:**
```json
{
  "invoice_id": 1,
  "payment_intent_id": "pi_..."
}
```

**Response:**
```json
{
  "success": true,
  "message": "Payment verified",
  "data": {
    "status": "completed"
  },
  "code": 200
}
```

---

## Tenant Lease Signing Routes

### Get Lease Document

**Endpoint:** `GET /api/v1/tenant/lease-signing/{leaseId}/document`

**Authentication:** Required (JWT)

**Description:** Get lease document for review and signing.

**Path Parameters:**
- `leaseId` (integer) - Lease ID

**Response:**
```json
{
  "success": true,
  "message": "Lease document fetched",
  "data": {
    "lease_id": 1,
    "document_html": "<html>...</html>",
    "custom_fields": [
      {
        "name": "signature_date",
        "label": "Signature Date",
        "type": "date"
      }
    ]
  },
  "code": 200
}
```

---

### Preview Document

**Endpoint:** `GET /api/v1/tenant/lease-signing/{leaseId}/preview`

**Authentication:** Required (JWT)

**Description:** Preview lease document before signing.

**Path Parameters:**
- `leaseId` (integer) - Lease ID

**Response:**
```json
{
  "success": true,
  "message": "Document preview fetched",
  "data": {
    "lease_id": 1,
    "document_url": "url_to_preview"
  },
  "code": 200
}
```

---

### Check Signing Eligibility

**Endpoint:** `GET /api/v1/tenant/lease-signing/{leaseId}/eligibility`

**Authentication:** Required (JWT)

**Description:** Check if lease is ready for signing (all approvals, documents, etc.).

**Path Parameters:**
- `leaseId` (integer) - Lease ID

**Response:**
```json
{
  "success": true,
  "message": "Eligibility check completed",
  "data": {
    "is_eligible": true,
    "reasons": [],
    "requires": []
  },
  "code": 200
}
```

---

### Sign Lease

**Endpoint:** `POST /api/v1/tenant/lease-signing/{leaseId}/sign`

**Authentication:** Required (JWT)

**Description:** Sign lease digitally with signature capture.

**Path Parameters:**
- `leaseId` (integer) - Lease ID

**Request Body:**
```json
{
  "signature_image": "data:image/png;base64,...",
  "signature_date": "2024-01-15",
  "ip_address": "192.168.1.1",
  "user_agent": "Mozilla/5.0..."
}
```

**Response:**
```json
{
  "success": true,
  "message": "Lease signed successfully",
  "data": {
    "lease_id": 1,
    "signed_at": "2024-01-15 10:30:00",
    "signature_audit_id": 1
  },
  "code": 200
}
```

---

### Update Custom Fields

**Endpoint:** `POST /api/v1/tenant/lease-signing/{leaseId}/custom-fields`

**Authentication:** Required (JWT)

**Description:** Save custom text input fields in lease document.

**Path Parameters:**
- `leaseId` (integer) - Lease ID

**Request Body:**
```json
{
  "fields": {
    "signature_date": "2024-01-15",
    "initial_date": "2024-01-15"
  }
}
```

**Response:**
```json
{
  "success": true,
  "message": "Custom fields saved",
  "data": [],
  "code": 200
}
```

---

### Download Lease Document

**Endpoint:** `GET /api/v1/tenant/lease-signing/{leaseId}/download`

**Authentication:** Required (JWT)

**Description:** Download signed lease as PDF.

**Path Parameters:**
- `leaseId` (integer) - Lease ID

**Response:** Binary PDF file

---

## Tenant Maintenance Routes

### Get Tenant Leases (Hierarchy)

**Endpoint:** `GET /api/v1/tenant/maintanance/my-leases`

**Authentication:** Required (JWT)

**Description:** Get all leases for tenant (for maintenance request hierarchy).

**Response:**
```json
{
  "success": true,
  "message": "Leases fetched",
  "data": {
    "leases": [
      {
        "id": 1,
        "lease_number": "LEASE-2024-001",
        "property_id": 1
      }
    ]
  },
  "code": 200
}
```

---

### Get Units by Lease

**Endpoint:** `GET /api/v1/tenant/maintanance/lease/{leaseId}/units`

**Authentication:** Required (JWT)

**Description:** Get units under a lease for maintenance location selection.

**Path Parameters:**
- `leaseId` (integer) - Lease ID

**Response:**
```json
{
  "success": true,
  "message": "Units fetched",
  "data": {
    "units": [
      {
        "id": 1,
        "name": "Unit A"
      }
    ]
  },
  "code": 200
}
```

---

### Get Rooms by Unit

**Endpoint:** `GET /api/v1/tenant/maintanance/unit/{unitId}/rooms`

**Authentication:** Required (JWT)

**Description:** Get rooms under a unit.

**Path Parameters:**
- `unitId` (integer) - Unit ID

**Response:**
```json
{
  "success": true,
  "message": "Rooms fetched",
  "data": {
    "rooms": [
      {
        "id": 1,
        "name": "Bedroom 1"
      }
    ]
  },
  "code": 200
}
```

---

### Get Beds by Room

**Endpoint:** `GET /api/v1/tenant/maintanance/room/{roomId}/beds`

**Authentication:** Required (JWT)

**Description:** Get beds under a room.

**Path Parameters:**
- `roomId` (integer) - Room ID

**Response:**
```json
{
  "success": true,
  "message": "Beds fetched",
  "data": {
    "beds": [
      {
        "id": 1,
        "bed_number": "A1"
      }
    ]
  },
  "code": 200
}
```

---

### List Maintenance Requests

**Endpoint:** `GET /api/v1/tenant/maintanance/list`

**Authentication:** Required (JWT)

**Description:** Get list of maintenance requests submitted by tenant.

**Query Parameters:**
- `per_page` (integer, optional)
- `page` (integer, optional)
- `status` (string, optional) - Filter by status (open, in_progress, completed)

**Response:**
```json
{
  "success": true,
  "message": "Maintenance requests fetched",
  "data": {
    "requests": [
      {
        "id": 1,
        "title": "Broken Faucet",
        "description": "Kitchen sink faucet is leaking",
        "property": "Downtown Apartments",
        "unit": "Unit A",
        "status": "open",
        "priority": "high",
        "created_at": "2024-01-10"
      }
    ],
    "pagination": {}
  },
  "code": 200
}
```

---

### Create Maintenance Request

**Endpoint:** `POST /api/v1/tenant/maintanance/store`

**Authentication:** Required (JWT)

**Description:** Submit new maintenance request.

**Request Body:**
```json
{
  "title": "Broken Faucet",
  "description": "Kitchen sink faucet is leaking",
  "lease_id": 1,
  "unit_id": 1,
  "room_id": 1,
  "bed_id": 1,
  "priority": "high",
  "attachments": []
}
```

**Response:**
```json
{
  "success": true,
  "message": "Maintenance request created",
  "data": {
    "request_id": 1,
    "status": "open"
  },
  "code": 200
}
```

---

### Get Maintenance Request Details

**Endpoint:** `GET /api/v1/tenant/maintanance/edit/{maintananceId}`

**Authentication:** Required (JWT)

**Description:** Get detailed information about a maintenance request.

**Path Parameters:**
- `maintananceId` (integer) - Maintenance Request ID

**Response:**
```json
{
  "success": true,
  "message": "Maintenance request fetched",
  "data": {
    "request": {
      "id": 1,
      "title": "Broken Faucet",
      "description": "Kitchen sink faucet is leaking",
      "status": "open",
      "priority": "high",
      "created_at": "2024-01-10",
      "attachments": [],
      "notes": []
    }
  },
  "code": 200
}
```

---

### Update Maintenance Request

**Endpoint:** `POST /api/v1/tenant/maintanance/update/{maintananceId}`

**Authentication:** Required (JWT)

**Description:** Update maintenance request details.

**Path Parameters:**
- `maintananceId` (integer) - Maintenance Request ID

**Request Body:**
```json
{
  "title": "Broken Faucet - Updated",
  "description": "Kitchen sink faucet is leaking - water pressure is decreasing",
  "priority": "critical"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Maintenance request updated",
  "data": {
    "request_id": 1
  },
  "code": 200
}
```

---

### Delete Maintenance Request

**Endpoint:** `DELETE /api/v1/tenant/maintanance/delete/{maintananceId}`

**Authentication:** Required (JWT)

**Description:** Delete a maintenance request.

**Path Parameters:**
- `maintananceId` (integer) - Maintenance Request ID

**Response:**
```json
{
  "success": true,
  "message": "Maintenance request deleted",
  "data": [],
  "code": 200
}
```

---

### Get Property Details

**Endpoint:** `GET /api/v1/tenant/maintanance/property/{propertyId}/details`

**Authentication:** Required (JWT)

**Description:** Get property details for maintenance context.

**Path Parameters:**
- `propertyId` (integer) - Property ID

**Response:**
```json
{
  "success": true,
  "message": "Property details fetched",
  "data": {
    "property": {
      "id": 1,
      "name": "Downtown Apartments",
      "address": "123 Main St",
      "city": "Los Angeles",
      "state": "CA"
    }
  },
  "code": 200
}
```

---

### Get Properties

**Endpoint:** `GET /api/v1/tenant/maintanance/properties`

**Authentication:** Required (JWT)

**Description:** Get all properties accessible to tenant.

**Response:**
```json
{
  "success": true,
  "message": "Properties fetched",
  "data": {
    "properties": [
      {
        "id": 1,
        "name": "Downtown Apartments"
      },
      {
        "id": 2,
        "name": "Uptown Suites"
      }
    ]
  },
  "code": 200
}
```

---

## Other Routes

### Get Properties for Application Forms

**Endpoint:** `GET /api/v1/properties`

**Authentication:** No

**Description:** Get dropdown list of properties for tenant application forms.

**Response:**
```json
{
  "success": true,
  "message": "Properties fetched",
  "data": {
    "properties": [
      {
        "id": 1,
        "name": "Downtown Apartments"
      }
    ]
  },
  "code": 200
}
```

---

### Get FAQs

**Endpoint:** `GET /api/faqs`

**Authentication:** Required (JWT)

**Description:** Get frequently asked questions.

**Response:**
```json
{
  "success": true,
  "message": "FAQs fetched",
  "data": {
    "faqs": [
      {
        "id": 1,
        "question": "How do I apply for a lease?",
        "answer": "You can apply by...",
        "category": "application"
      }
    ]
  },
  "code": 200
}
```

---

### Submit Contact Form

**Endpoint:** `POST /api/submit-contact`

**Authentication:** No

**Description:** Submit contact form inquiry.

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "subject": "Inquiry about properties",
  "message": "I would like to know more about your properties..."
}
```

**Response:**
```json
{
  "success": true,
  "message": "Contact form submitted successfully",
  "data": {
    "message_id": 1
  },
  "code": 200
}
```

---

## Health Check

### API Health Status

**Endpoint:** `GET /api/health`

**Authentication:** No

**Description:** Check if API is running and accessible.

**Response:**
```json
{
  "success": true,
  "message": "API is running",
  "timestamp": "2024-01-15T10:30:00Z"
}
```

---

## Error Handling

### Common HTTP Status Codes

| Code | Status | Description |
|------|--------|-------------|
| 200 | OK | Request successful |
| 201 | Created | Resource created successfully |
| 204 | No Content | Request successful with no content |
| 400 | Bad Request | Invalid request parameters |
| 401 | Unauthorized | Missing or invalid authentication |
| 403 | Forbidden | Authenticated but not permitted |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation error |
| 500 | Server Error | Internal server error |

### Error Examples

#### Validation Error (422)

```json
{
  "success": false,
  "message": "Validation failed",
  "data": [],
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password must be at least 8 characters."]
  },
  "code": 422
}
```

#### Authentication Error (401)

```json
{
  "status": false,
  "message": "Unauthorized: Invalid token",
  "data": [],
  "code": 401
}
```

#### Not Found Error (404)

```json
{
  "status": false,
  "message": "Resource not found",
  "data": [],
  "code": 404
}
```

#### Server Error (500)

```json
{
  "status": false,
  "message": "Internal server error",
  "data": [],
  "code": 500
}
```

---

## Authentication Best Practices

### Token Storage

- **Web**: Store in httpOnly cookies (more secure)
- **Mobile**: Store securely in device keychain/keystore
- **Never** store tokens in localStorage or SessionStorage (vulnerable to XSS)

### Token Refresh

Most JWT tokens have an expiration time. Implement token refresh:

```javascript
// Check if token is about to expire
if (tokenExpiresIn < 5 * 60) {  // 5 minutes
  // Request new token from server
  POST /api/v1/tenant/auth/refresh
}
```

### CORS Configuration

The API supports CORS. Include credentials when making cross-origin requests:

```javascript
fetch('/api/v1/tenant/profile', {
  headers: {
    'Authorization': 'Bearer ' + token
  },
  credentials: 'include'  // Include cookies
})
```

---

## Rate Limiting

Currently, there are no strict rate limits implemented. However, best practices suggest:

- Implement exponential backoff for failed requests
- Cache responses where possible
- Avoid making unnecessary API calls

---

## Versioning

The API uses URL-based versioning:

- Current version: **v1** (`/api/v1/`)
- Public routes (CMS): No versioning prefix (`/api/cms/`, `/api/submit-contact`)

Future versions will use `/api/v2/`, etc.

---

## Support & Documentation

For additional support:

1. Check the Laravel logs: `storage/logs/laravel.log`
2. Enable debug mode in `.env`: `APP_DEBUG=true`
3. Use `php artisan pail` for real-time log viewing
4. Review controller files in `app/Http/Controllers/Api/`

---

## Changelog

**v1.0.0** - Initial API release
- CMS endpoints
- Tenant authentication (JWT)
- Profile management
- Lease management
- Payment processing (Stripe)
- Maintenance requests
- Digital lease signing
