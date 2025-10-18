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
            $table->enum('nivel_dificuldade', ['facil', 'medio', 'dificil', 'muito_dificil'])
                  ->default('medio')
                  ->after('nivel')
                  ->comment('Nível de dificuldade da questão de concurso');

            $table->index('nivel_dificuldade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questoes', function (Blueprint $table) {
            $table->dropIndex(['nivel_dificuldade']);
            $table->dropColumn('nivel_dificuldade');
        });
    }
};
