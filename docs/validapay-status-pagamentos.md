# 📊 Status de Pagamentos ValidaPay

## Status Confirmados

Baseado no webhook real recebido da ValidaPay.

---

## 🔄 Estados de Pagamento

### 1. **PENDING** (Pendente)
- **Descrição**: Cobrança criada, aguardando pagamento
- **Quando ocorre**: Logo após criação da cobrança
- **Ação**: Mostrar QR Code para o usuário
- **Próximo estado**: CONFIRMED ou EXPIRED

### 2. **CONFIRMED** ✅ (Confirmado)
- **Descrição**: Pagamento confirmado
- **Quando ocorre**: Após usuário pagar o PIX
- **Ação**: Creditar usuário automaticamente
- **Webhook**: `{status: "CONFIRMED", transactionId: "2762993976"}`
- **Final**: ✅ Estado final positivo

### 3. **EXPIRED** ⏱️ (Expirado)
- **Descrição**: Cobrança expirou sem pagamento
- **Quando ocorre**: Após tempo de expiração (geralmente 1 hora)
- **Ação**: Marcar como expirado, não creditar
- **Final**: ❌ Estado final negativo

### 4. **CANCELLED** ❌ (Cancelado)
- **Descrição**: Cobrança cancelada manualmente
- **Quando ocorre**: Quando você cancela via API
- **Ação**: Não creditar usuário
- **Final**: ❌ Estado final negativo

---

## 🔄 Fluxo de Estados

```
        CRIAÇÃO
           ↓
      [PENDING]
           ↓
    ┌──────┴──────┐
    ↓             ↓
[CONFIRMED]   [EXPIRED]
    ✅            ❌
```

---

## 💾 Mapeamento no Banco de Dados

### Tabela `pagamentos_pix`

```sql
status ENUM('pendente', 'PENDING', 'CONFIRMED', 'EXPIRED', 'CANCELLED')
```

**Recomendação**: Usar os status exatos da API para evitar confusão:
- ✅ Use `PENDING`, `CONFIRMED`, `EXPIRED`, `CANCELLED`
- ❌ Evite traduzir para português no banco

---

## 🔍 Verificação de Pagamento

### No Código (ValidaPayService)

```php
public function isPago(string $status): bool
{
    return in_array(strtoupper($status), [
        'CONFIRMED',  // ✅ Status principal da ValidaPay
        'PAID',       // Alternativa (se API mudar)
        'CONCLUIDA',  // Compatibilidade
        'PAGO',       // Compatibilidade
        'COMPLETED'   // Compatibilidade
    ]);
}
```

### Uso

```php
if ($this->validaPayService->isPago($dados['status'])) {
    // Creditar usuário
    $this->processarPagamentoConcluido($pagamento);
}
```

---

## 📝 Formato do Webhook

### Webhook Real Recebido

```json
{
  "status": "CONFIRMED",
  "transactionId": "2762993976"
}
```

**Campos**:
- `status` (string): Status atual do pagamento
- `transactionId` (string|int): ID da transação

### Processamento

```php
// Controller
public function webhook(Request $request)
{
    $payload = $request->all();
    // {status: "CONFIRMED", transactionId: "2762993976"}
    
    $dados = $this->validaPayService->processarWebhook($payload);
    
    $pagamento = PagamentoPix::where('txid', (string) $dados['transactionId'])->first();
    
    if ($this->validaPayService->isPago($dados['status'])) {
        // Creditar usuário
    }
}
```

---

## 🎯 Estados no Frontend

### Pendente

```javascript
{
  "status": "PENDING",
  "display": "Aguardando pagamento",
  "color": "warning",
  "icon": "clock"
}
```

**UI**: Mostrar QR Code, timer de expiração, polling ativo

### Confirmado

```javascript
{
  "status": "CONFIRMED",
  "display": "Pagamento confirmado!",
  "color": "success",
  "icon": "check-circle"
}
```

**UI**: Mensagem de sucesso, redirecionar para dashboard

### Expirado

```javascript
{
  "status": "EXPIRED",
  "display": "Pagamento expirado",
  "color": "error",
  "icon": "x-circle"
}
```

**UI**: Botão para criar nova cobrança

### Cancelado

```javascript
{
  "status": "CANCELLED",
  "display": "Pagamento cancelado",
  "color": "error",
  "icon": "x-circle"
}
```

**UI**: Informar que foi cancelado, opção de tentar novamente

---

## 🔄 Polling de Status

### Frontend (React)

