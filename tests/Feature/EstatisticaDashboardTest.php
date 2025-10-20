<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tema;
use App\Models\Questao;
use App\Models\Alternativa;
use App\Models\Simulado;
use App\Models\RespostaUsuario;
use App\Models\SimuladoTentativa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstatisticaDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $tema;
    protected $simulado;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar usuário de teste
        $this->user = User::factory()->create();

        // Criar tema de teste
        $this->tema = Tema::create([
            'nome' => 'Teste de Tema',
            'descricao' => 'Tema para testes',
        ]);

        // Criar simulado de teste
        $this->simulado = Simulado::create([
            'user_id' => $this->user->id,
            'titulo' => 'Simulado de Teste',
            'descricao' => 'Descrição do simulado',
        ]);
    }

    /** @test */
    public function deve_retornar_estatisticas_dashboard_usuario_sem_respostas()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/estatisticas/dashboard');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'resumo' => [
                        'total_questoes_respondidas' => 0,
                        'total_acertos' => 0,
                        'total_erros' => 0,
                        'percentual_acerto' => 0,
                        'total_simulados' => 0,
                        'tempo_medio_resposta' => 0,
                        'sequencia_acertos' => 0,
                    ],
                    'melhor_simulado' => null,
                    'ultimo_simulado' => null,
                    'evolucao_7_dias' => [],
                ],
            ]);
    }

    /** @test */
    public function deve_calcular_corretamente_total_questoes_respondidas()
    {
        // Criar questões e respostas
        $this->criarRespostasUsuario(10, 7); // 10 questões, 7 acertos

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/estatisticas/dashboard');

        $response->assertStatus(200);
        
        $data = $response->json('data.resumo');
        
        $this->assertEquals(10, $data['total_questoes_respondidas']);
        $this->assertEquals(7, $data['total_acertos']);
        $this->assertEquals(3, $data['total_erros']);
    }

    /** @test */
    public function deve_calcular_percentual_acerto_corretamente()
    {
        // 20 questões, 15 acertos = 75%
        $this->criarRespostasUsuario(20, 15);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/estatisticas/dashboard');

        $response->assertStatus(200);
        
        $percentual = $response->json('data.resumo.percentual_acerto');
        
        $this->assertEquals(75.00, $percentual);
    }

    /** @test */
    public function deve_contar_simulados_unicos_corretamente()
    {
        // Criar 3 simulados diferentes
        $simulado1 = Simulado::create([
            'user_id' => $this->user->id,
            'titulo' => 'Simulado 1',
        ]);
        $simulado2 = Simulado::create([
            'user_id' => $this->user->id,
            'titulo' => 'Simulado 2',
        ]);
        $simulado3 = Simulado::create([
            'user_id' => $this->user->id,
            'titulo' => 'Simulado 3',
        ]);

        // Criar respostas para cada simulado
        $this->criarRespostasUsuario(5, 3, $simulado1->id);
        $this->criarRespostasUsuario(5, 4, $simulado2->id);
        $this->criarRespostasUsuario(5, 2, $simulado3->id);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/estatisticas/dashboard');

        $response->assertStatus(200);

        // PROBLEMA IDENTIFICADO: distinct('simulado_id')->count('simulado_id')
        // pode não funcionar corretamente em todos os bancos de dados
        $totalSimulados = $response->json('data.resumo.total_simulados');
        
        // Deve ser 3 simulados únicos
        $this->assertEquals(3, $totalSimulados, 
            "Total de simulados deveria ser 3, mas retornou {$totalSimulados}");
    }

    /** @test */
    public function deve_calcular_tempo_medio_resposta_corretamente()
    {
        // Criar respostas com tempos específicos
        $questao = $this->criarQuestao();
        $alternativa = $questao->alternativas->first();

        // 3 respostas: 30s, 60s, 90s = média 60s
        RespostaUsuario::create([
            'user_id' => $this->user->id,
            'questao_id' => $questao->id,
            'alternativa_id' => $alternativa->id,
            'simulado_id' => $this->simulado->id,
            'correta' => true,
            'tempo_resposta' => 30,
        ]);
        
        RespostaUsuario::create([
            'user_id' => $this->user->id,
            'questao_id' => $questao->id,
            'alternativa_id' => $alternativa->id,
            'simulado_id' => $this->simulado->id,
            'correta' => true,
            'tempo_resposta' => 60,
        ]);
        
        RespostaUsuario::create([
            'user_id' => $this->user->id,
            'questao_id' => $questao->id,
            'alternativa_id' => $alternativa->id,
            'simulado_id' => $this->simulado->id,
            'correta' => false,
            'tempo_resposta' => 90,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/estatisticas/dashboard');

        $response->assertStatus(200);
        
        $tempoMedio = $response->json('data.resumo.tempo_medio_resposta');
        
        $this->assertEquals(60.00, $tempoMedio);
    }

    /** @test */
    public function deve_identificar_melhor_simulado_corretamente()
    {
        // Simulado 1: 10 questões, 8 acertos (80%)
        $simulado1 = Simulado::create([
            'user_id' => $this->user->id,
            'titulo' => 'Simulado Bom',
        ]);
        $this->criarRespostasUsuario(10, 8, $simulado1->id);

        // Simulado 2: 10 questões, 5 acertos (50%)
        $simulado2 = Simulado::create([
            'user_id' => $this->user->id,
            'titulo' => 'Simulado Médio',
        ]);
        $this->criarRespostasUsuario(10, 5, $simulado2->id);

        // Simulado 3: 10 questões, 9 acertos (90%) - MELHOR
        $simulado3 = Simulado::create([
            'user_id' => $this->user->id,
            'titulo' => 'Simulado Excelente',
        ]);
        $this->criarRespostasUsuario(10, 9, $simulado3->id);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/estatisticas/dashboard');

        $response->assertStatus(200);
        
        $melhorSimulado = $response->json('data.melhor_simulado');
        
        $this->assertNotNull($melhorSimulado);
        $this->assertEquals($simulado3->id, $melhorSimulado['simulado_id']);
        $this->assertEquals('Simulado Excelente', $melhorSimulado['simulado_titulo']);
        $this->assertEquals(90.00, $melhorSimulado['percentual_acerto']);
        $this->assertEquals(9, $melhorSimulado['acertos']);
        $this->assertEquals(10, $melhorSimulado['total_questoes']);
    }

    /** @test */
    public function deve_identificar_ultimo_simulado_realizado()
    {
        // Criar 2 simulados em momentos diferentes
        $simulado1 = Simulado::create([
            'user_id' => $this->user->id,
            'titulo' => 'Simulado Antigo',
        ]);
        
        $simulado2 = Simulado::create([
            'user_id' => $this->user->id,
            'titulo' => 'Simulado Recente',
        ]);

        // Criar respostas para simulado antigo
        $this->criarRespostasUsuario(5, 3, $simulado1->id);
        
        // Aguardar 1 segundo para garantir diferença de tempo
        sleep(1);
        
        // Criar respostas para simulado recente
        $this->criarRespostasUsuario(5, 4, $simulado2->id);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/estatisticas/dashboard');

        $response->assertStatus(200);
        
        $ultimoSimulado = $response->json('data.ultimo_simulado');
        
        $this->assertNotNull($ultimoSimulado);
        $this->assertEquals($simulado2->id, $ultimoSimulado['simulado_id']);
        $this->assertEquals('Simulado Recente', $ultimoSimulado['simulado_titulo']);
    }

    /** @test */
    public function deve_calcular_sequencia_acertos_corretamente()
    {
        // Criar sequência: erro, acerto, acerto, acerto (do mais antigo para mais recente)
        // A sequência atual deve ser 3 (últimas 3 respostas foram acertos)
        $questao = $this->criarQuestao();
        $alternativa = $questao->alternativas->first();

        // Erro (mais antigo)
        $resposta1 = new RespostaUsuario([
            'user_id' => $this->user->id,
            'questao_id' => $questao->id,
            'alternativa_id' => $alternativa->id,
            'simulado_id' => $this->simulado->id,
            'correta' => false,
            'tempo_resposta' => 30,
        ]);
        $resposta1->created_at = now()->subMinutes(4);
        $resposta1->save();

        // Acertos (mais recentes)
        for ($i = 0; $i < 3; $i++) {
            $resposta = new RespostaUsuario([
                'user_id' => $this->user->id,
                'questao_id' => $questao->id,
                'alternativa_id' => $alternativa->id,
                'simulado_id' => $this->simulado->id,
                'correta' => true,
                'tempo_resposta' => 30,
            ]);
            $resposta->created_at = now()->subMinutes(3 - $i);
            $resposta->save();
        }

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/estatisticas/dashboard');

        $response->assertStatus(200);
        
        $sequencia = $response->json('data.resumo.sequencia_acertos');
        
        // Deve ser 3 (últimas 3 respostas foram acertos)
        $this->assertEquals(3, $sequencia);
    }

    /** @test */
    public function deve_retornar_evolucao_7_dias()
    {
        // Criar respostas nos últimos 3 dias
        for ($i = 0; $i < 3; $i++) {
            $questao = $this->criarQuestao();
            $alternativa = $questao->alternativas->first();
            
            // 5 questões por dia, 3 acertos
            for ($j = 0; $j < 5; $j++) {
                $resposta = new RespostaUsuario([
                    'user_id' => $this->user->id,
                    'questao_id' => $questao->id,
                    'alternativa_id' => $alternativa->id,
                    'simulado_id' => $this->simulado->id,
                    'correta' => $j < 3, // 3 acertos, 2 erros
                    'tempo_resposta' => 30,
                ]);
                $resposta->created_at = now()->subDays($i);
                $resposta->save();
            }
        }

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/estatisticas/dashboard');

        $response->assertStatus(200);
        
        $evolucao = $response->json('data.evolucao_7_dias');
        
        $this->assertIsArray($evolucao);
        $this->assertCount(3, $evolucao); // 3 dias com respostas
        
        // Verificar estrutura de cada dia
        foreach ($evolucao as $dia) {
            $this->assertArrayHasKey('data', $dia);
            $this->assertArrayHasKey('total_questoes', $dia);
            $this->assertArrayHasKey('acertos', $dia);
            $this->assertArrayHasKey('percentual_acerto', $dia);
        }
    }

    /** @test */
    public function nao_deve_retornar_dados_de_outros_usuarios()
    {
        // Criar outro usuário
        $outroUsuario = User::factory()->create();
        
        // Criar respostas para o outro usuário
        $questao = $this->criarQuestao();
        $alternativa = $questao->alternativas->first();
        
        RespostaUsuario::create([
            'user_id' => $outroUsuario->id,
            'questao_id' => $questao->id,
            'alternativa_id' => $alternativa->id,
            'simulado_id' => $this->simulado->id,
            'correta' => true,
            'tempo_resposta' => 30,
        ]);

        // Fazer requisição com o usuário atual (sem respostas)
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/estatisticas/dashboard');

        $response->assertStatus(200);
        
        $data = $response->json('data.resumo');
        
        // Deve retornar 0 questões, não deve contar as do outro usuário
        $this->assertEquals(0, $data['total_questoes_respondidas']);
    }

    // ===== MÉTODOS AUXILIARES =====

    /**
     * Criar respostas de usuário para testes
     */
    private function criarRespostasUsuario(int $total, int $acertos, ?int $simuladoId = null)
    {
        $simuladoId = $simuladoId ?? $this->simulado->id;
        
        for ($i = 0; $i < $total; $i++) {
            $questao = $this->criarQuestao();
            $alternativa = $questao->alternativas->first();
            
            RespostaUsuario::create([
                'user_id' => $this->user->id,
                'questao_id' => $questao->id,
                'alternativa_id' => $alternativa->id,
                'simulado_id' => $simuladoId,
                'correta' => $i < $acertos,
                'tempo_resposta' => rand(20, 60),
            ]);
        }
    }

    /**
     * Criar questão com alternativas para testes
     */
    private function criarQuestao()
    {
        $questao = Questao::create([
            'tema_id' => $this->tema->id,
            'user_id' => $this->user->id,
            'enunciado' => 'Pergunta de teste ' . uniqid(),
            'nivel' => 'medio',
            'tipo_questao' => 'concurso',
        ]);

        // Criar 4 alternativas
        for ($i = 0; $i < 4; $i++) {
            Alternativa::create([
                'questao_id' => $questao->id,
                'texto' => 'Alternativa ' . chr(65 + $i),
                'correta' => $i === 0, // Primeira é correta
            ]);
        }

        return $questao->load('alternativas');
    }
}
