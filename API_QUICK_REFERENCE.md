# OC Workforce Laravel - API Quick Reference Guide

## Quick Endpoint Reference

### 🔐 Authentication & Account Routes

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/v1/tenant/applications/submit-email` | ❌ | Submit email to start application |
| POST | `/api/v1/tenant/applications/submit-reservation` | ❌ | Submit reservation request |
| POST | `/api/v1/tenant/application/form/{token}` | ❌ | Submit complete application form |
| POST | `/api/v1/tenant/login` | ❌ | Tenant login (get JWT token) |
| POST | `/api/v1/tenant/logout` | ✅ | Tenant logout |
| POST | `/api/v1/tenant/password/setup` | ❌ | Set password after approval |
| POST | `/api/v1/tenant/password/forgot/send-otp` | ❌ | Send forgot password OTP |
| POST | `/api/v1/tenant/password/forgot/verify-otp` | ❌ | Verify OTP |
| POST | `/api/v1/tenant/password/reset` | ❌ | Reset password with token |

### 👤 Tenant Profile Routes

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/v1/tenant/profile` | ✅ | Get complete profile |
| PUT | `/api/v1/tenant/update-profile` | ✅ | Update profile info |
| POST | `/api/v1/tenant/update-avatar` | ✅ | Upload profile picture |
| POST | `/api/v1/tenant/change-password` | ✅ | Change password |
| DELETE | `/api/v1/tenant/delete-profile` | ✅ | Delete account |

### 📊 Dashboard Routes

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/v1/tenant/has-active-lease` | ✅ | Check for active lease |
| GET | `/api/v1/tenant/dashboard` | ✅ | Get dashboard overview |
| GET | `/api/v1/tenant/documents` | ✅ | Get documents list |
| GET | `/api/v1/tenant/0/{id}/download-pdf` | ✅ | Download document PDF |

### 📜 Lease Routes

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/v1/tenant/leases` | ✅ | List all leases |
| GET | `/api/v1/tenant/leases/{leaseId}` | ✅ | Get lease details |

### 💰 Invoice Routes

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/v1/tenant/invoices` | ✅ | List invoices |
| GET | `/api/v1/tenant/invoices/{invoiceId}` | ✅ | Get invoice details |

### 💳 Payment Routes

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/v1/tenant/payments` | ✅ | Get payment history |
| GET | `/api/v1/tenant/transactions` | ✅ | Get transactions |
| GET | `/api/v1/tenant/payments/invoice/{invoiceId}/details` | ✅ | Get payment details |
| POST | `/api/v1/tenant/payments/checkout/create` | ✅ | Create Stripe checkout |
| POST | `/api/v1/tenant/payments/verify` | ✅ | Verify payment (dev) |

### 📝 Lease Signing Routes

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/v1/tenant/lease-signing/{leaseId}/document` | ✅ | Get document for signing |
| GET | `/api/v1/tenant/lease-signing/{leaseId}/preview` | ✅ | Preview document |
| GET | `/api/v1/tenant/lease-signing/{leaseId}/eligibility` | ✅ | Check signing eligibility |
| POST | `/api/v1/tenant/lease-signing/{leaseId}/sign` | ✅ | Sign lease digitally |
| POST | `/api/v1/tenant/lease-signing/{leaseId}/custom-fields` | ✅ | Update custom fields |
| GET | `/api/v1/tenant/lease-signing/{leaseId}/download` | ✅ | Download signed lease |

### 🔧 Maintenance Routes

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/v1/tenant/maintanance/my-leases` | ✅ | Get tenant leases |
| GET | `/api/v1/tenant/maintanance/lease/{leaseId}/units` | ✅ | Get units by lease |
| GET | `/api/v1/tenant/maintanance/unit/{unitId}/rooms` | ✅ | Get rooms by unit |
| GET | `/api/v1/tenant/maintanance/room/{roomId}/beds` | ✅ | Get beds by room |
| GET | `/api/v1/tenant/maintanance/list` | ✅ | List maintenance requests |
| POST | `/api/v1/tenant/maintanance/store` | ✅ | Create maintenance request |
| GET | `/api/v1/tenant/maintanance/edit/{maintananceId}` | ✅ | Get maintenance details |
| POST | `/api/v1/tenant/maintanance/update/{maintananceId}` | ✅ | Update maintenance |
| DELETE | `/api/v1/tenant/maintanance/delete/{maintananceId}` | ✅ | Delete maintenance |
| GET | `/api/v1/tenant/maintanance/property/{propertyId}/details` | ✅ | Get property details |
| GET | `/api/v1/tenant/maintanance/properties` | ✅ | Get all properties |

