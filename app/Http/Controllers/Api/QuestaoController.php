<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alternativa;
use App\Models\Questao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestaoController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $query = Questao::with(['tema', 'alternativas', 'user'])
            ->where('user_id', $userId) // SEMPRE filtra pelo usuário logado
            ->orderBy('created_at', 'desc');

        // Filtro por tema
        if ($request->has('tema_id')) {
            $query->where('tema_id', $request->tema_id);
        }

        // Filtro por nível
        if ($request->has('nivel')) {
            $query->where('nivel', $request->nivel);
        }

        // Filtro por favoritas
        if ($request->has('favoritas') && $request->favoritas) {
            $query->where('favorita', true);
        }

        // Busca por texto - CORRIGIDO: mantém o filtro de user_id
        if ($request->has('busca')) {
            $busca = $request->busca;
            $query->where(function($q) use ($busca, $userId) {
                $q->where('user_id', $userId) // Garante que só busca nas questões do usuário
                  ->where(function($subq) use ($busca) {
                      $subq->where('enunciado', 'LIKE', "%{$busca}%")
                           ->orWhere('explicacao', 'LIKE', "%{$busca}%");
                  });
            });
        }

        $questoes = $query->paginate($request->per_page ?? 15);

        // Buscar IDs das questões respondidas pelo usuário
        $questoesRespondidas = \App\Models\RespostaUsuario::where('user_id', $userId)
            ->whereIn('questao_id', $questoes->pluck('id'))
            ->select('questao_id', DB::raw('COUNT(*) as total_respostas'), DB::raw('MAX(created_at) as ultima_resposta'))
            ->groupBy('questao_id')
            ->get()
            ->keyBy('questao_id');

        // Adicionar informações de resposta a cada questão
        $questoesComInfo = $questoes->map(function ($questao) use ($questoesRespondidas) {
            $questaoArray = $questao->toArray();

            if ($questoesRespondidas->has($questao->id)) {
                $info = $questoesRespondidas->get($questao->id);
                $questaoArray['foi_respondida'] = true;
                $questaoArray['total_respostas'] = $info->total_respostas;
                $questaoArray['ultima_resposta'] = $info->ultima_resposta;
            } else {
                $questaoArray['foi_respondida'] = false;
                $questaoArray['total_respostas'] = 0;
                $questaoArray['ultima_resposta'] = null;
            }

            return $questaoArray;
        });

        return response()->json([
            'success' => true,
            'data' => $questoesComInfo,
            'meta' => [
                'user_id' => Auth()->id(),
                'current_page' => $questoes->currentPage(),
                'last_page' => $questoes->lastPage(),
                'per_page' => $questoes->perPage(),
                'total' => $questoes->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tema_id' => 'required|exists:temas,id',
            'enunciado' => 'required|string',
            'nivel' => 'required|in:facil,medio,dificil',
            'explicacao' => 'nullable|string',
            'tags' => 'nullable|array',
            'alternativas' => 'required|array|min:2|max:6',
            'alternativas.*.texto' => 'required|string',
            'alternativas.*.correta' => 'required|boolean',
        ]);

        // Validar que existe pelo menos uma alternativa correta
        $temCorreta = collect($request->alternativas)->contains('correta', true);
        if (!$temCorreta) {
            return response()->json([
                'success' => false,
                'message' => 'É necessário marcar pelo menos uma alternativa como correta',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $questao = Questao::create([
                'tema_id' => $request->tema_id,
                'user_id' => $request->user()->id,
                'enunciado' => $request->enunciado,
                'nivel' => $request->nivel,
                'explicacao' => $request->explicacao,
                'tags' => $request->tags,
                'tipo_geracao' => 'manual',
            ]);

            foreach ($request->alternativas as $index => $alternativa) {
                Alternativa::create([
                    'questao_id' => $questao->id,
                    'texto' => $alternativa['texto'],
                    'correta' => $alternativa['correta'],
                    'ordem' => $index + 1,
                ]);
            }

            DB::commit();

            $questao->load(['tema', 'alternativas']);

            return response()->json([
                'success' => true,
                'message' => 'Questão criada com sucesso',
                'data' => $questao,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar questão: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        // Buscar questão do usuário logado
        $questao = Questao::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with(['tema', 'alternativas', 'user'])
            ->first();

        if (!$questao) {
            return response()->json([
                'success' => false,
                'message' => 'Questão não encontrada ou você não tem permissão para visualizá-la',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $questao,
        ]);
    }

    public function update(Request $request, $id)
    {
        // Buscar questão do usuário logado
        $questao = Questao::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$questao) {
            return response()->json([
                'success' => false,
                'message' => 'Questão não encontrada ou você não tem permissão para editá-la',
            ], 404);
        }

        $request->validate([
            'tema_id' => 'sometimes|exists:temas,id',
            'enunciado' => 'sometimes|string',
            'nivel' => 'sometimes|in:facil,medio,dificil',
            'explicacao' => 'nullable|string',
            'tags' => 'nullable|array',
            'alternativas' => 'sometimes|array|min:2|max:6',
            'alternativas.*.texto' => 'required_with:alternativas|string',
            'alternativas.*.correta' => 'required_with:alternativas|boolean',
        ]);

        DB::beginTransaction();
        try {
            $questao->update($request->only([
                'tema_id', 'enunciado', 'nivel', 'explicacao', 'tags'
            ]));

            if ($request->has('alternativas')) {
                // Deletar alternativas antigas
                $questao->alternativas()->delete();

                // Criar novas alternativas
                foreach ($request->alternativas as $index => $alternativa) {
                    Alternativa::create([
                        'questao_id' => $questao->id,
                        'texto' => $alternativa['texto'],
                        'correta' => $alternativa['correta'],
                        'ordem' => $index + 1,
                    ]);
                }
            }

            DB::commit();

            $questao->load(['tema', 'alternativas']);

            return response()->json([
                'success' => true,
                'message' => 'Questão atualizada com sucesso',
                'data' => $questao,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar questão: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        // Buscar questão do usuário logado
        $questao = Questao::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$questao) {
            return response()->json([
                'success' => false,
                'message' => 'Questão não encontrada ou você não tem permissão para excluí-la',
            ], 404);
        }

        $questao->delete();

        return response()->json([
            'success' => true,
            'message' => 'Questão excluída com sucesso',
        ]);
    }

    public function favoritar(Request $request, $id)
    {
        // Buscar questão do usuário logado
        $questao = Questao::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$questao) {
            return response()->json([
                'success' => false,
                'message' => 'Questão não encontrada ou você só pode favoritar suas próprias questões',
            ], 404);
        }

        $questao->update([
            'favorita' => !$questao->favorita
        ]);

        return response()->json([
            'success' => true,
            'message' => $questao->favorita ? 'Questão favoritada' : 'Questão desfavoritada',
            'data' => $questao,
        ]);
    }

    /**
     * Responder uma questão avulsa (sem simulado)
     * Cobra créditos por resposta
     * Nota: Não precisa ser dono da questão para responder
     */
    public function responder(Request $request, $id)
    {
        $request->validate([
            'alternativa_id' => 'required|exists:alternativas,id',
            'tempo_resposta' => 'nullable|integer|min:0',
        ]);

        // Buscar questão (qualquer usuário pode responder)
        $questao = Questao::with(['alternativas'])->find($id);

        if (!$questao) {
            return response()->json([
                'success' => false,
                'message' => 'Questão não encontrada',
            ], 404);
        }

        $user = $request->user();
        $creditoService = app(\App\Services\CreditoService::class);

        // Custo de 1 crédito por resposta avulsa
        $custoResposta = 1;

        // Verificar se o usuário tem créditos suficientes
        if (!$user->temCreditos($custoResposta)) {
            return response()->json([
                'success' => false,
                'message' => 'Créditos insuficientes. Você precisa de ' . $custoResposta . ' crédito para responder esta questão.',
                'data' => [
                    'creditos_necessarios' => $custoResposta,
                    'creditos_disponiveis' => $user->creditos,
                ],
            ], 402); // 402 Payment Required
        }

        // Verificar se a alternativa pertence à questão
        $alternativa = \App\Models\Alternativa::find($request->alternativa_id);
        if ($alternativa->questao_id !== $questao->id) {
            return response()->json([
                'success' => false,
                'message' => 'A alternativa selecionada não pertence a esta questão',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Debitar créditos
            $creditoService->debitar(
                $user,
                $custoResposta,
                'Resposta de questão avulsa',
                'questao',
                $questao->id
            );

            // Registrar resposta
            $resposta = \App\Models\RespostaUsuario::create([
                'user_id' => $user->id,
                'questao_id' => $questao->id,
                'alternativa_id' => $request->alternativa_id,
                'simulado_id' => null, // Não é parte de um simulado
                'tentativa_id' => null,
                'correta' => $alternativa->correta,
                'tempo_resposta' => $request->tempo_resposta ?? 0,
            ]);

            // Carregar dados completos para resposta
            $questao->load(['alternativas', 'tema']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Resposta registrada com sucesso',
                'data' => [
                    'resposta_id' => $resposta->id,
                    'correta' => $resposta->correta,
                    'alternativa_correta_id' => $questao->alternativas->where('correta', true)->first()->id,
                    'explicacao' => $questao->explicacao,
                    'creditos_debitados' => $custoResposta,
                    'creditos_restantes' => $user->fresh()->creditos,
                    'questao' => $questao,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar resposta: ' . $e->getMessage(),
            ], 500);
        }
    }
}
