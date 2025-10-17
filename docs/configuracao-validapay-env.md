# Configuração de Variáveis de Ambiente - ValidaPay

## 📋 Variáveis Obrigatórias

Adicione as seguintes variáveis no arquivo `.env`:

```bash
# ==============================================
# VALIDAPAY API - Configurações
# ==============================================

# URL de Autenticação OAuth2
VALIDAPAY_AUTH_URL=https://auth.validapay.com

# URL da API Principal
VALIDAPAY_API_URL=https://api.validapay.com

# Credenciais OAuth2
VALIDAPAY_CLIENT_ID=seu_client_id_aqui
VALIDAPAY_CLIENT_SECRET=seu_client_secret_aqui

# Chave PIX (CPF, CNPJ, Email, Telefone ou Chave Aleatória)
VALIDAPAY_CHAVE_PIX=sua_chave_pix_aqui

# Número da Conta ValidaPay (obrigatório para header X-Account-Number)
VALIDAPAY_ACCOUNT_NUMBER=123456
```

---

## 🔑 Como Obter as Credenciais

### 1. Cadastro na ValidaPay

1. Acesse o site da ValidaPay
2. Crie uma conta empresarial
3. Valide seu cadastro e documentos

### 2. Criar Aplicação OAuth2

1. Acesse o painel de desenvolvedor
2. Crie uma nova aplicação
3. Anote o `CLIENT_ID` e `CLIENT_SECRET`

### 3. Configurar Chave PIX

1. Registre uma chave PIX na plataforma
2. Pode ser:
   - CPF/CNPJ: `12345678900`
   - Email: `pagamentos@seusite.com`
   - Telefone: `+5511999999999`
   - Chave Aleatória: `abc123-def456-ghi789`

### 4. Obter Número da Conta

1. Localize o número da sua conta no dashboard
2. É um número inteiro (ex: `123456`)
3. **Importante**: Este número é usado no header `X-Account-Number`

---

## 🧪 Ambiente de Testes (Sandbox)

Se a ValidaPay disponibilizar sandbox, use URLs de teste:

```bash
# Sandbox URLs (verificar com ValidaPay)
VALIDAPAY_AUTH_URL=https://auth.sandbox.validapay.com
VALIDAPAY_API_URL=https://api.sandbox.validapay.com

# Credenciais de teste fornecidas pela ValidaPay
VALIDAPAY_CLIENT_ID=test_client_id
VALIDAPAY_CLIENT_SECRET=test_client_secret
VALIDAPAY_CHAVE_PIX=test_chave_pix
VALIDAPAY_ACCOUNT_NUMBER=999999
```

---

## ✅ Validação da Configuração

### Teste no Tinker

```bash
php artisan tinker
```

```php
// Testar autenticação
$service = app(\App\Services\ValidaPayService::class);
$token = $service->getAccessToken();
echo $token; // Deve exibir um token JWT

// Testar criação de cobrança (valor pequeno)
$resultado = $service->criarCobranca(
    valor: 0.01,
    webhookUrl: 'https://webhook.site/seu-webhook-id'
);
print_r($resultado); // Deve retornar transactionId e emv
```

### Teste via API

```bash
# 1. Obter token
curl -X POST "$(php -r "echo env('VALIDAPAY_AUTH_URL');")/oauth2/token" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=client_credentials" \
  -d "client_id=$(php -r "echo env('VALIDAPAY_CLIENT_ID');")" \
  -d "client_secret=$(php -r "echo env('VALIDAPAY_CLIENT_SECRET');")"

# 2. Criar cobrança (substitua {TOKEN} e {ACCOUNT_NUMBER})
curl -X POST "$(php -r "echo env('VALIDAPAY_API_URL');")/pix?eventType=cob_pix" \
  -H "Authorization: Bearer {TOKEN}" \
  -H "X-Account-Number: {ACCOUNT_NUMBER}" \
  -H "Content-Type: application/json" \
  -d '{"amount": 0.01, "webhook_url": "https://webhook.site/seu-id", "split": []}'
```

---

## 🔒 Segurança

### Boas Práticas

1. **NUNCA** commite o arquivo `.env` no Git
2. Use valores diferentes em desenvolvimento, staging e produção
3. Rotacione as credenciais periodicamente
4. Use variáveis de ambiente do servidor em produção (não arquivo `.env`)
5. Limite o acesso às credenciais apenas para equipe necessária

### Exemplo .env.example

Crie um arquivo `.env.example` (SEM valores reais):

```bash
# ValidaPay API
VALIDAPAY_AUTH_URL=
VALIDAPAY_API_URL=
VALIDAPAY_CLIENT_ID=
VALIDAPAY_CLIENT_SECRET=
VALIDAPAY_CHAVE_PIX=
VALIDAPAY_ACCOUNT_NUMBER=
```

---

## 🚨 Troubleshooting

### Erro: "CLIENT_ID not configured"

**Solução**: Verifique se as variáveis estão no `.env` e rode:

```bash
php artisan config:clear
php artisan cache:clear
```

### Erro: "Invalid client credentials"

**Soluções**:
1. Confirme `CLIENT_ID` e `CLIENT_SECRET` no painel ValidaPay
2. Verifique se copiou corretamente (sem espaços extras)
3. Tente regenerar as credenciais

### Erro: "X-Account-Number header required"

**Solução**: Adicione `VALIDAPAY_ACCOUNT_NUMBER` no `.env`

### Erro: "Invalid PIX key"

**Solução**: Verifique se a chave PIX está registrada e ativa no painel ValidaPay

---

## 📝 Checklist de Configuração

Antes de ir para produção:

- [ ] Todas as variáveis adicionadas no `.env`
- [ ] Credenciais testadas via Tinker
- [ ] Cobrança de teste criada com sucesso
- [ ] Webhook configurado e testado
- [ ] Cache limpo após configuração (`config:clear`)
- [ ] `.env` adicionado ao `.gitignore`
- [ ] `.env.example` atualizado (sem valores reais)
- [ ] Documentação interna atualizada
- [ ] Equipe tem acesso às credenciais (via vault/secrets manager)

---

## 🔗 Links Úteis

- **Painel ValidaPay**: https://dashboard.validapay.com (verificar URL real)
- **Documentação API**: Consulte com ValidaPay
- **Suporte**: suporte@validapay.com (verificar email real)
- **Webhook Tester**: https://webhook.site

---

## 📞 Suporte

Em caso de dúvidas sobre as credenciais, entre em contato com:

- **Email**: suporte@validapay.com
- **Telefone**: (11) xxxx-xxxx
- **Chat**: Disponível no dashboard

**Nota**: URLs e contatos são exemplos. Verifique as informações corretas na documentação oficial da ValidaPay.
