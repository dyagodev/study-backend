# Sistema de Desempenho em Questões

## 📋 Visão Geral

Quando o usuário não pode mais responder questões (seja por falta de questões disponíveis ou créditos), o sistema agora exibe um relatório completo de desempenho, mostrando como ele se saiu até o momento.

## 🎯 Cenários de Exibição

O desempenho é exibido automaticamente em três cenários:

### 1. Questões Esgotadas
Quando não há mais questões disponíveis no tema/nível escolhido:
```
GET /api/questoes/proxima-questao
```

### 2. Créditos Insuficientes
Quando o usuário tenta gerar novas questões mas não tem créditos:
```
POST /api/questoes/gerar-mais-questoes
```

### 3. Consulta Manual
O usuário pode consultar seu desempenho a qualquer momento:
```
POST /api/questoes/desempenho
```

## 📡 API Endpoints

### 1. Próxima Questão (com desempenho quando esgotado)

**POST** `/api/questoes/proxima-questao`

#### Request
```json
{
  "tema_id": 5,
  "nivel": "medio",
  "tipo_questao": "concurso",
  "banca": "CESPE"
}
```

#### Response (Questões Esgotadas - 404)
```json
{
  "success": false,
  "message": "Não há mais questões disponíveis com essas configurações.",
  "data": {
    "questoes_acabaram": true,
    "total_respondidas": 15,
    "desempenho": {
      "resumo": {
        "total_respostas": 15,
        "questoes_unicas": 15,
        "acertos": 12,
        "erros": 3,
        "percentual_acerto": 80,
        "tempo_medio_segundos": 45.5,
        "tempo_medio_formatado": "45s"
      },
      "sequencias": {
        "maior_sequencia_acertos": 7,
        "maior_sequencia_erros": 2,
        "sequencia_atual": 3
      },
      "ultima_resposta": {
        "correta": true,
        "data": "15/10/2025 14:30",
        "tempo_gasto": "42s"
      },
      "evolucao": {
        "percentual_inicio": 60,
        "percentual_recente": 85,
        "diferenca": 25,
        "melhorou": true,
        "mensagem": "Você melhorou 25% em relação ao início!"
      },
      "avaliacao": {
        "nivel": "Muito Bom",
        "mensagem": "Ótimo trabalho! Você tem um bom domínio do conteúdo.",
        "recomendacao": "Foque nas questões que errou para alcançar a excelência."
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

### 2. Gerar Mais Questões (com desempenho se sem créditos)

**POST** `/api/questoes/gerar-mais-questoes`

#### Request
```json
{
  "tema_id": 5,
  "nivel": "dificil",
  "quantidade": 10
}
```

#### Response (Sem Créditos - 402)
```json
{
  "success": false,
  "message": "Créditos insuficientes para gerar 10 novas questões. Necessário: 30 créditos.",
  "data": {
    "creditos_necessarios": 30,
    "creditos_disponiveis": 5,
    "desempenho": {
      "resumo": {
        "total_respostas": 25,
        "questoes_unicas": 20,
        "acertos": 18,
        "erros": 7,
        "percentual_acerto": 72,
        "tempo_medio_segundos": 62.3,
        "tempo_medio_formatado": "1min 2s"
      },
      "sequencias": {
        "maior_sequencia_acertos": 10,
        "maior_sequencia_erros": 3,
        "sequencia_atual": 2
      },
      "ultima_resposta": {
        "correta": false,
        "data": "15/10/2025 16:45",
        "tempo_gasto": "1min 15s"
      },
      "evolucao": {
        "percentual_inicio": 55,
        "percentual_recente": 75,
        "diferenca": 20,
        "melhorou": true,
        "mensagem": "Você melhorou 20% em relação ao início!"
      },
      "avaliacao": {
        "nivel": "Bom",
        "mensagem": "Você está no caminho certo!",
        "recomendacao": "Continue estudando e praticando para melhorar ainda mais."
      }
    },
    "mensagem_motivacional": "Enquanto isso, veja como você se saiu nas questões que já respondeu!"
  }
}
```

### 3. Consultar Desempenho

**POST** `/api/questoes/desempenho`

#### Request
```json
{
  "tema_id": 5,
  "nivel": "medio",          // opcional
  "tipo_questao": "concurso", // opcional
  "banca": "CESPE"            // opcional
}
```

#### Response (Success - 200)
```json
{
  "success": true,
  "data": {
    "tema": {
      "id": 5,
      "nome": "Direito Constitucional"
    },
    "filtros": {
      "nivel": "medio",
      "tipo_questao": "concurso",
      "banca": "CESPE"
    },
    "desempenho": {
      "resumo": {
        "total_respostas": 50,
        "questoes_unicas": 45,
        "acertos": 40,
        "erros": 10,
        "percentual_acerto": 80,
        "tempo_medio_segundos": 55.2,
        "tempo_medio_formatado": "55s"
      },
      "sequencias": {
        "maior_sequencia_acertos": 15,
        "maior_sequencia_erros": 3,
        "sequencia_atual": 5
      },
      "ultima_resposta": {
        "correta": true,
        "data": "15/10/2025 18:20",
        "tempo_gasto": "48s"
      },
      "evolucao": {
        "percentual_inicio": 65,
        "percentual_recente": 90,
        "diferenca": 25,
        "melhorou": true,
        "mensagem": "Você melhorou 25% em relação ao início!"
      },
      "avaliacao": {
        "nivel": "Muito Bom",
        "mensagem": "Ótimo trabalho! Você tem um bom domínio do conteúdo.",
        "recomendacao": "Foque nas questões que errou para alcançar a excelência."
      }
    }
  }
}
```

#### Response (Sem Respostas - 200)
```json
{
  "success": true,
  "data": {
    "tema": {
      "id": 5,
      "nome": "Matemática Financeira"
    },
    "filtros": {
      "nivel": null,
      "tipo_questao": null,
      "banca": null
    },
    "desempenho": {
      "mensagem": "Você ainda não respondeu nenhuma questão com estas configurações.",
      "total_respostas": 0
    }
  }
}
```

## 📊 Estrutura do Desempenho

### Resumo
| Campo | Tipo | Descrição |
|-------|------|-----------|
| `total_respostas` | integer | Total de respostas dadas |
| `questoes_unicas` | integer | Número de questões diferentes respondidas |
| `acertos` | integer | Total de respostas corretas |
| `erros` | integer | Total de respostas incorretas |
| `percentual_acerto` | float | Percentual de acerto (0-100) |
| `tempo_medio_segundos` | float | Tempo médio em segundos |
| `tempo_medio_formatado` | string | Tempo formatado legível |

### Sequências
| Campo | Tipo | Descrição |
|-------|------|-----------|
| `maior_sequencia_acertos` | integer | Maior sequência consecutiva de acertos |
| `maior_sequencia_erros` | integer | Maior sequência consecutiva de erros |
| `sequencia_atual` | integer | Sequência atual de acertos |

### Última Resposta
| Campo | Tipo | Descrição |
|-------|------|-----------|
| `correta` | boolean | Se a última resposta foi correta |
| `data` | string | Data/hora formatada |
| `tempo_gasto` | string | Tempo gasto formatado |

### Evolução
*Aparece apenas com 10+ respostas*

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `percentual_inicio` | float | Percentual das primeiras 10 respostas |
| `percentual_recente` | float | Percentual das últimas 10 respostas |
| `diferenca` | float | Diferença percentual |
| `melhorou` | boolean | Se houve melhora |
| `mensagem` | string | Mensagem motivacional |

### Avaliação
| Campo | Tipo | Descrição |
|-------|------|-----------|
| `nivel` | string | Nível de desempenho |
| `mensagem` | string | Feedback sobre o desempenho |
| `recomendacao` | string | Sugestão de melhoria |

#### Níveis de Avaliação
- **Excelente** (≥ 90%): "Parabéns! Você domina este conteúdo!"
- **Muito Bom** (≥ 75%): "Ótimo trabalho! Você tem um bom domínio do conteúdo."
- **Bom** (≥ 60%): "Você está no caminho certo!"
- **Regular** (≥ 40%): "Você está aprendendo, mas precisa de mais prática."
- **Precisa Melhorar** (< 40%): "Este conteúdo precisa de mais atenção."

## 💻 Exemplos de Implementação

### Vue.js - Modal de Desempenho

```vue
<template>
  <div>
    <!-- Botão Próxima Questão -->
    <button @click="buscarProximaQuestao" class="btn-primary">
      Próxima Questão
    </button>

    <!-- Modal de Desempenho -->
    <div v-if="mostrarDesempenho" class="modal-overlay" @click="fecharModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>📊 Seu Desempenho</h2>
          <button @click="fecharModal" class="btn-close">×</button>
        </div>

        <div class="modal-body">
          <!-- Mensagem Principal -->
          <div class="alert" :class="alertClass">
            {{ desempenho.mensagem }}
          </div>

          <!-- Resumo -->
          <div class="stats-grid">
            <div class="stat-card">
              <div class="stat-value">{{ desempenho.resumo.total_respostas }}</div>
              <div class="stat-label">Questões Respondidas</div>
            </div>
            <div class="stat-card success">
              <div class="stat-value">{{ desempenho.resumo.acertos }}</div>
              <div class="stat-label">Acertos</div>
            </div>
            <div class="stat-card danger">
              <div class="stat-value">{{ desempenho.resumo.erros }}</div>
              <div class="stat-label">Erros</div>
            </div>
            <div class="stat-card primary">
              <div class="stat-value">{{ desempenho.resumo.percentual_acerto }}%</div>
              <div class="stat-label">Taxa de Acerto</div>
            </div>
          </div>

          <!-- Avaliação -->
          <div class="avaliacao-card" :class="avaliacaoClass">
            <h3>{{ desempenho.avaliacao.nivel }}</h3>
            <p>{{ desempenho.avaliacao.mensagem }}</p>
            <p class="recomendacao">💡 {{ desempenho.avaliacao.recomendacao }}</p>
          </div>

          <!-- Evolução (se disponível) -->
          <div v-if="desempenho.evolucao" class="evolucao-card">
            <h4>📈 Sua Evolução</h4>
            <div class="evolucao-bar">
              <div class="bar-segment inicio">
                <span>Início: {{ desempenho.evolucao.percentual_inicio }}%</span>
              </div>
              <div class="bar-segment recente">
                <span>Recente: {{ desempenho.evolucao.percentual_recente }}%</span>
              </div>
            </div>
            <p class="evolucao-mensagem" :class="{ positiva: desempenho.evolucao.melhorou }">
              {{ desempenho.evolucao.mensagem }}
            </p>
          </div>

          <!-- Sequências -->
          <div class="sequencias-row">
            <div class="sequencia-item">
              <span class="icon">🔥</span>
              <span class="valor">{{ desempenho.sequencias.maior_sequencia_acertos }}</span>
              <span class="label">Maior Sequência</span>
            </div>
            <div class="sequencia-item">
              <span class="icon">⚡</span>
              <span class="valor">{{ desempenho.sequencias.sequencia_atual }}</span>
              <span class="label">Sequência Atual</span>
            </div>
            <div class="sequencia-item">
              <span class="icon">⏱️</span>
              <span class="valor">{{ desempenho.resumo.tempo_medio_formatado }}</span>
              <span class="label">Tempo Médio</span>
            </div>
          </div>

          <!-- Sugestão de Geração (se aplicável) -->
          <div v-if="sugestaoGeracao" class="sugestao-card">
            <h4>💡 Quer Continuar Praticando?</h4>
            <p>{{ sugestaoGeracao.mensagem }}</p>
            <button @click="gerarMaisQuestoes" class="btn-generate">
              Gerar {{ sugestaoGeracao.quantidade_sugerida }} Questões
              ({{ sugestaoGeracao.custo_creditos }} créditos)
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      mostrarDesempenho: false,
      desempenho: null,
      sugestaoGeracao: null,
      mensagemTipo: 'info',
      filtros: {
        tema_id: null,
        nivel: null,
        tipo_questao: null,
        banca: null
      }
    }
  },
  
  computed: {
    alertClass() {
      if (this.desempenho?.resumo?.percentual_acerto >= 80) return 'alert-success'
      if (this.desempenho?.resumo?.percentual_acerto >= 60) return 'alert-info'
      return 'alert-warning'
    },
    
    avaliacaoClass() {
      const nivel = this.desempenho?.avaliacao?.nivel
      if (nivel === 'Excelente' || nivel === 'Muito Bom') return 'nivel-alto'
      if (nivel === 'Bom') return 'nivel-medio'
      return 'nivel-baixo'
    }
  },
  
  methods: {
    async buscarProximaQuestao() {
      try {
        const response = await fetch('/api/questoes/proxima-questao', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${this.token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(this.filtros)
        })
        
        const data = await response.json()
        
        if (response.ok) {
          // Mostrar questão
          this.mostrarQuestao(data.data.questao)
        } else if (response.status === 404) {
          // Questões acabaram - mostrar desempenho
          this.desempenho = data.data.desempenho
          this.sugestaoGeracao = data.data.sugestao_geracao
          this.mostrarDesempenho = true
        }
      } catch (error) {
        console.error('Erro ao buscar questão:', error)
      }
    },
    
    async gerarMaisQuestoes() {
      try {
        const response = await fetch('/api/questoes/gerar-mais-questoes', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${this.token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            ...this.filtros,
            quantidade: this.sugestaoGeracao.quantidade_sugerida
          })
        })
        
        const data = await response.json()
        
        if (response.ok) {
          // Questões geradas com sucesso
          this.fecharModal()
          this.mostrarQuestao(data.data.questao)
        } else if (response.status === 402) {
          // Sem créditos - atualizar desempenho
          this.desempenho = data.data.desempenho
          alert(data.message)
        }
      } catch (error) {
        console.error('Erro ao gerar questões:', error)
      }
    },
    
    async consultarDesempenho() {
      try {
        const response = await fetch('/api/questoes/desempenho', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${this.token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(this.filtros)
        })
        
        const data = await response.json()
        
        if (response.ok) {
          this.desempenho = data.data.desempenho
          this.mostrarDesempenho = true
        }
      } catch (error) {
        console.error('Erro ao consultar desempenho:', error)
      }
    },
    
    fecharModal() {
      this.mostrarDesempenho = false
    }
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 12px;
  max-width: 700px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
}

