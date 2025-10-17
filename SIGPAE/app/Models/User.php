<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\PersonaTrait;

class User extends Authenticatable
{
    use HasFactory, Notifiable, PersonaTrait;

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
        'email_verified_at' => 'datetime',
        'dni' => 'integer',
        'password' => 'hashed',
    ];

    /**
     * Método personalizado que sobrescribe el del Trait.
     * Podés personalizar la descripción para el contexto de usuario.
     */
    public function getDescripcion(): string
    {
        return "Usuario: {$this->getNombreCompleto()}, Profesion: {$this->profesion}";
    }

    //relaciones con otros modelos
    //Esto asume que tenés un modelo Evento y una tabla eventos con una columna user_id que referencia al usuario.
    public function eventos() {
        return $this->hasMany(Evento::class);
    }


    public function getAuthPassword(){
        return $this->password;
    }

}
