# 🧪 Exemplos de Requisições - Coleção Postman/Insomnia

## Variáveis de Ambiente

```
base_url: http://study.test/api
token: (será preenchido após login)
```

---

## 1️⃣ Autenticação

### 1.1 Registrar Novo Usuário
```http
POST {{base_url}}/register
Content-Type: application/json

{
  "name": "Maria Silva",
  "email": "maria@example.com",
  "password": "senha12345",
  "password_confirmation": "senha12345",
  "role": "aluno"
}
```

### 1.2 Login
```http
POST {{base_url}}/login
Content-Type: application/json

{
  "email": "aluno@example.com",
  "password": "password"
}
```

### 1.3 Obter Usuário Atual
```http
GET {{base_url}}/user
Authorization: Bearer {{token}}
```

### 1.4 Atualizar Perfil
```http
PUT {{base_url}}/user/profile
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "name": "Maria Silva Santos",
  "avatar": "https://api.dicebear.com/7.x/avataaars/svg?seed=Maria"
}
```

### 1.5 Logout
```http
POST {{base_url}}/logout
Authorization: Bearer {{token}}
```

---

## 2️⃣ Temas

### 2.1 Listar Todos os Temas
```http
GET {{base_url}}/temas
Authorization: Bearer {{token}}
```

### 2.2 Obter Detalhes de um Tema
```http
GET {{base_url}}/temas/1
Authorization: Bearer {{token}}
```

### 2.3 Criar Novo Tema
```http
POST {{base_url}}/temas
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "nome": "Programação",
  "descricao": "Conceitos de programação e algoritmos",
  "icone": "💻",
  "cor": "#3F51B5",
  "ativo": true
}
```

### 2.4 Atualizar Tema
```http
PUT {{base_url}}/temas/1
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "nome": "Biologia Celular",
  "descricao": "Estudo detalhado das células"
}
```

### 2.5 Deletar Tema
```http
DELETE {{base_url}}/temas/9
Authorization: Bearer {{token}}
```

---

## 3️⃣ Geração de Questões com IA

### 3.1 Gerar Questões por Tema
```http
POST {{base_url}}/questoes/gerar-por-tema
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "tema_id": 1,
  "quantidade": 5,
  "nivel": "medio"
}
```

### 3.2 Gerar Questões Fáceis
```http
POST {{base_url}}/questoes/gerar-por-tema
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "tema_id": 2,
  "quantidade": 3,
  "nivel": "facil"
}
```

### 3.3 Gerar Questões Difíceis
```http
POST {{base_url}}/questoes/gerar-por-tema
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "tema_id": 3,
  "quantidade": 5,
  "nivel": "dificil"
}
```

### 3.4 Gerar Variações de uma Questão
```http
POST {{base_url}}/questoes/gerar-variacao
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "tema_id": 1,
  "questao_exemplo": "Qual é a principal função das mitocôndrias na célula?",
  "quantidade": 3
}
```

### 3.5 Gerar Questões por Imagem
```http
POST {{base_url}}/questoes/gerar-por-imagem
Authorization: Bearer {{token}}
Content-Type: multipart/form-data

{
  "tema_id": 1,
  "imagem": [arquivo],
  "contexto": "Diagrama do ciclo de Krebs"
}
```

---

## 4️⃣ Questões

### 4.1 Listar Todas as Questões
```http
GET {{base_url}}/questoes
Authorization: Bearer {{token}}
```

### 4.2 Listar Minhas Questões
```http
GET {{base_url}}/questoes?minhas=true
Authorization: Bearer {{token}}
```

### 4.3 Filtrar por Tema
```http
GET {{base_url}}/questoes?tema_id=1
Authorization: Bearer {{token}}
```

### 4.4 Filtrar por Nível
```http
GET {{base_url}}/questoes?nivel=dificil
Authorization: Bearer {{token}}
```

### 4.5 Buscar Questões
```http
GET {{base_url}}/questoes?busca=mitocondria
Authorization: Bearer {{token}}
```

### 4.6 Listar Favoritas
```http
GET {{base_url}}/questoes?favoritas=true
Authorization: Bearer {{token}}
```

### 4.7 Filtros Combinados
```http
GET {{base_url}}/questoes?tema_id=1&nivel=medio&minhas=true&page=1&per_page=10
Authorization: Bearer {{token}}
```

### 4.8 Criar Questão Manual
```http
POST {{base_url}}/questoes
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "tema_id": 1,
  "enunciado": "Qual organela é responsável pela síntese de proteínas?",
  "nivel": "medio",
  "explicacao": "Os ribossomos são as organelas responsáveis pela tradução do RNA mensageiro em proteínas",
  "tags": ["biologia", "célula", "organelas"],
  "alternativas": [
    {
      "texto": "Mitocôndria",
      "correta": false
    },
    {
      "texto": "Ribossomo",
      "correta": true
    },
    {
      "texto": "Complexo de Golgi",
      "correta": false
    },
    {
      "texto": "Núcleo",
      "correta": false
    }
  ]
}
```

