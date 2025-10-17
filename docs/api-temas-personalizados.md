# API de Temas Personalizados

## Visão Geral

O sistema permite que usuários cadastrem seus próprios temas personalizados além dos temas globais (criados pelo sistema). Isso oferece flexibilidade para organizar estudos de acordo com necessidades específicas.

## Conceitos

### Tipos de Temas

1. **Temas Globais** (`user_id = null`)
   - Criados e gerenciados pelo sistema/administrador
   - Disponíveis para todos os usuários
   - Não podem ser editados ou excluídos por usuários
   - Exemplos: "Português", "Matemática", "Direito Constitucional"

2. **Temas Personalizados** (`user_id != null`)
   - Criados por usuários individuais
   - Visíveis apenas para o criador
   - Podem ser editados e excluídos pelo criador
   - Exemplos: "Revisão Prova X", "Matérias Difíceis", "Estudo Intensivo"

## Endpoints

### 1. Listar Todos os Temas Disponíveis

Lista temas globais + temas personalizados do usuário autenticado.

**Endpoint:** `GET /api/temas`

**Headers:**
```
Authorization: Bearer {token}
```

**Resposta de Sucesso (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nome": "Português",
      "descricao": "Gramática, interpretação de texto, redação",
      "icone": "📚",
      "cor": "#3B82F6",
      "ativo": true,
      "questoes_count": 150,
      "tipo": "global",
      "editavel": false
    },
    {
      "id": 15,
      "nome": "Minha Revisão Final",
      "descricao": "Tópicos importantes para a prova",
      "icone": "🎯",
      "cor": "#10B981",
      "ativo": true,
      "questoes_count": 25,
      "tipo": "personalizado",
      "editavel": true
    }
  ]
}
```

**Notas:**
- Temas globais aparecem primeiro, depois os personalizados
- Campo `tipo` indica se é "global" ou "personalizado"
- Campo `editavel` indica se o usuário pode editar/excluir

---

### 2. Listar Apenas Meus Temas Personalizados

Lista somente os temas criados pelo usuário autenticado.

**Endpoint:** `GET /api/temas/meus-temas`

**Headers:**
```
Authorization: Bearer {token}
```

**Resposta de Sucesso (200):**
```json
{
  "success": true,
  "data": {
    "total": 3,
    "temas": [
      {
        "id": 15,
        "nome": "Minha Revisão Final",
        "descricao": "Tópicos importantes para a prova",
        "icone": "🎯",
        "cor": "#10B981",
        "ativo": true,
        "questoes_count": 25,
        "tipo": "personalizado",
        "editavel": true
      },
      {
        "id": 16,
        "nome": "Matérias Difíceis",
        "descricao": null,
        "icone": "⚠️",
        "cor": "#EF4444",
        "ativo": true,
        "questoes_count": 10,
        "tipo": "personalizado",
        "editavel": true
      }
    ]
  }
}
```

---

### 3. Criar Tema Personalizado

Cria um novo tema personalizado para o usuário.

**Endpoint:** `POST /api/temas`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body:**
```json
{
  "nome": "Revisão Intensiva",
  "descricao": "Tópicos para revisar antes da prova",
  "icone": "🔥",
  "cor": "#F59E0B"
}
```

**Campos:**
- `nome` (obrigatório): Nome do tema (máx. 255 caracteres)
- `descricao` (opcional): Descrição detalhada
- `icone` (opcional): Emoji/ícone (default: "📚")
- `cor` (opcional): Cor hexadecimal (default: "#3B82F6")

**Resposta de Sucesso (201):**
```json
{
  "success": true,
  "message": "Tema personalizado criado com sucesso",
  "data": {
    "id": 17,
    "nome": "Revisão Intensiva",
    "descricao": "Tópicos para revisar antes da prova",
    "icone": "🔥",
    "cor": "#F59E0B",
    "ativo": true,
    "tipo": "personalizado",
    "editavel": true
  }
}
```

**Resposta de Erro (422) - Nome Duplicado:**
```json
{
  "success": false,
  "message": "Você já possui um tema com este nome"
}
```

---

### 4. Ver Detalhes de um Tema

Exibe detalhes completos de um tema (global ou personalizado do usuário).

**Endpoint:** `GET /api/temas/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Resposta de Sucesso (200):**
```json
{
  "success": true,
  "data": {
    "id": 15,
    "nome": "Minha Revisão Final",
    "descricao": "Tópicos importantes para a prova",
    "icone": "🎯",
    "cor": "#10B981",
    "ativo": true,
    "questoes_count": 25,
    "tipo": "personalizado",
    "editavel": true
  }
}
```

