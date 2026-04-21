<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Core security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // HSTS only in production — setting this in dev forces browsers to use HTTPS
        // which causes ERR_TOO_MANY_REDIRECTS on HTTP local servers
        if (config('app.env') === 'production') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Cache control — long-lived caching for static assets, no caching for HTML
        $requestUri = $request->getRequestUri();
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|webp|woff2?|ttf|eot|ico|map)(\?|$)/i', $requestUri)) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        } else {
            $contentType = $response->headers->get('Content-Type', '');
            if (str_contains($contentType, 'text/html') || empty($contentType)) {
                $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
                $response->headers->set('Pragma', 'no-cache');
            }
        }

        // Content Security Policy
        // Allow Vite dev server in local development
        $viteDevServer = (config('app.debug') && config('app.env') !== 'production') ? ' http://127.0.0.1:*' : '';

        // Allow Cloudflare tunnel domains
        $cloudflareTunnel = ' https://*.trycloudflare.com';

        $csp = implode('; ', [
            "default-src 'self'" . $cloudflareTunnel,
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://code.jquery.com" . $viteDevServer . $cloudflareTunnel, // unsafe-inline required for inline scripts across views, unsafe-eval for Livewire/Alpine
            "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.googleapis.com" . $viteDevServer . $cloudflareTunnel,
            "font-src 'self' data: https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.gstatic.com" . $cloudflareTunnel,
            "img-src 'self' data: https: blob:" . $cloudflareTunnel,
            "connect-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com" . ($viteDevServer ? ' ws://127.0.0.1:*' . $viteDevServer : '') . $cloudflareTunnel,
            "frame-src 'self' https://www.google.com https://maps.google.com" . $cloudflareTunnel,
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'" . $cloudflareTunnel,
            "object-src 'none'",
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        // Additional modern security headers
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=()');

        // Cross-Origin headers for additional protection (only on HTTPS or localhost)
        if ($request->secure() || $request->getHost() === 'localhost' || $request->getHost() === '127.0.0.1') {
            $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
        }
        $response->headers->set('Cross-Origin-Resource-Policy', 'cross-origin');
        // Note: COEP require-corp disabled as it blocks legitimate external resources (avatars, CDN)
        // $response->headers->set('Cross-Origin-Embedder-Policy', 'require-corp');

        // Prevent Adobe Flash and PDF from accessing site data
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        // Prevent IE from executing downloads in site's context
        $response->headers->set('X-Download-Options', 'noopen');

        return $response;
    }
}
