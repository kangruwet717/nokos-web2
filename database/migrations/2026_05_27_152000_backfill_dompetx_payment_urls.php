<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('payment_invoices')
            ->whereNull('payment_url')
            ->orderBy('id')
            ->each(function (object $invoice): void {
                $response = json_decode((string) $invoice->raw_create_response, true);

                if (! is_array($response)) {
                    return;
                }

                $paymentUrl = $response['payment_url']
                    ?? $response['payment_link']
                    ?? $response['checkout_url']
                    ?? $response['checkoutUrl']
                    ?? $response['url']
                    ?? null;

                if ($paymentUrl) {
                    DB::table('payment_invoices')
                        ->where('id', $invoice->id)
                        ->update(['payment_url' => $paymentUrl]);
                }
            });
    }

    public function down(): void
    {
        //
    }
};
