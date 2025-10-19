<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\App\Database\Eloquent\relations\BelongsTo;
use Illuminate\Database\Eloquent\relations\BelongsToMany;

class Hermano extends Model
{
    protected $table = 'hermanos';

    protected $primaryKey = 'id_hermano';

    protected $fillable = [
        'observaciones',
        'fk_id_alumno',
    ];

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'fk_id_persona');
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class, 'fk_id_alumno');
    }
}