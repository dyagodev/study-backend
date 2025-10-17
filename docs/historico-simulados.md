# 📚 Sistema de Histórico de Simulados - Documentação

## ✅ Implementação Completa

O sistema agora armazena **todas as tentativas** de simulados de forma organizada, permitindo que os usuários visualizem todo o histórico de tentativas, comparem desempenhos e revisem erros e acertos de tentativas anteriores.

---

## 🗄️ Estrutura do Banco de Dados

### Nova Tabela: `simulado_tentativas`

Armazena cada tentativa completa de um simulado.

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | bigint | ID único da tentativa |
| simulado_id | bigint | FK para simulados |
| user_id | bigint | FK para users |
| numero_tentativa | integer | Número sequencial (1, 2, 3...) |
| total_questoes | integer | Total de questões respondidas |
| acertos | integer | Quantidade de acertos |
| erros | integer | Quantidade de erros |
| percentual_acerto | decimal(5,2) | Percentual de acerto |
| tempo_total | integer | Tempo total em segundos |
| data_inicio | timestamp | Quando iniciou |
| data_fim | timestamp | Quando finalizou |
| created_at | timestamp | - |
| updated_at | timestamp | - |

**Índices:**
- Único: `(simulado_id, user_id, numero_tentativa)`
- Composto: `(simulado_id, user_id)`

### Atualização: `respostas_usuario`

Adicionado campo `tentativa_id` para vincular cada resposta a uma tentativa específica.

---

## 🎯 Endpoints Atualizados

### 1. **Responder Simulado** (Modificado)
`POST /api/simulados/{simulado_id}/responder`

Agora cria automaticamente um registro de tentativa e vincula todas as respostas a ela.

**Request:**
```json
{
  "respostas": [
    {
      "questao_id": 1,
      "alternativa_id": 5,
      "tempo_resposta": 30
    },
    {
      "questao_id": 2,
      "alternativa_id": 8,
      "tempo_resposta": 45
    }
  ],
  "data_inicio": "2025-10-17 14:00:00"
}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Respostas registradas com sucesso",
  "data": {
    "tentativa_id": 123,
    "numero_tentativa": 3,
    "acertos": 15,
    "total_questoes": 20,
    "percentual_acerto": 75.00,
    "tempo_total": 900
  }
}
```

**Mudanças:**
- ✅ Cria registro em `simulado_tentativas`
- ✅ Calcula automaticamente o `numero_tentativa`
- ✅ Vincula todas as respostas à tentativa (`tentativa_id`)
- ✅ Retorna `tentativa_id` e `numero_tentativa`

---

### 2. **Ver Resultado** (Modificado)
`GET /api/simulados/{simulado_id}/resultado`

Retorna os detalhes da **última tentativa** do simulado.

**Response 200:**
```json
{
  "success": true,
  "data": {
    "simulado": {
      "id": 5,
      "titulo": "Simulado de Direito",
      "descricao": "Questões de nível concurso",
      "mostrar_gabarito": true
    },
    "tentativa": {
      "id": 123,
      "numero": 3,
      "data_inicio": "2025-10-17 14:00:00",
      "data_fim": "2025-10-17 14:15:00",
      "tempo_total": 900
    },
    "estatisticas": {
      "total_questoes": 20,
      "acertos": 15,
      "erros": 5,
      "percentual_acerto": 75.00
    },
    "respostas": [
      {
        "questao_id": 1,
        "questao_enunciado": "Qual é...",
        "alternativa_escolhida_id": 5,
        "alternativa_escolhida": "Alternativa C",
        "correta": true,
        "alternativa_correta_id": 5,
        "alternativa_correta": "Alternativa C",
        "explicacao": "Porque...",
        "tempo_resposta": 30
      }
      // ... mais respostas
    ]
  }
}
```

**Mudanças:**
- ✅ Usa `tentativa_id` ao invés de calcular janela de 30 minutos
- ✅ Retorna informações completas da tentativa
- ✅ Mais rápido e preciso
- ✅ Inclui IDs das alternativas para comparação no frontend

---

### 3. **Histórico de Tentativas** (Melhorado)
`GET /api/simulados/{simulado_id}/historico`

Lista **todas as tentativas** do usuário neste simulado.

