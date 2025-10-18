<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alternativa;
use App\Models\Questao;
use App\Models\Tema;
use App\Services\AIService;
use App\Services\CreditoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestaoGeracaoController extends Controller
{
    protected $aiService;
    protected $creditoService;

    public function __construct(AIService $aiService, CreditoService $creditoService)
    {
        $this->aiService = $aiService;
        $this->creditoService = $creditoService;
    }

    public function gerarPorTema(Request $request)
    {
        $request->validate([
            'tema_id' => 'required|exists:temas,id',
            'assunto' => 'required|string|max:255',
            'quantidade' => 'sometimes|integer|min:1|max:10',
            'nivel' => 'sometimes|in:facil,medio,dificil,muito_dificil',
            'tipo_questao' => 'sometimes|in:concurso,enem,prova_crc,oab,outros',
            'tipo_questao_outro' => 'required_if:tipo_questao,outros|string|max:100',
            'banca' => 'nullable|string|max:150',
        ]);

        $userId = $request->user()->id;
        $tema = Tema::findOrFail($request->tema_id);

        // Verificar se o tema está disponível para o usuário
        if ($tema->user_id && $tema->user_id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Tema não encontrado ou não disponível',
            ], 404);
        }

        $assunto = $request->assunto;
        $quantidade = $request->quantidade ?? 5;
        $nivel = $request->nivel ?? 'medio'; // Default: médio
        $tipoQuestao = $request->tipo_questao ?? 'concurso'; // Default: concurso
        $tipoQuestaoOutro = $request->tipo_questao_outro;
        $banca = $request->banca;

        // Calcular custo e verificar créditos
        $custo = $this->creditoService->calcularCustoQuestoes('simples', $quantidade);
        
        if (!$request->user()->temCreditos($custo)) {
            return response()->json([
                'success' => false,
                'message' => "Créditos insuficientes. Necessário: {$custo} créditos. Saldo atual: {$request->user()->creditos}",
                'custo_necessario' => $custo,
                'saldo_atual' => $request->user()->creditos,
            ], 402);
        }

        try {
            $questoesGeradas = $this->aiService->gerarQuestoesPorTema(
                $tema->nome,
                $assunto,
                $quantidade,
                $nivel,
                $tipoQuestao,
                $tipoQuestaoOutro,
                $banca
            );

            $questoesSalvas = $this->salvarQuestoes(
                $questoesGeradas,
                $tema->id,
                $assunto,
                $request->user()->id,
                'ia_tema',
                $nivel,
                null,
                $tipoQuestao,
                $tipoQuestaoOutro,
                $banca
            );

            // Debitar créditos após geração bem-sucedida
            $this->creditoService->debitar(
                $request->user(),
                $custo,
                "Geração de {$quantidade} questão(ões) - Tema: {$tema->nome}",
                'questao',
                null
            );

            return response()->json([
                'success' => true,
                'message' => 'Questões geradas com sucesso',
                'data' => $questoesSalvas,
                'custo' => $custo,
                'saldo_restante' => $request->user()->fresh()->creditos,
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
            'nivel' => 'sometimes|in:facil,medio,dificil,muito_dificil',
            'tipo_questao' => 'sometimes|in:concurso,enem,prova_crc,oab,outros',
            'tipo_questao_outro' => 'required_if:tipo_questao,outros|string|max:100',
            'banca' => 'nullable|string|max:150',
        ]);

        $userId = $request->user()->id;
        $quantidade = $request->quantidade ?? 3;
        $tema = Tema::findOrFail($request->tema_id);

        // Verificar se o tema está disponível para o usuário
        if ($tema->user_id && $tema->user_id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Tema não encontrado ou não disponível',
            ], 404);
        }

        $assunto = $request->assunto;
        $nivel = $request->nivel ?? 'medio'; // Default: médio
        $tipoQuestao = $request->tipo_questao ?? 'concurso'; // Default: concurso
        $tipoQuestaoOutro = $request->tipo_questao_outro;
        $banca = $request->banca;

        // Calcular custo e verificar créditos (variação)
        $custo = $this->creditoService->calcularCustoQuestoes('variacao', $quantidade);
        
        if (!$request->user()->temCreditos($custo)) {
            return response()->json([
                'success' => false,
                'message' => "Créditos insuficientes. Necessário: {$custo} créditos. Saldo atual: {$request->user()->creditos}",
                'custo_necessario' => $custo,
                'saldo_atual' => $request->user()->creditos,
            ], 402);
        }

        try {
            $questoesGeradas = $this->aiService->gerarVariacoesQuestao(
                $request->questao_exemplo,
                $quantidade,
                $nivel,
                $tipoQuestao,
                $tipoQuestaoOutro,
                $banca
            );

            $questoesSalvas = $this->salvarQuestoes(
                $questoesGeradas,
                $tema->id,
                $assunto,
                $request->user()->id,
                'ia_variacao',
                $nivel,
                null,
                $tipoQuestao,
                $tipoQuestaoOutro,
                $banca
            );

            // Debitar créditos após geração bem-sucedida
            $this->creditoService->debitar(
                $request->user(),
                $custo,
                "Geração de {$quantidade} variação(ões) de questão",
                'questao',
                null
            );

            return response()->json([
                'success' => true,
                'message' => 'Variações geradas com sucesso',
                'data' => $questoesSalvas,
                'custo' => $custo,
                'saldo_restante' => $request->user()->fresh()->creditos,
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
            'nivel' => 'sometimes|in:facil,medio,dificil,muito_dificil',
            'tipo_questao' => 'sometimes|in:concurso,enem,prova_crc,oab,outros',
            'tipo_questao_outro' => 'required_if:tipo_questao,outros|string|max:100',
            'banca' => 'nullable|string|max:150',
        ]);

        $userId = $request->user()->id;
        $tema = Tema::findOrFail($request->tema_id);

        // Verificar se o tema está disponível para o usuário
        if ($tema->user_id && $tema->user_id !== $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Tema não encontrado ou não disponível',
            ], 404);
        }
        $assunto = $request->assunto;
        $nivel = $request->nivel ?? 'medio'; // Default: médio
        $tipoQuestao = $request->tipo_questao ?? 'concurso'; // Default: concurso
        $tipoQuestaoOutro = $request->tipo_questao_outro;
        $banca = $request->banca;

        // Calcular custo e verificar créditos (por imagem)
        $custo = $this->creditoService->calcularCustoQuestoes('imagem', 1);
        
        if (!$request->user()->temCreditos($custo)) {
            return response()->json([
                'success' => false,
                'message' => "Créditos insuficientes. Necessário: {$custo} créditos. Saldo atual: {$request->user()->creditos}",
                'custo_necessario' => $custo,
                'saldo_atual' => $request->user()->creditos,
            ], 402);
        }

        try {
            // Converter imagem para base64
            $imagem = $request->file('imagem');
            $imagemBase64 = base64_encode(file_get_contents($imagem->getRealPath()));

            $questoesGeradas = $this->aiService->gerarQuestoesPorImagem(
                $imagemBase64,
                $request->contexto ?? '',
                $nivel,
                $tipoQuestao,
                $tipoQuestaoOutro,
                $banca
            );

            // Salvar imagem
            $imagemPath = $imagem->store('questoes/imagens', 'public');

            $questoesSalvas = $this->salvarQuestoes(
                $questoesGeradas,
                $tema->id,
                $assunto,
                $request->user()->id,
                'ia_imagem',
                $nivel,
                $imagemPath,
                $tipoQuestao,
                $tipoQuestaoOutro,
                $banca
            );

            // Debitar créditos após geração bem-sucedida
            $this->creditoService->debitar(
                $request->user(),
                $custo,
                "Geração de questão por imagem - Tema: {$tema->nome}",
                'questao',
                null
            );

            return response()->json([
                'success' => true,
                'message' => 'Questões geradas a partir da imagem com sucesso',
                'data' => $questoesSalvas,
                'custo' => $custo,
                'saldo_restante' => $request->user()->fresh()->creditos,
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
        string $nivelDificuldade = 'medio',
        ?string $imagemUrl = null,
        string $tipoQuestao = 'concurso',
        ?string $tipoQuestaoOutro = null,
        ?string $banca = null
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
                    'nivel' => 'concurso', // Mantido para compatibilidade
                    'nivel_dificuldade' => $nivelDificuldade, // facil, medio, dificil, muito_dificil
                    'tipo_questao' => $tipoQuestao, // concurso, enem, prova_crc, oab, outros
                    'tipo_questao_outro' => $tipoQuestaoOutro, // Especificação quando tipo_questao = 'outros'
                    'banca' => $banca, // Banca realizadora (opcional)
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
