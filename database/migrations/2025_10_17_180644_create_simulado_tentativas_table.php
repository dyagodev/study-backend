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
        Schema::create('simulado_tentativas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('simulado_id')->constrained('simulados')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('numero_tentativa')->comment('Número sequencial da tentativa do usuário');
            $table->integer('total_questoes');
            $table->integer('acertos');
            $table->integer('erros');
            $table->decimal('percentual_acerto', 5, 2);
            $table->integer('tempo_total')->nullable()->comment('Tempo total em segundos');
            $table->timestamp('data_inicio')->nullable();
            $table->timestamp('data_fim')->nullable();
            $table->timestamps();

            // Índices
            $table->index(['simulado_id', 'user_id']);
            $table->unique(['simulado_id', 'user_id', 'numero_tentativa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simulado_tentativas');
    }
};
