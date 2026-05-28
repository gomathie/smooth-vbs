# Smooth VBS — System Overview

## What is Smooth VBS?

Smooth VBS (Vehicle Booking System) is a multi-organization, web-based fleet management platform. It allows organizations to manage their vehicle fleets, handle booking requests and approvals, monitor vehicle locations in real time via GPS, and generate usage reports — all from a single hosted application.

Multiple organizations can run on the same installation. Each organization's data is fully isolated from others. Organizations can optionally white-label the platform with their own brand name, logo, colors, and custom domain.

---

## Technology Stack

| Layer | Technology |
|-------|-----------|
| Framework | Laravel 13 (PHP 8.3) |
| Database | SQLite (development) / MySQL or PostgreSQL (production) |
| Frontend CSS | Tailwind CSS v4 |
| Build tool | Vite 8 |
| Maps | Leaflet.js (CDN) |
| Web server | Apache (with mod_rewrite, mod_ssl) |
| Scheduler | Laravel Scheduler via system cron |

---

## Architecture

### Multi-tenancy

Every record in the system (users, vehicles, bookings, GPS integrations) belongs to an `organization_id`. Controllers automatically scope all queries to the authenticated user's organization, so data never leaks between tenants.

### Role-Based Access Control (RBAC)

Access is enforced at both the route level (middleware) and inside controllers. The role hierarchy from least to most privileged is:

| Role | Key Permissions |
|------|----------------|
| `employee` | View vehicles, create and cancel own bookings |
| `supervisor` | All of the above + approve or reject bookings, view reports |
| `fleet_manager` | All of the above + add/edit/delete vehicles, manage GPS integrations |
| `organization_admin` | All of the above + manage users within the organization |
| `super_admin` | Platform-wide access — manage all organizations, create new tenants |

### White Labeling

Each organization can store a brand name, logo URL, primary color, and custom domain in a JSON `settings` column. A middleware (`ResolveTenantBranding`) runs on every request, detects the organization by custom domain or logged-in user, and injects branding into all views via `View::share()`. CSS custom properties are injected dynamically to apply the brand color without rebuilding assets.

---

## Features

### 1. Vehicle Management

- Add, edit, and delete vehicles (make, model, year, license plate, capacity, status)
- Vehicle status: Available, In Use, Maintenance
- Vehicles store the last known GPS position (`last_latitude`, `last_longitude`, `last_location_at`)
- GPS vehicle ID field (`gps_vehicle_id`) links a vehicle record to the provider's tracking ID

**Access:** View — all users. Create/Edit/Delete — fleet_manager and above.

---

### 2. Booking System

- Employees request a vehicle for a specific date/time range with a stated purpose
- Requests start in `Pending` status
- Supervisors and above can approve or reject bookings
- Users can cancel their own pending bookings
- Booking conflicts are checked before approval
- Bookings are linked to vehicles and users with full audit history

**Booking statuses:** Pending → Approved / Rejected / Cancelled

**Access:** Create/cancel — all users. Approve/reject — supervisor and above.

---

### 3. GPS Integration

The GPS layer uses a driver abstraction pattern. Each GPS provider is a PHP class implementing `GpsDriverInterface`, which has a single method:

```php
fetchVehicleLocations(): array
```

This returns an array keyed by the provider's vehicle ID, with `latitude`, `longitude`, and `recorded_at`.

**Supported providers:**

| Provider | Notes |
|----------|-------|
| Pilot Telematics | HTTP Basic Auth, `GET /api/api.php?cmd=list&node=1` |
| Traccar | HTTP Basic Auth, `GET /api/positions` |
| Demo | Generates synthetic positions for testing (no real API needed) |

Adding a new provider requires creating one PHP class and registering it in `GpsDriverFactory`.

**Sync command:**

```bash
php artisan gps:sync
php artisan gps:sync --organization=1
```

The scheduler runs this every 3 minutes automatically. Each sync updates `last_latitude`, `last_longitude`, and `last_location_at` on matched vehicles.

**Access:** Live map — all users. Manage integrations — fleet_manager and above.

---

### 4. Live Map

A Leaflet.js map displays all vehicles that have a known GPS position. Markers show the vehicle's license plate and last recorded time. The map auto-centers on the fleet.

---

### 5. Reports & Analytics

Three built-in reports, all filterable by date range and exportable to CSV:

| Report | What it shows |
|--------|--------------|
| Booking History | All bookings with status, vehicle, user, duration |
| Vehicle Utilization | Hours booked per vehicle, idle vehicles highlighted |
| (Index) | Links to all reports with summary stats |

**Access:** supervisor and above.

---

### 6. User Management

Organization admins can:
- Create users and assign roles
- Edit user name, email, and role
- Deactivate or reactivate users (soft disable — no data is deleted)

Guards in place:
- Cannot deactivate your own account
- Cannot deactivate or demote the last active administrator in an organization

**Access:** organization_admin and super_admin only.

---

### 7. Organization Management

Super admins can:
- Create new organizations (with optional initial admin user)
- Edit organization name and timezone
- Configure white-label settings per organization
- Delete organizations that have no users or vehicles

**Access:** super_admin only.

---

### 8. Audit Log

Every significant action (user created/updated/deactivated, booking approved/rejected, GPS integration added, organization created) is written to the `audit_logs` table with the acting user, timestamp, and a JSON metadata payload. This provides a full change history for compliance and debugging.

---

## Data Model Summary

```
organizations
    └── users
    └── vehicles
    └── bookings (via user + vehicle)
    └── gps_integrations
    └── audit_logs

bookings
    └── booking_approvals
```

---

## Scheduled Tasks

| Task | Schedule | Command |
|------|----------|---------|
| GPS location sync | Every 3 minutes | `gps:sync` |

The scheduler must be triggered by the system cron every minute:

```cron
* * * * * php /var/www/smoothvbs/artisan schedule:run >> /dev/null 2>&1
```

---

## File Structure (key paths)

```
app/
  Http/
    Controllers/          — One controller per feature
    Middleware/
      RoleMiddleware.php           — RBAC enforcement
      ResolveTenantBranding.php   — White-label injection
  Models/                 — Eloquent models
  Services/
    Gps/                  — GPS driver abstraction layer
  Console/
    Commands/
      SyncGpsLocations.php
      CreateSuperAdmin.php

resources/
  views/
    layouts/
      app.blade.php       — Main authenticated layout
      auth.blade.php      — Login/register layout
    bookings/
    vehicles/
    users/
    organizations/
    gps/
    reports/

routes/
  web.php                 — All HTTP routes with middleware
  console.php             — Scheduler registration

docs/
  system-overview.md      — This document
  super-admin-guide.md    — Super admin operational guide
```

---

## Deployment Checklist

1. Clone repository and run `composer install`
2. Copy `.env.example` to `.env` and set `APP_KEY`, database credentials, `APP_URL`
3. Run `php artisan migrate`
4. Run `npm install && npm run build`
5. Create the first super admin: `php artisan admin:create`
6. Configure Apache VirtualHost pointing to `/public`
7. Enable `mod_rewrite` and `mod_ssl`: `a2enmod rewrite ssl`
8. Set up cron for the scheduler (see above)
9. Ensure `storage/` and `bootstrap/cache/` are writable by the web server
