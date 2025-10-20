# Sistema de Questões Avulsas - Documentação Completa

## 📚 Visão Geral

Sistema para responder questões individualmente (sem simulado), com busca automática da próxima questão não respondida e geração sob demanda.

---

## 🔄 Fluxo Completo

### 1️⃣ Buscar Próxima Questão

**Endpoint:** `POST /api/questoes/proxima-questao`

**Descrição:** Busca a próxima questão não respondida com as configurações especificadas.

**Body:**
```json
#### Request
```json
{
  "tema_id": 5,
  "nivel": "medio",
  "tipo_questao": "concurso",      // opcional
  "tipo_questao_outro": "string",  // opcional
  "banca": "CESPE",                 // opcional
  "incluir_respondidas": false      // opcional - default: false
}
```

**Novo Parâmetro:**
- `incluir_respondidas` (boolean, opcional): Se `true`, permite buscar questões já respondidas para revisão. Útil quando o usuário quer treinar novamente questões que já respondeu.

#### Response (Success - 200)
```

**Parâmetros:**
- `tema_id` (obrigatório) - ID do tema
- `nivel` (obrigatório) - `facil`, `medio`, `dificil`, `muito_dificil`
- `tipo_questao` (opcional) - `concurso`, `enem`, `prova_crc`, `oab`, `outros`
- `tipo_questao_outro` (opcional) - Texto livre quando tipo = "outros"
- `banca` (opcional) - Nome da banca (ex: "CESPE", "FCC", "FGV")

**Resposta Sucesso (200):**
```json
```json
{
  "success": true,
  "data": {
    "questao": {
      "id": 42,
      "tema_id": 5,
      "enunciado": "Qual é a capital do Brasil?",
      "nivel": "facil",
      "tipo_questao": "concurso",
      "banca": "CESPE",
      "tema": {
        "id": 5,
        "nome": "Geografia"
      },
      "alternativas": [
        {
          "id": 101,
          "texto": "São Paulo",
          "correta": false
        },
        {
          "id": 102,
          "texto": "Brasília",
          "correta": true
        },
        {
          "id": 103,
          "texto": "Rio de Janeiro",
          "correta": false
        }
      ]
    },
    "total_disponiveis": 15,
    "total_respondidas": 5,
    "ja_respondida": false,
    "modo_revisao": false
  }
}
```

**Novos Campos na Resposta:**
- `ja_respondida` (boolean): Indica se esta questão específica já foi respondida pelo usuário
- `modo_revisao` (boolean): Indica se o modo revisão está ativo (`incluir_respondidas=true`)

#### Response (Questão já Respondida em Modo Revisão - 200)
```json
{
  "success": true,
  "data": {
    "questao": { /* questão que já foi respondida */ },
    "total_disponiveis": 20,
    "total_respondidas": 15,
    "ja_respondida": true,
    "modo_revisao": true
  }
}
```

#### Response (Sem Questões - 404)
```

#### Response (Sem Questões - 404)
```json
{
  "success": false,
  "message": "Não há mais questões não respondidas. Você pode ativar o modo revisão para responder questões novamente.",
  "data": {
    "questoes_acabaram": true,
    "total_respondidas": 20,
    "modo_revisao_ativo": false,
    "sugestao_modo_revisao": "Ative incluir_respondidas=true para revisar questões já respondidas",
    "desempenho": {
      "resumo": {
        "total_respostas": 25,
        "questoes_unicas": 20,
        "acertos": 18,
        "erros": 7,
        "percentual_acerto": 72,
        "tempo_medio_segundos": 45.5,
        "tempo_medio_formatado": "45s"
      },
      "sequencias": {
        "maior_sequencia_acertos": 10,
        "maior_sequencia_erros": 2,
        "sequencia_atual": 3
      },
      "ultima_resposta": {
        "correta": true,
        "data": "15/10/2025 14:30",
        "tempo_gasto": "42s"
      },
      "evolucao": {
        "percentual_inicio": 65,
        "percentual_recente": 80,
        "diferenca": 15,
        "melhorou": true,
        "mensagem": "Você melhorou 15% em relação ao início!"
      },
      "avaliacao": {
        "nivel": "Bom",
        "mensagem": "Você está no caminho certo!",
        "recomendacao": "Continue estudando e praticando para melhorar ainda mais."
      }
    },
    "sugestao_geracao": {
      "quantidade_sugerida": 5,
      "custo_creditos": 15,
      "mensagem": "Você pode gerar 5 novas questões por 15 créditos"
    }
  }
}
```

