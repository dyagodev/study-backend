<?php

use App\Models\User;
use App\Models\Tema;
use App\Models\Questao;
use App\Models\Alternativa;
use App\Models\RespostaUsuario;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->user = User::factory()->create(['creditos' => 100]);
    Sanctum::actingAs($this->user);

    $this->tema = Tema::create([
        'nome' => 'Matemática',
        'descricao' => 'Questões de matemática',
    ]);
});

test('exibe desempenho quando não há mais questões disponíveis', function () {
    // Criar uma questão
    $questao = Questao::create([
        'user_id' => $this->user->id,
        'tema_id' => $this->tema->id,
        'enunciado' => 'Quanto é 2+2?',
        'nivel' => 'facil',
    ]);

    $alternativaCorreta = Alternativa::create([
        'questao_id' => $questao->id,
        'texto' => '4',
        'correta' => true,
    ]);

    // Responder a questão
    RespostaUsuario::create([
        'user_id' => $this->user->id,
        'questao_id' => $questao->id,
        'alternativa_id' => $alternativaCorreta->id,
        'correta' => true,
        'tempo_resposta' => 10,
    ]);

    // Tentar buscar próxima questão (não deve ter)
    $response = $this->postJson('/api/questoes/proxima-questao', [
        'tema_id' => $this->tema->id,
        'nivel' => 'facil',
    ]);

    $response->assertStatus(404)
        ->assertJsonPath('success', false)
        ->assertJsonPath('data.questoes_acabaram', true)
        ->assertJsonPath('data.desempenho.resumo.total_respostas', 1)
        ->assertJsonPath('data.desempenho.resumo.acertos', 1);

    // Percentual de acerto pode ser int ou float
    $percentual = $response->json('data.desempenho.resumo.percentual_acerto');
    expect($percentual)->toBeIn([100, 100.0]);
});

