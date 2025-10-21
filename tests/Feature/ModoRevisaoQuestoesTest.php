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
        'nome' => 'Português',
        'descricao' => 'Questões de português',
    ]);
});

test('busca questão não respondida por padrão', function () {
    // Criar 2 questões
    $questao1 = Questao::create([
        'user_id' => $this->user->id,
        'tema_id' => $this->tema->id,
        'enunciado' => 'Questão 1',
        'nivel' => 'facil',
    ]);

    Alternativa::create([
        'questao_id' => $questao1->id,
        'texto' => 'Alternativa',
        'correta' => true,
    ]);

    $questao2 = Questao::create([
        'user_id' => $this->user->id,
        'tema_id' => $this->tema->id,
        'enunciado' => 'Questão 2',
        'nivel' => 'facil',
    ]);

    $alt2 = Alternativa::create([
        'questao_id' => $questao2->id,
        'texto' => 'Alternativa',
        'correta' => true,
    ]);

    // Responder questão 1
    RespostaUsuario::create([
        'user_id' => $this->user->id,
        'questao_id' => $questao1->id,
        'alternativa_id' => $alt2->id,
        'correta' => true,
    ]);

    // Buscar próxima (deve retornar questão 2)
    $response = $this->postJson('/api/questoes/proxima-questao', [
        'tema_id' => $this->tema->id,
        'nivel' => 'facil',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.questao.id', $questao2->id)
        ->assertJsonPath('data.ja_respondida', false)
        ->assertJsonPath('data.modo_revisao', false)
        ->assertJsonPath('data.total_disponiveis', 1);
});

test('permite incluir questões já respondidas com flag', function () {
    // Criar 2 questões e responder ambas
    $questoes = [];
    for ($i = 1; $i <= 2; $i++) {
        $questao = Questao::create([
            'user_id' => $this->user->id,
            'tema_id' => $this->tema->id,
            'enunciado' => "Questão $i",
            'nivel' => 'medio',
        ]);

        $alt = Alternativa::create([
            'questao_id' => $questao->id,
            'texto' => 'Alternativa',
            'correta' => true,
        ]);

        RespostaUsuario::create([
            'user_id' => $this->user->id,
            'questao_id' => $questao->id,
            'alternativa_id' => $alt->id,
            'correta' => true,
        ]);

        $questoes[] = $questao;
    }

    // Sem flag - não deve retornar nenhuma
    $response1 = $this->postJson('/api/questoes/proxima-questao', [
        'tema_id' => $this->tema->id,
        'nivel' => 'medio',
    ]);

    $response1->assertStatus(404)
        ->assertJsonPath('data.questoes_acabaram', true)
        ->assertJsonPath('data.modo_revisao_ativo', false);

    // Com flag - deve retornar uma questão
    $response2 = $this->postJson('/api/questoes/proxima-questao', [
        'tema_id' => $this->tema->id,
        'nivel' => 'medio',
        'incluir_respondidas' => true,
    ]);

    $response2->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.ja_respondida', true)
        ->assertJsonPath('data.modo_revisao', true)
        ->assertJsonPath('data.total_disponiveis', 2);

    // Verificar se retornou uma das questões respondidas
    $questaoRetornada = $response2->json('data.questao.id');
    expect($questaoRetornada)->toBeIn([$questoes[0]->id, $questoes[1]->id]);
});

test('sugere modo revisão quando não há questões não respondidas', function () {
    // Criar e responder uma questão
    $questao = Questao::create([
        'user_id' => $this->user->id,
        'tema_id' => $this->tema->id,
        'enunciado' => 'Única questão',
        'nivel' => 'dificil',
    ]);

    $alt = Alternativa::create([
        'questao_id' => $questao->id,
        'texto' => 'Alternativa',
        'correta' => true,
    ]);

    RespostaUsuario::create([
        'user_id' => $this->user->id,
        'questao_id' => $questao->id,
        'alternativa_id' => $alt->id,
        'correta' => true,
    ]);

    // Buscar sem modo revisão
    $response = $this->postJson('/api/questoes/proxima-questao', [
        'tema_id' => $this->tema->id,
        'nivel' => 'dificil',
    ]);

    $response->assertStatus(404)
        ->assertJsonPath('data.modo_revisao_ativo', false)
        ->assertJsonPath('data.sugestao_modo_revisao', 'Ative incluir_respondidas=true para revisar questões já respondidas');
});

test('não sugere modo revisão quando já está ativo', function () {
    // Criar e responder uma questão
    $questao = Questao::create([
        'user_id' => $this->user->id,
        'tema_id' => $this->tema->id,
        'enunciado' => 'Única questão',
        'nivel' => 'facil',
    ]);

    $alt = Alternativa::create([
        'questao_id' => $questao->id,
        'texto' => 'Alternativa',
        'correta' => true,
    ]);

    RespostaUsuario::create([
        'user_id' => $this->user->id,
        'questao_id' => $questao->id,
        'alternativa_id' => $alt->id,
        'correta' => true,
    ]);

    // Responder a mesma questão novamente
    RespostaUsuario::create([
        'user_id' => $this->user->id,
        'questao_id' => $questao->id,
        'alternativa_id' => $alt->id,
        'correta' => false,
    ]);

    // Buscar COM modo revisão (mas já respondeu 2x)
    $response = $this->postJson('/api/questoes/proxima-questao', [
        'tema_id' => $this->tema->id,
        'nivel' => 'facil',
        'incluir_respondidas' => true,
    ]);

    // Deve retornar a questão normalmente
    $response->assertStatus(200)
        ->assertJsonPath('data.modo_revisao', true)
        ->assertJsonPath('data.ja_respondida', true);
});

test('modo revisão permite responder mesma questão múltiplas vezes', function () {
    // Criar questão
    $questao = Questao::create([
        'user_id' => $this->user->id,
        'tema_id' => $this->tema->id,
        'enunciado' => 'Questão para treino',
        'nivel' => 'medio',
    ]);

    $altCorreta = Alternativa::create([
        'questao_id' => $questao->id,
        'texto' => 'Correta',
        'correta' => true,
    ]);

    $altErrada = Alternativa::create([
        'questao_id' => $questao->id,
        'texto' => 'Errada',
        'correta' => false,
    ]);

    // Responder 3 vezes
    for ($i = 1; $i <= 3; $i++) {
        RespostaUsuario::create([
            'user_id' => $this->user->id,
            'questao_id' => $questao->id,
            'alternativa_id' => $i <= 2 ? $altErrada->id : $altCorreta->id,
            'correta' => $i > 2,
        ]);
    }

    // Buscar com modo revisão
    $response = $this->postJson('/api/questoes/proxima-questao', [
        'tema_id' => $this->tema->id,
        'nivel' => 'medio',
        'incluir_respondidas' => true,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.questao.id', $questao->id)
        ->assertJsonPath('data.ja_respondida', true);
});

test('conta corretamente questões respondidas no modo revisão', function () {
    // Criar 5 questões, responder 3
    for ($i = 1; $i <= 5; $i++) {
        $questao = Questao::create([
            'user_id' => $this->user->id,
            'tema_id' => $this->tema->id,
            'enunciado' => "Questão $i",
            'nivel' => 'facil',
        ]);

        $alt = Alternativa::create([
            'questao_id' => $questao->id,
            'texto' => 'Alternativa',
            'correta' => true,
        ]);

        // Responder apenas as 3 primeiras
        if ($i <= 3) {
            RespostaUsuario::create([
                'user_id' => $this->user->id,
                'questao_id' => $questao->id,
                'alternativa_id' => $alt->id,
                'correta' => true,
            ]);
        }
    }

    // Buscar sem modo revisão
    $response1 = $this->postJson('/api/questoes/proxima-questao', [
        'tema_id' => $this->tema->id,
        'nivel' => 'facil',
    ]);

    $response1->assertStatus(200)
        ->assertJsonPath('data.total_disponiveis', 2) // 5 - 3 = 2 não respondidas
        ->assertJsonPath('data.total_respondidas', 3);

    // Buscar com modo revisão
    $response2 = $this->postJson('/api/questoes/proxima-questao', [
        'tema_id' => $this->tema->id,
        'nivel' => 'facil',
        'incluir_respondidas' => true,
    ]);

    $response2->assertStatus(200)
        ->assertJsonPath('data.total_disponiveis', 5) // Todas disponíveis
        ->assertJsonPath('data.total_respondidas', 3);
});

test('modo revisão respeita outros filtros', function () {
    // Criar questões de diferentes níveis
    $questaoFacil = Questao::create([
        'user_id' => $this->user->id,
        'tema_id' => $this->tema->id,
        'enunciado' => 'Questão fácil',
        'nivel' => 'facil',
    ]);

    $altFacil = Alternativa::create([
        'questao_id' => $questaoFacil->id,
        'texto' => 'Alternativa',
        'correta' => true,
    ]);

    $questaoMedia = Questao::create([
        'user_id' => $this->user->id,
        'tema_id' => $this->tema->id,
        'enunciado' => 'Questão média',
        'nivel' => 'medio',
    ]);

    $altMedia = Alternativa::create([
        'questao_id' => $questaoMedia->id,
        'texto' => 'Alternativa',
        'correta' => true,
    ]);

    // Responder ambas
    RespostaUsuario::create([
        'user_id' => $this->user->id,
        'questao_id' => $questaoFacil->id,
        'alternativa_id' => $altFacil->id,
        'correta' => true,
    ]);

    RespostaUsuario::create([
        'user_id' => $this->user->id,
        'questao_id' => $questaoMedia->id,
        'alternativa_id' => $altMedia->id,
        'correta' => true,
    ]);

    // Buscar apenas fáceis com modo revisão
    $response = $this->postJson('/api/questoes/proxima-questao', [
        'tema_id' => $this->tema->id,
        'nivel' => 'facil',
        'incluir_respondidas' => true,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.questao.id', $questaoFacil->id)
        ->assertJsonPath('data.questao.nivel', 'facil');
});

test('modo revisão funciona com tipo_questao e banca', function () {
    // Criar questões com tipo e banca
    $questao1 = Questao::create([
        'user_id' => $this->user->id,
        'tema_id' => $this->tema->id,
        'enunciado' => 'Questão CESPE',
        'nivel' => 'medio',
        'tipo_questao' => 'concurso',
        'banca' => 'CESPE',
    ]);

    $alt1 = Alternativa::create([
        'questao_id' => $questao1->id,
        'texto' => 'Alternativa',
        'correta' => true,
    ]);

    $questao2 = Questao::create([
        'user_id' => $this->user->id,
        'tema_id' => $this->tema->id,
        'enunciado' => 'Questão FCC',
        'nivel' => 'medio',
        'tipo_questao' => 'concurso',
        'banca' => 'FCC',
    ]);

    $alt2 = Alternativa::create([
        'questao_id' => $questao2->id,
        'texto' => 'Alternativa',
        'correta' => true,
    ]);

    // Responder ambas
    RespostaUsuario::create([
        'user_id' => $this->user->id,
        'questao_id' => $questao1->id,
        'alternativa_id' => $alt1->id,
        'correta' => true,
    ]);

    RespostaUsuario::create([
        'user_id' => $this->user->id,
        'questao_id' => $questao2->id,
        'alternativa_id' => $alt2->id,
        'correta' => true,
    ]);

    // Buscar apenas CESPE com modo revisão
    $response = $this->postJson('/api/questoes/proxima-questao', [
        'tema_id' => $this->tema->id,
        'nivel' => 'medio',
        'tipo_questao' => 'concurso',
        'banca' => 'CESPE',
        'incluir_respondidas' => true,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.questao.id', $questao1->id)
        ->assertJsonPath('data.questao.banca', 'CESPE');
});

test('campo ja_respondida indica corretamente status da questão', function () {
    // Criar 2 questões
    $questao1 = Questao::create([
        'user_id' => $this->user->id,
        'tema_id' => $this->tema->id,
        'enunciado' => 'Questão 1 - respondida',
        'nivel' => 'facil',
    ]);

    $alt1 = Alternativa::create([
        'questao_id' => $questao1->id,
        'texto' => 'Alternativa',
        'correta' => true,
    ]);

    $questao2 = Questao::create([
        'user_id' => $this->user->id,
        'tema_id' => $this->tema->id,
        'enunciado' => 'Questão 2 - não respondida',
        'nivel' => 'facil',
    ]);

    Alternativa::create([
        'questao_id' => $questao2->id,
        'texto' => 'Alternativa',
        'correta' => true,
    ]);

    // Responder apenas questão 1
    RespostaUsuario::create([
        'user_id' => $this->user->id,
        'questao_id' => $questao1->id,
        'alternativa_id' => $alt1->id,
        'correta' => true,
    ]);

    // Buscar questão não respondida
    $response1 = $this->postJson('/api/questoes/proxima-questao', [
        'tema_id' => $this->tema->id,
        'nivel' => 'facil',
    ]);

    $response1->assertStatus(200)
        ->assertJsonPath('data.questao.id', $questao2->id)
        ->assertJsonPath('data.ja_respondida', false);

    // Buscar com modo revisão (pode retornar qualquer uma)
    $response2 = $this->postJson('/api/questoes/proxima-questao', [
        'tema_id' => $this->tema->id,
        'nivel' => 'facil',
        'incluir_respondidas' => true,
    ]);

    $response2->assertStatus(200);

    $questaoRetornada = $response2->json('data.questao.id');
    $jaRespondida = $response2->json('data.ja_respondida');

    if ($questaoRetornada === $questao1->id) {
        expect($jaRespondida)->toBeTrue();
    } else {
        expect($jaRespondida)->toBeFalse();
    }
});

test('mensagem personalizada quando modo revisão ativo e sem questões', function () {
    // Criar e responder uma questão
    $questao = Questao::create([
        'user_id' => $this->user->id,
        'tema_id' => $this->tema->id,
        'enunciado' => 'Única questão',
        'nivel' => 'medio',
    ]);

    $alt = Alternativa::create([
        'questao_id' => $questao->id,
        'texto' => 'Alternativa',
        'correta' => true,
    ]);

    // Deletar a questão para não ter nenhuma disponível
    $questao->delete();

    // Buscar com modo revisão
    $response = $this->postJson('/api/questoes/proxima-questao', [
        'tema_id' => $this->tema->id,
        'nivel' => 'medio',
        'incluir_respondidas' => true,
    ]);

    $response->assertStatus(404)
        ->assertJsonPath('data.modo_revisao_ativo', true);

    $mensagem = $response->json('message');
    expect($mensagem)->toContain('incluindo as revisadas');
});
