<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tema extends Model
{
    protected $fillable = [
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
}
