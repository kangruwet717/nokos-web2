<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('service_prices', 'provider_price_key')) {
            return;
        }

        Schema::table('service_prices', function (Blueprint $table) {
            $table->string('provider_price_key')->default('default')->after('country_id');
            $table->json('provider_meta')->nullable()->after('provider_price');
        });

        DB::table('service_prices')->update(['provider_price_key' => 'default']);

        Schema::table('service_prices', function (Blueprint $table) {
            $table->dropUnique(['provider_id', 'otp_service_id', 'country_id']);
            $table->unique(['provider_id', 'otp_service_id', 'country_id', 'provider_price_key'], 'service_prices_provider_service_country_key_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('service_prices', 'provider_price_key')) {
            return;
        }

        Schema::table('service_prices', function (Blueprint $table) {
            $table->dropUnique('service_prices_provider_service_country_key_unique');
            $table->dropColumn(['provider_price_key', 'provider_meta']);
            $table->unique(['provider_id', 'otp_service_id', 'country_id']);
        });
    }
};