**Campos Adicionais quando Sem Questões:**
- `modo_revisao_ativo` (boolean): Indica se o modo revisão estava ativo
- `sugestao_modo_revisao` (string|null): Sugestão para ativar modo revisão (só aparece se não estava ativo)
- `desempenho` (object): Relatório completo de desempenho do usuário neste tema/nível

---

### 2️⃣ Responder Questão

**Endpoint:** `POST /api/questoes/{questao_id}/responder`

**Body:**
```json
{
  "alternativa_id": 456,
  "tempo_resposta": 45
}
```

**Resposta (200):**
```json
{
  "success": true,
  "message": "Resposta registrada com sucesso",
  "data": {
    "resposta_id": 789,
    "correta": true,
    "alternativa_correta_id": 456,
    "explicacao": "A capital do Brasil é Brasília...",
    "creditos_debitados": 1,
    "creditos_restantes": 99,
    "questao": { ... }
  }
}
```

**Custo:** 1 crédito por resposta

---

### 3️⃣ Gerar Mais Questões (Quando Acabarem)

**Endpoint:** `POST /api/questoes/gerar-mais-questoes`

**Descrição:** Gera novas questões quando o usuário solicitar explicitamente (clicou no botão).

**Body:**
```json
{
  "tema_id": 1,
  "nivel": "medio",
  "quantidade": 5,
  "tipo_questao": "concurso",
  "banca": "CESPE"
}
```

**Parâmetros:**
- `tema_id` (obrigatório)
- `nivel` (obrigatório)
- `quantidade` (obrigatório) - 1 a 10 questões
- `tipo_questao` (opcional)
- `tipo_questao_outro` (opcional)
- `banca` (opcional)

**Resposta Sucesso (200):**
```json
{
  "success": true,
  "message": "5 nova(s) questão(ões) gerada(s) com sucesso!",
  "data": {
    "questao": {
      "id": 124,
      "enunciado": "Nova questão gerada...",
      ...
    },
    "gerada_agora": true,
    "total_geradas": 5,
    "creditos_debitados": 15,
    "creditos_restantes": 84
  }
}
```

**Custo:** 3 créditos por questão gerada

---

### 4️⃣ Estatísticas de Questões Disponíveis

**Endpoint:** `POST /api/questoes/estatisticas-disponiveis`

**Body:**
```json
{
  "tema_id": 1,
  "nivel": "medio",
  "tipo_questao": "concurso"
}
```

**Resposta (200):**
```json
{
  "success": true,
  "data": {
    "tema_id": 1,
    "total_respondidas": 15,
    "por_nivel": {
      "facil": {
        "total": 20,
        "disponiveis": 10,
        "respondidas": 10,
        "percentual_completo": 50.00
      },
      "medio": {
        "total": 30,
        "disponiveis": 5,
        "respondidas": 25,
        "percentual_completo": 83.33
      },
      ...
    },
    "total_geral": {
      "total": 100,
      "disponiveis": 35,
      "respondidas": 65,
      "percentual_completo": 65.00
    }
  }
}
```

---

## 💻 Exemplo de Implementação JavaScript

