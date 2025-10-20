# Indicador de Respostas em Listagem de Questões

## 📋 Visão Geral

A listagem de questões agora exibe informações sobre quais questões o usuário já respondeu, permitindo que o frontend mostre indicadores visuais de progresso.

## 🎯 Campos Adicionados

Cada questão na listagem agora inclui três novos campos:

```json
{
  "id": 123,
  "enunciado": "Qual é a capital do Brasil?",
  "nivel": "facil",
  // ... outros campos da questão
  
  // ✨ NOVOS CAMPOS
  "foi_respondida": true,
  "total_respostas": 3,
  "ultima_resposta": "2025-01-15T14:30:00.000000Z"
}
```

### Descrição dos Campos

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `foi_respondida` | `boolean` | `true` se o usuário já respondeu esta questão pelo menos uma vez, `false` caso contrário |
| `total_respostas` | `integer` | Número total de vezes que o usuário respondeu esta questão (permite re-tentativas) |
| `ultima_resposta` | `string\|null` | Timestamp ISO 8601 da última vez que respondeu, ou `null` se nunca respondeu |

## 📡 Endpoint

**GET** `/api/questoes`

### Headers
```
Authorization: Bearer {token}
Accept: application/json
```

### Query Parameters

Todos os filtros existentes continuam funcionando normalmente:
- `tema_id` - Filtrar por tema
- `nivel` - Filtrar por nível (facil, medio, dificil)
- `favoritas` - Filtrar apenas favoritas
- `search` - Buscar por texto no enunciado
- `per_page` - Itens por página (padrão: 15)
- `page` - Número da página

### Resposta de Sucesso

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 10,
      "tema_id": 5,
      "enunciado": "O que é Laravel?",
      "nivel": "medio",
      "favorita": false,
      "foi_respondida": true,
      "total_respostas": 2,
      "ultima_resposta": "2025-01-15T10:23:45.000000Z",
      "tema": {
        "id": 5,
        "nome": "PHP & Laravel"
      },
      "alternativas": [
        {
          "id": 1,
          "texto": "Um framework PHP",
          "correta": true
        },
        {
          "id": 2,
          "texto": "Uma linguagem de programação",
          "correta": false
        }
      ]
    },
    {
      "id": 2,
      "user_id": 10,
      "tema_id": 5,
      "enunciado": "O que é Eloquent?",
      "nivel": "facil",
      "favorita": true,
      "foi_respondida": false,
      "total_respostas": 0,
      "ultima_resposta": null,
      "tema": {
        "id": 5,
        "nome": "PHP & Laravel"
      },
      "alternativas": [...]
    }
  ],
  "meta": {
    "user_id": 10,
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 73
  }
}
```

## 💻 Exemplos de Implementação Frontend

### Vue.js

```vue
<template>
  <div class="questoes-lista">
    <div 
      v-for="questao in questoes" 
      :key="questao.id" 
      class="questao-card"
      :class="{ 'respondida': questao.foi_respondida }"
    >
      <!-- Badge de Status -->
      <div class="status-badges">
        <span 
          v-if="questao.foi_respondida" 
          class="badge badge-success"
        >
          ✓ Respondida {{ questao.total_respostas }}x
        </span>
        <span 
          v-else 
          class="badge badge-secondary"
        >
          Não respondida
        </span>
        
        <span 
          v-if="questao.favorita" 
          class="badge badge-warning"
        >
          ⭐ Favorita
        </span>
      </div>

      <!-- Conteúdo da Questão -->
      <h3>{{ questao.enunciado }}</h3>
      
      <!-- Info Adicional -->
      <div class="questao-meta">
        <span class="nivel">{{ questao.nivel }}</span>
        <span class="tema">{{ questao.tema.nome }}</span>
        <span v-if="questao.ultima_resposta" class="ultima-tentativa">
          Última tentativa: {{ formatarData(questao.ultima_resposta) }}
        </span>
      </div>

      <button @click="responderQuestao(questao.id)">
        {{ questao.foi_respondida ? 'Tentar Novamente' : 'Responder' }}
      </button>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      questoes: [],
      loading: false
    }
  },
  
  methods: {
    async carregarQuestoes(filtros = {}) {
      this.loading = true
      try {
        const params = new URLSearchParams(filtros)
        const response = await fetch(`/api/questoes?${params}`, {
          headers: {
            'Authorization': `Bearer ${this.token}`,
            'Accept': 'application/json'
          }
        })
        
        const data = await response.json()
        this.questoes = data.data
      } catch (error) {
        console.error('Erro ao carregar questões:', error)
      } finally {
        this.loading = false
      }
    },
    
    formatarData(timestamp) {
      return new Date(timestamp).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }
  },
  
  mounted() {
    this.carregarQuestoes()
  }
}
</script>

