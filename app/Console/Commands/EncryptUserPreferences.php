<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class EncryptUserPreferences extends Command
{
    protected $signature = 'users:encrypt-preferences';

    protected $description = 'Encrypt existing notification_preferences values for users (one-time migration)';

    public function handle(): int
    {
        $users = DB::table('users')
            ->whereNotNull('notification_preferences')
            ->where('notification_preferences', '!=', '')
            ->get(['id', 'notification_preferences']);

        $count = 0;

        foreach ($users as $user) {
            $raw = $user->notification_preferences;

            // Skip if already encrypted (won't be valid JSON)
            if (!$this->isJson($raw)) {
                $this->line("User {$user->id}: already encrypted, skipping.");
                continue;
            }

            // Re-encrypt via Crypt
            $encrypted = Crypt::encryptString($raw);

            DB::table('users')
                ->where('id', $user->id)
                ->update(['notification_preferences' => $encrypted]);

            $count++;
        }

        $this->info("Encrypted notification_preferences for {$count} users.");

        return self::SUCCESS;
    }

    private function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
