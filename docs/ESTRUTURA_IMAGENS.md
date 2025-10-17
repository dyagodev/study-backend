# 📸 Estrutura de Imagens - Dois Campos Separados

## Conceito

O sistema agora possui **dois campos distintos** para gerenciar imagens nas questões:

### 1. `imagem_url` (Imagem Original)
- **Propósito**: Armazena a imagem **enviada pelo usuário**
- **Uso**: Quando o professor envia uma imagem para a IA analisar e gerar questões
- **Exemplo**: Foto de um experimento, diagrama escaneado, gráfico existente

### 2. `imagem_gerada_url` (Imagem Gerada pela IA)
- **Propósito**: Armazena a imagem **criada pela IA** (DALL-E 3)
- **Uso**: Quando o enunciado da questão menciona uma figura/gráfico/diagrama
- **Exemplo**: Triângulo geométrico, diagrama celular, gráfico de função

## Estrutura no Banco de Dados

```sql
CREATE TABLE questoes (
    id BIGINT PRIMARY KEY,
    tema_id BIGINT,
    user_id BIGINT,
    enunciado TEXT,
    nivel ENUM('facil', 'medio', 'dificil'),
    explicacao TEXT,
    imagem_url VARCHAR(255),              -- Imagem ORIGINAL (enviada)
    imagem_gerada_url VARCHAR(255),       -- Imagem GERADA (IA)
    tags JSON,
    tipo_geracao ENUM(...),
    favorita BOOLEAN,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## Casos de Uso

### Caso 1: Questão Gerada por Tema (COM imagem necessária)

**Requisição:**
```http
POST /api/questoes/gerar-por-tema
{
  "tema_id": 1,
  "quantidade": 1,
  "nivel": "medio"
}
```

**IA gera:**
```json
{
  "enunciado": "Observe o triângulo retângulo na figura. Calcule o valor de x.",
  "alternativas": [...]
}
```

**Sistema detecta:** "Observe o triângulo" → precisa de imagem

**Resultado salvo:**
```json
{
  "enunciado": "Observe o triângulo retângulo na figura...",
  "imagem_url": null,                    // ✗ Não foi enviada
  "imagem_gerada_url": "questoes/imagens/tri_123.png",  // ✓ Gerada pela IA
  "imagem_url_completa": null,
  "imagem_gerada_url_completa": "http://study.test/storage/questoes/imagens/tri_123.png"
}
```

### Caso 2: Questão Gerada por Tema (SEM imagem)

**Requisição:**
```http
POST /api/questoes/gerar-por-tema
{
  "tema_id": 2,
  "quantidade": 1,
  "nivel": "facil"
}
```

**IA gera:**
```json
{
  "enunciado": "Qual é a fórmula da água?",
  "alternativas": [...]
}
```

**Sistema detecta:** Não menciona imagem

**Resultado salvo:**
```json
{
  "enunciado": "Qual é a fórmula da água?",
  "imagem_url": null,                    // ✗ Não foi enviada
  "imagem_gerada_url": null,             // ✗ Não precisa
  "imagem_url_completa": null,
  "imagem_gerada_url_completa": null
}
```

### Caso 3: Questão Gerada a partir de Imagem Enviada

**Requisição:**
```http
POST /api/questoes/gerar-por-imagem
{
  "imagem": [arquivo],
  "tema_id": 3,
  "contexto": "Biologia"
}
```

**Sistema:**
1. Salva imagem enviada → `imagem_url`
2. IA analisa e gera questão baseada na imagem
3. Questão **não** menciona "observe a figura" (usa a imagem enviada)

**Resultado salvo:**
```json
{
  "enunciado": "Identifique a estrutura celular apontada pela seta.",
  "imagem_url": "questoes/imagens/uploaded_456.jpg",  // ✓ Imagem enviada
  "imagem_gerada_url": null,                          // ✗ Usa a enviada
  "imagem_url_completa": "http://study.test/storage/questoes/imagens/uploaded_456.jpg",
  "imagem_gerada_url_completa": null
}
```

### Caso 4: Ambas (Raro, mas possível)

Cenário: Professor envia imagem para análise + IA decide gerar uma versão melhorada

**Resultado salvo:**
```json
{
  "enunciado": "Compare as duas representações do ciclo celular...",
  "imagem_url": "questoes/imagens/original_789.jpg",       // ✓ Original enviada
  "imagem_gerada_url": "questoes/imagens/generated_790.png", // ✓ Versão melhorada
  "imagem_url_completa": "http://study.test/storage/questoes/imagens/original_789.jpg",
  "imagem_gerada_url_completa": "http://study.test/storage/questoes/imagens/generated_790.png"
}
```

## Lógica de Detecção

### Quando gerar imagem?

```php
// Palavras-chave que indicam necessidade de imagem
$palavrasChave = [
    'figura', 'imagem', 'gráfico', 'diagrama', 'ilustração',
    'observe', 'analise', 'veja', 'considere', 'de acordo com',
    'representado', 'mostrado', 'apresentado', 'exibido',
    'desenho', 'esquema', 'tabela', 'mapa', 'foto'
];

