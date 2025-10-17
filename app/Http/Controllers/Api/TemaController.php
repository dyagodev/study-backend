<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tema;
use Illuminate\Http\Request;

class TemaController extends Controller
{
    public function index()
    {
        $temas = Tema::where('ativo', true)
            ->withCount('questoes')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $temas,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'icone' => 'nullable|string|max:255',
            'cor' => 'nullable|string|max:50',
            'ativo' => 'sometimes|boolean',
        ]);

        $tema = Tema::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Tema criado com sucesso',
            'data' => $tema,
        ], 201);
    }

    public function show(Tema $tema)
    {
        $tema->loadCount('questoes');

        return response()->json([
            'success' => true,
            'data' => $tema,
        ]);
    }

    public function update(Request $request, Tema $tema)
    {
        $request->validate([
            'nome' => 'sometimes|string|max:255',
            'descricao' => 'nullable|string',
            'icone' => 'nullable|string|max:255',
            'cor' => 'nullable|string|max:50',
            'ativo' => 'sometimes|boolean',
        ]);

        $tema->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Tema atualizado com sucesso',
            'data' => $tema,
        ]);
    }

    public function destroy(Tema $tema)
    {
        $tema->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tema excluído com sucesso',
        ]);
    }
}
