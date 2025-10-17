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
        Schema::create('simulado_questao', function (Blueprint $table) {
            $table->id();
            $table->foreignId('simulado_id')->constrained('simulados')->onDelete('cascade');
            $table->foreignId('questao_id')->constrained('questoes')->onDelete('cascade');
            $table->integer('ordem')->default(0);
            $table->decimal('pontuacao', 5, 2)->default(1.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simulado_questao');
    }
};
