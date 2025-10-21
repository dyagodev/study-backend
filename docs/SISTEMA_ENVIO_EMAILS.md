# Sistema de Envio de E-mails - Painel Administrativo

**Data de Implementação:** 21 de outubro de 2025

## 📧 Visão Geral

Sistema completo de envio de e-mails para usuários através do painel administrativo, permitindo comunicação individual ou em massa com os usuários da plataforma.

## ✨ Funcionalidades Implementadas

### 1. Envio de E-mail Individual

**Rota:** `/admin/usuarios/{id}/enviar-email`

**Descrição:** Permite enviar e-mail personalizado para um usuário específico.

**Características:**
- Formulário de composição de e-mail
- Visualização de informações do destinatário (nome, e-mail, role)
- Validação de campos obrigatórios
- Mensagem de sucesso/erro após envio
- Template de e-mail personalizado com o nome do usuário

**Acesso:**
- Através da página de detalhes do usuário
- Botão "Enviar E-mail" no topo da página

### 2. Envio de E-mail para Usuários Selecionados

**Rota:** `/admin/usuarios-email-selecionados` (POST)

**Descrição:** Permite selecionar usuários específicos na listagem e enviar e-mail para eles.

**Características:**
- Checkboxes na listagem de usuários para seleção individual
- Checkbox "Selecionar Todos" no cabeçalho da tabela
- Contador em tempo real de usuários selecionados
- Botão "Enviar E-mail" que aparece quando há usuários selecionados
- Modal com formulário de composição de e-mail
- Confirmação antes do envio
- Relatório de envio (sucessos e falhas)

**Acesso:**
- Na página de listagem de usuários (`/admin/usuarios`)
- Selecione os usuários desejados com checkboxes
- Clique no botão "Enviar E-mail (X)" no topo da página

### 3. Envio de E-mail em Massa por Categoria

**Rota:** `/admin/usuarios-email-massa`

**Descrição:** Permite enviar e-mail para múltiplos usuários de uma vez baseado em categorias.

**Filtros de Destinatários:**
- **Todos os Usuários**: Envia para todos os cadastrados
- **Apenas Alunos**: Filtra usuários com role 'aluno'
- **Apenas Professores**: Filtra usuários com role 'professor'
- **Apenas Administradores**: Filtra usuários com role 'admin'
- **Usuários Ativos**: Usuários que se cadastraram nos últimos 30 dias

**Características:**
- Dashboard com estatísticas de usuários por categoria
- Seleção de grupo de destinatários
- Contador de quantos e-mails serão enviados
- Confirmação antes do envio
- Relatório de envio (sucessos e falhas)
- Log de erros para troubleshooting

**Acesso:**
- Menu lateral do painel admin: "E-mail em Massa"

## 🎨 Template de E-mail

**Arquivo:** `resources/views/emails/admin-notification.blade.php`

**Características do Template:**
- Design responsivo e moderno
- Header com branding da plataforma
- Personalização automática com nome do usuário
- Área destacada para a mensagem
- Footer com informações da plataforma
- Estilo profissional com gradiente roxo/azul

**Estrutura:**
```
┌─────────────────────────────────┐
│   ⚡ Study Platform (Header)    │
├─────────────────────────────────┤
│   Olá, [Nome do Usuário]!       │
│                                 │
│   ┌─────────────────────────┐   │
│   │ [Mensagem Personalizada]│   │
│   └─────────────────────────┘   │
│                                 │
│   Atenciosamente,               │
│   Equipe Study Platform         │
├─────────────────────────────────┤
│   Footer (Informações)          │
└─────────────────────────────────┘
```

## 📂 Arquivos Criados/Modificados

### Novos Arquivos

1. **app/Mail/AdminNotificationMail.php**
   - Mailable class para envio de e-mails administrativos
   - Recebe: assunto, mensagem, nome do usuário

2. **resources/views/emails/admin-notification.blade.php**
   - Template HTML do e-mail
   - Design responsivo e profissional

3. **resources/views/admin/usuarios/enviar-email.blade.php**
   - Formulário para envio individual
   - Exibição de informações do destinatário

