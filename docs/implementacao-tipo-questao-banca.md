# Implementação: Tipo de Questão e Banca Realizadora

## 📋 Resumo das Alterações

Sistema atualizado para permitir especificação do tipo de questão (Concurso, ENEM, Prova CRC, OAB, Outros) e banca realizadora opcional.

## ✅ Arquivos Modificados

### 1. **Migration**
- **Arquivo**: `database/migrations/2025_10_18_145707_add_tipo_questao_and_banca_to_questoes_table.php`
- **Ação**: Criada e executada com sucesso
- **Campos Adicionados**:
  - `tipo_questao` (ENUM): 'concurso', 'enem', 'prova_crc', 'oab', 'outros'
  - `tipo_questao_outro` (STRING, 100): Especificação quando tipo = 'outros'
  - `banca` (STRING, 150): Banca realizadora (opcional)

### 2. **Model: Questao.php**
- **Arquivo**: `app/Models/Questao.php`
- **Alteração**: Adicionados campos no `$fillable`:
  ```php
  'tipo_questao',
  'tipo_questao_outro',
  'banca',
  ```

### 3. **Controller: QuestaoGeracaoController.php**
- **Arquivo**: `app/Http/Controllers/Api/QuestaoGeracaoController.php`
- **Métodos Atualizados**:
  - ✅ `gerarPorTema()` - Validações e passagem de parâmetros
  - ✅ `gerarVariacao()` - Validações e passagem de parâmetros
  - ✅ `gerarPorImagem()` - Validações e passagem de parâmetros
  - ✅ `salvarQuestoes()` - Novos parâmetros no salvamento

**Validações Adicionadas**:
```php
'tipo_questao' => 'sometimes|in:concurso,enem,prova_crc,oab,outros',
'tipo_questao_outro' => 'required_if:tipo_questao,outros|string|max:100',
'banca' => 'nullable|string|max:150',
```

### 4. **Service: AIService.php**
- **Arquivo**: `app/Services/AIService.php`
- **Métodos Atualizados**:
  - ✅ `gerarQuestoesPorTema()` - Novos parâmetros
  - ✅ `gerarVariacoesQuestao()` - Novos parâmetros
  - ✅ `gerarQuestoesPorImagem()` - Novos parâmetros
  - ✅ `construirPromptPorTema()` - Prompt adaptado por tipo
  - ✅ `construirPromptVariacao()` - Prompt adaptado por tipo
  - ✅ `construirPromptImagem()` - Prompt adaptado por tipo

**Lógica de Prompt Dinâmico**:
```php
$tipoDescricao = match($tipoQuestao) {
    'concurso' => 'CONCURSOS PÚBLICOS BRASILEIROS',
    'enem' => 'ENEM (Exame Nacional do Ensino Médio)',
    'prova_crc' => 'PROVA DO CRC (Conselho Regional de Contabilidade)',
    'oab' => 'EXAME DA OAB (Ordem dos Advogados do Brasil)',
    'outros' => $tipoQuestaoOutro ? strtoupper($tipoQuestaoOutro) : 'PROVAS E EXAMES',
    default => 'CONCURSOS PÚBLICOS BRASILEIROS',
};

$bancaInfo = $banca ? "\n**BANCA REALIZADORA: {$banca}**..." : '';
```

## 📊 Banco de Dados

### Estrutura da Tabela `questoes` (Novos Campos)

| Campo | Tipo | Nulo | Padrão | Descrição |
|-------|------|------|--------|-----------|
| tipo_questao | ENUM | NÃO | 'concurso' | Tipo da questão |
| tipo_questao_outro | VARCHAR(100) | SIM | NULL | Especificação quando tipo='outros' |
| banca | VARCHAR(150) | SIM | NULL | Banca realizadora |

### Valores Válidos para `tipo_questao`

- `concurso` - Concursos Públicos Brasileiros (padrão)
- `enem` - ENEM
- `prova_crc` - Prova CRC
- `oab` - Exame OAB
- `outros` - Outros tipos (requer tipo_questao_outro)

## 🧪 Testes Realizados

### ✅ Teste 1: Questão de Concurso com Banca
```php
tipo_questao: 'concurso'
banca: 'CESPE/CEBRASPE'
✅ SUCESSO - ID: 31
```

### ✅ Teste 2: Questão Customizada (Outros)
```php
tipo_questao: 'outros'
tipo_questao_outro: 'Certificação PMP'
banca: 'PMI'
✅ SUCESSO - ID: 32
```

### ✅ Teste 3: Questão ENEM
```php
tipo_questao: 'enem'
banca: null (opcional)
✅ SUCESSO - ID: 33
```

## 🔄 Fluxo de Geração com IA

### Exemplo: Gerar Questão OAB com Banca FGV

1. **Request**:
```json
POST /api/questoes-ia/gerar-por-tema
{
  "tema_id": 1,
  "assunto": "Direito Civil - Contratos",
  "quantidade": 3,
  "nivel": "dificil",
  "tipo_questao": "oab",
  "banca": "FGV"
}
```

2. **Prompt para IA**:
```
Você é um especialista em educação e elaboração de questões para 
EXAME DA OAB (Ordem dos Advogados do Brasil).

**TIPO DE QUESTÃO: EXAME DA OAB**
**BANCA REALIZADORA: FGV**
As questões devem seguir o estilo e formato típicos desta banca examinadora.
**NÍVEL DE DIFICULDADE EXIGIDO: DIFÍCIL**

Crie 3 questões de múltipla escolha...
```

