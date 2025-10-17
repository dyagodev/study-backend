# 🎨 Geração Automática de Imagens nas Questões

## Visão Geral

O sistema agora suporta **geração automática de imagens** usando DALL-E 3 da OpenAI. Quando uma questão **menciona uma imagem no enunciado** (exemplo: "Observe a figura...", "Analise o gráfico..."), o sistema automaticamente:

1. **Detecta** que o enunciado requer uma imagem
2. Gera um prompt para DALL-E 3 (baseado no enunciado ou fornecido pela IA)
3. **Cria a imagem** usando DALL-E 3
4. **Salva localmente** em `storage/app/public/questoes/imagens/`
5. **Associa** à questão no campo `imagem_url`

## Como Funciona

### 1. Durante a Geração de Questões

Quando você solicita a geração de questões por tema:

```http
POST /api/questoes/gerar-por-tema
{
  "tema_id": 1,
  "quantidade": 5,
  "nivel": "medio"
}
```

A IA pode criar questões que mencionam imagens no enunciado:

```json
{
  "enunciado": "Observe o triângulo retângulo na figura. Qual é o valor do ângulo X?",
  "alternativas": [...],
  "explicacao": "...",
  "prompt_imagem": "(opcional) A right triangle with a 90-degree angle..."
}
```

### 2. Detecção Automática

O sistema **detecta automaticamente** quando o enunciado menciona:
- 📊 "figura", "imagem", "gráfico", "diagrama"
- 👁️ "observe", "analise", "veja", "considere"
- 📐 "desenho", "esquema", "tabela", "mapa"
- 📷 "foto", "ilustração", "representado"

### 3. Geração da Imagem

Quando detectado, o sistema:

1. **Usa prompt fornecido** pela IA (se disponível no campo `prompt_imagem`)
2. **OU gera prompt automaticamente** baseado no enunciado + alternativas
3. **Chama DALL-E 3** com o prompt em inglês
4. **Faz download** da imagem gerada
5. **Salva** em `storage/app/public/questoes/imagens/`
6. **Associa** à questão no campo `imagem_url`

### 3. Acesso à Imagem

A questão retornada incluirá **dois campos de imagem**:

```json
{
  "id": 123,
  "enunciado": "Observe o triângulo retângulo na figura. Qual é o valor de X?",
  "imagem_url": null,  // Imagem ORIGINAL (se foi enviada pelo usuário)
  "imagem_url_completa": null,
  "imagem_gerada_url": "questoes/imagens/abc123.png",  // Imagem GERADA pela IA
  "imagem_gerada_url_completa": "http://study.test/storage/questoes/imagens/abc123.png",
  "alternativas": [...]
}
```

**Diferença entre os campos:**
- `imagem_url` / `imagem_url_completa`: Imagem **enviada** pelo usuário (origem)
- `imagem_gerada_url` / `imagem_gerada_url_completa`: Imagem **gerada** pela IA (resultado)

## Tipos de Imagens Geradas

A IA pode gerar imagens para:

### 📊 Matemática
- Gráficos de funções
- Figuras geométricas
- Diagramas de teoremas
- Representações de problemas

### 🔬 Ciências
- Diagramas de células
- Ciclos biológicos
- Estruturas moleculares
- Experimentos ilustrados

### 🌍 Geografia
- Mapas temáticos
- Representações de relevo
- Diagramas climáticos

### 📐 Física
- Diagramas de força
- Circuitos elétricos
- Trajetórias de movimento

## Configuração

### Pré-requisitos

1. **API Key da OpenAI** com acesso a DALL-E 3
2. **Link simbólico do storage** (já configurado):
   ```bash
   php artisan storage:link
   ```

### Custos

- **DALL-E 3**: ~$0.040 por imagem (1024x1024, quality: standard)
- A geração de imagens só ocorre quando **realmente necessário**

## Exemplos de Uso

### Exemplo 1: Questão de Geometria

