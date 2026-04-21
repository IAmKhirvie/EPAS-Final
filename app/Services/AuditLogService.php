<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    public function log(
        string $action,
        string $description,
        $model = null,
        array $oldValues = null,
        array $newValues = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
        ]);
    }

    public function logLogin(int $userId, bool $successful = true): AuditLog
    {
        return AuditLog::create([
            'user_id' => $userId,
            'action' => $successful ? 'login' : 'login_failed',
            'description' => $successful ? 'User logged in successfully' : 'Failed login attempt',
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
        ]);
    }

    public function logLogout(): AuditLog
    {
        return $this->log('logout', 'User logged out');
    }

    public function logCreate($model, string $modelName = null): AuditLog
    {
        $name = $modelName ?? class_basename($model);
        return $this->log(
            'create',
            "{$name} created",
            $model,
            null,
            $model->toArray()
        );
    }

    public function logUpdate($model, array $oldValues, string $modelName = null): AuditLog
    {
        $name = $modelName ?? class_basename($model);
        return $this->log(
            'update',
            "{$name} updated",
            $model,
            $oldValues,
            $model->toArray()
        );
    }

    public function logDelete($model, string $modelName = null): AuditLog
    {
        $name = $modelName ?? class_basename($model);
        return $this->log(
            'delete',
            "{$name} deleted",
            $model,
            $model->toArray(),
            null
        );
    }

    public function logExport(string $exportType, int $recordCount): AuditLog
    {
        return $this->log(
            'export',
            "Exported {$recordCount} {$exportType} records"
        );
    }

    public function getRecentLogs(int $limit = 50)
    {
        return AuditLog::with('user')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function getLogsForUser(int $userId, int $limit = 50)
    {
        return AuditLog::forUser($userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function getLogsByAction(string $action, int $limit = 50)
    {
        return AuditLog::byAction($action)
            ->with('user')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function getSecurityLogs(int $days = 7)
    {
        return AuditLog::whereIn('action', ['login', 'login_failed', 'logout', 'password_reset'])
            ->recent($days)
            ->with('user')
            ->orderByDesc('created_at')
            ->get();
    }
}
