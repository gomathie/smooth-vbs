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
        Schema::table('gps_integrations', function (Blueprint $table) {
            $table->string('label')->after('organization_id')->default('');
            $table->string('base_url')->nullable()->after('provider');
        });
    }

    public function down(): void
    {
        Schema::table('gps_integrations', function (Blueprint $table) {
            $table->dropColumn(['label', 'base_url']);
        });
    }
};
