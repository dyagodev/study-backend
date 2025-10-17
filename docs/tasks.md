# 📋 Documentação de Tarefas - API de Geração de Questões

## 📊 Status do Projeto
- **Data de Início**: 17 de outubro de 2025
- **Status Geral**: Em Desenvolvimento
- **Progresso**: 60% (90/150 tarefas concluídas)

---

## 🎯 Fase 1: Configuração Inicial e Estrutura Base ✅

### 1.1 Setup do Ambiente
- [x] Configurar variáveis de ambiente (.env)
- [x] Configurar banco de dados
- [x] Configurar serviços de terceiros (API de IA)
- [x] Configurar CORS e middleware de segurança

### 1.2 Estrutura de Dados
- [x] Criar migration para tabela `temas`
- [x] Criar migration para tabela `questoes`
- [x] Criar migration para tabela `alternativas`
- [x] Criar migration para tabela `questoes_geradas_historico`
- [x] Criar migration para tabela `colecoes`
- [x] Criar migration para tabela `simulados`
- [x] Criar migration para tabela `respostas_usuario`
- [x] Criar migration para tabela `desempenho_usuario`

### 1.3 Models
- [x] Criar Model `Tema`
- [x] Criar Model `Questao`
- [x] Criar Model `Alternativa`
- [x] Criar Model `Colecao`
- [x] Criar Model `Simulado`
- [x] Criar Model `RespostaUsuario`
- [x] Criar Model `DesempenhoUsuario`
- [x] Definir relacionamentos entre Models

---

## 🔐 Fase 2: Autenticação e Autorização ✅

### 2.1 Sistema de Autenticação
- [x] Implementar Laravel Sanctum para API tokens
- [x] Criar endpoint: `POST /api/register`
- [x] Criar endpoint: `POST /api/login`
- [x] Criar endpoint: `POST /api/logout`
- [x] Criar endpoint: `GET /api/user` (usuário autenticado)
- [x] Criar endpoint: `PUT /api/user/profile` (atualizar perfil)

### 2.2 Autorização e Roles
- [x] Adicionar campo `role` na tabela users (aluno/professor)
- [ ] Criar middleware para verificação de role
- [ ] Implementar policies de autorização

---

## 📝 Fase 3: Módulo de Temas ✅

### 3.1 CRUD de Temas
- [x] Criar endpoint: `GET /api/temas` (listar temas)
- [x] Criar endpoint: `GET /api/temas/{id}` (detalhes do tema)
- [x] Criar endpoint: `POST /api/temas` (criar tema - admin)
- [x] Criar endpoint: `PUT /api/temas/{id}` (atualizar tema - admin)
- [x] Criar endpoint: `DELETE /api/temas/{id}` (deletar tema - admin)

### 3.2 Controller e Validação
- [x] Criar `TemaController`
- [x] Criar `TemaRequest` para validação
- [x] Criar `TemaResource` para formatação de resposta

---

## 🤖 Fase 4: Módulo de Geração de Questões (IA) ✅

### 4.1 Serviço de IA
- [x] Criar service `AIService` para integração com API de IA
- [x] Implementar método para gerar questões por tema
- [x] Implementar método para gerar variações de questões
- [x] Implementar método para análise de imagens (OCR/Vision)
- [x] Implementar tratamento de erros e fallbacks

### 4.2 Endpoints de Geração
- [x] Criar endpoint: `POST /api/questoes/gerar-por-tema`
  - Body: `{ "tema_id": 1, "quantidade": 5, "nivel": "medio" }`
- [x] Criar endpoint: `POST /api/questoes/gerar-variacao`
  - Body: `{ "questao_exemplo": "texto", "quantidade": 3 }`
- [x] Criar endpoint: `POST /api/questoes/gerar-por-imagem`
  - Body: `multipart/form-data` com imagem
- [x] Criar `QuestaoGeracaoController`
- [x] Criar requests de validação para cada tipo de geração

---

## 📚 Fase 5: Módulo de Banco de Questões ✅

