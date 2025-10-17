<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Colecao;
use App\Models\Questao;
use Illuminate\Http\Request;

class ColecaoController extends Controller
{
    public function index(Request $request)
    {
        $query = Colecao::with(['user'])
            ->withCount('questoes')
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc');

        $colecoes = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $colecoes->items(),
            'meta' => [
                'current_page' => $colecoes->currentPage(),
                'last_page' => $colecoes->lastPage(),
                'per_page' => $colecoes->perPage(),
                'total' => $colecoes->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'publica' => 'sometimes|boolean',
        ]);

        $colecao = Colecao::create([
            'user_id' => $request->user()->id,
            'nome' => $request->nome,
            'descricao' => $request->descricao,
            'publica' => $request->publica ?? false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Coleção criada com sucesso',
            'data' => $colecao,
        ], 201);
    }

    public function show(Colecao $colecao, Request $request)
    {
        if ($colecao->user_id !== $request->user()->id && !$colecao->publica) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para visualizar esta coleção',
            ], 403);
        }

        $colecao->load(['questoes.tema', 'questoes.alternativas', 'user']);

        return response()->json([
            'success' => true,
            'data' => $colecao,
        ]);
    }

    public function update(Request $request, Colecao $colecao)
    {
        if ($colecao->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para editar esta coleção',
            ], 403);
        }

        $request->validate([
            'nome' => 'sometimes|string|max:255',
            'descricao' => 'nullable|string',
            'publica' => 'sometimes|boolean',
        ]);

        $colecao->update($request->only(['nome', 'descricao', 'publica']));

        return response()->json([
            'success' => true,
            'message' => 'Coleção atualizada com sucesso',
            'data' => $colecao,
        ]);
    }

    public function destroy(Colecao $colecao, Request $request)
    {
        if ($colecao->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para excluir esta coleção',
            ], 403);
        }

        $colecao->delete();

        return response()->json([
            'success' => true,
            'message' => 'Coleção excluída com sucesso',
        ]);
    }

    public function adicionarQuestao(Request $request, Colecao $colecao)
    {
        if ($colecao->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para modificar esta coleção',
            ], 403);
        }

        $request->validate([
            'questao_id' => 'required|exists:questoes,id',
        ]);

        // Verificar se a questão já está na coleção
        if ($colecao->questoes()->where('questao_id', $request->questao_id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Esta questão já está na coleção',
            ], 422);
        }

        $ordem = $colecao->questoes()->max('colecao_questao.ordem') + 1;

        $colecao->questoes()->attach($request->questao_id, ['ordem' => $ordem]);

        return response()->json([
            'success' => true,
            'message' => 'Questão adicionada à coleção com sucesso',
        ]);
    }

    public function removerQuestao(Colecao $colecao, Questao $questao, Request $request)
    {
        if ($colecao->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para modificar esta coleção',
            ], 403);
        }

        $colecao->questoes()->detach($questao->id);

        return response()->json([
            'success' => true,
            'message' => 'Questão removida da coleção com sucesso',
        ]);
    }

    public function listarQuestoes(Colecao $colecao, Request $request)
    {
        if ($colecao->user_id !== $request->user()->id && !$colecao->publica) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para visualizar esta coleção',
            ], 403);
        }

        $questoes = $colecao->questoes()
            ->with(['tema', 'alternativas'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $questoes,
        ]);
    }
}
