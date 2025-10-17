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
        Schema::create('simulados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->integer('tempo_limite')->nullable(); // em minutos
            $table->boolean('embaralhar_questoes')->default(false);
            $table->boolean('mostrar_gabarito')->default(true);
            $table->enum('status', ['rascunho', 'ativo', 'arquivado'])->default('rascunho');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simulados');
    }
};