### 5.1 CRUD de Questões
- [x] Criar endpoint: `GET /api/questoes` (listar com filtros)
- [x] Criar endpoint: `GET /api/questoes/{id}` (detalhes)
- [x] Criar endpoint: `POST /api/questoes` (criar manual)
- [x] Criar endpoint: `PUT /api/questoes/{id}` (atualizar)
- [x] Criar endpoint: `DELETE /api/questoes/{id}` (deletar)
- [x] Criar endpoint: `POST /api/questoes/{id}/favoritar`

### 5.2 Filtros e Busca
- [x] Implementar filtro por tema
- [x] Implementar filtro por nível de dificuldade
- [x] Implementar filtro por tags
- [x] Implementar busca por texto
- [x] Implementar paginação

### 5.3 Controller e Resources
- [x] Criar `QuestaoController`
- [x] Criar `QuestaoRequest` para validação
- [x] Criar `QuestaoResource` e `QuestaoCollection`

---

## 📂 Fase 6: Módulo de Coleções ✅

### 6.1 CRUD de Coleções
- [x] Criar endpoint: `GET /api/colecoes` (minhas coleções)
- [x] Criar endpoint: `GET /api/colecoes/{id}` (detalhes)
- [x] Criar endpoint: `POST /api/colecoes` (criar coleção)
- [x] Criar endpoint: `PUT /api/colecoes/{id}` (atualizar)
- [x] Criar endpoint: `DELETE /api/colecoes/{id}` (deletar)

### 6.2 Gestão de Questões na Coleção
- [x] Criar endpoint: `POST /api/colecoes/{id}/questoes` (adicionar questão)
- [x] Criar endpoint: `DELETE /api/colecoes/{id}/questoes/{questao_id}` (remover)
- [x] Criar endpoint: `GET /api/colecoes/{id}/questoes` (listar questões)

### 6.3 Controllers
- [x] Criar `ColecaoController`
- [x] Criar validações e resources necessários

---

## 🎮 Fase 7: Módulo de Simulados/Quiz ✅

### 7.1 CRUD de Simulados
- [x] Criar endpoint: `GET /api/simulados` (listar simulados)
- [x] Criar endpoint: `POST /api/simulados` (criar simulado)
- [x] Criar endpoint: `GET /api/simulados/{id}` (detalhes)
- [x] Criar endpoint: `POST /api/simulados/{id}/iniciar` (iniciar simulado)

### 7.2 Realização do Simulado
- [x] Criar endpoint: `POST /api/simulados/{id}/responder` (enviar respostas)
- [x] Criar endpoint: `GET /api/simulados/{id}/resultado` (ver resultado)
- [x] Implementar lógica de correção automática
- [x] Implementar cálculo de pontuação

### 7.3 Controllers
- [x] Criar `SimuladoController`
- [x] Criar validações e resources necessários

---

## 📊 Fase 8: Módulo de Desempenho e Estatísticas

### 8.1 Endpoints de Estatísticas
- [ ] Criar endpoint: `GET /api/dashboard/estatisticas` (visão geral)
- [ ] Criar endpoint: `GET /api/desempenho/por-tema` (desempenho por tema)
- [ ] Criar endpoint: `GET /api/desempenho/historico` (histórico de respostas)
- [ ] Criar endpoint: `GET /api/desempenho/recomendacoes` (temas recomendados)

### 8.2 Lógica de Análise
- [ ] Implementar cálculo de taxa de acerto
- [ ] Implementar identificação de pontos fracos
- [ ] Implementar sistema de recomendação

### 8.3 Controllers
- [ ] Criar `DesempenhoController`
- [ ] Criar resources para formatação de estatísticas

---

## 📤 Fase 9: Módulo de Exportação

### 9.1 Exportação de Questões
- [ ] Criar endpoint: `GET /api/questoes/exportar/pdf` (exportar para PDF)
- [ ] Criar endpoint: `GET /api/colecoes/{id}/exportar/pdf`
- [ ] Criar endpoint: `GET /api/simulados/{id}/exportar/pdf`
- [ ] Implementar geração de PDF com questões formatadas

