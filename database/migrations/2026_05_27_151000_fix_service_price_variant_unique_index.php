<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_INDEX = 'service_prices_provider_id_otp_service_id_country_id_unique';

    private const NEW_INDEX = 'service_prices_provider_service_country_key_unique';

    public function up(): void
    {
        if (! Schema::hasColumn('service_prices', 'provider_price_key')) {
            return;
        }

        Schema::table('service_prices', function (Blueprint $table) {
            if (! $this->hasIndex(self::NEW_INDEX)) {
                $table->unique(['provider_id', 'otp_service_id', 'country_id', 'provider_price_key'], self::NEW_INDEX);
            }
        });

        Schema::table('service_prices', function (Blueprint $table) {
            if ($this->hasIndex(self::OLD_INDEX)) {
                $table->dropUnique(self::OLD_INDEX);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('service_prices', 'provider_price_key')) {
            return;
        }

        Schema::table('service_prices', function (Blueprint $table) {
            if (! $this->hasIndex(self::OLD_INDEX)) {
                $table->unique(['provider_id', 'otp_service_id', 'country_id'], self::OLD_INDEX);
            }
        });

        Schema::table('service_prices', function (Blueprint $table) {
            if ($this->hasIndex(self::NEW_INDEX)) {
                $table->dropUnique(self::NEW_INDEX);
            }
        });
    }

    private function hasIndex(string $name): bool
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return collect(DB::select("PRAGMA index_list('service_prices')"))
                ->contains(fn (object $index): bool => ($index->name ?? null) === $name);
        }

        return collect(DB::select('SHOW INDEX FROM service_prices'))
            ->contains(fn (object $index): bool => ($index->Key_name ?? null) === $name);
    }
};
