# Retorno da Visualização de um Simulado

## Endpoint
```
GET /api/simulados/{id}
```

## Autenticação
Requer token Bearer (Sanctum)

## Validação
- O usuário deve ser o dono do simulado (user_id)
- Caso contrário, retorna erro 403

## Estrutura do Retorno

### Sucesso (200 OK)
```json
{
    "success": true,
    "data": {
        "id": 1,
        "user_id": 1,
        "titulo": "Simulado de Matemática Básica",
        "descricao": "Simulado para testar conhecimentos básicos",
        "tempo_limite": 60,
        "embaralhar_questoes": false,
        "mostrar_gabarito": true,
        "status": "ativo",
        "created_at": "2025-10-18T10:00:00.000000Z",
        "updated_at": "2025-10-18T10:00:00.000000Z",
        "questoes": [
            {
                "id": 1,
                "tema_id": 1,
                "enunciado": "Quanto é 2 + 2?",
                "tipo": "multipla_escolha",
                "dificuldade": "facil",
                "nivel_dificuldade": "facil",
                "imagem_gerada": null,
                "created_at": "2025-10-18T09:00:00.000000Z",
                "updated_at": "2025-10-18T09:00:00.000000Z",
                "pivot": {
                    "simulado_id": 1,
                    "questao_id": 1,
                    "ordem": 1,
                    "pontuacao": 1.0
                },
                "tema": {
                    "id": 1,
                    "user_id": 1,
                    "nome": "Matemática Básica",
                    "descricao": "Operações matemáticas básicas",
                    "created_at": "2025-10-18T08:00:00.000000Z",
                    "updated_at": "2025-10-18T08:00:00.000000Z"
                },
                "alternativas": [
                    {
                        "id": 1,
                        "questao_id": 1,
                        "texto": "3",
                        "correta": false,
                        "created_at": "2025-10-18T09:00:00.000000Z",
                        "updated_at": "2025-10-18T09:00:00.000000Z"
                    },
                    {
                        "id": 2,
                        "questao_id": 1,
                        "texto": "4",
                        "correta": true,
                        "created_at": "2025-10-18T09:00:00.000000Z",
                        "updated_at": "2025-10-18T09:00:00.000000Z"
                    },
                    {
                        "id": 3,
                        "questao_id": 1,
                        "texto": "5",
                        "correta": false,
                        "created_at": "2025-10-18T09:00:00.000000Z",
                        "updated_at": "2025-10-18T09:00:00.000000Z"
                    },
                    {
                        "id": 4,
                        "questao_id": 1,
                        "texto": "6",
                        "correta": false,
                        "created_at": "2025-10-18T09:00:00.000000Z",
                        "updated_at": "2025-10-18T09:00:00.000000Z"
                    }
                ]
            }
            // ... mais questões
        ]
    }
}
```

### Erro de Permissão (403 Forbidden)
```json
{
    "success": false,
    "message": "Você não tem permissão para visualizar este simulado"
}
```

### Erro de Não Encontrado (404 Not Found)
```json
{
    "message": "No query results for model [App\\Models\\Simulado] {id}"
}
```

## Relacionamentos Carregados (Eager Loading)

O método `show()` utiliza o seguinte carregamento:
```php
$simulado->load(['questoes.tema', 'questoes.alternativas']);
```

Isso significa que o retorno inclui:

### 1. **Simulado (Principal)**
- id
- user_id
- titulo
- descricao
- tempo_limite (em minutos)
- embaralhar_questoes (boolean)
- mostrar_gabarito (boolean)
- status (rascunho | ativo | arquivado)
- created_at
- updated_at

### 2. **Questões (Array)**
Cada questão inclui:
- id
- tema_id
- enunciado
- tipo (multipla_escolha | verdadeiro_falso | discursiva)
- dificuldade
- nivel_dificuldade (facil | medio | dificil)
- tipo_questao (objetiva | dissertativa)
- banca (nome da banca/instituição)
- imagem_gerada (URL ou null)
- created_at
- updated_at
- **pivot** (tabela intermediária):
  - simulado_id
  - questao_id
  - ordem (posição da questão no simulado)
  - pontuacao (peso da questão)

### 3. **Tema (dentro de cada questão)**
- id
- user_id
- nome
- descricao
- created_at
- updated_at

### 4. **Alternativas (array dentro de cada questão)**
Cada alternativa inclui:
- id
- questao_id
- texto (conteúdo da alternativa)
- correta (boolean)
- created_at
- updated_at

## Observações Importantes

### 1. **Ordem das Questões**
As questões são retornadas na ordem definida pelo campo `ordem` na tabela pivot `simulado_questao`.

### 2. **Gabarito Visível**
O campo `correta` nas alternativas sempre vem no retorno da visualização. Se você não quiser mostrar o gabarito durante a realização do simulado, deve tratá-lo no frontend ou usar o endpoint `iniciar` que não expõe as respostas corretas.

### 3. **Pontuação**
Cada questão tem um campo `pontuacao` na tabela pivot que define seu peso no simulado (padrão: 1.0).

### 4. **Status do Simulado**
- **rascunho**: Simulado em construção, não pode ser iniciado
- **ativo**: Disponível para realização
- **arquivado**: Não aparece em listagens normais

## Uso no Frontend

### Para Visualização/Edição
Use este endpoint quando precisar:
- Editar o simulado
- Ver o gabarito completo
- Gerenciar as questões
- Ver detalhes completos

### Para Realizar o Simulado
**NÃO use este endpoint** para fazer o simulado, pois ele expõe as respostas corretas. Use:
```
POST /api/simulados/{id}/iniciar
```

## Exemplo de Chamada

```javascript
// Axios
const response = await axios.get('/api/simulados/1', {
    headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
    }
});

console.log(response.data.data);
```

```javascript
// Fetch
const response = await fetch('/api/simulados/1', {
    method: 'GET',
    headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    }
});

const data = await response.json();
console.log(data.data);
```

## Diferença entre Endpoints

### GET /api/simulados/{id}
- **Retorna**: Simulado completo com gabarito
- **Uso**: Visualização, edição, administração
- **Expõe respostas**: ✅ Sim

### POST /api/simulados/{id}/iniciar
- **Retorna**: Simulado sem gabarito + tentativa iniciada
- **Uso**: Realizar o simulado
- **Expõe respostas**: ❌ Não

### GET /api/simulados/{id}/resultado
- **Retorna**: Resultado da última tentativa
- **Uso**: Ver desempenho após conclusão
- **Expõe respostas**: ✅ Sim (com suas respostas comparadas)

---

**Data**: 18 de outubro de 2025
**Controller**: `App\Http\Controllers\Api\SimuladoController`
**Método**: `show(Simulado $simulado, Request $request)`
