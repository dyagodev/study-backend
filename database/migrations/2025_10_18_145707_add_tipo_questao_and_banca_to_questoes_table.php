<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('questoes', function (Blueprint $table) {
            // Tipo de questão: concurso, enem, prova_crc, oab, outros
            $table->enum('tipo_questao', ['concurso', 'enem', 'prova_crc', 'oab', 'outros'])
                ->default('concurso')
                ->after('nivel_dificuldade');
            
            // Campo para especificar quando tipo_questao = 'outros'
            $table->string('tipo_questao_outro', 100)
                ->nullable()
                ->after('tipo_questao');
            
            // Banca realizadora (opcional)
            $table->string('banca', 150)
                ->nullable()
                ->after('tipo_questao_outro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questoes', function (Blueprint $table) {
            $table->dropColumn(['tipo_questao', 'tipo_questao_outro', 'banca']);
        });
    }
};
