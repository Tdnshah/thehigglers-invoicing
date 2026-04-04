# The Higglers Invoicing System - Codebase Context

This document provides a high-level overview of the architecture, models, and business logic of the invoicing system. It is intended to help developers (and AI assistants) quickly understand the codebase and onboard, regardless of the development environment.

## 1. Tech Stack Overview
- **Framework:** Laravel 12.x (`php: ^8.2`)
- **Frontend Assets:** Vite, Tailwind CSS (implied by config files)
- **PDF Generation:** `barryvdh/laravel-dompdf`

## 2. Core Domain Models & Relationships

- **Company (`app/Models/Company.php`)**: Represents the invoicing entity. A company has many `User`s (Admins).
- **Client (`app/Models/Client.php`)**: Represents the customer being invoiced. 
   - `belongsTo(User::class)`: The admin who owns the client.
   - `hasOne(User::class, 'client_id')`: The client user login mapping.
   - `hasMany(Invoice::class)`
- **User (`app/Models/User.php`)**: Handles authentication and role-based access.
   - `isCompanyAdmin()`: Returns true if `company_id !== null`. Company Admins can manage clients, create invoices, and register payments.
   - `isClientUser()`: Returns true if `client_id !== null`. Client Users have restricted read-only or scoped access (e.g., viewing only their own invoices and dashboard).
- **Invoice (`app/Models/Invoice.php`)**: The core entity for billing.
   - Has properties like `invoice_type` (`regular`, `export`, `interstate`), `place_of_supply`, `currency`, `status` (`draft`, `sent`, `paid`, `overdue`), and GST fields (`cgst`, `sgst`, `igst`).
   - `belongsTo(User::class)` (the admin) and `belongsTo(Client::class)`.
   - `hasMany(InvoiceItem::class)` and `hasMany(Payment::class)`.
- **InvoiceItem (`app/Models/InvoiceItem.php`)**: Line items for an invoice. Includes `hsn_code`, `tax_rate`, `quantity`, `unit_price`, etc.
- **Payment (`app/Models/Payment.php`)**: Records transactions against invoices.

## 3. Access Control & Authorization Logic
Authorization is heavily reliant on `User` helper methods (`isCompanyAdmin()` and `isClientUser()`). These checks are repeatedly implemented inside controllers (e.g., `InvoiceController`, `DashboardController`).

- **Company Admin Access:** Filtered by `$user->id`. They see entities they created (`where('user_id', $user->id)`).
- **Client User Access:** Filtered by `$user->client_id`. They see data belonging to their client account (`where('client_id', $user->client_id)`).

*Note: There is no strict Spatie-like role/permission system, it relies primarily on checking the presence of `company_id` and `client_id` foreign keys.*

## 4. Key Controllers & Routing (`routes/web.php`)

- **DashboardController:** Returns aggregated totals (outstanding, earnings), status counts, and recent invoices, scoped by the user's role.
- **InvoiceController:** Full CRUD for invoices. Enforces GST logic on the backend:
   - `regular`: Computes CGST and SGST (split equally).
   - `interstate`: Computes IGST.
   - `export`: Computes IGST (assumes zero-rated, but relies on user-provided tax rate or LUT).
   - Has custom methods for `print` and `downloadPdf`.
- **PaymentController:** Adds payments to invoices and auto-updates the invoice status to `paid` if the sum of payments reaches the total amount.
- **ClientController:** Handles CRUD for clients and also contains nested sub-routes to manage Client Users (e.g., `createUser`, `storeUser`).
- **InstallController:** Handles the initial setup routing (`/install`), likely bypassed after setup using `CheckInstallation` middleware.

## 5. Directory Structure Pointers
- **Migrations:** Custom prefixes and timestamps, handling schema updates nicely (e.g., `add_gst_fields_to_invoices_table.php`, `modify_users_table_for_company_relation.php`). This shows the database was evolved smoothly to support company/client relationships.
- **Routes:** The entirety of the core logic routes are defined in `routes/web.php` wrapped in `auth` middleware.

## AI Assistant Instructions
When picking up tasks from this document:
1. Always maintain the role-based constraint checks (`isCompanyAdmin()` vs `isClientUser()`) when adding or modifying controllers, as access leaks should be strictly avoided.
2. For database migrations, adhere to the existing naming and timestamp conventions found in `database/migrations/`.
3. Respect the GST tax logic established in `InvoiceController@store` and `@update` when dealing with pricing modifications.

## 6. Local Development Environment Setup

Because this project uses a custom Docker Compose setup that only spins up the services, `composer install` doesn't run automatically by default on `docker compose up`. Use the following steps to spin up the local development environment from scratch:

1. **Copy the Environment File:**
   ```bash
   cp .env.example .env
   ```

2. **Start the Docker Containers:**
   Launch the app, nginx, and database containers in the background.
   ```bash
   docker compose up -d
   ```

3. **Install PHP Dependencies (Composer):**
   Run composer install inside the application container.
   ```bash
   docker compose exec app composer install
   ```

4. **Generate Application Key:**
   ```bash
   docker compose exec app php artisan key:generate
   ```

5. **Run Database Migrations:**
   ```bash
   docker compose exec app php artisan migrate
   ```

6. **Install Frontend Dependencies (Node/NPM):**
   *Note: Since Node isn't bundled within the default PHP and Nginx containers, run this locally on your host machine.*
   ```bash
   npm install
   ```

7. **Start the Frontend Dev Server:**
   ```bash
   npm run dev
   ```

The application will now be running on `http://localhost:8000` (served via Nginx) and Vite will handle hot-module replacement for frontend assets.
