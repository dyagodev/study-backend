<?php

use App\Models\User;
use App\Models\Tema;
use App\Models\Questao;
use App\Models\Alternativa;
use App\Models\RespostaUsuario;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = User::factory()->create(['creditos' => 100]);
    Sanctum::actingAs($this->user);

    $this->tema = Tema::create([
        'nome' => 'Tema Teste',
        'descricao' => 'Tema para teste',
    ]);
});

test('questões não respondidas mostram foi_respondida como false', function () {
    // Criar questão
    $questao = Questao::create([
        'user_id' => $this->user->id,
        'tema_id' => $this->tema->id,
        'enunciado' => 'Questão não respondida',
        'nivel' => 'medio',
    ]);

    Alternativa::create([
        'questao_id' => $questao->id,
        'texto' => 'Alternativa correta',
        'correta' => true,
    ]);

    $response = $this->getJson('/api/questoes');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.0.foi_respondida', false)
        ->assertJsonPath('data.0.total_respostas', 0)
        ->assertJsonPath('data.0.ultima_resposta', null);
});

test('questões respondidas mostram foi_respondida como true', function () {
    // Criar questão
    $questao = Questao::create([
        'user_id' => $this->user->id,
        'tema_id' => $this->tema->id,
        'enunciado' => 'Questão respondida',
        'nivel' => 'medio',
    ]);

    $alternativaCorreta = Alternativa::create([
        'questao_id' => $questao->id,
        'texto' => 'Alternativa correta',
        'correta' => true,
    ]);

    // Responder a questão
    RespostaUsuario::create([
        'user_id' => $this->user->id,
        'questao_id' => $questao->id,
        'alternativa_id' => $alternativaCorreta->id,
        'correta' => true,
    ]);

    $response = $this->getJson('/api/questoes');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.0.foi_respondida', true)
        ->assertJsonPath('data.0.total_respostas', 1);

    // Verificar que ultima_resposta não é null
    expect($response->json('data.0.ultima_resposta'))->not->toBeNull();
});

test('questões com múltiplas respostas mostram contagem correta', function () {
    // Criar questão
    $questao = Questao::create([
        'user_id' => $this->user->id,
        'tema_id' => $this->tema->id,
        'enunciado' => 'Questão com múltiplas respostas',
        'nivel' => 'medio',
    ]);

    $alternativaCorreta = Alternativa::create([
        'questao_id' => $questao->id,
        'texto' => 'Alternativa correta',
        'correta' => true,
    ]);

    $alternativaIncorreta = Alternativa::create([
        'questao_id' => $questao->id,
        'texto' => 'Alternativa incorreta',
        'correta' => false,
    ]);

    // Responder 3 vezes
    RespostaUsuario::create([
        'user_id' => $this->user->id,
        'questao_id' => $questao->id,
        'alternativa_id' => $alternativaIncorreta->id,
        'correta' => false,
    ]);

    sleep(1); // Garantir timestamps diferentes

    RespostaUsuario::create([
        'user_id' => $this->user->id,
        'questao_id' => $questao->id,
        'alternativa_id' => $alternativaIncorreta->id,
        'correta' => false,
    ]);

    sleep(1);

    $ultimaResposta = RespostaUsuario::create([
        'user_id' => $this->user->id,
        'questao_id' => $questao->id,
        'alternativa_id' => $alternativaCorreta->id,
        'correta' => true,
    ]);

    $response = $this->getJson('/api/questoes');

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.0.foi_respondida', true)
        ->assertJsonPath('data.0.total_respostas', 3);

    // Verificar que a última resposta é a mais recente
    $ultimaRespostaRetornada = $response->json('data.0.ultima_resposta');
    expect($ultimaRespostaRetornada)->not->toBeNull();
});

test('listagem mista mostra status correto para cada questão', function () {
    // Criar 3 questões
    $questao1 = Questao::create([
        'user_id' => $this->user->id,
        'tema_id' => $this->tema->id,
        'enunciado' => 'Questão 1 - Respondida',
        'nivel' => 'medio',
    ]);

    $alt1 = Alternativa::create([
        'questao_id' => $questao1->id,
        'texto' => 'Alternativa',
        'correta' => true,
    ]);

    RespostaUsuario::create([
        'user_id' => $this->user->id,
        'questao_id' => $questao1->id,
        'alternativa_id' => $alt1->id,
        'correta' => true,
    ]);

    $questao2 = Questao::create([
        'user_id' => $this->user->id,
        'tema_id' => $this->tema->id,
        'enunciado' => 'Questão 2 - Não respondida',
        'nivel' => 'facil',
    ]);

    Alternativa::create([
        'questao_id' => $questao2->id,
        'texto' => 'Alternativa',
        'correta' => true,
    ]);

    $questao3 = Questao::create([
        'user_id' => $this->user->id,
        'tema_id' => $this->tema->id,
        'enunciado' => 'Questão 3 - Respondida duas vezes',
        'nivel' => 'dificil',
    ]);

    $alt3 = Alternativa::create([
        'questao_id' => $questao3->id,
        'texto' => 'Alternativa',
        'correta' => true,
    ]);

    RespostaUsuario::create([
        'user_id' => $this->user->id,
        'questao_id' => $questao3->id,
        'alternativa_id' => $alt3->id,
        'correta' => false,
    ]);

    RespostaUsuario::create([
        'user_id' => $this->user->id,
        'questao_id' => $questao3->id,
        'alternativa_id' => $alt3->id,
        'correta' => true,
    ]);

    $response = $this->getJson('/api/questoes');

    $response->assertStatus(200)
        ->assertJsonPath('success', true);

    $questoes = $response->json('data');
    expect($questoes)->toHaveCount(3);

    // Encontrar cada questão e validar status
    $q1 = collect($questoes)->firstWhere('id', $questao1->id);
    $q2 = collect($questoes)->firstWhere('id', $questao2->id);
    $q3 = collect($questoes)->firstWhere('id', $questao3->id);

    expect($q1['foi_respondida'])->toBeTrue();
    expect($q1['total_respostas'])->toBe(1);

    expect($q2['foi_respondida'])->toBeFalse();
    expect($q2['total_respostas'])->toBe(0);

    expect($q3['foi_respondida'])->toBeTrue();
    expect($q3['total_respostas'])->toBe(2);
});

