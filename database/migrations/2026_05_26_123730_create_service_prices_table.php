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
        Schema::create('service_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('otp_service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('provider_price_key')->default('default');
            $table->decimal('provider_price', 15, 4);
            $table->json('provider_meta')->nullable();
            $table->string('margin_type')->default('percent');
            $table->decimal('margin_value', 10, 2)->default(30);
            $table->decimal('selling_price', 15, 2);
            $table->unsignedInteger('stock_count')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->unique(['provider_id', 'otp_service_id', 'country_id', 'provider_price_key'], 'service_prices_provider_service_country_key_unique');
            $table->index(['country_id', 'is_active']);
            $table->index(['otp_service_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_prices');
    }
};
