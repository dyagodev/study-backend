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
        Schema::table('respostas_usuario', function (Blueprint $table) {
            $table->foreignId('tentativa_id')->nullable()->after('simulado_id')->constrained('simulado_tentativas')->onDelete('cascade');
            $table->index('tentativa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('respostas_usuario', function (Blueprint $table) {
            $table->dropForeign(['tentativa_id']);
            $table->dropColumn('tentativa_id');
        });
    }
};