**Response 200:**
```json
{
  "success": true,
  "data": {
    "simulado": {
      "id": 5,
      "titulo": "Simulado de Direito",
      "descricao": "Questões de nível concurso"
    },
    "total_tentativas": 3,
    "estatisticas_gerais": {
      "melhor_percentual": 85.00,
      "melhor_tentativa_numero": 2,
      "media_percentual": 73.33,
      "tempo_medio": 780
    },
    "tentativas": [
      {
        "tentativa_id": 125,
        "numero_tentativa": 3,
        "data_inicio": "2025-10-17 14:00:00",
        "data_fim": "2025-10-17 14:15:00",
        "total_questoes": 20,
        "acertos": 15,
        "erros": 5,
        "percentual_acerto": 75.00,
        "tempo_total": 900
      },
      {
        "tentativa_id": 120,
        "numero_tentativa": 2,
        "data_inicio": "2025-10-16 10:00:00",
        "data_fim": "2025-10-16 10:12:00",
        "total_questoes": 20,
        "acertos": 17,
        "erros": 3,
        "percentual_acerto": 85.00,
        "tempo_total": 720
      },
      {
        "tentativa_id": 115,
        "numero_tentativa": 1,
        "data_inicio": "2025-10-15 16:00:00",
        "data_fim": "2025-10-15 16:11:00",
        "total_questoes": 20,
        "acertos": 13,
        "erros": 7,
        "percentual_acerto": 65.00,
        "tempo_total": 660
      }
    ]
  }
}
```

**Mudanças:**
- ✅ Ordenado da mais recente para mais antiga
- ✅ Estatísticas gerais do histórico
- ✅ Melhor tentativa destacada
- ✅ Média de desempenho
- ✅ Tempo médio de todas as tentativas

---

### 4. **Detalhe de Tentativa Específica** (NOVO ✨)
`GET /api/simulados/{simulado_id}/tentativas/{tentativa_id}`

Visualiza os detalhes completos de **qualquer tentativa anterior**.

**Response 200:**
```json
{
  "success": true,
  "data": {
    "simulado": {
      "id": 5,
      "titulo": "Simulado de Direito",
      "descricao": "Questões de nível concurso",
      "mostrar_gabarito": true
    },
    "tentativa": {
      "id": 120,
      "numero": 2,
      "data_inicio": "2025-10-16 10:00:00",
      "data_fim": "2025-10-16 10:12:00",
      "tempo_total": 720
    },
    "estatisticas": {
      "total_questoes": 20,
      "acertos": 17,
      "erros": 3,
      "percentual_acerto": 85.00
    },
    "respostas": [
      {
        "questao_id": 1,
        "questao_enunciado": "Qual é...",
        "alternativa_escolhida_id": 5,
        "alternativa_escolhida": "Alternativa C",
        "correta": true,
        "alternativa_correta_id": 5,
        "alternativa_correta": "Alternativa C",
        "explicacao": "Porque...",
        "tempo_resposta": 30
      }
      // ... todas as respostas da tentativa
    ]
  }
}
```

**Casos de Uso:**
- ✅ Revisar tentativa anterior
- ✅ Comparar erros entre tentativas
- ✅ Analisar evolução do aprendizado
- ✅ Refazer questões que errou

---

## 🔄 Fluxo Completo

### 1️⃣ Usuário Inicia Simulado
```bash
POST /api/simulados/5/iniciar
```
- Retorna questões do simulado
- Frontend inicia cronômetro

### 2️⃣ Usuário Responde Questões
- Frontend coleta respostas
- Frontend registra tempo de cada resposta
- Frontend armazena `data_inicio`

### 3️⃣ Usuário Finaliza Simulado
```bash
POST /api/simulados/5/responder
{
  "respostas": [...],
  "data_inicio": "2025-10-17 14:00:00"
}
```
- Backend cria `SimuladoTentativa`
- Backend vincula todas as `RespostaUsuario` à tentativa
- Backend calcula estatísticas automaticamente
- Retorna `tentativa_id` e resumo

### 4️⃣ Usuário Vê Resultado
```bash
GET /api/simulados/5/resultado
```
- Retorna detalhes da última tentativa
- Mostra gabarito se `mostrar_gabarito = true`
- Mostra tempo de cada resposta

### 5️⃣ Usuário Acessa Histórico
```bash
GET /api/simulados/5/historico
```
- Lista todas as tentativas
- Mostra evolução do desempenho
- Destaca melhor tentativa

### 6️⃣ Usuário Revisa Tentativa Anterior
```bash
GET /api/simulados/5/tentativas/120
```
- Vê detalhes completos da tentativa #2
- Compara com tentativa atual
- Identifica questões que continua errando

---

## 📊 Comparação: Antes vs Depois

### ❌ ANTES (Problemático)
- Respostas sem agrupamento claro
- Tentativas calculadas por gap de tempo (30 min)
- Difícil identificar tentativa específica
- Estatísticas calculadas em tempo real
- Conflito ao refazer simulado rapidamente

