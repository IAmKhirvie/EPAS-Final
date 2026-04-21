<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Certificate;

class CertificatePolicy
{
    public function view(User $user, Certificate $certificate): bool
    {
        // Admin can view any certificate
        if ($user->role === 'admin' || $user->role === 'super_admin') {
            return true;
        }

        // Owner can view their own certificate
        return $user->id === $certificate->user_id;
    }

    // Use same logic for download
    public function download(User $user, Certificate $certificate): bool
    {
        return $this->view($user, $certificate);
    }
}
