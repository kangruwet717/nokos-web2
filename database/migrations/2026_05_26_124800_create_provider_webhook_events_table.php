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
        Schema::create('provider_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('smsbower');
            $table->string('event_id')->nullable();
            $table->string('activation_id')->nullable()->index();
            $table->foreignId('otp_order_id')->nullable()->constrained('otp_orders')->nullOnDelete();
            $table->json('payload');
            $table->boolean('signature_valid')->default(false);
            $table->boolean('processed')->default(false)->index();
            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'event_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_webhook_events');
    }
};
