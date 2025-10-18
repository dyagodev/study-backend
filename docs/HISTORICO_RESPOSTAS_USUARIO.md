# Histórico de Respostas de Usuário - Simulados

## Visão Geral

Este documento explica como visualizar o histórico completo de respostas de um usuário para simulados, incluindo todas as tentativas e detalhes de cada resposta individual.

## Endpoints Disponíveis

### 1. Histórico de Tentativas
**Endpoint:** `GET /api/simulados/{simulado_id}/historico`

**Descrição:** Retorna todas as tentativas que um usuário fez em um simulado específico, incluindo estatísticas gerais.

**Autenticação:** Bearer Token (Sanctum)

**Resposta de Sucesso (200):**
```json
{
  "success": true,
  "data": {
    "simulado": {
      "id": 1,
      "titulo": "Simulado de Matemática",
      "descricao": "Teste seus conhecimentos"
    },
    "total_tentativas": 3,
    "estatisticas_gerais": {
      "melhor_percentual": 85.50,
      "melhor_tentativa_numero": 2,
      "media_percentual": 75.33,
      "tempo_medio": 1800.50
    },
    "tentativas": [
      {
        "tentativa_id": 15,
        "numero_tentativa": 3,
        "data_inicio": "2025-01-17 10:30:00",
        "data_fim": "2025-01-17 11:00:00",
        "total_questoes": 20,
        "acertos": 16,
        "erros": 4,
        "percentual_acerto": 80.00,
        "tempo_total": 1800
      },
      {
        "tentativa_id": 12,
        "numero_tentativa": 2,
        "data_inicio": "2025-01-15 14:00:00",
        "data_fim": "2025-01-15 14:28:30",
        "total_questoes": 20,
        "acertos": 17,
        "erros": 3,
        "percentual_acerto": 85.00,
        "tempo_total": 1710
      }
    ]
  }
}
```

**Resposta de Erro (404):**
```json
{
  "success": false,
  "message": "Você ainda não respondeu este simulado"
}
```

**Observações:**
- Tentativas ordenadas da mais recente para a mais antiga
- Tempo em segundos
- Percentual de acerto com 2 casas decimais

---

### 2. Detalhes de uma Tentativa
**Endpoint:** `GET /api/simulados/{simulado_id}/tentativas/{tentativa_id}`

**Descrição:** Retorna todos os detalhes de uma tentativa específica, incluindo cada questão e resposta do usuário.

**Autenticação:** Bearer Token (Sanctum)

**Resposta de Sucesso (200):**
```json
{
  "success": true,
  "data": {
    "simulado": {
      "id": 1,
      "titulo": "Simulado de Matemática",
      "descricao": "Teste seus conhecimentos",
      "mostrar_gabarito": true
    },
    "tentativa": {
      "id": 15,
      "numero": 3,
      "data_inicio": "2025-01-17 10:30:00",
      "data_fim": "2025-01-17 11:00:00",
      "tempo_total": 1800
    },
    "estatisticas": {
      "total_questoes": 20,
      "acertos": 16,
      "erros": 4,
      "percentual_acerto": 80.00
    },
    "respostas": [
      {
        "questao_id": 45,
        "questao_enunciado": "Qual é o resultado de 2 + 2?",
        "alternativa_escolhida_id": 180,
        "alternativa_escolhida": "4",
        "correta": true,
        "alternativa_correta_id": 180,
        "alternativa_correta": "4",
        "explicacao": "A soma de 2 + 2 é igual a 4",
        "tempo_resposta": 15
      },
      {
        "questao_id": 46,
        "questao_enunciado": "Qual é a raiz quadrada de 16?",
        "alternativa_escolhida_id": 185,
        "alternativa_escolhida": "5",
        "correta": false,
        "alternativa_correta_id": 184,
        "alternativa_correta": "4",
        "explicacao": "√16 = 4, pois 4 × 4 = 16",
        "tempo_resposta": 22
      }
    ]
  }
}
```

**Resposta de Erro (404):**
```json
{
  "success": false,
  "message": "Tentativa não encontrada"
}
```

