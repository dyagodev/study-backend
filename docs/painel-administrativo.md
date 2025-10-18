# Painel Administrativo - Study

## 📋 Visão Geral

O painel administrativo foi criado para gerenciar usuários, créditos, estatísticas e pagamentos do sistema Study.

## 🚀 Acesso ao Painel

### URL de Acesso
```
http://seu-dominio.com/admin/login
```

### Requisitos
- Usuário com role `admin` no banco de dados
- Credenciais de login (email e senha)

## 🎯 Funcionalidades

### 1. Dashboard Principal (`/admin`)
- **Visão geral do sistema**: Estatísticas em tempo real
- **Contadores principais**:
  - Total de usuários e usuários ativos
  - Usuários bloqueados
  - Total de questões e questões criadas hoje
  - Total de simulados e simulados criados hoje
  - Créditos distribuídos no sistema
  - Pagamentos pendentes
  - Receita total
- **Listas recentes**:
  - Últimos 10 usuários cadastrados
  - Últimos 10 simulados criados

### 2. Gerenciamento de Usuários (`/admin/usuarios`)

#### Listagem de Usuários
- Visualização de todos os usuários do sistema
- **Filtros disponíveis**:
  - Busca por nome ou email
  - Filtro por role (aluno, professor, admin)
  - Filtro por status (ativo, bloqueado)
- **Informações exibidas**:
  - ID, nome, email
  - Role (papel do usuário)
  - Quantidade de créditos
  - Total de questões criadas
  - Total de simulados criados
  - Status (ativo/bloqueado)

#### Visualizar Detalhes do Usuário (`/admin/usuarios/{id}`)
- **Informações completas**:
  - Dados pessoais (nome, email, role)
  - Status de bloqueio
  - Créditos atuais e créditos semanais
  - Última renovação de créditos
  - Próxima renovação
  - Data de cadastro
- **Estatísticas**:
  - Total de questões criadas
  - Total de simulados criados
  - Total de transações de créditos
- **Ações rápidas**:
  - ➕ **Adicionar Créditos**: Adiciona créditos manualmente
  - ➖ **Remover Créditos**: Remove créditos manualmente
  - 🔒 **Bloquear Usuário**: Bloqueia o acesso do usuário
  - 🔓 **Desbloquear Usuário**: Restaura o acesso
  - 🗑️ **Deletar Usuário**: Remove permanentemente (apenas não-admins)
- **Histórico de transações**: Últimas 20 transações de créditos

#### Editar Usuário (`/admin/usuarios/{id}/edit`)
- Edição de dados do usuário:
  - Nome
  - Email
  - Role (aluno, professor, admin)
  - Créditos atuais
  - Créditos semanais

### 3. Estatísticas (`/admin/estatisticas`)

#### Distribuição de Usuários
- Total de usuários por role
- Percentual de cada tipo

#### Cadastros ao Longo do Tempo
- Novos usuários por mês (últimos 12 meses)

#### Atividade Diária
- Questões criadas por dia (últimos 30 dias)
- Simulados criados por dia (últimos 30 dias)

#### Rankings
- **Top 10 Usuários com Mais Questões**
- **Top 10 Usuários com Mais Simulados**

### 4. Gerenciamento de Pagamentos (`/admin/pagamentos`)

#### Visão Geral
- **Estatísticas de pagamento**:
  - Total recebido
  - Pagamentos pendentes
  - Pagamentos aprovados
  - Pagamentos rejeitados

#### Lista de Pagamentos
- **Filtros disponíveis**:
  - Status (pending, approved, rejected, cancelled)
  - Período (hoje, última semana, último mês)
- **Informações exibidas**:
  - ID da transação
  - Usuário que realizou o pagamento
  - Valor em reais
  - Quantidade de créditos adquiridos
  - Status do pagamento
  - ID do pagamento (externo)
  - Data da transação

## 🔐 Sistema de Créditos

### Gerenciamento Manual
Os administradores podem:
- **Adicionar créditos**: Aumenta o saldo de créditos do usuário
- **Remover créditos**: Diminui o saldo (não fica negativo)
- **Definir descrição**: Cada transação pode ter uma descrição personalizada

### Registro de Transações
Todas as alterações de créditos são registradas com:
- Tipo de transação (adicao_manual, remocao_manual, etc.)
- Quantidade adicionada/removida
- Saldo anterior
- Saldo atual
- Descrição da operação
- Data e hora

## 🔒 Sistema de Bloqueio

### Bloquear Usuário
Quando um usuário é bloqueado:
- Campo `blocked_at` é preenchido com timestamp atual
- Todos os tokens de autenticação são revogados
- O usuário não consegue mais fazer login via API

### Desbloquear Usuário
- Remove o bloqueio (limpa o campo `blocked_at`)
- Usuário pode fazer login normalmente novamente

### Restrições
- **Administradores não podem ser bloqueados ou deletados** via painel

## 🎨 Interface

### Design
- Layout responsivo e moderno
- Sidebar fixa com navegação principal
- Cards de estatísticas com cores indicativas
- Tabelas organizadas com paginação
- Alertas de sucesso/erro para feedback

