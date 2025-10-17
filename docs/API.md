# 📚 API de Geração de Questões - Documentação

## 🚀 Sobre o Projeto

API RESTful para geração automática de questões educacionais usando Inteligência Artificial. A plataforma permite que professores e alunos criem, organizem e respondam questões de múltipla escolha sobre diversos temas.

## ✨ Funcionalidades Principais

- ✅ Autenticação com Laravel Sanctum
- 🤖 Geração automática de questões usando OpenAI
- 📝 CRUD completo de questões
- 📂 Sistema de coleções de questões
- 🎮 Criação e realização de simulados
- 📊 Estatísticas e análise de desempenho
- 🎨 Suporte a múltiplos temas educacionais
- 🖼️ Geração de questões a partir de imagens

## 🛠️ Tecnologias Utilizadas

- **Framework**: Laravel 11
- **Autenticação**: Laravel Sanctum
- **Banco de Dados**: SQLite (desenvolvimento) / MySQL/PostgreSQL (produção)
- **IA**: OpenAI API (GPT-4)
- **Testes**: Pest PHP

## 📦 Instalação

### Pré-requisitos

- PHP 8.2 ou superior
- Composer
- Node.js e NPM (opcional, para frontend)

### Passos

1. Clone o repositório:
```bash
git clone <url-do-repositorio>
cd study
```

2. Instale as dependências:
```bash
composer install
```

3. Configure o arquivo `.env`:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure as variáveis de ambiente no `.env`:
```env
# Banco de Dados
DB_CONNECTION=sqlite

# OpenAI API
OPENAI_API_KEY=sua-chave-api-aqui
OPENAI_MODEL=gpt-4-turbo-preview
OPENAI_MAX_TOKENS=2000

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000
SESSION_DOMAIN=localhost
```

5. Execute as migrations e seeders:
```bash
php artisan migrate --seed
```

6. Inicie o servidor:
```bash
php artisan serve
```

A API estará disponível em `http://localhost:8000`

## 📖 Documentação da API

### Base URL
```
http://localhost:8000/api
```

### 🔐 Autenticação

#### Registrar Usuário
```http
POST /api/register
Content-Type: application/json

{
  "name": "João Silva",
  "email": "joao@example.com",
  "password": "senha123",
  "password_confirmation": "senha123",
  "role": "aluno" // ou "professor"
}
```

**Resposta:**
```json
{
  "success": true,
  "message": "Usuário registrado com sucesso",
  "data": {
    "user": {
      "id": 1,
      "name": "João Silva",
      "email": "joao@example.com",
      "role": "aluno"
    },
    "token": "1|abc123..."
  }
}
```

#### Login
```http
POST /api/login
Content-Type: application/json

{
  "email": "joao@example.com",
  "password": "senha123"
}
```

#### Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

#### Obter Usuário Autenticado
```http
GET /api/user
Authorization: Bearer {token}
```

#### Atualizar Perfil
```http
PUT /api/user/profile
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "João da Silva",
  "email": "joao.silva@example.com",
  "avatar": "https://example.com/avatar.jpg"
}
```

---

### 📚 Temas

#### Listar Temas
```http
GET /api/temas
Authorization: Bearer {token}
```

**Resposta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nome": "Biologia",
      "descricao": "Estudo dos seres vivos",
      "icone": "🧬",
      "cor": "#4CAF50",
      "questoes_count": 15
    }
  ]
}
```

#### Criar Tema
```http
POST /api/temas
Authorization: Bearer {token}
Content-Type: application/json

{
  "nome": "Astronomia",
  "descricao": "Estudo do universo",
  "icone": "🌟",
  "cor": "#673AB7",
  "ativo": true
}
```

---

### 🤖 Geração de Questões com IA

#### Gerar Questões por Tema
```http
POST /api/questoes/gerar-por-tema
Authorization: Bearer {token}
Content-Type: application/json

