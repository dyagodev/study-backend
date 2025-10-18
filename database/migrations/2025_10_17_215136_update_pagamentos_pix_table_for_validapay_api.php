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
        Schema::table('pagamentos_pix', function (Blueprint $table) {
            // Remover campo qrcode_imagem (não mais necessário)
            $table->dropColumn('qrcode_imagem');

            // Atualizar comentário do campo qrcode para refletir que é EMV
            $table->text('qrcode')->comment('EMV - Código PIX Copia e Cola')->change();

            // txid agora armazena transactionId (número inteiro da ValidaPay)
            $table->string('txid', 35)->comment('Transaction ID da ValidaPay')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagamentos_pix', function (Blueprint $table) {
            // Restaurar campo qrcode_imagem
            $table->text('qrcode_imagem')->nullable()->comment('QR Code em base64');

            // Remover comentários
            $table->text('qrcode')->change();
            $table->string('txid', 35)->change();
        });
    }
};
