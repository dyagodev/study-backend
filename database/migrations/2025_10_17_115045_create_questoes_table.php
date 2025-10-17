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
        Schema::create('questoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tema_id')->constrained('temas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('enunciado');
            $table->enum('nivel', ['facil', 'medio', 'dificil'])->default('medio');
            $table->text('explicacao')->nullable();
            $table->string('imagem_url')->nullable();
            $table->json('tags')->nullable();
            $table->enum('tipo_geracao', ['manual', 'ia_tema', 'ia_variacao', 'ia_imagem'])->default('manual');
            $table->boolean('favorita')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questoes');
    }
};
