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
    protected $signature = 'creditos:renovar-semanais';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renova os créditos semanais de todos os usuários';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando renovação de créditos semanais...');

        $usuarios = User::all();
        $renovados = 0;

        foreach ($usuarios as $usuario) {
            $usuario->creditos = $usuario->creditos_semanais;
            $usuario->ultima_renovacao = Carbon::now();
            $usuario->save();
            $renovados++;
        }

        $this->info("✅ {$renovados} usuário(s) tiveram seus créditos renovados!");
        $this->info("Créditos semanais: {$usuarios->first()->creditos_semanais}");
        
        return Command::SUCCESS;
    }
}