**Resposta de Erro (404) - Tema de Outro Usuário:**
```json
{
  "success": false,
  "message": "Tema não encontrado"
}
```

**Nota:** Usuários não podem ver temas personalizados de outros usuários.

---

### 5. Atualizar Tema Personalizado

Atualiza um tema personalizado do usuário. Apenas temas criados pelo próprio usuário podem ser editados.

**Endpoint:** `PUT /api/temas/{id}`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Body:**
```json
{
  "nome": "Revisão Final Atualizada",
  "descricao": "Descrição atualizada",
  "icone": "🎓",
  "cor": "#8B5CF6",
  "ativo": false
}
```

**Campos (todos opcionais):**
- `nome`: Novo nome do tema
- `descricao`: Nova descrição
- `icone`: Novo ícone
- `cor`: Nova cor
- `ativo`: Status (true/false)

**Resposta de Sucesso (200):**
```json
{
  "success": true,
  "message": "Tema atualizado com sucesso",
  "data": {
    "id": 15,
    "nome": "Revisão Final Atualizada",
    "descricao": "Descrição atualizada",
    "icone": "🎓",
    "cor": "#8B5CF6",
    "ativo": false,
    "tipo": "personalizado",
    "editavel": true
  }
}
```

**Resposta de Erro (403) - Tema Global:**
```json
{
  "success": false,
  "message": "Você não tem permissão para editar este tema"
}
```

**Resposta de Erro (422) - Nome Duplicado:**
```json
{
  "success": false,
  "message": "Você já possui um tema com este nome"
}
```

---

### 6. Excluir Tema Personalizado

Exclui um tema personalizado do usuário.

**Endpoint:** `DELETE /api/temas/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Resposta de Sucesso (200):**
```json
{
  "success": true,
  "message": "Tema excluído com sucesso"
}
```

**Resposta de Erro (403) - Não é Tema Personalizado:**
```json
{
  "success": false,
  "message": "Você não tem permissão para excluir este tema"
}
```

**Resposta de Erro (422) - Tema com Questões:**
```json
{
  "success": false,
  "message": "Não é possível excluir um tema que possui questões associadas"
}
```

**Nota:** Temas com questões não podem ser excluídos. Você deve primeiro excluir ou reatribuir as questões.

---

## Integração com Geração de Questões

Os temas personalizados funcionam da mesma forma que os globais na geração de questões por IA.

**Exemplo:**
```json
POST /api/questoes/gerar-por-tema
{
  "tema_id": 15,  // Pode ser tema global ou personalizado
  "assunto": "Revisão Geral",
  "quantidade": 5
}
```

**Validação:** O sistema verifica se o `tema_id` é um tema global ou um tema personalizado do usuário autenticado. Caso contrário, retorna erro 404.

---

## Casos de Uso

### 1. Organização Personalizada

Usuário quer organizar estudos por **prova específica**:
```
POST /api/temas
{
  "nome": "Concurso TRF 2024",
  "descricao": "Matérias específicas da prova",
  "icone": "⚖️",
  "cor": "#6366F1"
}
```

### 2. Separação por Dificuldade

Usuário quer separar **matérias por nível de dificuldade**:
```
POST /api/temas
{
  "nome": "Matérias Fáceis",
  "icone": "✅"
}

POST /api/temas
{
  "nome": "Matérias Difíceis",
  "icone": "⚠️"
}
```

### 3. Revisão Focada

Usuário quer criar tema para **revisão final**:
```
POST /api/temas
{
  "nome": "Revisão Última Semana",
  "descricao": "Tópicos mais importantes para revisar",
  "icone": "🎯",
  "cor": "#EF4444"
}
```

---

## Regras de Negócio

### Permissões

| Ação | Tema Global | Tema Personalizado (Próprio) | Tema Personalizado (Outro Usuário) |
|------|-------------|------------------------------|-----------------------------------|
| Visualizar | ✅ | ✅ | ❌ |
| Criar | ❌ | ✅ | N/A |
| Editar | ❌ | ✅ | ❌ |
| Excluir | ❌ | ✅ (sem questões) | ❌ |
| Usar para gerar questões | ✅ | ✅ | ❌ |

### Validações

1. **Nome Único por Usuário:** Um usuário não pode ter dois temas personalizados com o mesmo nome
2. **Temas Globais Protegidos:** Temas globais não podem ser editados ou excluídos por usuários
3. **Exclusão com Questões:** Não é possível excluir tema com questões associadas
4. **Privacidade:** Temas personalizados são privados e visíveis apenas para o criador

### Limites

- Nome: máximo 255 caracteres
- Descrição: sem limite
- Ícone: máximo 255 caracteres (recomendado emoji único)
- Cor: formato hexadecimal (#RRGGBB)

---

## Schema do Banco de Dados

```sql
temas
├── id (bigint, PK)
├── user_id (bigint, FK nullable) -- NULL = tema global, INT = tema personalizado
├── nome (varchar 255)
├── descricao (text, nullable)
├── icone (varchar 255, nullable)
├── cor (varchar 50, nullable)
├── ativo (boolean, default: true)
├── created_at (timestamp)
└── updated_at (timestamp)