{
  "tema_id": 1,
  "quantidade": 5,
  "nivel": "medio" // facil, medio, dificil
}
```

**Resposta:**
```json
{
  "success": true,
  "message": "Questões geradas com sucesso",
  "data": [
    {
      "id": 1,
      "tema_id": 1,
      "enunciado": "Qual é a principal função das mitocôndrias?",
      "nivel": "medio",
      "explicacao": "As mitocôndrias são responsáveis pela respiração celular...",
      "alternativas": [
        {
          "id": 1,
          "texto": "Síntese proteica",
          "correta": false,
          "ordem": 1
        },
        {
          "id": 2,
          "texto": "Respiração celular",
          "correta": true,
          "ordem": 2
        }
      ]
    }
  ]
}
```

#### Gerar Variações de Questão
```http
POST /api/questoes/gerar-variacao
Authorization: Bearer {token}
Content-Type: application/json

{
  "tema_id": 1,
  "questao_exemplo": "Qual é a fórmula da água?",
  "quantidade": 3
}
```

#### Gerar Questões por Imagem
```http
POST /api/questoes/gerar-por-imagem
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
  "tema_id": 1,
  "imagem": <arquivo>,
  "contexto": "Gráfico sobre fotossíntese" (opcional)
}
```

---

### 📝 Questões

#### Listar Questões
```http
GET /api/questoes?tema_id=1&nivel=medio&minhas=true&busca=mitocondria&page=1
Authorization: Bearer {token}
```

**Parâmetros de query:**
- `tema_id`: Filtrar por tema
- `nivel`: Filtrar por nível (facil, medio, dificil)
- `minhas`: Mostrar apenas minhas questões (true/false)
- `favoritas`: Mostrar apenas favoritas (true/false)
- `busca`: Buscar no enunciado e explicação
- `page`: Número da página
- `per_page`: Itens por página (padrão: 15)

#### Criar Questão Manual
```http
POST /api/questoes
Authorization: Bearer {token}
Content-Type: application/json

{
  "tema_id": 1,
  "enunciado": "Qual é a capital do Brasil?",
  "nivel": "facil",
  "explicacao": "Brasília é a capital federal do Brasil desde 1960",
  "tags": ["geografia", "brasil", "capitais"],
  "alternativas": [
    { "texto": "São Paulo", "correta": false },
    { "texto": "Rio de Janeiro", "correta": false },
    { "texto": "Brasília", "correta": true },
    { "texto": "Salvador", "correta": false }
  ]
}
```

#### Atualizar Questão
```http
PUT /api/questoes/{id}
Authorization: Bearer {token}
Content-Type: application/json
```

#### Deletar Questão
```http
DELETE /api/questoes/{id}
Authorization: Bearer {token}
```

#### Favoritar/Desfavoritar Questão
```http
POST /api/questoes/{id}/favoritar
Authorization: Bearer {token}
```

---

### 📂 Coleções

#### Listar Coleções
```http
GET /api/colecoes
Authorization: Bearer {token}
```

#### Criar Coleção
```http
POST /api/colecoes
Authorization: Bearer {token}
Content-Type: application/json

{
  "nome": "Revisão de Biologia",
  "descricao": "Questões para prova final",
  "publica": false
}
```

#### Adicionar Questão à Coleção
```http
POST /api/colecoes/{colecao_id}/questoes
Authorization: Bearer {token}
Content-Type: application/json

{
  "questao_id": 1
}
```

#### Remover Questão da Coleção
```http
DELETE /api/colecoes/{colecao_id}/questoes/{questao_id}
Authorization: Bearer {token}
```

#### Listar Questões da Coleção
```http
GET /api/colecoes/{colecao_id}/questoes
Authorization: Bearer {token}
```

---

### 🎮 Simulados

#### Listar Simulados
```http
GET /api/simulados?status=ativo
Authorization: Bearer {token}
```

#### Criar Simulado
```http
POST /api/simulados
Authorization: Bearer {token}
Content-Type: application/json

