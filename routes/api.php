<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TemaController;
use App\Http\Controllers\Api\QuestaoController;
use App\Http\Controllers\Api\QuestaoGeracaoController;
use App\Http\Controllers\Api\ColecaoController;
use App\Http\Controllers\Api\SimuladoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Rotas de Autenticação
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rotas protegidas
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);

    // Temas
    Route::apiResource('temas', TemaController::class);

    // Questões - Geração com IA
    Route::prefix('questoes')->group(function () {
        Route::post('/gerar-por-tema', [QuestaoGeracaoController::class, 'gerarPorTema']);
        Route::post('/gerar-variacao', [QuestaoGeracaoController::class, 'gerarVariacao']);
        Route::post('/gerar-por-imagem', [QuestaoGeracaoController::class, 'gerarPorImagem']);
        Route::post('/{questao}/favoritar', [QuestaoController::class, 'favoritar']);
    });

    // Questões - CRUD
    Route::apiResource('questoes', QuestaoController::class);

    // Coleções
    Route::apiResource('colecoes', ColecaoController::class);
    Route::prefix('colecoes/{colecao}')->group(function () {
        Route::post('/questoes', [ColecaoController::class, 'adicionarQuestao']);
        Route::delete('/questoes/{questao}', [ColecaoController::class, 'removerQuestao']);
        Route::get('/questoes', [ColecaoController::class, 'listarQuestoes']);
    });

    // Simulados
    Route::apiResource('simulados', SimuladoController::class);
    Route::prefix('simulados/{simulado}')->group(function () {
        Route::post('/iniciar', [SimuladoController::class, 'iniciar']);
        Route::post('/responder', [SimuladoController::class, 'responder']);
        Route::get('/resultado', [SimuladoController::class, 'resultado']);
        Route::get('/historico', [SimuladoController::class, 'historico']);
    });
});

