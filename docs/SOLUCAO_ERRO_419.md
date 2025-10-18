# Solução para Erro 419 - Page Expired

## Problema
Erro 419 ao tentar fazer login ou qualquer requisição POST na aplicação Laravel.

## Causa
O erro 419 "Page Expired" ocorre quando o token CSRF (Cross-Site Request Forgery) falha na validação. Isso pode acontecer por vários motivos:

1. **Configuração duplicada de SESSION_DOMAIN no .env**
2. **Cache de configuração desatualizado**
3. **Problemas com armazenamento de sessão**
4. **Permissões incorretas nas pastas de storage**
5. **Domínios não configurados corretamente no Sanctum**

## Soluções Aplicadas

### 1. Correção do arquivo .env

**Problema encontrado:** `SESSION_DOMAIN` estava duplicado no arquivo `.env` (linha 33 e 77).

**Correção aplicada:**
```env
# Configurações de Sessão (corrigidas)
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax

# CORS Configuration (corrigida)
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000,localhost,127.0.0.1
```

### 2. Limpeza de Cache

Execute os seguintes comandos:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### 3. Permissões de Pastas

Certifique-se de que as pastas de storage têm as permissões corretas:

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
mkdir -p storage/framework/sessions
```

### 4. Verificação da Migration

A tabela `sessions` deve existir no banco de dados. Verifique com:

```bash
php artisan migrate:status
```

Se necessário, execute as migrations:

```bash
php artisan migrate
```

## Verificações Adicionais

### 1. Verifique se o APP_KEY está configurado

```bash
php artisan key:generate
```

### 2. Teste a sessão no Tinker

```bash
php artisan tinker
>>> session()->put('test', 'value')
>>> session()->get('test')
```

### 3. Verifique os logs

Se o erro persistir, verifique os logs em:
```
storage/logs/laravel.log
```

## Configurações Importantes

### config/session.php

Certifique-se de que as seguintes configurações estão corretas:

```php
'driver' => env('SESSION_DRIVER', 'database'),
'domain' => env('SESSION_DOMAIN'),
'secure' => env('SESSION_SECURE_COOKIE'),
'same_site' => env('SESSION_SAME_SITE', 'lax'),
```

### Middleware CSRF

O Laravel Breeze já inclui o middleware `VerifyCsrfToken` automaticamente. 
Em Laravel 11, não há mais arquivo `Kernel.php`, a configuração de middleware é feita em `bootstrap/app.php`.

### Blade Templates

Certifique-se de que seus formulários incluem a diretiva `@csrf`:

```blade
<form method="POST" action="{{ route('login') }}">
    @csrf
    <!-- campos do formulário -->
</form>
```

## Testando a Solução

1. Abra o navegador em modo anônimo/privado
2. Limpe os cookies do navegador
3. Acesse a página de login: `http://localhost:8000/login`
4. Tente fazer login

## Troubleshooting Adicional

### Se ainda houver erro 419:

1. **Verifique o domínio usado:**
   - Use `localhost` ou `127.0.0.1` consistentemente
   - Não misture diferentes domínios (ex: localhost e 127.0.0.1)

2. **Inspecione os cookies:**
   - Abra DevTools (F12) → Application → Cookies
   - Verifique se existe um cookie de sessão (ex: `laravel-session`)
   - Verifique se o cookie tem o domínio correto

3. **Teste com SESSION_DRIVER=file:**
   ```env
   SESSION_DRIVER=file
   ```
   Execute `php artisan config:clear` e teste novamente

4. **Verifique HTTPS:**
   Se estiver usando HTTPS local:
   ```env
   SESSION_SECURE_COOKIE=true
   ```

5. **Desabilite temporariamente o CSRF para teste:**
   ⚠️ **APENAS PARA TESTE LOCAL - NUNCA EM PRODUÇÃO**
   
   Crie `app/Http/Middleware/VerifyCsrfToken.php`:
   ```php
   <?php

   namespace App\Http\Middleware;

   use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

   class VerifyCsrfToken extends Middleware
   {
       protected $except = [
           // Adicione rotas que devem ser excluídas (apenas para teste)
       ];
   }
   ```

## Links Úteis

- [Laravel Session Configuration](https://laravel.com/docs/11.x/session)
- [CSRF Protection](https://laravel.com/docs/11.x/csrf)
- [Laravel Sanctum SPA Authentication](https://laravel.com/docs/11.x/sanctum#spa-authentication)

## Data da Correção
18 de outubro de 2025