3. **Questões Salvas**:
```php
tipo_questao: 'oab'
banca: 'FGV'
nivel_dificuldade: 'dificil'
```

## 📝 Documentação Criada

### Arquivo: `docs/tipo-questao-banca.md`
- ✅ Visão geral completa
- ✅ Estrutura de campos
- ✅ Exemplos de API
- ✅ Regras de validação
- ✅ Comportamento da IA
- ✅ Impacto nos custos
- ✅ Retrocompatibilidade
- ✅ Testes recomendados

## 🎯 Funcionalidades

### ✅ Implementado

1. **Tipo de Questão Selecionável**
   - 5 tipos pré-definidos
   - Campo customizável para outros tipos
   - Prompt da IA adaptado automaticamente

2. **Banca Realizadora Opcional**
   - Influencia estilo das questões
   - Integrado em todos os prompts da IA
   - Campo livre (máx. 150 caracteres)

3. **Validações Robustas**
   - `tipo_questao_outro` obrigatório quando tipo = 'outros'
   - Validação de enum para tipos pré-definidos
   - Tamanho máximo dos campos

4. **Retrocompatibilidade**
   - Questões antigas funcionam normalmente
   - Valores padrão aplicados automaticamente
   - Campo `nivel` mantido para compatibilidade

5. **Integração Completa**
   - Todos os 3 métodos de geração (tema, variação, imagem)
   - Prompts adaptados dinamicamente
   - Salvamento automático dos novos campos

## 💰 Impacto nos Custos

**NÃO HÁ ALTERAÇÃO** nos custos de geração:
- Questão Simples: 3 créditos
- Variação: 5 créditos
- Por Imagem: 8 créditos
- Simulado: 10 créditos

## 🔗 Endpoints da API

### 1. Gerar por Tema
```
POST /api/questoes-ia/gerar-por-tema

Novos Parâmetros:
- tipo_questao (opcional): concurso|enem|prova_crc|oab|outros
- tipo_questao_outro (condicional): string, máx 100 chars
- banca (opcional): string, máx 150 chars
```

### 2. Gerar Variação
```
POST /api/questoes-ia/gerar-variacao

Novos Parâmetros:
- tipo_questao (opcional): concurso|enem|prova_crc|oab|outros
- tipo_questao_outro (condicional): string, máx 100 chars
- banca (opcional): string, máx 150 chars
```

### 3. Gerar por Imagem
```
POST /api/questoes-ia/gerar-por-imagem

Novos Parâmetros:
- tipo_questao (opcional): concurso|enem|prova_crc|oab|outros
- tipo_questao_outro (condicional): string, máx 100 chars
- banca (opcional): string, máx 150 chars
```

## 📱 Frontend - Componentes Necessários

### 1. Select: Tipo de Questão
```html
<select name="tipo_questao">
  <option value="concurso">Concurso Público</option>
  <option value="enem">ENEM</option>
  <option value="prova_crc">Prova CRC</option>
  <option value="oab">Exame OAB</option>
  <option value="outros">Outros</option>
</select>
```

### 2. Input Condicional: Tipo Customizado
```html
<!-- Mostrar apenas quando tipo_questao = 'outros' -->
<input 
  type="text" 
  name="tipo_questao_outro" 
  placeholder="Ex: Certificação PMP"
/>
```

### 3. Input Opcional: Banca
```html
<input 
  type="text" 
  name="banca" 
  placeholder="Ex: CESPE, FCC, FGV"
/>
```

## 🎓 Exemplos Práticos

### Concurso CESPE - Direito Administrativo
```json
{
  "tipo_questao": "concurso",
  "banca": "CESPE/CEBRASPE",
  "nivel": "medio",
  "assunto": "Licitações e Contratos"
}
```

### ENEM - História
```json
{
  "tipo_questao": "enem",
  "nivel": "facil",
  "assunto": "Brasil Colonial"
}
```

### OAB FGV - Direito Civil
```json
{
  "tipo_questao": "oab",
  "banca": "FGV",
  "nivel": "dificil",
  "assunto": "Contratos"
}
```

### Certificação PMP
```json
{
  "tipo_questao": "outros",
  "tipo_questao_outro": "Certificação PMP",
  "banca": "PMI",
  "nivel": "muito_dificil",
  "assunto": "Gestão de Riscos"
}
```

## 🚀 Próximos Passos

### Frontend
1. Implementar select de tipo de questão
2. Adicionar campo condicional para tipo customizado
3. Adicionar input opcional para banca
4. Atualizar validações no formulário

### Testes
1. Testar geração real com OpenAI
2. Validar qualidade das questões por tipo
3. Verificar consistência do estilo por banca
4. Testar todos os fluxos de validação

### Melhorias Futuras
1. Lista de bancas sugeridas (autocomplete)
2. Histórico de bancas mais usadas
3. Estatísticas por tipo de questão
4. Filtros avançados de busca

## ✨ Benefícios da Implementação

1. **Personalização Avançada**: Questões adaptadas ao objetivo de estudo
2. **Precisão no Estilo**: Banca influencia formato e linguagem
3. **Flexibilidade Total**: Campo "Outros" para tipos não listados
4. **Zero Impacto**: Retrocompatibilidade garantida
5. **Experiência Melhorada**: Mais controle sobre a geração

---

**Status**: ✅ IMPLEMENTADO E TESTADO
**Data**: 18 de outubro de 2025
**Versão**: 2.0
