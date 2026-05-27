<?php

namespace App\Console\Commands;

use App\Services\Payments\PaymentService;
use Illuminate\Console\Command;

class ReconcilePendingPaymentInvoicesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:reconcile-pending {--limit=50 : Maximum pending invoices to reconcile}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconcile pending DompetX invoices and expire overdue invoices';

    /**
     * Execute the console command.
     */
    public function handle(PaymentService $payments): int
    {
        $stats = $payments->reconcilePending((int) $this->option('limit'));

        $this->components->info(
            "Payment invoices checked {$stats['checked']}, paid {$stats['paid']}, expired {$stats['expired']}, failed {$stats['failed']}, errors {$stats['errors']}.",
        );

        return $stats['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
