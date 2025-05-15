<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MascotaLFV extends Model
{
    use HasFactory;
    const TIPOS=['Perro', 'Gato', 'Pájaro','Dragón','Conejo','Hamster','Tortuga','Pez','Serpiente'];
    protected $table = 'mascotas';
    protected $fillable = ['nombre', 'tipo', 'publica', 'megustas', 'user_id','descripcion'];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
