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
        Schema::create('pagamentos_pix', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('txid', 35)->unique()->comment('ID da transação ValidaPay');
            $table->decimal('valor', 10, 2)->comment('Valor em reais');
            $table->integer('creditos')->comment('Quantidade de créditos comprados');
            $table->enum('status', ['PENDENTE', 'ATIVA', 'CONCLUIDA', 'CANCELADA', 'EXPIRADA'])->default('PENDENTE');
            $table->text('qrcode')->nullable()->comment('Código PIX Copia e Cola');
            $table->text('qrcode_imagem')->nullable()->comment('QR Code em base64');
            $table->string('location_id')->nullable()->comment('ID da location ValidaPay');
            $table->timestamp('expira_em')->nullable();
            $table->timestamp('pago_em')->nullable();
            $table->json('dados_pagador')->nullable()->comment('Dados do pagador');
            $table->json('resposta_validapay')->nullable()->comment('Resposta completa da API');
            $table->timestamps();

            $table->index('user_id');
            $table->index('status');
            $table->index('txid');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagamentos_pix');
    }
};
