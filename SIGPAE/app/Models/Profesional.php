<?php

namespace App\Models;

use App\Enums\Siglas;
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

  
    protected $fillable = [
        // campos mínimos de profesional / usuario
        'telefono',
        'usuario',
        'profesion',
        'email',
        'siglas',
        'contrasenia',
    ];

    /**
     * Campos que se ocultan al serializar el modelo (por ejemplo, al devolverlo como JSON).
     */
    protected $hidden = [
        'contrasenia',
        'remember_token',
    ];

    /**
     * Casts automáticos: transforma atributos al tipo indicado.
     * Esto permite que 'fecha_nacimiento' sea un objeto DateTime, y 'contrasenia' se hashee automáticamente.
     */
    protected $casts = [
        'email_verified_at' => 'datetime', 
        'contrasenia' => 'hashed',
        'siglas' => Siglas::class,
    ];

    // revisado
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'fk_id_persona', 'id_persona');
    }
    
    // revisado
    public function eventos():HasMany
    {
        return $this->hasMany(Evento::class, 'fk_id_profesional_creador', 'id_profesional');
    }

    // revisado
    public function esInvitadoA():HasMany
    {
        return $this->hasMany(EsInvitadoA::class, 'fk_id_profesional', 'id_profesional');
    }

    /**
     * Método personalizado que sobrescribe el del Trait.
     * Podés personalizar la descripción para el contexto de usuario.
     */
    public function getDescripcion(): string
    {
        $persona = $this->persona;
        $nombre = $persona ? ($persona->nombre . ' ' . $persona->apellido) : ($this->usuario ?? '');
        return "Profesional: {$nombre}";
    }
    
    public function getAuthPassword(){
        return $this->contrasenia;
    }

    // revisado
    public function intervencionesCreadas(): HasMany
    {
        return $this->hasMany(Intervencion::class, 'fk_id_profesional', 'id_profesional');
    }
    
    // revisado
    public function intervenciones(): BelongsToMany {
        return $this->belongsToMany(Intervencion::class, 'reune',  'fk_id_profesional','fk_id_intervencion');
    }

    // revisado
    public function planesCreados(): HasMany
    {
        return $this->hasMany(PlanDeAccion::class, 'fk_id_profesional_creador', 'id_profesional');
    }

    // revisado
    public function planesParticipa(): BelongsToMany
    {
        return $this->belongsToMany(PlanDeAccion::class, 'participa_plan', 'fk_id_profesional', 'fk_id_plan_de_accion');
    }

    // revisado
    public function estaEnActas(): BelongsToMany
    {
        return $this->belongsToMany(Acta::class, 'acta_profesional', 'fk_id_profesional', 'fk_id_acta');
    }

    // revisado
    public function documentosCargados(): HasMany
    {
        return $this->hasMany(Documento::class, 'fk_id_profesional', 'id_profesional');
    }
}