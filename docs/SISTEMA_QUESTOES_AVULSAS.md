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
{
  "tema_id": 1,
  "nivel": "medio",
  "tipo_questao": "concurso",
  "banca": "CESPE"
}
```

**Parâmetros:**
- `tema_id` (obrigatório) - ID do tema
- `nivel` (obrigatório) - `facil`, `medio`, `dificil`, `muito_dificil`
- `tipo_questao` (opcional) - `concurso`, `enem`, `prova_crc`, `oab`, `outros`
- `tipo_questao_outro` (opcional) - Texto livre quando tipo = "outros"
- `banca` (opcional) - Nome da banca (ex: "CESPE", "FCC", "FGV")

**Resposta Sucesso (200):**
```json
{
  "success": true,
  "data": {
    "questao": {
      "id": 123,
      "enunciado": "Qual é a capital do Brasil?",
      "nivel": "medio",
      "tema": { ... },
      "alternativas": [ ... ]
    },
    "total_disponiveis": 15,
    "total_respondidas": 5
  }
}
```

**Resposta Questões Acabaram (404):**
```json
{
  "success": false,
  "message": "Não há mais questões disponíveis com essas configurações.",
  "data": {
    "questoes_acabaram": true,
    "total_respondidas": 20,
    "sugestao_geracao": {
      "quantidade_sugerida": 5,
      "custo_creditos": 15,
      "mensagem": "Você pode gerar 5 novas questões por 15 créditos"
    }
  }
}
```

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

## 📊 Tabela de Custos

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
