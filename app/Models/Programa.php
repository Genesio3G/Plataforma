<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Programa extends Model
{
    protected $fillable = ['nome', 'descricao', 'data_inicio', 'data_final', 'estado'];

    public function candidaturas()
    {
        return $this->hasMany(Candidatura::class);
    }

    public function estaDisponivel()
    {
        return $this->estado === 'ativo'
            && now()->between($this->data_inicio, $this->data_final);
    }
}