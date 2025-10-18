# Integração com Resend - Envio de Emails

Este documento descreve a integração do sistema com o serviço [Resend](https://resend.com/) para envio de emails transacionais.

## 📋 Índice

- [Visão Geral](#visão-geral)
- [Configuração](#configuração)
- [Emails Implementados](#emails-implementados)
- [Estrutura de Arquivos](#estrutura-de-arquivos)
- [Exemplos de Uso](#exemplos-de-uso)
- [Testes](#testes)
- [Troubleshooting](#troubleshooting)

## 🎯 Visão Geral

O Resend é um serviço moderno de envio de emails que oferece:
- API simples e intuitiva
- Suporte nativo no Laravel 11+
- Excelente deliverability
- Dashboard para monitoramento
- Logs e análises detalhadas

### Emails Implementados

1. **Email de Boas-vindas**: Enviado após o cadastro do usuário
2. **Email de Pagamento Aprovado**: Enviado quando o webhook confirma o pagamento PIX

## ⚙️ Configuração

### 1. Instalação

O pacote já foi instalado via Composer:

```bash
composer require resend/resend-laravel
```

### 2. Configuração do .env

Adicione as seguintes variáveis no arquivo `.env`:

```env
MAIL_MAILER=resend
MAIL_FROM_ADDRESS=seu-email@seudominio.com
MAIL_FROM_NAME="${APP_NAME}"
RESEND_API_KEY=re_xxxxxxxxxxxxxxxxxxxxxxxx
```

### 3. Obter API Key do Resend

1. Acesse [resend.com](https://resend.com/)
2. Crie uma conta ou faça login
3. Vá em **API Keys** no dashboard
4. Clique em **Create API Key**
5. Dê um nome (ex: "Production") e copie a chave
6. Cole a chave na variável `RESEND_API_KEY` do `.env`

### 4. Verificar Domínio

Para enviar emails em produção, você precisa verificar seu domínio:

1. No dashboard do Resend, vá em **Domains**
2. Clique em **Add Domain**
3. Digite seu domínio (ex: `seusite.com`)
4. Adicione os registros DNS fornecidos ao seu provedor de domínio:
   - Registro TXT para verificação
   - Registros DKIM para autenticação
   - Registro SPF (opcional, mas recomendado)
5. Aguarde a verificação (pode levar até 24h)

**Nota**: Para testes em desenvolvimento, você pode usar `onboarding@resend.dev` como remetente.

## 📧 Emails Implementados

### 1. Email de Boas-vindas (WelcomeMail)

**Classe**: `App\Mail\WelcomeMail`
**View**: `resources/views/emails/welcome.blade.php`
**Quando é enviado**: Após o registro de um novo usuário

**Conteúdo**:
- Saudação personalizada com nome do usuário
- Informações da conta (email, data de cadastro, créditos)
- Lista de recursos disponíveis
- Botão de call-to-action para acessar a plataforma

**Código**:
```php
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;

Mail::to($user->email)->send(new WelcomeMail($user));
```

### 2. Email de Pagamento Aprovado (PaymentApprovedMail)

**Classe**: `App\Mail\PaymentApprovedMail`
**View**: `resources/views/emails/payment-approved.blade.php`
**Quando é enviado**: Quando o webhook da ValidaPay confirma o pagamento

**Conteúdo**:
- Confirmação de pagamento aprovado
- Detalhes da transação (ID, data, valor, método)
- Quantidade de créditos adicionados
- Saldo atual de créditos
- Botão para acessar a plataforma

**Código**:
```php
use App\Mail\PaymentApprovedMail;
use Illuminate\Support\Facades\Mail;

Mail::to($pagamento->user->email)->send(new PaymentApprovedMail($pagamento));
```

## 📁 Estrutura de Arquivos

```
app/
  Mail/
    WelcomeMail.php                    # Mailable de boas-vindas
    PaymentApprovedMail.php            # Mailable de pagamento aprovado

resources/
  views/
    emails/
      welcome.blade.php                # Template de boas-vindas
      payment-approved.blade.php       # Template de pagamento aprovado

app/Http/Controllers/Api/
  AuthController.php                   # Envia email no registro
  PagamentoPixController.php           # Envia email no webhook
```

## 💡 Exemplos de Uso

### Enviar Email Manualmente

```php
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

$user = User::find(1);
Mail::to($user->email)->send(new WelcomeMail($user));
```

### Enviar para Múltiplos Destinatários

```php
Mail::to($user->email)
    ->cc('admin@example.com')
    ->bcc('backup@example.com')
    ->send(new WelcomeMail($user));
```

### Usar Filas (Queue)

Para melhor performance, você pode enviar emails em background:

```php
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;

Mail::to($user->email)->queue(new WelcomeMail($user));
```

Para isso, configure as filas no `.env`:

```env
QUEUE_CONNECTION=database
```

E execute o worker:

```bash
php artisan queue:work
```

## 🧪 Testes

### Testar Envio Local

Durante o desenvolvimento, você pode usar o driver `log` para ver os emails no log:

```env
MAIL_MAILER=log
```

Os emails serão salvos em `storage/logs/laravel.log`.

### Testar com Resend em Desenvolvimento

Para testar com Resend sem verificar domínio:

```env
MAIL_MAILER=resend
MAIL_FROM_ADDRESS=onboarding@resend.dev
MAIL_FROM_NAME="${APP_NAME}"
RESEND_API_KEY=sua_chave_api
```

### Comando Artisan para Testar

Você pode criar um comando para testar o envio:

```bash
php artisan tinker
```

```php
$user = App\Models\User::first();
Mail::to($user->email)->send(new App\Mail\WelcomeMail($user));
```

## 🔧 Troubleshooting

### Email não está sendo enviado

1. **Verifique a configuração do .env**:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

2. **Confira a API Key**:
   - Certifique-se que a chave está correta
   - Verifique se não há espaços extras

3. **Verifique os logs**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Erro de domínio não verificado

- Em produção, você **DEVE** verificar seu domínio
- Em desenvolvimento, use `onboarding@resend.dev`

### Email cai no spam

1. Verifique os registros DNS (SPF, DKIM)
2. Use um domínio verificado
3. Evite palavras de spam no assunto
4. Mantenha uma boa taxa de entrega

### Rate Limit

O Resend tem limites de envio:
- **Gratuito**: 100 emails/dia
- **Pro**: 50.000 emails/mês

Se atingir o limite:
1. Considere usar filas para espalhar os envios
2. Upgrade do plano se necessário

## 📊 Monitoramento

### Dashboard do Resend

Acesse o [dashboard do Resend](https://resend.com/emails) para:
- Ver todos os emails enviados
- Verificar status de entrega
- Analisar taxas de abertura
- Debugar problemas de entrega

### Logs no Laravel

Os envios são logados automaticamente:

```php
Log::info('Email de boas-vindas enviado', [
    'user_id' => $user->id,
    'email' => $user->email,
]);
```

## 🚀 Próximos Passos

Emails que podem ser implementados futuramente:

1. **Recuperação de senha**
2. **Confirmação de email**
3. **Notificação de créditos baixos**
4. **Resumo semanal de estudos**
5. **Lembrete de simulados pendentes**
6. **Novidades e atualizações do sistema**

## 📚 Referências

- [Documentação Resend](https://resend.com/docs)
- [Documentação Laravel Mail](https://laravel.com/docs/11.x/mail)
- [Resend Laravel Package](https://github.com/resendlabs/resend-laravel)

---

**Atualizado em**: 18 de outubro de 2025
