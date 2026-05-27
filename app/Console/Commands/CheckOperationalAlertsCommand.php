<?php

namespace App\Console\Commands;

use App\Services\Operations\OperationalAlertService;
use Illuminate\Console\Command;

class CheckOperationalAlertsCommand extends Command
{
    protected $signature = 'ops:check-alerts';

    protected $description = 'Check operational health signals and create admin alerts';

    public function handle(OperationalAlertService $alerts): int
    {
        $result = $alerts->check();

        $this->components->info('Operational alerts checked: '.json_encode($result));

        return self::SUCCESS;
    }
}
