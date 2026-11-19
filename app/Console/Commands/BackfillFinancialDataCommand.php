<?php

namespace App\Console\Commands;

use Database\Seeders\FinancialDataSeeder;
use Illuminate\Console\Command;

class BackfillFinancialDataCommand extends Command
{
    protected $signature = 'polmart:backfill-financial';

    protected $description = 'Lengkapi cost_price produk, backfill snapshot HPP order_items, dan isi OPEX realistis secara aman dan idempoten.';

    public function handle(): int
    {
        $seeder = new FinancialDataSeeder();
        $seeder->setCommand($this);
        $seeder->run();

        return self::SUCCESS;
    }
}