{
  "titulo": "Simulado de Biologia - Módulo 1",
  "descricao": "Avaliação sobre células e tecidos",
  "tempo_limite": 60, // em minutos
  "embaralhar_questoes": true,
  "mostrar_gabarito": true,
  "status": "ativo",
  "questoes": [
    { "questao_id": 1, "pontuacao": 1.0 },
    { "questao_id": 2, "pontuacao": 1.5 },
    { "questao_id": 3, "pontuacao": 1.0 }
  ]
}
```

#### Iniciar Simulado
```http
POST /api/simulados/{id}/iniciar
Authorization: Bearer {token}
```

**Resposta:**
```json
{
  "success": true,
  "message": "Simulado iniciado",
  "data": {
    "simulado": {
      "id": 1,
      "titulo": "Simulado de Biologia",
      "tempo_limite": 60
    },
    "questoes": [...],
    "tempo_inicio": "2025-10-17T11:56:00.000000Z"
  }
}
```

#### Responder Simulado
```http
POST /api/simulados/{id}/responder
Authorization: Bearer {token}
Content-Type: application/json

{
  "respostas": [
    {
      "questao_id": 1,
      "alternativa_id": 2,
      "tempo_resposta": 30 // em segundos
    },
    {
      "questao_id": 2,
      "alternativa_id": 7,
      "tempo_resposta": 45
    }
  ]
}
```

**Resposta:**
```json
{
  "success": true,
  "message": "Respostas registradas com sucesso",
  "data": {
    "acertos": 8,
    "total_questoes": 10,
    "percentual_acerto": 80.00
  }
}
```

#### Ver Resultado do Simulado (Última Tentativa)
```http
GET /api/simulados/{id}/resultado
Authorization: Bearer {token}
```

**Retorna:** Estatísticas e detalhes da tentativa mais recente do simulado.

#### Ver Histórico de Todas as Tentativas
```http
GET /api/simulados/{id}/historico
Authorization: Bearer {token}
```

**Retorna:** Lista com todas as tentativas realizadas neste simulado, incluindo data, acertos e percentual de cada uma.

---

## 🧪 Testando a API

### Usuários de Teste

Após rodar `php artisan db:seed`, você terá:

- **Aluno**: aluno@example.com / password
- **Professor**: professor@example.com / password

### Exemplo com cURL

```bash
# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "aluno@example.com",
    "password": "password"
  }'

# Listar temas (substitua {token} pelo token recebido)
curl -X GET http://localhost:8000/api/temas \
  -H "Authorization: Bearer {token}"

# Gerar questões
curl -X POST http://localhost:8000/api/questoes/gerar-por-tema \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "tema_id": 1,
    "quantidade": 3,
    "nivel": "medio"
  }'
```

## 📊 Estrutura do Banco de Dados

### Tabelas Principais

- **users**: Usuários do sistema
- **temas**: Temas das questões (Biologia, Matemática, etc.)
- **questoes**: Questões criadas
- **alternativas**: Alternativas de cada questão
- **colecoes**: Coleções de questões
- **colecao_questao**: Relacionamento many-to-many
- **simulados**: Simulados/Quizzes
- **simulado_questao**: Relacionamento many-to-many
- **respostas_usuario**: Respostas dos usuários

## 🔒 Segurança

- Autenticação via Bearer Token (Sanctum)
- Validação de todos os inputs
- Proteção contra SQL Injection (Eloquent ORM)
- Rate limiting (configurável)
- CORS configurado

## 📝 Próximos Passos

- [ ] Implementar módulo de desempenho e estatísticas
- [ ] Adicionar exportação de questões em PDF
- [ ] Criar sistema de recomendação de temas
- [ ] Implementar testes automatizados
- [ ] Adicionar documentação Swagger/OpenAPI
- [ ] Implementar cache para otimização

## 🤝 Contribuindo

Contribuições são bem-vindas! Por favor, abra uma issue ou pull request.

## 📄 Licença

Este projeto está sob a licença MIT.

## 📧 Contato

Para dúvidas e sugestões, entre em contato através do email: suporte@example.com

---

**Última atualização**: 17 de outubro de 2025
