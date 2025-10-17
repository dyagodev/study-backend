<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CreditoService;
use Illuminate\Http\Request;

class CreditoController extends Controller
{
    protected $creditoService;

    public function __construct(CreditoService $creditoService)
    {
        $this->creditoService = $creditoService;
    }

    /**
     * Retorna o saldo de créditos do usuário
     */
    public function saldo(Request $request)
    {
        $user = $request->user();
        $user->verificarERenovarCreditos(); // Verifica e renova se necessário

        return response()->json([
            'success' => true,
            'data' => [
                'creditos' => $user->creditos,
                'creditos_semanais' => $user->creditos_semanais,
                'dias_para_renovacao' => $user->diasParaRenovacao(),
                'proxima_renovacao' => $user->proximaRenovacao()?->format('Y-m-d H:i:s'),
                'ultima_renovacao' => $user->ultima_renovacao?->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * Retorna o histórico de transações
     */
    public function historico(Request $request)
    {
        $limite = $request->get('limite', 50);
        $transacoes = $this->creditoService->historicoTransacoes($request->user(), $limite);

        return response()->json([
            'success' => true,
            'data' => $transacoes->map(function ($t) {
                return [
                    'id' => $t->id,
                    'tipo' => $t->tipo,
                    'quantidade' => $t->quantidade,
                    'saldo_anterior' => $t->saldo_anterior,
                    'saldo_posterior' => $t->saldo_posterior,
                    'descricao' => $t->descricao,
                    'referencia_tipo' => $t->referencia_tipo,
                    'referencia_id' => $t->referencia_id,
                    'data' => $t->created_at->format('Y-m-d H:i:s'),
                ];
            }),
        ]);
    }

    /**
     * Retorna estatísticas de uso de créditos
     */
    public function estatisticas(Request $request)
    {
        $stats = $this->creditoService->estatisticasCreditos($request->user());

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Adiciona créditos (apenas admin)
     */
    public function adicionar(Request $request)
    {
        // Verificar se é admin
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Apenas administradores podem adicionar créditos',
            ], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'quantidade' => 'required|integer|min:1',
            'motivo' => 'required|string|max:255',
        ]);

        $user = \App\Models\User::findOrFail($request->user_id);

        try {
            $transacao = $this->creditoService->creditar(
                $user,
                $request->quantidade,
                $request->motivo,
                'admin',
                $request->user()->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Créditos adicionados com sucesso',
                'data' => [
                    'transacao_id' => $transacao->id,
                    'usuario' => $user->name,
                    'quantidade_adicionada' => $request->quantidade,
                    'saldo_anterior' => $transacao->saldo_anterior,
                    'saldo_atual' => $transacao->saldo_posterior,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Retorna tabela de custos
     */
    public function custos()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'questoes' => [
                    'simples' => [
                        'custo_por_questao' => CreditoService::CUSTO_QUESTAO_SIMPLES,
                        'descricao' => 'Geração de questão por tema',
                    ],
                    'variacao' => [
                        'custo_por_questao' => CreditoService::CUSTO_QUESTAO_VARIACAO,
                        'descricao' => 'Geração de variação de questão',
                    ],
                    'imagem' => [
                        'custo_por_questao' => CreditoService::CUSTO_QUESTAO_IMAGEM,
                        'descricao' => 'Geração de questão por imagem',
                    ],
                ],
                'simulado' => [
                    'custo' => CreditoService::CUSTO_SIMULADO,
                    'descricao' => 'Criação de simulado',
                ],
            ],
        ]);
    }
}