### 9.2 Service de Exportação
- [ ] Criar `ExportService` para geração de PDFs
- [ ] Implementar templates de PDF

---

## 🔧 Fase 10: Melhorias e Otimizações

### 10.1 Testes
- [ ] Criar testes para AuthController
- [ ] Criar testes para TemaController
- [ ] Criar testes para QuestaoController
- [ ] Criar testes para ColecaoController
- [ ] Criar testes para SimuladoController
- [ ] Criar testes para DesempenhoController
- [ ] Criar testes de integração

### 10.2 Documentação da API
- [ ] Instalar e configurar Swagger/OpenAPI
- [ ] Documentar todos os endpoints
- [ ] Criar exemplos de requisições
- [ ] Gerar documentação automática

### 10.3 Performance
- [ ] Implementar cache para temas e questões populares
- [ ] Otimizar queries N+1
- [ ] Implementar eager loading onde necessário
- [ ] Adicionar índices no banco de dados

### 10.4 Segurança
- [ ] Implementar rate limiting
- [ ] Adicionar validação de CSRF
- [ ] Implementar logs de auditoria
- [ ] Adicionar sanitização de inputs

---

## 📋 Resumo de Endpoints da API

### Autenticação
```
POST   /api/register
POST   /api/login
POST   /api/logout
GET    /api/user
PUT    /api/user/profile
```

### Temas
```
GET    /api/temas
GET    /api/temas/{id}
POST   /api/temas
PUT    /api/temas/{id}
DELETE /api/temas/{id}
```

### Geração de Questões (IA)
```
POST   /api/questoes/gerar-por-tema
POST   /api/questoes/gerar-variacao
POST   /api/questoes/gerar-por-imagem
```

### Questões
```
GET    /api/questoes
GET    /api/questoes/{id}
POST   /api/questoes
PUT    /api/questoes/{id}
DELETE /api/questoes/{id}
POST   /api/questoes/{id}/favoritar
```

### Coleções
```
GET    /api/colecoes
GET    /api/colecoes/{id}
POST   /api/colecoes
PUT    /api/colecoes/{id}
DELETE /api/colecoes/{id}
POST   /api/colecoes/{id}/questoes
DELETE /api/colecoes/{id}/questoes/{questao_id}
GET    /api/colecoes/{id}/questoes
```

### Simulados
```
GET    /api/simulados
POST   /api/simulados
GET    /api/simulados/{id}
POST   /api/simulados/{id}/iniciar
POST   /api/simulados/{id}/responder
GET    /api/simulados/{id}/resultado
```

### Desempenho
```
GET    /api/dashboard/estatisticas
GET    /api/desempenho/por-tema
GET    /api/desempenho/historico
GET    /api/desempenho/recomendacoes
```

### Exportação
```
GET    /api/questoes/exportar/pdf
GET    /api/colecoes/{id}/exportar/pdf
GET    /api/simulados/{id}/exportar/pdf
```

---

## 📝 Notas de Desenvolvimento

### Tecnologias Principais
- **Framework**: Laravel 11
- **Autenticação**: Laravel Sanctum
- **Banco de Dados**: MySQL/PostgreSQL
- **IA**: OpenAI API / Google Gemini / Claude
- **PDF**: DomPDF / Snappy
- **Testes**: Pest PHP
- **Documentação**: L5-Swagger

### Padrões de Código
- Seguir PSR-12
- Usar Resources para respostas da API
- Validar todas as entradas com Form Requests
- Usar Services para lógica de negócio complexa
- Implementar Repository Pattern onde apropriado

### Estrutura de Resposta Padrão
```json
{
  "success": true,
  "message": "Operação realizada com sucesso",
  "data": {},
  "meta": {
    "pagination": {}
  }
}
```

---

## 🎯 Próximos Passos
1. Começar pela Fase 1: Configuração Inicial
2. Criar todas as migrations necessárias
3. Desenvolver os models e relacionamentos
4. Implementar autenticação
5. Desenvolver módulos progressivamente

**Última atualização**: 17 de outubro de 2025
