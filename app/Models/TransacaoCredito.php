<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransacaoCredito extends Model
{
    protected $table = 'transacoes_creditos';

    protected $fillable = [
        'user_id',
        'tipo',
        'quantidade',
        'saldo_anterior',
        'saldo_posterior',
        'descricao',
        'referencia_tipo',
        'referencia_id',
    ];

    protected $casts = [
        'quantidade' => 'integer',
        'saldo_anterior' => 'integer',
        'saldo_posterior' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
