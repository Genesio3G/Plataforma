<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Programa;
use App\Models\Candidatura;


class CandidaturaController extends Controller
{
  public function index()
{
    $candidaturas = Candidatura::where('candidato_id', Auth::id())
        ->with('programa')
        ->get();

    return response()->json($candidaturas);
}

public function store(Request $request)
{
    $candidato = $request->user();

    if (! $candidato) {
        return response()->json([
            'sucesso' => false,
            'mensagem' => 'Autenticação necessária. Faça login para continuar.'
        ], 401);
    }

    $request->validate([
        'programa_id' => 'required|exists:programas,id'
    ], [
        'programa_id.required' => 'O campo programa é obrigatório.',
        'programa_id.exists' => 'O programa selecionado não foi encontrado ou está inválido.'
    ]);

    $programa = Programa::find($request->programa_id);

    if (! $programa || ! $programa->estaDisponivel()) {
        return response()->json([
            'sucesso' => false,
            'mensagem' => 'Programa indisponível para inscrição.'
        ], 403);
    }

    $jaInscrito = Candidatura::where('candidato_id', $candidato->id)
        ->where('programa_id', $programa->id)
        ->exists();

    if ($jaInscrito) {
        return response()->json([
            'sucesso' => false,
            'mensagem' => 'Você já está inscrito neste programa.'
        ], 409);
    }

    $candidatura = Candidatura::create([
        'candidato_id' => $candidato->id,
        'programa_id' => $programa->id,
        'status' => 'pendente'
    ]);

    return response()->json([
        'sucesso' => true,
        'mensagem' => 'Inscrição realizada com sucesso.',
        'candidatura' => $candidatura
    ], 201);
}
}
