# ShypaTap

**ShypaTap** is a medical appointment booking and management system. It helps patients find verified doctors, view their available consultation times, make or cancel appointments, and submit a review after a completed visit. Doctors, receptionists, and administrators receive role-appropriate tools for handling appointments and the clinical directory.

The project is a Laravel REST API with a server-rendered admin area. The accompanying project specification describes a Vue 3 client as the intended public-facing frontend; a Vue application is not currently included in this repository.

## Tech Stack

| Area | Technology |
| --- | --- |
| Backend | PHP 8.3+ and Laravel 13 |
| API authentication | Laravel Sanctum personal access tokens |
| Database | PostgreSQL (the active local configuration and migrations are PostgreSQL-oriented) |
| Data access | Eloquent ORM, migrations, factories, and seeders |
| Authorization | Custom `role` middleware plus `auth:sanctum` |
| Admin UI | Laravel Blade views, session authentication, Bootstrap assets |
| Asset tooling | Vite 8, Tailwind CSS 4, PostCSS, and Tabler Icons |
| Developer tools | Composer, npm, PHPUnit, Laravel Pint, Laravel Pail, and Laravel Boost |

## Core Features and Architecture

### Role-based access

| Role | Capabilities exposed by the backend |
| --- | --- |
| Patient | Registers and signs in, browses verified doctors and clinics, creates appointments, views and cancels their own appointments, and reviews completed appointments. |
| Doctor | Views appointments assigned to their doctor profile and changes their appointment status. |
| Receptionist | Creates an appointment on a patient's behalf. |
| Administrator | Uses JSON endpoints and protected Blade screens to manage users, clinics, specializations, and doctor profiles; verifies doctors; inspects appointments; and views dashboard totals. |

The `RoleMiddleware` checks that a user is authenticated, active, and assigned an allowed role. API tokens are protected through Sanctum; the web admin area uses Laravel's session guard and the same role middleware.

### Booking domain

The domain model covers users, doctor profiles, specializations, clinics, clinic/doctor and clinic/staff relationships, weekly doctor schedules, days off, appointments, and reviews. Doctors can be associated with multiple clinics. Schedules define ISO weekday working windows, optional breaks, and slot duration; a service is provided to calculate availability in the `Asia/Ashgabat` timezone while excluding days off, breaks, past times, and overlapping active appointments.

Appointments support `pending`, `confirmed`, `cancelled`, `rejected`, `completed`, and `no_show` statuses. The patient booking flow validates the requested date and time, creates a 30-minute appointment, and checks active (`pending` or `confirmed`) records for an already-booked time inside a database transaction. A patient may cancel only their own non-completed, non-cancelled appointment. One review is permitted per appointment and only after completion.

The project specification additionally defines the desired business rules: prevent double booking at the database level, ensure bookings lie within a doctor's schedule, and restrict every user to records they own or are authorized to administer. Notifications, payments, video consultations, and electronic medical records are future-scope items, not implemented features.

### Application layout

```text
app/
├── Http/
│   ├── Controllers/Api/       # Public, booking, and admin API controllers
│   └── Middleware/            # Role-based authorization
├── Models/                    # Eloquent domain models and relationships
├── Services/                  # Appointment availability calculation
database/
├── migrations/                # PostgreSQL schema
├── factories/                 # Model factories
└── seeders/                   # Demo clinics, users, schedules, appointments, reviews
resources/
├── css/ and js/               # Vite entry points
└── views/                     # Blade login, layout, and admin views
routes/
├── api.php                    # Sanctum and role-protected REST API
├── web.php                    # Login/logout routes
└── admin.php                  # Session-protected Blade admin routes
```

## Setup and Installation

### Prerequisites

- PHP 8.3 or newer with the PostgreSQL extension
- Composer 2
- Node.js and npm
- PostgreSQL

### 1. Clone and install dependencies

```bash
git clone <repository-url>
cd ShypaTap-backend

composer install
npm install
```

### 2. Configure the environment

Copy the example file and generate the application key:

```bash
cp .env.example .env
php artisan key:generate
```

Set the application and PostgreSQL connection values in `.env`:

```dotenv
APP_NAME=ShypaTap
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=shypatap_backend
DB_USERNAME=postgres
DB_PASSWORD=your_database_password
```

Create the database in PostgreSQL if it does not already exist. Keep `SESSION_DRIVER`, `CACHE_STORE`, and `QUEUE_CONNECTION` set to `database` if using the provided defaults; the included migrations create their required tables.

### 3. Create schema and optional demo data

```bash
php artisan migrate
php artisan db:seed
```

For a clean local database populated in one step, use:

```bash
php artisan migrate:fresh --seed
```

The demo seeder creates administrator, doctor, patient, and receptionist accounts. Its default password is `password`; change or remove these credentials outside local development.

