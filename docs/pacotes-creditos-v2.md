# 💳 Pacotes de Créditos - Atualizado

## 📦 Pacotes Disponíveis

### 1. Básico (pacote_50)
```json
{
  "id": "pacote_50",
  "nome": "Básico",
  "creditos": 50,
  "valor": 4.90,
  "bonus": 0,
  "total_creditos": 50,
  "desconto": 0,
  "descricao": "Ideal para testar"
}
```
- **Preço**: R$ 4,90
- **Créditos**: 50
- **Custo por crédito**: R$ 0,098

---

### 2. Popular (pacote_100) ⭐
```json
{
  "id": "pacote_100",
  "nome": "Popular",
  "creditos": 100,
  "valor": 9.90,
  "bonus": 0,
  "total_creditos": 100,
  "desconto": 10,
  "descricao": "Mais vendido",
  "popular": true
}
```
- **Preço**: R$ 9,90
- **Créditos**: 100
- **Desconto**: 10%
- **Custo por crédito**: R$ 0,099
- **Badge**: Popular ⭐

---

### 3. Avançado (pacote_250)
```json
{
  "id": "pacote_250",
  "nome": "Avançado",
  "creditos": 250,
  "valor": 19.90,
  "bonus": 0,
  "total_creditos": 250,
  "desconto": 20,
  "descricao": "Melhor custo-benefício"
}
```
- **Preço**: R$ 19,90
- **Créditos**: 250
- **Desconto**: 20%
- **Custo por crédito**: R$ 0,0796
- **Economia**: 19,6% vs Básico

---

### 4. Premium (pacote_500) 💎
```json
{
  "id": "pacote_500",
  "nome": "Premium",
  "creditos": 500,
  "valor": 34.90,
  "bonus": 0,
  "total_creditos": 500,
  "desconto": 30,
  "descricao": "Para usuários avançados",
  "destaque": true
}
```
- **Preço**: R$ 34,90
- **Créditos**: 500
- **Desconto**: 30%
- **Custo por crédito**: R$ 0,0698
- **Economia**: 28,8% vs Básico
- **Badge**: Destaque 💎

---

## 📊 Comparativo

| Pacote | Preço | Créditos | Custo/Crédito | Desconto | Economia |
|--------|-------|----------|---------------|----------|----------|
| Básico | R$ 4,90 | 50 | R$ 0,098 | 0% | - |
| Popular ⭐ | R$ 9,90 | 100 | R$ 0,099 | 10% | -1% |
| Avançado | R$ 19,90 | 250 | R$ 0,0796 | 20% | 19% ↓ |
| Premium 💎 | R$ 34,90 | 500 | R$ 0,0698 | 30% | 29% ↓ |

---

## 🎯 Estratégia de Preços

### Curva de Desconto
```
Básico:    R$ 0,098 por crédito (baseline)
Popular:   R$ 0,099 por crédito (+1%)   - Incentiva volume
Avançado:  R$ 0,080 por crédito (-19%)  - Melhor custo-benefício
Premium:   R$ 0,070 por crédito (-29%)  - Máxima economia
```

### Recomendações por Uso

**Iniciante** (1-5 questões/dia)
- Pacote Básico (50 créditos)
- Dura ~10 dias

**Regular** (5-10 questões/dia)
- Pacote Popular (100 créditos) ⭐
- Dura ~10-20 dias

**Intensivo** (10-25 questões/dia)
- Pacote Avançado (250 créditos)
- Dura ~10-25 dias

**Power User** (25+ questões/dia)
- Pacote Premium (500 créditos) 💎
- Dura ~20+ dias

---

## 💰 Estrutura de Receita

### Cenário Base (100 vendas/mês)

| Pacote | Vendas | Receita |
|--------|--------|---------|
| Básico | 30 | R$ 147,00 |
| Popular | 40 | R$ 396,00 |
| Avançado | 20 | R$ 398,00 |
| Premium | 10 | R$ 349,00 |
| **Total** | **100** | **R$ 1.290,00** |

### Ticket Médio
```
R$ 1.290,00 / 100 = R$ 12,90 por cliente
```

---

## 🔄 API Endpoints

### Listar Pacotes
```http
GET /api/pagamentos/pix/pacotes
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "pacote_50",
      "nome": "Básico",
      "creditos": 50,
      "valor": 4.90,
      "bonus": 0,
      "total_creditos": 50,
      "desconto": 0,
      "descricao": "Ideal para testar"
    },
    // ... outros pacotes
  ]
}
```

