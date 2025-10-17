<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Renovar créditos semanais toda segunda-feira às 00:00
Schedule::command('creditos:renovar-semanais')
    ->weekly()
    ->mondays()
    ->at('00:00')
    ->timezone('America/Sao_Paulo');
