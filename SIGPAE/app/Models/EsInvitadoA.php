<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EsInvitadoA extends Model
{

    protected $table = 'es_invitado_a';

    protected $primaryKey = 'id_es_invitado_a';

    // permitir asignación masiva para los campos pivot
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

    // revisado
    public function evento()
    {
        return $this->belongsTo(Evento::class, 'fk_id_evento', 'id_evento');
    }

    // revisado
    public function profesional()
    {
        return $this->belongsTo(Profesional::class, 'fk_id_profesional', 'id_profesional');
    }

    public $timestamps = true;
}
