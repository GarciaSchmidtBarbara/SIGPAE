<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Persona;

class Profesional extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Nombre de la tabla asociada en la base de datos.
     * Laravel lo infiere como 'users' por convención, pero lo dejamos explícito.
     */
    protected $table = 'profesionales';

    /**
     * Clave primaria personalizada
     */
    protected $primaryKey = 'id_profesional';

    /**
     * Campos que pueden asignarse masivamente (por ejemplo, al usar create() o fill()).
     * Deben coincidir con los nombres de columnas en la tabla 'users'.
     */
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

    /**
     * Campos que se ocultan al serializar el modelo (por ejemplo, al devolverlo como JSON).
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casts automáticos: transforma atributos al tipo indicado.
     * Esto permite que 'fecha_nacimiento' sea un objeto DateTime, y 'password' se hashee automáticamente.
     */
    protected $casts = [
        'email_verified_at' => 'datetime', //Movi dni para persona
        'password' => 'hashed',
    ];

    public function persona(): BelongsTo
    {   //Un profesional es una persona
        return $this->belongsTo(Persona::class, 'fk_id_persona', 'id_persona');
    }
    
    public function eventos()
    {   //Un profesional puede crear muchos eventos
        return $this->hasMany(Evento::class, 'creador_id', 'id_profesional');
    }

     public function eventosAsistidos()
    {   //Un profesional puede asistir a muchos eventos (relación muchos a muchos)
        return $this->belongsToMany(Evento::class, 'evento_profesional', 'id_profesional', 'id_evento')
                    ->using(Asiste::class)
                    ->withPivot('asistio', 'asistencia_confirmada')
                    ->withTimestamps();
    }

    /**
     * Método personalizado que sobrescribe el del Trait.
     * Podés personalizar la descripción para el contexto de usuario.
     */
    public function getDescripcion(): string
    {
        return "Usuario: {$this->getNombreCompleto()}, Profesion: {$this->profesion}";
    }
    
    public function getAuthPassword(){
        return $this->password;
    }

    public function intervencionesCreadas(): HasMany
    {
        return $this->hasMany(Intervencion::class, 'fk_profesional_creador');
    }

    public function planesCreados(): HasMany
    {
        return $this->hasMany(PlanDeAccion::class, 'fk_id_profesional_creador');
    }
    
    public function planesResponsables(): BelongsToMany
    {
        return $this->belongsToMany(PlanDeAccion::class, 'responsables', 'fk_id_profesional_responsable', 'fk_id_plan');
    }



}
