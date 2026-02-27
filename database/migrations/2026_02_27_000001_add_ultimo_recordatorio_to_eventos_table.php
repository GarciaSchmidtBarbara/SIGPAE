<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            //Registra cuándo se envió el último recordatorio de derivación externa.
            //Permite calcular si ya pasó el periodo y hay que enviar uno nuevo.
            $table->timestamp('ultimo_recordatorio_at')->nullable()->after('periodo_recordatorio');
        });
    }

    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn('ultimo_recordatorio_at');
        });
    }
};
