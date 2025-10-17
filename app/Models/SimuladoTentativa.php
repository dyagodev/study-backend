<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SimuladoTentativa extends Model
{
    protected $table = 'simulado_tentativas';

    protected $fillable = [
        'simulado_id',
        'user_id',
        'numero_tentativa',
        'total_questoes',
        'acertos',
        'erros',
        'percentual_acerto',
        'tempo_total',
        'data_inicio',
        'data_fim',
    ];

    protected $casts = [
        'percentual_acerto' => 'decimal:2',
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime',
    ];

    /**
     * Relação com Simulado
     */
    public function simulado(): BelongsTo
    {
        return $this->belongsTo(Simulado::class);
    }

    /**
     * Relação com User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relação com RespostaUsuario
     */
    public function respostas(): HasMany
    {
        return $this->hasMany(RespostaUsuario::class, 'tentativa_id');
    }
}
