<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'creditos',
        'creditos_semanais',
        'ultima_renovacao',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'ultima_renovacao' => 'datetime',
            'blocked_at' => 'datetime',
        ];
    }

    public function questoes()
    {
        return $this->hasMany(Questao::class);
    }

    public function colecoes()
    {
        return $this->hasMany(Colecao::class);
    }

    public function simulados()
    {
        return $this->hasMany(Simulado::class);
    }

    public function respostas()
    {
        return $this->hasMany(RespostaUsuario::class);
    }

    public function transacoesCreditos()
    {
        return $this->hasMany(TransacaoCredito::class);
    }

    public function isProfessor(): bool
    {
        return $this->role === 'professor';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Verifica se o usuário tem créditos suficientes
     */
    public function temCreditos(int $quantidade): bool
    {
        $this->verificarERenovarCreditos();
        return $this->creditos >= $quantidade;
    }

    /**
     * Verifica se passou 1 semana e renova os créditos automaticamente
     */
    public function verificarERenovarCreditos(): void
    {
        // Se nunca foi renovado, define agora
        if (!$this->ultima_renovacao) {
            $this->ultima_renovacao = now();
            $this->save();
            return;
        }

        // Verifica se passou 1 semana (7 dias)
        $diasDesdeRenovacao = $this->ultima_renovacao->diffInDays(now());

        if ($diasDesdeRenovacao >= 7) {
            $this->creditos = $this->creditos_semanais;
            $this->ultima_renovacao = now();
            $this->save();
        }
    }

    /**
     * Retorna quantos dias faltam para renovação
     */
    public function diasParaRenovacao(): int
    {
        if (!$this->ultima_renovacao) {
            return 0;
        }

        $diasDesdeRenovacao = $this->ultima_renovacao->diffInDays(now());
        $diasRestantes = 7 - $diasDesdeRenovacao;

        return max(0, $diasRestantes);
    }

    /**
     * Retorna data da próxima renovação
     */
    public function proximaRenovacao(): ?\Carbon\Carbon
    {
        if (!$this->ultima_renovacao) {
            return null;
        }

        return $this->ultima_renovacao->copy()->addDays(7);
    }
}
