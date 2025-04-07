<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mencion extends Model
{
    protected $table = 'menciones';

    protected $fillable = [        
    'titulo',
    'enlace',
    'fuente',
    'fecha',
    'descripcion',
    'sentimiento'
    ];




    public function temas()
    {
        return $this->belongsToMany(Tema::class, 'mencion_tema');
    }

    public function alerta()
    {
        return $this->hasOne(Alerta::class);
    }
}