.modal-header {
  padding: 24px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.modal-body {
  padding: 24px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 16px;
  margin: 24px 0;
}

.stat-card {
  background: #f9fafb;
  padding: 20px;
  border-radius: 8px;
  text-align: center;
}

.stat-card.success { border-left: 4px solid #10b981; }
.stat-card.danger { border-left: 4px solid #ef4444; }
.stat-card.primary { border-left: 4px solid #3b82f6; }

.stat-value {
  font-size: 32px;
  font-weight: bold;
  color: #111827;
}

.stat-label {
  font-size: 14px;
  color: #6b7280;
  margin-top: 4px;
}

.avaliacao-card {
  padding: 20px;
  border-radius: 8px;
  margin: 24px 0;
}

.avaliacao-card.nivel-alto { background: #d1fae5; color: #065f46; }
.avaliacao-card.nivel-medio { background: #dbeafe; color: #1e40af; }
.avaliacao-card.nivel-baixo { background: #fee2e2; color: #991b1b; }

.evolucao-mensagem.positiva {
  color: #10b981;
  font-weight: 600;
}

.sequencias-row {
  display: flex;
  justify-content: space-around;
  margin: 24px 0;
}

.sequencia-item {
  text-align: center;
}

.sequencia-item .icon {
  font-size: 32px;
  display: block;
  margin-bottom: 8px;
}

.sequencia-item .valor {
  font-size: 24px;
  font-weight: bold;
  display: block;
  color: #111827;
}

.sequencia-item .label {
  font-size: 12px;
  color: #6b7280;
}

.sugestao-card {
  background: #f3f4f6;
  padding: 20px;
  border-radius: 8px;
  margin-top: 24px;
  text-align: center;
}

.btn-generate {
  background: #3b82f6;
  color: white;
  padding: 12px 24px;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  margin-top: 12px;
}

.btn-generate:hover {
  background: #2563eb;
}
</style>
```

## 🧪 Testes

A funcionalidade possui 11 testes automatizados cobrindo:

✅ Exibe desempenho quando questões esgotadas  
✅ Calcula percentual de acerto corretamente  
✅ Calcula maior sequência de acertos  
✅ Mostra evolução com 10+ respostas  
✅ Avalia como "Excelente" quando ≥90%  
✅ Avalia como "Precisa Melhorar" quando <40%  
✅ Mostra desempenho quando sem créditos  
✅ Calcula tempo médio de resposta  
✅ Respeita isolamento entre usuários  
✅ Mensagem adequada quando sem respostas  
✅ Filtra desempenho por nível  

Execute os testes:
```bash
php artisan test --filter=DesempenhoQuestoesTest
```

## 🎨 Sugestões de UX

### 1. Gamificação
- Badges por conquistas (sequência de 10 acertos, 90%+ acerto, etc)
- Gráfico de progresso visual
- Comparação com média geral

### 2. Compartilhamento
- Permitir compartilhar conquistas nas redes sociais
- "Acertei 95% em Direito Constitucional! 🎉"

### 3. Análise Detalhada
- Botão "Ver questões que errei"
- Gráfico de evolução temporal
- Comparar diferentes temas

### 4. Motivação
- Mensagens personalizadas baseadas no desempenho
- Sugestões de estudo baseadas nos erros
- Metas e objetivos

## 📝 Notas Importantes

1. **Evolução**: Só aparece com 10+ respostas (compara primeiras 10 vs últimas 10)
2. **Isolamento**: Cada usuário vê apenas seu próprio desempenho
3. **Filtros**: Respeita todos os filtros (tema, nível, tipo, banca)
4. **Re-tentativas**: Conta todas as respostas, mesmo da mesma questão
5. **Performance**: Query otimizada, não causa sobrecarga

## 🚀 Melhorias Futuras

- [ ] Gráfico de evolução ao longo do tempo
- [ ] Comparação com outros usuários (ranking)
- [ ] Análise por assunto específico dentro do tema
- [ ] Previsão de desempenho em prova real
- [ ] Recomendações de estudo personalizadas
- [ ] Export de relatório em PDF
