<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Questao;
use App\Models\RespostaUsuario;
use App\Services\AIService;
use App\Services\CreditoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProximaQuestaoController extends Controller
{
    protected $aiService;
    protected $creditoService;

    public function __construct(AIService $aiService, CreditoService $creditoService)
    {
        $this->aiService = $aiService;
        $this->creditoService = $creditoService;
    }

    /**
     * Busca a próxima questão não respondida com as mesmas configurações
     * NUNCA gera automaticamente - apenas informa se não há questões disponíveis
     */
    public function proximaQuestao(Request $request)
    {
        $request->validate([
            'tema_id' => 'required|exists:temas,id',
            'nivel' => 'required|in:facil,medio,dificil,muito_dificil',
            'tipo_questao' => 'nullable|in:concurso,enem,prova_crc,oab,outros',
            'tipo_questao_outro' => 'nullable|string',
            'banca' => 'nullable|string',
        ]);

        $userId = $request->user()->id;
        $temaId = $request->tema_id;
        $nivel = $request->nivel;
        $tipoQuestao = $request->tipo_questao ?? 'concurso';
        $banca = $request->banca;

        try {
            // Buscar IDs das questões já respondidas pelo usuário
            $questoesRespondidas = RespostaUsuario::where('user_id', $userId)
                ->pluck('questao_id')
                ->unique()
                ->toArray();

            // Buscar questões disponíveis (não respondidas) com os filtros
            $query = Questao::with(['tema', 'alternativas'])
                ->where('tema_id', $temaId)
                ->where('nivel', $nivel);

            // Filtrar por tipo de questão se especificado
            if ($tipoQuestao) {
                $query->where('tipo_questao', $tipoQuestao);
            }

            // Filtrar por banca se especificado
            if ($banca) {
                $query->where('banca', $banca);
            }

            // Excluir questões já respondidas
            if (count($questoesRespondidas) > 0) {
                $query->whereNotIn('id', $questoesRespondidas);
            }

            // Contar total disponível antes de buscar
            $totalDisponiveis = $query->count();

            // Buscar uma questão aleatória
            $proximaQuestao = $query->inRandomOrder()->first();

            // Se encontrou uma questão, retornar
            if ($proximaQuestao) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'questao' => $proximaQuestao,
                        'total_disponiveis' => $totalDisponiveis,
                        'total_respondidas' => count($questoesRespondidas),
                    ],
                ]);
            }

            // Não encontrou questões disponíveis
            // Calcular custo para gerar novas
            $quantidadeSugerida = 5;
            $custoGeracao = $this->creditoService->calcularCustoQuestoes('simples', $quantidadeSugerida);

            return response()->json([
                'success' => false,
                'message' => 'Não há mais questões disponíveis com essas configurações.',
                'data' => [
                    'questoes_acabaram' => true,
                    'total_respondidas' => count($questoesRespondidas),
                    'sugestao_geracao' => [
                        'quantidade_sugerida' => $quantidadeSugerida,
                        'custo_creditos' => $custoGeracao,
                        'mensagem' => "Você pode gerar {$quantidadeSugerida} novas questões por {$custoGeracao} créditos",
                    ],
                ],
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar próxima questão: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Gera novas questões quando o usuário solicitar explicitamente
     * Endpoint separado que só é chamado quando o usuário clica para gerar
     */
    public function gerarMaisQuestoes(Request $request)
    {
        $request->validate([
            'tema_id' => 'required|exists:temas,id',
            'nivel' => 'required|in:facil,medio,dificil,muito_dificil',
            'quantidade' => 'required|integer|min:1|max:10',
            'tipo_questao' => 'nullable|in:concurso,enem,prova_crc,oab,outros',
            'tipo_questao_outro' => 'nullable|string',
            'banca' => 'nullable|string',
        ]);

        return $this->gerarNovasQuestoes(
            $request->user(),
            $request->tema_id,
            $request->nivel,
            $request->quantidade,
            $request->tipo_questao ?? 'concurso',
            $request->tipo_questao_outro,
            $request->banca
        );
    }

    /**
     * Método interno para gerar questões
     */
    protected function gerarNovasQuestoes(
        $user,
        $temaId,
        $nivel,
        $quantidade,
        $tipoQuestao,
        $tipoQuestaoOutro,
        $banca
    ) {
        // Calcular custo
        $custoTotal = $this->creditoService->calcularCustoQuestoes('simples', $quantidade);

        // Verificar se tem créditos
        if (!$user->temCreditos($custoTotal)) {
            return response()->json([
                'success' => false,
                'message' => "Créditos insuficientes para gerar {$quantidade} novas questões. Necessário: {$custoTotal} créditos.",
                'data' => [
                    'creditos_necessarios' => $custoTotal,
                    'creditos_disponiveis' => $user->creditos,
                ],
            ], 402);
        }

        DB::beginTransaction();
        try {
            // Buscar informações do tema
            $tema = \App\Models\Tema::findOrFail($temaId);

            // Gerar questões via IA
            $questoesGeradas = $this->aiService->gerarQuestoesPorTema(
                $tema->nome,
                $tema->descricao ?? 'Conhecimentos gerais',
                $quantidade,
                $nivel,
                $tipoQuestao,
                $tipoQuestaoOutro,
                $banca
            );

            if (empty($questoesGeradas)) {
                throw new \Exception('Não foi possível gerar questões');
            }

            // Debitar créditos
            $this->creditoService->debitar(
                $user,
                $custoTotal,
                "Geração automática de {$quantidade} questão(ões) - {$tema->nome}",
                'geracao_automatica',
                $temaId
            );

            // Salvar questões no banco
            $questoesSalvas = [];
            foreach ($questoesGeradas as $questaoData) {
                $questao = Questao::create([
                    'tema_id' => $temaId,
                    'user_id' => $user->id,
                    'enunciado' => $questaoData['enunciado'],
                    'nivel' => $nivel,
                    'tipo_questao' => $tipoQuestao,
                    'tipo_questao_outro' => $tipoQuestaoOutro,
                    'banca' => $banca,
                    'explicacao' => $questaoData['explicacao'] ?? null,
                    'tipo_geracao' => 'ia_tema',
                ]);

                // Criar alternativas
                foreach ($questaoData['alternativas'] as $index => $alternativa) {
                    \App\Models\Alternativa::create([
                        'questao_id' => $questao->id,
                        'texto' => $alternativa['texto'],
                        'correta' => $alternativa['correta'],
                        'ordem' => $index + 1,
                    ]);
                }

                $questoesSalvas[] = $questao->load(['tema', 'alternativas']);
            }

            DB::commit();

            // Retornar a primeira questão gerada
            return response()->json([
                'success' => true,
                'message' => "{$quantidade} nova(s) questão(ões) gerada(s) com sucesso!",
                'data' => [
                    'questao' => $questoesSalvas[0],
                    'gerada_agora' => true,
                    'total_geradas' => count($questoesSalvas),
                    'creditos_debitados' => $custoTotal,
                    'creditos_restantes' => $user->fresh()->creditos,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar novas questões: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Retorna estatísticas sobre questões disponíveis
     */
    public function estatisticasDisponiveis(Request $request)
    {
        $request->validate([
            'tema_id' => 'required|exists:temas,id',
            'nivel' => 'nullable|in:facil,medio,dificil,muito_dificil',
            'tipo_questao' => 'nullable|in:concurso,enem,prova_crc,oab,outros',
            'banca' => 'nullable|string',
        ]);

        $userId = $request->user()->id;
        $temaId = $request->tema_id;

        // Buscar questões respondidas
        $questoesRespondidas = RespostaUsuario::where('user_id', $userId)
            ->whereHas('questao', function ($q) use ($temaId) {
                $q->where('tema_id', $temaId);
            })
            ->pluck('questao_id')
            ->unique()
            ->toArray();

        // Contar questões disponíveis por nível
        $query = Questao::where('tema_id', $temaId);

        if ($request->has('tipo_questao')) {
            $query->where('tipo_questao', $request->tipo_questao);
        }

        if ($request->has('banca')) {
            $query->where('banca', $request->banca);
        }

        $estatisticas = [
            'tema_id' => $temaId,
            'total_respondidas' => count($questoesRespondidas),
            'por_nivel' => [],
        ];

        $niveis = ['facil', 'medio', 'dificil', 'muito_dificil'];
        foreach ($niveis as $nivel) {
            $totalNivel = (clone $query)->where('nivel', $nivel)->count();
            $disponiveisNivel = (clone $query)
                ->where('nivel', $nivel)
                ->whereNotIn('id', $questoesRespondidas)
                ->count();

            $estatisticas['por_nivel'][$nivel] = [
                'total' => $totalNivel,
                'disponiveis' => $disponiveisNivel,
                'respondidas' => $totalNivel - $disponiveisNivel,
                'percentual_completo' => $totalNivel > 0 ? round((($totalNivel - $disponiveisNivel) / $totalNivel) * 100, 2) : 0,
            ];
        }

        // Total geral
        $totalGeral = (clone $query)->count();
        $disponiveisGeral = (clone $query)->whereNotIn('id', $questoesRespondidas)->count();

        $estatisticas['total_geral'] = [
            'total' => $totalGeral,
            'disponiveis' => $disponiveisGeral,
            'respondidas' => count($questoesRespondidas),
            'percentual_completo' => $totalGeral > 0 ? round((count($questoesRespondidas) / $totalGeral) * 100, 2) : 0,
        ];

        return response()->json([
            'success' => true,
            'data' => $estatisticas,
        ]);
    }
}