```javascript
class SistemaQuestoesAvulsas {
  constructor(token) {
    this.token = token;
    this.baseUrl = '/api';
    this.configuracaoAtual = null;
  }

  // 1. Buscar próxima questão
  async buscarProximaQuestao(config) {
    this.configuracaoAtual = config;
    
    const response = await fetch(`${this.baseUrl}/questoes/proxima-questao`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${this.token}`
      },
      body: JSON.stringify(config)
    });

    const data = await response.json();

    if (response.status === 404 && data.data?.questoes_acabaram) {
      // Acabaram as questões - mostrar opção de gerar mais
      this.mostrarTelaGerarMais(data.data.sugestao_geracao);
      return null;
    }

    if (!response.ok) {
      throw new Error(data.message);
    }

    return data.data.questao;
  }

  // 2. Responder questão
  async responderQuestao(questaoId, alternativaId, tempoResposta = 0) {
    const response = await fetch(
      `${this.baseUrl}/questoes/${questaoId}/responder`,
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${this.token}`
        },
        body: JSON.stringify({
          alternativa_id: alternativaId,
          tempo_resposta: tempoResposta
        })
      }
    );

    const data = await response.json();

    if (response.status === 402) {
      alert('Créditos insuficientes!');
      window.location.href = '/comprar-creditos';
      return null;
    }

    if (!response.ok) {
      throw new Error(data.message);
    }

    // Mostrar feedback da resposta
    this.mostrarResultado(data.data);

    return data.data;
  }

  // 3. Gerar mais questões
  async gerarMaisQuestoes(quantidade = 5) {
    if (!this.configuracaoAtual) {
      throw new Error('Configure o sistema primeiro');
    }

    const response = await fetch(
      `${this.baseUrl}/questoes/gerar-mais-questoes`,
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${this.token}`
        },
        body: JSON.stringify({
          ...this.configuracaoAtual,
          quantidade
        })
      }
    );

    const data = await response.json();

    if (response.status === 402) {
      alert(`Créditos insuficientes! Necessário: ${data.data.creditos_necessarios}`);
      return null;
    }

    if (!response.ok) {
      throw new Error(data.message);
    }

    return data.data;
  }

  // 4. Fluxo completo
  async iniciarPratica(config) {
    try {
      // Buscar primeira questão
      const questao = await this.buscarProximaQuestao(config);
      
      if (!questao) return; // Questões acabaram

      this.mostrarQuestao(questao);
    } catch (error) {
      console.error('Erro:', error);
      alert(error.message);
    }
  }

  async aoResponder(questaoId, alternativaId, tempoResposta) {
    try {
      // Responder questão atual
      const resultado = await this.responderQuestao(
        questaoId, 
        alternativaId, 
        tempoResposta
      );

      if (!resultado) return;

      // Aguardar alguns segundos mostrando resultado
      await this.delay(3000);

      // Buscar próxima questão automaticamente
      const proximaQuestao = await this.buscarProximaQuestao(
        this.configuracaoAtual
      );

      if (proximaQuestao) {
        this.mostrarQuestao(proximaQuestao);
      }

    } catch (error) {
      console.error('Erro:', error);
      alert(error.message);
    }
  }

  async aoClicarGerarMais(quantidade) {
    try {
      const resultado = await this.gerarMaisQuestoes(quantidade);
      
      if (!resultado) return;

      alert(`${resultado.total_geradas} questões geradas! Créditos restantes: ${resultado.creditos_restantes}`);

      // Mostrar primeira questão gerada
      this.mostrarQuestao(resultado.questao);

    } catch (error) {
      console.error('Erro:', error);
      alert(error.message);
    }
  }

  // Helpers UI
  mostrarQuestao(questao) {
    // Implementar sua lógica de UI aqui
    console.log('Mostrar questão:', questao);
  }

  mostrarResultado(resultado) {
    if (resultado.correta) {
      alert('✅ Resposta correta!\n\n' + resultado.explicacao);
    } else {
      alert('❌ Resposta incorreta!\n\n' + resultado.explicacao);
    }
  }

  mostrarTelaGerarMais(sugestao) {
    const confirmar = confirm(
      `Não há mais questões disponíveis!\n\n${sugestao.mensagem}\n\nDeseja gerar mais questões?`
    );

    if (confirmar) {
      this.aoClicarGerarMais(sugestao.quantidade_sugerida);
    }
  }

  delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }
}