### Navegação
- **Dashboard**: Visão geral do sistema
- **Usuários**: Gerenciamento completo de usuários
- **Estatísticas**: Gráficos e relatórios
- **Pagamentos**: Controle financeiro
- **Voltar ao Site**: Retorna para o site principal
- **Sair**: Faz logout do painel

## 🔧 Instalação e Configuração

### 1. Migration
A migration para adicionar o campo `blocked_at` já foi executada:
```bash
php artisan migrate
```

### 2. Middleware
O middleware `admin` foi registrado automaticamente em `bootstrap/app.php`.

### 3. Criar Usuário Admin
Para criar um usuário administrador, use o Tinker:

```bash
php artisan tinker
```

```php
$user = User::find(1); // ou crie um novo
$user->role = 'admin';
$user->save();
```

Ou crie um novo usuário admin:
```php
User::create([
    'name' => 'Admin',
    'email' => 'admin@study.com',
    'password' => bcrypt('senha123'),
    'role' => 'admin',
    'creditos' => 1000,
    'creditos_semanais' => 100,
]);
```

## 📝 Rotas Disponíveis

### Autenticação
- `GET /admin/login` - Formulário de login
- `POST /admin/login` - Processa login
- `POST /admin/logout` - Faz logout

### Dashboard e Estatísticas
- `GET /admin` - Dashboard principal
- `GET /admin/estatisticas` - Estatísticas detalhadas
- `GET /admin/pagamentos` - Lista de pagamentos

### Usuários
- `GET /admin/usuarios` - Lista de usuários
- `GET /admin/usuarios/{id}` - Detalhes do usuário
- `GET /admin/usuarios/{id}/edit` - Formulário de edição
- `PUT /admin/usuarios/{id}` - Atualiza usuário
- `DELETE /admin/usuarios/{id}` - Deleta usuário

### Ações de Usuários
- `POST /admin/usuarios/{id}/adicionar-creditos` - Adiciona créditos
- `POST /admin/usuarios/{id}/remover-creditos` - Remove créditos
- `POST /admin/usuarios/{id}/bloquear` - Bloqueia usuário
- `POST /admin/usuarios/{id}/desbloquear` - Desbloqueia usuário

## 🛡️ Segurança

### Proteções Implementadas
1. **Autenticação obrigatória**: Todas as rotas (exceto login) exigem autenticação
2. **Verificação de role**: Apenas usuários com role `admin` têm acesso
3. **Proteção contra auto-bloqueio**: Admins não podem bloquear outros admins
4. **Proteção contra deleção**: Admins não podem ser deletados
5. **Regeneração de sessão**: Tokens são regenerados no login/logout
6. **Validação de dados**: Todos os formulários possuem validação

## 📊 Relatórios e Análises

### Métricas Disponíveis
1. **Crescimento de usuários** ao longo do tempo
2. **Atividade de criação** de questões e simulados
3. **Distribuição de roles** no sistema
4. **Usuários mais ativos** (por questões e simulados)
5. **Status financeiro** (receita, pagamentos pendentes)

## 🎯 Casos de Uso Comuns

### 1. Dar créditos de bônus para um usuário
1. Acesse `/admin/usuarios`
2. Busque ou filtre o usuário
3. Clique em "Ver"
4. Na seção "Ações Rápidas", insira a quantidade
5. Opcionalmente adicione uma descrição (ex: "Bônus de boas-vindas")
6. Clique em "➕ Adicionar Créditos"

### 2. Bloquear um usuário problemático
1. Acesse `/admin/usuarios/{id}`
2. Clique em "🔒 Bloquear"
3. Confirme a ação
4. O usuário será imediatamente desconectado

### 3. Verificar receita do mês
1. Acesse `/admin/pagamentos`
2. Use o filtro "Período" → "Último Mês"
3. Filtre por status "Aprovado"
4. O card "Total Recebido" mostrará o valor

### 4. Identificar usuários mais engajados
1. Acesse `/admin/estatisticas`
2. Veja os rankings:
   - "Top 10 - Usuários com Mais Questões"
   - "Top 10 - Usuários com Mais Simulados"

## 🐛 Troubleshooting

### Não consigo fazer login
- Verifique se o usuário tem role `admin` no banco
- Confirme email e senha
- Verifique se o usuário não está bloqueado

### Erro 403 ao acessar
- Verifique se está autenticado
- Confirme se o usuário tem role `admin`

### Estatísticas vazias
- Normal se for um sistema novo
- Crie alguns dados de teste para popular

## 🔄 Próximas Melhorias Sugeridas

1. **Exportação de relatórios** (CSV, PDF)
2. **Gráficos visuais** (Chart.js, ApexCharts)
3. **Notificações push** para admins
4. **Log de ações** dos administradores
5. **Filtros avançados** com data ranges
6. **Bulk actions** (bloquear múltiplos usuários)
7. **Dashboard personalizável** (drag and drop widgets)

## 📞 Suporte

Para dúvidas ou problemas:
1. Verifique esta documentação
2. Consulte os logs em `storage/logs/laravel.log`
3. Use `php artisan tinker` para debug manual
