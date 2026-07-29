<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('home:check-section-schema', function () {
    $columns = [
        'products' => ['is_deal_of_the_day', 'is_best_seller'],
        'cosmetic_products' => ['hot_deals', 'best_selling'],
        'motorcycle_products' => ['today_best_deals', 'best_seller'],
        'fashion_products' => ['today_best_deals', 'best_seller'],
        'home_need_products' => ['today_best_deals', 'best_seller'],
    ];

    $missing = [];

    foreach ($columns as $table => $tableColumns) {
        if (!Schema::hasTable($table)) {
            $missing[] = "{$table}.*";
            $this->error("MISSING TABLE {$table}");
            continue;
        }

        foreach ($tableColumns as $column) {
            $exists = Schema::hasColumn($table, $column);
            $this->line(($exists ? 'OK      ' : 'MISSING ') . "{$table}.{$column}");

            if (!$exists) {
                $missing[] = "{$table}.{$column}";
            }
        }
    }

    if ($missing) {
        $this->newLine();
        $this->error('Missing home section schema: ' . implode(', ', $missing));

        return 1;
    }

    $this->newLine();
    $this->info('Home section schema is complete.');

    return 0;
})->purpose('Check product columns required by home page marketing sections');

Schedule::command('sms:send-pending')
    ->everyMinute()
    ->withoutOverlapping();