// USO:
const sistema = new SistemaQuestoesAvulsas(localStorage.getItem('token'));

// Iniciar prática
sistema.iniciarPratica({
  tema_id: 1,
  nivel: 'medio',
  tipo_questao: 'concurso',
  banca: 'CESPE'
});
```

---

## � Modo Revisão

### O que é?

O **Modo Revisão** permite que o usuário responda novamente questões que já foram respondidas anteriormente. É útil para:
- Treinar questões que errou
- Reforçar conhecimento em questões já vistas
- Praticar até memorizar as respostas corretas
- Medir evolução respondendo a mesma questão após estudar

### Como Ativar

Adicione o parâmetro `incluir_respondidas: true` na chamada de próxima questão:

```javascript
await fetch('/api/questoes/proxima-questao', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    tema_id: 5,
    nivel: 'medio',
    incluir_respondidas: true  // ✨ Ativa modo revisão
  })
});
```

### Comportamento

**Sem Modo Revisão (padrão):**
- Retorna apenas questões **não respondidas**
- Quando acabarem, sugere gerar mais ou ativar modo revisão
- `ja_respondida: false` sempre
- `modo_revisao: false`

**Com Modo Revisão:**
- Retorna **todas** as questões (respondidas e não respondidas)
- Permite responder a mesma questão múltiplas vezes
- `ja_respondida: true/false` indica status da questão retornada
- `modo_revisao: true`

### Exemplo de UX

```vue
<template>
  <div class="pratica-questoes">
    <!-- Toggle Modo Revisão -->
    <div class="controles">
      <label class="switch">
        <input 
          type="checkbox" 
          v-model="modoRevisao"
          @change="buscarProximaQuestao"
        >
        <span>Modo Revisão (incluir questões respondidas)</span>
      </label>
    </div>

    <!-- Badge Indicador -->
    <div v-if="questaoAtual.ja_respondida" class="badge badge-info">
      📚 Você já respondeu esta questão antes
    </div>

    <!-- Questão -->
    <div class="questao-card">
      <h3>{{ questaoAtual.enunciado }}</h3>
      <!-- alternativas -->
    </div>

    <!-- Info -->
    <div class="questoes-info">
      <span v-if="modoRevisao">
        Modo Revisão Ativo - {{ totalDisponiveis }} questões disponíveis
      </span>
      <span v-else>
        {{ totalDisponiveis }} questões não respondidas
      </span>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      modoRevisao: false,
      questaoAtual: null,
      totalDisponiveis: 0
    }
  },
  
  methods: {
    async buscarProximaQuestao() {
      const response = await fetch('/api/questoes/proxima-questao', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${this.token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          tema_id: this.temaId,
          nivel: this.nivel,
          incluir_respondidas: this.modoRevisao
        })
      });
      
      const data = await response.json();
      
      if (response.ok) {
        this.questaoAtual = data.data.questao;
        this.totalDisponiveis = data.data.total_disponiveis;
      } else if (response.status === 404) {
        // Sem questões disponíveis
        if (data.data.sugestao_modo_revisao && !this.modoRevisao) {
          // Sugerir ativar modo revisão
          this.mostrarDialogo({
            titulo: 'Questões Esgotadas',
            mensagem: 'Você já respondeu todas as questões disponíveis!',
            opcoes: [
              {
                texto: 'Ativar Modo Revisão',
                acao: () => {
                  this.modoRevisao = true;
                  this.buscarProximaQuestao();
                }
              },
              {
                texto: 'Gerar Novas Questões',
                acao: () => this.gerarMaisQuestoes()
              }
            ]
          });
        }
      }
    }
  }
}
</script>
```

### Casos de Uso

**1. Treino de Questões Difíceis**
```javascript
// Usuário quer treinar apenas questões que errou
// (Implementação futura: filtrar por questões erradas)
await fetch('/api/questoes/proxima-questao', {
  body: JSON.stringify({
    tema_id: 5,
    nivel: 'dificil',
    incluir_respondidas: true,
    // apenas_erradas: true  // Feature futura
  })
});
```

**2. Prática Antes de Prova**
```javascript
// Usuário quer revisar todas as questões de um tema específico
await fetch('/api/questoes/proxima-questao', {
  body: JSON.stringify({
    tema_id: 8,
    nivel: 'medio',
    banca: 'CESPE',
    incluir_respondidas: true  // Revisar tudo
  })
});
```

**3. Medição de Evolução**
```javascript
// Sistema pode comparar:
// - 1ª tentativa: 60% de acerto
// - 2ª tentativa: 85% de acerto
// - Evolução: +25%

