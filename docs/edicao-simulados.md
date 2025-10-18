# 📝 Edição Completa de Simulados

## 🎯 Funcionalidades Implementadas

Sistema completo para edição de simulados, incluindo:
- ✅ Editar informações básicas (título, descrição, etc.)
- ✅ Substituir todas as questões de uma vez
- ✅ Adicionar questão individual
- ✅ Remover questão individual
- ✅ Reordenar questões

---

## 📡 Endpoints da API

### 1. Atualizar Simulado Completo

Atualiza informações básicas e/ou substitui todas as questões.

**PUT** `/api/simulados/{id}`

#### Request Body

```json
{
  "titulo": "Simulado Atualizado",
  "descricao": "Nova descrição",
  "tempo_limite": 90,
  "embaralhar_questoes": true,
  "mostrar_gabarito": false,
  "status": "ativo",
  "questoes": [
    {
      "questao_id": 1,
      "pontuacao": 2.0
    },
    {
      "questao_id": 3,
      "pontuacao": 1.5
    },
    {
      "questao_id": 5,
      "pontuacao": 1.0
    }
  ]
}
```

#### Campos

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| titulo | string | Não | Título do simulado (máx 255) |
| descricao | string | Não | Descrição do simulado |
| tempo_limite | integer | Não | Tempo em minutos |
| embaralhar_questoes | boolean | Não | Embaralhar ao iniciar |
| mostrar_gabarito | boolean | Não | Mostrar gabarito ao final |
| status | string | Não | rascunho, ativo, arquivado |
| questoes | array | Não* | Lista de questões do simulado |
| questoes[].questao_id | integer | Sim** | ID da questão |
| questoes[].pontuacao | numeric | Não | Pontuação (padrão: 1.0) |

*Se `questoes` for enviado, **todas** as questões antigas serão substituídas pelas novas.  
**Obrigatório se `questoes` for enviado.

#### Response Success (200)

```json
{
  "success": true,
  "message": "Simulado atualizado com sucesso",
  "data": {
    "id": 1,
    "titulo": "Simulado Atualizado",
    "descricao": "Nova descrição",
    "tempo_limite": 90,
    "embaralhar_questoes": true,
    "mostrar_gabarito": false,
    "status": "ativo",
    "questoes": [
      {
        "id": 1,
        "enunciado": "Questão 1...",
        "pivot": {
          "ordem": 1,
          "pontuacao": 2.0
        }
      }
      // ... mais questões
    ]
  }
}
```

#### Response Error (403)

```json
{
  "success": false,
  "message": "Você só pode adicionar questões que você criou"
}
```

---

### 2. Adicionar Questão Individual

Adiciona uma questão ao final do simulado.

**POST** `/api/simulados/{id}/questoes`

#### Request Body

```json
{
  "questao_id": 7,
  "pontuacao": 1.5
}
```

#### Campos

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| questao_id | integer | Sim | ID da questão a adicionar |
| pontuacao | numeric | Não | Pontuação (padrão: 1.0) |

#### Response Success (200)

```json
{
  "success": true,
  "message": "Questão adicionada ao simulado",
  "data": {
    "id": 1,
    "titulo": "Meu Simulado",
    "questoes": [
      // ... questões existentes
      {
        "id": 7,
        "enunciado": "Nova questão...",
        "pivot": {
          "ordem": 4,
          "pontuacao": 1.5
        }
      }
    ]
  }
}
```

#### Response Error (422)

```json
{
  "success": false,
  "message": "Esta questão já está no simulado"
}
```

---

### 3. Remover Questão Individual

Remove uma questão específica do simulado e reordena as demais.

**DELETE** `/api/simulados/{id}/questoes/{questaoId}`

#### Response Success (200)

```json
{
  "success": true,
  "message": "Questão removida do simulado",
  "data": {
    "id": 1,
    "titulo": "Meu Simulado",
    "questoes": [
      // Questões restantes, reordenadas
    ]
  }
}
```

#### Response Error (404)

```json
{
  "success": false,
  "message": "Esta questão não está no simulado"
}
```

---

### 4. Reordenar Questões

Reordena as questões do simulado.

**PUT** `/api/simulados/{id}/questoes/reordenar`

#### Request Body

```json
{
  "questoes": [
    {
      "questao_id": 5,
      "ordem": 1
    },
    {
      "questao_id": 3,
      "ordem": 2
    },
    {
      "questao_id": 1,
      "ordem": 3
    }
  ]
}
```

#### Campos

| Campo | Tipo | Obrigatório | Descrição |
|-------|------|-------------|-----------|
| questoes | array | Sim | Lista completa com novas ordens |
| questoes[].questao_id | integer | Sim | ID da questão |
| questoes[].ordem | integer | Sim | Nova ordem (1, 2, 3...) |

