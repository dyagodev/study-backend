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
            // Alterar a coluna nivel para incluir 'concurso'
            $table->enum('nivel', ['facil', 'medio', 'dificil', 'concurso'])->default('medio')->change();

            // Adicionar coluna assunto
            $table->string('assunto')->nullable()->after('tema_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questoes', function (Blueprint $table) {
            // Reverter a coluna nivel para os valores originais
            $table->enum('nivel', ['facil', 'medio', 'dificil'])->default('medio')->change();

            // Remover coluna assunto
            $table->dropColumn('assunto');
        });
    }
};
