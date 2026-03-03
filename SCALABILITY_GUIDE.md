# System Scalability Guide - 100K+ Records

## Overview
This document outlines the scalability improvements made and additional recommendations for handling 100K+ records efficiently.

## Changes Made

### 1. Database Indexes Added
**Migration:** `database/migrations/2026_03_03_000001_add_performance_indexes.php`

Added composite and single-column indexes on:
- `leases`: tenant_id, status, start_date, end_date, property_id+status
- `lease_assignments`: is_current, lease_id+is_current, bed_id+is_current
- `lease_payment_schedules`: due_date, lease_id+due_date
- `tenants`: status, move_in_date
- `tenant_profiles`: tenant_id
- `units`: is_active, property_id+is_active
- `rooms`: is_active, unit_id+is_active
- `beds`: is_occupied, room_id+is_occupied
- `maintenance_requests`: status, property_id+status

**Run migration:**
```bash
php artisan migrate
```

### 2. Controller Optimizations

#### DashboardController (Optimized)
- Replaced N+1 loops with aggregated queries
- Uses single SQL aggregation for invoice totals instead of looping through leases
- Optimized eager loading with selective columns

#### LeaseController (Optimized)
- Changed `Tenant::all()` to limited, column-selective queries
- Dropdown data now loads only essential columns

#### MaintananceController (Optimized)
- Replaced `Property::all()` with filtered, column-selective queries
- Added tenant limits for dropdowns

#### LeaseDocumentController (Optimized)
- Optimized tenant dropdown loading

### 3. Model Optimizations

#### Property Model
- Replaced nested `whereIn` subqueries with efficient `JOIN` operations
- `totalBeds()` and `totalRooms()` now use single JOIN queries

---

## Additional Recommendations

### High Priority (Implement Before 100K Records)

#### 1. Enable Redis Cache
```bash
# Install Redis
# Update .env
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
```

Add caching to dashboard:
```php
// In DashboardController
$stats = Cache::remember('dashboard_stats', 300, function () {
    return [
        'active' => Lease::where('status', 'ACTIVE')->count(),
        // ...
    ];
});
```

#### 2. Convert Tenant Dropdown to AJAX Search
For pages with tenant dropdowns, implement AJAX search instead of loading all:
```javascript
// Use Select2 with AJAX
$('.tenant-select').select2({
    ajax: {
        url: '/api/tenants/search',
        dataType: 'json',
        delay: 250,
        data: function (params) {
            return { q: params.term, limit: 20 };
        }
    }
});
```

Create search endpoint:
```php
// In TenantController
public function search(Request $request)
{
    return Tenant::with('profile:id,tenant_id,first_name,last_name')
        ->where('email', 'like', '%' . $request->q . '%')
        ->orWhereHas('profile', function($q) use ($request) {
            $q->where('first_name', 'like', '%' . $request->q . '%');
        })
        ->select('id', 'email')
        ->limit(20)
        ->get();
}
```

#### 3. Add Chunking for Reports
For report exports, use chunking:
```php
// Instead of get(), use chunk
Invoice::where('status', 'PAID')
    ->chunk(1000, function ($invoices) {
        foreach ($invoices as $invoice) {
            // Process
        }
    });
```

#### 4. Queue Heavy Operations
- PDF generation
- Email sending
- Report exports
- Document processing

```php
// Already have queue setup, ensure using it
dispatch(new GenerateLeaseDocumentJob($lease));
```

### Medium Priority

#### 5. Database Configuration (MySQL)
Add to MySQL config for 100K+ records:
```ini
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
max_connections = 200
query_cache_size = 64M
```

#### 6. Soft Delete Cleanup
Regularly clean up soft-deleted records older than 6 months:
```php
// Schedule in Console/Kernel.php
$schedule->command('model:prune')->daily();
```

#### 7. Add Database Read Replicas
For very high traffic, consider read replicas:
```php
// config/database.php
'mysql' => [
    'read' => ['host' => env('DB_READ_HOST')],
    'write' => ['host' => env('DB_WRITE_HOST')],
    // ...
]
```

### Lower Priority (Future Scaling)

#### 8. Implement Full-Text Search
For searching tenants, properties, leases:
```sql
ALTER TABLE tenant_profiles ADD FULLTEXT INDEX idx_tenant_search (first_name, last_name);
```

#### 9. Consider Elasticsearch
For complex search requirements beyond 500K records.

#### 10. Horizontal Scaling
- Move to containerized deployment (Docker/Kubernetes)
- Load balancer for multiple app servers
- Separate queue workers

---

## Performance Monitoring

### 1. Enable Query Logging
```php
// In AppServiceProvider boot()
if (config('app.debug')) {
    DB::listen(function ($query) {
        if ($query->time > 100) { // Log slow queries over 100ms
            Log::warning('Slow Query', [
                'sql' => $query->sql,
                'time' => $query->time,
            ]);
        }
    });
}
```

### 2. Install Laravel Telescope (Development)
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

### 3. Use Laravel Debugbar (Development)
```bash
composer require barryvdh/laravel-debugbar --dev
```

---

## Expected Performance at Scale

| Records | With Optimizations | Without Optimizations |
|---------|-------------------|----------------------|
| 10K | < 100ms | 200-500ms |
| 50K | < 200ms | 1-3 seconds |
| 100K | < 500ms | 5-15 seconds |
| 500K | < 1 second | 30+ seconds (may timeout) |

---

## Quick Checklist Before Production

- [ ] Run migration: `php artisan migrate`
- [ ] Enable Redis: `CACHE_STORE=redis`
- [ ] Configure queue: `QUEUE_CONNECTION=redis`
- [ ] Run queue workers: `php artisan queue:work --daemon`
- [ ] Enable OPcache for PHP
- [ ] Configure MySQL buffer pools
- [ ] Set up database backups
- [ ] Enable monitoring (Telescope/Debugbar)
