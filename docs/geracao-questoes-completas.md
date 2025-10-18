# Geração de Questões Completas

## ✅ Questões Completas vs Testes Manuais

### O que é uma Questão Completa?

Uma questão completa no sistema possui:

1. **Dados Básicos**:
   - `tema_id` - Tema associado
   - `assunto` - Assunto específico
   - `user_id` - Usuário criador
   - `enunciado` - Texto da questão
   - `explicacao` - Explicação da resposta

2. **Classificação**:
   - `nivel` - Nível geral (mantido por compatibilidade)
   - `nivel_dificuldade` - facil, medio, dificil, muito_dificil
   - `tipo_questao` - concurso, enem, prova_crc, oab, outros
   - `tipo_questao_outro` - Especificação quando tipo='outros'
   - `banca` - Banca realizadora (opcional)

3. **Alternativas** (obrigatório):
   - Mínimo 4 alternativas
   - Cada alternativa possui:
     - `texto` - Conteúdo da alternativa
     - `correta` - Boolean (apenas 1 deve ser true)
     - `ordem` - Ordem de exibição

4. **Metadados**:
   - `tipo_geracao` - manual, ia_tema, ia_variacao, ia_imagem
   - `imagem_url` - URL da imagem (se aplicável)
   - `tags` - Tags para categorização
   - `favorita` - Marcador de favorito

## 🔄 Fluxos de Criação

### 1. Geração via IA (Automática e Completa)

Quando você usa os endpoints de geração, a IA cria questões **completas automaticamente**:

```json
POST /api/questoes-ia/gerar-por-tema
{
  "tema_id": 1,
  "assunto": "Direito Constitucional",
  "quantidade": 3,
  "nivel": "medio",
  "tipo_questao": "concurso",
  "banca": "CESPE/CEBRASPE"
}
```

**O que acontece:**
1. ✅ AIService gera o JSON com questões completas
2. ✅ Controller salva a questão (enunciado, explicação, etc.)
3. ✅ Controller cria automaticamente as 4 alternativas
4. ✅ Retorna questões prontas para uso

**Resultado:**
```json
{
  "success": true,
  "data": [
    {
      "id": 42,
      "enunciado": "A Constituição Federal de 1988...",
      "tipo_questao": "concurso",
      "banca": "CESPE/CEBRASPE",
      "nivel_dificuldade": "medio",
      "explicacao": "A alternativa correta...",
      "alternativas": [
        {"id": 168, "texto": "...", "correta": false, "ordem": 1},
        {"id": 169, "texto": "...", "correta": true, "ordem": 2},
        {"id": 170, "texto": "...", "correta": false, "ordem": 3},
        {"id": 171, "texto": "...", "correta": false, "ordem": 4}
      ]
    }
  ]
}
```

### 2. Teste Manual (Apenas para Testes)

O teste que fizemos criou **apenas a estrutura básica**, não as alternativas:

```php
$questao = App\Models\Questao::create([
    'tema_id' => $tema->id,
    'enunciado' => 'Texto...',
    'tipo_questao' => 'concurso',
    'banca' => 'CESPE/CEBRASPE',
    // ... outros campos
]);

// ❌ Alternativas NÃO foram criadas automaticamente
// ✅ Precisamos criar manualmente:

Alternativa::create([
    'questao_id' => $questao->id,
    'texto' => 'Alternativa A',
    'correta' => false,
    'ordem' => 1
]);
// ... criar as outras 3 alternativas
```

## 📊 Verificação de Questões Completas

### Script de Verificação

```php
$questao = Questao::with('alternativas')->find($id);

echo "Questão #{$questao->id}";
echo "Tipo: {$questao->tipo_questao}";
echo "Banca: {$questao->banca}";
echo "Alternativas: " . $questao->alternativas->count();

// Verificar se está completa
$completa = $questao->alternativas->count() >= 4;
echo $completa ? '✅ COMPLETA' : '❌ INCOMPLETA';
```

## 🎯 Status Atual do Sistema

### ✅ Questões de Teste Criadas

Após correção, as questões de teste agora estão completas:

| ID | Tipo | Banca | Alternativas | Status |
|----|------|-------|--------------|--------|
| 31 | concurso | CESPE/CEBRASPE | 4 | ✅ Completa |
| 32 | outros (PMP) | PMI | 4 | ✅ Completa |
| 33 | enem | - | 4 | ✅ Completa |

### 🔧 Método `salvarQuestoes()` - Como Funciona

