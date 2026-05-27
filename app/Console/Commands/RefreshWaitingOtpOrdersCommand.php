<?php

namespace App\Console\Commands;

use App\Services\Orders\OtpOrderService;
use Illuminate\Console\Command;

class RefreshWaitingOtpOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'otp:refresh-waiting {--limit=50 : Maximum waiting orders to refresh}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh waiting OTP orders and expire orders that passed their timeout';

    /**
     * Execute the console command.
     */
    public function handle(OtpOrderService $orders): int
    {
        $count = $orders->refreshWaitingBatch((int) $this->option('limit'));

        $this->components->info("Refreshed {$count} waiting OTP order(s).");

        return self::SUCCESS;
    }
}
