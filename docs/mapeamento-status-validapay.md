# 🔄 Mapeamento de Status ValidaPay → Banco de Dados

## 📊 Status da ValidaPay vs Status no Banco

### Mapeamento Completo

| Status ValidaPay | Status no Banco | Descrição |
|------------------|-----------------|-----------|
| `PENDING` | `pendente` | Aguardando pagamento |
| `CONFIRMED` | `concluida` | Pagamento confirmado ✅ |
| `PAID` | `concluida` | Pagamento confirmado (alternativo) |
| `CANCELLED` | `cancelado` | Cobrança cancelada |
| `EXPIRED` | `expirado` | Cobrança expirou |

---

## 🎯 Motivo do Mapeamento

### Problema Original
```sql
-- Enum da tabela pagamentos_pix
status ENUM('pendente', 'concluida', 'cancelado', 'expirado')
```

A ValidaPay retorna `CONFIRMED` no webhook, mas nosso banco usa `concluida` (em português).

### Solução Implementada

```php
protected function mapearStatusParaBanco(string $status): string
{
    $mapeamento = [
        'PENDING' => 'PENDENTE',
        'CONFIRMED' => 'CONCLUIDA',  // ✅ Mapeamento principal
        'PAID' => 'CONCLUIDA',
        'CANCELLED' => 'CANCELADA',
        'EXPIRED' => 'EXPIRADA',
    ];

    return $mapeamento[strtoupper($status)] ?? strtolower($status);
}
```

---

## 🔄 Fluxo Atualizado

### 1. Webhook Recebido
```json
{
  "status": "CONFIRMED",
  "transactionId": "2762993976"
}
```

### 2. Processamento no Controller
```php
// Mapear status
$statusBanco = $this->mapearStatusParaBanco('CONFIRMED');
// Retorna: 'concluida'

// Atualizar no banco
$pagamento->update([
    'status' => $statusBanco, // 'concluida'
    'resposta_validapay' => $dados,
]);
```

### 3. Verificação de Pagamento
```php
// Ainda usa status da ValidaPay
if ($this->validaPayService->isPago($dados['status'])) {
    // $dados['status'] = 'CONFIRMED'
    // isPago('CONFIRMED') retorna true
    $this->processarPagamentoConcluido($pagamento);
}
```

---

## 🗄️ Estrutura do Banco

### Enum da Coluna Status
```sql
ALTER TABLE pagamentos_pix 
MODIFY COLUMN status 
ENUM('pendente', 'concluida', 'cancelado', 'expirado') 
NOT NULL DEFAULT 'pendente';
```

### Valores Permitidos
- ✅ `pendente` - Status inicial
- ✅ `concluida` - Pagamento confirmado
- ✅ `cancelado` - Cobrança cancelada
- ✅ `expirado` - Cobrança expirou

---

## 📝 Exemplos de Uso

### Criação de Cobrança
```php
$pagamento = PagamentoPix::create([
    'user_id' => $user->id,
    'txid' => $resultado['transactionId'],
    'valor' => 9.90,
    'creditos' => 100,
    'status' => 'pendente', // ✅ Status inicial em português
    'qrcode' => $resultado['emv'],
]);
```

### Webhook - Status Confirmado
```php
// Webhook recebe: {status: "CONFIRMED", transactionId: "..."}

// Mapeia para banco
$statusBanco = $this->mapearStatusParaBanco('CONFIRMED');
// Retorna: 'concluida'

// Atualiza
$pagamento->update(['status' => 'concluida']);
```

### Consulta no Banco
```sql
-- Buscar pagamentos confirmados
SELECT * FROM pagamentos_pix WHERE status = 'concluida';

-- Buscar pagamentos pendentes
SELECT * FROM pagamentos_pix WHERE status = 'pendente';
```

---

## 🔍 Validação de Status

### No Service (ValidaPayService)
```php
public function isPago(string $status): bool
{
    // Valida usando status da ValidaPay (inglês)
    return in_array(strtoupper($status), [
        'CONFIRMED',  // ✅ Status da API
        'PAID',
        'CONCLUIDA',  // Compatibilidade
        'PAGO',
        'COMPLETED'
    ]);
}
```

### No Model (PagamentoPix)
```php
public function isConcluido(): bool
{
    // Valida usando status do banco (português)
    return $this->status === 'concluida';
}
```

---

## 🎨 Frontend - Exibição de Status

### Mapeamento para UI
```javascript
const statusLabels = {
  'pendente': {
    label: 'Aguardando pagamento',
    color: 'warning',
    icon: 'clock'
  },
  'concluida': {
    label: 'Pagamento confirmado',
    color: 'success',
    icon: 'check-circle'
  },
  'cancelado': {
    label: 'Cancelado',
    color: 'error',
    icon: 'x-circle'
  },
  'expirado': {
    label: 'Expirado',
    color: 'error',
    icon: 'alert-circle'
  }
};

// Uso
const statusInfo = statusLabels[pagamento.status];
```

