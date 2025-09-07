<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Candidato;

class AuthController extends Controller
{

 public function register(Request $request)
{
    $request->validate([
        'nome' => 'required|string',
        'email' => 'required|email|unique:candidatos',
        'data_nascimento' => 'nullable|date',
        'password' => 'required|confirmed|min:6'
    ]);

    $candidato = Candidato::create([
        'nome' => $request->nome,
        'email' => $request->email,
         'password' => Hash::make($request->password),
        'data_nascimento' => $request->data_nascimento
       
    ]);

    $token = $candidato->createToken('plataforma-candidaturas')->plainTextToken;

    return response()->json([
        'mensagem' => 'Registro realizado com sucesso!',
        'token' => $token,
        'candidato' => $candidato
    ], 201);
}
 public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $candidato = Candidato::where('email', $request->email)->first();

    if (! $candidato || ! Hash::check($request->password, $candidato->password)) {
        return response()->json(['erro' => 'Credenciais inválidas'], 401);
    }

    $token = $candidato->createToken('plataforma-candidaturas')->plainTextToken;

    return response()->json([
        'token' => $token,
        'candidato' => $candidato
    ]);
}

public function logout(Request $request)
{
    $request->user()->tokens()->delete();
    return response()->json(['mensagem' => 'Logout realizado com sucesso']);
}
}