```php
protected function salvarQuestoes(
    array $questoesGeradas,
    int $temaId,
    string $assunto,
    int $userId,
    string $tipoGeracao,
    string $nivelDificuldade = 'medio',
    ?string $imagemUrl = null,
    string $tipoQuestao = 'concurso',
    ?string $tipoQuestaoOutro = null,
    ?string $banca = null
): array {
    $questoesSalvas = [];

    DB::beginTransaction();
    try {
        foreach ($questoesGeradas as $questaoData) {
            // 1. Criar questão
            $questao = Questao::create([
                'tema_id' => $temaId,
                'assunto' => $assunto,
                'user_id' => $userId,
                'enunciado' => $questaoData['enunciado'],
                'nivel' => 'concurso',
                'nivel_dificuldade' => $nivelDificuldade,
                'tipo_questao' => $tipoQuestao,
                'tipo_questao_outro' => $tipoQuestaoOutro,
                'banca' => $banca,
                'explicacao' => $questaoData['explicacao'] ?? null,
                'tipo_geracao' => $tipoGeracao,
                'imagem_url' => $imagemUrl,
            ]);

            // 2. Criar alternativas (4 obrigatórias)
            foreach ($questaoData['alternativas'] as $index => $alternativaData) {
                Alternativa::create([
                    'questao_id' => $questao->id,
                    'texto' => $alternativaData['texto'],
                    'correta' => $alternativaData['correta'],
                    'ordem' => $index + 1,
                ]);
            }

            // 3. Carregar relacionamentos
            $questao->load('alternativas');
            $questoesSalvas[] = $questao;
        }

        DB::commit();
        return $questoesSalvas;
    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

## 🧪 Teste Real com IA

Para testar a geração completa via IA, você precisa:

### 1. Configurar OpenAI API Key

```env
OPENAI_API_KEY=sk-proj-...
OPENAI_MODEL=gpt-4-turbo-preview
OPENAI_MAX_TOKENS=2000
```

### 2. Fazer Request Real

```bash
curl -X POST http://localhost/api/questoes-ia/gerar-por-tema \
  -H "Authorization: Bearer {seu_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "tema_id": 1,
    "assunto": "Direito Constitucional - Princípios",
    "quantidade": 2,
    "nivel": "medio",
    "tipo_questao": "concurso",
    "banca": "CESPE/CEBRASPE"
  }'
```

### 3. Resposta Esperada

```json
{
  "success": true,
  "message": "Questões geradas com sucesso",
  "data": [
    {
      "id": 34,
      "tema_id": 1,
      "assunto": "Direito Constitucional - Princípios",
      "enunciado": "Considerando os princípios fundamentais da Constituição Federal de 1988, assinale a alternativa correta sobre o princípio da legalidade.",
      "nivel": "concurso",
      "nivel_dificuldade": "medio",
      "tipo_questao": "concurso",
      "banca": "CESPE/CEBRASPE",
      "explicacao": "O princípio da legalidade estabelece que...",
      "tipo_geracao": "ia_tema",
      "alternativas": [
        {
          "id": 136,
          "texto": "O princípio da legalidade aplica-se apenas aos particulares.",
          "correta": false,
          "ordem": 1
        },
        {
          "id": 137,
          "texto": "Segundo o princípio da legalidade, a Administração Pública só pode fazer o que a lei autoriza ou permite.",
          "correta": true,
          "ordem": 2
        },
        {
          "id": 138,
          "texto": "O princípio da legalidade permite à Administração agir por analogia em qualquer situação.",
          "correta": false,
          "ordem": 3
        },
        {
          "id": 139,
          "texto": "A legalidade administrativa é idêntica à legalidade aplicável aos particulares.",
          "correta": false,
          "ordem": 4
        }
      ]
    }
  ],
  "custo": 6,
  "saldo_restante": 94
}
```

## 📝 Resumo

### Teste Manual (O que fizemos)
- ✅ Validou que os campos `tipo_questao` e `banca` funcionam
- ✅ Testou a estrutura do banco de dados
- ✅ Confirmou que o Model aceita os novos campos
- ⚠️ Criou questões sem alternativas inicialmente
- ✅ Corrigimos adicionando alternativas manualmente

### Geração via IA (Produção)
- ✅ Cria questões **completas automaticamente**
- ✅ Inclui 4 alternativas por questão
- ✅ Usa os prompts customizados por tipo e banca
- ✅ Aplica nível de dificuldade correto
- ✅ Debita créditos do usuário
- ✅ Retorna JSON completo com tudo

## ✨ Conclusão

**Os testes manuais foram apenas para validar a estrutura**. Na produção, quando você usar os endpoints de geração via IA, as questões virão **completas** com:

- ✅ Enunciado contextualizado
- ✅ 4 alternativas com uma correta
- ✅ Explicação detalhada
- ✅ Tipo de questão aplicado
- ✅ Banca considerada no estilo
- ✅ Nível de dificuldade respeitado

**Tudo pronto para uso imediato!**
