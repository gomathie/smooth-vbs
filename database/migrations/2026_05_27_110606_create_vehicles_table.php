<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->index();
            $table->string('registration_number');
            $table->string('vehicle_type');
            $table->unsignedSmallInteger('capacity')->default(1);
            $table->string('fuel_type')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('status')->default('Available');
            $table->string('gps_vehicle_id')->nullable();
            $table->string('imei')->nullable();
            $table->string('vin')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'registration_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
