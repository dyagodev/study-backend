# Troubleshooting: Erro 419 Page Expired

## Problema
Ao tentar fazer login no painel administrativo (`/admin/login`), você recebe o erro:
```
419 Page Expired
```

## Causas Comuns

### 1. Token CSRF Expirado
- A sessão expirou antes de enviar o formulário
- O navegador estava inativo por muito tempo

### 2. Configuração de Sessão
- Driver de sessão não configurado corretamente
- Tabela de sessões não criada (se usando database)

### 3. Cookie Bloqueado
- Navegador bloqueando cookies
- Configuração de domínio incorreta

### 4. Cache Desatualizado
- Cache de configuração desatualizado

## Soluções

### Solução 1: Limpar Cache (Mais Comum)
```bash
php artisan config:clear
php artisan view:clear
php artisan cache:clear
```

### Solução 2: Verificar Driver de Sessão

Verifique seu arquivo `.env`:

```env
SESSION_DRIVER=file
# ou
SESSION_DRIVER=database
```

Se usar `database`, execute:
```bash
php artisan session:table
php artisan migrate
```

### Solução 3: Verificar Configuração de App Key

Certifique-se que a `APP_KEY` está definida no `.env`:
```bash
php artisan key:generate
```

### Solução 4: Limpar Cookies do Navegador

1. Abra as Ferramentas de Desenvolvimento (F12)
2. Vá em Application > Storage > Clear site data
3. Recarregue a página

### Solução 5: Verificar Domínio da Sessão

No arquivo `.env`, certifique-se que:
```env
SESSION_DOMAIN=null
# ou especifique seu domínio
SESSION_DOMAIN=.seudominio.com
```

### Solução 6: Aumentar Tempo de Sessão

No arquivo `.env`:
```env
SESSION_LIFETIME=120  # em minutos
```

### Solução 7: Testar com Navegador Anônimo

Abra uma janela anônima/privada e tente novamente. Isso elimina problemas de cache do navegador.

## Verificação Rápida

### 1. Verificar se o CSRF token está sendo gerado
Abra a página `/admin/login` e inspecione o código fonte. Procure por:
```html
<meta name="csrf-token" content="...">
```
e
```html
<input type="hidden" name="_token" value="...">
```

Se ambos estiverem presentes com valores, o token está sendo gerado.

### 2. Verificar rotas
```bash
php artisan route:list | grep admin
```

Deve mostrar as rotas:
- POST /admin/login
- GET /admin/login

### 3. Verificar permissões de storage
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

## Solução Definitiva: Reconfigurar Sessões

### Passo 1: Usar file driver (mais simples)
No `.env`:
```env
SESSION_DRIVER=file
```

### Passo 2: Limpar tudo
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
rm -rf storage/framework/sessions/*
```

### Passo 3: Recriar storage/framework/sessions
```bash
mkdir -p storage/framework/sessions
chmod -R 775 storage
```

### Passo 4: Testar novamente
1. Limpe os cookies do navegador
2. Acesse `/admin/login`
3. Tente fazer login

## Se Nada Funcionar

### Método de Debug

Adicione isso temporariamente no início do método `login` do `AdminController.php`:

```php
public function login(Request $request)
{
    // DEBUG
    \Log::info('Login attempt', [
        'has_token' => $request->has('_token'),
        'token_value' => $request->input('_token'),
        'session_token' => $request->session()->token(),
        'email' => $request->input('email'),
    ]);
    
    // ... resto do código
}
```

Depois tente fazer login e verifique o log:
```bash
tail -f storage/logs/laravel.log
```

## Configuração Recomendada para Desenvolvimento

No seu `.env`:
```env
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:...

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax
```

## Testando se Funciona

Após aplicar as soluções:

1. **Limpe tudo**:
   ```bash
   php artisan config:clear && php artisan cache:clear && php artisan view:clear
   ```

2. **Limpe cookies do navegador** (F12 > Application > Clear site data)

3. **Recarregue a página** `/admin/login`

4. **Tente fazer login** com suas credenciais de admin

5. **Se funcionar**: Parabéns! ✅

6. **Se não funcionar**: Verifique o log em `storage/logs/laravel.log`

## Contato

Se o problema persistir mesmo após todas essas soluções, pode ser um problema mais específico do ambiente. Nesse caso:

1. Verifique a versão do PHP: `php -v`
2. Verifique as extensões instaladas: `php -m`
3. Certifique-se que as extensões necessárias estão ativas:
   - openssl
   - pdo
   - mbstring
   - tokenizer
   - json
   - session
