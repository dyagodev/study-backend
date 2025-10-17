<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alternativa extends Model
{
    protected $fillable = [
        'questao_id',
        'texto',
        'correta',
        'ordem',
    ];

    protected $casts = [
        'correta' => 'boolean',
    ];

    public function questao(): BelongsTo
    {
        return $this->belongsTo(Questao::class);
    }
}