if (enunciadoContemPalavraChave($enunciado)) {
    gerarImagemComDallE();
}
```

## Frontend - Como Exibir

### Prioridade de Exibição

```javascript
// Qual imagem mostrar para o aluno?
const imagemParaMostrar = 
    questao.imagem_gerada_url_completa ||  // 1º: Imagem gerada pela IA
    questao.imagem_url_completa ||         // 2º: Imagem original enviada
    null;                                  // 3º: Sem imagem
```

### Componente React (Exemplo)

```jsx
function Questao({ questao }) {
  return (
    <div className="questao">
      <p className="enunciado">{questao.enunciado}</p>
      
      {/* Imagem gerada tem prioridade */}
      {questao.imagem_gerada_url_completa && (
        <img 
          src={questao.imagem_gerada_url_completa} 
          alt="Ilustração da questão"
          className="questao-imagem"
        />
      )}
      
      {/* Se não tem gerada, mostra a original */}
      {!questao.imagem_gerada_url_completa && questao.imagem_url_completa && (
        <img 
          src={questao.imagem_url_completa} 
          alt="Imagem da questão"
          className="questao-imagem"
        />
      )}
      
      <div className="alternativas">
        {questao.alternativas.map(alt => (
          <button key={alt.id}>{alt.texto}</button>
        ))}
      </div>
    </div>
  );
}
```

## Vantagens da Separação

### ✅ Rastreabilidade
- Saber qual imagem veio do usuário
- Saber qual foi gerada automaticamente

### ✅ Flexibilidade
- Poder ter ambas se necessário
- Escolher qual exibir no frontend

### ✅ Auditoria
- Verificar custos de geração (contar `imagem_gerada_url` não nulos)
- Identificar qualidade das imagens geradas vs enviadas

### ✅ Manutenção
- Regenerar apenas imagens geradas sem perder originais
- Melhorar prompts e regenerar em lote

## Migração Executada

```bash
php artisan make:migration add_imagem_gerada_to_questoes_table
php artisan migrate
```

**Arquivo:** `2025_10_17_134024_add_imagem_gerada_to_questoes_table.php`

**Mudança:**
```php
Schema::table('questoes', function (Blueprint $table) {
    $table->string('imagem_gerada_url')->nullable()->after('imagem_url')
        ->comment('URL da imagem gerada automaticamente por IA (DALL-E) para a questão');
});
```

## Model Atualizado

**Arquivo:** `app/Models/Questao.php`

```php
protected $fillable = [
    'tema_id', 'user_id', 'enunciado', 'nivel', 'explicacao',
    'imagem_url',          // ✓ Adicionado
    'imagem_gerada_url',   // ✓ Adicionado
    'tags', 'tipo_geracao', 'favorita'
];

protected $appends = [
    'imagem_url_completa',         // ✓ Accessor
    'imagem_gerada_url_completa'   // ✓ Accessor
];
```

## Resumo

| Campo | Origem | Quando Preencher | Exemplo |
|-------|--------|------------------|---------|
| `imagem_url` | Usuário | POST `/gerar-por-imagem` | Foto enviada pelo professor |
| `imagem_gerada_url` | IA (DALL-E) | Enunciado menciona imagem | Triângulo gerado automaticamente |

**Regra de Ouro:** 
- `imagem_url` = O que o usuário **enviou**
- `imagem_gerada_url` = O que a IA **criou**

---

**Status:** ✅ Implementado  
**Data:** 17 de outubro de 2025  
**Migration:** Executada com sucesso
