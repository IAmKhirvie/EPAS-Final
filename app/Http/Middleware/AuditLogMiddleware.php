<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AuditLogService;
use Symfony\Component\HttpFoundation\Response;

class AuditLogMiddleware
{
    protected AuditLogService $auditLog;

    protected array $auditableActions = [
        'POST', 'PUT', 'PATCH', 'DELETE'
    ];

    protected array $excludedRoutes = [
        'login',
        'logout',
        'api/*',
    ];

    /**
     * Sensitive fields that should never be logged
     */
    protected array $sensitiveFields = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'reset_token',
        'secret',
        'backup_codes',
        'two_factor_secret',
        'api_token',
        'remember_token',
        '_token',
        'credit_card',
        'card_number',
        'cvv',
        'ssn',
    ];

    public function __construct(AuditLogService $auditLog)
    {
        $this->auditLog = $auditLog;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log for authenticated users and specific HTTP methods (both success AND failure)
        if (
            auth()->check() &&
            in_array($request->method(), $this->auditableActions) &&
            !$this->isExcluded($request)
        ) {
            $this->logRequest($request, $response);
        }

        return $response;
    }

    protected function isExcluded(Request $request): bool
    {
        $routeName = $request->route()?->getName();

        foreach ($this->excludedRoutes as $pattern) {
            if (str_contains($pattern, '*')) {
                $regex = str_replace('*', '.*', $pattern);
                if (preg_match("/^{$regex}$/", $routeName)) {
                    return true;
                }
            } elseif ($routeName === $pattern) {
                return true;
            }
        }

        return false;
    }

    protected function logRequest(Request $request, Response $response): void
    {
        $action = $this->getActionFromMethod($request->method());
        $routeName = $request->route()?->getName() ?? 'unknown';
        $isSuccess = $response->isSuccessful();

        // Include response status in action for failed requests
        $actionDescription = $isSuccess
            ? "Action performed: {$routeName}"
            : "Action FAILED ({$response->getStatusCode()}): {$routeName}";

        $this->auditLog->log(
            $isSuccess ? $action : "{$action}_failed",
            $actionDescription,
            null,
            null,
            $this->filterSensitiveData($request->all())
        );
    }

    /**
     * Filter out sensitive data from request payload
     */
    protected function filterSensitiveData(array $data): array
    {
        $filtered = [];

        foreach ($data as $key => $value) {
            // Check if key matches any sensitive field (case-insensitive)
            $isSensitive = false;
            foreach ($this->sensitiveFields as $field) {
                if (stripos($key, $field) !== false) {
                    $isSensitive = true;
                    break;
                }
            }

            if ($isSensitive) {
                $filtered[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $filtered[$key] = $this->filterSensitiveData($value);
            } else {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }

    protected function getActionFromMethod(string $method): string
    {
        return match ($method) {
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => 'action',
        };
    }
}
