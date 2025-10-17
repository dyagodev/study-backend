# 🔍 Debug ValidaPay API - Guia de Troubleshooting

## Erro 400: Bad Request

O erro 400 geralmente indica que a requisição está mal formatada ou falta algum parâmetro obrigatório.

---

## 🧪 Testes Passo a Passo

### 1. Testar Autenticação

```bash
php artisan tinker
```

```php
$service = app(\App\Services\ValidaPayService::class);

// Isso deve retornar um token JWT
$token = $service->getAccessToken();
echo $token;
```

**Resultado esperado**: String JWT longa (ex: `eyJhbGciOiJSUzI1NiIs...`)

**Se der erro**:
- Verifique `VALIDAPAY_CLIENT_ID` no `.env`
- Verifique `VALIDAPAY_CLIENT_SECRET` no `.env`
- Verifique `VALIDAPAY_AUTH_URL` (deve ser `https://auth.validapay.com.br`)

---

### 2. Testar Criação de Cobrança

```php
// No tinker
$service = app(\App\Services\ValidaPayService::class);

try {
    $resultado = $service->criarCobranca(
        valor: 0.01, // R$ 0,01 para teste
        webhookUrl: 'https://webhook.site/seu-uuid' // Crie em webhook.site
    );
    
    print_r($resultado);
} catch (\Exception $e) {
    echo "ERRO: " . $e->getMessage();
}
```

**Resultado esperado**:
```php
Array
(
    [transactionId] => 2774031695
    [emv] => 00020101021226890014br.gov.bcb.pix...
)
```

**Se der erro 400**, verifique os logs:
```bash
tail -f storage/logs/laravel.log
```

---

## 🔍 Checklist de Validação

### Variáveis de Ambiente

```bash
# Verifique no .env
cat .env | grep VALIDAPAY
```

Deve mostrar:
```
VALIDAPAY_AUTH_URL=https://auth.validapay.com.br
VALIDAPAY_API_URL=https://api.validapay.com.br
VALIDAPAY_CLIENT_ID=4j74tmi6uio0cn5ch51gpuq19a
VALIDAPAY_CLIENT_SECRET=185o0gomaciheichr98fvg3old42s71pa48je0dt6eol9maf9kb0
VALIDAPAY_ACCOUNT_NUMBER=447975871
```

### Config Cache

Se mudou o `.env`, limpe o cache:
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan optimize:clear
```

### Testar Config

```bash
php artisan tinker
```

```php
config('services.validapay');
// Deve mostrar todas as configs
```

---

## 🐛 Possíveis Causas do Erro 400

### 1. **Campo `amount` inválido**

A API pode exigir:
- Valor mínimo (ex: R$ 0,01 ou R$ 1,00)
- Valor máximo
- Formato específico (float vs string)

**Teste**:
```php
// Tente diferentes valores
$service->criarCobranca(valor: 1.00, webhookUrl: '...');
$service->criarCobranca(valor: 10.00, webhookUrl: '...');
```

### 2. **Header `X-Account-Number` incorreto**

Verifique se o número da conta está correto:
```php
config('services.validapay.account_number'); // Deve retornar 447975871
```

### 3. **URL de webhook inválida**

A API pode exigir:
- URL pública (não localhost)
- HTTPS obrigatório
- Formato específico

**Teste sem webhook**:
```php
$service->criarCobranca(valor: 1.00, webhookUrl: null);
```

### 4. **Campo `split` com formato errado**

Se a API não usar split, pode rejeitar array vazio.

**Teste**: Remova temporariamente do request:
```php
// Em ValidaPayService.php, linha ~95
$body = [
    'amount' => (float) $valor,
    'webhook_url' => $webhookUrl,
    // 'split' => $split, // COMENTAR TEMPORARIAMENTE
];
```

### 5. **Scope OAuth2 incorreto**

Pode ser que o scope `pix.cob/write` esteja errado.

**Teste sem scope**:
```php
// Em ValidaPayService.php, linha ~40
$response = Http::asForm()->post("{$this->authUrl}/oauth2/token", [
    'grant_type' => 'client_credentials',
    'client_id' => $this->clientId,
    'client_secret' => $this->clientSecret,
    // 'scope' => 'pix.cob/write', // COMENTAR TEMPORARIAMENTE
]);
```

### 6. **Endpoint incorreto**

Pode ser que o endpoint real seja diferente.

**Testes alternativos**:
```php
// Teste 1: Sem query string
->post("{$this->apiUrl}/pix", $body);