4. **resources/views/admin/usuarios/enviar-email-massa.blade.php**
   - Formulário para envio em massa
   - Dashboard de estatísticas
   - Seletor de grupos

### Arquivos Modificados

1. **app/Http/Controllers/AdminController.php**
   - Adicionados métodos:
     - `enviarEmailForm($id)` - Exibe formulário individual
     - `enviarEmail(Request $request, $id)` - Processa envio individual
     - `enviarEmailMassaForm()` - Exibe formulário em massa
     - `enviarEmailMassa(Request $request)` - Processa envio em massa
     - `enviarEmailSelecionados(Request $request)` - Processa envio para selecionados
   - Importações: `Mail` facade e `AdminNotificationMail`

2. **routes/web.php**
   - Rotas adicionadas:
     - `GET /admin/usuarios/{id}/enviar-email`
     - `POST /admin/usuarios/{id}/enviar-email`
     - `GET /admin/usuarios-email-massa`
     - `POST /admin/usuarios-email-massa`
     - `POST /admin/usuarios-email-selecionados`

3. **resources/views/admin/usuarios/modern-index.blade.php**
   - Checkboxes para seleção de usuários
   - Botão "Enviar E-mail" com contador de selecionados
   - Modal para composição de e-mail
   - JavaScript para gerenciar seleção e modal

4. **resources/views/admin/usuarios/modern-show.blade.php**
   - Botão "Enviar E-mail" adicionado no topo da página

5. **resources/views/admin/modern-layout.blade.php**
   - Link "E-mail em Massa" adicionado ao menu lateral

6. **resources/views/emails/admin-notification.blade.php**
   - Botão CTA "Acessar a Plataforma" adicionado

## 🔧 Como Usar

### Enviar E-mail Individual

1. Acesse o painel admin em `/admin`
2. Vá para "Usuários" no menu lateral
3. Clique em "Ver" no usuário desejado
4. Clique no botão "Enviar E-mail"
5. Preencha o assunto e a mensagem
6. Clique em "Enviar E-mail"

### Enviar E-mail para Usuários Selecionados

1. Acesse o painel admin em `/admin`
2. Vá para "Usuários" no menu lateral
3. Marque os checkboxes dos usuários desejados
   - Use o checkbox no cabeçalho para selecionar todos
4. Clique no botão "Enviar E-mail (X)" que aparece no topo
5. No modal que abre, preencha o assunto e a mensagem
6. Revise a quantidade de destinatários
7. Clique em "Enviar E-mails"

### Enviar E-mail em Massa por Categoria

1. Acesse o painel admin em `/admin`
2. Clique em "E-mail em Massa" no menu lateral
3. Selecione o grupo de destinatários (todos, alunos, professores, etc.)
4. Preencha o assunto e a mensagem
5. Revise as informações e quantidade de destinatários
6. Confirme o envio

## ⚙️ Configuração de E-mail

