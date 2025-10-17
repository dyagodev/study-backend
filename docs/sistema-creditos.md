# Sistema de Créditos

## Visão Geral

O sistema de créditos controla o uso de recursos que utilizam IA (geração de questões e criação de simulados). Cada usuário possui um saldo de créditos que é debitado conforme usa esses recursos.

## Conceitos Principais

### Créditos
- **Moeda virtual** do sistema para controlar uso de recursos
- Cada usuário começa com **100 créditos** gratuitos ao se cadastrar
- Créditos são **debitados** ao gerar questões ou criar simulados
- Admin pode **adicionar créditos** manualmente

### Transações
- Todo débito ou crédito gera uma **transação registrada**
- Histórico completo mantido para auditoria
- Campos rastreados: quantidade, saldo anterior/posterior, descrição, referência

## Tabela de Custos

| Operação | Custo por Unidade | Descrição |
|----------|------------------|-----------|
| **Questão Simples** | 1 crédito | Geração de questão por tema |
| **Questão Variação** | 2 créditos | Geração de variação de questão existente |
| **Questão por Imagem** | 3 créditos | Geração de questão baseada em imagem |
| **Simulado** | 5 créditos | Criação de um simulado |

### Exemplos de Cálculo:

- Gerar **5 questões simples** = 5 × 1 = **5 créditos**
- Gerar **3 variações** = 3 × 2 = **6 créditos**
- Gerar **1 questão por imagem** = 1 × 3 = **3 créditos**
- Criar **1 simulado** = **5 créditos**

## Endpoints da API

### 1. Consultar Saldo

Retorna o saldo atual de créditos do usuário.

**Endpoint:** `GET /api/creditos/saldo`

**Headers:**
```
Authorization: Bearer {token}
```

**Resposta (200):**
```json
{
  "success": true,
  "data": {
    "creditos": 85
  }
}
```

---

### 2. Histórico de Transações

Lista todas as transações de créditos do usuário.

**Endpoint:** `GET /api/creditos/historico`

**Query Parameters:**
- `limite` (opcional): Número máximo de registros (padrão: 50)

**Headers:**
```
Authorization: Bearer {token}
```

