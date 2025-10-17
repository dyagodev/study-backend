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
        Schema::create('transacoes_creditos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('tipo', ['credito', 'debito']);
            $table->integer('quantidade');
            $table->integer('saldo_anterior');
            $table->integer('saldo_posterior');
            $table->string('descricao');
            $table->string('referencia_tipo')->nullable(); // 'questao', 'simulado', 'admin', 'bonus'
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['referencia_tipo', 'referencia_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transacoes_creditos');
    }
};
