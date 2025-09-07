<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CandidatoController;
use App\Http\Controllers\ProgramaController;
use App\Http\Controllers\CandidaturaController;

// 🔓 Rotas públicas (sem autenticação)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// 🔐 Rotas protegidas por Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // 👤 Candidato autenticado
    Route::get('/candidato', [CandidatoController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // 📋 Programas
    Route::get('/programas', [ProgramaController::class, 'index']);
    Route::post('/programas', [ProgramaController::class, 'store']);

    // 📝 Candidaturas
    Route::get('/candidaturas', [CandidaturaController::class, 'index']);
    Route::post('/candidaturas', [CandidaturaController::class, 'store']);
});