<?php

namespace App\Services;

use App\Models\User;
use App\Models\TransacaoCredito;
use Illuminate\Support\Facades\DB;

class CreditoService
{
    /**
     * Custos de operações em créditos (atualizados)
     */
    const CUSTO_QUESTAO_SIMPLES = 3;    // Era 1, agora 3 (200% de aumento)
    const CUSTO_QUESTAO_VARIACAO = 5;   // Era 2, agora 5 (150% de aumento)
    const CUSTO_QUESTAO_IMAGEM = 8;     // Era 3, agora 8 (167% de aumento)
    const CUSTO_SIMULADO = 10;          // Era 5, agora 10 (100% de aumento)
    const CUSTO_RESPOSTA_AVULSA = 1;    // Custo para responder questão avulsa (sem simulado)

    /**
     * Debita créditos do usuário
     */
    public function debitar(
        User $user,
        int $quantidade,
        string $descricao,
        ?string $referenciaTipo = null,
        ?int $referenciaId = null
    ): TransacaoCredito {
        return DB::transaction(function () use ($user, $quantidade, $descricao, $referenciaTipo, $referenciaId) {
            // Lock na linha do usuário para evitar race conditions
            $user = User::where('id', $user->id)->lockForUpdate()->first();

            if (!$user->temCreditos($quantidade)) {
                throw new \Exception('Créditos insuficientes');
            }

            $saldoAnterior = $user->creditos;
            $saldoPosterior = $saldoAnterior - $quantidade;

            // Atualizar saldo do usuário
            $user->update(['creditos' => $saldoPosterior]);

            // Registrar transação
            return TransacaoCredito::create([
                'user_id' => $user->id,
                'tipo' => 'debito',
                'quantidade' => $quantidade,
                'saldo_anterior' => $saldoAnterior,
                'saldo_posterior' => $saldoPosterior,
                'descricao' => $descricao,
                'referencia_tipo' => $referenciaTipo,
                'referencia_id' => $referenciaId,
            ]);
        });
    }

    /**
     * Credita créditos para o usuário
     */
    public function creditar(
        User $user,
        int $quantidade,
        string $descricao,
        ?string $referenciaTipo = null,
        ?int $referenciaId = null
    ): TransacaoCredito {
        return DB::transaction(function () use ($user, $quantidade, $descricao, $referenciaTipo, $referenciaId) {
            // Lock na linha do usuário
            $user = User::where('id', $user->id)->lockForUpdate()->first();

            $saldoAnterior = $user->creditos;
            $saldoPosterior = $saldoAnterior + $quantidade;

            // Atualizar saldo do usuário
            $user->update(['creditos' => $saldoPosterior]);

            // Registrar transação
            return TransacaoCredito::create([
                'user_id' => $user->id,
                'tipo' => 'credito',
                'quantidade' => $quantidade,
                'saldo_anterior' => $saldoAnterior,
                'saldo_posterior' => $saldoPosterior,
                'descricao' => $descricao,
                'referencia_tipo' => $referenciaTipo,
                'referencia_id' => $referenciaId,
            ]);
        });
    }

    /**
     * Calcula o custo de geração de questões
     */
    public function calcularCustoQuestoes(string $tipo, int $quantidade): int
    {
        return match ($tipo) {
            'simples' => self::CUSTO_QUESTAO_SIMPLES * $quantidade,
            'variacao' => self::CUSTO_QUESTAO_VARIACAO * $quantidade,
            'imagem' => self::CUSTO_QUESTAO_IMAGEM * $quantidade,
            default => throw new \Exception('Tipo de questão inválido'),
        };
    }

    /**
     * Verifica se o usuário tem créditos para uma operação
     */
    public function verificarCreditos(User $user, int $custoNecessario): bool
    {
        return $user->temCreditos($custoNecessario);
    }

    /**
     * Retorna o histórico de transações do usuário
     */
    public function historicoTransacoes(User $user, int $limite = 50): \Illuminate\Database\Eloquent\Collection
    {
        return $user->transacoesCreditos()
            ->orderBy('created_at', 'desc')
            ->limit($limite)
            ->get();
    }

    /**
     * Estatísticas de uso de créditos
     */
    public function estatisticasCreditos(User $user): array
    {
        $transacoes = $user->transacoesCreditos;

        $totalDebitado = $transacoes->where('tipo', 'debito')->sum('quantidade');
        $totalCreditado = $transacoes->where('tipo', 'credito')->sum('quantidade');

        return [
            'saldo_atual' => $user->creditos,
            'total_debitado' => $totalDebitado,
            'total_creditado' => $totalCreditado,
            'total_transacoes' => $transacoes->count(),
        ];
    }
}
