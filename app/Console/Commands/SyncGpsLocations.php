<?php

namespace App\Console\Commands;

use App\Models\GpsIntegration;
use App\Services\Gps\GpsDriverFactory;
use App\Services\Gps\GpsLocationSyncer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncGpsLocations extends Command
{
    protected $signature = 'gps:sync {--organization= : Only sync a specific organization ID}';

    protected $description = 'Pull vehicle locations from configured GPS providers and update the fleet map.';

    public function handle(): int
    {
        $query = GpsIntegration::where('status', GpsIntegration::STATUS_ACTIVE);

        if ($orgId = $this->option('organization')) {
            $query->where('organization_id', $orgId);
        }

        $integrations = $query->get();

        if ($integrations->isEmpty()) {
            $this->info('No active GPS integrations found.');

            return self::SUCCESS;
        }

        $totalUpdated = 0;

        foreach ($integrations as $integration) {
            $this->line("Syncing [{$integration->label}] ({$integration->provider}) …");

            try {
                $driver    = GpsDriverFactory::make($integration);
                $locations = $driver->fetchVehicleLocations();

                $updated = (new GpsLocationSyncer($integration->organization_id))->applyLocations($locations);
                $totalUpdated += $updated;

                $integration->update([
                    'last_sync_at' => now(),
                    'status'       => GpsIntegration::STATUS_ACTIVE,
                ]);

                $this->info("  ✓ Updated {$updated} vehicle(s).");
            } catch (\Throwable $e) {
                $integration->update(['status' => GpsIntegration::STATUS_ERROR]);

                $this->error("  ✗ {$e->getMessage()}");
                Log::error("gps:sync failed for integration #{$integration->id}", [
                    'integration_id' => $integration->id,
                    'provider'       => $integration->provider,
                    'error'          => $e->getMessage(),
                ]);
            }
        }

        $this->info("Sync complete. {$totalUpdated} vehicle(s) updated across {$integrations->count()} integration(s).");

        return self::SUCCESS;
    }

    private function applyLocations(int $organizationId, array $locations): int
    {
        return (new GpsLocationSyncer($organizationId))->applyLocations($locations);
    }
}
