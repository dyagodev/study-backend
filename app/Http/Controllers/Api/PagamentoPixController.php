<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PaymentApprovedMail;
use App\Models\PagamentoPix;
use App\Services\CreditoService;
use App\Services\ValidaPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PagamentoPixController extends Controller
{
    protected $validaPayService;
    protected $creditoService;

    public function __construct(ValidaPayService $validaPayService, CreditoService $creditoService)
    {
        $this->validaPayService = $validaPayService;
        $this->creditoService = $creditoService;
    }

    /**
     * Listar pacotes de créditos disponíveis
     */
    public function pacotes()
    {
        $pacotes = [
            [
                'id' => 'pacote_50',
                'nome' => 'Básico',
                'creditos' => 50,
                'valor' => 4.90,
                'bonus' => 0,
                'total_creditos' => 50,
                'desconto' => 0,
                'descricao' => 'Ideal para testar',
            ],
            [
                'id' => 'pacote_100',
                'nome' => 'Popular',
                'creditos' => 100,
                'valor' => 9.90,
                'bonus' => 0,
                'total_creditos' => 100,
                'desconto' => 10,
                'descricao' => 'Mais vendido',
                'popular' => true,
            ],
            [
                'id' => 'pacote_250',
                'nome' => 'Avançado',
                'creditos' => 250,
                'valor' => 19.90,
                'bonus' => 0,
                'total_creditos' => 250,
                'desconto' => 20,
                'descricao' => 'Melhor custo-benefício',
            ],
            [
                'id' => 'pacote_500',
                'nome' => 'Premium',
                'creditos' => 500,
                'valor' => 34.90,
                'bonus' => 0,
                'total_creditos' => 500,
                'desconto' => 30,
                'descricao' => 'Para usuários avançados',
                'destaque' => true,
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $pacotes,
        ]);
    }

    /**
     * Criar cobrança PIX
     */
    public function criar(Request $request)
    {

        $request->validate([
            'pacote_id' => 'required|string',
            'cpf' => 'string',
            'nome' => 'string|max:255',
        ]);

        $user = $request->user();

        // Buscar pacote
        $pacotesDisponiveis = [
            'pacote_50' => ['creditos' => 50, 'valor' => 4.90],
            'pacote_100' => ['creditos' => 100, 'valor' => 9.90],
            'pacote_250' => ['creditos' => 250, 'valor' => 19.90],
            'pacote_500' => ['creditos' => 500, 'valor' => 34.90],
        ];

        $pacote = $pacotesDisponiveis[$request->pacote_id] ?? null;

        if (!$pacote) {
            return response()->json([
                'success' => false,
                'message' => 'Pacote inválido',
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Criar cobrança PIX via ValidaPay API
            $resultado = $this->validaPayService->criarCobranca(
                valor: $pacote['valor'],
                webhookUrl: route('webhook.validapay')
            );

            // Salvar no banco
            $pagamento = PagamentoPix::create([
                'user_id' => $user->id,
                'txid' => (string) $resultado['transactionId'], // Converter para string
                'valor' => $pacote['valor'],
                'creditos' => $pacote['creditos'],
                'status' => 'pendente',
                'qrcode' => $resultado['emv'], // EMV é o código PIX Copia e Cola
                'expira_em' => now()->addHour(),
                'dados_pagador' => [
                    'cpf' => $request->cpf,
                    'nome' => $request->nome,
                ],
                'resposta_validapay' => $resultado,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cobrança PIX criada com sucesso',
                'data' => [
                    'id' => $pagamento->id,
                    'transaction_id' => $resultado['transactionId'],
                    'valor' => $pagamento->valor,
                    'creditos' => $pagamento->creditos,
                    'qrcode' => $pagamento->qrcode, // EMV string para copiar/colar
                    'expira_em' => $pagamento->expira_em,
                    'status' => $pagamento->status,
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar pagamento PIX', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar cobrança PIX: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Consultar status de um pagamento
     */
    public function consultar($id, Request $request)
    {
        $user = $request->user();
        $pagamento = PagamentoPix::where('user_id', $user->id)->findOrFail($id);

        // Verificar se expirou
        if ($pagamento->isExpirado() && $pagamento->isPendente()) {
            $pagamento->marcarComoExpirado();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $pagamento->id,
                'transaction_id' => $pagamento->txid, // transactionId armazenado no campo txid
                'valor' => $pagamento->valor,
                'creditos' => $pagamento->creditos,
                'status' => $pagamento->status,
                'qrcode' => $pagamento->qrcode, // EMV string
                'expira_em' => $pagamento->expira_em,
                'pago_em' => $pagamento->pago_em,
                'created_at' => $pagamento->created_at,
            ],
        ]);
    }

    /**
     * Listar pagamentos do usuário
     */
    public function historico(Request $request)
    {
        $user = $request->user();

        $pagamentos = PagamentoPix::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $pagamentos->items(),
            'pagination' => [
                'current_page' => $pagamentos->currentPage(),
                'total' => $pagamentos->total(),
                'per_page' => $pagamentos->perPage(),
                'last_page' => $pagamentos->lastPage(),
            ],
        ]);
    }

    /**
     * Webhook da ValidaPay
     */
    public function webhook(Request $request)
    {
        try {
            Log::info('Webhook PIX recebido', ['payload' => $request->all()]);

            $dados = $this->validaPayService->processarWebhook($request->all());

            $pagamento = PagamentoPix::where('txid', (string) $dados['transactionId'])->first();

            if (!$pagamento) {
                Log::warning('Pagamento não encontrado para transactionId: ' . $dados['transactionId']);
                return response()->json(['message' => 'Pagamento não encontrado'], 404);
            }

            // Mapear status da ValidaPay para o banco
            $statusBanco = $this->mapearStatusParaBanco($dados['status']);

            // Atualizar status
            $pagamento->update([
                'status' => $statusBanco,
                'resposta_validapay' => $dados,
            ]);

            // Se foi pago, creditar
            if ($this->validaPayService->isPago($dados['status']) && !$pagamento->pago_em) {
                $this->processarPagamentoConcluido($pagamento);
            }

            return response()->json(['message' => 'Webhook processado com sucesso']);
        } catch (\Exception $e) {
            Log::error('Erro ao processar webhook: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao processar webhook'], 500);
        }
    }

    /**
     * Mapear status da ValidaPay para o banco de dados
     */
    protected function mapearStatusParaBanco(string $status): string
    {
        $mapeamento = [
            'PENDING' => 'PENDENTE',
            'CONFIRMED' => 'CONCLUIDA',  // ✅ Mapeamento principal
            'PAID' => 'CONCLUIDA',
            'CANCELLED' => 'CANCELADA',
            'EXPIRED' => 'EXPIRADA',
        ];

        return $mapeamento[strtoupper($status)] ?? strtolower($status);
    }

    /**
     * Processar pagamento concluído (creditar usuário)
     */
    protected function processarPagamentoConcluido(PagamentoPix $pagamento): void
    {
        try {
            DB::beginTransaction();

            // Marcar como pago
            $pagamento->marcarComoPago();

            // Creditar usuário
            $this->creditoService->creditar(
                $pagamento->user,
                $pagamento->creditos,
                "Compra de créditos via PIX - Pacote {$pagamento->creditos} créditos",
                'pagamento_pix',
                $pagamento->id
            );

            DB::commit();

            Log::info('Pagamento processado com sucesso', [
                'pagamento_id' => $pagamento->id,
                'user_id' => $pagamento->user_id,
                'creditos' => $pagamento->creditos,
            ]);

            // Enviar email de confirmação de pagamento
            try {
                // Recarregar o pagamento com o usuário para garantir dados atualizados
                $pagamento->refresh();
                $pagamento->load('user');

                Mail::to($pagamento->user->email)->send(new PaymentApprovedMail($pagamento));

                Log::info('Email de pagamento aprovado enviado', [
                    'pagamento_id' => $pagamento->id,
                    'user_email' => $pagamento->user->email,
                ]);
            } catch (\Exception $e) {
                // Log do erro mas não falha o processamento do pagamento
                Log::error('Erro ao enviar email de pagamento aprovado: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao processar pagamento concluído: ' . $e->getMessage());
            throw $e;
        }
    }
}
