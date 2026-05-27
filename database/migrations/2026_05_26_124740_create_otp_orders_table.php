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
        Schema::create('otp_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('otp_service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('provider_activation_id')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('phone_number_masked')->nullable();
            $table->decimal('provider_cost', 15, 4)->default(0);
            $table->decimal('selling_price', 15, 2);
            $table->decimal('margin_amount', 15, 2)->default(0);
            $table->string('status')->default('creating')->index();
            $table->text('sms_code')->nullable();
            $table->text('sms_text_masked')->nullable();
            $table->json('raw_provider_response')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->string('refund_reason')->nullable();
            $table->timestamps();

            $table->unique(['provider_id', 'provider_activation_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_orders');
    }
};
