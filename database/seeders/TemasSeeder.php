<?php

namespace Database\Seeders;

use App\Models\Tema;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TemasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $temas = [
            [
                'nome' => 'Biologia',
                'descricao' => 'Estudo dos seres vivos e seus processos vitais',
                'icone' => '🧬',
                'cor' => '#4CAF50',
                'ativo' => true,
            ],
            [
                'nome' => 'Matemática',
                'descricao' => 'Ciência dos números, formas e padrões',
                'icone' => '🔢',
                'cor' => '#2196F3',
                'ativo' => true,
            ],
            [
                'nome' => 'História',
                'descricao' => 'Estudo dos acontecimentos passados',
                'icone' => '📚',
                'cor' => '#FF9800',
                'ativo' => true,
            ],
            [
                'nome' => 'Geografia',
                'descricao' => 'Ciência que estuda a Terra e suas características',
                'icone' => '🌍',
                'cor' => '#009688',
                'ativo' => true,
            ],
            [
                'nome' => 'Física',
                'descricao' => 'Estudo da matéria, energia e suas interações',
                'icone' => '⚛️',
                'cor' => '#9C27B0',
                'ativo' => true,
            ],
            [
                'nome' => 'Química',
                'descricao' => 'Ciência que estuda a composição e transformações da matéria',
                'icone' => '🧪',
                'cor' => '#F44336',
                'ativo' => true,
            ],
            [
                'nome' => 'Português',
                'descricao' => 'Estudo da língua portuguesa e literatura',
                'icone' => '📖',
                'cor' => '#3F51B5',
                'ativo' => true,
            ],
            [
                'nome' => 'Inglês',
                'descricao' => 'Aprendizado da língua inglesa',
                'icone' => '🇺🇸',
                'cor' => '#00BCD4',
                'ativo' => true,
            ],
        ];

        foreach ($temas as $tema) {
            Tema::create($tema);
        }
    }
}
