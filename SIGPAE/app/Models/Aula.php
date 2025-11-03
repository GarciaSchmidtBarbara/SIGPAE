<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Aula extends Model
{
    use HasFactory;
    protected $table = 'aulas';

    protected $primaryKey = 'id_aula';

    protected $fillable = [
        'curso',
        'division',
    ];

    public function getDescripcionAttribute()
    {
        return "{$this->curso}°{$this->division}";
    }

    public function intervenciones(): BelongsToMany{
        return $this->belongsToMany(Intervencion::class, 'intervencion_aula', 'fk_aula', 'fk_intervencion');
    }
}