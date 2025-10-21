<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tema;
use App\Models\Questao;
use App\Models\Alternativa;
use App\Models\RespostaUsuario;
use App\Services\CreditoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RespostaAvulsaTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $tema;
    protected $questao;
    protected $alternativaCorreta;
    protected $alternativaIncorreta;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar usuário com créditos
        $this->user = User::factory()->create([
            'creditos' => 100,
        ]);

        // Criar tema
        $this->tema = Tema::create([
            'nome' => 'Teste',
            'descricao' => 'Tema para testes',
        ]);

        // Criar questão
        $this->questao = Questao::create([
            'tema_id' => $this->tema->id,
            'user_id' => $this->user->id,
            'enunciado' => 'Qual é a capital do Brasil?',
            'nivel' => 'facil',
            'explicacao' => 'A capital do Brasil é Brasília desde 1960.',
        ]);

        // Criar alternativas
        $this->alternativaCorreta = Alternativa::create([
            'questao_id' => $this->questao->id,
            'texto' => 'Brasília',
            'correta' => true,
            'ordem' => 1,
        ]);

        $this->alternativaIncorreta = Alternativa::create([
            'questao_id' => $this->questao->id,
            'texto' => 'São Paulo',
            'correta' => false,
            'ordem' => 2,
        ]);
    }

    /** @test */
    public function pode_responder_questao_avulsa_corretamente()
    {
        $creditosIniciais = $this->user->creditos;

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/questoes/{$this->questao->id}/responder", [
                'alternativa_id' => $this->alternativaCorreta->id,
                'tempo_resposta' => 30,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Resposta registrada com sucesso',
            ]);

        $data = $response->json('data');

        // Verificar dados da resposta
        $this->assertTrue($data['correta']);
        $this->assertEquals($this->alternativaCorreta->id, $data['alternativa_correta_id']);
        $this->assertEquals(CreditoService::CUSTO_RESPOSTA_AVULSA, $data['creditos_debitados']);
        $this->assertEquals($creditosIniciais - CreditoService::CUSTO_RESPOSTA_AVULSA, $data['creditos_restantes']);

        // Verificar que a resposta foi salva no banco
        $this->assertDatabaseHas('respostas_usuario', [
            'user_id' => $this->user->id,
            'questao_id' => $this->questao->id,
            'alternativa_id' => $this->alternativaCorreta->id,
            'correta' => true,
            'simulado_id' => null,
        ]);

        // Verificar que os créditos foram debitados
        $this->user->refresh();
        $this->assertEquals($creditosIniciais - CreditoService::CUSTO_RESPOSTA_AVULSA, $this->user->creditos);
    }

    /** @test */
    public function pode_responder_questao_avulsa_incorretamente()
    {
        $creditosIniciais = $this->user->creditos;

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/questoes/{$this->questao->id}/responder", [
                'alternativa_id' => $this->alternativaIncorreta->id,
                'tempo_resposta' => 25,
            ]);

        $response->assertStatus(200);

        $data = $response->json('data');

        // Mesmo errando, deve debitar créditos
        $this->assertFalse($data['correta']);
        $this->assertEquals($this->alternativaCorreta->id, $data['alternativa_correta_id']);
        $this->assertEquals(CreditoService::CUSTO_RESPOSTA_AVULSA, $data['creditos_debitados']);

        // Verificar que a resposta foi salva como incorreta
        $this->assertDatabaseHas('respostas_usuario', [
            'user_id' => $this->user->id,
            'questao_id' => $this->questao->id,
            'alternativa_id' => $this->alternativaIncorreta->id,
            'correta' => false,
        ]);

        // Créditos foram debitados mesmo com erro
        $this->user->refresh();
        $this->assertEquals($creditosIniciais - CreditoService::CUSTO_RESPOSTA_AVULSA, $this->user->creditos);
    }

    /** @test */
    public function nao_pode_responder_sem_creditos_suficientes()
    {
        // Zerar créditos do usuário
        $this->user->update(['creditos' => 0]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/questoes/{$this->questao->id}/responder", [
                'alternativa_id' => $this->alternativaCorreta->id,
            ]);

        $response->assertStatus(402) // Payment Required
            ->assertJson([
                'success' => false,
                'message' => 'Créditos insuficientes. Você precisa de 1 crédito para responder esta questão.',
            ]);

        // Verificar que nenhuma resposta foi criada
        $this->assertDatabaseCount('respostas_usuario', 0);
    }

    /** @test */
    public function valida_campos_obrigatorios()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/questoes/{$this->questao->id}/responder", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['alternativa_id']);
    }

    /** @test */
    public function nao_aceita_alternativa_de_outra_questao()
    {
        // Criar outra questão
        $outraQuestao = Questao::create([
            'tema_id' => $this->tema->id,
            'user_id' => $this->user->id,
            'enunciado' => 'Outra questão?',
            'nivel' => 'medio',
        ]);

        $outraAlternativa = Alternativa::create([
            'questao_id' => $outraQuestao->id,
            'texto' => 'Alternativa de outra questão',
            'correta' => true,
            'ordem' => 1,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/questoes/{$this->questao->id}/responder", [
                'alternativa_id' => $outraAlternativa->id,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'A alternativa selecionada não pertence a esta questão',
            ]);
    }

    /** @test */
    public function registra_tempo_de_resposta_corretamente()
    {
        $tempoResposta = 45;

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/questoes/{$this->questao->id}/responder", [
                'alternativa_id' => $this->alternativaCorreta->id,
                'tempo_resposta' => $tempoResposta,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('respostas_usuario', [
            'user_id' => $this->user->id,
            'questao_id' => $this->questao->id,
            'tempo_resposta' => $tempoResposta,
        ]);
    }

    /** @test */
    public function usa_zero_como_tempo_padrao_quando_nao_informado()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/questoes/{$this->questao->id}/responder", [
                'alternativa_id' => $this->alternativaCorreta->id,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('respostas_usuario', [
            'user_id' => $this->user->id,
            'questao_id' => $this->questao->id,
            'tempo_resposta' => 0,
        ]);
    }

    /** @test */
    public function cria_transacao_de_credito_ao_responder()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/questoes/{$this->questao->id}/responder", [
                'alternativa_id' => $this->alternativaCorreta->id,
            ]);

        $response->assertStatus(200);

        // Verificar que a transação foi criada
        $this->assertDatabaseHas('transacoes_creditos', [
            'user_id' => $this->user->id,
            'tipo' => 'debito',
            'quantidade' => CreditoService::CUSTO_RESPOSTA_AVULSA,
            'descricao' => 'Resposta de questão avulsa',
            'referencia_tipo' => 'questao',
            'referencia_id' => $this->questao->id,
        ]);
    }

    /** @test */
    public function pode_responder_mesma_questao_multiplas_vezes()
    {
        $creditosIniciais = $this->user->creditos;

        // Primeira resposta
        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/questoes/{$this->questao->id}/responder", [
                'alternativa_id' => $this->alternativaIncorreta->id,
            ])
            ->assertStatus(200);

        // Segunda resposta
        $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/questoes/{$this->questao->id}/responder", [
                'alternativa_id' => $this->alternativaCorreta->id,
            ])
            ->assertStatus(200);

        // Deve ter criado 2 respostas
        $this->assertDatabaseCount('respostas_usuario', 2);

        // Deve ter debitado 2 créditos (1 por resposta)
        $this->user->refresh();
        $this->assertEquals($creditosIniciais - (CreditoService::CUSTO_RESPOSTA_AVULSA * 2), $this->user->creditos);
    }

    /** @test */
    public function retorna_explicacao_apos_responder()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/questoes/{$this->questao->id}/responder", [
                'alternativa_id' => $this->alternativaCorreta->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.explicacao', $this->questao->explicacao);
    }

    /** @test */
    public function resposta_avulsa_nao_cria_simulado_ou_tentativa()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/questoes/{$this->questao->id}/responder", [
                'alternativa_id' => $this->alternativaCorreta->id,
            ]);

        $response->assertStatus(200);

        // Verificar que não criou simulado
        $this->assertDatabaseCount('simulados', 0);

        // Verificar que não criou tentativa
        $this->assertDatabaseCount('simulado_tentativas', 0);

        // Verificar que a resposta não está vinculada a simulado/tentativa
        $this->assertDatabaseHas('respostas_usuario', [
            'user_id' => $this->user->id,
            'questao_id' => $this->questao->id,
            'simulado_id' => null,
            'tentativa_id' => null,
        ]);
    }
}
