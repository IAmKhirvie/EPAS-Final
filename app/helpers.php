<?php

if (!function_exists('dynamic_asset')) {
    function dynamic_asset($path)
    {
        // Use secure_asset() for production or when FORCE_HTTPS is enabled (e.g. Cloudflare Tunnel)
        if (config('app.env') === 'production' || config('app.force_https', false)) {
            return secure_asset($path);
        }
        return asset($path);
    }
}

if (!function_exists('dynamic_url')) {
    function dynamic_url($path = null, $parameters = [], $secure = null)
    {
        return url($path, $parameters, true);
    }
}

if (!function_exists('dynamic_route')) {
    function dynamic_route($name, $parameters = [], $absolute = true)
    {
        // Use regular url() for local development, secure_url() for production
        if ($absolute) {
            if (config('app.env') === 'production') {
                return secure_url(route($name, $parameters, false));
            }
            return url(route($name, $parameters, false));
        }

        return route($name, $parameters, $absolute);
    }
}