<style scoped>
.questao-card.respondida {
  border-left: 4px solid #10b981;
}

.badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  margin-right: 8px;
}

.badge-success {
  background: #d1fae5;
  color: #065f46;
}

.badge-secondary {
  background: #f3f4f6;
  color: #6b7280;
}
</style>
```

### React

```jsx
import React, { useState, useEffect } from 'react';
import { format } from 'date-fns';
import { ptBR } from 'date-fns/locale';

function QuestoesLista() {
  const [questoes, setQuestoes] = useState([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    carregarQuestoes();
  }, []);

  const carregarQuestoes = async (filtros = {}) => {
    setLoading(true);
    try {
      const params = new URLSearchParams(filtros);
      const response = await fetch(`/api/questoes?${params}`, {
        headers: {
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
          'Accept': 'application/json'
        }
      });
      
      const data = await response.json();
      setQuestoes(data.data);
    } catch (error) {
      console.error('Erro ao carregar questões:', error);
    } finally {
      setLoading(false);
    }
  };

  const formatarData = (timestamp) => {
    return format(new Date(timestamp), "dd/MM/yyyy 'às' HH:mm", {
      locale: ptBR
    });
  };

  return (
    <div className="questoes-lista">
      {questoes.map(questao => (
        <div 
          key={questao.id} 
          className={`questao-card ${questao.foi_respondida ? 'respondida' : ''}`}
        >
          <div className="status-badges">
            {questao.foi_respondida ? (
              <span className="badge badge-success">
                ✓ Respondida {questao.total_respostas}x
              </span>
            ) : (
              <span className="badge badge-secondary">
                Não respondida
              </span>
            )}
            
            {questao.favorita && (
              <span className="badge badge-warning">
                ⭐ Favorita
              </span>
            )}
          </div>

          <h3>{questao.enunciado}</h3>
          
          <div className="questao-meta">
            <span className="nivel">{questao.nivel}</span>
            <span className="tema">{questao.tema.nome}</span>
            {questao.ultima_resposta && (
              <span className="ultima-tentativa">
                Última tentativa: {formatarData(questao.ultima_resposta)}
              </span>
            )}
          </div>

          <button onClick={() => responderQuestao(questao.id)}>
            {questao.foi_respondida ? 'Tentar Novamente' : 'Responder'}
          </button>
        </div>
      ))}
    </div>
  );
}

export default QuestoesLista;
```

### JavaScript Vanilla

```javascript
class QuestoesLista {
  constructor(containerId, token) {
    this.container = document.getElementById(containerId);
    this.token = token;
    this.questoes = [];
  }

  async carregarQuestoes(filtros = {}) {
    try {
      const params = new URLSearchParams(filtros);
      const response = await fetch(`/api/questoes?${params}`, {
        headers: {
          'Authorization': `Bearer ${this.token}`,
          'Accept': 'application/json'
        }
      });

      const data = await response.json();
      this.questoes = data.data;
      this.renderizar();
    } catch (error) {
      console.error('Erro ao carregar questões:', error);
    }
  }

