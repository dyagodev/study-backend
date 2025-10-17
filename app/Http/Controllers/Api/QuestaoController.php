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
        $query = Questao::with(['tema', 'alternativas', 'user'])
            ->orderBy('created_at', 'desc');

        // Filtro por tema
        if ($request->has('tema_id')) {
            $query->where('tema_id', $request->tema_id);
        }

        // Filtro por nível
        if ($request->has('nivel')) {
            $query->where('nivel', $request->nivel);
        }

        // Filtro por usuário (minhas questões)
        if ($request->has('minhas') && $request->minhas) {
            $query->where('user_id', $request->user()->id);
        }

        // Filtro por favoritas
        if ($request->has('favoritas') && $request->favoritas) {
            $query->where('user_id', $request->user()->id)
                  ->where('favorita', true);
        }

        // Busca por texto
        if ($request->has('busca')) {
            $busca = $request->busca;
            $query->where(function($q) use ($busca) {
                $q->where('enunciado', 'LIKE', "%{$busca}%")
                  ->orWhere('explicacao', 'LIKE', "%{$busca}%");
            });
        }

        $questoes = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $questoes->items(),
            'meta' => [
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

    public function show(Questao $questao)
    {
        $questao->load(['tema', 'alternativas', 'user']);

        return response()->json([
            'success' => true,
            'data' => $questao,
        ]);
    }

    public function update(Request $request, Questao $questao)
    {
        // Verificar se o usuário é dono da questão
        if ($questao->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para editar esta questão',
            ], 403);
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

    public function destroy(Questao $questao, Request $request)
    {
        if ($questao->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para excluir esta questão',
            ], 403);
        }

        $questao->delete();

        return response()->json([
            'success' => true,
            'message' => 'Questão excluída com sucesso',
        ]);
    }

    public function favoritar(Questao $questao, Request $request)
    {
        if ($questao->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Você só pode favoritar suas próprias questões',
            ], 403);
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
}