**Observações sobre Gabarito:**
- Se `mostrar_gabarito = true`: retorna alternativa correta e explicação
- Se `mostrar_gabarito = false`: campos `alternativa_correta_id`, `alternativa_correta` e `explicacao` são `null`
- Respostas ordenadas por ordem de resposta (created_at)
- `tempo_resposta` em segundos

---

## Como Usar (Fluxo Completo)

### Passo 1: Listar Tentativas do Usuário
```javascript
// Usando fetch API
const response = await fetch('/api/simulados/1/historico', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
});

const data = await response.json();
console.log(`Total de tentativas: ${data.data.total_tentativas}`);
console.log(`Melhor resultado: ${data.data.estatisticas_gerais.melhor_percentual}%`);
```

### Passo 2: Ver Detalhes de uma Tentativa Específica
```javascript
// Usando a tentativa_id obtida no passo 1
const tentativaId = data.data.tentativas[0].tentativa_id;

const responseDetalhe = await fetch(`/api/simulados/1/tentativas/${tentativaId}`, {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
});

const detalhe = await responseDetalhe.json();
console.log('Respostas:', detalhe.data.respostas);
```

---

## Exemplo de Interface

### Card de Histórico (Lista)
```html
<div class="tentativa-card">
  <h3>Tentativa #3</h3>
  <p>Data: 17/01/2025 às 10:30</p>
  <div class="stats">
    <span class="acertos">✓ 16 acertos</span>
    <span class="erros">✗ 4 erros</span>
    <span class="percentual">📊 80%</span>
    <span class="tempo">⏱️ 30min</span>
  </div>
  <button onclick="verDetalhes(15)">Ver Detalhes</button>
</div>
```

### Tela de Detalhes da Tentativa
```html
<div class="questao" data-questao-id="45">
  <div class="enunciado">
    <p>Questão 1: Qual é o resultado de 2 + 2?</p>
  </div>
  
  <div class="resposta-usuario correta">
    <span class="icone">✓</span>
    <p><strong>Sua resposta:</strong> 4</p>
  </div>
  
  <div class="explicacao">
    <p><strong>Explicação:</strong> A soma de 2 + 2 é igual a 4</p>
  </div>
  
  <div class="tempo">
    <small>Tempo de resposta: 15s</small>
  </div>
</div>
```

---

## Segurança

### Validações Implementadas:
- ✅ Usuário só pode ver suas próprias tentativas
- ✅ Tentativa deve pertencer ao simulado solicitado
- ✅ Gabarito controlado pela flag `mostrar_gabarito` do simulado
- ✅ Autenticação via Sanctum obrigatória

### Regras de Negócio:
1. Um usuário pode fazer múltiplas tentativas no mesmo simulado
2. Cada tentativa é numerada sequencialmente (`numero_tentativa`)
3. Gabarito só é exibido se `mostrar_gabarito = true`
4. Respostas mantêm a ordem cronológica de resposta

---

## Modelos Relacionados

### SimuladoTentativa
- `id`: ID único da tentativa
- `simulado_id`: ID do simulado
- `user_id`: ID do usuário
- `numero_tentativa`: Número sequencial da tentativa
- `data_inicio`: Data/hora de início
- `data_fim`: Data/hora de conclusão
- `total_questoes`: Total de questões
- `acertos`: Número de acertos
- `erros`: Número de erros
- `percentual_acerto`: Percentual de acertos
- `tempo_total`: Tempo total em segundos

### RespostaUsuario
- `id`: ID único da resposta
- `tentativa_id`: ID da tentativa
- `questao_id`: ID da questão
- `alternativa_id`: ID da alternativa escolhida
- `correta`: Boolean (true/false)
- `tempo_resposta`: Tempo em segundos

---

## Rotas API

Certifique-se de que as rotas estão definidas em `routes/api.php`:

```php
Route::middleware('auth:sanctum')->group(function () {
    // Histórico de tentativas do usuário em um simulado
    Route::get('simulados/{simulado}/historico', [SimuladoController::class, 'historico']);
    
    // Detalhes de uma tentativa específica
    Route::get('simulados/{simulado}/tentativas/{tentativa}', [SimuladoController::class, 'detalheTentativa']);
});
```

