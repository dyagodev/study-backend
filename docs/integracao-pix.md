# Integração PIX - ValidaPay

## 🎯 Visão Geral

Sistema completo de pagamentos PIX para compra de créditos usando a API da ValidaPay.

---

## 📋 Configuração

### 1. Variáveis de Ambiente

Adicione no `.env`:

```env
# ValidaPay
VALIDAPAY_AUTH_URL=https://auth.validapay.com.br
VALIDAPAY_API_URL=https://api.validapay.com.br
VALIDAPAY_CLIENT_ID=seu_client_id
VALIDAPAY_CLIENT_SECRET=seu_client_secret
VALIDAPAY_CHAVE_PIX=sua_chave_pix
```

### 2. Configurar Webhook

Configure o webhook na ValidaPay apontando para:

```
https://seudominio.com/api/webhook/validapay
```

---

## 💰 Pacotes Disponíveis

| Pacote | Créditos | Bônus | Total | Valor |
|--------|----------|-------|-------|-------|
| **Básico** | 100 | 0 | 100 | R$ 9,90 |
| **Plus** | 300 | 30 | 330 | R$ 24,90 |
| **Pro** | 500 | 75 | 575 | R$ 39,90 |
| **Premium** | 1000 | 200 | 1200 | R$ 69,90 |

---

## 📡 Endpoints

### 1. Listar Pacotes

```http
GET /api/pagamentos/pix/pacotes
Authorization: Bearer {token}
```

**Resposta:**
```json
{
  "success": true,
  "data": [
    {
      "id": "pacote_100",
      "nome": "Pacote Básico",
      "creditos": 100,
      "valor": 9.90,
      "bonus": 0,
      "total_creditos": 100,
      "descricao": "Ideal para começar"
    },
    {
      "id": "pacote_300",
      "nome": "Pacote Plus",
      "creditos": 300,
      "valor": 24.90,
      "bonus": 30,
      "total_creditos": 330,
      "descricao": "+10% de bônus",
      "popular": true
    }
  ]
}
```

### 2. Criar Cobrança PIX

```http
POST /api/pagamentos/pix/criar
Authorization: Bearer {token}
Content-Type: application/json

{
  "pacote_id": "pacote_300",
  "cpf": "12345678900",
  "nome": "João Silva"
}
```

**Resposta:**
```json
{
  "success": true,
  "message": "Cobrança PIX criada com sucesso",
  "data": {
    "id": 123,
    "txid": "ABC123XYZ789...",
    "valor": 24.90,
    "creditos": 330,
    "qrcode": "00020126580014br.gov.bcb.pix...",
    "qrcode_imagem": "data:image/png;base64,iVBORw0KGg...",
    "expira_em": "2025-10-17T22:00:00.000000Z",
    "status": "ATIVA"
  }
}
```

### 3. Consultar Status do Pagamento

```http
GET /api/pagamentos/pix/{id}
Authorization: Bearer {token}
```

**Resposta:**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "txid": "ABC123XYZ789...",
    "valor": 24.90,
    "creditos": 330,
    "status": "CONCLUIDA",
    "qrcode": "00020126580014br.gov.bcb.pix...",
    "qrcode_imagem": "data:image/png;base64,...",
    "expira_em": "2025-10-17T22:00:00.000000Z",
    "pago_em": "2025-10-17T21:30:15.000000Z",
    "created_at": "2025-10-17T21:00:00.000000Z"
  }
}
```

### 4. Histórico de Pagamentos

```http
GET /api/pagamentos/pix
Authorization: Bearer {token}
```

**Resposta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "valor": 24.90,
      "creditos": 330,
      "status": "CONCLUIDA",
      "created_at": "2025-10-17T21:00:00Z",
      "pago_em": "2025-10-17T21:30:15Z"
    }
  ],
  "pagination": {
    "current_page": 1,
    "total": 5,
    "per_page": 20,
    "last_page": 1
  }
}
```

### 5. Webhook (Interno)

```http
POST /api/webhook/validapay
Content-Type: application/json

{
  "txid": "ABC123XYZ789...",
  "status": "CONCLUIDA",
  ...
}
```

**Resposta:**
```json
{
  "message": "Webhook processado com sucesso"
}
```

---

## 🔄 Fluxo de Pagamento

