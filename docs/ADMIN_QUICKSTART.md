# 🚀 Quick Start - Painel Administrativo

## Passo 1: Executar a Migration

```bash
php artisan migrate
```

Isso criará o campo `blocked_at` na tabela `users`.

## Passo 2: Criar um Usuário Admin

Execute o comando interativo:

```bash
php artisan admin:create
```

Você será solicitado a informar:
- Nome do administrador
- Email do administrador  
- Senha (mínimo 6 caracteres)
- Confirmação da senha
- Créditos iniciais (padrão: 1000)
- Créditos semanais (padrão: 100)

### Exemplo:
```
=================================
  Criar Novo Usuário Admin
=================================

Nome do administrador: João Silva
Email do administrador: admin@study.com
Senha (mínimo 6 caracteres): ******
Confirme a senha: ******
Créditos iniciais (padrão: 1000): 1000
Créditos semanais (padrão: 100): 100

Dados do usuário:
Nome: João Silva
Email: admin@study.com
Role: admin
Créditos: 1000
Créditos Semanais: 100

Deseja criar este usuário admin? (yes/no) [yes]: yes

✓ Usuário admin criado com sucesso!

Acesse o painel em: /admin/login
Email: admin@study.com
```

## Passo 3: Acessar o Painel

1. Abra seu navegador
2. Acesse: `http://seu-dominio.com/admin/login`
3. Faça login com as credenciais criadas
4. Pronto! Você terá acesso ao painel administrativo completo

## 📋 URLs do Painel

- **Login**: `/admin/login`
- **Dashboard**: `/admin`
- **Usuários**: `/admin/usuarios`
- **Estatísticas**: `/admin/estatisticas`
- **Pagamentos**: `/admin/pagamentos`

## 🔐 Alternativamente: Criar Admin via Tinker

Se preferir, você pode usar o Tinker:

```bash
php artisan tinker
```

```php
User::create([
    'name' => 'Admin',
    'email' => 'admin@study.com',
    'password' => bcrypt('senha123'),
    'role' => 'admin',
    'creditos' => 1000,
    'creditos_semanais' => 100,
    'ultima_renovacao' => now(),
]);
```

## ✅ Checklist de Verificação

- [ ] Migration executada (`php artisan migrate`)
- [ ] Usuário admin criado (`php artisan admin:create`)
- [ ] Testou o login em `/admin/login`
- [ ] Acessou o dashboard em `/admin`

## 🎯 Funcionalidades Disponíveis

### Dashboard Principal
✓ Estatísticas em tempo real
✓ Usuários recentes
✓ Simulados recentes

### Gerenciamento de Usuários
✓ Listar, filtrar e buscar usuários
✓ Visualizar detalhes completos
✓ Editar informações
✓ Adicionar/remover créditos
✓ Bloquear/desbloquear usuários
✓ Deletar usuários (exceto admins)

### Estatísticas
✓ Distribuição por role
✓ Cadastros por mês
✓ Atividade diária
✓ Rankings de usuários

### Pagamentos
✓ Lista de transações
✓ Filtros por status e período
✓ Estatísticas financeiras

## 📚 Documentação Completa

Para documentação detalhada, consulte:
- `/docs/painel-administrativo.md` - Documentação completa

## 🆘 Problemas Comuns

### Erro 403 ao acessar
**Solução**: Verifique se o usuário tem `role = 'admin'` no banco de dados.

### Não consigo fazer login
**Solução**: 
1. Verifique email e senha
2. Confirme que o usuário existe no banco
3. Verifique se não está bloqueado (`blocked_at` deve ser NULL)

### Página não encontrada
**Solução**: Execute `php artisan route:list` para ver se as rotas estão registradas.

## 🎨 Interface

O painel possui:
- ✅ Design moderno e responsivo
- ✅ Navegação intuitiva via sidebar
- ✅ Cards de estatísticas coloridos
- ✅ Tabelas organizadas com paginação
- ✅ Filtros e busca avançada
- ✅ Feedback visual (alertas de sucesso/erro)

## 🔒 Segurança

- ✓ Autenticação obrigatória
- ✓ Verificação de role admin
- ✓ Proteção contra CSRF
- ✓ Validação de dados
- ✓ Sanitização de inputs
- ✓ Proteção contra auto-bloqueio de admins

---

**Pronto para usar! 🎉**

Se tiver dúvidas, consulte a documentação completa em `/docs/painel-administrativo.md`.
