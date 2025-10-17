<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TemaController extends Controller
{
    /**
     * Lista todos os temas disponíveis para o usuário (globais + personalizados)
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $temas = Tema::where('ativo', true)
            ->disponiveis($userId)
            ->withCount('questoes')
            ->orderByRaw('user_id IS NULL DESC') // Globais primeiro
            ->orderBy('nome')
            ->get()
            ->map(function($tema) {
                return [
                    'id' => $tema->id,
                    'nome' => $tema->nome,
                    'descricao' => $tema->descricao,
                    'icone' => $tema->icone,
                    'cor' => $tema->cor,
                    'ativo' => $tema->ativo,
                    'questoes_count' => $tema->questoes_count,
                    'tipo' => $tema->user_id ? 'personalizado' : 'global',
                    'editavel' => (bool) $tema->user_id,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $temas,
        ]);
    }

    /**
     * Cria um novo tema personalizado para o usuário
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'icone' => 'nullable|string|max:255',
            'cor' => 'nullable|string|max:50',
        ]);

        // Verificar se já existe um tema com esse nome para o usuário
        $existe = Tema::where('user_id', $request->user()->id)
            ->where('nome', $request->nome)
            ->exists();

        if ($existe) {
            return response()->json([
                'success' => false,
                'message' => 'Você já possui um tema com este nome',
            ], 422);
        }

        $tema = Tema::create([
            'user_id' => $request->user()->id,
            'nome' => $request->nome,
            'descricao' => $request->descricao,
            'icone' => $request->icone ?? '📚',
            'cor' => $request->cor ?? '#3B82F6',
            'ativo' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tema personalizado criado com sucesso',
            'data' => [
                'id' => $tema->id,
                'nome' => $tema->nome,
                'descricao' => $tema->descricao,
                'icone' => $tema->icone,
                'cor' => $tema->cor,
                'ativo' => $tema->ativo,
                'tipo' => 'personalizado',
                'editavel' => true,
            ],
        ], 201);
    }

    /**
     * Exibe detalhes de um tema
     */
    public function show(Request $request, Tema $tema)
    {
        $userId = $request->user()->id;

        // Verificar se o tema está disponível para o usuário
        if ($tema->user_id && $tema->user_id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Tema não encontrado',
            ], 404);
        }

        $tema->loadCount('questoes');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $tema->id,
                'nome' => $tema->nome,
                'descricao' => $tema->descricao,
                'icone' => $tema->icone,
                'cor' => $tema->cor,
                'ativo' => $tema->ativo,
                'questoes_count' => $tema->questoes_count,
                'tipo' => $tema->user_id ? 'personalizado' : 'global',
                'editavel' => $tema->user_id === $userId,
            ],
        ]);
    }

    /**
     * Atualiza um tema personalizado (apenas do próprio usuário)
     */
    public function update(Request $request, Tema $tema)
    {
        // Verificar se é um tema personalizado do usuário
        if (!$tema->user_id || $tema->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para editar este tema',
            ], 403);
        }

        $request->validate([
            'nome' => 'sometimes|string|max:255',
            'descricao' => 'nullable|string',
            'icone' => 'nullable|string|max:255',
            'cor' => 'nullable|string|max:50',
            'ativo' => 'sometimes|boolean',
        ]);

        // Verificar duplicação de nome (exceto o próprio tema)
        if ($request->has('nome')) {
            $existe = Tema::where('user_id', $request->user()->id)
                ->where('nome', $request->nome)
                ->where('id', '!=', $tema->id)
                ->exists();

            if ($existe) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você já possui um tema com este nome',
                ], 422);
            }
        }

        $tema->update($request->only(['nome', 'descricao', 'icone', 'cor', 'ativo']));

        return response()->json([
            'success' => true,
            'message' => 'Tema atualizado com sucesso',
            'data' => [
                'id' => $tema->id,
                'nome' => $tema->nome,
                'descricao' => $tema->descricao,
                'icone' => $tema->icone,
                'cor' => $tema->cor,
                'ativo' => $tema->ativo,
                'tipo' => 'personalizado',
                'editavel' => true,
            ],
        ]);
    }

    /**
     * Exclui um tema personalizado (apenas do próprio usuário)
     */
    public function destroy(Request $request, Tema $tema)
    {
        // Verificar se é um tema personalizado do usuário
        if (!$tema->user_id || $tema->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para excluir este tema',
            ], 403);
        }

        // Verificar se há questões associadas
        if ($tema->questoes()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível excluir um tema que possui questões associadas',
            ], 422);
        }

        $tema->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tema excluído com sucesso',
        ]);
    }

    /**
     * Lista apenas os temas personalizados do usuário
     */
    public function meusTemas(Request $request)
    {
        $userId = $request->user()->id;

        $temas = Tema::personalizados($userId)
            ->withCount('questoes')
            ->orderBy('nome')
            ->get()
            ->map(function($tema) {
                return [
                    'id' => $tema->id,
                    'nome' => $tema->nome,
                    'descricao' => $tema->descricao,
                    'icone' => $tema->icone,
                    'cor' => $tema->cor,
                    'ativo' => $tema->ativo,
                    'questoes_count' => $tema->questoes_count,
                    'tipo' => 'personalizado',
                    'editavel' => true,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $temas->count(),
                'temas' => $temas,
            ],
        ]);
    }
}
