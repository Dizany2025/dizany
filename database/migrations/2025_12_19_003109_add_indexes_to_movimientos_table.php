<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            $table->index('fecha', 'idx_movimientos_fecha');
            $table->index(['tipo', 'estado'], 'idx_movimientos_tipo_estado');
            $table->index('metodo_pago', 'idx_movimientos_metodo_pago');
        });
    }

    public function down(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            $table->dropIndex('idx_movimientos_fecha');
            $table->dropIndex('idx_movimientos_tipo_estado');
            $table->dropIndex('idx_movimientos_metodo_pago');
        });
    }
};