#### Response Success (200)

```json
{
  "success": true,
  "message": "Questões reordenadas com sucesso",
  "data": {
    "id": 1,
    "titulo": "Meu Simulado",
    "questoes": [
      {
        "id": 5,
        "pivot": {
          "ordem": 1
        }
      },
      {
        "id": 3,
        "pivot": {
          "ordem": 2
        }
      },
      {
        "id": 1,
        "pivot": {
          "ordem": 3
        }
      }
    ]
  }
}
```

---

## 🔒 Segurança

### Validações Implementadas

1. **Propriedade do Simulado**
   - Usuário só pode editar simulados que criou
   - Retorna 403 se tentar editar simulado de outro usuário

2. **Propriedade das Questões**
   - Usuário só pode adicionar questões que criou
   - Sistema valida `user_id` de cada questão
   - Retorna 403 se tentar adicionar questão de outro usuário

3. **Duplicação**
   - Não permite adicionar questão já existente no simulado
   - Retorna 422 se tentar duplicar

4. **Existência**
   - Valida que questões existem no banco
   - Valida que questões estão no simulado (ao remover/reordenar)

---

## 💡 Exemplos de Uso

### Exemplo 1: Atualizar Apenas o Título

```bash
curl -X PUT http://localhost/api/simulados/1 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "titulo": "Novo Título"
  }'
```

### Exemplo 2: Substituir Todas as Questões

```bash
curl -X PUT http://localhost/api/simulados/1 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "questoes": [
      {"questao_id": 10, "pontuacao": 2.0},
      {"questao_id": 11, "pontuacao": 1.5},
      {"questao_id": 12, "pontuacao": 1.0}
    ]
  }'
```

### Exemplo 3: Adicionar Uma Questão

```bash
curl -X POST http://localhost/api/simulados/1/questoes \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "questao_id": 15,
    "pontuacao": 1.5
  }'
```

### Exemplo 4: Remover Uma Questão

```bash
curl -X DELETE http://localhost/api/simulados/1/questoes/15 \
  -H "Authorization: Bearer {token}"
```

### Exemplo 5: Reordenar Questões (Drag & Drop)

```bash
curl -X PUT http://localhost/api/simulados/1/questoes/reordenar \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "questoes": [
      {"questao_id": 12, "ordem": 1},
      {"questao_id": 10, "ordem": 2},
      {"questao_id": 11, "ordem": 3}
    ]
  }'
```

---

## 🎨 Frontend - Fluxos de UX

### Fluxo 1: Editar Simulado (Modal/Página)

```javascript
// 1. Carregar simulado atual
const simulado = await api.get(`/simulados/${id}`);

// 2. Usuário edita campos
const dadosAtualizados = {
  titulo: "Novo título",
  descricao: "Nova descrição",
  tempo_limite: 120
};

// 3. Salvar alterações
await api.put(`/simulados/${id}`, dadosAtualizados);
```

### Fluxo 2: Gerenciar Questões (Lista Interativa)

```javascript
// Carregar simulado com questões
const simulado = await api.get(`/simulados/${id}`);
const questoes = simulado.data.questoes;

// Adicionar questão (botão + modal de seleção)
async function adicionarQuestao(questaoId) {
  await api.post(`/simulados/${id}/questoes`, {
    questao_id: questaoId,
    pontuacao: 1.0
  });
  recarregarSimulado();
}

// Remover questão (botão X na lista)
async function removerQuestao(questaoId) {
  if (confirm('Remover esta questão?')) {
    await api.delete(`/simulados/${id}/questoes/${questaoId}`);
    recarregarSimulado();
  }
}

// Reordenar questões (drag & drop)
async function salvarNovaOrdem(questoesReordenadas) {
  const payload = {
    questoes: questoesReordenadas.map((q, index) => ({
      questao_id: q.id,
      ordem: index + 1
    }))
  };
  
  await api.put(`/simulados/${id}/questoes/reordenar`, payload);
}
```

### Fluxo 3: Substituir Todas as Questões

```javascript
// Substituir completamente a lista de questões
async function substituirQuestoes(novasQuestoes) {
  await api.put(`/simulados/${id}`, {
    questoes: novasQuestoes.map((q, index) => ({
      questao_id: q.id,
      pontuacao: q.pontuacao || 1.0
    }))
  });
}
```

---

## 🔄 Comportamentos Importantes

### 1. Atualização Parcial vs Completa

```javascript
// ✅ Atualizar APENAS o título (questões não são tocadas)
PUT /api/simulados/1
{ "titulo": "Novo título" }

// ✅ Atualizar título E substituir questões
PUT /api/simulados/1
{
  "titulo": "Novo título",
  "questoes": [...]
}

// ⚠️ Enviar array vazio remove TODAS as questões!
PUT /api/simulados/1
{ "questoes": [] }
// Retorna erro: min:1
```

