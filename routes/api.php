<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartorioController;
use App\Http\Controllers\Api\ImovelController;
use App\Http\Controllers\Api\ProprietarioController;
use App\Http\Controllers\Api\RelatorioController;
use App\Http\Controllers\Api\UsuarioController;
use Illuminate\Support\Facades\Route;

// ── Autenticação ─────────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

// ── Rotas protegidas ─────────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Cartórios
    Route::apiResource('cartorios', CartorioController::class)
        ->parameters(['cartorios' => 'cartorio']);

    // Imóveis
    Route::apiResource('imoveis', ImovelController::class)
        ->parameters(['imoveis' => 'imovel']);

    // Usuários
    Route::apiResource('usuarios', UsuarioController::class)
        ->parameters(['usuarios' => 'usuario']);

    // Proprietários
    Route::get('proprietarios/busca', [ProprietarioController::class, 'busca']);
    Route::apiResource('proprietarios', ProprietarioController::class)
        ->parameters(['proprietarios' => 'proprietario']);

    // Relatórios
    Route::prefix('relatorios')->group(function () {
        Route::get('/resumo', [RelatorioController::class, 'resumo']);
        Route::get('/imoveis-por-cartorio', [RelatorioController::class, 'imoveisPorCartorio']);
        Route::get('/imoveis-por-status', [RelatorioController::class, 'imoveisPorStatus']);
        Route::get('/usuarios-por-cartorio', [RelatorioController::class, 'usuariosPorCartorio']);
        Route::get('/imoveis', [RelatorioController::class, 'relatorioImoveis']);
    });
});
