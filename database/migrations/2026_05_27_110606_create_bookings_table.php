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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->index();
            $table->unsignedBigInteger('vehicle_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->string('purpose');
            $table->string('destination')->nullable();
            $table->unsignedSmallInteger('passenger_count')->default(1);
            $table->string('status')->default('Pending');
            $table->timestamps();

            $table->index(['organization_id', 'vehicle_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