---

## Casos de Uso

### 1. Dashboard do Aluno
- Exibir últimas tentativas em todos os simulados
- Mostrar evolução de desempenho
- Comparar tentativas (primeira vs última)

### 2. Revisão de Simulado
- Estudante revê questões que errou
- Analisa tempo gasto em cada questão
- Estuda explicações das respostas corretas

### 3. Estatísticas de Progresso
- Calcular média geral de acertos
- Identificar pontos fracos (questões mais erradas)
- Gerar relatórios de desempenho

---

## Exemplo de Integração Vue.js

```vue
<template>
  <div class="historico-simulado">
    <h2>Histórico do Simulado</h2>
    
    <!-- Estatísticas Gerais -->
    <div class="stats-card" v-if="historico">
      <p>Total de Tentativas: {{ historico.total_tentativas }}</p>
      <p>Melhor Resultado: {{ historico.estatisticas_gerais.melhor_percentual }}%</p>
      <p>Média: {{ historico.estatisticas_gerais.media_percentual }}%</p>
    </div>
    
    <!-- Lista de Tentativas -->
    <div class="tentativas-list">
      <div 
        v-for="tentativa in historico.tentativas" 
        :key="tentativa.tentativa_id"
        class="tentativa-item"
        @click="verDetalhes(tentativa.tentativa_id)"
      >
        <h3>Tentativa #{{ tentativa.numero_tentativa }}</h3>
        <p>{{ formatarData(tentativa.data_inicio) }}</p>
        <div class="stats">
          <span>Acertos: {{ tentativa.acertos }}/{{ tentativa.total_questoes }}</span>
          <span class="badge">{{ tentativa.percentual_acerto }}%</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      historico: null,
      simuladoId: 1
    }
  },
  
  mounted() {
    this.carregarHistorico()
  },
  
  methods: {
    async carregarHistorico() {
      try {
        const response = await fetch(`/api/simulados/${this.simuladoId}/historico`, {
          headers: {
            'Authorization': `Bearer ${this.token}`,
            'Accept': 'application/json'
          }
        })
        
        const data = await response.json()
        this.historico = data.data
      } catch (error) {
        console.error('Erro ao carregar histórico:', error)
      }
    },
    
    verDetalhes(tentativaId) {
      this.$router.push({
        name: 'DetalhesTentativa',
        params: { 
          simuladoId: this.simuladoId,
          tentativaId: tentativaId 
        }
      })
    },
    
    formatarData(data) {
      return new Date(data).toLocaleString('pt-BR')
    }
  }
}
</script>
```

---

## Testes

### Teste Manual via Postman/cURL:

```bash
# 1. Obter token de autenticação
TOKEN="seu-token-aqui"

# 2. Listar histórico de tentativas
curl -X GET "http://localhost/api/simulados/1/historico" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# 3. Ver detalhes de tentativa específica
curl -X GET "http://localhost/api/simulados/1/tentativas/15" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

---

## Troubleshooting

### Problema: "Tentativa não encontrada"
**Causa:** Tentativa não pertence ao usuário ou simulado incorreto  
**Solução:** Verificar que o usuário autenticado é o dono da tentativa

### Problema: Gabarito não aparece
**Causa:** `mostrar_gabarito = false` no simulado  
**Solução:** Atualizar configuração do simulado no banco de dados

### Problema: "Você ainda não respondeu este simulado"
**Causa:** Usuário não possui tentativas registradas  
**Solução:** Usuário precisa completar ao menos uma tentativa primeiro

---

## Próximos Passos

1. Implementar filtros de data no histórico
2. Adicionar paginação para tentativas (>10)
3. Criar endpoint para comparar duas tentativas
4. Gerar relatórios PDF de desempenho
5. Implementar sistema de medalhas/conquistas

---

**Data de Criação:** 17/01/2025  
**Última Atualização:** 17/01/2025  
**Autor:** Sistema de Documentação
