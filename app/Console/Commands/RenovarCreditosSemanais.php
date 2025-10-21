<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class RenovarCreditosSemanais extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'creditos:renovar-diarios';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renova os créditos diários de todos os usuários (20 créditos por dia)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando renovação de créditos diários...');

        $usuarios = User::all();
        $renovados = 0;
        $pulados = 0;

        foreach ($usuarios as $usuario) {
            // Se o usuário tem menos de 20 créditos, renova para 20
            if ($usuario->creditos < 20) {
                $usuario->creditos = 20;
                $usuario->ultima_renovacao = Carbon::now();
                $usuario->save();
                $renovados++;
            }
            // Se o usuário tem 20 ou mais créditos, adiciona +20 apenas uma vez por dia
            else {
                // Verifica se já renovou hoje
                $ultimaRenovacao = $usuario->ultima_renovacao ? Carbon::parse($usuario->ultima_renovacao) : null;
                
                if (!$ultimaRenovacao || !$ultimaRenovacao->isToday()) {
                    $usuario->creditos += 20;
                    $usuario->ultima_renovacao = Carbon::now();
                    $usuario->save();
                    $renovados++;
                } else {
                    $pulados++;
                }
            }
        }

        $this->info("✅ {$renovados} usuário(s) tiveram seus créditos renovados!");
        if ($pulados > 0) {
            $this->info("⏭️  {$pulados} usuário(s) já renovaram hoje.");
        }

        return Command::SUCCESS;
    }
}
