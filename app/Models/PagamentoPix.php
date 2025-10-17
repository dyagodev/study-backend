<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagamentoPix extends Model
{
    protected $table = 'pagamentos_pix';

    protected $fillable = [
        'user_id',
        'txid',
        'valor',
        'creditos',
        'status',
        'qrcode',
        'location_id',
        'expira_em',
        'pago_em',
        'dados_pagador',
        'resposta_validapay',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'creditos' => 'integer',
        'expira_em' => 'datetime',
        'pago_em' => 'datetime',
        'dados_pagador' => 'array',
        'resposta_validapay' => 'array',
    ];

    /**
     * Relacionamento com usuário
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verificar se pagamento está pendente
     */
    public function isPendente(): bool
    {
        return in_array($this->status, ['PENDENTE', 'ATIVA']);
    }

    /**
     * Verificar se pagamento foi concluído
     */
    public function isConcluido(): bool
    {
        return $this->status === 'CONCLUIDA';
    }

    /**
     * Verificar se pagamento expirou
     */
    public function isExpirado(): bool
    {
        return $this->expira_em && $this->expira_em->isPast();
    }

    /**
     * Marcar como pago
     */
    public function marcarComoPago(): void
    {
        $this->update([
            'status' => 'CONCLUIDA',
            'pago_em' => now(),
        ]);
    }

    /**
     * Marcar como expirado
     */
    public function marcarComoExpirado(): void
    {
        $this->update([
            'status' => 'EXPIRADA',
        ]);
    }
}