### 2. Reordenação Automática

```javascript
// Ao remover uma questão, as demais são reordenadas automaticamente
DELETE /api/simulados/1/questoes/5

// Antes: [1, 5, 7] (ordens: 1, 2, 3)
// Depois: [1, 7] (ordens: 1, 2) ← reordenado automaticamente
```

### 3. Validação de Propriedade

```javascript
// ❌ Tentando adicionar questão de outro usuário
POST /api/simulados/1/questoes
{ "questao_id": 999 } // Questão do usuário B

// Response: 403 Forbidden
{
  "success": false,
  "message": "Você só pode adicionar questões que você criou"
}
```

---

## 🧪 Testes

### Teste 1: Editar Informações Básicas

```php
$simulado = Simulado::factory()->create(['user_id' => $user->id]);

$response = $this->putJson("/api/simulados/{$simulado->id}", [
    'titulo' => 'Título Editado',
    'tempo_limite' => 90
]);

$response->assertStatus(200);
$this->assertEquals('Título Editado', $simulado->fresh()->titulo);
```

### Teste 2: Adicionar Questão

```php
$simulado = Simulado::factory()->create(['user_id' => $user->id]);
$questao = Questao::factory()->create(['user_id' => $user->id]);

$response = $this->postJson("/api/simulados/{$simulado->id}/questoes", [
    'questao_id' => $questao->id,
    'pontuacao' => 2.0
]);

$response->assertStatus(200);
$this->assertTrue($simulado->questoes->contains($questao));
```

### Teste 3: Remover Questão

```php
$simulado = Simulado::factory()->hasAttached($questao)->create();

$response = $this->deleteJson("/api/simulados/{$simulado->id}/questoes/{$questao->id}");

$response->assertStatus(200);
$this->assertFalse($simulado->fresh()->questoes->contains($questao));
```

### Teste 4: Não Pode Adicionar Questão de Outro Usuário

```php
$simulado = Simulado::factory()->create(['user_id' => $user1->id]);
$questaoOutro = Questao::factory()->create(['user_id' => $user2->id]);

$response = $this->postJson("/api/simulados/{$simulado->id}/questoes", [
    'questao_id' => $questaoOutro->id
]);

$response->assertStatus(403);
```

---

## 📊 Diagrama de Fluxo

```
┌─────────────────────────────────────────────┐
│         Editar Simulado                     │
└─────────────────┬───────────────────────────┘
                  │
        ┌─────────┴─────────┐
        │                   │
    ┌───▼───┐         ┌────▼─────┐
    │ Dados │         │ Questões │
    │Básicos│         │          │
    └───┬───┘         └────┬─────┘
        │                  │
        │      ┌───────────┴───────────┐
        │      │                       │
        │  ┌───▼────┐  ┌──────▼─────┐  │
        │  │Adicionar│  │Remover     │  │
        │  │Uma     │  │Uma         │  │
        │  └───┬────┘  └──────┬─────┘  │
        │      │              │        │
        │      │      ┌───────▼─────┐  │
        │      │      │Reordenar    │  │
        │      │      │(Drag&Drop)  │  │
        │      │      └───────┬─────┘  │
        │      │              │        │
        └──────┴──────────────┴────────┘
                      │
              ┌───────▼────────┐
              │ Validações:    │
              │ - Propriedade  │
              │ - Existência   │
              │ - Duplicação   │
              └───────┬────────┘
                      │
              ┌───────▼────────┐
              │ Salvar no DB   │
              └───────┬────────┘
                      │
              ┌───────▼────────┐
              │ Retornar       │
              │ Simulado       │
              │ Atualizado     │
              └────────────────┘
```

---

## ✅ Checklist de Implementação

- [x] Método `update()` com suporte a questões
- [x] Método `adicionarQuestao()`
- [x] Método `removerQuestao()`
- [x] Método `reordenarQuestoes()`
- [x] Validação de propriedade em todos os métodos
- [x] Validação de questões do usuário
- [x] Reordenação automática ao remover
- [x] Rotas adicionadas em `api.php`
- [x] Documentação completa
- [ ] Testes unitários
- [ ] Testes de integração
- [ ] Implementação no frontend

---

## 🚀 Status

**✅ BACKEND 100% IMPLEMENTADO**

- Data: 18/10/2025
- Versão: 2.2
- Status: Pronto para integração frontend

---

**Próximos Passos:**
1. Implementar interface de edição no frontend
2. Adicionar drag & drop para reordenação
3. Criar modal de seleção de questões
4. Testes E2E
