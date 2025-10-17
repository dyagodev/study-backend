<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Criar usuário de teste
        User::factory()->create([
            'name' => 'Aluno Teste',
            'email' => 'aluno@example.com',
            'role' => 'aluno',
        ]);

        User::factory()->create([
            'name' => 'Professor Teste',
            'email' => 'professor@example.com',
            'role' => 'professor',
        ]);

        // Popular temas
        $this->call([
            TemasSeeder::class,
        ]);
    }
}