**Resposta (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 15,
      "tipo": "debito",
      "quantidade": 5,
      "saldo_anterior": 90,
      "saldo_posterior": 85,
      "descricao": "Geração de 5 questão(ões) - Tema: Matemática",
      "referencia_tipo": "questao",
      "referencia_id": null,
      "data": "2025-10-17 18:30:45"
    },
    {
      "id": 14,
      "tipo": "credito",
      "quantidade": 50,
      "saldo_anterior": 40,
      "saldo_posterior": 90,
      "descricao": "Bônus de boas-vindas",
      "referencia_tipo": "admin",
      "referencia_id": 1,
      "data": "2025-10-17 10:00:00"
    }
  ]
}
```

**Tipos de Transação:**
- `debito`: Créditos consumidos
- `credito`: Créditos adicionados

**Tipos de Referência:**
- `questao`: Geração de questão
- `simulado`: Criação de simulado
- `admin`: Adicionado manualmente por admin
- `bonus`: Bônus do sistema

---

### 3. Estatísticas de Uso

Retorna estatísticas consolidadas sobre uso de créditos.

**Endpoint:** `GET /api/creditos/estatisticas`

**Headers:**
```
Authorization: Bearer {token}
```

**Resposta (200):**
```json
{
  "success": true,
  "data": {
    "saldo_atual": 85,
    "total_debitado": 65,
    "total_creditado": 150,
    "total_transacoes": 12
  }
}
```

---

### 4. Tabela de Custos

Retorna a tabela de custos de todas as operações.

**Endpoint:** `GET /api/creditos/custos`

**Headers:**
```
Authorization: Bearer {token}
```

**Resposta (200):**
```json
{
  "success": true,
  "data": {
    "questoes": {
      "simples": {
        "custo_por_questao": 1,
        "descricao": "Geração de questão por tema"
      },
      "variacao": {
        "custo_por_questao": 2,
        "descricao": "Geração de variação de questão"
      },
      "imagem": {
        "custo_por_questao": 3,
        "descricao": "Geração de questão por imagem"
      }
    },
    "simulado": {
      "custo": 5,
      "descricao": "Criação de simulado"
    }
  }
}
```

---

### 5. Adicionar Créditos (Admin)

Permite que administradores adicionem créditos para qualquer usuário.

**Endpoint:** `POST /api/creditos/adicionar`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body:**
```json
{
  "user_id": 15,
  "quantidade": 100,
  "motivo": "Bônus de participação no evento"
}
```

**Campos:**
- `user_id` (obrigatório): ID do usuário que receberá os créditos
- `quantidade` (obrigatório): Quantidade de créditos a adicionar (mínimo: 1)
- `motivo` (obrigatório): Descrição do motivo (máx. 255 caracteres)

**Resposta (201):**
```json
{
  "success": true,
  "message": "Créditos adicionados com sucesso",
  "data": {
    "transacao_id": 25,
    "usuario": "João Silva",
    "quantidade_adicionada": 100,
    "saldo_anterior": 85,
    "saldo_atual": 185
  }
}
```

**Erro (403) - Não é Admin:**
```json
{
  "success": false,
  "message": "Apenas administradores podem adicionar créditos"
}
```

---

## Integração com Geração de Questões

Todos os endpoints de geração de questões agora consomem créditos automaticamente.

### Geração de Questão por Tema

**Endpoint:** `POST /api/questoes/gerar-por-tema`

**Body:**
```json
{
  "tema_id": 1,
  "assunto": "Equações de 2º grau",
  "quantidade": 5
}
```

**Resposta de Sucesso (201):**
```json
{
  "success": true,
  "message": "Questões geradas com sucesso",
  "data": [ /* questões geradas */ ],
  "custo": 5,
  "saldo_restante": 80
}
```

**Erro (402) - Créditos Insuficientes:**
```json
{
  "success": false,
  "message": "Créditos insuficientes. Necessário: 5 créditos. Saldo atual: 3",
  "custo_necessario": 5,
  "saldo_atual": 3
}
```

### Outras Operações

As mesmas regras se aplicam para:
- `POST /api/questoes/gerar-variacao` (2 créditos/questão)
- `POST /api/questoes/gerar-por-imagem` (3 créditos/questão)
- `POST /api/simulados` (5 créditos)

---

## Fluxo de Verificação

### 1. Antes da Geração
```
1. Usuário solicita geração de 5 questões
2. Sistema calcula custo: 5 × 1 = 5 créditos
3. Sistema verifica saldo do usuário
4. Se saldo < custo → Retorna erro 402
5. Se saldo >= custo → Prossegue
```

### 2. Durante a Geração
```
6. Sistema chama AI Service
7. AI gera as questões
8. Sistema salva questões no banco
```

### 3. Após Geração Bem-Sucedida
```
9. Sistema debita créditos do usuário
10. Cria registro de transação
11. Atualiza saldo do usuário
12. Retorna questões + novo saldo
```

### 4. Em Caso de Erro
```
- Se AI falhar → Créditos NÃO são debitados
- Se salvar falhar → Créditos NÃO são debitados
- Apenas débito se operação completa com sucesso
```

---

## Regras de Negócio

### Débito de Créditos
- ✅ Apenas após operação bem-sucedida
- ✅ Uso de transações DB (atomicidade garantida)
- ✅ Lock pessimista para evitar race conditions
- ✅ Histórico completo mantido

### Crédito de Créditos
- ✅ Apenas admins podem adicionar manualmente
- ✅ Registro com referência ao admin responsável
- ✅ Motivo obrigatório para auditoria

### Proteções
- 🔒 Verificação ANTES de processar (economia de recursos)
- 🔒 Lock no registro do usuário durante transação
- 🔒 Rollback automático em caso de erro
- 🔒 Histórico imutável de transações

---

## Schema do Banco de Dados

### Tabela: users
```sql
users
├── id
├── name
├── email
├── creditos (integer, default: 100)  -- Novo campo
└── ...
```

### Tabela: transacoes_creditos
```sql
transacoes_creditos
├── id (bigint, PK)
├── user_id (bigint, FK) → users.id
├── tipo (enum: 'credito', 'debito')
├── quantidade (integer)
├── saldo_anterior (integer)
├── saldo_posterior (integer)
├── descricao (string)
├── referencia_tipo (string, nullable) -- 'questao', 'simulado', 'admin', 'bonus'
├── referencia_id (bigint, nullable)
├── created_at
└── updated_at

