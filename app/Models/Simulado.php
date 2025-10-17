<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Simulado extends Model
{
    protected $fillable = [
        'user_id',
        'titulo',
        'descricao',
        'tempo_limite',
        'embaralhar_questoes',
        'mostrar_gabarito',
        'status',
    ];

    protected $casts = [
        'embaralhar_questoes' => 'boolean',
        'mostrar_gabarito' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function questoes(): BelongsToMany
    {
        return $this->belongsToMany(Questao::class, 'simulado_questao')
            ->withPivot('ordem', 'pontuacao')
            ->withTimestamps()
            ->orderBy('simulado_questao.ordem');
    }

    public function respostas()
    {
        return $this->hasMany(RespostaUsuario::class);
    }
}
