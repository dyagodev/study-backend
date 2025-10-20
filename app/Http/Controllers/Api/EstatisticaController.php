<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RespostaUsuario;
use App\Models\Simulado;
use App\Models\SimuladoTentativa;
use App\Models\Tema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EstatisticaController extends Controller
{
    /**
     * Dashboard geral com estatísticas resumidas
     */
    public function dashboard(Request $request)
    {
        $userId = $request->user()->id;

        // Total de questões respondidas
        $totalQuestoesRespondidas = RespostaUsuario::where('user_id', $userId)->count();

        // Total de acertos
        $totalAcertos = RespostaUsuario::where('user_id', $userId)
            ->where('correta', true)
            ->count();

        // Percentual geral de acerto
        $percentualAcerto = $totalQuestoesRespondidas > 0
            ? ($totalAcertos / $totalQuestoesRespondidas) * 100
            : 0;

        // Total de simulados realizados
        $totalSimulados = RespostaUsuario::where('user_id', $userId)
            ->distinct('simulado_id')
            ->count('simulado_id');

        // Tempo médio de resposta (em segundos)
        $tempoMedioResposta = RespostaUsuario::where('user_id', $userId)
            ->avg('tempo_resposta');

        // Melhor desempenho (simulado com maior percentual de acerto)
        $melhorSimulado = $this->getMelhorSimulado($userId);

        // Último simulado realizado
        $ultimoSimulado = $this->getUltimoSimulado($userId);

        // Sequência de acertos atual
        $sequenciaAcertos = $this->getSequenciaAcertos($userId);

        // Evolução nos últimos 7 dias
        $evolucao7Dias = $this->getEvolucao7Dias($userId);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $ultimoSimulado['simulado_id'] ?? null,
                'resumo' => [
                    'total_questoes_respondidas' => $totalQuestoesRespondidas,
                    'total_acertos' => $totalAcertos,
                    'total_erros' => $totalQuestoesRespondidas - $totalAcertos,
                    'percentual_acerto' => round($percentualAcerto, 2),
                    'total_simulados' => $totalSimulados,
                    'tempo_medio_resposta' => round($tempoMedioResposta ?? 0, 2),
                    'sequencia_acertos' => $sequenciaAcertos,
                ],
                'melhor_simulado' => $melhorSimulado,
                'ultimo_simulado' => $ultimoSimulado,
                'evolucao_7_dias' => $evolucao7Dias,
            ],
        ]);
    }

    /**
     * Desempenho por tema
     */
    public function desempenhoPorTema(Request $request)
    {
        $userId = $request->user()->id;

        $desempenho = RespostaUsuario::select(
                'questoes.tema_id',
                'temas.nome as tema_nome',
                DB::raw('COUNT(*) as total_questoes'),
                DB::raw('SUM(CASE WHEN respostas_usuario.correta = 1 THEN 1 ELSE 0 END) as total_acertos'),
                DB::raw('SUM(CASE WHEN respostas_usuario.correta = 0 THEN 1 ELSE 0 END) as total_erros'),
                DB::raw('ROUND((SUM(CASE WHEN respostas_usuario.correta = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 2) as percentual_acerto'),
                DB::raw('ROUND(AVG(respostas_usuario.tempo_resposta), 2) as tempo_medio')
            )
            ->join('questoes', 'respostas_usuario.questao_id', '=', 'questoes.id')
            ->join('temas', 'questoes.tema_id', '=', 'temas.id')
            ->where('respostas_usuario.user_id', $userId)
            ->groupBy('questoes.tema_id', 'temas.nome')
            ->orderByDesc('percentual_acerto')
            ->get();

        // Calcular pontos fortes e fracos
        $pontosFortes = $desempenho->where('percentual_acerto', '>=', 70)->values();
        $pontosFracos = $desempenho->where('percentual_acerto', '<', 70)->values();

        return response()->json([
            'success' => true,
            'data' => [
                'desempenho_por_tema' => $desempenho,
                'pontos_fortes' => $pontosFortes,
                'pontos_fracos' => $pontosFracos,
            ],
        ]);
    }

    /**
     * Evolução temporal (gráfico de progresso)
     */
    public function evolucaoTemporal(Request $request)
    {
        $request->validate([
            'periodo' => 'sometimes|in:7dias,30dias,90dias,ano',
        ]);

        $userId = $request->user()->id;
        $periodo = $request->periodo ?? '30dias';

        // Definir data inicial baseado no período
        $dataInicial = match($periodo) {
            '7dias' => Carbon::now()->subDays(7),
            '30dias' => Carbon::now()->subDays(30),
            '90dias' => Carbon::now()->subDays(90),
            'ano' => Carbon::now()->subYear(),
            default => Carbon::now()->subDays(30),
        };

        // Agrupar por data
        $evolucao = RespostaUsuario::select(
                DB::raw('DATE(created_at) as data'),
                DB::raw('COUNT(*) as total_questoes'),
                DB::raw('SUM(CASE WHEN correta = 1 THEN 1 ELSE 0 END) as acertos'),
                DB::raw('SUM(CASE WHEN correta = 0 THEN 1 ELSE 0 END) as erros'),
                DB::raw('ROUND((SUM(CASE WHEN correta = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 2) as percentual_acerto')
            )
            ->where('user_id', $userId)
            ->where('created_at', '>=', $dataInicial)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('data', 'asc')
            ->get();

        // Calcular média móvel (últimos 7 dias)
        $mediaMovel = [];
        for ($i = 0; $i < $evolucao->count(); $i++) {
            $inicio = max(0, $i - 6);
            $fim = $i + 1;
            $slice = $evolucao->slice($inicio, $fim - $inicio);
            $mediaMovel[] = round($slice->avg('percentual_acerto'), 2);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'periodo' => $periodo,
                'data_inicial' => $dataInicial->format('Y-m-d'),
                'evolucao' => $evolucao,
                'media_movel_7_dias' => $mediaMovel,
                'tendencia' => $this->calcularTendencia($evolucao),
            ],
        ]);
    }

    /**
     * Estatísticas detalhadas de simulados
     */
    public function estatisticasSimulados(Request $request)
    {
        $userId = $request->user()->id;

        // Buscar todas as últimas tentativas de cada simulado
        $tentativas = SimuladoTentativa::select('simulado_tentativas.*')
            ->join(DB::raw('(SELECT simulado_id, MAX(numero_tentativa) as max_tentativa
                            FROM simulado_tentativas
                            WHERE user_id = ' . $userId . '
                            GROUP BY simulado_id) as latest'),
                function($join) {
                    $join->on('simulado_tentativas.simulado_id', '=', 'latest.simulado_id')
                         ->on('simulado_tentativas.numero_tentativa', '=', 'latest.max_tentativa');
                })
            ->where('simulado_tentativas.user_id', $userId)
            ->with('simulado')
            ->get();

        $estatisticas = $tentativas->map(function ($tentativa) {
            return [
                'simulado_id' => $tentativa->simulado_id,
                'simulado_titulo' => $tentativa->simulado->titulo ?? 'Sem título',
                'total_questoes' => $tentativa->total_questoes,
                'acertos' => $tentativa->acertos,
                'erros' => $tentativa->erros,
                'percentual_acerto' => $tentativa->percentual_acerto,
                'tempo_medio_resposta' => $tentativa->tempo_total > 0
                    ? round($tentativa->tempo_total / $tentativa->total_questoes, 2)
                    : 0,
                'total_tentativas' => SimuladoTentativa::where('simulado_id', $tentativa->simulado_id)
                    ->where('user_id', $tentativa->user_id)
                    ->count(),
                'ultima_tentativa' => $tentativa->data_fim->format('Y-m-d H:i:s'),
            ];
        })->sortByDesc('percentual_acerto')->values();

        return response()->json([
            'success' => true,
            'data' => [
                'total_simulados_realizados' => $estatisticas->count(),
                'simulados' => $estatisticas,
                'media_geral' => $estatisticas->count() > 0
                    ? round($estatisticas->avg('percentual_acerto'), 2)
                    : 0,
            ],
        ]);
    }

    /**
     * Métodos auxiliares privados
     */
    private function getMelhorSimulado($userId)
    {
        $simuladosIds = RespostaUsuario::where('user_id', $userId)
            ->distinct('simulado_id')
            ->pluck('simulado_id');

        $melhorSimulado = null;
        $melhorPercentual = 0;

        foreach ($simuladosIds as $simuladoId) {
            $ultimaResposta = RespostaUsuario::where('user_id', $userId)
                ->where('simulado_id', $simuladoId)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$ultimaResposta) continue;

            $dataLimite = $ultimaResposta->created_at->copy()->subMinutes(30);

            $respostas = RespostaUsuario::where('user_id', $userId)
                ->where('simulado_id', $simuladoId)
                ->where('created_at', '>=', $dataLimite)
                ->get();

            $totalQuestoes = $respostas->count();
            $acertos = $respostas->where('correta', true)->count();
            $percentualAcerto = $totalQuestoes > 0 ? ($acertos / $totalQuestoes) * 100 : 0;

            if ($percentualAcerto > $melhorPercentual) {
                $melhorPercentual = $percentualAcerto;
                $simulado = Simulado::find($simuladoId);
                $melhorSimulado = [
                    'simulado_id' => $simuladoId,
                    'simulado_titulo' => $simulado->titulo ?? 'Sem título',
                    'percentual_acerto' => round($percentualAcerto, 2),
                    'acertos' => $acertos,
                    'total_questoes' => $totalQuestoes,
                ];
            }
        }

        return $melhorSimulado;
    }

    private function getUltimoSimulado($userId)
    {
        $ultimaResposta = RespostaUsuario::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$ultimaResposta) {
            return null;
        }

        $simuladoId = $ultimaResposta->simulado_id;
        $dataLimite = $ultimaResposta->created_at->copy()->subMinutes(30);

        $respostas = RespostaUsuario::where('user_id', $userId)
            ->where('simulado_id', $simuladoId)
            ->where('created_at', '>=', $dataLimite)
            ->get();

        $totalQuestoes = $respostas->count();
        $acertos = $respostas->where('correta', true)->count();

        $simulado = Simulado::find($simuladoId);

        return [
            'simulado_id' => $simuladoId,
            'simulado_titulo' => $simulado->titulo ?? 'Sem título',
            'data' => $ultimaResposta->created_at->format('Y-m-d H:i:s'),
            'acertos' => $acertos,
            'total_questoes' => $totalQuestoes,
            'percentual_acerto' => $totalQuestoes > 0 ? round(($acertos / $totalQuestoes) * 100, 2) : 0,
        ];
    }

    private function getSequenciaAcertos($userId)
    {
        $respostas = RespostaUsuario::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        $sequencia = 0;
        foreach ($respostas as $resposta) {
            if ($resposta->correta) {
                $sequencia++;
            } else {
                break;
            }
        }

        return $sequencia;
    }

    private function getEvolucao7Dias($userId)
    {
        $dataInicial = Carbon::now()->subDays(7);

        return RespostaUsuario::select(
                DB::raw('DATE(created_at) as data'),
                DB::raw('COUNT(*) as total_questoes'),
                DB::raw('SUM(CASE WHEN correta = 1 THEN 1 ELSE 0 END) as acertos'),
                DB::raw('ROUND((SUM(CASE WHEN correta = 1 THEN 1 ELSE 0 END) * 100.0 / COUNT(*)), 2) as percentual_acerto')
            )
            ->where('user_id', $userId)
            ->where('created_at', '>=', $dataInicial)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('data', 'asc')
            ->get();
    }

    private function calcularTendencia($evolucao)
    {
        if ($evolucao->count() < 2) {
            return 'estavel';
        }

        // Calcular média dos primeiros 30% vs últimos 30%
        $total = $evolucao->count();
        $primeiros = $evolucao->take(ceil($total * 0.3));
        $ultimos = $evolucao->slice(-ceil($total * 0.3));

        $mediaPrimeiros = $primeiros->avg('percentual_acerto');
        $mediaUltimos = $ultimos->avg('percentual_acerto');

        $diferenca = $mediaUltimos - $mediaPrimeiros;

        if ($diferenca > 5) {
            return 'crescente';
        } elseif ($diferenca < -5) {
            return 'decrescente';
        } else {
            return 'estavel';
        }
    }
}
