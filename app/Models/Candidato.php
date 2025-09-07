<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Candidato extends Model
{
    use HasApiTokens, HasFactory, Notifiable, \Illuminate\Auth\Authenticatable;

    protected $fillable = ['nome', 'email', 'password','data_nascimento'];

    public function candidaturas()
    {
        return $this->hasMany(Candidatura::class);
    }
}