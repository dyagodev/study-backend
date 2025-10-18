# Tipo de Questão e Banca Realizadora

## Visão Geral

Esta funcionalidade permite que os usuários especifiquem o tipo de questão e a banca realizadora ao gerar questões via IA.

## Novos Campos na Tabela `questoes`

### 1. `tipo_questao` (ENUM)
Define o tipo de questão a ser gerada:

- **concurso** (padrão): Questões no estilo de concursos públicos brasileiros
- **enem**: Questões no estilo do ENEM (Exame Nacional do Ensino Médio)
- **prova_crc**: Questões no estilo da Prova do CRC (Conselho Regional de Contabilidade)
- **oab**: Questões no estilo do Exame da OAB (Ordem dos Advogados do Brasil)
- **outros**: Para especificar outros tipos de provas

### 2. `tipo_questao_outro` (STRING, opcional)
Campo obrigatório quando `tipo_questao = 'outros'`. Permite especificar o tipo de questão customizado.

**Exemplo**: "Prova de Residência Médica", "Exame de Certificação PMP", etc.

### 3. `banca` (STRING, opcional)
Permite especificar a banca realizadora da prova.

**Exemplos**: 
- "CESPE/CEBRASPE"
- "FCC - Fundação Carlos Chagas"
- "FGV"
- "VUNESP"
- "Quadrix"
- "IBFC"
- "AOCP"

## API - Endpoints Atualizados

### 1. Gerar Questões por Tema
**POST** `/api/questoes-ia/gerar-por-tema`

```json
{
  "tema_id": 1,
  "assunto": "Direito Constitucional",
  "quantidade": 5,
  "nivel": "medio",
  "tipo_questao": "concurso",
  "banca": "CESPE/CEBRASPE"
}
```

### 2. Gerar Variação de Questão
**POST** `/api/questoes-ia/gerar-variacao`

```json
{
  "questao_exemplo": "Qual é o capital social mínimo...",
  "assunto": "Contabilidade Societária",
  "quantidade": 3,
  "tema_id": 2,
  "nivel": "dificil",
  "tipo_questao": "prova_crc",
  "banca": "CFC - Conselho Federal de Contabilidade"
}
```

### 3. Gerar Questões por Imagem
**POST** `/api/questoes-ia/gerar-por-imagem`

```json
{
  "imagem": "[arquivo]",
  "tema_id": 3,
  "assunto": "Anatomia Humana",
  "contexto": "Esquema do sistema cardiovascular",
  "nivel": "facil",
  "tipo_questao": "enem"
}
```

### 4. Exemplo com Tipo Customizado
**POST** `/api/questoes-ia/gerar-por-tema`

```json
{
  "tema_id": 4,
  "assunto": "Gerenciamento de Projetos",
  "quantidade": 3,
  "nivel": "muito_dificil",
  "tipo_questao": "outros",
  "tipo_questao_outro": "Certificação PMP",
  "banca": "PMI"
}
```

## Validações

### Regras de Validação

1. **tipo_questao**: 
   - Opcional
   - Valores aceitos: `concurso`, `enem`, `prova_crc`, `oab`, `outros`
   - Padrão: `concurso`

2. **tipo_questao_outro**:
   - Obrigatório se `tipo_questao = 'outros'`
   - Máximo 100 caracteres
   - String

3. **banca**:
   - Opcional
   - Máximo 150 caracteres
   - String

### Exemplos de Erros

```json
{
  "success": false,
  "message": "The tipo questao outro field is required when tipo questao is outros.",
  "errors": {
    "tipo_questao_outro": [
      "The tipo questao outro field is required when tipo questao is outros."
    ]
  }
}
```

## Comportamento da IA

A IA ajusta o prompt de geração baseado no tipo de questão e banca especificados:

### Exemplo: Concurso com Banca CESPE
```
Você é um especialista em educação e elaboração de questões para CONCURSOS PÚBLICOS BRASILEIROS.

**TIPO DE QUESTÃO: CONCURSOS PÚBLICOS BRASILEIROS**
**BANCA REALIZADORA: CESPE/CEBRASPE**
As questões devem seguir o estilo e formato típicos desta banca examinadora.
**NÍVEL DE DIFICULDADE EXIGIDO: MÉDIO**
```

### Exemplo: ENEM
```
Você é um especialista em educação e elaboração de questões para ENEM (Exame Nacional do Ensino Médio).

**TIPO DE QUESTÃO: ENEM (Exame Nacional do Ensino Médio)**
**NÍVEL DE DIFICULDADE EXIGIDO: MÉDIO**
```

### Exemplo: Tipo Customizado
```
Você é um especialista em educação e elaboração de questões para CERTIFICAÇÃO PMP.

**TIPO DE QUESTÃO: CERTIFICAÇÃO PMP**
**BANCA REALIZADORA: PMI**
As questões devem seguir o estilo e formato típicos desta banca examinadora.
**NÍVEL DE DIFICULDADE EXIGIDO: MUITO DIFÍCIL**
```

## Impacto nos Custos

Os custos de geração **NÃO são afetados** pelos novos campos. Os valores continuam:

- **Questão Simples**: 3 créditos
- **Variação**: 5 créditos
- **Por Imagem**: 8 créditos
- **Simulado**: 10 créditos

## Compatibilidade com Sistema Existente

### Retrocompatibilidade
- Questões antigas sem os novos campos terão:
  - `tipo_questao = 'concurso'` (padrão)
  - `tipo_questao_outro = null`
  - `banca = null`