```
1. Usuario → GET /pacotes                     → Lista pacotes
2. Usuario → POST /criar                      → Cria cobrança PIX
3. API    → ValidaPay (criar cobrança)        → Recebe QR Code
4. API    → Retorna QR Code                   → Usuario paga
5. ValidaPay → POST /webhook                  → Notifica pagamento
6. API    → Credita usuário                   → Atualiza saldo
7. Usuario → GET /{id}                        → Confirma pagamento
```

---

## 💻 Exemplos de Integração

### Frontend - JavaScript/Fetch

```javascript
// 1. Listar pacotes
async function listarPacotes() {
  const response = await fetch('/api/pagamentos/pix/pacotes', {
    headers: {
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json',
    },
  });
  return await response.json();
}

// 2. Criar pagamento
async function criarPagamento(pacoteId, cpf, nome) {
  const response = await fetch('/api/pagamentos/pix/criar', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    },
    body: JSON.stringify({
      pacote_id: pacoteId,
      cpf: cpf,
      nome: nome,
    }),
  });
  
  if (!response.ok) {
    throw new Error('Erro ao criar pagamento');
  }
  
  return await response.json();
}

// 3. Exibir QR Code
function exibirQRCode(pagamento) {
  const modal = document.getElementById('modal-pagamento');
  
  // QR Code como imagem
  const img = document.createElement('img');
  img.src = pagamento.data.qrcode_imagem;
  img.alt = 'QR Code PIX';
  
  // Código PIX Copia e Cola
  const codigoPix = document.createElement('input');
  codigoPix.value = pagamento.data.qrcode;
  codigoPix.readOnly = true;
  
  // Botão copiar
  const btnCopiar = document.createElement('button');
  btnCopiar.textContent = 'Copiar Código PIX';
  btnCopiar.onclick = () => {
    codigoPix.select();
    document.execCommand('copy');
    alert('Código copiado!');
  };
  
  modal.appendChild(img);
  modal.appendChild(codigoPix);
  modal.appendChild(btnCopiar);
  
  // Iniciar polling para verificar pagamento
  iniciarVerificacaoPagamento(pagamento.data.id);
}

// 4. Verificar pagamento (polling)
function iniciarVerificacaoPagamento(pagamentoId) {
  const intervalo = setInterval(async () => {
    try {
      const response = await fetch(`/api/pagamentos/pix/${pagamentoId}`, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
      });
      
      const data = await response.json();
      
      if (data.data.status === 'CONCLUIDA') {
        clearInterval(intervalo);
        alert(`✅ Pagamento confirmado!\n${data.data.creditos} créditos adicionados!`);
        window.location.reload();
      } else if (data.data.status === 'EXPIRADA') {
        clearInterval(intervalo);
        alert('⏰ Pagamento expirou');
      }
    } catch (error) {
      console.error('Erro ao verificar pagamento:', error);
    }
  }, 5000); // Verificar a cada 5 segundos
  
  // Parar após 1 hora
  setTimeout(() => clearInterval(intervalo), 3600000);
}
```

### React Component

