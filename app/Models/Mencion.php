<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mencion extends Model
{
    protected $table = 'menciones';

    protected $fillable = [
        'alerta_id',
        'titulo',
        'titulo_normalizado',
        'leida',
        'tematica',
        'fuente',
        'enlace',
        'fecha',
        'descripcion',
        'sentimiento',
    ];

    public function alerta()
    {
        return $this->belongsTo(Alerta::class);
    }
}
