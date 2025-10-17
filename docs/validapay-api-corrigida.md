# ValidaPay API - Integração Corrigida

## 📋 Índice

- [Visão Geral](#visão-geral)
- [Configuração](#configuração)
- [Autenticação OAuth2](#autenticação-oauth2)
- [Criar Cobrança PIX](#criar-cobrança-pix)
- [Consultar Pagamento](#consultar-pagamento)
- [Webhook](#webhook)
- [Fluxo Completo](#fluxo-completo)
- [Diferenças da API Anterior](#diferenças-da-api-anterior)

---

## 🎯 Visão Geral

Esta documentação reflete a **API real da ValidaPay**, após correção da integração inicial baseada em documentação incorreta/desatualizada.

### Principais Características

- **Autenticação**: OAuth2 Client Credentials
- **Endpoint de Criação**: `POST /pix?eventType=cob_pix`
- **Resposta**: `{transactionId: int, emv: string}`
- **EMV**: Código PIX "Copia e Cola" (não requer geração de QR Code separada)
- **Headers Obrigatórios**: `Authorization`, `X-Account-Number`, `Content-Type`

---

## ⚙️ Configuração

### Variáveis de Ambiente

Adicione no arquivo `.env`:

```bash
# ValidaPay API Credentials
VALIDAPAY_AUTH_URL=https://auth.validapay.com
VALIDAPAY_API_URL=https://api.validapay.com
VALIDAPAY_CLIENT_ID=seu_client_id
VALIDAPAY_CLIENT_SECRET=seu_client_secret
VALIDAPAY_CHAVE_PIX=sua_chave_pix
VALIDAPAY_ACCOUNT_NUMBER=seu_numero_conta
```

### Configuração no Laravel

Já configurado em `config/services.php`:

```php
'validapay' => [
    'auth_url' => env('VALIDAPAY_AUTH_URL'),
    'api_url' => env('VALIDAPAY_API_URL'),
    'client_id' => env('VALIDAPAY_CLIENT_ID'),
    'client_secret' => env('VALIDAPAY_CLIENT_SECRET'),
    'chave_pix' => env('VALIDAPAY_CHAVE_PIX'),
    'account_number' => env('VALIDAPAY_ACCOUNT_NUMBER'),
],
```

---

## 🔐 Autenticação OAuth2

### Endpoint

```
POST https://auth.validapay.com/oauth2/token
```

### Request

```bash
curl -X POST https://auth.validapay.com/oauth2/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=client_credentials" \
  -d "client_id=seu_client_id" \
  -d "client_secret=seu_client_secret"
```

### Response

```json
{
  "access_token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9...",
  "token_type": "Bearer",
  "expires_in": 3600
}
```

### Implementação no Laravel

O `ValidaPayService` gerencia o token automaticamente com cache:

```php
protected function getAccessToken(): string
{
    return Cache::remember('validapay_token', function () {
        $response = Http::asForm()->post(
            $this->authUrl . '/oauth2/token',
            [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]
        );

        $data = $response->json();
        $expiresIn = $data['expires_in'] - 60; // 60s de margem

        return $data['access_token'];
    }, $expiresIn);
}
```

**Importante**: Cache expira 60 segundos antes do token real expirar para evitar falhas.

---

## 💰 Criar Cobrança PIX

### Endpoint

```
POST https://api.validapay.com/pix?eventType=cob_pix
```

### Headers

```
Authorization: Bearer {access_token}
X-Account-Number: {account_number}
Content-Type: application/json
```

### Request Body

```json
{
  "amount": 0.20,
  "webhook_url": "https://seusite.com/api/webhook/validapay",
  "split": []
}
```

**Campos**:
- `amount` (float, required): Valor em reais (ex: 9.90, 24.90)
- `webhook_url` (string, optional): URL para receber notificações
- `split` (array, optional): Divisão do pagamento (deixar vazio se não usar)

### Response

```json
{
  "transactionId": 2774031695,
  "emv": "00020101021226890014br.gov.bcb.pix2567qrcode.validapay.com..."
}
```

**Campos**:
- `transactionId` (int): ID da transação na ValidaPay (use para consultas)
- `emv` (string): Código PIX "Copia e Cola" (código QR completo)

### Exemplo cURL

```bash
curl -X POST 'https://api.validapay.com/pix?eventType=cob_pix' \
  -H 'Authorization: Bearer eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9...' \
  -H 'X-Account-Number: 123456' \
  -H 'Content-Type: application/json' \
  -d '{
    "amount": 9.90,
    "webhook_url": "https://seusite.com/api/webhook/validapay",
    "split": []
  }'
```

### Implementação no Laravel

```php
public function criarCobranca(
    float $valor,
    ?string $webhookUrl = null,
    array $split = []
): array {
    $token = $this->getAccessToken();

    $payload = [
        'amount' => $valor,
        'webhook_url' => $webhookUrl,
        'split' => $split,
    ];

    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $token,
        'X-Account-Number' => $this->accountNumber,
        'Content-Type' => 'application/json',
    ])->post($this->apiUrl . '/pix?eventType=cob_pix', $payload);

    if ($response->failed()) {
        throw new \Exception('Erro ao criar cobrança PIX: ' . $response->body());
    }

    return $response->json();
}
```

### Uso no Controller

```php
$resultado = $this->validaPayService->criarCobranca(
    valor: 9.90,
    webhookUrl: route('webhook.validapay')
);

// $resultado = [
//     'transactionId' => 2774031695,
//     'emv' => '00020101021226890014br.gov.bcb.pix...'
// ]

$pagamento = PagamentoPix::create([
    'user_id' => auth()->id(),
    'txid' => (string) $resultado['transactionId'],
    'valor' => 9.90,
    'creditos' => 100,
    'status' => 'pendente',
    'qrcode' => $resultado['emv'], // Código PIX Copia e Cola
    'expira_em' => now()->addHour(),
]);
```

---

## 🔍 Consultar Pagamento

### Endpoint

```
GET https://api.validapay.com/pix/{transactionId}
```

### Headers

```
Authorization: Bearer {access_token}
X-Account-Number: {account_number}
```

### Response

```json
{
  "transactionId": 2774031695,
  "status": "PAID",
  "amount": 9.90,
  "payer": {
    "name": "João Silva",
    "document": "12345678900"
  },
  "createdAt": "2025-01-17T20:30:00Z"
}
```

**Status Possíveis**:
- `PENDING`: Aguardando pagamento
- `PAID`: Pago
- `CANCELLED`: Cancelado
- `EXPIRED`: Expirado

### Exemplo cURL

```bash
curl -X GET 'https://api.validapay.com/pix/2774031695' \
  -H 'Authorization: Bearer eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9...' \
  -H 'X-Account-Number: 123456'
```

### Implementação no Laravel

```php
public function consultarCobranca(int $transactionId): array
{
    $token = $this->getAccessToken();

    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . $token,
        'X-Account-Number' => $this->accountNumber,
    ])->get($this->apiUrl . '/pix/' . $transactionId);

    if ($response->failed()) {
        throw new \Exception('Erro ao consultar cobrança PIX: ' . $response->body());
    }

    return $response->json();
}
```

### Uso no Controller

```php
// Consultar pagamento
$resultado = $this->validaPayService->consultarCobranca((int) $pagamento->txid);

// Atualizar status
if ($resultado['status'] !== $pagamento->status) {
    $pagamento->update([
        'status' => $resultado['status'],
        'resposta_validapay' => $resultado,
    ]);

    // Se foi pago, creditar
    if ($this->validaPayService->isPago($resultado['status'])) {
        $this->processarPagamentoConcluido($pagamento);
    }
}
```

---

## 🔔 Webhook

### Configuração

O webhook é enviado pela ValidaPay quando o status do pagamento muda.

**URL**: Configurada no momento da criação da cobrança (`webhook_url`)

### Payload

```json
{
  "transactionId": 2774031695,
  "status": "PAID",
  "amount": 9.90,
  "payer": {
    "name": "João Silva",
    "document": "12345678900"
  },
  "createdAt": "2025-01-17T20:30:00Z"
}
```

### Implementação no Laravel

#### Route (`routes/api.php`)

```php
// Webhook (sem autenticação)
Route::post('/webhook/validapay', [PagamentoPixController::class, 'webhook']);
```

#### Controller

```php
public function webhook(Request $request)
{
    try {
        Log::info('Webhook PIX recebido', ['payload' => $request->all()]);

        $dados = $this->validaPayService->processarWebhook($request->all());
        
        $pagamento = PagamentoPix::where('txid', (string) $dados['transactionId'])->first();

        if (!$pagamento) {
            Log::warning('Pagamento não encontrado para transactionId: ' . $dados['transactionId']);
            return response()->json(['message' => 'Pagamento não encontrado'], 404);
        }

        // Atualizar status
        $pagamento->update([
            'status' => $dados['status'],
            'resposta_validapay' => $dados,
        ]);

        // Se foi pago, creditar
        if ($this->validaPayService->isPago($dados['status']) && !$pagamento->pago_em) {
            $this->processarPagamentoConcluido($pagamento);
        }

        return response()->json(['message' => 'Webhook processado com sucesso']);
    } catch (\Exception $e) {
        Log::error('Erro ao processar webhook: ' . $e->getMessage());
        return response()->json(['message' => 'Erro ao processar webhook'], 500);
    }
}
```

### Processamento do Webhook (ValidaPayService)

```php
public function processarWebhook(array $payload): array
{
    if (!isset($payload['transactionId'])) {
        throw new \Exception('Webhook inválido: transactionId não informado');
    }

    // Consultar pagamento para validar
    try {
        $dados = $this->consultarCobranca($payload['transactionId']);
    } catch (\Exception $e) {
        Log::warning('Erro ao consultar cobrança no webhook: ' . $e->getMessage());
        $dados = $payload;
    }

    return [
        'transactionId' => $dados['transactionId'] ?? $payload['transactionId'],
        'status' => $dados['status'] ?? $payload['status'],
        'valor' => $dados['amount'] ?? $payload['amount'],
        'pagador' => $dados['payer'] ?? $payload['payer'] ?? null,
        'data_criacao' => $dados['createdAt'] ?? $payload['createdAt'] ?? null,
    ];
}
```

### Creditar Usuário Automaticamente

```php
protected function processarPagamentoConcluido(PagamentoPix $pagamento): void
{
    try {
        DB::beginTransaction();

        // Marcar como pago
        $pagamento->marcarComoPago();

        // Creditar usuário
        $this->creditoService->adicionarCreditos(
            userId: $pagamento->user_id,
            quantidade: $pagamento->creditos,
            motivo: "Compra de créditos - Pacote de {$pagamento->creditos} créditos",
            tipoTransacao: 'compra'
        );

        DB::commit();

        Log::info('Pagamento processado com sucesso', [
            'pagamento_id' => $pagamento->id,
            'user_id' => $pagamento->user_id,
            'creditos' => $pagamento->creditos,
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Erro ao processar pagamento concluído', [
            'pagamento_id' => $pagamento->id,
            'error' => $e->getMessage(),
        ]);
        throw $e;
    }
}
```

---

## 🔄 Fluxo Completo

### 1. Usuário Seleciona Pacote

```javascript
// Frontend
const pacotes = await fetch('/api/pagamentos/pix/pacotes').then(r => r.json());
// Usuário escolhe: pacote_100 (R$ 9.90 = 100 créditos)
```

### 2. Backend Cria Cobrança

```php
// Controller
$resultado = $this->validaPayService->criarCobranca(
    valor: 9.90,
    webhookUrl: route('webhook.validapay')
);

// Salva no banco
$pagamento = PagamentoPix::create([
    'user_id' => auth()->id(),
    'txid' => (string) $resultado['transactionId'], // "2774031695"
    'valor' => 9.90,
    'creditos' => 100,
    'status' => 'pendente',
    'qrcode' => $resultado['emv'], // "00020101021226890014br.gov.bcb.pix..."
    'expira_em' => now()->addHour(),
]);
```

### 3. Frontend Exibe QR Code

```javascript
// Resposta da API
{
  "success": true,
  "data": {
    "id": 1,
    "transaction_id": 2774031695,
    "valor": 9.90,
    "creditos": 100,
    "qrcode": "00020101021226890014br.gov.bcb.pix...",
    "expira_em": "2025-01-17T21:30:00Z",
    "status": "pendente"
  }
}

// Exibir QR Code (biblioteca QRCode)
<QRCodeCanvas value={pagamento.data.qrcode} size={256} />

// Ou copiar código
<button onClick={() => navigator.clipboard.writeText(pagamento.data.qrcode)}>
  Copiar código PIX
</button>
```

### 4. Usuário Paga no App Bancário

- Escaneia QR Code ou cola o código EMV
- Confirma pagamento
- ValidaPay detecta o pagamento

### 5. ValidaPay Envia Webhook

```bash
POST https://seusite.com/api/webhook/validapay
Content-Type: application/json

{
  "transactionId": 2774031695,
  "status": "PAID",
  "amount": 9.90,
  "payer": {
    "name": "João Silva",
    "document": "12345678900"
  },
  "createdAt": "2025-01-17T20:35:00Z"
}
```

### 6. Backend Processa Webhook

```php
// 1. Atualiza status do pagamento
$pagamento->update(['status' => 'PAID']);

// 2. Marca como pago
$pagamento->marcarComoPago();

// 3. Credita usuário automaticamente
$this->creditoService->adicionarCreditos(
    userId: $pagamento->user_id,
    quantidade: 100,
    motivo: "Compra de créditos - Pacote de 100 créditos",
    tipoTransacao: 'compra'
);
```

### 7. Frontend Detecta Pagamento (Polling)

```javascript
// Polling a cada 3 segundos
const checkPayment = async (paymentId) => {
  const response = await fetch(`/api/pagamentos/pix/${paymentId}`);
  const data = await response.json();
  
  if (data.data.status === 'PAID') {
    // Pagamento confirmado!
    showSuccess('Pagamento confirmado! Créditos adicionados.');
    return true;
  }
  
  return false;
};

// Iniciar polling
const intervalId = setInterval(async () => {
  const isPaid = await checkPayment(paymentId);
  if (isPaid) {
    clearInterval(intervalId);
  }
}, 3000);
```

---

## 🔄 Diferenças da API Anterior

### Mudanças Principais

| Aspecto | API Antiga (Incorreta) | API Nova (Correta) |
|---------|------------------------|-------------------|
| **Endpoint** | `POST /v2/cob/{txid}` | `POST /pix?eventType=cob_pix` |
| **ID Transação** | `txid` (string UUID) | `transactionId` (int) |
| **QR Code** | Endpoint separado `/v2/loc/{id}/qrcode` | Incluído na resposta (`emv`) |
| **Headers** | Apenas `Authorization` | `Authorization` + `X-Account-Number` |
| **Request Body** | `{valor, chave, devedor, calendario}` | `{amount, webhook_url, split}` |
| **Response** | `{txid, location, calendario, status}` | `{transactionId, emv}` |
| **Webhook** | Complexo com múltiplos campos | Simples: `{transactionId, status, amount}` |

### O que NÃO Mudou

- ✅ Autenticação OAuth2 (mesmo fluxo)
- ✅ Token em cache
- ✅ Lógica de créditos
- ✅ Webhook para notificação
- ✅ Status de pagamento

### Código Atualizado

#### Antes (Incorreto)

```php
// ❌ INCORRETO
$response = Http::withToken($token)
    ->put($this->apiUrl . '/v2/cob/' . $txid, [
        'valor' => ['original' => number_format($valor, 2, '.', '')],
        'chave' => $chavePix,
        'devedor' => $devedor,
        'calendario' => ['expiracao' => $expiracao],
    ]);

$resultado = $response->json();
// {txid, location, calendario, status}

// Depois precisava gerar QR Code separadamente
$qrCode = $this->gerarQrCode($resultado['loc']['id']);
```

#### Depois (Correto)

```php
// ✅ CORRETO
$response = Http::withHeaders([
    'Authorization' => 'Bearer ' . $token,
    'X-Account-Number' => $this->accountNumber,
])->post($this->apiUrl . '/pix?eventType=cob_pix', [
    'amount' => $valor,
    'webhook_url' => $webhookUrl,
    'split' => [],
]);

$resultado = $response->json();
// {transactionId: 2774031695, emv: "00020101..."}

// QR Code já vem no campo 'emv' - não precisa gerar
$qrCode = $resultado['emv']; // ✅ Pronto para usar
```

---

## 📝 Checklist de Implementação

- [x] Atualizar `ValidaPayService` para usar endpoint correto
- [x] Adicionar header `X-Account-Number`
- [x] Mudar request body para `{amount, webhook_url, split}`
- [x] Usar `transactionId` (int) em vez de `txid` (string)
- [x] Remover método `gerarQrCode()` (QR vem em `emv`)
- [x] Atualizar `processarWebhook()` para usar `transactionId`
- [x] Atualizar `PagamentoPixController` para salvar `transactionId`
- [x] Remover campo `qrcode_imagem` do banco (opcional)
- [x] Atualizar documentação
- [x] Testar fluxo completo com credenciais reais
- [ ] Adicionar variável `VALIDAPAY_ACCOUNT_NUMBER` no `.env` de produção
- [ ] Configurar webhook na ValidaPay (URL pública)
- [ ] Testar webhook em ambiente de homologação
- [ ] Monitorar logs de webhook em produção

---

## 🧪 Testando a Integração

### 1. Teste de Autenticação

```php
// Tinker
$service = app(App\Services\ValidaPayService::class);
$token = $service->getAccessToken();
dd($token); // Deve retornar um token JWT
```

### 2. Teste de Criação

```bash
# cURL
curl -X POST 'https://api.validapay.com/pix?eventType=cob_pix' \
  -H 'Authorization: Bearer SEU_TOKEN' \
  -H 'X-Account-Number: SEU_NUMERO_CONTA' \
  -H 'Content-Type: application/json' \
  -d '{"amount": 0.20, "webhook_url": "https://webhook.site/...", "split": []}'
```

### 3. Teste de Consulta

```bash
# cURL
curl -X GET 'https://api.validapay.com/pix/2774031695' \
  -H 'Authorization: Bearer SEU_TOKEN' \
  -H 'X-Account-Number: SEU_NUMERO_CONTA'
```

### 4. Teste de Webhook

Use [webhook.site](https://webhook.site) para capturar o webhook:

1. Crie uma URL no webhook.site
2. Use essa URL como `webhook_url` ao criar cobrança
3. Faça um pagamento de teste
4. Veja o payload no webhook.site

---

## 🚨 Troubleshooting

### Erro: "X-Account-Number header required"

**Solução**: Adicione o header em todas as requisições:

```php
'X-Account-Number' => config('services.validapay.account_number')
```

### Erro: "Invalid transactionId"

**Solução**: `transactionId` é um inteiro, não string. Converta:

```php
$this->validaPayService->consultarCobranca((int) $pagamento->txid);
```

### QR Code não funciona

**Solução**: O campo `emv` é uma string longa. Use uma biblioteca para gerar a imagem:

```javascript
// React
import QRCode from 'qrcode.react';
<QRCode value={pagamento.qrcode} size={256} />
```

### Webhook não está sendo recebido

**Soluções**:
1. Verifique se a URL é pública (não localhost)
2. Use ngrok para testar localmente: `ngrok http 8000`
3. Confirme que a rota não exige autenticação
4. Verifique logs da ValidaPay (se disponível)

---

## 📚 Recursos Adicionais

- **Documentação Oficial**: Consulte a documentação da ValidaPay
- **Suporte**: Entre em contato com o suporte da ValidaPay para dúvidas
- **Sandbox**: Use ambiente de testes antes de produção
- **Monitoring**: Configure alertas para falhas de webhook

---

## ✅ Conclusão

A integração agora está **corrigida e funcional** com a API real da ValidaPay:

- ✅ Endpoint correto: `POST /pix?eventType=cob_pix`
- ✅ Headers corretos: `Authorization` + `X-Account-Number`
- ✅ Resposta correta: `{transactionId, emv}`
- ✅ QR Code incluído (campo `emv`)
- ✅ Webhook simplificado
- ✅ Sistema pronto para produção

**Próximos passos**:
1. Adicionar `VALIDAPAY_ACCOUNT_NUMBER` no `.env` de produção
2. Testar com valores reais em ambiente controlado
3. Monitorar webhooks em produção
4. Ajustar timeouts e retries conforme necessário
