<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE provider_sync_logs MODIFY error_message LONGTEXT NULL');
        DB::statement('ALTER TABLE provider_sync_scopes MODIFY error_message LONGTEXT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE provider_sync_logs MODIFY error_message TEXT NULL');
        DB::statement('ALTER TABLE provider_sync_scopes MODIFY error_message TEXT NULL');
    }
};
