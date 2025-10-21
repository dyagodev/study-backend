<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tema;
use App\Models\Questao;
use App\Models\Alternativa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuestaoIsolamentoTest extends TestCase
{
    use RefreshDatabase;

    protected $usuario1;
    protected $usuario2;
    protected $tema;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar dois usuários
        $this->usuario1 = User::factory()->create(['name' => 'Usuário 1']);
        $this->usuario2 = User::factory()->create(['name' => 'Usuário 2']);

        // Criar tema
        $this->tema = Tema::create([
            'nome' => 'Teste',
            'descricao' => 'Tema para testes',
        ]);
    }

    /** @test */
    public function usuario_so_ve_suas_proprias_questoes()
    {
        // Criar questões para usuário 1
        $questao1 = $this->criarQuestao($this->usuario1, 'Questão do Usuário 1');

        // Criar questões para usuário 2
        $questao2 = $this->criarQuestao($this->usuario2, 'Questão do Usuário 2');

        // Usuário 1 lista suas questões
        $response = $this->actingAs($this->usuario1, 'sanctum')
            ->getJson('/api/questoes');

        $response->assertStatus(200);

        $questoes = $response->json('data');

        // Deve ter apenas 1 questão
        $this->assertCount(1, $questoes);

        // Deve ser a questão do usuário 1
        $this->assertEquals($questao1->id, $questoes[0]['id']);
        $this->assertEquals($this->usuario1->id, $questoes[0]['user_id']);
    }

    /** @test */
    public function busca_por_texto_nao_retorna_questoes_de_outros_usuarios()
    {
        // Usuário 1: questão com palavra "Laravel"
        $questao1 = $this->criarQuestao($this->usuario1, 'O que é Laravel?');

        // Usuário 2: questão com palavra "Laravel"
        $questao2 = $this->criarQuestao($this->usuario2, 'Laravel é um framework?');

        // Usuário 1 busca por "Laravel"
        $response = $this->actingAs($this->usuario1, 'sanctum')
            ->getJson('/api/questoes?busca=Laravel');

        $response->assertStatus(200);

        $questoes = $response->json('data');

        // Deve retornar apenas 1 questão
        $this->assertCount(1, $questoes, 'Busca retornou questões de outros usuários!');

        // Deve ser a questão do usuário 1
        $this->assertEquals($questao1->id, $questoes[0]['id']);
        $this->assertEquals($this->usuario1->id, $questoes[0]['user_id']);
        $this->assertStringContainsString('Laravel', $questoes[0]['enunciado']);
    }

    /** @test */
    public function filtro_por_tema_nao_retorna_questoes_de_outros_usuarios()
    {
        // Ambos usuários têm questões no mesmo tema
        $questao1 = $this->criarQuestao($this->usuario1, 'Questão User 1 Tema 1');
        $questao2 = $this->criarQuestao($this->usuario2, 'Questão User 2 Tema 1');

        // Usuário 1 filtra por tema
        $response = $this->actingAs($this->usuario1, 'sanctum')
            ->getJson("/api/questoes?tema_id={$this->tema->id}");

        $response->assertStatus(200);

        $questoes = $response->json('data');

        // Deve retornar apenas questões do usuário 1
        $this->assertCount(1, $questoes);
        $this->assertEquals($questao1->id, $questoes[0]['id']);
        $this->assertEquals($this->usuario1->id, $questoes[0]['user_id']);
    }

    /** @test */
    public function filtro_por_nivel_nao_retorna_questoes_de_outros_usuarios()
    {
        // Ambos usuários têm questões de nível fácil
        $questao1 = $this->criarQuestao($this->usuario1, 'Questão Fácil User 1', 'facil');
        $questao2 = $this->criarQuestao($this->usuario2, 'Questão Fácil User 2', 'facil');

        // Usuário 1 filtra por nível fácil
        $response = $this->actingAs($this->usuario1, 'sanctum')
            ->getJson('/api/questoes?nivel=facil');

        $response->assertStatus(200);

        $questoes = $response->json('data');

        // Deve retornar apenas questões do usuário 1
        $this->assertCount(1, $questoes);
        $this->assertEquals($questao1->id, $questoes[0]['id']);
        $this->assertEquals($this->usuario1->id, $questoes[0]['user_id']);
    }

    /** @test */
    public function filtro_por_favoritas_nao_retorna_questoes_de_outros_usuarios()
    {
        // Ambos usuários têm questões favoritas
        $questao1 = $this->criarQuestao($this->usuario1, 'Favorita User 1', 'medio', true);
        $questao2 = $this->criarQuestao($this->usuario2, 'Favorita User 2', 'medio', true);

        // Usuário 1 filtra por favoritas
        $response = $this->actingAs($this->usuario1, 'sanctum')
            ->getJson('/api/questoes?favoritas=1');

        $response->assertStatus(200);

        $questoes = $response->json('data');

        // Deve retornar apenas questões do usuário 1
        $this->assertCount(1, $questoes);
        $this->assertEquals($questao1->id, $questoes[0]['id']);
        $this->assertEquals($this->usuario1->id, $questoes[0]['user_id']);
    }

    /** @test */
    public function busca_combinada_nao_retorna_questoes_de_outros_usuarios()
    {
        // Criar questões similares para ambos
        $questao1 = $this->criarQuestao($this->usuario1, 'PHP é uma linguagem?', 'facil');
        $questao2 = $this->criarQuestao($this->usuario2, 'PHP é interpretado?', 'facil');

        // Busca combinada: texto + nível
        $response = $this->actingAs($this->usuario1, 'sanctum')
            ->getJson('/api/questoes?busca=PHP&nivel=facil');

        $response->assertStatus(200);

        $questoes = $response->json('data');

        // Deve retornar apenas 1 questão do usuário 1
        $this->assertCount(1, $questoes);
        $this->assertEquals($questao1->id, $questoes[0]['id']);
        $this->assertEquals($this->usuario1->id, $questoes[0]['user_id']);
    }

    /** @test */
    public function usuario_nao_pode_visualizar_questao_de_outro_usuario()
    {
        $questao = $this->criarQuestao($this->usuario2, 'Questão privada do User 2');

        // Usuário 1 tenta visualizar questão do usuário 2
        $response = $this->actingAs($this->usuario1, 'sanctum')
            ->getJson("/api/questoes/{$questao->id}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Você não tem permissão para visualizar esta questão',
            ]);
    }

    /** @test */
    public function usuario_nao_pode_editar_questao_de_outro_usuario()
    {
        $questao = $this->criarQuestao($this->usuario2, 'Questão do User 2');

        // Usuário 1 tenta editar questão do usuário 2
        $response = $this->actingAs($this->usuario1, 'sanctum')
            ->putJson("/api/questoes/{$questao->id}", [
                'enunciado' => 'Tentativa de edição maliciosa',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Você não tem permissão para editar esta questão',
            ]);

        // Verificar que a questão não foi alterada
        $questao->refresh();
        $this->assertNotEquals('Tentativa de edição maliciosa', $questao->enunciado);
    }

    /** @test */
    public function usuario_nao_pode_excluir_questao_de_outro_usuario()
    {
        $questao = $this->criarQuestao($this->usuario2, 'Questão do User 2');

        // Usuário 1 tenta excluir questão do usuário 2
        $response = $this->actingAs($this->usuario1, 'sanctum')
            ->deleteJson("/api/questoes/{$questao->id}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Você não tem permissão para excluir esta questão',
            ]);

        // Verificar que a questão ainda existe
        $this->assertDatabaseHas('questoes', [
            'id' => $questao->id,
        ]);
    }

    /** @test */
    public function usuario_nao_pode_favoritar_questao_de_outro_usuario()
    {
        $questao = $this->criarQuestao($this->usuario2, 'Questão do User 2');

        // Usuário 1 tenta favoritar questão do usuário 2
        $response = $this->actingAs($this->usuario1, 'sanctum')
            ->postJson("/api/questoes/{$questao->id}/favoritar");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Você só pode favoritar suas próprias questões',
            ]);
    }

    /** @test */
    public function total_de_questoes_reflete_apenas_questoes_do_usuario()
    {
        // Usuário 1: 5 questões
        for ($i = 0; $i < 5; $i++) {
            $this->criarQuestao($this->usuario1, "Questão {$i} User 1");
        }

        // Usuário 2: 10 questões
        for ($i = 0; $i < 10; $i++) {
            $this->criarQuestao($this->usuario2, "Questão {$i} User 2");
        }

        // Usuário 1 lista suas questões
        $response = $this->actingAs($this->usuario1, 'sanctum')
            ->getJson('/api/questoes');

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 5);

        // Usuário 2 lista suas questões
        $response = $this->actingAs($this->usuario2, 'sanctum')
            ->getJson('/api/questoes');

        $response->assertStatus(200)
            ->assertJsonPath('meta.total', 10);
    }

    // ===== MÉTODOS AUXILIARES =====

    private function criarQuestao(User $usuario, string $enunciado, string $nivel = 'medio', bool $favorita = false)
    {
        $questao = Questao::create([
            'tema_id' => $this->tema->id,
            'user_id' => $usuario->id,
            'enunciado' => $enunciado,
            'nivel' => $nivel,
            'favorita' => $favorita,
            'explicacao' => 'Explicação da questão',
        ]);

        // Criar alternativas
        Alternativa::create([
            'questao_id' => $questao->id,
            'texto' => 'Alternativa A (correta)',
            'correta' => true,
            'ordem' => 1,
        ]);

        Alternativa::create([
            'questao_id' => $questao->id,
            'texto' => 'Alternativa B',
            'correta' => false,
            'ordem' => 2,
        ]);

        return $questao->fresh();
    }
}
