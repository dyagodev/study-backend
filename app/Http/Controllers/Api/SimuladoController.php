<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RespostaUsuario;
use App\Models\Simulado;
use App\Models\SimuladoTentativa;
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
            'data_inicio' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            $acertos = 0;
            $totalQuestoes = count($request->respostas);
            $tempoTotal = 0;

            // Calcular número da próxima tentativa
            $ultimaTentativa = SimuladoTentativa::where('simulado_id', $simulado->id)
                ->where('user_id', $request->user()->id)
                ->max('numero_tentativa');
            
            $numeroTentativa = ($ultimaTentativa ?? 0) + 1;

            // Criar registro da tentativa
            $tentativa = SimuladoTentativa::create([
                'simulado_id' => $simulado->id,
                'user_id' => $request->user()->id,
                'numero_tentativa' => $numeroTentativa,
                'total_questoes' => $totalQuestoes,
                'acertos' => 0, // Será atualizado depois
                'erros' => 0,
                'percentual_acerto' => 0,
                'tempo_total' => 0,
                'data_inicio' => $request->data_inicio ?? now(),
                'data_fim' => now(),
            ]);

            // Registrar cada resposta
            foreach ($request->respostas as $resposta) {
                // Verificar se a alternativa está correta
                $alternativa = \App\Models\Alternativa::find($resposta['alternativa_id']);
                $correta = $alternativa->correta;

                if ($correta) {
                    $acertos++;
                }

                $tempoResposta = $resposta['tempo_resposta'] ?? 0;
                $tempoTotal += $tempoResposta;

                RespostaUsuario::create([
                    'user_id' => $request->user()->id,
                    'questao_id' => $resposta['questao_id'],
                    'alternativa_id' => $resposta['alternativa_id'],
                    'simulado_id' => $simulado->id,
                    'tentativa_id' => $tentativa->id,
                    'correta' => $correta,
                    'tempo_resposta' => $tempoResposta,
                ]);
            }

            $percentualAcerto = ($acertos / $totalQuestoes) * 100;

            // Atualizar estatísticas da tentativa
            $tentativa->update([
                'acertos' => $acertos,
                'erros' => $totalQuestoes - $acertos,
                'percentual_acerto' => $percentualAcerto,
                'tempo_total' => $tempoTotal,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Respostas registradas com sucesso',
                'data' => [
                    'tentativa_id' => $tentativa->id,
                    'numero_tentativa' => $numeroTentativa,
                    'acertos' => $acertos,
                    'total_questoes' => $totalQuestoes,
                    'percentual_acerto' => round($percentualAcerto, 2),
                    'tempo_total' => $tempoTotal,
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
        // Buscar a última tentativa do usuário
        $ultimaTentativa = SimuladoTentativa::where('simulado_id', $simulado->id)
            ->where('user_id', $request->user()->id)
            ->orderBy('numero_tentativa', 'desc')
            ->first();

        if (!$ultimaTentativa) {
            return response()->json([
                'success' => false,
                'message' => 'Você ainda não respondeu este simulado',
            ], 404);
        }

        // Buscar respostas da última tentativa com relacionamentos
        $respostas = RespostaUsuario::where('tentativa_id', $ultimaTentativa->id)
            ->with(['questao.alternativas', 'alternativa'])
            ->orderBy('created_at', 'asc')
            ->get();

        $detalhesRespostas = $respostas->map(function ($resposta) use ($simulado) {
            $alternativaCorreta = $resposta->questao->alternativas->where('correta', true)->first();
            
            return [
                'questao_id' => $resposta->questao_id,
                'questao_enunciado' => $resposta->questao->enunciado,
                'alternativa_escolhida_id' => $resposta->alternativa_id,
                'alternativa_escolhida' => $resposta->alternativa->texto ?? 'Não respondida',
                'correta' => $resposta->correta,
                'alternativa_correta_id' => $simulado->mostrar_gabarito ? $alternativaCorreta->id : null,
                'alternativa_correta' => $simulado->mostrar_gabarito ? $alternativaCorreta->texto : null,
                'explicacao' => $simulado->mostrar_gabarito ? $resposta->questao->explicacao : null,
                'tempo_resposta' => $resposta->tempo_resposta,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'simulado' => $simulado->only(['id', 'titulo', 'descricao', 'mostrar_gabarito']),
                'tentativa' => [
                    'id' => $ultimaTentativa->id,
                    'numero' => $ultimaTentativa->numero_tentativa,
                    'data_inicio' => $ultimaTentativa->data_inicio->format('Y-m-d H:i:s'),
                    'data_fim' => $ultimaTentativa->data_fim->format('Y-m-d H:i:s'),
                    'tempo_total' => $ultimaTentativa->tempo_total,
                ],
                'estatisticas' => [
                    'total_questoes' => $ultimaTentativa->total_questoes,
                    'acertos' => $ultimaTentativa->acertos,
                    'erros' => $ultimaTentativa->erros,
                    'percentual_acerto' => $ultimaTentativa->percentual_acerto,
                ],
                'respostas' => $detalhesRespostas,
            ],
        ]);
    }

    public function historico(Simulado $simulado, Request $request)
    {
        // Buscar todas as tentativas do usuário para este simulado
        $tentativas = SimuladoTentativa::where('simulado_id', $simulado->id)
            ->where('user_id', $request->user()->id)
            ->orderBy('numero_tentativa', 'desc')
            ->get();

        if ($tentativas->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Você ainda não respondeu este simulado',
            ], 404);
        }

        // Formatar histórico de tentativas
        $historicoTentativas = $tentativas->map(function ($tentativa) {
            return [
                'tentativa_id' => $tentativa->id,
                'numero_tentativa' => $tentativa->numero_tentativa,
                'data_inicio' => $tentativa->data_inicio->format('Y-m-d H:i:s'),
                'data_fim' => $tentativa->data_fim->format('Y-m-d H:i:s'),
                'total_questoes' => $tentativa->total_questoes,
                'acertos' => $tentativa->acertos,
                'erros' => $tentativa->erros,
                'percentual_acerto' => $tentativa->percentual_acerto,
                'tempo_total' => $tentativa->tempo_total,
            ];
        });

        // Estatísticas gerais
        $melhorTentativa = $tentativas->sortByDesc('percentual_acerto')->first();
        $mediaPercentual = $tentativas->avg('percentual_acerto');
        $tempoMedio = $tentativas->avg('tempo_total');

        return response()->json([
            'success' => true,
            'data' => [
                'simulado' => $simulado->only(['id', 'titulo', 'descricao']),
                'total_tentativas' => $tentativas->count(),
                'estatisticas_gerais' => [
                    'melhor_percentual' => round($melhorTentativa->percentual_acerto, 2),
                    'melhor_tentativa_numero' => $melhorTentativa->numero_tentativa,
                    'media_percentual' => round($mediaPercentual, 2),
                    'tempo_medio' => round($tempoMedio, 2),
                ],
                'tentativas' => $historicoTentativas,
            ],
        ]);
    }

    public function detalheTentativa(Simulado $simulado, $tentativaId, Request $request)
    {
        // Buscar a tentativa específica
        $tentativa = SimuladoTentativa::where('id', $tentativaId)
            ->where('simulado_id', $simulado->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$tentativa) {
            return response()->json([
                'success' => false,
                'message' => 'Tentativa não encontrada',
            ], 404);
        }

        // Buscar respostas da tentativa
        $respostas = RespostaUsuario::where('tentativa_id', $tentativa->id)
            ->with(['questao.alternativas', 'alternativa'])
            ->orderBy('created_at', 'asc')
            ->get();

        $detalhesRespostas = $respostas->map(function ($resposta) use ($simulado) {
            $alternativaCorreta = $resposta->questao->alternativas->where('correta', true)->first();
            
            return [
                'questao_id' => $resposta->questao_id,
                'questao_enunciado' => $resposta->questao->enunciado,
                'alternativa_escolhida_id' => $resposta->alternativa_id,
                'alternativa_escolhida' => $resposta->alternativa->texto ?? 'Não respondida',
                'correta' => $resposta->correta,
                'alternativa_correta_id' => $simulado->mostrar_gabarito ? $alternativaCorreta->id : null,
                'alternativa_correta' => $simulado->mostrar_gabarito ? $alternativaCorreta->texto : null,
                'explicacao' => $simulado->mostrar_gabarito ? $resposta->questao->explicacao : null,
                'tempo_resposta' => $resposta->tempo_resposta,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'simulado' => $simulado->only(['id', 'titulo', 'descricao', 'mostrar_gabarito']),
                'tentativa' => [
                    'id' => $tentativa->id,
                    'numero' => $tentativa->numero_tentativa,
                    'data_inicio' => $tentativa->data_inicio->format('Y-m-d H:i:s'),
                    'data_fim' => $tentativa->data_fim->format('Y-m-d H:i:s'),
                    'tempo_total' => $tentativa->tempo_total,
                ],
                'estatisticas' => [
                    'total_questoes' => $tentativa->total_questoes,
                    'acertos' => $tentativa->acertos,
                    'erros' => $tentativa->erros,
                    'percentual_acerto' => $tentativa->percentual_acerto,
                ],
                'respostas' => $detalhesRespostas,
            ],
        ]);
    }
}
