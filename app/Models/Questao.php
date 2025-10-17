<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Questao extends Model
{
    protected $table = 'questoes';

    protected $fillable = [
        'tema_id',
        'assunto',
        'user_id',
        'enunciado',
        'nivel',
        'nivel_dificuldade',
        'explicacao',
        'imagem_url',
        'imagem_gerada_url',
        'tags',
        'tipo_geracao',
        'favorita',
    ];

    protected $casts = [
        'tags' => 'array',
        'favorita' => 'boolean',
    ];

    protected $appends = ['imagem_url_completa', 'imagem_gerada_url_completa'];

    /**
     * Retorna a URL completa da imagem original (enviada pelo usuário)
     */
    public function getImagemUrlCompletaAttribute(): ?string
    {
        if (!$this->imagem_url) {
            return null;
        }

        // Se já for uma URL completa, retorna como está
        if (filter_var($this->imagem_url, FILTER_VALIDATE_URL)) {
            return $this->imagem_url;
        }

        // Caso contrário, retorna a URL do storage público
        return asset('storage/' . $this->imagem_url);
    }

    /**
     * Retorna a URL completa da imagem gerada pela IA (DALL-E)
     */
    public function getImagemGeradaUrlCompletaAttribute(): ?string
    {
        if (!$this->imagem_gerada_url) {
            return null;
        }

        // Se já for uma URL completa, retorna como está
        if (filter_var($this->imagem_gerada_url, FILTER_VALIDATE_URL)) {
            return $this->imagem_gerada_url;
        }

        // Caso contrário, retorna a URL do storage público
        return asset('storage/' . $this->imagem_gerada_url);
    }

    public function tema(): BelongsTo
    {
        return $this->belongsTo(Tema::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function alternativas(): HasMany
    {
        return $this->hasMany(Alternativa::class);
    }

    public function colecoes(): BelongsToMany
    {
        return $this->belongsToMany(Colecao::class, 'colecao_questao')
            ->withPivot('ordem')
            ->withTimestamps();
    }

    public function simulados(): BelongsToMany
    {
        return $this->belongsToMany(Simulado::class, 'simulado_questao')
            ->withPivot('ordem', 'pontuacao')
            ->withTimestamps();
    }

    public function respostas(): HasMany
    {
        return $this->hasMany(RespostaUsuario::class);
    }
}
