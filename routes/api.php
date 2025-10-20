<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TemaController;
use App\Http\Controllers\Api\QuestaoController;
use App\Http\Controllers\Api\QuestaoGeracaoController;
use App\Http\Controllers\Api\ProximaQuestaoController;
use App\Http\Controllers\Api\ColecaoController;
use App\Http\Controllers\Api\SimuladoController;
use App\Http\Controllers\Api\EstatisticaController;
use App\Http\Controllers\Api\CreditoController;
use App\Http\Controllers\Api\PagamentoPixController;
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
    Route::get('temas', [TemaController::class, 'index']); // Listar todos disponíveis
    Route::get('temas/meus-temas', [TemaController::class, 'meusTemas']); // Listar apenas personalizados
    Route::post('temas', [TemaController::class, 'store']); // Criar personalizado
    Route::get('temas/{tema}', [TemaController::class, 'show']); // Ver detalhes
    Route::put('temas/{tema}', [TemaController::class, 'update']); // Editar personalizado
    Route::delete('temas/{tema}', [TemaController::class, 'destroy']); // Excluir personalizado

    // Questões - Geração com IA
    Route::prefix('questoes')->group(function () {
        Route::post('/gerar-por-tema', [QuestaoGeracaoController::class, 'gerarPorTema']);
        Route::post('/gerar-variacao', [QuestaoGeracaoController::class, 'gerarVariacao']);
        Route::post('/gerar-por-imagem', [QuestaoGeracaoController::class, 'gerarPorImagem']);
        Route::post('/proxima-questao', [ProximaQuestaoController::class, 'proximaQuestao']); // Buscar próxima questão não respondida
        Route::post('/gerar-mais-questoes', [ProximaQuestaoController::class, 'gerarMaisQuestoes']); // Gerar mais questões quando acabarem
        Route::post('/estatisticas-disponiveis', [ProximaQuestaoController::class, 'estatisticasDisponiveis']); // Ver quantas questões disponíveis
        Route::post('/{questao}/favoritar', [QuestaoController::class, 'favoritar']);
        Route::post('/{questao}/responder', [QuestaoController::class, 'responder']); // Responder questão avulsa
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
        Route::get('/tentativas/{tentativaId}', [SimuladoController::class, 'detalheTentativa']);

        // Gerenciar questões do simulado
        Route::post('/questoes', [SimuladoController::class, 'adicionarQuestao']);
        Route::delete('/questoes/{questaoId}', [SimuladoController::class, 'removerQuestao']);
        Route::put('/questoes/reordenar', [SimuladoController::class, 'reordenarQuestoes']);
    });

    // Estatísticas
    Route::prefix('estatisticas')->group(function () {
        Route::get('/dashboard', [EstatisticaController::class, 'dashboard']);
        Route::get('/desempenho-por-tema', [EstatisticaController::class, 'desempenhoPorTema']);
        Route::get('/evolucao-temporal', [EstatisticaController::class, 'evolucaoTemporal']);
        Route::get('/simulados', [EstatisticaController::class, 'estatisticasSimulados']);
    });

    // Créditos
    Route::prefix('creditos')->group(function () {
        Route::get('/saldo', [CreditoController::class, 'saldo']);
        Route::get('/historico', [CreditoController::class, 'historico']);
        Route::get('/estatisticas', [CreditoController::class, 'estatisticas']);
        Route::get('/custos', [CreditoController::class, 'custos']);
        Route::post('/adicionar', [CreditoController::class, 'adicionar']); // Apenas admin
    });

    // Pagamentos PIX
    Route::prefix('pagamentos/pix')->group(function () {
        Route::get('/pacotes', [PagamentoPixController::class, 'pacotes']);
        Route::post('/criar', [PagamentoPixController::class, 'criar']);
        Route::get('/{id}', [PagamentoPixController::class, 'consultar']);
        Route::get('/', [PagamentoPixController::class, 'historico']);
    });
});

// Webhook ValidaPay (não precisa autenticação)
Route::post('/webhook/validapay', [PagamentoPixController::class, 'webhook'])
    ->name('webhook.validapay');