// Teste 2: Endpoint diferente
->post("{$this->apiUrl}/pix/create", $body);

// Teste 3: Versão da API
->post("{$this->apiUrl}/v1/pix", $body);
```

---

## 📝 Logs Detalhados

Com os logs melhorados, você verá:

```
[2025-10-17 22:30:00] local.INFO: ValidaPay: Solicitando novo token
[2025-10-17 22:30:01] local.INFO: ValidaPay: Token gerado com sucesso {"expires_in":3600}
[2025-10-17 22:30:01] local.INFO: ValidaPay: Criando cobrança PIX {"url":"...","body":{...}}
[2025-10-17 22:30:02] local.ERROR: Erro ao criar cobrança ValidaPay {"status":400,"body":"...","json":{...}}
```

A chave é o campo `json` no erro - ele terá a mensagem específica da API.

---

## 🔧 Script de Teste Completo

Crie `test-validapay.php` na raiz:

```php
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\ValidaPayService;
use Illuminate\Support\Facades\Log;

$service = app(ValidaPayService::class);

echo "=== TESTE VALIDAPAY ===\n\n";

// 1. Testar autenticação
echo "1. Testando autenticação...\n";
try {
    $token = $service->getAccessToken();
    echo "✅ Token obtido: " . substr($token, 0, 30) . "...\n\n";
} catch (\Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n\n";
    exit(1);
}

// 2. Testar criação
echo "2. Testando criação de cobrança...\n";
try {
    $resultado = $service->criarCobranca(
        valor: 1.00,
        webhookUrl: 'https://webhook.site/test'
    );
    
    echo "✅ Cobrança criada!\n";
    echo "   Transaction ID: " . $resultado['transactionId'] . "\n";
    echo "   EMV: " . substr($resultado['emv'], 0, 50) . "...\n\n";
} catch (\Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n\n";
    echo "Verifique os logs: tail -f storage/logs/laravel.log\n";
    exit(1);
}

echo "=== SUCESSO! ===\n";
```

Execute:
```bash
php test-validapay.php
```

---

## 📞 Contatar Suporte ValidaPay

Se nada funcionar, você precisa:

1. **Documentação da API**
   - Solicite a documentação oficial completa
   - Exemplo de requisição cURL funcionando
   - Lista de códigos de erro

2. **Credenciais de Teste**
   - Ambiente sandbox/homologação
   - Credenciais de teste
   - Valores permitidos para teste

3. **Informações Específicas**
   - Formato exato do campo `amount` (float? string? centavos?)
   - Campo `split` é obrigatório?
   - Webhook URL precisa ser HTTPS?
   - Há limite de valor mínimo/máximo?
   - Header `X-Account-Number` está correto?

---

## 🔄 Próximos Passos

1. **Execute os testes acima** e anote os resultados
2. **Copie os logs completos** do Laravel
3. **Teste com cURL direto** (se tiver exemplo da API)
4. **Entre em contato com suporte** com essas informações

---

## 📧 Template para Suporte

```
Assunto: Erro 400 ao criar cobrança PIX via API

Olá equipe ValidaPay,

Estou integrando a API de PIX e recebo erro 400 ao criar cobrança.

Endpoint usado: POST https://api.validapay.com.br/pix?eventType=cob_pix

Headers:
- Authorization: Bearer {token_obtido_com_sucesso}
- X-Account-Number: 447975871
- Content-Type: application/json

Body:
{
  "amount": 1.00,
  "webhook_url": "https://meusite.com/webhook",
  "split": []
}

Resposta: HTTP 400 Bad Request

Poderiam me informar:
1. O formato correto do campo "amount"?
2. O campo "split" é obrigatório?
3. Há valor mínimo para cobrança?
4. Webhook URL precisa ser HTTPS?
5. O endpoint está correto?

Agradeço a ajuda!
```

---

## ✅ Resolução

Uma vez identificado o problema:

1. Atualize `ValidaPayService.php`
2. Teste novamente
3. Documente a solução
4. Atualize este arquivo de debug

Boa sorte! 🚀
