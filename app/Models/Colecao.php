<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Colecao extends Model
{
    protected $table = 'colecoes';

    protected $fillable = [
        'user_id',
        'nome',
        'descricao',
        'publica',
    ];

    protected $casts = [
        'publica' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function questoes(): BelongsToMany
    {
        return $this->belongsToMany(Questao::class, 'colecao_questao')
            ->withPivot('ordem')
            ->withTimestamps()
            ->orderBy('colecao_questao.ordem');
    }
}
