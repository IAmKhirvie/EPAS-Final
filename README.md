# EPAS-E LMS

**Electronic Products Assembly and Servicing - Enhanced**

A comprehensive Learning Management System built with Laravel 12 for Philippine K-12 vocational/technical training programs.

## Tech Stack

| Layer    | Technology                                       |
| -------- | ------------------------------------------------ |
| Backend  | Laravel 12, PHP 8.2+                             |
| Frontend | Blade + Livewire + Tailwind CSS v4 + Bootstrap 5 |
| Build    | Vite                                             |
| Database | MySQL / MariaDB (SQLite for testing)             |
| Auth     | Laravel Sanctum + Session + 2FA (TOTP)           |
| Mail     | PHPMailer (SMTP)                                 |
| PDF      | DomPDF                                           |
| Excel    | Maatwebsite/Excel                                |

## Features

### Learning & Assessment

- **Course hierarchy**: Course > Module > Information Sheet > Topic
- **4 assessment types**: Self-Check (auto-graded, 14+ question types), Homework (manual grading), Task Sheet (practical tasks), Job Sheet (step-by-step procedures)
- **Competency checklists** with item-level ratings
- **Module prerequisites** with circular dependency detection
- **Certificate generation** with PDF export and public verification

### Grading (Philippine K-12 Scale)

- Weighted module grades: Self-checks 20%, Homeworks 30%, Task sheets 25%, Job sheets 25%
- Scale: Outstanding (90-100), Very Satisfactory (85-89), Satisfactory (80-84), Fairly Satisfactory (75-79), Did Not Meet (0-74)
- GPA calculation (4.0 scale)
- Grade export to CSV/Excel

### Gamification & Engagement

- Activity-based points (daily login, submissions, completions, perfect scores)
- **Achievement system** with 8 unlockable achievements (First Steps, Week Warrior, Perfectionist, Module Master, Graduate, etc.)
- **Progress milestones** awarding bonus points at 25%, 50%, 75%, and 100% course completion
- Leaderboards and streak tracking

### Communication

- Role-targeted announcements with comments
- Email notifications via queued jobs

### User Management

- 3 roles: Admin, Instructor, Student
- Multi-stage registration (email verification + admin approval)
- Bulk operations (import, activate, deactivate, assign sections)
- Instructor-scoped class/section management

### Security

- Two-factor authentication (TOTP with backup codes)
- Rate limiting (login, registration, password reset, **API endpoints**)
- Comprehensive audit logging
- Content sanitization (XSS prevention)
- **Nonce-based CSP** (Content Security Policy) with security headers
- **CORS configuration** with explicit allowed origins
- Session management (8hr absolute, 30min idle timeout)
- **Password breach checking** via Have I Been Pwned (Password::uncompromised)
- **Encrypted storage** for sensitive user preferences

### Performance

- 100% server-side search, pagination, sorting, and filtering (Livewire components)
- **Lazy-loaded Livewire components** with skeleton loading states
- N+1 query prevention with batch prefetching and aggregate queries
- Multi-tier caching (dashboard 10 min, grades 5 min, analytics 1 hr)
- **CDN-friendly cache headers** for static assets (1-year immutable)
- **Image optimization service** with automatic resizing and compression
- **PWA support** with service worker for offline access
- 16+ composite database indexes

## Requirements

- PHP 8.2+ with extensions: mbstring, xml, gd, zip, fileinfo, curl, openssl, pdo_mysql
- Composer 2.x+
- Node.js 18+
- MySQL 5.7+ / MariaDB 10.3+ (or SQLite for development)

## Installation

```bash
# Clone the repository
git clone https://github.com/IAmKhirvie/EPAS-E.git
cd EPAS-E

# Install dependencies
composer install
npm install

# Environment setup
copy .env.example .env
php artisan key:generate
php artisan storage:link

# Database setup
php artisan migrate
php artisan db:seed

# Start development servers
php artisan serve
npm run dev
```

The app will be available at `http://127.0.0.1:8000`.

### Post-Install: New Feature Setup

After the initial installation, run these additional steps to enable the latest features:

```bash
# 1. Run new migrations (achievements)
php artisan migrate

# 2. Encrypt existing user notification preferences (one-time)
php artisan users:encrypt-preferences

# 3. (Optional) Install Redis for production caching
composer require predis/predis
# Then update .env:
#   CACHE_STORE=redis
#   SESSION_DRIVER=redis
#   QUEUE_CONNECTION=redis

# 4. (Optional) Install Intervention Image for upload optimization
composer require intervention/image
```

> **Note:** Steps 3 and 4 are optional but recommended for production. The app works without them using file-based caching and uncompressed image uploads.

## Deployment (Cloudflare Tunnel)

To deploy the app publicly via Cloudflare Tunnel:

```bash
# 1. Install cloudflared (Windows)
winget install Cloudflare.cloudflared

# 2. Set production environment in .env
APP_ENV=production
APP_DEBUG=false
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

# 3. Build frontend assets for production
npm run build

# 4. Cache everything for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 5. Start Laravel server
php artisan serve --host=127.0.0.1 --port=8000

# 6. Start Cloudflare Tunnel (in a separate terminal)
cloudflared tunnel --url http://127.0.0.1:8000
```

Cloudflare will output a public URL like `https://xxxxx.trycloudflare.com`. HTTPS is auto-detected — no extra config needed.

### Switching back to local development

```bash
# In .env, change:
APP_ENV=local
APP_DEBUG=true
SESSION_SECURE_COOKIE=false

# Clear and rebuild config
php artisan config:clear
php artisan config:cache

# Start both servers
php artisan serve
npm run dev
```

