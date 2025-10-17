<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Tema extends Model
{
    protected $fillable = [
        'user_id',
        'nome',
        'descricao',
        'icone',
        'cor',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    public function questoes(): HasMany
    {
        return $this->hasMany(Questao::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para temas globais (criados pelo sistema)
     */
    public function scopeGlobais(Builder $query): Builder
    {
        return $query->whereNull('user_id');
    }

    /**
     * Scope para temas do usuário (globais + personalizados)
     */
    public function scopeDisponiveis(Builder $query, int $userId): Builder
    {
        return $query->where(function($q) use ($userId) {
            $q->whereNull('user_id')
              ->orWhere('user_id', $userId);
        });
    }

    /**
     * Scope para temas personalizados do usuário
     */
    public function scopePersonalizados(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
