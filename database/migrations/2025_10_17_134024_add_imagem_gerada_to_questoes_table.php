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
            $table->string('imagem_gerada_url')->nullable()->after('imagem_url')
                ->comment('URL da imagem gerada automaticamente por IA (DALL-E) para a questão');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questoes', function (Blueprint $table) {
            $table->dropColumn('imagem_gerada_url');
        });
    }
};
