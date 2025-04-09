<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alerta extends Model
{
    protected $table = 'alertas';

    protected $fillable = [
        'nombre',
        'resuelta',
    ];

    public function menciones()
    {
        return $this->hasMany(Mencion::class);
    }
}
