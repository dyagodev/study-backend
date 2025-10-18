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
        Schema::table('users', function (Blueprint $table) {
            // Alterar o padrão de créditos de 100 para 50
            $table->integer('creditos')->default(50)->change();
            
            // Alterar o padrão de créditos semanais de 100 para 50
            $table->integer('creditos_semanais')->default(50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Reverter para os valores anteriores (100)
            $table->integer('creditos')->default(100)->change();
            $table->integer('creditos_semanais')->default(100)->change();
        });
    }
};
