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
        Schema::create('respostas_usuario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('questao_id')->constrained('questoes')->onDelete('cascade');
            $table->foreignId('alternativa_id')->nullable()->constrained('alternativas')->onDelete('cascade');
            $table->foreignId('simulado_id')->nullable()->constrained('simulados')->onDelete('cascade');
            $table->boolean('correta')->default(false);
            $table->integer('tempo_resposta')->nullable(); // em segundos
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('respostas_usuario');
    }
};
