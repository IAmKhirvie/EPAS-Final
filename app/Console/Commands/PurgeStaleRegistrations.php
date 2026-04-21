<?php

namespace App\Console\Commands;

use App\Models\Registration;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PurgeStaleRegistrations extends Command
{
    protected $signature = 'registrations:purge-stale {--dry-run : Preview what would be deleted without actually deleting}';

    protected $description = 'Soft-delete stale pending and rejected registrations older than the configured threshold';

    public function handle(): int
    {
        $staleDays = (int) config('joms.registration.stale_days', 30);
        $cutoff = now()->subDays($staleDays);
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info("[DRY RUN] Showing registrations that would be purged (older than {$staleDays} days)...");
        } else {
            $this->info("Purging stale registrations older than {$staleDays} days...");
        }

        // Pending registrations that never verified email
        $pendingQuery = Registration::where('status', Registration::STATUS_PENDING)
            ->where('created_at', '<', $cutoff);

        $pendingCount = $pendingQuery->count();

        // Rejected registrations older than threshold
        $rejectedQuery = Registration::where('status', Registration::STATUS_REJECTED)
            ->where('updated_at', '<', $cutoff);

        $rejectedCount = $rejectedQuery->count();

        if ($dryRun) {
            $this->table(
                ['Type', 'Count', 'Cutoff Date'],
                [
                    ['Pending (unverified)', $pendingCount, $cutoff->toDateTimeString()],
                    ['Rejected', $rejectedCount, $cutoff->toDateTimeString()],
                ]
            );
            $this->info("Total: " . ($pendingCount + $rejectedCount) . " registrations would be purged.");
            return self::SUCCESS;
        }

        $deletedPending = $pendingQuery->delete();
        $deletedRejected = $rejectedQuery->delete();

        $total = $deletedPending + $deletedRejected;

        $this->info("Purged {$deletedPending} stale pending registrations.");
        $this->info("Purged {$deletedRejected} old rejected registrations.");
        $this->info("Total purged: {$total}");

        Log::info("PurgeStaleRegistrations: deleted {$deletedPending} pending, {$deletedRejected} rejected (threshold: {$staleDays} days)");

        return self::SUCCESS;
    }
}
