<<<<<<< HEAD
# Government Beneficiary Management System

Modern and secure admin-only platform for managing citizen and beneficiary records, QR-enabled identification cards, barangay analytics, reports, and audit activity.

## Features

- Admin-only authentication with role separation for `super_admin` and `staff_admin`
- Beneficiary registration with uploads, generated beneficiary numbers, and QR-linked verification pages
- Printable beneficiary ID cards with QR codes and PDF export
- Dashboard analytics for totals, barangay distribution, monthly trends, age groups, gender, and category statistics
- Search, filtering, pagination, and bulk actions for beneficiary records
- Assistance logging, activity logs, backup export, and backup restore
- CSV, Excel, and PDF export support for beneficiary records

## Stack

- Backend: Laravel 12, PHP 8.2
- Frontend: Blade, Tailwind CSS, Alpine.js, Chart.js
- Exports: Laravel Excel, DOMPDF
- QR Codes: Endroid QR Code
- Database: SQLite by default for local demo, MySQL-ready for deployment

## Local Setup

Run all commands below from the Laravel app folder:

```powershell
Set-Location .\beneficiary-system
```

1. Install dependencies:

```powershell
composer install
npm install
```

2. Prepare the environment:

```powershell
copy .env.example .env
php artisan key:generate
```

3. Run migrations, seed admin users, and link storage:

```powershell
php artisan storage:link
php artisan migrate --seed
```

4. Start the app:

```powershell
npm run dev
php artisan serve
```

## Default Admin Accounts

- Super Admin: `superadmin@gov.local`
- Staff Admin: `staffadmin@gov.local`
- Default Password: `Admin@12345`

Change these credentials in `.env` before production use.

## MySQL Deployment

For MySQL, update `.env` with your server values and rerun a fresh migration:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gbms
DB_USERNAME=root
DB_PASSWORD=
```

Then run:

```powershell
php artisan migrate:fresh --seed
```

## Key Routes

- `/login` Admin login page
- `/dashboard` Analytics dashboard
- `/beneficiaries` Beneficiary management
- `/activity-logs` Audit log viewer
- `/backups` Backup and restore
- `/verification/{token}` Public QR verification page

## Notes

- The local environment uses SQLite so the workspace can run immediately.
- File uploads are stored on the `public` disk. Run `php artisan storage:link` before using photo and document uploads.
- Backup restore is restricted to super admins.
- Delete actions are restricted to super admins.
=======
# beneficiary-system
>>>>>>> 88c193e13d97d1d294c93b555ca1f6693e55a1a8
