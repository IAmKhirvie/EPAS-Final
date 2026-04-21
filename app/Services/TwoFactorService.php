<?php

namespace App\Services;

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class TwoFactorService
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function generateSecret(): string
    {
        return $this->google2fa->generateSecretKey();
    }

    public function getQrCodeSvg(User $user, string $secret): string
    {
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);
        return $writer->writeString($qrCodeUrl);
    }

    public function enable(User $user, string $secret, string $code): bool
    {
        // Verify the code before enabling
        if (!$this->verify($secret, $code)) {
            return false;
        }

        // Generate backup codes
        $backupCodes = $this->generateBackupCodes();

        // Store encrypted secret and backup codes
        DB::table('user_two_factor')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'secret' => Crypt::encryptString($secret),
                'backup_codes' => Crypt::encryptString(json_encode($backupCodes)),
                'is_enabled' => true,
                'enabled_at' => now(),
                'updated_at' => now(),
            ]
        );

        return true;
    }

    public function disable(User $user): bool
    {
        return DB::table('user_two_factor')
            ->where('user_id', $user->id)
            ->update([
                'is_enabled' => false,
                'secret' => null,
                'backup_codes' => null,
                'updated_at' => now(),
            ]) > 0;
    }

    public function verify(string $secret, string $code): bool
    {
        return $this->google2fa->verifyKey($secret, $code);
    }

    public function verifyForUser(User $user, string $code): bool
    {
        $twoFactor = DB::table('user_two_factor')
            ->where('user_id', $user->id)
            ->where('is_enabled', true)
            ->first();

        if (!$twoFactor) {
            return false;
        }

        $secret = Crypt::decryptString($twoFactor->secret);

        if ($this->verify($secret, $code)) {
            // Update last used
            DB::table('user_two_factor')
                ->where('user_id', $user->id)
                ->update(['last_used_at' => now()]);

            return true;
        }

        // Check backup codes
        return $this->verifyBackupCode($user, $code);
    }

    public function isEnabled(User $user): bool
    {
        return DB::table('user_two_factor')
            ->where('user_id', $user->id)
            ->where('is_enabled', true)
            ->exists();
    }

    protected function generateBackupCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4)));
        }
        return $codes;
    }

    public function getBackupCodes(User $user): array
    {
        $twoFactor = DB::table('user_two_factor')
            ->where('user_id', $user->id)
            ->first();

        if (!$twoFactor || !$twoFactor->backup_codes) {
            return [];
        }

        return json_decode(Crypt::decryptString($twoFactor->backup_codes), true);
    }

    protected function verifyBackupCode(User $user, string $code): bool
    {
        $codes = $this->getBackupCodes($user);
        $code = strtoupper($code);

        if (in_array($code, $codes)) {
            // Remove used backup code
            $codes = array_values(array_diff($codes, [$code]));

            DB::table('user_two_factor')
                ->where('user_id', $user->id)
                ->update([
                    'backup_codes' => Crypt::encryptString(json_encode($codes)),
                    'last_used_at' => now(),
                ]);

            return true;
        }

        return false;
    }

    public function regenerateBackupCodes(User $user): array
    {
        $codes = $this->generateBackupCodes();

        DB::table('user_two_factor')
            ->where('user_id', $user->id)
            ->update([
                'backup_codes' => Crypt::encryptString(json_encode($codes)),
                'updated_at' => now(),
            ]);

        return $codes;
    }
}