```javascript
const checkPaymentStatus = async (paymentId) => {
  try {
    const response = await api.get(`/api/pagamentos/pix/${paymentId}`);
    const { status } = response.data.data;
    
    // Estados finais - parar polling
    if (['CONFIRMED', 'EXPIRED', 'CANCELLED'].includes(status)) {
      stopPolling();
      
      if (status === 'CONFIRMED') {
        showSuccess('Pagamento confirmado!');
        navigate('/dashboard');
      } else {
        showError('Pagamento não concluído');
      }
    }
    
    return status;
  } catch (error) {
    console.error('Erro ao verificar pagamento:', error);
  }
};

// Polling a cada 3 segundos
useEffect(() => {
  const interval = setInterval(() => {
    checkPaymentStatus(paymentId);
  }, 3000);
  
  return () => clearInterval(interval);
}, [paymentId]);
```

---

## 🔒 Segurança do Webhook

### Validações Recomendadas

1. **Validar transactionId existe**
```php
if (!isset($payload['transactionId'])) {
    return response()->json(['error' => 'transactionId obrigatório'], 400);
}
```

2. **Verificar pagamento existe no banco**
```php
$pagamento = PagamentoPix::where('txid', $payload['transactionId'])->first();
if (!$pagamento) {
    Log::warning('Pagamento não encontrado');
    return response()->json(['error' => 'Pagamento não encontrado'], 404);
}
```

3. **Evitar duplicação de créditos**
```php
if ($pagamento->pago_em) {
    Log::info('Pagamento já processado');
    return response()->json(['message' => 'Já processado'], 200);
}
```

4. **Consultar API para validar (opcional)**
```php
try {
    $validacao = $this->validaPayService->consultarCobranca($payload['transactionId']);
    if ($validacao['status'] !== 'CONFIRMED') {
        throw new Exception('Status divergente');
    }
} catch (\Exception $e) {
    Log::error('Validação de webhook falhou: ' . $e->getMessage());
}
```

---

## 📊 Monitoramento

### Queries Úteis

```sql
-- Pagamentos confirmados nas últimas 24h
SELECT * FROM pagamentos_pix 
WHERE status = 'CONFIRMED' 
AND pago_em >= NOW() - INTERVAL 24 HOUR;

-- Pagamentos pendentes há mais de 1 hora (possível problema)
SELECT * FROM pagamentos_pix 
WHERE status = 'PENDING' 
AND created_at < NOW() - INTERVAL 1 HOUR;

-- Taxa de conversão (pagos vs criados)
SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'CONFIRMED' THEN 1 ELSE 0 END) as confirmados,
    (SUM(CASE WHEN status = 'CONFIRMED' THEN 1 ELSE 0 END) / COUNT(*)) * 100 as taxa_conversao
FROM pagamentos_pix
WHERE DATE(created_at) = CURDATE();
```

---

## 🧪 Testando Webhooks

### 1. Criar Cobrança de Teste

```bash
php artisan tinker
```

```php
$service = app(\App\Services\ValidaPayService::class);
$resultado = $service->criarCobranca(
    valor: 0.01,
    webhookUrl: 'https://webhook.site/seu-uuid'
);

echo "Transaction ID: " . $resultado['transactionId'];
```

### 2. Simular Webhook Manualmente

```bash
curl -X POST http://localhost:8000/api/webhook/validapay \
  -H "Content-Type: application/json" \
  -d '{
    "status": "CONFIRMED",
    "transactionId": "2762993976"
  }'
```

### 3. Verificar Logs

```bash
tail -f storage/logs/laravel.log | grep -i webhook
```

---

## ✅ Checklist de Implementação

- [x] Adicionar `CONFIRMED` no método `isPago()`
- [x] Processar webhook com formato `{status, transactionId}`
- [x] Buscar pagamento por `transactionId`
- [x] Validar status antes de creditar
- [x] Evitar crédito duplicado (check `pago_em`)
- [x] Log detalhado de webhooks recebidos
- [ ] Adicionar retry para webhooks falhos
- [ ] Dashboard admin para monitorar pagamentos
- [ ] Alertas para pagamentos travados em PENDING
- [ ] Testes automatizados de webhook

---

## 📝 Resumo

| Status | Descrição | Ação | Final |
|--------|-----------|------|-------|
| **PENDING** | Aguardando | Mostrar QR | ❌ |
| **CONFIRMED** | Pago ✅ | Creditar | ✅ |
| **EXPIRED** | Expirado ⏱️ | Nada | ✅ |
| **CANCELLED** | Cancelado ❌ | Nada | ✅ |

**Webhook Real**:
```json
{
  "status": "CONFIRMED",
  "transactionId": "2762993976"
}
```

**Código Atualizado**: ✅ Método `isPago()` já inclui `CONFIRMED`

---

Atualizado em: 17 de Janeiro de 2025  
Baseado em: Webhook real recebido da ValidaPay