### ✅ DEPOIS (Robusto)
- ✅ Cada tentativa é um registro único
- ✅ Número sequencial automático (1, 2, 3...)
- ✅ Estatísticas pré-calculadas e armazenadas
- ✅ Histórico completo e organizado
- ✅ Comparação fácil entre tentativas
- ✅ Performance otimizada
- ✅ Não depende de janelas de tempo

---

## 💡 Casos de Uso Frontend

### Dashboard de Simulado
```javascript
// Mostrar botão "Ver Última Tentativa"
GET /api/simulados/5/resultado

// Mostrar histórico em tabela
GET /api/simulados/5/historico

// Gráfico de evolução
// Eixo X: número_tentativa
// Eixo Y: percentual_acerto
```

### Comparador de Tentativas
```javascript
// Buscar tentativa atual
const atual = await fetch('/api/simulados/5/resultado');

// Buscar tentativa anterior
const anterior = await fetch('/api/simulados/5/tentativas/120');

// Comparar erros
const errosNovos = atual.respostas
  .filter(r => !r.correta && anterior.respostas
    .find(a => a.questao_id === r.questao_id)?.correta
  );

console.log('Questões que passou a errar:', errosNovos);
```

### Evolução do Aprendizado
```javascript
// Pegar histórico
const historico = await fetch('/api/simulados/5/historico');

// Renderizar gráfico de linha
const data = historico.tentativas.map(t => ({
  x: t.numero_tentativa,
  y: t.percentual_acerto
}));

// Mostrar tendência
const tendencia = data[data.length - 1].y > data[0].y 
  ? '📈 Melhorando!' 
  : '📉 Precisa revisar';
```

---

## 🧪 Testando

### 1. Criar e Responder Simulado
```bash
# Primeira tentativa
curl -X POST "http://localhost/api/simulados/5/responder" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "respostas": [
      {"questao_id": 1, "alternativa_id": 2, "tempo_resposta": 30},
      {"questao_id": 2, "alternativa_id": 6, "tempo_resposta": 45}
    ],
    "data_inicio": "2025-10-17 14:00:00"
  }'

# Response
{
  "tentativa_id": 1,
  "numero_tentativa": 1,
  "acertos": 1,
  "percentual_acerto": 50.00
}
```

### 2. Responder Novamente (Segunda Tentativa)
```bash
curl -X POST "http://localhost/api/simulados/5/responder" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "respostas": [
      {"questao_id": 1, "alternativa_id": 2, "tempo_resposta": 25},
      {"questao_id": 2, "alternativa_id": 7, "tempo_resposta": 40}
    ]
  }'

# Response
{
  "tentativa_id": 2,
  "numero_tentativa": 2,  // ← Incrementado automaticamente!
  "acertos": 2,
  "percentual_acerto": 100.00
}
```

### 3. Ver Histórico
```bash
curl -X GET "http://localhost/api/simulados/5/historico" \
  -H "Authorization: Bearer {token}"

# Retorna ambas as tentativas com estatísticas
```

---

## ✅ Checklist de Implementação

- [x] Migration `simulado_tentativas` criada
- [x] Migration `add_tentativa_id_to_respostas_usuario` criada
- [x] Model `SimuladoTentativa` criado
- [x] Model `RespostaUsuario` atualizado
- [x] Endpoint `responder` atualizado para criar tentativas
- [x] Endpoint `resultado` atualizado para usar tentativas
- [x] Endpoint `historico` atualizado para listar tentativas
- [x] Endpoint `detalheTentativa` criado (NOVO)
- [x] Rota `/tentativas/{tentativaId}` adicionada
- [x] Migrations executadas com sucesso
- [x] Documentação completa criada

---

## 🚀 Benefícios

1. **Organização**: Cada tentativa é um registro único
2. **Performance**: Estatísticas pré-calculadas
3. **Histórico**: Todas as tentativas salvas permanentemente
4. **Comparação**: Fácil comparar desempenho entre tentativas
5. **Análise**: Identificar questões problemáticas recorrentes
6. **Motivação**: Ver evolução visual do aprendizado
7. **Confiabilidade**: Não depende de cálculos de janela de tempo

---

## 📝 Próximos Passos (Opcional)

- [ ] Endpoint para deletar tentativa específica
- [ ] Comparador automático de duas tentativas
- [ ] Relatório de questões mais erradas
- [ ] Badge/conquista ao melhorar desempenho
- [ ] Exportar tentativa em PDF
