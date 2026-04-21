<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     * Set TRUSTED_PROXIES in .env (comma-separated IPs, or '*' for all).
     * In production, restrict to your reverse proxy/Cloudflare IPs.
     */
    protected $proxies;

    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;

    public function __construct()
    {
        $proxies = config('app.trusted_proxies') ?? env('TRUSTED_PROXIES');
        if ($proxies === '*') {
            $this->proxies = '*';
        } elseif (!empty($proxies)) {
            $this->proxies = array_map('trim', explode(',', $proxies));
        } else {
            // Default to trusting all proxies in local development
            $this->proxies = '*';
        }
    }
}
