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
        Schema::create('otp_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->string('provider_code');
            $table->string('name');
            $table->string('category')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_blacklisted')->default(false)->index();
            $table->timestamps();

            $table->unique(['provider_id', 'provider_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_services');
    }
};
