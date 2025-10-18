# Sistema de Créditos Semanais

## Visão Geral

O sistema implementa **créditos semanais** que são renovados automaticamente toda semana, permitindo que os usuários tenham um limite de uso renovável.

## Como Funciona

### Renovação Automática

- ✅ **Créditos renovam toda SEGUNDA-FEIRA às 00:00** (horário de Brasília)
- ✅ **Verificação em tempo real** ao usar qualquer funcionalidade
- ✅ **Reset automático** se passaram 7 dias desde a última renovação
- ✅ **50 créditos semanais** por padrão (configurável)

### Campos no Banco de Dados

```sql
users
├── creditos (integer)              -- Saldo atual
├── creditos_semanais (integer)     -- Quantidade que recebe toda semana (default: 50)
└── ultima_renovacao (timestamp)    -- Data da última renovação
```

## Lógica de Renovação

### 1. Automática (Tempo Real)

Sempre que o usuário tenta usar créditos, o sistema:

```php
1. Verifica se ultima_renovacao existe
2. Calcula dias desde a última renovação
3. Se >= 7 dias:
   - Reseta creditos = creditos_semanais
   - Atualiza ultima_renovacao = agora
4. Prossegue com o uso normal
```

### 2. Agendada (Cron)

Um comando roda toda segunda às 00:00:

```bash
php artisan creditos:renovar-semanais
```

Esse comando:
- Busca TODOS os usuários
- Reseta `creditos = creditos_semanais`
- Atualiza `ultima_renovacao = agora`

## API Atualizada

### Consultar Saldo

**Endpoint:** `GET /api/creditos/saldo`

**Resposta:**
```json
{
  "success": true,
  "data": {
    "creditos": 35,
    "creditos_semanais": 50,
    "dias_para_renovacao": 3,
    "proxima_renovacao": "2025-10-21 00:00:00",
    "ultima_renovacao": "2025-10-14 00:00:00"
  }
}
```

**Campos:**
- `creditos`: Saldo atual
- `creditos_semanais`: Quantidade que receberá na renovação
- `dias_para_renovacao`: Quantos dias faltam (0-7)
- `proxima_renovacao`: Data exata da próxima renovação
- `ultima_renovacao`: Quando foi a última renovação

## Comandos Artisan

### Renovar Manualmente

Força a renovação de todos os usuários imediatamente:

```bash
php artisan creditos:renovar-semanais
```

**Saída:**
```
Iniciando renovação de créditos semanais...
✅ 150 usuário(s) tiveram seus créditos renovados!
Créditos semanais: 50
```

### Verificar Agendamento

```bash
php artisan schedule:list
```

Deve mostrar:
```
0 0 * * 1  php artisan creditos:renovar-semanais  America/Sao_Paulo
```

## Configuração do Cron

### Servidor Linux/Ubuntu

Adicione ao crontab:

```bash
crontab -e
```

Adicione a linha:
```
* * * * * cd /caminho/do/projeto && php artisan schedule:run >> /dev/null 2>&1
```

### Laravel Forge / Herd / Valet

O schedule já roda automaticamente.

### Servidor Compartilhado

Configure um cron job que rode a cada minuto:
```
* * * * * /usr/bin/php /caminho/completo/artisan schedule:run
```

## Casos de Uso

### 1. Usuário Novo Se Cadastra

```
1. User cria conta
2. creditos = 50 (default)
3. creditos_semanais = 50 (default)
4. ultima_renovacao = NULL
5. Ao primeiro uso:
   - Sistema define ultima_renovacao = agora
```

### 2. Usuário Usa Créditos Durante a Semana

```
Segunda às 00:00: 50 créditos
Terça: Usa 20 → 80 créditos restantes
Quarta: Usa 30 → 50 créditos restantes
Sexta: Usa 40 → 10 créditos restantes
Domingo: Usa 5 → 5 créditos restantes
```

### 3. Renovação Semanal

```
Segunda às 00:00 (7 dias depois):
- Sistema detecta que passou 1 semana
- creditos = 50 (resetado!)
- ultima_renovacao = agora
- User pode usar 50 créditos novamente
```

### 4. Usuário Inativo Volta Após 3 Semanas