## Default Accounts

After running `php artisan db:seed`, the following accounts are created:

| Role       | Email                                | Section | Password     |
| ---------- | ------------------------------------ | ------- | ------------ |
| Admin      | `khirviecliffordbautista@gmail.com`  | —       | `EPASe@2025` |
| Instructor | `karlrapada@gmail.com`               | S8B1    | `EPASe@2025` |
| Instructor | `KebinSy2121252@gmail.com`           | S8A1    | `EPASe@2025` |
| Student    | `mikaellayap23@gmail.com`            | S8A1    | `EPASe@2025` |
| Student    | `kookyarabia06@gmail.com`            | S8A1    | `EPASe@2025` |
| Student    | `sheilamerida@gmail.com`             | S8B1    | `EPASe@2025` |

> **Note:** These accounts are created by the `UserSeeder`. Make sure to run `php artisan db:seed` after migrations.

## Project Structure

```
app/
├── Constants/       # Role constants (ADMIN, INSTRUCTOR, STUDENT)
├── Exports/         # Excel exporters (grades, progress)
├── Http/
│   ├── Controllers/ # 34 controllers
│   ├── Middleware/   # 8 custom middleware
│   ├── Requests/    # Form request validation
│   └── Traits/
├── Imports/         # CSV/Excel user import
├── Jobs/            # Queued jobs (email, bulk operations)
├── Livewire/        # 9 interactive table/list components
├── Models/          # 58 Eloquent models
├── Observers/       # Submission lifecycle hooks
├── Policies/        # 5 authorization policies
├── Services/        # 19 business logic services
└── Traits/          # HasCommonScopes, HasMedia
```

### Key Services

| Service                      | Purpose                                                |
| ---------------------------- | ------------------------------------------------------ |
| `GradingService`             | Philippine K-12 grade calculation, GPA, rankings       |
| `SelfCheckGradingService`    | Auto-grading for 14+ question types                    |
| `GamificationService`        | Points, achievements, streaks with race condition prevention |
| `NotificationService`        | Multi-channel notification orchestration               |
| `AnalyticsService`           | Dashboard metrics, progress tracking                   |
| `DashboardStatisticsService` | Role-based dashboard data aggregation                  |
| `CertificateService`         | PDF certificate generation and verification            |
| `PrerequisiteService`        | Module access gating and dependency checking           |
| `ContentSanitizationService` | XSS/injection protection                               |
| `AuditLogService`            | System action logging                                  |
| `AchievementService`         | Achievement checking and awarding                      |
| `ImageOptimizationService`   | Upload resizing and compression                        |

## Code Quality Fixes & Updates

### Critical Bug Fixes

- **User::isActive()**: Fixed boolean comparison — `$this->stat === true` changed to `(int) $this->stat === 1` (tinyInteger column requires integer comparison)
- **NotificationService**: Fixed incorrect method name `sendMail()` → `sendNotificationEmail()`, added division-by-zero guard
- **PHPMailerService**: Replaced all `env()` calls with `config()` (prevents null values when running `php artisan config:cache`)
- **GamificationService**: Fixed `increment()` + `save()` overwrite bug — switched to direct assignment with `DB::transaction()` and `lockForUpdate()` for concurrent safety

### Model Relationship Fixes

- Fixed 6 models with incorrect `belongsTo` foreign key inference — added explicit FK parameters where column name differs from Eloquent's convention

### Database Normalization (1NF-4NF)

- `users.stat`: Changed from string to tinyInteger (0/1)
- Dropped redundant announcement tables
- Fixed `certificates.status` enum
- Added `description` and `is_active` columns to `information_sheets`
- Replaced MySQL-only `FIND_IN_SET` with portable LIKE patterns for SQLite compatibility
- Updated all `'stat', true/false` comparisons to `'stat', 1/0` across ~20 files

### Performance Optimizations

- **GradesController**: Eliminated N+1 queries via `prefetchStudentSubmissions()` batch method
- **GradingService**: Simplified nested `whereHas` with pre-fetched `sheetIds`
- **AnalyticsService**:
    - `calculateAverageProgress`: N+1 → single aggregate query
    - `getModuleMetrics`: 4N queries → 1 batched GROUP BY query
    - `getDailyActiveUsers`: loop → single GROUP BY
- **GradeTable (Livewire)**: 3 aggregate queries for entire paginated page instead of N per-student
- Added DB transactions to `SelfCheckController::store`, `JobSheetController::store`, `TaskSheetController::store`
- Added 16+ composite database indexes across 11+ tables

### Other Fixes

- **Media::boot()**: Changed `deleting` → `forceDeleting` event (prevents file loss on soft-delete)
- **Homework**: Added null `due_date` guards on `is_past_due`/`days_until_due` accessors
- **SelfCheck**: `completion_rate` now counts active students instead of ALL users
- **Module**: Auto-generated slugs for URL-friendly routes

## Testing

```bash
php artisan test
```

- PHPUnit with SQLite `:memory:` for fast execution
- Feature tests covering auth, CRUD, grade export, role-based access
- Route helper usage (`route()`) instead of hardcoded paths
- Factories for all major models with role-specific states

## Configuration

Centralized in `config/joms.php`:

- Grading thresholds and scale
- Password policy and rate limits
- Gamification point values
- Session timeouts
- File upload limits (10MB docs, 5MB images, 20MB audio, 100MB video)
- Cache TTL values
- SMTP mail settings

## License

MIT