### 4.9 Atualizar Questão
```http
PUT {{base_url}}/questoes/1
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "enunciado": "Qual organela celular é responsável pela síntese de proteínas?",
  "nivel": "facil",
  "tags": ["biologia", "célula", "proteínas", "ribossomos"]
}
```

### 4.10 Favoritar Questão
```http
POST {{base_url}}/questoes/1/favoritar
Authorization: Bearer {{token}}
```

### 4.11 Deletar Questão
```http
DELETE {{base_url}}/questoes/1
Authorization: Bearer {{token}}
```

### 4.12 Ver Detalhes da Questão
```http
GET {{base_url}}/questoes/1
Authorization: Bearer {{token}}
```

---

## 5️⃣ Coleções

### 5.1 Listar Minhas Coleções
```http
GET {{base_url}}/colecoes
Authorization: Bearer {{token}}
```

### 5.2 Criar Coleção
```http
POST {{base_url}}/colecoes
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "nome": "Revisão Final - Biologia",
  "descricao": "Questões selecionadas para revisão da prova final",
  "publica": false
}
```

### 5.3 Criar Coleção Pública
```http
POST {{base_url}}/colecoes
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "nome": "Questões de Matemática Básica",
  "descricao": "Coleção compartilhada com todos os alunos",
  "publica": true
}
```

### 5.4 Ver Detalhes da Coleção
```http
GET {{base_url}}/colecoes/1
Authorization: Bearer {{token}}
```

### 5.5 Adicionar Questão à Coleção
```http
POST {{base_url}}/colecoes/1/questoes
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "questao_id": 5
}
```

### 5.6 Listar Questões da Coleção
```http
GET {{base_url}}/colecoes/1/questoes
Authorization: Bearer {{token}}
```

### 5.7 Remover Questão da Coleção
```http
DELETE {{base_url}}/colecoes/1/questoes/5
Authorization: Bearer {{token}}
```

### 5.8 Atualizar Coleção
```http
PUT {{base_url}}/colecoes/1
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "nome": "Revisão Final - Biologia (Atualizada)",
  "descricao": "Questões atualizadas para a prova",
  "publica": true
}
```

### 5.9 Deletar Coleção
```http
DELETE {{base_url}}/colecoes/1
Authorization: Bearer {{token}}
```

---

## 6️⃣ Simulados

### 6.1 Listar Simulados
```http
GET {{base_url}}/simulados
Authorization: Bearer {{token}}
```

### 6.2 Listar Simulados Ativos
```http
GET {{base_url}}/simulados?status=ativo
Authorization: Bearer {{token}}
```

### 6.3 Criar Simulado
```http
POST {{base_url}}/simulados
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "titulo": "Simulado ENEM - Biologia",
  "descricao": "Simulado preparatório para o ENEM com questões de biologia",
  "tempo_limite": 60,
  "embaralhar_questoes": true,
  "mostrar_gabarito": true,
  "status": "ativo",
  "questoes": [
    {
      "questao_id": 1,
      "pontuacao": 1.0
    },
    {
      "questao_id": 2,
      "pontuacao": 1.0
    },
    {
      "questao_id": 3,
      "pontuacao": 1.5
    },
    {
      "questao_id": 4,
      "pontuacao": 1.0
    },
    {
      "questao_id": 5,
      "pontuacao": 1.5
    }
  ]
}
```

### 6.4 Criar Simulado Rápido (Rascunho)
```http
POST {{base_url}}/simulados
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "titulo": "Quiz Rápido - Matemática",
  "descricao": "Quiz de 5 minutos",
  "tempo_limite": 5,
  "embaralhar_questoes": false,
  "mostrar_gabarito": true,
  "status": "rascunho",
  "questoes": [
    {
      "questao_id": 10,
      "pontuacao": 1.0
    },
    {
      "questao_id": 11,
      "pontuacao": 1.0
    }
  ]
}
```

### 6.5 Ver Detalhes do Simulado
```http
GET {{base_url}}/simulados/1
Authorization: Bearer {{token}}
```

### 6.6 Atualizar Simulado
```http
PUT {{base_url}}/simulados/1
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "titulo": "Simulado ENEM - Biologia (Revisado)",
  "tempo_limite": 90,
  "status": "ativo"
}
```

### 6.7 Iniciar Simulado
```http
POST {{base_url}}/simulados/1/iniciar
Authorization: Bearer {{token}}
```

### 6.8 Responder Simulado
```http
POST {{base_url}}/simulados/1/responder
Authorization: Bearer {{token}}
Content-Type: application/json

{
  "respostas": [
    {
      "questao_id": 1,
      "alternativa_id": 2,
      "tempo_resposta": 30
    },
    {
      "questao_id": 2,
      "alternativa_id": 7,
      "tempo_resposta": 45
    },
    {
      "questao_id": 3,
      "alternativa_id": 11,
      "tempo_resposta": 60
    },
    {
      "questao_id": 4,
      "alternativa_id": 16,
      "tempo_resposta": 40
    },
    {
      "questao_id": 5,
      "alternativa_id": 19,
      "tempo_resposta": 55
    }
  ]
}
```

