<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Candidato;
use Illuminate\Support\Facades\Hash;

class CandidatoController extends Controller
{
   public function me()
{
    $candidato = Candidato::find(Auth::id());
    return response()->json($candidato);
}

public function register(Request $request)
{
   $request->validate([
    'nome' => 'required|string',
    'email' => 'required|email|unique:candidatos',
     'password' => 'required|confirmed|min:6',
    'data_nascimento' => 'nullable|date'
   
    ]);

    $candidato = Candidato::create([
    'nome' => $request->nome,
    'email' => $request->email,
     'password' => Hash::make($request->password),
    'data_nascimento' => $request->data_nascimento
   
]);

    return response()->json($candidato, 201);
}
}