O sistema utiliza as configurações de e-mail do Laravel definidas em `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=seu_email@example.com
MAIL_PASSWORD=sua_senha
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@studyplatform.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Ambientes de Teste

Para testes em desenvolvimento, você pode usar:

- **Mailtrap**: Serviço de teste de e-mail
- **Log**: E-mails salvos em `storage/logs/laravel.log`
  ```env
  MAIL_MAILER=log
  ```

## 🛡️ Segurança e Validações

### Validações Implementadas

**Envio Individual:**
- Assunto: obrigatório, máximo 255 caracteres
- Mensagem: obrigatória, mínimo 10 caracteres

**Envio em Massa:**
- Destinatários: obrigatório, valores permitidos (todos, alunos, professores, admins, ativos)
- Assunto: obrigatório, máximo 255 caracteres
- Mensagem: obrigatória, mínimo 10 caracteres

### Segurança

- Todas as rotas protegidas por middleware `auth` e `admin`
- Apenas administradores podem enviar e-mails
- Confirmação obrigatória para envio em massa
- Log de erros para monitoramento
- Try-catch para captura de exceções no envio

## 📊 Monitoramento

### Logs de Erro

Erros de envio são registrados em:
```
storage/logs/laravel.log
```

Formato:
```
[timestamp] local.ERROR: Erro ao enviar e-mail para usuario@example.com: [mensagem de erro]
```

### Relatório de Envio

Após envio em massa, o sistema exibe:
- Quantidade de e-mails enviados com sucesso
- Quantidade total de destinatários
- Quantidade de falhas (se houver)

## 💡 Dicas de Uso

### Para E-mails em Massa

1. **Teste Primeiro**: Envie para você mesmo antes de enviar em massa
2. **Horário Adequado**: Evite enviar fora do horário comercial
3. **Conteúdo Claro**: Seja objetivo e profissional
4. **Evite Spam**: Não abuse da funcionalidade
5. **Personalização**: A mensagem será personalizada com o nome de cada usuário
6. **Revisão**: Sempre revise ortografia e gramática

### Boas Práticas

- Use assuntos descritivos e claros
- Mantenha mensagens concisas
- Inclua call-to-action quando necessário
- Evite envios frequentes para o mesmo grupo
- Monitore os logs para identificar problemas

## 🔄 Fluxo de Envio

### Envio Individual
```
Usuário Admin → Formulário → Validação → Mail::send → Feedback
```

### Envio em Massa
```
Usuário Admin → Seleção Grupo → Formulário → Validação → 
Query Usuários → Loop Envio → Contadores → Relatório
```

## 📈 Estatísticas no Dashboard

A página de envio em massa exibe:
- Total de usuários cadastrados
- Total de alunos
- Total de professores
- Total de administradores
- Usuários ativos (últimos 30 dias)

## 🚀 Próximas Melhorias (Sugestões)

- [ ] Agendamento de e-mails para envio futuro
- [ ] Templates de e-mail salvos
- [ ] Histórico de e-mails enviados
- [ ] Estatísticas de abertura (se integrado com serviço de e-mail)
- [ ] Anexos de arquivos
- [ ] Editor WYSIWYG para formatação rica
- [ ] Envio assíncrono com filas (Queue)
- [ ] Filtros avançados de usuários
- [ ] Preview do e-mail antes de enviar
- [ ] Variáveis personalizadas no template

## 🐛 Troubleshooting

### E-mail não está sendo enviado

1. Verifique as configurações SMTP no `.env`
2. Confirme que o servidor SMTP está acessível
3. Verifique os logs em `storage/logs/laravel.log`
4. Teste com `MAIL_MAILER=log` para debug

### Erro "Connection could not be established"

- Verifique credenciais SMTP
- Confirme porta e encryption corretas
- Verifique firewall/rede

### E-mails indo para spam

- Configure SPF e DKIM no DNS
- Use endereço de e-mail verificado
- Evite palavras típicas de spam
- Mantenha boa reputação de envio

## 📝 Exemplo de Uso

```php
// Envio individual (interno do controller)
Mail::to($usuario->email)->send(
    new AdminNotificationMail(
        'Bem-vindo à plataforma',
        'Estamos felizes em tê-lo conosco!',
        $usuario->name
    )
);

// Envio em massa (exemplo)
$usuarios = User::where('role', 'aluno')->get();
foreach ($usuarios as $usuario) {
    Mail::to($usuario->email)->send(
        new AdminNotificationMail(
            'Novidade na plataforma',
            'Confira as novas funcionalidades...',
            $usuario->name
        )
    );
}
```

## ✅ Checklist de Implementação

- [x] Criar Mailable class
- [x] Criar template de e-mail
- [x] Criar formulário de envio individual
- [x] Criar formulário de envio em massa
- [x] Adicionar rotas
- [x] Implementar controllers
- [x] Adicionar botões nas views
- [x] Adicionar link no menu
- [x] Implementar validações
- [x] Implementar tratamento de erros
- [x] Criar documentação

---

**Implementado por:** GitHub Copilot
**Data:** 21 de outubro de 2025
**Versão:** 1.0