// Buscar desempenho
await fetch('/api/questoes/desempenho', {
  body: JSON.stringify({
    tema_id: 5,
    nivel: 'medio'
  })
});
// Retorna: evolucao.diferenca: +25%
```

### Contadores e Estatísticas

Com modo revisão ativo:
- `total_disponiveis`: Conta **todas** as questões (respondidas + não respondidas)
- `total_respondidas`: Quantidade de questões únicas já respondidas
- `ja_respondida`: `true` se a questão retornada já foi respondida antes

Sem modo revisão:
- `total_disponiveis`: Apenas questões **não respondidas**
- `total_respondidas`: Total de questões já respondidas
- `ja_respondida`: Sempre `false`

### Observações Importantes

1. ✅ **Múltiplas Respostas**: O sistema permite responder a mesma questão quantas vezes quiser
2. ✅ **Custo por Resposta**: Cada resposta custa 1 crédito, mesmo em modo revisão
3. ✅ **Estatísticas**: O desempenho considera todas as tentativas
4. ✅ **Isolamento**: Cada usuário vê apenas suas próprias respostas
5. ✅ **Filtros Mantidos**: Modo revisão respeita tema, nível, tipo e banca

---

## �📊 Tabela de Custos

| Ação | Custo em Créditos |
|------|-------------------|
| Responder questão | 1 crédito |
| Gerar questão nova | 3 créditos |
| Gerar 5 questões | 15 créditos |

---

## ✅ Resumo do Fluxo

1. **Usuário clica "Começar Prática"**
   - Frontend chama `POST /questoes/proxima-questao`
   - Sistema retorna questão não respondida

2. **Usuário responde a questão**
   - Frontend chama `POST /questoes/{id}/responder`
   - Sistema debita 1 crédito
   - Mostra se acertou/errou + explicação

3. **Usuário clica "Próxima Questão"**
   - Frontend chama `POST /questoes/proxima-questao` novamente
   - Se houver questões: retorna próxima
   - Se acabaram: retorna erro 404 com sugestão

4. **Se questões acabaram**
   - Mostrar mensagem: "Acabaram as questões! Gerar mais?"
   - Se usuário clicar "Sim": `POST /questoes/gerar-mais-questoes`
   - Sistema gera novas questões e debita créditos
   - Retorna primeira questão gerada

---

## 🎯 Vantagens desta Abordagem

✅ **Controle total do usuário** - Só gera quando solicitado
✅ **Transparência** - Usuário sempre sabe quanto vai gastar
✅ **Experiência fluida** - Próxima questão automática enquanto houver
✅ **Economia** - Não gera questões desnecessariamente
✅ **Flexibilidade** - Usuário escolhe quantas gerar

---

**Data:** 20 de outubro de 2025
**Versão:** 1.0
