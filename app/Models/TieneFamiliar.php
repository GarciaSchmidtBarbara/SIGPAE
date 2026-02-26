<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Enums\Parentesco;

class TieneFamiliar extends Pivot
{
    protected $table = 'tiene_familiar';

    protected $primaryKey = 'id_tiene_familiar';

    public $incrementing = true;

    protected $fillable = [
        'fk_id_alumno',
        'fk_id_familiar',
        'parentesco',
        'observaciones',
        'activa',
        'otro_parentesco'
    ];

    protected $casts = [
        'parentesco' => Parentesco::class,
        'activa'     => 'boolean',
    ];
}
