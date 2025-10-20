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
            'incluir_respondidas' => 'nullable|boolean',
        ]);

        $userId = $request->user()->id;
        $temaId = $request->tema_id;
        $nivel = $request->nivel;
        $tipoQuestao = $request->tipo_questao ?? 'concurso';
        $banca = $request->banca;
        $incluirRespondidas = $request->incluir_respondidas ?? false;

        try {
            // Buscar IDs das questões já respondidas pelo usuário
            $questoesRespondidas = RespostaUsuario::where('user_id', $userId)
                ->pluck('questao_id')
                ->unique()
                ->toArray();

            // Buscar questões disponíveis com os filtros
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

            // Excluir questões já respondidas APENAS se o usuário não quiser revisá-las
            if (!$incluirRespondidas && count($questoesRespondidas) > 0) {
                $query->whereNotIn('id', $questoesRespondidas);
            }

            // Contar total disponível antes de buscar
            $totalDisponiveis = $query->count();
            
            // Contar quantas já foram respondidas neste contexto
            $totalRespondidas = $incluirRespondidas 
                ? RespostaUsuario::where('user_id', $userId)
                    ->whereHas('questao', function ($q) use ($temaId, $nivel, $tipoQuestao, $banca) {
                        $q->where('tema_id', $temaId)
                          ->where('nivel', $nivel);
                        if ($tipoQuestao) {
                            $q->where('tipo_questao', $tipoQuestao);
                        }
                        if ($banca) {
                            $q->where('banca', $banca);
                        }
                    })
                    ->pluck('questao_id')
                    ->unique()
                    ->count()
                : count($questoesRespondidas);

            // Buscar uma questão aleatória
            $proximaQuestao = $query->inRandomOrder()->first();

            // Se encontrou uma questão, retornar
            if ($proximaQuestao) {
                // Verificar se esta questão específica já foi respondida
                $foiRespondida = in_array($proximaQuestao->id, $questoesRespondidas);
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'questao' => $proximaQuestao,
                        'total_disponiveis' => $totalDisponiveis,
                        'total_respondidas' => $totalRespondidas,
                        'ja_respondida' => $foiRespondida,
                        'modo_revisao' => $incluirRespondidas,
                    ],
                ]);
            }

            // Não encontrou questões disponíveis
            // Calcular desempenho do usuário neste tema/nível
            $desempenho = $this->calcularDesempenho($userId, $temaId, $nivel, $tipoQuestao, $banca);
            
            // Calcular custo para gerar novas
            $quantidadeSugerida = 5;
            $custoGeracao = $this->creditoService->calcularCustoQuestoes('simples', $quantidadeSugerida);

            // Mensagem customizada baseada no modo
            $mensagem = $incluirRespondidas
                ? 'Você já respondeu todas as questões disponíveis com essas configurações, incluindo as revisadas.'
                : 'Não há mais questões não respondidas. Você pode ativar o modo revisão para responder questões novamente.';

            return response()->json([
                'success' => false,
                'message' => $mensagem,
                'data' => [
                    'questoes_acabaram' => true,
                    'total_respondidas' => $totalRespondidas,
                    'modo_revisao_ativo' => $incluirRespondidas,
                    'sugestao_modo_revisao' => !$incluirRespondidas ? 'Ative incluir_respondidas=true para revisar questões já respondidas' : null,
                    'desempenho' => $desempenho,
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
            // Calcular desempenho até o momento
            $desempenho = $this->calcularDesempenho($user->id, $temaId, $nivel, $tipoQuestao, $banca);
            
            return response()->json([
                'success' => false,
                'message' => "Créditos insuficientes para gerar {$quantidade} novas questões. Necessário: {$custoTotal} créditos.",
                'data' => [
                    'creditos_necessarios' => $custoTotal,
                    'creditos_disponiveis' => $user->creditos,
                    'desempenho' => $desempenho,
                    'mensagem_motivacional' => 'Enquanto isso, veja como você se saiu nas questões que já respondeu!',
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
     * Calcula o desempenho do usuário em um tema/nível específico
     */
    protected function calcularDesempenho($userId, $temaId, $nivel = null, $tipoQuestao = null, $banca = null)
    {
        // Buscar todas as respostas do usuário neste tema
        $query = RespostaUsuario::where('user_id', $userId)
            ->whereHas('questao', function ($q) use ($temaId, $nivel, $tipoQuestao, $banca) {
                $q->where('tema_id', $temaId);
                
                if ($nivel) {
                    $q->where('nivel', $nivel);
                }
                
                if ($tipoQuestao) {
                    $q->where('tipo_questao', $tipoQuestao);
                }
                
                if ($banca) {
                    $q->where('banca', $banca);
                }
            });

        $respostas = $query->get();

        if ($respostas->isEmpty()) {
            return [
                'mensagem' => 'Você ainda não respondeu nenhuma questão com estas configurações.',
                'total_respostas' => 0,
            ];
        }

        $totalRespostas = $respostas->count();
        $acertos = $respostas->where('correta', true)->count();
        $erros = $totalRespostas - $acertos;
        $percentualAcerto = $totalRespostas > 0 ? round(($acertos / $totalRespostas) * 100, 2) : 0;

        // Calcular tempo médio de resposta (em segundos)
        $tempoTotal = $respostas->sum('tempo_resposta');
        $tempoMedio = $totalRespostas > 0 ? round($tempoTotal / $totalRespostas, 2) : 0;

        // Questões únicas respondidas (pode responder a mesma várias vezes)
        $questoesUnicas = $respostas->pluck('questao_id')->unique()->count();

        // Melhor e pior desempenho em sequência
        $sequenciaAtual = 0;
        $maiorSequenciaAcertos = 0;
        $maiorSequenciaErros = 0;
        $sequenciaErrosAtual = 0;

        foreach ($respostas->sortBy('created_at') as $resposta) {
            if ($resposta->correta) {
                $sequenciaAtual++;
                $sequenciaErrosAtual = 0;
                $maiorSequenciaAcertos = max($maiorSequenciaAcertos, $sequenciaAtual);
            } else {
                $sequenciaErrosAtual++;
                $sequenciaAtual = 0;
                $maiorSequenciaErros = max($maiorSequenciaErros, $sequenciaErrosAtual);
            }
        }

        // Última resposta
        $ultimaResposta = $respostas->sortByDesc('created_at')->first();

        // Análise de evolução (últimas 10 vs primeiras 10)
        $evolucao = null;
        if ($totalRespostas >= 10) {
            $primeiras10 = $respostas->sortBy('created_at')->take(10);
            $ultimas10 = $respostas->sortByDesc('created_at')->take(10);

            $acertosPrimeiras = $primeiras10->where('correta', true)->count();
            $acertosUltimas = $ultimas10->where('correta', true)->count();

            $percentualPrimeiras = ($acertosPrimeiras / 10) * 100;
            $percentualUltimas = ($acertosUltimas / 10) * 100;
            $diferencaPercentual = round($percentualUltimas - $percentualPrimeiras, 2);

            $evolucao = [
                'percentual_inicio' => round($percentualPrimeiras, 2),
                'percentual_recente' => round($percentualUltimas, 2),
                'diferenca' => $diferencaPercentual,
                'melhorou' => $diferencaPercentual > 0,
                'mensagem' => $diferencaPercentual > 0 
                    ? "Você melhorou {$diferencaPercentual}% em relação ao início!" 
                    : ($diferencaPercentual < 0 
                        ? "Seu desempenho caiu {$diferencaPercentual}% em relação ao início." 
                        : "Seu desempenho se manteve estável."),
            ];
        }

        // Avaliação do desempenho
        $avaliacao = $this->gerarAvaliacaoDesempenho($percentualAcerto, $totalRespostas);

        return [
            'resumo' => [
                'total_respostas' => $totalRespostas,
                'questoes_unicas' => $questoesUnicas,
                'acertos' => $acertos,
                'erros' => $erros,
                'percentual_acerto' => $percentualAcerto,
                'tempo_medio_segundos' => $tempoMedio,
                'tempo_medio_formatado' => $this->formatarTempo($tempoMedio),
            ],
            'sequencias' => [
                'maior_sequencia_acertos' => $maiorSequenciaAcertos,
                'maior_sequencia_erros' => $maiorSequenciaErros,
                'sequencia_atual' => $sequenciaAtual,
            ],
            'ultima_resposta' => [
                'correta' => $ultimaResposta->correta,
                'data' => $ultimaResposta->created_at->format('d/m/Y H:i'),
                'tempo_gasto' => $this->formatarTempo($ultimaResposta->tempo_resposta),
            ],
            'evolucao' => $evolucao,
            'avaliacao' => $avaliacao,
        ];
    }

    /**
     * Gera uma avaliação textual do desempenho
     */
    protected function gerarAvaliacaoDesempenho($percentualAcerto, $totalRespostas)
    {
        $nivel = '';
        $mensagem = '';
        $recomendacao = '';

        if ($percentualAcerto >= 90) {
            $nivel = 'Excelente';
            $mensagem = 'Parabéns! Você domina este conteúdo!';
            $recomendacao = 'Continue praticando para manter o alto desempenho.';
        } elseif ($percentualAcerto >= 75) {
            $nivel = 'Muito Bom';
            $mensagem = 'Ótimo trabalho! Você tem um bom domínio do conteúdo.';
            $recomendacao = 'Foque nas questões que errou para alcançar a excelência.';
        } elseif ($percentualAcerto >= 60) {
            $nivel = 'Bom';
            $mensagem = 'Você está no caminho certo!';
            $recomendacao = 'Continue estudando e praticando para melhorar ainda mais.';
        } elseif ($percentualAcerto >= 40) {
            $nivel = 'Regular';
            $mensagem = 'Você está aprendendo, mas precisa de mais prática.';
            $recomendacao = 'Revise o conteúdo teórico e pratique mais questões.';
        } else {
            $nivel = 'Precisa Melhorar';
            $mensagem = 'Este conteúdo precisa de mais atenção.';
            $recomendacao = 'Recomendamos revisar a teoria antes de continuar praticando.';
        }

        if ($totalRespostas < 10) {
            $recomendacao .= ' Responda mais questões para uma avaliação mais precisa.';
        }

        return [
            'nivel' => $nivel,
            'mensagem' => $mensagem,
            'recomendacao' => $recomendacao,
        ];
    }

    /**
     * Formata tempo em segundos para formato legível
     */
    protected function formatarTempo($segundos)
    {
        if ($segundos < 60) {
            return round($segundos) . 's';
        } elseif ($segundos < 3600) {
            $minutos = floor($segundos / 60);
            $segs = $segundos % 60;
            return $minutos . 'min ' . round($segs) . 's';
        } else {
            $horas = floor($segundos / 3600);
            $minutos = floor(($segundos % 3600) / 60);
            return $horas . 'h ' . $minutos . 'min';
        }
    }

    /**
     * Retorna o desempenho do usuário em um tema específico
     */
    public function desempenho(Request $request)
    {
        $request->validate([
            'tema_id' => 'required|exists:temas,id',
            'nivel' => 'nullable|in:facil,medio,dificil,muito_dificil',
            'tipo_questao' => 'nullable|in:concurso,enem,prova_crc,oab,outros',
            'banca' => 'nullable|string',
        ]);

        $userId = $request->user()->id;
        $temaId = $request->tema_id;
        $nivel = $request->nivel;
        $tipoQuestao = $request->tipo_questao;
        $banca = $request->banca;

        $desempenho = $this->calcularDesempenho($userId, $temaId, $nivel, $tipoQuestao, $banca);

        // Buscar nome do tema
        $tema = \App\Models\Tema::find($temaId);

        return response()->json([
            'success' => true,
            'data' => [
                'tema' => [
                    'id' => $tema->id,
                    'nome' => $tema->nome,
                ],
                'filtros' => [
                    'nivel' => $nivel,
                    'tipo_questao' => $tipoQuestao,
                    'banca' => $banca,
                ],
                'desempenho' => $desempenho,
            ],
        ]);
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