INDEX: (user_id, nome)
FOREIGN KEY: user_id REFERENCES users(id) ON DELETE CASCADE
```

---

## Exemplos de Frontend

### Listar Temas com Separação Visual

```javascript
const response = await fetch('/api/temas', {
  headers: { 'Authorization': `Bearer ${token}` }
});
const { data } = await response.json();

const temasGlobais = data.filter(t => t.tipo === 'global');
const temasPersonalizados = data.filter(t => t.tipo === 'personalizado');

// Renderizar separadamente
renderSecao('Temas do Sistema', temasGlobais);
renderSecao('Meus Temas', temasPersonalizados);
```

### Criar Tema com Validação

```javascript
async function criarTema(dados) {
  try {
    const response = await fetch('/api/temas', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(dados)
    });
    
    if (!response.ok) {
      const error = await response.json();
      alert(error.message); // "Você já possui um tema com este nome"
      return;
    }
    
    const result = await response.json();
    console.log('Tema criado:', result.data);
  } catch (error) {
    console.error('Erro ao criar tema:', error);
  }
}
```

### Botões Condicionais (Editar/Excluir)

```javascript
function renderTema(tema) {
  return `
    <div class="tema" style="border-left: 4px solid ${tema.cor}">
      <span class="icone">${tema.icone}</span>
      <span class="nome">${tema.nome}</span>
      <span class="badge">${tema.tipo}</span>
      <span class="questoes">${tema.questoes_count} questões</span>
      
      ${tema.editavel ? `
        <button onclick="editarTema(${tema.id})">Editar</button>
        <button onclick="excluirTema(${tema.id})">Excluir</button>
      ` : ''}
    </div>
  `;
}
```

---

## Migração

Se você já tem temas no banco, eles automaticamente se tornarão **temas globais** (user_id = null).

Para converter um tema em personalizado:
```sql
UPDATE temas SET user_id = 1 WHERE id = 10;
```

---

## Testando

### 1. Criar Tema Personalizado
```bash
curl -X POST http://localhost/api/temas \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "nome": "Meu Tema Teste",
    "descricao": "Descrição do teste",
    "icone": "🧪",
    "cor": "#8B5CF6"
  }'
```

### 2. Listar Temas
```bash
curl http://localhost/api/temas \
  -H "Authorization: Bearer {token}"
```

### 3. Tentar Editar Tema Global (deve falhar)
```bash
curl -X PUT http://localhost/api/temas/1 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"nome": "Novo Nome"}'
# Resposta: 403 Forbidden
```

### 4. Gerar Questão com Tema Personalizado
```bash
curl -X POST http://localhost/api/questoes/gerar-por-tema \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "tema_id": 15,
    "assunto": "Teste com Tema Personalizado",
    "quantidade": 3
  }'
```

---

## Considerações Finais

✅ **Implementado:**
- Diferenciação entre temas globais e personalizados
- CRUD completo para temas personalizados
- Validações de permissão e duplicação
- Integração com geração de questões
- Scopes no modelo para facilitar queries

📝 **Próximos Passos Sugeridos:**
- Implementar compartilhamento de temas personalizados entre usuários
- Adicionar limite de temas por usuário (ex: máximo 50 temas personalizados)
- Implementar importação/exportação de temas
- Criar sistema de templates de temas populares

🔒 **Segurança:**
- Todos os endpoints são protegidos por autenticação Sanctum
- Validação de ownership em todas as operações de modificação
- Temas personalizados são isolados por usuário (privacy by design)