### 🎨 CMS Routes (Public)

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/cms/home` | ❌ | Home page data |
| GET | `/api/cms/properties` | ❌ | Properties page data |
| GET | `/api/cms/about-us` | ❌ | About us page data |
| GET | `/api/cms/amenities` | ❌ | Amenities page data |
| GET | `/api/cms/pricing` | ❌ | Pricing page data |
| GET | `/api/cms/reservation` | ❌ | Reservation page data |
| GET | `/api/cms/navigation` | ❌ | Navigation menu data |

### 📧 Other Routes

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/submit-contact` | ❌ | Submit contact form |
| GET | `/api/v1/properties` | ❌ | Properties for forms |
| GET | `/api/faqs` | ✅ | Get FAQs |
| GET | `/api/health` | ❌ | Health check |

---

## Common Request Examples

### Login & Get Token

```bash
curl -X POST http://localhost:8000/api/v1/tenant/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "tenant@example.com",
    "password": "password123"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "tenant": {...},
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer"
  },
  "code": 200
}
```

---

### Access Protected Endpoint

```bash
curl -X GET http://localhost:8000/api/v1/tenant/profile \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc..."
```

---

### Upload Avatar

```bash
curl -X POST http://localhost:8000/api/v1/tenant/update-avatar \
  -H "Authorization: Bearer <token>" \
  -F "avatar=@/path/to/image.jpg"
```

---

### Submit Maintenance Request

```bash
curl -X POST http://localhost:8000/api/v1/tenant/maintanance/store \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Broken Faucet",
    "description": "Kitchen sink faucet is leaking",
    "lease_id": 1,
    "unit_id": 1,
    "room_id": 1,
    "priority": "high"
  }'
```

---

### Create Stripe Payment Session

```bash
curl -X POST http://localhost:8000/api/v1/tenant/payments/checkout/create \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "invoice_id": 1,
    "amount": 1500,
    "return_url": "https://app.example.com/payment-success"
  }'
```

---

## Status Codes Quick Reference

| Code | Meaning | Common Cause |
|------|---------|------------|
| 200 | OK | Request successful |
| 201 | Created | Resource created |
| 204 | No Content | Success, no response body |
| 400 | Bad Request | Invalid parameters |
| 401 | Unauthorized | Missing/invalid token |
| 403 | Forbidden | Not permitted |
| 404 | Not Found | Resource doesn't exist |
| 422 | Validation Error | Invalid data format |
| 500 | Server Error | Internal error |

---

## Response Structure

### Success Response (default)
```
{
  "success": true,           // Boolean
  "message": "...",          // Human-readable message
  "data": {...},             // Response payload
  "code": 200                // HTTP status code
}
```

### Validation Error
```
{
  "success": false,
  "message": "Validation failed",
  "data": [],
  "errors": {
    "field_name": ["Error message"]
  },
  "code": 422
}
```

### Error Response
```
{
  "status": false,           // Different from "success"!
  "message": "Error message",
  "data": [],
  "code": 500
}
```

---

## Pagination Parameters

For list endpoints (leases, invoices, maintenance, etc.):

```bash
GET /api/v1/tenant/leases?page=1&per_page=15&status=active
```

**Query Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15)
- `status` - Filter by status
- `sort_by` - Sort field
- `sort_order` - asc or desc

**Response includes:**
```json
{
  "data": {...},
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 50,
    "last_page": 4
  }
}
```

---

## Token Management

### Storing Tokens (Web)

```javascript
// Set httpOnly cookie (server-side)
res.cookie('auth_token', token, {
  httpOnly: true,
  secure: true,
  sameSite: 'Strict',
  maxAge: 7 * 24 * 60 * 60 * 1000  // 7 days
});
```