```
1. Última renovação: 21 dias atrás
2. User tenta gerar questão
3. Sistema verifica: 21 dias >= 7 dias ✅
4. Reseta automaticamente: creditos = 50
5. Atualiza ultima_renovacao = agora
6. Prossegue com a geração
```

## Personalização de Créditos Semanais

### Admin Pode Alterar Limite Semanal

Adicione endpoint no `CreditoController`:

```php
public function alterarCreditosSemanais(Request $request)
{
    if (!$request->user()->isAdmin()) {
        return response()->json(['success' => false, 'message' => 'Não autorizado'], 403);
    }

    $request->validate([
        'user_id' => 'required|exists:users,id',
        'creditos_semanais' => 'required|integer|min:0|max:1000',
    ]);

    $user = User::find($request->user_id);
    $user->creditos_semanais = $request->creditos_semanais;
    $user->save();

    return response()->json([
        'success' => true,
        'message' => 'Créditos semanais atualizados',
        'data' => [
            'usuario' => $user->name,
            'creditos_semanais' => $user->creditos_semanais,
        ],
    ]);
}
```

Rota:
```php
Route::post('/creditos/alterar-semanais', [CreditoController::class, 'alterarCreditosSemanais']);
```

Uso:
```bash
curl -X POST http://localhost/api/creditos/alterar-semanais \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 15,
    "creditos_semanais": 200
  }'
```

## Planos/Tiers (Exemplo)

Você pode criar planos com diferentes limites semanais:

| Plano | Créditos Semanais | Preço |
|-------|------------------|-------|
| **Gratuito** | 50 | R$ 0 |
| **Básico** | 300 | R$ 19,90/mês |
| **Pro** | 1000 | R$ 49,90/mês |
| **Premium** | Ilimitado* | R$ 99,90/mês |

*Ilimitado = 999999 créditos semanais

## Vantagens do Sistema Semanal

✅ **Controle de Uso:** Evita abuso do sistema  
✅ **Sustentabilidade:** Gerencia custos de API (OpenAI)  
✅ **Engajamento:** Usuários voltam toda semana  
✅ **Monetização:** Base para planos pagos  
✅ **Justo:** Todos recebem renovação igual  
✅ **Automático:** Sem intervenção manual necessária  

## Frontend - Exemplos de UI

### Widget de Créditos

```javascript
async function mostrarSaldoCreditos() {
  const response = await fetch('/api/creditos/saldo', {
    headers: { 'Authorization': `Bearer ${token}` }
  });
  const { data } = await response.json();
  
  return `
    <div class="creditos-widget">
      <div class="saldo">
        <span class="numero">${data.creditos}</span>
        <span class="label">créditos</span>
      </div>
      <div class="renovacao">
        <small>Renova em ${data.dias_para_renovacao} dias</small>
        <progress value="${7 - data.dias_para_renovacao}" max="7"></progress>
      </div>
      <div class="info">
        <small>Você recebe ${data.creditos_semanais} créditos toda segunda-feira</small>
      </div>
    </div>
  `;
}
```

### Alerta de Créditos Baixos

```javascript
if (data.creditos < 10) {
  mostrarAlerta(`
    ⚠️ Você tem apenas ${data.creditos} créditos restantes!
    Seus créditos serão renovados em ${data.dias_para_renovacao} dias.
  `);
}
```

### Countdown para Renovação

```javascript
function countdownRenovacao(proximaRenovacao) {
  const agora = new Date();
  const renovacao = new Date(proximaRenovacao);
  const diff = renovacao - agora;
  
  const dias = Math.floor(diff / (1000 * 60 * 60 * 24));
  const horas = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  
  return `${dias}d ${horas}h até renovação`;
}
```

## Testes

### 1. Testar Renovação Manual

```bash
# Ver saldo atual
php artisan tinker
>>> $user = User::first();
>>> $user->creditos
=> 45

# Forçar renovação
>>> exit
php artisan creditos:renovar-semanais

# Verificar novo saldo
php artisan tinker
>>> $user->fresh()->creditos
=> 50
```

### 2. Testar Renovação Automática

