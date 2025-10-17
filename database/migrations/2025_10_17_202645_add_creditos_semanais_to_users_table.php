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
            $table->integer('creditos_semanais')->default(100)->after('creditos');
            $table->timestamp('ultima_renovacao')->nullable()->after('creditos_semanais');
            $table->index('ultima_renovacao');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['ultima_renovacao']);
            $table->dropColumn(['creditos_semanais', 'ultima_renovacao']);
        });
    }
};
