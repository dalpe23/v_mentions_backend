<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alerta extends Model
{
    protected $table = 'alertas';

    protected $fillable = ['mencion_id', 'nivel', 'resuelta'];



    
    public function mencion()
    {
        return $this->belongsTo(Mencion::class);
    }
}