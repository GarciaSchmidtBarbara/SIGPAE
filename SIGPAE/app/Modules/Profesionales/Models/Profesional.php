<?php

namespace App\Modules\Profesionales\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Modules\Personas\Models\Persona;
use App\Models\Evento;
use App\Models\Asiste;

class Profesional extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'profesionales';

    protected $primaryKey = 'id_profesional';

    protected $fillable = [
    'nombre',
    'apellido',
    'dni',
    'profesion',
    'telefono',
    'usuario',
    'email',
    'password',
    'fk_id_persona',
];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'fk_id_persona', 'id_persona');
    }
    
    public function eventos()
    {
        return $this->hasMany(Evento::class, 'creador_id', 'id_profesional');
    }

     public function eventosAsistidos()
    {
        return $this->belongsToMany(Evento::class, 'evento_profesional', 'id_profesional', 'id_evento')
                    ->using(Asiste::class)
                    ->withPivot('asistio', 'asistencia_confirmada')
                    ->withTimestamps();
    }

    public function getDescripcion(): string
    {
        return "Usuario: {$this->getNombreCompleto()}, Profesion: {$this->profesion}";
    }
    
    public function getAuthPassword(){
        return $this->password;
    }

    public function intervencionesCreadas(): HasMany
    {
        return $this->hasMany(\App\Modules\Planes\Models\Intervencion::class, 'fk_profesional_creador');
    }

    public function planesCreados(): HasMany
    {
        return $this->hasMany(\App\Modules\Planes\Models\PlanDeAccion::class, 'fk_id_profesional_creador');
    }
    
    public function planesResponsables(): BelongsToMany
    {
        return $this->belongsToMany(\App\Modules\Planes\Models\PlanDeAccion::class, 'responsables', 'fk_id_profesional_responsable', 'fk_id_plan');
    }

}
