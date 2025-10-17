# API de Estatísticas - Documentação

## Endpoints Disponíveis

### 1. Dashboard Geral
**GET** `/api/estatisticas/dashboard`

Retorna um resumo geral das estatísticas do usuário.

**Headers:**
```
Authorization: Bearer {token}
```

**Response 200:**
```json
{
  "success": true,
  "data": {
    "resumo": {
      "total_questoes_respondidas": 150,
      "total_acertos": 105,
      "total_erros": 45,
      "percentual_acerto": 70.00,
      "total_simulados": 8,
      "tempo_medio_resposta": 45.30,
      "sequencia_acertos": 5
    },
    "melhor_simulado": {
      "simulado_id": 3,
      "simulado_titulo": "Direito Constitucional - Básico",
      "percentual_acerto": 95.00,
      "acertos": 19,
      "total_questoes": 20
    },
    "ultimo_simulado": {
      "simulado_id": 5,
      "simulado_titulo": "Matemática Avançada",
      "data": "2025-10-17 13:30:00",
      "acertos": 15,
      "total_questoes": 20,
      "percentual_acerto": 75.00
    },
    "evolucao_7_dias": [
      {
        "data": "2025-10-11",
        "total_questoes": 20,
        "acertos": 14,
        "percentual_acerto": 70.00
      },
      {
        "data": "2025-10-12",
        "total_questoes": 15,
        "acertos": 12,
        "percentual_acerto": 80.00
      }
      // ... mais dias
    ]
  }
}
```

---

### 2. Desempenho por Tema
**GET** `/api/estatisticas/desempenho-por-tema`

Mostra o desempenho do usuário em cada tema, incluindo pontos fortes e fracos.

**Headers:**
```
Authorization: Bearer {token}
```

**Response 200:**
```json
{
  "success": true,
  "data": {
    "desempenho_por_tema": [
      {
        "tema_id": 1,
        "tema_nome": "Direito Constitucional",
        "total_questoes": 50,
        "total_acertos": 40,
        "total_erros": 10,
        "percentual_acerto": 80.00,
        "tempo_medio": 42.50
      },
      {
        "tema_id": 2,
        "tema_nome": "Matemática",
        "total_questoes": 40,
        "total_acertos": 25,
        "total_erros": 15,
        "percentual_acerto": 62.50,
        "tempo_medio": 55.30
      }
      // ... mais temas
    ],
    "pontos_fortes": [
      {
        "tema_id": 1,
        "tema_nome": "Direito Constitucional",
        "percentual_acerto": 80.00
      }
    ],
    "pontos_fracos": [
      {
        "tema_id": 2,
        "tema_nome": "Matemática",
        "percentual_acerto": 62.50
      }
    ]
  }
}
```

---

### 3. Evolução Temporal
**GET** `/api/estatisticas/evolucao-temporal?periodo={periodo}`

Mostra a evolução do desempenho ao longo do tempo com gráfico e tendências.

**Headers:**
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `periodo` (opcional): `7dias`, `30dias`, `90dias`, `ano` (padrão: `30dias`)

**Response 200:**
```json
{
  "success": true,
  "data": {
    "periodo": "30dias",
    "data_inicial": "2025-09-17",
    "evolucao": [
      {
        "data": "2025-09-17",
        "total_questoes": 10,
        "acertos": 7,
        "erros": 3,
        "percentual_acerto": 70.00
      },
      {
        "data": "2025-09-18",
        "total_questoes": 15,
        "acertos": 12,
        "erros": 3,
        "percentual_acerto": 80.00
      }
      // ... mais datas
    ],
    "media_movel_7_dias": [70.00, 75.00, 78.50, 80.00],
    "tendencia": "crescente"
  }
}
```

**Valores de tendência:**
- `crescente`: Desempenho melhorando (diferença > 5%)
- `decrescente`: Desempenho piorando (diferença < -5%)
- `estavel`: Desempenho estável

---

### 4. Estatísticas de Simulados
**GET** `/api/estatisticas/simulados`

Retorna estatísticas detalhadas de todos os simulados realizados.

**Headers:**
```
Authorization: Bearer {token}
```