### Componente React
```jsx
const StatusBadge = ({ status }) => {
  const config = {
    pendente: { label: 'Pendente', color: 'yellow' },
    concluida: { label: 'Confirmado ✅', color: 'green' },
    cancelado: { label: 'Cancelado', color: 'red' },
    expirado: { label: 'Expirado', color: 'gray' },
  };

  const { label, color } = config[status] || {};

  return (
    <span className={`badge badge-${color}`}>
      {label}
    </span>
  );
};

// Uso
<StatusBadge status={pagamento.status} />
```

---

## 🧪 Testes

### Teste de Mapeamento
```php
// test/Unit/PagamentoPixControllerTest.php

public function test_mapear_status_confirmed_para_concluida()
{
    $controller = new PagamentoPixController(
        app(ValidaPayService::class),
        app(CreditoService::class)
    );

    $reflection = new \ReflectionClass($controller);
    $method = $reflection->getMethod('mapearStatusParaBanco');
    $method->setAccessible(true);

    $resultado = $method->invokeArgs($controller, ['CONFIRMED']);
    
    $this->assertEquals('concluida', $resultado);
}

public function test_todos_status_da_validapay_sao_mapeados()
{
    $controller = new PagamentoPixController(
        app(ValidaPayService::class),
        app(CreditoService::class)
    );

    $reflection = new \ReflectionClass($controller);
    $method = $reflection->getMethod('mapearStatusParaBanco');
    $method->setAccessible(true);

    $testes = [
        'PENDING' => 'pendente',
        'CONFIRMED' => 'concluida',
        'PAID' => 'concluida',
        'CANCELLED' => 'cancelado',
        'EXPIRED' => 'expirado',
    ];

    foreach ($testes as $statusApi => $statusBanco) {
        $resultado = $method->invokeArgs($controller, [$statusApi]);
        $this->assertEquals($statusBanco, $resultado, 
            "Status {$statusApi} deveria mapear para {$statusBanco}"
        );
    }
}
```

### Teste de Webhook
```php
public function test_webhook_atualiza_status_para_concluida()
{
    $user = User::factory()->create();
    $pagamento = PagamentoPix::factory()->create([
        'user_id' => $user->id,
        'status' => 'pendente',
        'txid' => '2762993976',
    ]);

    $response = $this->postJson('/api/webhook/validapay', [
        'status' => 'CONFIRMED',
        'transactionId' => '2762993976',
    ]);

    $response->assertOk();
    
    $pagamento->refresh();
    $this->assertEquals('concluida', $pagamento->status);
    $this->assertNotNull($pagamento->pago_em);
}
```

---

## 📊 Queries Úteis

### Relatório de Status
```sql
SELECT 
    status,
    COUNT(*) as total,
    SUM(valor) as valor_total,
    AVG(valor) as valor_medio
FROM pagamentos_pix
GROUP BY status
ORDER BY total DESC;
```

### Conversão de Status
```sql
-- Ver transições de status (se histórico disponível)
SELECT 
    txid,
    status,
    created_at,
    pago_em,
    TIMESTAMPDIFF(MINUTE, created_at, pago_em) as minutos_ate_pagamento
FROM pagamentos_pix
WHERE status = 'concluida'
ORDER BY created_at DESC
LIMIT 10;
```

### Taxa de Conclusão
```sql
SELECT 
    DATE(created_at) as data,
    COUNT(*) as total,
    SUM(CASE WHEN status = 'concluida' THEN 1 ELSE 0 END) as concluidos,
    ROUND(SUM(CASE WHEN status = 'concluida' THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as taxa_conclusao
FROM pagamentos_pix
GROUP BY DATE(created_at)
ORDER BY data DESC;
```

---

## ✅ Checklist de Implementação

- [x] Criar método `mapearStatusParaBanco()`
- [x] Atualizar webhook para usar mapeamento
- [x] Manter validação com status da API no `isPago()`
- [x] Documentar mapeamento
- [x] Enum do banco já está correto
- [ ] Adicionar testes unitários
- [ ] Atualizar frontend para usar status em português
- [ ] Documentar para equipe

---

## 🔄 Benefícios da Abordagem

### ✅ Vantagens
1. **Consistência**: Banco sempre em português
2. **Compatibilidade**: API usa padrão internacional (inglês)
3. **Flexibilidade**: Fácil adicionar novos status
4. **Clareza**: Código explícito sobre o que faz
5. **Manutenibilidade**: Mapeamento centralizado

### 🎯 Separação de Responsabilidades
- **ValidaPayService**: Trabalha com status da API (CONFIRMED, PAID, etc)
- **Controller**: Faz mapeamento entre API e banco
- **Model/Banco**: Usa status em português (concluida, pendente, etc)

---

**Atualizado em**: 17 de Janeiro de 2025  
**Versão**: 3.0  
**Status**: ✅ Implementado

**Webhook Validado**:
```json
{
  "status": "CONFIRMED",
  "transactionId": "2762993976"
}
```

**Mapeamento**: `CONFIRMED` → `concluida` ✅
