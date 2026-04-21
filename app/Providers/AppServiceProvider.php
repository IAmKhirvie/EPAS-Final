<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use App\Http\View\Composers\AnnouncementComposer;
use App\Http\View\Composers\TrashComposer;
use App\Models\Course;
use App\Models\Module;
use App\Models\Homework;
use App\Models\Announcement;
use App\Policies\CoursePolicy;
use App\Policies\ModulePolicy;
use App\Policies\HomeworkPolicy;
use App\Policies\AnnouncementPolicy;
use App\Models\Certificate;
use App\Policies\CertificatePolicy;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->singleton(\App\Services\PHPMailerService::class, function ($app) {
            return new \App\Services\PHPMailerService();
        });
    }

    public function boot(): void
    {
        // Auto-detect HTTPS based on environment:
        // 1. FORCE_HTTPS=true in .env - always use HTTPS
        // 2. Production environment - always use HTTPS
        // 3. Cloudflare tunnel detected (X-Forwarded-Proto or trycloudflare.com host)
        // 4. Any reverse proxy with X-Forwarded-Proto: https
        //
        // For local development without tunnel: set FORCE_HTTPS=false (default)
        // For Cloudflare tunnel: auto-detected, no config needed
        // For production: set FORCE_HTTPS=true or APP_ENV=production

        $forceHttps = config('app.force_https', false);
        $isProduction = config('app.env') === 'production';
        $isBehindHttpsProxy = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https';
        $isCloudflare = isset($_SERVER['HTTP_CF_VISITOR']) || str_contains($_SERVER['HTTP_HOST'] ?? '', 'trycloudflare.com');

        if ($forceHttps || $isProduction || $isBehindHttpsProxy || $isCloudflare) {
            URL::forceScheme('https');
        }

        // API rate limiting: 60 requests per minute per user/IP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // CSP nonce Blade directive for inline scripts
        Blade::directive('nonce', function () {
            return '<?php echo "nonce=\"" . request()->attributes->get("csp-nonce", "") . "\""; ?>';
        });

        // Use Bootstrap 5 pagination
        Paginator::useBootstrapFive();

        View::composer(['partials.navbar', 'partials.header'], AnnouncementComposer::class);
        View::composer('partials.sidebar', TrashComposer::class);

        // Register authorization policies
        Gate::policy(Course::class, CoursePolicy::class);
        Gate::policy(Module::class, ModulePolicy::class);
        Gate::policy(Homework::class, HomeworkPolicy::class);
        Gate::policy(Announcement::class, AnnouncementPolicy::class);
        Gate::policy(Certificate::class, CertificatePolicy::class);
    }
}
