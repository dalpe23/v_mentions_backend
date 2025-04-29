<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alerta extends Model
{
    protected $table = 'alertas';

    protected $fillable = [
        'nombre',
        'resuelta',
        'user_id',
        'url',
    ];

    public function menciones()
    {
        return $this->hasMany(Mencion::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