**Response 200:**
```json
{
  "success": true,
  "data": {
    "total_simulados_realizados": 5,
    "simulados": [
      {
        "simulado_id": 3,
        "simulado_titulo": "Direito Constitucional - Básico",
        "total_questoes": 20,
        "acertos": 18,
        "erros": 2,
        "percentual_acerto": 90.00,
        "tempo_medio_resposta": 38.50,
        "total_tentativas": 3,
        "ultima_tentativa": "2025-10-15 14:30:00"
      },
      {
        "simulado_id": 5,
        "simulado_titulo": "Matemática Avançada",
        "total_questoes": 20,
        "acertos": 15,
        "erros": 5,
        "percentual_acerto": 75.00,
        "tempo_medio_resposta": 52.30,
        "total_tentativas": 2,
        "ultima_tentativa": "2025-10-17 13:30:00"
      }
      // ... mais simulados (ordenados por percentual de acerto)
    ],
    "media_geral": 82.50
  }
}
```

---

## Exemplos de Uso

### Exemplo 1: Dashboard (cURL)
```bash
curl -X GET "http://localhost/api/estatisticas/dashboard" \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "Accept: application/json"
```

### Exemplo 2: Desempenho por Tema (JavaScript/Fetch)
```javascript
fetch('http://localhost/api/estatisticas/desempenho-por-tema', {
  method: 'GET',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Accept': 'application/json'
  }
})
.then(response => response.json())
.then(data => {
  console.log('Pontos Fortes:', data.data.pontos_fortes);
  console.log('Pontos Fracos:', data.data.pontos_fracos);
});
```

### Exemplo 3: Evolução Temporal - Últimos 90 dias (cURL)
```bash
curl -X GET "http://localhost/api/estatisticas/evolucao-temporal?periodo=90dias" \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "Accept: application/json"
```

### Exemplo 4: Estatísticas de Simulados (Python/Requests)
```python
import requests

headers = {
    'Authorization': f'Bearer {token}',
    'Accept': 'application/json'
}

response = requests.get(
    'http://localhost/api/estatisticas/simulados',
    headers=headers
)

data = response.json()
print(f"Média Geral: {data['data']['media_geral']}%")
```

---

## Métricas Calculadas

### Sequência de Acertos
Quantidade de questões consecutivas acertadas (últimas 100 respostas).

### Tempo Médio de Resposta
Média de tempo (em segundos) que o usuário leva para responder uma questão.

### Percentual de Acerto
Calculado como: `(total_acertos / total_questoes) * 100`

### Média Móvel (7 dias)
Média do percentual de acerto considerando os últimos 7 dias de cada ponto no gráfico.

### Tendência
- **Crescente**: Média dos últimos 30% > Média dos primeiros 30% (diferença > 5%)
- **Decrescente**: Média dos últimos 30% < Média dos primeiros 30% (diferença < -5%)
- **Estável**: Diferença entre -5% e +5%

---

## Casos de Uso Frontend

### Dashboard Principal
Use o endpoint `/dashboard` para exibir:
- Cards com totais (questões, acertos, simulados)
- Percentual de acerto geral
- Melhor e último simulado
- Gráfico de evolução dos últimos 7 dias

### Página de Análise
Use `/desempenho-por-tema` para:
- Gráfico de pizza ou barras por tema
- Lista de pontos fortes (celebrar!)
- Lista de pontos fracos (sugerir estudo)

### Gráfico de Progresso
Use `/evolucao-temporal` para:
- Gráfico de linha com evolução
- Linha de média móvel
- Indicador de tendência
- Filtros de período (7, 30, 90 dias, ano)

### Histórico de Simulados
Use `/simulados` para:
- Tabela com todos os simulados
- Ordenação por percentual
- Indicadores de tentativas
- Opção de refazer simulados com baixo desempenho

---

## Notas Importantes

1. **Autenticação**: Todos os endpoints requerem autenticação via Bearer Token (Sanctum)

2. **Filtro por Tentativa**: As estatísticas consideram apenas a **última tentativa** de cada simulado (últimos 30 minutos da resposta mais recente)

3. **Performance**: Para grandes volumes de dados, considere implementar cache nos endpoints

4. **Timezone**: Todas as datas estão em UTC. Converta no frontend conforme necessário

5. **Dados Mínimos**: Alguns cálculos retornam `null` ou `0` quando não há dados suficientes