```bash
php artisan tinker

# Simular que passou 1 semana
>>> $user = User::first();
>>> $user->ultima_renovacao = now()->subDays(7);
>>> $user->creditos = 10;
>>> $user->save();

# Tentar usar créditos (vai renovar automaticamente)
>>> $user->fresh()->temCreditos(5);
=> true

>>> $user->fresh()->creditos
=> 100  // Renovado!
```

### 3. Testar Endpoint

```bash
curl http://localhost/api/creditos/saldo \
  -H "Authorization: Bearer {token}"
```

Deve retornar:
```json
{
  "success": true,
  "data": {
    "creditos": 100,
    "creditos_semanais": 100,
    "dias_para_renovacao": 7,
    "proxima_renovacao": "2025-10-21 00:00:00",
    "ultima_renovacao": "2025-10-14 00:00:00"
  }
}
```

## Monitoramento

### Query: Usuários com Créditos Zerados

```sql
SELECT id, name, email, creditos, 
       DATEDIFF(NOW(), ultima_renovacao) as dias_desde_renovacao
FROM users 
WHERE creditos = 0 
ORDER BY ultima_renovacao DESC;
```

### Query: Estatísticas de Consumo Semanal

```sql
SELECT 
    COUNT(*) as total_usuarios,
    AVG(creditos) as media_creditos_restantes,
    SUM(CASE WHEN creditos < 10 THEN 1 ELSE 0 END) as usuarios_quase_sem_creditos
FROM users;
```

### Query: Próximas Renovações

```sql
SELECT name, creditos, 
       DATE_ADD(ultima_renovacao, INTERVAL 7 DAY) as proxima_renovacao
FROM users
WHERE ultima_renovacao IS NOT NULL
ORDER BY proxima_renovacao ASC
LIMIT 10;
```

## Troubleshooting

### Cron não está rodando?

**Verificar:**
```bash
php artisan schedule:list
```

**Testar manualmente:**
```bash
php artisan schedule:run
```

**Ver logs:**
```bash
tail -f storage/logs/laravel.log
```

### Renovação não acontece automaticamente?

Verifique se o método `verificarERenovarCreditos()` está sendo chamado:
- No método `temCreditos()` do User
- Antes de qualquer operação que use créditos

### Usuários reclamando de créditos zerados?

Possíveis causas:
1. Cron não está configurado
2. Timezone incorreto
3. Última renovação NULL

**Solução temporária:**
```bash
php artisan creditos:renovar-semanais
```

## Configurações Avançadas

### Alterar Dia da Semana

No `routes/console.php`:

```php
// Renovar aos domingos
Schedule::command('creditos:renovar-semanais')
    ->weekly()
    ->sundays()
    ->at('00:00');

// Renovar aos sábados
Schedule::command('creditos:renovar-semanais')
    ->weekly()
    ->saturdays()
    ->at('00:00');
```

### Alterar Horário

```php
Schedule::command('creditos:renovar-semanais')
    ->weekly()
    ->mondays()
    ->at('03:00'); // 3h da manhã
```

### Notificar Usuários

```php
Schedule::command('creditos:renovar-semanais')
    ->weekly()
    ->mondays()
    ->at('00:00')
    ->before(function () {
        // Enviar email avisando que vai renovar
    })
    ->after(function () {
        // Enviar notificação de renovação concluída
    });
```

## Migração de Usuários Existentes

Se você já tem usuários no sistema:

```php
// Em um comando ou seeder
$usuarios = User::whereNull('ultima_renovacao')->get();

foreach ($usuarios as $usuario) {
    $usuario->creditos = 100;
    $usuario->creditos_semanais = 100;
    $usuario->ultima_renovacao = now();
    $usuario->save();
}
```

## Resumo

✅ **Créditos semanais**: 100 por padrão  
✅ **Renovação**: Toda segunda às 00:00  
✅ **Automático**: Verifica e renova em tempo real  
✅ **Comando**: `php artisan creditos:renovar-semanais`  
✅ **Customizável**: Admin pode alterar limite por usuário  
✅ **Escalável**: Base para planos premium  

🎯 **Resultado**: Sistema sustentável que controla uso de IA mantendo boa experiência do usuário!
