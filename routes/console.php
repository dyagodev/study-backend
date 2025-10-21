<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Renovar créditos diários todos os dias às 00:00
Schedule::command('creditos:renovar-diarios')
    ->daily()
    ->at('00:00')
    ->timezone('America/Sao_Paulo');
