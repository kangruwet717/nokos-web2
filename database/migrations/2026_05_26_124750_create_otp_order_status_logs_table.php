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
        Schema::create('otp_order_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('otp_order_id')->constrained()->cascadeOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->string('source');
            $table->string('message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['otp_order_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_order_status_logs');
    }
};
