<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EsInvitadoA extends Pivot
{
    protected $table = 'es_invitado_a';

    protected $primaryKey = null;  // No hay PK específica en pivote
    public $incrementing = false;

    protected $fillable = [
        'fk_id_evento',
        'fk_id_profesional',
        'asistio',
        'confirmacion', 
    ];

    protected $casts = [
        'asistio' => 'boolean',
        'confirmacion' => 'boolean',
    ];

    public function evento()
    {
        return $this->belongsTo(Evento::class, 'fk_id_evento', 'id_evento');
    }

    public function profesional()
    {
        return $this->belongsTo(Profesional::class, 'fk_id_profesional', 'id_profesional');
    }

    public $timestamps = true;  
}