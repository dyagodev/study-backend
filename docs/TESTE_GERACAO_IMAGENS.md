# 🧪 Teste - Geração de Questões com Imagens

## Como Testar

### 1. Teste via Postman/Insomnia

```http
POST http://study.test/api/questoes/gerar-por-tema
Authorization: Bearer {seu_token}
Content-Type: application/json

{
  "tema_id": 1,
  "quantidade": 2,
  "nivel": "medio"
}
```

### 2. Teste via cURL

```bash
curl -X POST http://study.test/api/questoes/gerar-por-tema \
  -H "Authorization: Bearer {seu_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "tema_id": 1,
    "quantidade": 2,
    "nivel": "medio"
  }'
```

### 3. Verificar Resposta

A resposta deve incluir:

```json
{
  "success": true,
  "message": "Questões geradas com sucesso",
  "data": [
    {
      "id": 123,
      "enunciado": "Observe o gráfico...",
      "imagem_url": "questoes/imagens/abc123.png",
      "imagem_url_completa": "http://study.test/storage/questoes/imagens/abc123.png",
      "alternativas": [...]
    }
  ]
}
```

### 4. Visualizar a Imagem

Acesse a URL retornada em `imagem_url_completa`:
```
http://study.test/storage/questoes/imagens/abc123.png
```

## Temas Ideais para Teste

**Temas que provavelmente gerarão questões com imagens:**

1. **Matemática** (tema_id: 1)
   - Geometria
   - Funções e gráficos
   - Trigonometria

2. **Física** (tema_id: 2)
   - Cinemática
   - Eletricidade
   - Óptica

3. **Biologia** (tema_id: 3)
   - Citologia
   - Anatomia
   - Evolução

## Monitorar Logs

Terminal 1 - Acompanhar logs:
```bash
cd /Users/dyagoaraujo/Herd/study
tail -f storage/logs/laravel.log
```

Terminal 2 - Fazer requisição:
```bash
# Usar Postman ou cURL
```

## Verificar Storage

```bash
# Listar imagens geradas
ls -lh storage/app/public/questoes/imagens/

# Ver última imagem
ls -lt storage/app/public/questoes/imagens/ | head -2
```

## Exemplo de Resultado Esperado

### Questão SEM imagem:
```json
{
  "enunciado": "Qual é a fórmula da água?",
  "imagem_url": null,
  "imagem_url_completa": null,
  "alternativas": [...]
}
```

### Questão COM imagem:
```json
{
  "enunciado": "Observe o triângulo na figura. Qual é o valor de x?",
  "imagem_url": "questoes/imagens/def456.png",
  "imagem_url_completa": "http://study.test/storage/questoes/imagens/def456.png",
  "alternativas": [...]
}
```

## Troubleshooting

### Erro: "Erro ao gerar imagem"
- Verificar se a API key tem acesso ao DALL-E 3
- Verificar créditos na conta OpenAI

### Erro: "Erro ao salvar imagem"
- Verificar permissões: `chmod -R 775 storage`
- Verificar link simbólico: `php artisan storage:link`

### Imagem não carrega
- Verificar se o link está correto
- Verificar permissões do arquivo
- Testar URL diretamente no navegador

## Tempo Esperado

- **Sem imagem**: ~10-15 segundos
- **Com 1 imagem**: ~25-30 segundos
- **Com múltiplas imagens**: +15s por imagem adicional

## Custos Estimados

- **Geração de texto**: ~$0.01 por 1000 tokens
- **Geração de imagem**: ~$0.04 por imagem (DALL-E 3, 1024x1024)

**Exemplo**: Gerar 5 questões com 2 imagens = ~$0.08-$0.10

## Próximos Passos

Após testar e confirmar que funciona:

1. ✅ Testar geração básica
2. ✅ Verificar imagens geradas
3. ✅ Confirmar URLs acessíveis
4. 📝 Documentar exemplos reais
5. 🎨 Ajustar prompts se necessário