**Solicitação:**
```http
POST /api/questoes/gerar-por-tema
{
  "tema_id": 1,
  "quantidade": 1,
  "nivel": "medio"
}
```

**Resultado:**
```json
{
  "enunciado": "Observe o triângulo equilátero ABCD na figura. Se cada lado mede 6 cm, qual é a área do triângulo?",
  "imagem_url": null,
  "imagem_url_completa": null,
  "imagem_gerada_url": "questoes/imagens/xyz789.png",
  "imagem_gerada_url_completa": "http://study.test/storage/questoes/imagens/xyz789.png",
  "alternativas": [
    {"texto": "9√3 cm²", "correta": true},
    {"texto": "18 cm²", "correta": false},
    {"texto": "12√3 cm²", "correta": false},
    {"texto": "6√3 cm²", "correta": false}
  ]
}
```

### Exemplo 2: Questão de Biologia

**Resultado:**
```json
{
  "enunciado": "Observe o diagrama do ciclo celular. Em qual fase ocorre a duplicação do DNA?",
  "imagem_url": null,
  "imagem_url_completa": null,
  "imagem_gerada_url": "questoes/imagens/bio456.png",
  "imagem_gerada_url_completa": "http://study.test/storage/questoes/imagens/bio456.png",
  "alternativas": [
    {"texto": "Interfase (fase S)", "correta": true},
    {"texto": "Prófase", "correta": false},
    {"texto": "Metáfase", "correta": false},
    {"texto": "Anáfase", "correta": false}
  ]
}
```

### Exemplo 3: Questão com Imagem Enviada + Gerada

Quando o usuário envia uma imagem para análise:

**Solicitação:**
```http
POST /api/questoes/gerar-por-imagem
{
  "imagem": [arquivo de imagem],
  "tema_id": 3,
  "contexto": "Biologia celular"
}
```

**Resultado:**
```json
{
  "enunciado": "Na imagem apresentada, identifique a estrutura celular responsável pela produção de energia.",
  "imagem_url": "questoes/imagens/original_123.jpg",  // Imagem ENVIADA
  "imagem_url_completa": "http://study.test/storage/questoes/imagens/original_123.jpg",
  "imagem_gerada_url": null,  // Neste caso não gerou nova imagem
  "imagem_gerada_url_completa": null,
  "alternativas": [...]
}
```

## Tratamento de Erros

Se a geração de imagem falhar:
- ✅ A questão **ainda é criada** (sem imagem)
- ⚠️ Um aviso é registrado nos logs
- 👤 O usuário recebe a questão normalmente

Isso garante que falhas na geração de imagem não impeçam a criação de questões.

## Melhorias Futuras

Possíveis melhorias para implementar:

1. **Cache de imagens similares** - Reutilizar imagens já geradas
2. **Diferentes tamanhos** - 512x512, 1024x1024, 1792x1024
3. **Edição de imagens** - Permitir regenerar/editar imagens
4. **Biblioteca de imagens** - Banco de imagens pré-geradas
5. **Otimização de prompts** - Melhorar qualidade das imagens geradas

## Logs e Debug

Para verificar a geração de imagens, consulte:

```bash
tail -f storage/logs/laravel.log | grep "imagem"
```

Mensagens comuns:
- `Erro ao gerar imagem para questão: [erro]` - Falha na geração
- `Erro ao salvar imagem de URL: [erro]` - Falha no download

## Performance

- **Tempo médio**: 10-15 segundos por imagem
- **Timeout configurado**: 60 segundos
- **Processamento**: Assíncrono para cada questão
- **Impacto**: Questões com imagem levam ~15s a mais para gerar

## Resumo

✅ **Automático** - IA decide quando imagem é necessária  
✅ **Integrado** - Funciona com geração por tema  
✅ **Resiliente** - Falhas não impedem criação da questão  
✅ **Transparente** - URLs completas retornadas automaticamente  
✅ **Escalável** - Imagens salvas localmente  

🎨 **O sistema agora cria questões visuais automaticamente!**
