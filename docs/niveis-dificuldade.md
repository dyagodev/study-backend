# Níveis de Dificuldade das Questões

## 🎯 Conceito

**TODAS as questões geradas são do tipo CONCURSO PÚBLICO**, mas você pode escolher o **nível de dificuldade**.

### Estrutura de Campos

- **`nivel`**: Tipo de questão (sempre "concurso")
- **`nivel_dificuldade`**: Nível de complexidade (facil, medio, dificil, muito_dificil)

## Níveis Disponíveis

### 1. FÁCIL (`facil`)
Questões de concurso que abordam **conceitos básicos e diretos**:
- ✅ Exigem conhecimento fundamental sobre o assunto
- ✅ Linguagem clara e objetiva no estilo de concurso
- ✅ Foco em definições e conceitos essenciais
- ✅ Ideal para iniciantes em concursos públicos

**Exemplo:**
```
(Questão de Concurso - Nível Fácil)

Segundo a Constituição Federal de 1988, qual é a capital do Brasil?

a) São Paulo
b) Brasília ✓
c) Rio de Janeiro
d) Salvador
```

### 2. MÉDIO (`medio`) - **PADRÃO**
Questões de concurso que exigem **interpretação e aplicação de conceitos**:
- ✅ Raciocínio moderado
- ✅ Correlação de conceitos básicos
- ✅ Situações práticas no contexto de concursos
- ✅ Nível típico de concursos de nível médio

**Exemplo:**
```
(Questão de Concurso - Nível Médio)

Considerando o princípio da legalidade previsto no art. 37 da CF/88, 
é correto afirmar que:

a) O administrador pode fazer tudo que a lei não proíbe
b) O administrador só pode fazer o que a lei autoriza ✓
c) A lei não se aplica aos agentes públicos em situações excepcionais
d) O princípio não se aplica a contratos administrativos
```

### 3. DIFÍCIL (`dificil`)
Questões de concurso complexas que exigem **análise crítica**:
- ✅ Correlação de múltiplos conceitos jurídicos/técnicos
- ✅ Raciocínio avançado
- ✅ Situações complexas típicas de provas
- ✅ Nível típico de concursos de nível superior

**Exemplo:**
```
(Questão de Concurso - Nível Difícil)

À luz da jurisprudência consolidada do STF sobre o princípio da 
legalidade tributária e da reserva de lei complementar, analise as 
assertivas sobre os limites do poder de tributar e assinale a alternativa 
correta considerando a teoria dos poderes implícitos...
```

### 4. MUITO DIFÍCIL (`muito_dificil`)
Questões de concurso de **alta complexidade**:
- ✅ Conhecimento profundo e especializado
- ✅ Interpretação de casos complexos e precedentes
- ✅ Raciocínio expert e análise crítica aprofundada
- ✅ Nível típico de concursos de alto escalão (magistratura, procuradorias, tribunais superiores)

**Exemplo:**
```
(Questão de Concurso - Nível Muito Difícil)

Considerando os precedentes vinculantes do STF em matéria de controle 
de constitucionalidade difuso, a recente alteração do CPC/2015 quanto à 
modulação de efeitos, e a doutrina majoritária sobre segurança jurídica, 
analise o caso concreto apresentado e a compatibilidade das decisões 
com o sistema de precedentes brasileiro, indicando a alternativa que 
corretamente aplica a teoria dos diálogos institucionais...
```

## Uso nos Endpoints

### 1. Geração por Tema

**Endpoint:** `POST /api/questoes/gerar-por-tema`

**Body:**
```json
{
  "tema_id": 1,
  "assunto": "Princípios Administrativos",
  "quantidade": 5,
  "nivel": "dificil"
}
```

**Resposta:**
```json
{
  "success": true,
  "message": "Questões geradas com sucesso",
  "data": [
    {
      "id": 123,
      "enunciado": "...",
      "nivel": "concurso",
      "nivel_dificuldade": "dificil",
      "alternativas": [...]
    }
  ],
  "custo": 5,
  "saldo_restante": 95
}
```

### 2. Geração de Variação

**Endpoint:** `POST /api/questoes/gerar-variacao`

**Body:**
```json
{
  "questao_exemplo": "Qual é o princípio que obriga o administrador público a seguir a lei?",
  "tema_id": 1,
  "assunto": "Direito Administrativo",
  "quantidade": 3,
  "nivel": "facil"
}
```

