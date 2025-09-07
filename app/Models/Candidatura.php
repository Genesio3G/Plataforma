<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candidatura extends Model
{
    protected $fillable = ['candidato_id', 'programa_id', 'status'];

    public function candidato()
    {
        return $this->belongsTo(Candidato::class);
    }

    public function programa()
    {
        return $this->belongsTo(Programa::class);
    }
}