### Migração Automática
A migration adiciona os campos com valores padrão, mantendo todas as questões existentes funcionando normalmente.

### Campo `nivel` Mantido
O campo antigo `nivel = 'concurso'` foi mantido para compatibilidade. O novo sistema usa `tipo_questao` para identificar o tipo de prova.

## Exemplos de Uso Completo

### 1. Questão para Concurso CESPE - Médio
```json
POST /api/questoes-ia/gerar-por-tema
{
  "tema_id": 1,
  "assunto": "Direito Administrativo - Licitações",
  "quantidade": 5,
  "nivel": "medio",
  "tipo_questao": "concurso",
  "banca": "CESPE/CEBRASPE"
}
```

### 2. Questão ENEM - Fácil
```json
POST /api/questoes-ia/gerar-por-tema
{
  "tema_id": 2,
  "assunto": "História do Brasil - Período Colonial",
  "quantidade": 3,
  "nivel": "facil",
  "tipo_questao": "enem"
}
```

### 3. Questão OAB - Difícil
```json
POST /api/questoes-ia/gerar-variacao
{
  "questao_exemplo": "João celebrou contrato de locação...",
  "assunto": "Direito Civil - Contratos",
  "quantidade": 2,
  "tema_id": 1,
  "nivel": "dificil",
  "tipo_questao": "oab",
  "banca": "FGV"
}
```

### 4. Questão CRC - Muito Difícil
```json
POST /api/questoes-ia/gerar-por-tema
{
  "tema_id": 3,
  "assunto": "Contabilidade Avançada - Consolidação",
  "quantidade": 4,
  "nivel": "muito_dificil",
  "tipo_questao": "prova_crc",
  "banca": "CFC"
}
```

### 5. Questão Customizada - Certificação
```json
POST /api/questoes-ia/gerar-por-tema
{
  "tema_id": 4,
  "assunto": "Gestão de Riscos em Projetos",
  "quantidade": 5,
  "nivel": "muito_dificil",
  "tipo_questao": "outros",
  "tipo_questao_outro": "Certificação PMP",
  "banca": "PMI"
}
```

## Resposta da API

A resposta inclui as questões geradas com todos os novos campos:

```json
{
  "success": true,
  "message": "Questões geradas com sucesso",
  "data": [
    {
      "id": 42,
      "tema_id": 1,
      "assunto": "Direito Administrativo - Licitações",
      "user_id": 1,
      "enunciado": "Considerando a Lei nº 14.133/2021...",
      "nivel": "concurso",
      "nivel_dificuldade": "medio",
      "tipo_questao": "concurso",
      "tipo_questao_outro": null,
      "banca": "CESPE/CEBRASPE",
      "explicacao": "A alternativa correta é...",
      "tipo_geracao": "ia_tema",
      "alternativas": [
        {
          "id": 168,
          "texto": "Alternativa A",
          "correta": false,
          "ordem": 1
        },
        // ...
      ]
    }
  ],
  "custo": 15,
  "saldo_restante": 85
}
```

## Frontend - Campos de Formulário

### Seletor de Tipo de Questão
```html
<select name="tipo_questao">
  <option value="concurso" selected>Concurso Público</option>
  <option value="enem">ENEM</option>
  <option value="prova_crc">Prova CRC</option>
  <option value="oab">Exame OAB</option>
  <option value="outros">Outros (especificar)</option>
</select>
```

### Campo Condicional - Tipo Customizado
```html
<!-- Mostrar apenas quando tipo_questao = 'outros' -->
<input 
  type="text" 
  name="tipo_questao_outro" 
  placeholder="Ex: Certificação PMP, Residência Médica, etc."
  maxlength="100"
/>
```

### Campo Opcional - Banca
```html
<input 
  type="text" 
  name="banca" 
  placeholder="Ex: CESPE, FCC, FGV, VUNESP..."
  maxlength="150"
/>
```

## Benefícios

1. **Personalização**: Questões adaptadas ao estilo específico de cada tipo de prova
2. **Precisão**: Banca realizadora influencia o formato e linguagem das questões
3. **Flexibilidade**: Campo "Outros" permite tipos de provas não listadas
4. **Contextualização**: Questões mais adequadas ao objetivo de estudo do usuário
5. **Retrocompatibilidade**: Sistema anterior continua funcionando normalmente

## Migration

```php
// 2025_10_18_145707_add_tipo_questao_and_banca_to_questoes_table.php

Schema::table('questoes', function (Blueprint $table) {
    $table->enum('tipo_questao', ['concurso', 'enem', 'prova_crc', 'oab', 'outros'])
        ->default('concurso')
        ->after('nivel_dificuldade');
    
    $table->string('tipo_questao_outro', 100)
        ->nullable()
        ->after('tipo_questao');
    
    $table->string('banca', 150)
        ->nullable()
        ->after('tipo_questao_outro');
});
```

## Testes Recomendados

1. ✅ Gerar questão de concurso sem banca
2. ✅ Gerar questão de concurso com banca CESPE
3. ✅ Gerar questão ENEM
4. ✅ Gerar questão OAB com banca FGV
5. ✅ Gerar questão CRC
6. ✅ Gerar questão customizada (outros) sem especificar tipo_questao_outro (deve falhar)
7. ✅ Gerar questão customizada com tipo_questao_outro e banca
8. ✅ Verificar retrocompatibilidade com questões antigas
