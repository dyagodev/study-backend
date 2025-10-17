<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alternativa;
use App\Models\Questao;
use App\Models\Tema;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestaoGeracaoController extends Controller
{
    protected $aiService;

    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    public function gerarPorTema(Request $request)
    {
        $request->validate([
            'tema_id' => 'required|exists:temas,id',
            'assunto' => 'required|string|max:255',
            'quantidade' => 'sometimes|integer|min:1|max:10',
        ]);

        $tema = Tema::findOrFail($request->tema_id);
        $assunto = $request->assunto;
        $quantidade = $request->quantidade ?? 5;
        $nivel = 'concurso'; // Fixado como concurso

        try {
            $questoesGeradas = $this->aiService->gerarQuestoesPorTema(
                $tema->nome,
                $assunto,
                $quantidade,
                $nivel
            );

            $questoesSalvas = $this->salvarQuestoes(
                $questoesGeradas,
                $tema->id,
                $assunto,
                $request->user()->id,
                'ia_tema',
                $nivel
            );

            return response()->json([
                'success' => true,
                'message' => 'Questões geradas com sucesso',
                'data' => $questoesSalvas,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function gerarVariacao(Request $request)
    {
        $request->validate([
            'questao_exemplo' => 'required|string',
            'assunto' => 'required|string|max:255',
            'quantidade' => 'sometimes|integer|min:1|max:5',
            'tema_id' => 'required|exists:temas,id',
        ]);

        $quantidade = $request->quantidade ?? 3;
        $tema = Tema::findOrFail($request->tema_id);
        $assunto = $request->assunto;

        try {
            $questoesGeradas = $this->aiService->gerarVariacoesQuestao(
                $request->questao_exemplo,
                $quantidade
            );

            $questoesSalvas = $this->salvarQuestoes(
                $questoesGeradas,
                $tema->id,
                $assunto,
                $request->user()->id,
                'ia_variacao',
                'concurso'
            );

            return response()->json([
                'success' => true,
                'message' => 'Variações geradas com sucesso',
                'data' => $questoesSalvas,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function gerarPorImagem(Request $request)
    {
        $request->validate([
            'imagem' => 'required|image|max:5120', // 5MB
            'tema_id' => 'required|exists:temas,id',
            'assunto' => 'required|string|max:255',
            'contexto' => 'nullable|string',
        ]);

        $tema = Tema::findOrFail($request->tema_id);
        $assunto = $request->assunto;

        try {
            // Converter imagem para base64
            $imagem = $request->file('imagem');
            $imagemBase64 = base64_encode(file_get_contents($imagem->getRealPath()));

            $questoesGeradas = $this->aiService->gerarQuestoesPorImagem(
                $imagemBase64,
                $request->contexto ?? ''
            );

            // Salvar imagem
            $imagemPath = $imagem->store('questoes/imagens', 'public');

            $questoesSalvas = $this->salvarQuestoes(
                $questoesGeradas,
                $tema->id,
                $assunto,
                $request->user()->id,
                'ia_imagem',
                'concurso',
                $imagemPath
            );

            return response()->json([
                'success' => true,
                'message' => 'Questões geradas a partir da imagem com sucesso',
                'data' => $questoesSalvas,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    protected function salvarQuestoes(
        array $questoesGeradas,
        int $temaId,
        string $assunto,
        int $userId,
        string $tipoGeracao,
        string $nivel = 'concurso',
        ?string $imagemUrl = null
    ): array {
        $questoesSalvas = [];

        DB::beginTransaction();
        try {
            foreach ($questoesGeradas as $questaoData) {
                $questao = Questao::create([
                    'tema_id' => $temaId,
                    'assunto' => $assunto,
                    'user_id' => $userId,
                    'enunciado' => $questaoData['enunciado'],
                    'nivel' => $nivel,
                    'explicacao' => $questaoData['explicacao'] ?? null,
                    'tipo_geracao' => $tipoGeracao,
                    'imagem_url' => $imagemUrl,  // Apenas imagem enviada pelo usuário
                ]);

                foreach ($questaoData['alternativas'] as $index => $alternativaData) {
                    Alternativa::create([
                        'questao_id' => $questao->id,
                        'texto' => $alternativaData['texto'],
                        'correta' => $alternativaData['correta'],
                        'ordem' => $index + 1,
                    ]);
                }

                $questao->load('alternativas');
                $questoesSalvas[] = $questao;
            }

            DB::commit();
            return $questoesSalvas;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
