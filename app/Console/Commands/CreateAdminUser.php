<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria um novo usuário administrador';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=================================');
        $this->info('  Criar Novo Usuário Admin');
        $this->info('=================================');
        $this->newLine();

        // Solicita informações
        $name = $this->ask('Nome do administrador');
        $email = $this->ask('Email do administrador');

        // Valida email
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email|unique:users,email'
        ]);

        if ($validator->fails()) {
            $this->error('Email inválido ou já cadastrado!');
            return 1;
        }

        $password = $this->secret('Senha (mínimo 6 caracteres)');
        $passwordConfirm = $this->secret('Confirme a senha');

        // Valida senha
        if (strlen($password) < 6) {
            $this->error('A senha deve ter no mínimo 6 caracteres!');
            return 1;
        }

        if ($password !== $passwordConfirm) {
            $this->error('As senhas não coincidem!');
            return 1;
        }

        $creditos = $this->ask('Créditos iniciais (padrão: 1000)', '1000');
        $creditosSemanais = $this->ask('Créditos semanais (padrão: 100)', '100');

        // Confirma criação
        $this->newLine();
        $this->info('Dados do usuário:');
        $this->line("Nome: {$name}");
        $this->line("Email: {$email}");
        $this->line("Role: admin");
        $this->line("Créditos: {$creditos}");
        $this->line("Créditos Semanais: {$creditosSemanais}");
        $this->newLine();

        if (!$this->confirm('Deseja criar este usuário admin?', true)) {
            $this->warn('Operação cancelada!');
            return 0;
        }

        // Cria o usuário
        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin',
                'creditos' => (int) $creditos,
                'creditos_semanais' => (int) $creditosSemanais,
                'ultima_renovacao' => now(),
            ]);

            $this->newLine();
            $this->info('✓ Usuário admin criado com sucesso!');
            $this->newLine();
            $this->line('Acesse o painel em: /admin/login');
            $this->line("Email: {$email}");
            $this->newLine();

            return 0;
        } catch (\Exception $e) {
            $this->error('Erro ao criar usuário: ' . $e->getMessage());
            return 1;
        }
    }
}
