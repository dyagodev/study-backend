<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RespostaUsuario;
use App\Models\Simulado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SimuladoController extends Controller
{
    public function index(Request $request)
    {
        $query = Simulado::with(['user'])
            ->withCount('questoes')
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc');

        // Filtro por status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $simulados = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $simulados->items(),
            'meta' => [
                'current_page' => $simulados->currentPage(),
                'last_page' => $simulados->lastPage(),
                'per_page' => $simulados->perPage(),
                'total' => $simulados->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'tempo_limite' => 'nullable|integer|min:1',
            'embaralhar_questoes' => 'sometimes|boolean',
            'mostrar_gabarito' => 'sometimes|boolean',
            'status' => 'sometimes|in:rascunho,ativo,arquivado',
            'questoes' => 'required|array|min:1',
            'questoes.*.questao_id' => 'required|exists:questoes,id',
            'questoes.*.pontuacao' => 'sometimes|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $simulado = Simulado::create([
                'user_id' => $request->user()->id,
                'titulo' => $request->titulo,
                'descricao' => $request->descricao,
                'tempo_limite' => $request->tempo_limite,
                'embaralhar_questoes' => $request->embaralhar_questoes ?? false,
                'mostrar_gabarito' => $request->mostrar_gabarito ?? true,
                'status' => $request->status ?? 'rascunho',
            ]);

            foreach ($request->questoes as $index => $questaoData) {
                $simulado->questoes()->attach($questaoData['questao_id'], [
                    'ordem' => $index + 1,
                    'pontuacao' => $questaoData['pontuacao'] ?? 1.0,
                ]);
            }

            DB::commit();

            $simulado->load(['questoes']);

            return response()->json([
                'success' => true,
                'message' => 'Simulado criado com sucesso',
                'data' => $simulado,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar simulado: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Simulado $simulado, Request $request)
    {
        if ($simulado->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para visualizar este simulado',
            ], 403);
        }

        $simulado->load(['questoes.tema', 'questoes.alternativas']);

        return response()->json([
            'success' => true,
            'data' => $simulado,
        ]);
    }

    public function update(Request $request, Simulado $simulado)
    {
        if ($simulado->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para editar este simulado',
            ], 403);
        }

        $request->validate([
            'titulo' => 'sometimes|string|max:255',
            'descricao' => 'nullable|string',
            'tempo_limite' => 'nullable|integer|min:1',
            'embaralhar_questoes' => 'sometimes|boolean',
            'mostrar_gabarito' => 'sometimes|boolean',
            'status' => 'sometimes|in:rascunho,ativo,arquivado',
        ]);

        $simulado->update($request->only([
            'titulo', 'descricao', 'tempo_limite', 
            'embaralhar_questoes', 'mostrar_gabarito', 'status'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Simulado atualizado com sucesso',
            'data' => $simulado,
        ]);
    }

    public function destroy(Simulado $simulado, Request $request)
    {
        if ($simulado->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para excluir este simulado',
            ], 403);
        }

        $simulado->delete();

        return response()->json([
            'success' => true,
            'message' => 'Simulado excluído com sucesso',
        ]);
    }

    public function iniciar(Simulado $simulado, Request $request)
    {
        if ($simulado->status !== 'ativo') {
            return response()->json([
                'success' => false,
                'message' => 'Este simulado não está ativo',
            ], 422);
        }

        $questoes = $simulado->questoes()->with(['tema', 'alternativas'])->get();

        if ($simulado->embaralhar_questoes) {
            $questoes = $questoes->shuffle();
        }

        return response()->json([
            'success' => true,
            'message' => 'Simulado iniciado',
            'data' => [
                'simulado' => $simulado->only(['id', 'titulo', 'descricao', 'tempo_limite']),
                'questoes' => $questoes,
                'tempo_inicio' => now(),
            ],
        ]);
    }

    public function responder(Request $request, Simulado $simulado)
    {
        $request->validate([
            'respostas' => 'required|array',
            'respostas.*.questao_id' => 'required|exists:questoes,id',
            'respostas.*.alternativa_id' => 'required|exists:alternativas,id',
            'respostas.*.tempo_resposta' => 'nullable|integer',
        ]);

        DB::beginTransaction();
        try {
            $acertos = 0;
            $totalQuestoes = count($request->respostas);

            foreach ($request->respostas as $resposta) {
                // Verificar se a alternativa está correta
                $alternativa = \App\Models\Alternativa::find($resposta['alternativa_id']);
                $correta = $alternativa->correta;

                if ($correta) {
                    $acertos++;
                }

                RespostaUsuario::create([
                    'user_id' => $request->user()->id,
                    'questao_id' => $resposta['questao_id'],
                    'alternativa_id' => $resposta['alternativa_id'],
                    'simulado_id' => $simulado->id,
                    'correta' => $correta,
                    'tempo_resposta' => $resposta['tempo_resposta'] ?? null,
                ]);
            }

            DB::commit();

            $percentualAcerto = ($acertos / $totalQuestoes) * 100;

            return response()->json([
                'success' => true,
                'message' => 'Respostas registradas com sucesso',
                'data' => [
                    'acertos' => $acertos,
                    'total_questoes' => $totalQuestoes,
                    'percentual_acerto' => round($percentualAcerto, 2),
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao registrar respostas: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function resultado(Simulado $simulado, Request $request)
    {
        // Buscar a data/hora da resposta mais recente
        $ultimaResposta = RespostaUsuario::where('simulado_id', $simulado->id)
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$ultimaResposta) {
            return response()->json([
                'success' => false,
                'message' => 'Você ainda não respondeu este simulado',
            ], 404);
        }

        // Buscar apenas respostas da última tentativa
        // Considera última tentativa = respostas nos últimos 30 minutos a partir da resposta mais recente
        $dataLimite = $ultimaResposta->created_at->copy()->subMinutes(30);
        
        $respostas = RespostaUsuario::where('simulado_id', $simulado->id)
            ->where('user_id', $request->user()->id)
            ->where('created_at', '>=', $dataLimite)
            ->with(['questao.alternativas', 'alternativa'])
            ->orderBy('created_at', 'asc')
            ->get();

        $totalQuestoes = $respostas->count();
        $acertos = $respostas->where('correta', true)->count();
        $erros = $totalQuestoes - $acertos;
        $percentualAcerto = $totalQuestoes > 0 ? ($acertos / $totalQuestoes) * 100 : 0;

        $detalhesRespostas = $respostas->map(function ($resposta) use ($simulado) {
            return [
                'questao_id' => $resposta->questao_id,
                'questao_enunciado' => $resposta->questao->enunciado,
                'alternativa_escolhida' => $resposta->alternativa->texto ?? 'Não respondida',
                'correta' => $resposta->correta,
                'alternativa_correta' => $simulado->mostrar_gabarito 
                    ? $resposta->questao->alternativas->where('correta', true)->first()->texto 
                    : null,
                'explicacao' => $simulado->mostrar_gabarito ? $resposta->questao->explicacao : null,
                'tempo_resposta' => $resposta->tempo_resposta,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'simulado' => $simulado->only(['id', 'titulo', 'descricao']),
                'tentativa' => [
                    'data' => $ultimaResposta->created_at->format('Y-m-d H:i:s'),
                ],
                'estatisticas' => [
                    'total_questoes' => $totalQuestoes,
                    'acertos' => $acertos,
                    'erros' => $erros,
                    'percentual_acerto' => round($percentualAcerto, 2),
                ],
                'respostas' => $detalhesRespostas,
            ],
        ]);
    }

    public function historico(Simulado $simulado, Request $request)
    {
        // Buscar todas as respostas do usuário para este simulado, ordenadas por data (mais recente primeiro)
        $todasRespostas = RespostaUsuario::where('simulado_id', $simulado->id)
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($todasRespostas->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Você ainda não respondeu este simulado',
            ], 404);
        }

        // Agrupar respostas por tentativa
        // Lógica: Se o gap entre duas respostas consecutivas > 5 minutos, são tentativas diferentes
        $tentativas = [];
        $tentativaAtual = 1;
        $tentativas[$tentativaAtual] = [$todasRespostas[0]]; // Primeira resposta

        for ($i = 1; $i < count($todasRespostas); $i++) {
            $respostaAtual = $todasRespostas[$i];
            $respostaAnterior = $todasRespostas[$i - 1];
            
            // Calcular diferença de tempo (em segundos) entre respostas consecutivas
            $diferencaTempo = $respostaAnterior->created_at->timestamp - $respostaAtual->created_at->timestamp;
            
            // Se diferença > 5 minutos (300 segundos), é uma tentativa diferente
            if ($diferencaTempo > 300) {
                $tentativaAtual++;
            }
            
            $tentativas[$tentativaAtual][] = $respostaAtual;
        }

        // Processar cada tentativa
        $historicoTentativas = [];
        foreach ($tentativas as $numero => $respostasTentativa) {
            $respostas = collect($respostasTentativa);
            $totalQuestoes = $respostas->count();
            $acertos = $respostas->where('correta', true)->count();
            $percentualAcerto = $totalQuestoes > 0 ? ($acertos / $totalQuestoes) * 100 : 0;

            $historicoTentativas[] = [
                'tentativa' => $numero,
                'data' => $respostas->first()->created_at->format('Y-m-d H:i:s'),
                'total_questoes' => $totalQuestoes,
                'acertos' => $acertos,
                'erros' => $totalQuestoes - $acertos,
                'percentual_acerto' => round($percentualAcerto, 2),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'simulado' => $simulado->only(['id', 'titulo', 'descricao']),
                'total_tentativas' => count($tentativas),
                'tentativas' => $historicoTentativas,
            ],
        ]);
    }
}
