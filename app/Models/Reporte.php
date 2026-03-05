<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Reporte extends Model
{
    // Función para traducir meses de PostgreSQL al español
    public static function traducirMes($mesIngles) {
        $meses = [
            'January' => 'Enero', 'February' => 'Febrero', 'March' => 'Marzo',
            'April' => 'Abril', 'May' => 'Mayo', 'June' => 'Junio',
            'July' => 'Julio', 'August' => 'Agosto', 'September' => 'Septiembre',
            'October' => 'Octubre', 'November' => 'Noviembre', 'December' => 'Diciembre'
        ];
        return $meses[$mesIngles] ?? $mesIngles;
    }

    // Lógica para el gráfico de evolución
    public static function getEvolucionIntervenciones($meses = 6) {
        $datos = Intervencion::select(
            DB::raw('count(*) as total'),
            DB::raw("TRIM(to_char(fecha_hora_intervencion, 'Month')) as mes"),
            DB::raw("extract(month from fecha_hora_intervencion) as mes_num")
        )
        ->where('fecha_hora_intervencion', '>=', now()->subMonths($meses))
        ->groupBy('mes', 'mes_num')
        ->orderBy('mes_num')
        ->get();

        // Aplicamos la traducción aquí mismo
        return $datos->map(function($item) {
            $item->mes = self::traducirMes($item->mes);
            return $item;
        });
    }
}