  renderizar() {
    this.container.innerHTML = this.questoes.map(questao => `
      <div class="questao-card ${questao.foi_respondida ? 'respondida' : ''}">
        <div class="status-badges">
          ${questao.foi_respondida 
            ? `<span class="badge badge-success">✓ Respondida ${questao.total_respostas}x</span>`
            : `<span class="badge badge-secondary">Não respondida</span>`
          }
          ${questao.favorita 
            ? `<span class="badge badge-warning">⭐ Favorita</span>`
            : ''
          }
        </div>

        <h3>${questao.enunciado}</h3>

        <div class="questao-meta">
          <span class="nivel">${questao.nivel}</span>
          <span class="tema">${questao.tema.nome}</span>
          ${questao.ultima_resposta 
            ? `<span class="ultima-tentativa">
                Última tentativa: ${this.formatarData(questao.ultima_resposta)}
              </span>`
            : ''
          }
        </div>

        <button onclick="responderQuestao(${questao.id})">
          ${questao.foi_respondida ? 'Tentar Novamente' : 'Responder'}
        </button>
      </div>
    `).join('');
  }

  formatarData(timestamp) {
    const data = new Date(timestamp);
    return data.toLocaleDateString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  }
}

// Uso
const lista = new QuestoesLista('questoes-container', userToken);
lista.carregarQuestoes();
```

## 🎨 Sugestões de UX

### Indicadores Visuais

1. **Badge de Status**
   - ✅ Verde com check para respondidas
   - ⚪ Cinza para não respondidas
   - 🔄 Amarelo para questões com múltiplas tentativas

2. **Borda Lateral**
   - Borda verde à esquerda para questões respondidas
   - Borda cinza para não respondidas

3. **Ícones**
   - ✓ Check mark para respondidas
   - 📝 Ícone de lápis para não respondidas
   - 🔁 Ícone de refresh para re-tentativas

### Filtros Adicionais (Sugestão)

Considere adicionar filtros para:
- Mostrar apenas respondidas
- Mostrar apenas não respondidas
- Mostrar questões com baixo desempenho (para revisão)

```javascript
// Exemplo de filtros
await carregarQuestoes({
  apenas_respondidas: true,    // Apenas questões já respondidas
  apenas_nao_respondidas: true // Apenas questões não respondidas
});
```

## 📊 Performance

### Otimizações Implementadas

1. **Query Eficiente**: Usa apenas uma query adicional com `GROUP BY` para buscar todas as respostas
2. **Carregamento em Lote**: Busca informações de resposta para todas as questões da página de uma vez
3. **Sem N+1**: Não há queries adicionais por questão

### Impacto

- **Queries por requisição**: 2 (uma para questões, uma para respostas)
- **Overhead de memória**: Mínimo (apenas IDs e contagens)
- **Tempo adicional**: ~5-10ms em média

## 🧪 Testes

A funcionalidade possui 7 testes automatizados cobrindo:

✅ Questões não respondidas mostram `foi_respondida: false`  
✅ Questões respondidas mostram `foi_respondida: true`  
✅ Múltiplas respostas contam corretamente  
✅ Listagem mista mostra status correto  
✅ Isolamento entre usuários  
✅ Funciona com paginação  
✅ Funciona com filtros  

Execute os testes:
```bash
php artisan test --filter=QuestaoIndicadorRespostaTest
```

## 🔄 Compatibilidade

- ✅ **Retrocompatível**: Campos adicionais não quebram clientes existentes
- ✅ **Filtros existentes**: Todos continuam funcionando normalmente
- ✅ **Paginação**: Funciona perfeitamente com a paginação
- ✅ **Autenticação**: Usa o mesmo sistema Sanctum existente

## 📝 Notas Importantes

1. **Re-tentativas Permitidas**: Um usuário pode responder a mesma questão múltiplas vezes (para treino)
2. **Última Resposta**: O campo `ultima_resposta` sempre mostra a tentativa mais recente
3. **Isolamento**: Cada usuário vê apenas o status das suas próprias respostas
4. **Performance**: A query é otimizada para não causar sobrecarga

## 🚀 Próximos Passos Sugeridos

1. **Filtro por Status**: Adicionar parâmetros `apenas_respondidas` e `apenas_nao_respondidas`
2. **Estatísticas de Desempenho**: Adicionar taxa de acerto por questão
3. **Ordenação por Status**: Permitir ordenar por "não respondidas primeiro"
4. **Badge de Performance**: Mostrar se acertou/errou na última tentativa