test('calcula percentual de acerto corretamente', function () {
    // Criar 10 questões
    for ($i = 1; $i <= 10; $i++) {
        $questao = Questao::create([
            'user_id' => $this->user->id,
            'tema_id' => $this->tema->id,
            'enunciado' => "Questão $i",
            'nivel' => 'medio',
        ]);

        $alternativaCorreta = Alternativa::create([
            'questao_id' => $questao->id,
            'texto' => 'Correta',
            'correta' => true,
        ]);

        $alternativaErrada = Alternativa::create([
            'questao_id' => $questao->id,
            'texto' => 'Errada',
            'correta' => false,
        ]);

        // Acertar 7, errar 3
        $correta = $i <= 7;
        RespostaUsuario::create([
            'user_id' => $this->user->id,
            'questao_id' => $questao->id,
            'alternativa_id' => $correta ? $alternativaCorreta->id : $alternativaErrada->id,
            'correta' => $correta,
            'tempo_resposta' => 15,
        ]);
    }

    $response = $this->postJson('/api/questoes/desempenho', [
        'tema_id' => $this->tema->id,
        'nivel' => 'medio',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.desempenho.resumo.total_respostas', 10)
        ->assertJsonPath('data.desempenho.resumo.acertos', 7)
        ->assertJsonPath('data.desempenho.resumo.erros', 3);

    $percentual = $response->json('data.desempenho.resumo.percentual_acerto');
    expect($percentual)->toBeIn([70, 70.0]);
});

test('calcula maior sequência de acertos', function () {
    // Criar padrão: acerto, acerto, acerto, erro, acerto, acerto, erro
    $padroes = [true, true, true, false, true, true, false];

    foreach ($padroes as $index => $acerto) {
        $questao = Questao::create([
            'user_id' => $this->user->id,
            'tema_id' => $this->tema->id,
            'enunciado' => "Questão " . ($index + 1),
            'nivel' => 'facil',
        ]);

        $alternativa = Alternativa::create([
            'questao_id' => $questao->id,
            'texto' => 'Alternativa',
            'correta' => true,
        ]);

        RespostaUsuario::create([
            'user_id' => $this->user->id,
            'questao_id' => $questao->id,
            'alternativa_id' => $alternativa->id,
            'correta' => $acerto,
            'tempo_resposta' => 10,
            'created_at' => Carbon::now()->addSeconds($index),
        ]);
    }

    $response = $this->postJson('/api/questoes/desempenho', [
        'tema_id' => $this->tema->id,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.desempenho.sequencias.maior_sequencia_acertos', 3)
        ->assertJsonPath('data.desempenho.sequencias.maior_sequencia_erros', 1);
});

test('mostra evolução quando há mais de 10 respostas', function () {
    // Criar 20 respostas para testar evolução
    for ($i = 1; $i <= 20; $i++) {
        $questao = Questao::create([
            'user_id' => $this->user->id,
            'tema_id' => $this->tema->id,
            'enunciado' => "Questão $i",
            'nivel' => 'dificil',
        ]);

        $alternativa = Alternativa::create([
            'questao_id' => $questao->id,
            'texto' => 'Alternativa',
            'correta' => true,
        ]);

        // Primeiras 10: 40% de acerto, Últimas 10: 80% de acerto
        $correta = ($i <= 10 && $i <= 4) || ($i > 10 && $i <= 18);

        RespostaUsuario::create([
            'user_id' => $this->user->id,
            'questao_id' => $questao->id,
            'alternativa_id' => $alternativa->id,
            'correta' => $correta,
            'tempo_resposta' => 20,
            'created_at' => Carbon::now()->subDays(30)->addHours($i),
        ]);
    }

    $response = $this->postJson('/api/questoes/desempenho', [
        'tema_id' => $this->tema->id,
    ]);

    $response->assertStatus(200);

    $evolucao = $response->json('data.desempenho.evolucao');
    expect($evolucao)->not->toBeNull();
    expect($evolucao)->toHaveKey('percentual_inicio');
    expect($evolucao)->toHaveKey('percentual_recente');
    expect($evolucao)->toHaveKey('diferenca');
    expect($evolucao)->toHaveKey('melhorou');
});

test('avalia desempenho como excelente quando acima de 90%', function () {
    // Acertar 95 de 100
    for ($i = 1; $i <= 100; $i++) {
        $questao = Questao::create([
            'user_id' => $this->user->id,
            'tema_id' => $this->tema->id,
            'enunciado' => "Questão $i",
            'nivel' => 'facil',
        ]);

        $alternativa = Alternativa::create([
            'questao_id' => $questao->id,
            'texto' => 'Alternativa',
            'correta' => true,
        ]);

        RespostaUsuario::create([
            'user_id' => $this->user->id,
            'questao_id' => $questao->id,
            'alternativa_id' => $alternativa->id,
            'correta' => $i <= 95,
            'tempo_resposta' => 10,
        ]);
    }

    $response = $this->postJson('/api/questoes/desempenho', [
        'tema_id' => $this->tema->id,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.desempenho.avaliacao.nivel', 'Excelente');
});

test('avalia desempenho como precisa melhorar quando abaixo de 40%', function () {
    // Acertar apenas 3 de 10
    for ($i = 1; $i <= 10; $i++) {
        $questao = Questao::create([
            'user_id' => $this->user->id,
            'tema_id' => $this->tema->id,
            'enunciado' => "Questão $i",
            'nivel' => 'dificil',
        ]);

        $alternativa = Alternativa::create([
            'questao_id' => $questao->id,
            'texto' => 'Alternativa',
            'correta' => true,
        ]);

        RespostaUsuario::create([
            'user_id' => $this->user->id,
            'questao_id' => $questao->id,
            'alternativa_id' => $alternativa->id,
            'correta' => $i <= 3,
            'tempo_resposta' => 30,
        ]);
    }

    $response = $this->postJson('/api/questoes/desempenho', [
        'tema_id' => $this->tema->id,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.desempenho.avaliacao.nivel', 'Precisa Melhorar');
});

test('mostra desempenho quando sem créditos para gerar questões', function () {
    // Usuário sem créditos
    $this->user->update(['creditos' => 0]);

    // Criar e responder uma questão
    $questao = Questao::create([
        'user_id' => $this->user->id,
        'tema_id' => $this->tema->id,
        'enunciado' => 'Questão única',
        'nivel' => 'medio',
    ]);

    $alternativa = Alternativa::create([
        'questao_id' => $questao->id,
        'texto' => 'Alternativa',
        'correta' => true,
    ]);

    RespostaUsuario::create([
        'user_id' => $this->user->id,
        'questao_id' => $questao->id,
        'alternativa_id' => $alternativa->id,
        'correta' => true,
        'tempo_resposta' => 15,
    ]);

    // Tentar gerar mais questões
    $response = $this->postJson('/api/questoes/gerar-mais-questoes', [
        'tema_id' => $this->tema->id,
        'nivel' => 'medio',
        'quantidade' => 5,
    ]);

    $response->assertStatus(402)
        ->assertJsonPath('success', false)
        ->assertJsonPath('data.desempenho.resumo.total_respostas', 1)
        ->assertJsonPath('data.mensagem_motivacional', 'Enquanto isso, veja como você se saiu nas questões que já respondeu!');
});

test('calcula tempo médio de resposta corretamente', function () {
    // Criar 5 questões com tempos variados
    $tempos = [10, 20, 30, 40, 50]; // Média = 30

    foreach ($tempos as $index => $tempo) {
        $questao = Questao::create([
            'user_id' => $this->user->id,
            'tema_id' => $this->tema->id,
            'enunciado' => "Questão " . ($index + 1),
            'nivel' => 'medio',
        ]);

        $alternativa = Alternativa::create([
            'questao_id' => $questao->id,
            'texto' => 'Alternativa',
            'correta' => true,
        ]);

        RespostaUsuario::create([
            'user_id' => $this->user->id,
            'questao_id' => $questao->id,
            'alternativa_id' => $alternativa->id,
            'correta' => true,
            'tempo_resposta' => $tempo,
        ]);
    }

    $response = $this->postJson('/api/questoes/desempenho', [
        'tema_id' => $this->tema->id,
    ]);

    $response->assertStatus(200);

    $tempoMedio = $response->json('data.desempenho.resumo.tempo_medio_segundos');
    expect($tempoMedio)->toBeIn([30, 30.0]);
    expect($response->json('data.desempenho.resumo.tempo_medio_formatado'))->toBe('30s');
});

test('desempenho respeita isolamento entre usuários', function () {
    // Usuário 2
    $user2 = User::factory()->create(['creditos' => 100]);

    // Criar questões e respostas para ambos usuários
    for ($i = 1; $i <= 5; $i++) {
        $questao = Questao::create([
            'user_id' => $this->user->id,
            'tema_id' => $this->tema->id,
            'enunciado' => "Questão user 1 - $i",
            'nivel' => 'facil',
        ]);

        $alternativa = Alternativa::create([
            'questao_id' => $questao->id,
            'texto' => 'Alternativa',
            'correta' => true,
        ]);

        // Usuário 1 acerta todas
        RespostaUsuario::create([
            'user_id' => $this->user->id,
            'questao_id' => $questao->id,
            'alternativa_id' => $alternativa->id,
            'correta' => true,
            'tempo_resposta' => 10,
        ]);
    }

    for ($i = 1; $i <= 5; $i++) {
        $questao2 = Questao::create([
            'user_id' => $user2->id,
            'tema_id' => $this->tema->id,
            'enunciado' => "Questão user 2 - $i",
            'nivel' => 'facil',
        ]);

        $alternativa2 = Alternativa::create([
            'questao_id' => $questao2->id,
            'texto' => 'Alternativa',
            'correta' => true,
        ]);

        // Usuário 2 erra todas
        RespostaUsuario::create([
            'user_id' => $user2->id,
            'questao_id' => $questao2->id,
            'alternativa_id' => $alternativa2->id,
            'correta' => false,
            'tempo_resposta' => 20,
        ]);
    }

    // Verificar desempenho do usuário 1
    $response1 = $this->postJson('/api/questoes/desempenho', [
        'tema_id' => $this->tema->id,
    ]);

    $response1->assertStatus(200);
    $percentual1 = $response1->json('data.desempenho.resumo.percentual_acerto');
    expect($percentual1)->toBeIn([100, 100.0]);

    // Verificar desempenho do usuário 2
    Sanctum::actingAs($user2);
    $response2 = $this->postJson('/api/questoes/desempenho', [
        'tema_id' => $this->tema->id,
    ]);

    $response2->assertStatus(200);
    $percentual2 = $response2->json('data.desempenho.resumo.percentual_acerto');
    expect($percentual2)->toBeIn([0, 0.0]);
});

test('mensagem adequada quando não há respostas', function () {
    // Não responder nenhuma questão
    $response = $this->postJson('/api/questoes/desempenho', [
        'tema_id' => $this->tema->id,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.desempenho.mensagem', 'Você ainda não respondeu nenhuma questão com estas configurações.')
        ->assertJsonPath('data.desempenho.total_respostas', 0);
});

test('filtra desempenho por nível corretamente', function () {
    // Criar questões fáceis e médias
    for ($i = 1; $i <= 5; $i++) {
        $questaoFacil = Questao::create([
            'user_id' => $this->user->id,
            'tema_id' => $this->tema->id,
            'enunciado' => "Questão fácil $i",
            'nivel' => 'facil',
        ]);

        $altFacil = Alternativa::create([
            'questao_id' => $questaoFacil->id,
            'texto' => 'Alternativa',
            'correta' => true,
        ]);

        RespostaUsuario::create([
            'user_id' => $this->user->id,
            'questao_id' => $questaoFacil->id,
            'alternativa_id' => $altFacil->id,
            'correta' => true,
            'tempo_resposta' => 10,
        ]);

        $questaoMedia = Questao::create([
            'user_id' => $this->user->id,
            'tema_id' => $this->tema->id,
            'enunciado' => "Questão média $i",
            'nivel' => 'medio',
        ]);

        $altMedia = Alternativa::create([
            'questao_id' => $questaoMedia->id,
            'texto' => 'Alternativa',
            'correta' => true,
        ]);

        RespostaUsuario::create([
            'user_id' => $this->user->id,
            'questao_id' => $questaoMedia->id,
            'alternativa_id' => $altMedia->id,
            'correta' => false,
            'tempo_resposta' => 20,
        ]);
    }

    // Desempenho apenas nas fáceis
    $response = $this->postJson('/api/questoes/desempenho', [
        'tema_id' => $this->tema->id,
        'nivel' => 'facil',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.desempenho.resumo.total_respostas', 5);

    $percentual = $response->json('data.desempenho.resumo.percentual_acerto');
    expect($percentual)->toBeIn([100, 100.0]);
});
