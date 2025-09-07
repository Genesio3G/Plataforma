<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Programa;

class ProgramaController extends Controller
{
  public function index()
{
    $programas = Programa::where('estado', 'ativo')
        ->whereDate('data_inicio', '<=', now())
        ->whereDate('data_final', '>=', now())
        ->get();

    return response()->json($programas);
}

public function store(Request $request)
{
    $request->validate([
        'nome' => 'required|string',
        'descricao' => 'nullable|string',
        'data_inicio' => 'required|date',
        'data_final' => 'required|date|after_or_equal:data_inicio',
        'estado' => 'required|in:ativo,inativo'
    ]);

    $programa = Programa::create($request->all());

    return response()->json($programa, 201);
}
}