test('usuários diferentes não veem respostas uns dos outros', function () {
    // Criar questão do usuário 1
    $questao = Questao::create([
        'user_id' => $this->user->id,
        'tema_id' => $this->tema->id,
        'enunciado' => 'Questão do usuário 1',
        'nivel' => 'medio',
    ]);

    $alternativa = Alternativa::create([
        'questao_id' => $questao->id,
        'texto' => 'Alternativa',
        'correta' => true,
    ]);

    // Usuário 1 responde
    RespostaUsuario::create([
        'user_id' => $this->user->id,
        'questao_id' => $questao->id,
        'alternativa_id' => $alternativa->id,
        'correta' => true,
    ]);

    // Criar usuário 2
    $user2 = User::factory()->create(['creditos' => 100]);

    // Criar questão do usuário 2 (mesma questão, mas dele)
    $questao2 = Questao::create([
        'user_id' => $user2->id,
        'tema_id' => $this->tema->id,
        'enunciado' => 'Questão do usuário 1', // Mesmo enunciado
        'nivel' => 'medio',
    ]);

    Alternativa::create([
        'questao_id' => $questao2->id,
        'texto' => 'Alternativa',
        'correta' => true,
    ]);

    // Autenticar como usuário 2
    Sanctum::actingAs($user2);

    // Usuário 2 lista suas questões
    $response = $this->getJson('/api/questoes');

    $response->assertStatus(200);

    $questoes = $response->json('data');
    expect($questoes)->toHaveCount(1);

    // A questão do usuário 2 não deve mostrar como respondida
    expect($questoes[0]['foi_respondida'])->toBeFalse();
    expect($questoes[0]['total_respostas'])->toBe(0);
});

test('indicador funciona com paginação', function () {
    // Criar 20 questões, responder apenas as 10 primeiras
    for ($i = 1; $i <= 20; $i++) {
        $questao = Questao::create([
            'user_id' => $this->user->id,
            'tema_id' => $this->tema->id,
            'enunciado' => "Questão $i",
            'nivel' => 'medio',
        ]);

        $alternativa = Alternativa::create([
            'questao_id' => $questao->id,
            'texto' => 'Alternativa',
            'correta' => true,
        ]);

        // Responder apenas as 10 primeiras
        if ($i <= 10) {
            RespostaUsuario::create([
                'user_id' => $this->user->id,
                'questao_id' => $questao->id,
                'alternativa_id' => $alternativa->id,
                'correta' => true,
            ]);
        }
    }

    // Primeira página (15 itens)
    $response1 = $this->getJson('/api/questoes?per_page=15');
    $response1->assertStatus(200);

    $questoesPagina1 = $response1->json('data');
    expect($questoesPagina1)->toHaveCount(15);

    // Segunda página (5 itens)
    $response2 = $this->getJson('/api/questoes?per_page=15&page=2');
    $response2->assertStatus(200);

    $questoesPagina2 = $response2->json('data');
    expect($questoesPagina2)->toHaveCount(5);

    // Todas as questões devem ter os campos de resposta
    foreach ($questoesPagina1 as $questao) {
        expect($questao)->toHaveKeys(['foi_respondida', 'total_respostas', 'ultima_resposta']);
    }

    foreach ($questoesPagina2 as $questao) {
        expect($questao)->toHaveKeys(['foi_respondida', 'total_respostas', 'ultima_resposta']);
    }
});

test('filtros não afetam indicador de resposta', function () {
    // Criar questões com diferentes atributos
    $questaoFavorita = Questao::create([
        'user_id' => $this->user->id,
        'tema_id' => $this->tema->id,
        'enunciado' => 'Questão favorita respondida',
        'nivel' => 'medio',
        'favorita' => true,
    ]);

    $alt = Alternativa::create([
        'questao_id' => $questaoFavorita->id,
        'texto' => 'Alternativa',
        'correta' => true,
    ]);

    RespostaUsuario::create([
        'user_id' => $this->user->id,
        'questao_id' => $questaoFavorita->id,
        'alternativa_id' => $alt->id,
        'correta' => true,
    ]);

    // Filtrar por favoritas
    $response = $this->getJson('/api/questoes?favoritas=true');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.foi_respondida', true)
        ->assertJsonPath('data.0.total_respostas', 1);
});
