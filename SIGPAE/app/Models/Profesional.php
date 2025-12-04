<?php

namespace App\Models;

use App\Enums\Siglas;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Persona;
use App\Models\Evento;
use App\Models\EsInvitadoA;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;

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
        'fk_id_persona',
        'telefono',
        'usuario',
        'profesion',
        'email',
        'siglas',
        'contrasenia',
        'google_access_token',
        'google_refresh_token',
        'google_token_expires_at',
        'notifiction_anticipation_minutos',
        'hora_envio_resumen_diario',
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
        'google_token_expires_at' => 'datetime',
        'hora_envio_resumen_diario' => 'time',
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
    public function intervencionesGeneradas(): HasMany
    {
        return $this->hasMany(Intervencion::class, 'fk_id_profesional_genera', 'id_profesional');
    }
    
    // revisado
    public function intervenciones(): BelongsToMany {
        return $this->belongsToMany(Intervencion::class, 'reune', 'fk_id_profesional', 'fk_id_intervencion');
    }

    // revisado
    public function planesGenerados(): HasMany
    {
        return $this->hasMany(PlanDeAccion::class, 'fk_id_profesional_generador', 'id_profesional');
    }

    // revisado
    public function planesParticipa(): BelongsToMany
    {
        return $this->belongsToMany(PlanDeAccion::class, 'participa_plan', 'fk_id_profesional', 'fk_id_plan_de_accion');
    }

    // revisado
    public function actas(): BelongsToMany
    {
        return $this->belongsToMany(Acta::class, 'acta_profesional', 'fk_id_profesional', 'fk_id_acta');
    }

    // revisado
    public function documentosCargados(): HasMany
    {
        return $this->hasMany(Documento::class, 'fk_id_profesional', 'id_profesional');
    }

    // Métodos personalizados
    public static function crearProfesional(array $data): Alumno
    {
        return self::create($data);
    }

    //revisa si el token de acceso de google está expirado
    public function googleTokenExpirado():bool
    {
        return $this->google_token_expires_at && $this->google_token_expires_at->isPast();
    }

    public function eventosCreados(): HasMany
    {
        return $this->hasMany(Evento::class, 'fk_id_profesional_creador', 'id_profesional');
    }

    public function eventosInvitado(): BelongsToMany
    {
        return $this->belongsToMany(Evento::class, 'es_invitado_a', 'fk_id_profesional', 'fk_id_evento')
            ->using(esInvitadoA::class);
    }


    public function sendPasswordResetNotification($token)
    {
        $url = url(route('password.reset', [
            'token' => $token,
            'email' => $this->email,
        ], false));
    
        Mail::to($this->email)->send(new ResetPasswordMail($url));
    }
}