```jsx
// components/ComprarCreditos.jsx
import React, { useState, useEffect } from 'react';
import { QRCodeSVG } from 'qrcode.react';

export default function ComprarCreditos() {
  const [pacotes, setPacotes] = useState([]);
  const [pagamento, setPagamento] = useState(null);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    carregarPacotes();
  }, []);

  async function carregarPacotes() {
    const response = await fetch('/api/pagamentos/pix/pacotes', {
      headers: { 'Authorization': `Bearer ${token}` },
    });
    const data = await response.json();
    setPacotes(data.data);
  }

  async function comprarPacote(pacoteId) {
    setLoading(true);
    try {
      const response = await fetch('/api/pagamentos/pix/criar', {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          pacote_id: pacoteId,
          cpf: '12345678900',
          nome: 'João Silva',
        }),
      });

      const data = await response.json();
      setPagamento(data.data);
      iniciarVerificacao(data.data.id);
    } catch (error) {
      alert('Erro ao criar pagamento');
    } finally {
      setLoading(false);
    }
  }

  function iniciarVerificacao(pagamentoId) {
    const intervalo = setInterval(async () => {
      const response = await fetch(`/api/pagamentos/pix/${pagamentoId}`, {
        headers: { 'Authorization': `Bearer ${token}` },
      });
      const data = await response.json();

      if (data.data.status === 'CONCLUIDA') {
        clearInterval(intervalo);
        alert('Pagamento confirmado!');
        setPagamento(null);
        window.location.reload();
      }
    }, 5000);
  }

  function copiarCodigo() {
    navigator.clipboard.writeText(pagamento.qrcode);
    alert('Código copiado!');
  }

  if (pagamento) {
    return (
      <div className="modal-pagamento">
        <h2>Pague com PIX</h2>
        <QRCodeSVG value={pagamento.qrcode} size={256} />
        <p>Valor: R$ {pagamento.valor}</p>
        <p>Créditos: {pagamento.creditos}</p>
        <input
          type="text"
          value={pagamento.qrcode}
          readOnly
          style={{ width: '100%', marginTop: 16 }}
        />
        <button onClick={copiarCodigo}>Copiar Código PIX</button>
        <p>Aguardando pagamento...</p>
      </div>
    );
  }

  return (
    <div className="pacotes-grid">
      {pacotes.map((pacote) => (
        <div key={pacote.id} className="pacote-card">
          <h3>{pacote.nome}</h3>
          <div className="creditos">{pacote.total_creditos} créditos</div>
          {pacote.bonus > 0 && (
            <div className="bonus">+{pacote.bonus} bônus</div>
          )}
          <div className="preco">R$ {pacote.valor.toFixed(2)}</div>
          <button
            onClick={() => comprarPacote(pacote.id)}
            disabled={loading}
          >
            Comprar
          </button>
        </div>
      ))}
    </div>
  );
}
```

---

## 🔒 Segurança

### 1. Validação de Webhook

```php
// Opcional: Adicionar assinatura/token no webhook
if ($request->header('X-ValidaPay-Signature') !== config('services.validapay.webhook_secret')) {
    return response()->json(['error' => 'Unauthorized'], 401);
}
```

### 2. Evitar Duplicação de Créditos

O sistema já previne duplicação através do campo `pago_em`:

```php
if ($this->validaPayService->isPago($novoStatus) && !$pagamento->pago_em) {
    $this->processarPagamentoConcluido($pagamento);
}
```

### 3. Logs Detalhados

Todos os eventos são registrados:

```php
Log::info('Pagamento processado', ['pagamento_id' => $pagamento->id]);
Log::error('Erro ao criar cobrança', ['error' => $e->getMessage()]);
```

---

## 🧪 Testes

### Teste Manual

```bash
# 1. Criar pagamento
curl -X POST http://localhost/api/pagamentos/pix/criar \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "pacote_id": "pacote_100",
    "cpf": "12345678900",
    "nome": "Teste"
  }'

# 2. Consultar pagamento
curl -X GET http://localhost/api/pagamentos/pix/123 \
  -H "Authorization: Bearer {token}"

# 3. Simular webhook (desenvolvimento)
curl -X POST http://localhost/api/webhook/validapay \
  -H "Content-Type: application/json" \
  -d '{
    "txid": "ABC123...",
    "status": "CONCLUIDA"
  }'
```

---

## 📊 Monitoramento

### Query: Pagamentos Pendentes

```sql
SELECT id, user_id, valor, creditos, status, created_at, expira_em
FROM pagamentos_pix
WHERE status IN ('PENDENTE', 'ATIVA')
  AND expira_em > NOW()
ORDER BY created_at DESC;
```

### Query: Receita Total

```sql
SELECT 
  COUNT(*) as total_pagamentos,
  SUM(valor) as receita_total,
  SUM(creditos) as creditos_vendidos
FROM pagamentos_pix
WHERE status = 'CONCLUIDA';
```

### Query: Taxa de Conversão

```sql
SELECT 
  COUNT(CASE WHEN status = 'CONCLUIDA' THEN 1 END) * 100.0 / COUNT(*) as taxa_conversao
FROM pagamentos_pix
WHERE created_at >= NOW() - INTERVAL 30 DAY;
```

---

## 🎯 Resumo

✅ **API ValidaPay** integrada  
✅ **4 pacotes** de créditos  
✅ **QR Code** automático  
✅ **Webhook** para confirmação  
✅ **Polling** para verificação em tempo real  
✅ **Segurança** contra duplicação  
✅ **Logs** completos  
✅ **Pronto para produção** 🚀