### Using Tokens in Requests

```javascript
// Fetch with token
const response = await fetch('/api/v1/tenant/profile', {
  headers: {
    'Authorization': `Bearer ${token}`
  },
  credentials: 'include'  // Include cookies
});
```

### Axios with Token

```javascript
// Set default header
axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

// Or per request
axios.get('/api/v1/tenant/profile', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});
```

---

## File Upload Routes

| Endpoint | Method | Field | Accepted Types |
|----------|--------|-------|-----------------|
| `/api/v1/tenant/update-avatar` | POST | avatar | image/jpeg, image/png |
| `/api/v1/tenant/maintanance/store` | POST | attachments[] | image/*, application/pdf |

---

## Error Handling Best Practices

### Check Response Status

```javascript
const response = await fetch('/api/v1/tenant/profile', {
  headers: { 'Authorization': `Bearer ${token}` }
});

const data = await response.json();

if (!response.ok) {
  // Handle error
  if (response.status === 401) {
    // Refresh token or redirect to login
  } else if (response.status === 422) {
    // Validation errors in data.errors
  } else {
    // Generic error in data.message
  }
}
```

### Handle Network Errors

```javascript
try {
  const response = await fetch('/api/v1/tenant/profile', {
    headers: { 'Authorization': `Bearer ${token}` }
  });
  const data = await response.json();
  // Process data
} catch (error) {
  if (error.name === 'TypeError') {
    // Network error
  }
}
```

---

## Development Endpoints

Some endpoints are marked **"Dev Only"** and should not be used in production:

- `POST /api/v1/tenant/payments/verify` - Use webhook verification in production

---

## Rate Limiting Notes

While not currently enforced, implement these best practices:

1. **Exponential Backoff**: Wait longer between retries (1s, 2s, 4s, 8s...)
2. **Caching**: Cache GET responses where appropriate
3. **Batch Operations**: Use bulk endpoints when available
4. **Connection Pooling**: Reuse HTTP connections

---

## Testing Credentials (Development)

Use these for testing:

```
Email: test@example.com
Password: Password@123
```

Or create test tenants via admin panel.

---

## Useful Tools

### cURL
```bash
curl -X GET http://localhost:8000/api/health
```

### Postman
- Import API documentation into Postman
- Create environment variables for `base_url` and `token`
- Use pre-request scripts to refresh tokens

### HTTPie
```bash
http GET http://localhost:8000/api/health
http POST http://localhost:8000/api/v1/tenant/login email=test@example.com password=pass
```

### VS Code REST Client
Create `.http` file:
```
### Get Profile
GET http://localhost:8000/api/v1/tenant/profile
Authorization: Bearer <token>
```

---

## Troubleshooting

### "Invalid token" (401)

**Possible causes:**
- Token expired - refresh token
- Token malformed - check format `Bearer <token>`
- Wrong secret in `.env` - check `JWT_SECRET`
- User deleted - create new account

### "Validation failed" (422)

**Solution:**
- Check `errors` object in response for specific field errors
- Ensure required fields are present
- Match data types (email must be valid email, dates must be ISO format)

### "Not Found" (404)

**Solution:**
- Check endpoint URL spelling
- Verify resource ID exists
- Check if resource is soft-deleted

### "Server Error" (500)

**Solution:**
- Check Laravel logs: `storage/logs/laravel.log`
- Enable debug: `APP_DEBUG=true` in `.env`
- Check database connection
- Run migrations: `php artisan migrate`

---

## API Versioning

**Current Version:** v1

Future changes will be versioned. Deprecated endpoints will return:
```json
{
  "success": false,
  "message": "This endpoint is deprecated. Please use /api/v2/... instead.",
  "code": 301
}
```

---

## Contact & Support

- **Documentation:** Check `API_DOCUMENTATION.md`
- **Issues:** Check Laravel logs in `storage/logs/`
- **Debug:** Use `php artisan pail` for real-time logs
- **Testing:** Use Postman collection for API testing

---

## Version History

**v1.0.0** (2024-01-15)
- Initial API release
- JWT authentication
- Tenant management
- Lease signing
- Payment integration
- Maintenance requests
