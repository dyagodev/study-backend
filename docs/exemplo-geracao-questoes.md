# Exemplo de Uso - Geração de Questões com Assunto

## Alterações Implementadas

### 1. Banco de Dados
- ✅ Adicionado campo `assunto` na tabela `questoes`
- ✅ Adicionado nível `concurso` ao enum de níveis
- ✅ Todas as questões agora são **nível concurso** por padrão

### 2. Endpoints Atualizados

#### POST /api/questoes/gerar-por-tema
Agora requer o campo `assunto` obrigatório.

**Request:**
```json
{
  "tema_id": 1,
  "assunto": "Direito Constitucional - Direitos Fundamentais",
  "quantidade": 5
}
```

**Response:**
```json
{
  "success": true,
  "message": "Questões geradas com sucesso",
  "data": [
    {
      "id": 123,
      "tema_id": 1,
      "assunto": "Direito Constitucional - Direitos Fundamentais",
      "user_id": 3,
      "enunciado": "A questão aqui...",
      "nivel": "concurso",
      "explicacao": "Explicação detalhada...",
      "tipo_geracao": "ia_tema",
      "alternativas": [
        {
          "id": 456,
          "questao_id": 123,
          "texto": "Alternativa A",
          "correta": false,
          "ordem": 1
        },
        // ... mais alternativas
      ]
    }
  ]
}
```

#### POST /api/questoes/gerar-variacao
Também requer o campo `assunto`.

**Request:**
```json
{
  "tema_id": 1,
  "assunto": "Algoritmos - Ordenação",
  "questao_exemplo": "Uma questão exemplo aqui...",
  "quantidade": 3
}
```

#### POST /api/questoes/gerar-por-imagem
Requer o campo `assunto`.

**Request (multipart/form-data):**
```
tema_id: 1
assunto: "Geografia - Mapas do Brasil"
imagem: [arquivo]
contexto: "Mapa político do Brasil" (opcional)
```

## Níveis Disponíveis

- `facil` - Nível fácil (básico)
- `medio` - Nível médio
- `dificil` - Nível difícil
- `concurso` - **Nível concurso público (PADRÃO)** ⭐

## Características das Questões Nível Concurso

As questões geradas agora são:
- ✅ Complexas e desafiadoras
- ✅ Exigem raciocínio crítico
- ✅ Requerem conhecimento profundo do assunto
- ✅ Usam linguagem formal e técnica
- ✅ Apropriadas para concursos públicos brasileiros
- ✅ **NÃO dependem de imagens** (apenas texto)

## Exemplo Completo de Uso

### Gerar Questões de Direito Administrativo

```bash
curl -X POST http://localhost/api/questoes/gerar-por-tema \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "Content-Type: application/json" \
  -d '{
    "tema_id": 2,
    "assunto": "Direito Administrativo - Princípios da Administração Pública",
    "quantidade": 5
  }'
```

### Gerar Questões de Matemática

```bash
curl -X POST http://localhost/api/questoes/gerar-por-tema \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "Content-Type: application/json" \
  -d '{
    "tema_id": 5,
    "assunto": "Matemática - Análise Combinatória e Probabilidade",
    "quantidade": 3
  }'
```

### Gerar Questões de Informática

```bash
curl -X POST http://localhost/api/questoes/gerar-por-tema \
  -H "Authorization: Bearer SEU_TOKEN_AQUI" \
  -H "Content-Type: application/json" \
  -d '{
    "tema_id": 8,
    "assunto": "Redes de Computadores - Protocolos TCP/IP",
    "quantidade": 4
  }'
```

## Validações

Os seguintes campos são **obrigatórios**:
- `tema_id` - ID do tema existente
- `assunto` - Descrição específica do assunto (max 255 caracteres)

Campos opcionais:
- `quantidade` - Padrão: 5 (min: 1, max: 10)

## Notas Importantes

1. **Todas as questões são nível concurso** - não é mais possível escolher o nível
2. **Assunto é obrigatório** - ajuda a IA a gerar questões mais específicas e relevantes
3. **Sem imagens geradas** - todas as questões são 100% baseadas em texto
4. O campo `assunto` permite especificidade como:
   - "Língua Portuguesa - Concordância Verbal"
   - "História do Brasil - República Velha"
   - "Economia - Macroeconomia e Política Fiscal"