INDEX: (user_id, created_at)
INDEX: (referencia_tipo, referencia_id)
```

---

## Exemplos de Uso

### Frontend: Verificar Saldo Antes de Gerar

```javascript
async function verificarESolicitar() {
  // 1. Consultar saldo
  const saldo = await fetch('/api/creditos/saldo', {
    headers: { 'Authorization': `Bearer ${token}` }
  }).then(r => r.json());

  // 2. Consultar custos
  const custos = await fetch('/api/creditos/custos', {
    headers: { 'Authorization': `Bearer ${token}` }
  }).then(r => r.json());

  const custoQuestao = custos.data.questoes.simples.custo_por_questao;
  const quantidade = 5;
  const custoTotal = custoQuestao * quantidade;

  // 3. Verificar se tem créditos
  if (saldo.data.creditos < custoTotal) {
    alert(`Créditos insuficientes! Você tem ${saldo.data.creditos}, precisa de ${custoTotal}`);
    return;
  }

  // 4. Prosseguir com a geração
  const resultado = await fetch('/api/questoes/gerar-por-tema', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      tema_id: 1,
      assunto: 'Matemática',
      quantidade: 5
    })
  }).then(r => r.json());

  console.log('Questões geradas:', resultado.data);
  console.log('Créditos gastos:', resultado.custo);
  console.log('Saldo restante:', resultado.saldo_restante);
}
```

### Frontend: Mostrar Saldo no Header

```javascript
async function atualizarSaldo() {
  const response = await fetch('/api/creditos/saldo', {
    headers: { 'Authorization': `Bearer ${token}` }
  });
  const { data } = await response.json();
  
  document.getElementById('saldo-creditos').textContent = `${data.creditos} créditos`;
}

// Atualizar a cada ação que consuma créditos
setInterval(atualizarSaldo, 30000); // A cada 30 segundos
```

### Frontend: Histórico de Transações

```javascript
async function mostrarHistorico() {
  const response = await fetch('/api/creditos/historico?limite=10', {
    headers: { 'Authorization': `Bearer ${token}` }
  });
  const { data } = await response.json();
  
  const html = data.map(t => `
    <div class="transacao ${t.tipo}">
      <span class="tipo">${t.tipo === 'debito' ? '−' : '+'}</span>
      <span class="quantidade">${t.quantidade} créditos</span>
      <span class="descricao">${t.descricao}</span>
      <span class="saldo">Saldo: ${t.saldo_posterior}</span>
      <span class="data">${new Date(t.data).toLocaleString()}</span>
    </div>
  `).join('');
  
  document.getElementById('historico').innerHTML = html;
}
```

---

## Casos de Uso

### 1. Usuário Novo Cadastra-se
```
1. User se registra
2. Sistema cria conta com 100 créditos (default)
3. User pode gerar até 100 questões simples antes de acabar
```

### 2. Usuário Tenta Gerar Sem Créditos
```
1. User tem 2 créditos
2. User tenta gerar 5 questões (custo: 5)
3. Sistema retorna erro 402 com mensagem clara
4. User vê quanto falta e pode solicitar mais créditos ao admin
```

### 3. Admin Adiciona Bônus
```
1. Admin acessa painel
2. Admin seleciona usuário e adiciona 50 créditos
3. Admin informa motivo: "Bônus de participação em evento"
4. Sistema registra transação com referência ao admin
5. User recebe notificação (opcional) de créditos adicionados
```

### 4. User Visualiza Gastos
```
1. User acessa página "Meus Créditos"
2. Vê saldo atual: 45 créditos
3. Vê estatísticas:
   - Total gasto: 155 créditos
   - Total recebido: 200 créditos
   - Total de transações: 23
4. Vê histórico detalhado das últimas 50 transações
```

---

## Prevenção de Fraudes

### Race Conditions
```php
// Lock pessimista garante que apenas 1 transação por vez
$user = User::where('id', $userId)->lockForUpdate()->first();
```

### Verificação Dupla
```php
// 1. Verificação antes de processar (UX)
if (!$user->temCreditos($custo)) {
    return error('Créditos insuficientes');
}

