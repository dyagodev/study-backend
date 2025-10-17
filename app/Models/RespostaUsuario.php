<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RespostaUsuario extends Model
{
    protected $table = 'respostas_usuario';

    protected $fillable = [
        'user_id',
        'questao_id',
        'alternativa_id',
        'simulado_id',
        'tentativa_id',
        'correta',
        'tempo_resposta',
    ];

    protected $casts = [
        'correta' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function questao(): BelongsTo
    {
        return $this->belongsTo(Questao::class);
    }

    public function alternativa(): BelongsTo
    {
        return $this->belongsTo(Alternativa::class);
    }

    public function simulado(): BelongsTo
    {
        return $this->belongsTo(Simulado::class);
    }

    public function tentativa(): BelongsTo
    {
        return $this->belongsTo(SimuladoTentativa::class, 'tentativa_id');
    }
}