### Criar Cobrança
```http
POST /api/pagamentos/pix/criar
Authorization: Bearer {token}
Content-Type: application/json
```

**Request:**
```json
{
  "pacote_id": "pacote_100",
  "cpf": "12345678900",
  "nome": "João Silva"
}
```

---

## 🎨 UI/UX Recomendações

### Card de Pacote - Básico
```jsx
<div className="package-card">
  <h3>Básico</h3>
  <p className="price">R$ 4,90</p>
  <p className="credits">50 créditos</p>
  <p className="description">Ideal para testar</p>
  <button>Comprar</button>
</div>
```

### Card de Pacote - Popular (Destacado)
```jsx
<div className="package-card popular">
  <div className="badge">⭐ Mais Vendido</div>
  <h3>Popular</h3>
  <p className="price">
    R$ 9,90
    <span className="discount">10% OFF</span>
  </p>
  <p className="credits">100 créditos</p>
  <p className="description">Mais vendido</p>
  <button className="primary">Comprar</button>
</div>
```

### Card de Pacote - Premium (Destacado)
```jsx
<div className="package-card premium">
  <div className="badge">💎 Melhor Economia</div>
  <h3>Premium</h3>
  <p className="price">
    R$ 34,90
    <span className="discount">30% OFF</span>
  </p>
  <p className="credits">500 créditos</p>
  <div className="savings">
    <p>Economia de R$ 14,10</p>
    <p className="small">vs comprar individualmente</p>
  </div>
  <p className="description">Para usuários avançados</p>
  <button className="premium">Comprar</button>
</div>
```

---

## 📈 Métricas para Acompanhar

### Conversão por Pacote
```sql
SELECT 
    JSON_EXTRACT(resposta_validapay, '$.creditos') as creditos,
    COUNT(*) as vendas,
    SUM(valor) as receita,
    AVG(valor) as ticket_medio
FROM pagamentos_pix 
WHERE status = 'CONFIRMED'
GROUP BY creditos
ORDER BY receita DESC;
```

### Evolução Mensal
```sql
SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as mes,
    COUNT(*) as total_vendas,
    SUM(valor) as receita_total,
    AVG(valor) as ticket_medio
FROM pagamentos_pix 
WHERE status = 'CONFIRMED'
GROUP BY mes
ORDER BY mes DESC;
```

### Taxa de Conversão
```sql
SELECT 
    COUNT(*) as total_criados,
    SUM(CASE WHEN status = 'CONFIRMED' THEN 1 ELSE 0 END) as confirmados,
    ROUND(SUM(CASE WHEN status = 'CONFIRMED' THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as taxa_conversao
FROM pagamentos_pix;
```

---

## ✅ Checklist de Atualização

- [x] Atualizar método `pacotes()` no controller
- [x] Atualizar array `$pacotesDisponiveis` no método `criar()`
- [x] Remover pacotes antigos (pacote_300, pacote_1000)
- [x] Adicionar novos pacotes (pacote_50, pacote_250)
- [x] Ajustar valores e descontos
- [x] Documentar estrutura
- [ ] Atualizar frontend para exibir novos pacotes
- [ ] Testar criação de cobrança para cada pacote
- [ ] Atualizar material de marketing
- [ ] Comunicar mudanças aos usuários existentes

---

## 🔄 Mudanças vs Versão Anterior

### Removidos
- ❌ `pacote_300` (330 créditos, R$ 24,90)
- ❌ `pacote_1000` (1200 créditos, R$ 69,90)

### Adicionados
- ✅ `pacote_50` (50 créditos, R$ 4,90) - Novo pacote entry-level
- ✅ `pacote_250` (250 créditos, R$ 19,90) - Pacote intermediário

### Mantidos
- ✅ `pacote_100` (100 créditos, R$ 9,90)
- ✅ `pacote_500` (500 créditos, R$ 34,90)

### Motivo das Mudanças
1. **Barreira de entrada mais baixa**: R$ 4,90 facilita primeiras compras
2. **Escala mais linear**: 50 → 100 → 250 → 500 (progressão mais natural)
3. **Foco no valor percebido**: Descontos claros (10%, 20%, 30%)
4. **Simplicidade**: 4 pacotes são mais fáceis de escolher que 4+

---

**Atualizado em**: 17 de Janeiro de 2025  
**Versão**: 2.0  
**Status**: ✅ Implementado e Documentado