### 6.9 Ver Resultado do Simulado (Última Tentativa)
```http
GET {{base_url}}/simulados/1/resultado
Authorization: Bearer {{token}}
```

**Resposta:**
```json
{
  "success": true,
  "data": {
    "simulado": {
      "id": 1,
      "titulo": "Simulado de Matemática Básica",
      "descricao": "Teste seus conhecimentos em matemática"
    },
    "tentativa": {
      "numero": 3,
      "data": "2025-01-17 15:30:22"
    },
    "estatisticas": {
      "total_questoes": 10,
      "acertos": 8,
      "erros": 2,
      "percentual_acerto": 80.00
    },
    "respostas": [
      {
        "questao_id": 1,
        "enunciado": "Quanto é 2 + 2?",
        "alternativa_selecionada": "4",
        "correta": true
      }
    ]
  }
}
```

### 6.10 Ver Histórico de Todas as Tentativas
```http
GET {{base_url}}/simulados/1/historico
Authorization: Bearer {{token}}
```

**Resposta:**
```json
{
  "success": true,
  "data": {
    "simulado": {
      "id": 1,
      "titulo": "Simulado de Matemática Básica",
      "descricao": "Teste seus conhecimentos em matemática"
    },
    "total_tentativas": 3,
    "tentativas": [
      {
        "tentativa": 1,
        "data": "2025-01-17 15:30:22",
        "total_questoes": 10,
        "acertos": 8,
        "erros": 2,
        "percentual_acerto": 80.00
      },
      {
        "tentativa": 2,
        "data": "2025-01-17 14:15:10",
        "total_questoes": 10,
        "acertos": 6,
        "erros": 4,
        "percentual_acerto": 60.00
      },
      {
        "tentativa": 3,
        "data": "2025-01-17 10:00:05",
        "total_questoes": 10,
        "acertos": 5,
        "erros": 5,
        "percentual_acerto": 50.00
      }
    ]
  }
}
```

### 6.11 Deletar Simulado
```http
DELETE {{base_url}}/simulados/1
Authorization: Bearer {{token}}
```

---

## 📝 Fluxo Completo de Uso

### Cenário: Professor cria um simulado e aluno responde

#### 1. Professor faz login
```http
POST {{base_url}}/login
{
  "email": "professor@example.com",
  "password": "password"
}
```

#### 2. Professor gera questões com IA
```http
POST {{base_url}}/questoes/gerar-por-tema
Authorization: Bearer {{token_professor}}
{
  "tema_id": 1,
  "quantidade": 10,
  "nivel": "medio"
}
```

#### 3. Professor cria um simulado
```http
POST {{base_url}}/simulados
Authorization: Bearer {{token_professor}}
{
  "titulo": "Avaliação Bimestral",
  "questoes": [...ids das questões geradas...],
  "status": "ativo"
}
```

#### 4. Aluno faz login
```http
POST {{base_url}}/login
{
  "email": "aluno@example.com",
  "password": "password"
}
```

#### 5. Aluno lista simulados disponíveis
```http
GET {{base_url}}/simulados?status=ativo
Authorization: Bearer {{token_aluno}}
```

#### 6. Aluno inicia o simulado
```http
POST {{base_url}}/simulados/1/iniciar
Authorization: Bearer {{token_aluno}}
```

#### 7. Aluno responde as questões
```http
POST {{base_url}}/simulados/1/responder
Authorization: Bearer {{token_aluno}}
{
  "respostas": [...]
}
```

#### 8. Aluno visualiza o resultado
```http
GET {{base_url}}/simulados/1/resultado
Authorization: Bearer {{token_aluno}}
```

---

## 🎯 Dicas para Testes

1. **Sempre salve o token** após o login para usar nas próximas requisições
2. **Use variáveis de ambiente** no Postman/Insomnia para facilitar
3. **Teste os filtros** combinados para ver a flexibilidade da API
4. **Experimente gerar questões** com diferentes temas e níveis
5. **Crie coleções** para organizar questões antes de criar simulados
6. **Teste as permissões** tentando editar recursos de outros usuários

---

## 🐛 Tratamento de Erros

### Erro 401 - Não autorizado
```json
{
  "message": "Unauthenticated."
}
```

### Erro 403 - Sem permissão
```json
{
  "success": false,
  "message": "Você não tem permissão para acessar este recurso"
}
```

### Erro 422 - Validação
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

### Erro 500 - Erro do servidor
```json
{
  "success": false,
  "message": "Erro interno do servidor"
}
```

---

**Data**: 17 de outubro de 2025
