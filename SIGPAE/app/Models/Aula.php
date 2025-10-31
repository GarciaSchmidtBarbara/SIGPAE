        <?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aula extends Model
{
    protected $table = 'aulas';

    protected $primaryKey = 'id_aula';

   
    protected $fillable = [
        'curso',
        'division',
    ];

    public function getDescripcionAttribute()
    {
        return $this->curso . ' ' . $this->division;
    }

    public function intervenciones(): BelongsToMany{
        return $this->belongsToMany(Intervencion::class, 'intervencion_aula', 'fk_id_aula', 'fk_id_intervencion');
    }
}