### 3. Geração por Imagem

**Endpoint:** `POST /api/questoes/gerar-por-imagem`

**Form Data:**
```
imagem: [arquivo]
tema_id: 1
assunto: "Organogramas Administrativos"
nivel: "medio"
contexto: "Análise de estrutura organizacional"
```

## Validação

```php
'nivel' => 'sometimes|in:facil,medio,dificil,muito_dificil'
```

### Valores Aceitos
- ✅ `facil`
- ✅ `medio` (padrão se omitido)
- ✅ `dificil`
- ✅ `muito_dificil`

### Valores NÃO Aceitos
- ❌ `fundamental`
- ❌ `superior`
- ❌ `concurso`
- ❌ Qualquer outro valor retorna erro 422

## Comportamento Padrão

Se o campo `nivel` não for enviado, o sistema usará **`medio`** como padrão.

## Prompts da IA

O sistema instrui a IA da seguinte forma:

```
"Você é um especialista em educação e elaboração de questões para 
CONCURSOS PÚBLICOS BRASILEIROS.

**NÍVEL DE DIFICULDADE EXIGIDO: {NÍVEL}**

IMPORTANTE: 
- TODAS as questões devem ser do tipo CONCURSO PÚBLICO
- O nível de dificuldade deve ser EXATAMENTE {NÍVEL}
- Use linguagem formal e técnica apropriada para concursos públicos"
```

Isso garante que:
1. **Tipo**: Sempre concurso público (campo `nivel` = "concurso")
2. **Complexidade**: Ajustada conforme seleção (campo `nivel_dificuldade`)

## Exemplos Completos

### Exemplo 1: Questão Fácil de Concurso

```bash
curl -X POST http://localhost/api/questoes/gerar-por-tema \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "tema_id": 1,
    "assunto": "Poderes da Administração",
    "quantidade": 5,
    "nivel": "facil"
  }'
```

**Resultado:**
- Tipo: Concurso Público
- Dificuldade: Fácil
- Foco: Conceitos básicos diretos
- Custo: 5 créditos (1 por questão)

### Exemplo 2: Questão Muito Difícil de Concurso

```bash
curl -X POST http://localhost/api/questoes/gerar-por-tema \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "tema_id": 2,
    "assunto": "Controle de Constitucionalidade",
    "quantidade": 3,
    "nivel": "muito_dificil"
  }'
```

**Resultado:**
- Tipo: Concurso Público
- Dificuldade: Muito Difícil
- Foco: Análise complexa de precedentes e doutrina
- Custo: 3 créditos

## Filtros e Consultas

### Consultar Questões por Dificuldade

```bash
GET /api/questoes?nivel_dificuldade=dificil
```

### Consultar Estatísticas

```sql
SELECT nivel_dificuldade, COUNT(*) as total
FROM questoes
WHERE nivel = 'concurso'
GROUP BY nivel_dificuldade;
```

**Resultado:**
```
| nivel_dificuldade | total |
|-------------------|-------|
| facil             | 120   |
| medio             | 350   |
| dificil           | 180   |
| muito_dificil     | 50    |
```

## Frontend - Seletor de Dificuldade

```javascript
const niveisDificuldade = [
  { value: 'facil', label: '⭐ Fácil', description: 'Conceitos básicos' },
  { value: 'medio', label: '⭐⭐ Médio', description: 'Interpretação moderada' },
  { value: 'dificil', label: '⭐⭐⭐ Difícil', description: 'Análise crítica' },
  { value: 'muito_dificil', label: '⭐⭐⭐⭐ Muito Difícil', description: 'Raciocínio expert' }
];

// Componente React
<select name="nivel">
  {niveisDificuldade.map(n => (
    <option key={n.value} value={n.value}>
      {n.label} - {n.description}
    </option>
  ))}
</select>
```

## Resumo

✅ **Todas questões = Concurso Público** (campo `nivel`)  
✅ **Dificuldade configurável** (campo `nivel_dificuldade`)  
✅ **4 níveis**: facil, medio, dificil, muito_dificil  
✅ **Padrão**: medio  
✅ **Prompts otimizados** para cada nível  
✅ **Validação rigorosa** nos endpoints  

🎯 **Resultado**: Questões de concurso com complexidade ajustável às necessidades do estudante!