// 2. Verificação dentro da transação (segurança)
DB::transaction(function() use ($user, $custo) {
    $user = User::lockForUpdate()->find($user->id);
    if (!$user->temCreditos($custo)) {
        throw new Exception('Créditos insuficientes');
    }
    // Débito seguro
});
```

### Atomicidade
```php
// Se qualquer etapa falhar, rollback automático:
DB::transaction(function() {
    1. Gerar questões com AI
    2. Salvar questões no banco
    3. Debitar créditos
    4. Criar transação
});
```

---

## Configuração de Custos

Para alterar os custos, edite `app/Services/CreditoService.php`:

```php
class CreditoService
{
    const CUSTO_QUESTAO_SIMPLES = 1;  // Altere aqui
    const CUSTO_QUESTAO_VARIACAO = 2; // Altere aqui
    const CUSTO_QUESTAO_IMAGEM = 3;   // Altere aqui
    const CUSTO_SIMULADO = 5;          // Altere aqui
}
```

Após alteração, limpe o cache:
```bash
php artisan cache:clear
```

---

## Monitoramento

### Consultas Úteis

**Usuários com saldo baixo:**
```sql
SELECT id, name, email, creditos 
FROM users 
WHERE creditos < 10 
ORDER BY creditos ASC;
```

**Top 10 usuários que mais gastam:**
```sql
SELECT u.name, SUM(t.quantidade) as total_gasto
FROM users u
JOIN transacoes_creditos t ON u.id = t.user_id
WHERE t.tipo = 'debito'
GROUP BY u.id, u.name
ORDER BY total_gasto DESC
LIMIT 10;
```

**Transações do dia:**
```sql
SELECT COUNT(*) as total, SUM(quantidade) as creditos_movimentados
FROM transacoes_creditos
WHERE DATE(created_at) = CURDATE();
```

---

## Próximas Melhorias Sugeridas

📝 **Pacotes de Créditos:**
- Implementar compra de pacotes (10, 50, 100 créditos)
- Integração com gateway de pagamento

📝 **Créditos Recorrentes:**
- Planos mensais com recarga automática
- Diferentes tiers: Basic (100/mês), Pro (500/mês), Premium (ilimitado)

📝 **Sistema de Bônus:**
- Bônus diário por login
- Bônus por convite de amigos
- Bônus por completar desafios

📝 **Notificações:**
- Alerta quando créditos < 10
- Email semanal com uso de créditos
- Push notification ao receber créditos

📝 **Analytics:**
- Dashboard admin com métricas de uso
- Gráficos de consumo por período
- Previsão de esgotamento de créditos

---

## Testando

### 1. Criar Usuário e Verificar Saldo Inicial
```bash
# Registrar
curl -X POST http://localhost/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Teste",
    "email": "teste@example.com",
    "password": "senha123",
    "password_confirmation": "senha123"
  }'

# Login e pegar token
curl -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "teste@example.com",
    "password": "senha123"
  }'

# Verificar saldo (deve ser 100)
curl http://localhost/api/creditos/saldo \
  -H "Authorization: Bearer {token}"
```

### 2. Gerar Questões e Verificar Débito
```bash
# Gerar 5 questões (custo: 5)
curl -X POST http://localhost/api/questoes/gerar-por-tema \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "tema_id": 1,
    "assunto": "Teste",
    "quantidade": 5
  }'

# Verificar novo saldo (deve ser 95)
curl http://localhost/api/creditos/saldo \
  -H "Authorization: Bearer {token}"
```

### 3. Tentar Gerar Sem Créditos Suficientes
```bash
# Supondo que você tenha apenas 3 créditos
curl -X POST http://localhost/api/questoes/gerar-por-tema \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "tema_id": 1,
    "assunto": "Teste",
    "quantidade": 10
  }'
# Deve retornar erro 402
```

### 4. Admin Adiciona Créditos
```bash
curl -X POST http://localhost/api/creditos/adicionar \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 2,
    "quantidade": 100,
    "motivo": "Bônus de teste"
  }'
```

---

## Considerações Finais

✅ **Implementado:**
- Sistema completo de créditos com saldo por usuário
- Tabela de transações com histórico completo
- Integração com todos endpoints de geração
- Verificação automática antes de processar
- Proteção contra race conditions
- Débito apenas após sucesso
- API completa de gerenciamento
- Endpoints para admin adicionar créditos

🔒 **Segurança:**
- Lock pessimista em transações
- Atomicidade garantida por DB transactions
- Histórico imutável para auditoria
- Verificação de permissões (admin)

📊 **Transparência:**
- Usuário sempre sabe quanto tem
- Usuário vê custo antes de confirmar
- Histórico completo acessível
- Estatísticas consolidadas disponíveis