### 4. Build and run

In one terminal, start Laravel:

```bash
php artisan serve
```

In another terminal, run Vite for local asset changes:

```bash
npm run dev
```

Alternatively, `composer run dev` starts Laravel's local development workflow. Use `npm run build` to produce production assets.

The session-based administrator login is available at `/admin/login`. API requests are rooted at `/api`; authenticate with `Authorization: Bearer <Sanctum-token>` after registering or logging in.

## API Routes Summary

All endpoints below have the `/api` prefix. `POST /register` and `POST /login` are rate-limited to five requests per minute. Endpoints marked **Sanctum** require a bearer token; each role-restricted endpoint also requires an active account with the indicated role.

### Public and authentication

| Method | Endpoint | Access | Purpose |
| --- | --- | --- | --- |
| POST | `/register` | Public | Create a patient account and return a Sanctum token. |
| POST | `/login` | Public | Authenticate and return a Sanctum token. |
| GET | `/doctors` | Public | Paginated verified doctors; supports `specialization_id` and `search`. |
| GET | `/doctors/{doctor}` | Public | Verified doctor profile, clinics, schedules, and reviews. |
| GET | `/doctors/{doctor}/slots` | Public | Request doctor availability for `?date=YYYY-MM-DD`. |
| GET | `/specializations` | Public | List specializations with doctor counts. |
| GET | `/clinics` | Public | List clinics with doctor counts. |
| POST | `/logout` | Sanctum | Revoke the current access token. |
| GET | `/me` | Sanctum | Return the authenticated user. |

### Appointment workflows

| Method | Endpoint | Access | Purpose |
| --- | --- | --- | --- |
| POST | `/appointments` | Sanctum + patient | Create an appointment. |
| GET | `/my-appointments` | Sanctum + patient | Paginated appointments belonging to the patient. |
| PATCH | `/appointments/{appointment}/cancel` | Sanctum + patient | Cancel the patient's own eligible appointment. |
| POST | `/appointments/{appointment}/review` | Sanctum + patient | Create or update a review for the patient's completed appointment. |
| GET | `/doctor/appointments` | Sanctum + doctor | Paginated appointments for the authenticated doctor's profile. |
| PATCH | `/doctor/appointments/{appointment}/status` | Sanctum + doctor | Set a permitted appointment status. |
| POST | `/receptionist/appointments` | Sanctum + receptionist or admin | Create an appointment for a patient. |

### Administrator API

All administrator routes require **Sanctum + admin**.

| Method(s) | Endpoint | Purpose |
| --- | --- | --- |
| GET | `/admin/dashboard` | Patient, doctor, clinic, and recent-appointment totals. |
| GET, POST, GET by ID, PUT/PATCH, DELETE | `/admin/users` | User administration. |
| PATCH | `/admin/users/{user}/status` | Activate or deactivate a user; deactivation revokes tokens. |
| GET, POST, GET by ID, PUT/PATCH, DELETE | `/admin/clinics` | Clinic administration. |
| GET, POST, GET by ID, PUT/PATCH, DELETE | `/admin/specializations` | Specialization administration. |
| GET, POST, GET by ID, PUT/PATCH, DELETE | `/admin/doctors` | Doctor profile and clinic assignment administration. |
| PATCH | `/admin/doctors/{doctor}/verify` | Update a doctor's verification status. |
| GET | `/admin/appointments` | Filterable appointment list (`status`, dates, clinic, doctor profile). |
| GET | `/admin/appointments/{appointment}` | Appointment details and review. |

The protected web routes under `/admin` provide Blade screens for the dashboard plus clinic, doctor, specialization, and user management. They use `auth` and `role:admin`, rather than Sanctum tokens.

## Current Implementation Notes

This README reflects both the supplied specification and the checked-in source. Before production use, resolve the following source-level integration gaps:

- `DoctorController::slots()` calls `getAvailableSlots()`, while `AppointmentAvailabilityService` currently exposes `slots()` and requires a clinic ID. The public slots endpoint therefore needs alignment before it can calculate availability.
- The current appointment controller uses legacy `doctor_id` fields, whereas the migrations and Eloquent models use `doctor_profile_id`, require `clinic_id`, and require a unique `booking_code`. Align the controller with the current schema before relying on appointment creation, listing, status updates, or reviews.
- The intended Vue 3 frontend described in the project documentation has not been committed. The included interface is the Blade-based administrator area plus Vite/Tailwind assets.

## Testing and Code Quality

```bash
php artisan test
vendor/bin/pint --format agent
```

The repository currently contains Laravel's example test files. Add feature coverage for role authorization, availability, booking conflicts, ownership checks, and status transitions as the booking implementation is finalized.

## License

No project-specific license file is currently included. Add a license before distributing the project.
