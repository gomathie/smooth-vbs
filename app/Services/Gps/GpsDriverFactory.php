<?php

namespace App\Services\Gps;

use App\Models\GpsIntegration;

class GpsDriverFactory
{
    public static function make(GpsIntegration $integration): GpsDriverInterface
    {
        return match ($integration->provider) {
            'pilot_telematics' => new PilotTelematicsDriver($integration),
            'traccar'          => new TraccarDriver($integration),
            'demo'             => new DemoDriver($integration),
            default            => throw new \InvalidArgumentException("Unsupported GPS provider: {$integration->provider}"),
        };
    }
}
