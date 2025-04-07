<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tema extends Model
{
    protected $table = 'temas';

    protected $fillable = ['nombre'];



    
    public function menciones()
    {
        return $this->belongsToMany(Mencion::class, 'mencion_tema');
    }
}
