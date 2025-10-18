# 💰 Atualização de Custos de Créditos - Versão 3.0

**Data**: 18 de Outubro de 2025  
**Tipo**: Aumento significativo nos custos + Nova cobrança para simulados

---

## 📊 Comparativo de Custos

### Antes (Versão 2.0)

| Operação | Custo Anterior | Descrição |
|----------|----------------|-----------|
| Questão Simples | 1 crédito | Geração básica por tema |
| Questão Variação | 2 créditos | Variação de questão existente |
| Questão com Imagem | 3 créditos | Geração com suporte a imagens |
| Simulado | 5 créditos | **❌ NÃO COBRAVA** |

### Agora (Versão 3.0)

| Operação | Custo Novo | Aumento | Motivo |
|----------|------------|---------|---------|
| Questão Simples | **3 créditos** | +200% | Maior complexidade de IA |
| Questão Variação | **5 créditos** | +150% | Processamento mais sofisticado |
| Questão com Imagem | **8 créditos** | +167% | Custo de processamento de imagem |
| Simulado | **10 créditos** | ✅ NOVO | Funcionalidade premium |

---

## 🎯 Justificativas dos Aumentos

### 1. **Questão Simples: 1 → 3 créditos (+200%)**
- **Motivo**: IA mais avançada com prompts mais elaborados
- **Benefício**: Questões de maior qualidade e precisão
- **Impacto**: Uso mais consciente, evita spam de gerações

### 2. **Questão Variação: 2 → 5 créditos (+150%)**
- **Motivo**: Processamento complexo para manter contexto
- **Benefício**: Variações mais inteligentes e relevantes
- **Impacto**: Incentiva planejamento das gerações

### 3. **Questão com Imagem: 3 → 8 créditos (+167%)**
- **Motivo**: Custo real de processamento de imagens
- **Benefício**: Suporte completo a análise visual
- **Impacto**: Reserva para casos realmente necessários

### 4. **Simulado: 0 → 10 créditos (NOVO)**
- **Motivo**: Funcionalidade premium que consome recursos
- **Benefício**: Experiência completa de simulado
- **Impacto**: Valoriza a funcionalidade de simulados

---

## 🔄 Implementação Técnica

### CreditoService (Atualizado)

```php
// app/Services/CreditoService.php

/**
 * Custos de operações em créditos (atualizados)
 */
const CUSTO_QUESTAO_SIMPLES = 3;    // Era 1, agora 3 (+200%)
const CUSTO_QUESTAO_VARIACAO = 5;   // Era 2, agora 5 (+150%)
const CUSTO_QUESTAO_IMAGEM = 8;     // Era 3, agora 8 (+167%)
const CUSTO_SIMULADO = 10;          // Era 5, agora 10 (+100%)
```

### SimuladoController (Nova Cobrança)

```php
public function iniciar(Simulado $simulado, Request $request)
{
    $user = $request->user();
    $custoSimulado = CreditoService::CUSTO_SIMULADO; // 10 créditos

    // Verificar créditos antes de iniciar
    if (!$user->temCreditos($custoSimulado)) {
        return response()->json([
            'success' => false,
            'message' => 'Créditos insuficientes para iniciar simulado',
            'creditos_necessarios' => $custoSimulado,
            'creditos_atuais' => $user->creditos,
        ], 422);
    }

    // Debitar créditos
    $this->creditoService->debitar(
        $user,
        $custoSimulado,
        "Simulado iniciado: {$simulado->titulo}",
        'simulado',
        $simulado->id
    );

    // Retornar simulado...
}
```

---

## 📈 Impacto nos Pacotes de Créditos

### Duração Estimada dos Pacotes

#### Pacote Básico (50 créditos - R$ 4,90)

| Operação | Antes | Agora | Redução |
|----------|-------|-------|---------|
| Questões Simples | 50 questões | 16 questões | -68% |
| Questões Variação | 25 questões | 10 questões | -60% |
| Questões Imagem | 16 questões | 6 questões | -62% |
| Simulados | 10 simulados | 5 simulados | -50% |

#### Pacote Popular (100 créditos - R$ 9,90)

| Operação | Antes | Agora | Redução |
|----------|-------|-------|---------|
| Questões Simples | 100 questões | 33 questões | -67% |
| Questões Variação | 50 questões | 20 questões | -60% |
| Questões Imagem | 33 questões | 12 questões | -64% |
| Simulados | 20 simulados | 10 simulados | -50% |

#### Pacote Avançado (250 créditos - R$ 19,90)

| Operação | Antes | Agora | Redução |
|----------|-------|-------|---------|
| Questões Simples | 250 questões | 83 questões | -67% |
| Questões Variação | 125 questões | 50 questões | -60% |
| Questões Imagem | 83 questões | 31 questões | -63% |
| Simulados | 50 simulados | 25 simulados | -50% |

#### Pacote Premium (500 créditos - R$ 34,90)

| Operação | Antes | Agora | Redução |
|----------|-------|-------|---------|
| Questões Simples | 500 questões | 166 questões | -67% |
| Questões Variação | 250 questões | 100 questões | -60% |
| Questões Imagem | 166 questões | 62 questões | -63% |
| Simulados | 100 simulados | 50 simulados | -50% |

---

## 🎯 Uso Misto Realista

### Cenário: Usuário Estudante Típico

**Pacote Avançado (250 créditos)**:
- 60 questões simples (180 créditos)
- 10 questões variação (50 créditos)  
- 2 simulados (20 créditos)
- **Total**: 250 créditos

**Antes duraria**: 
- 60 + 20 + 10 = 90 créditos (sobrariam 160)

**Agora dura exato**: 250 créditos ✅

---

## 🔍 Monitoramento de Impacto

### Queries de Análise

```sql
-- Consumo médio por usuário (últimos 30 dias)
SELECT 
    user_id,
    COUNT(*) as transacoes,
    SUM(quantidade) as creditos_gastos,
    AVG(quantidade) as media_por_transacao
FROM transacoes_creditos 
WHERE tipo = 'debito' 
AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY user_id
ORDER BY creditos_gastos DESC;

-- Distribuição por tipo de operação
SELECT 
    referencia_tipo,
    COUNT(*) as usos,
    SUM(quantidade) as creditos_totais,
    AVG(quantidade) as custo_medio
FROM transacoes_creditos 
WHERE tipo = 'debito'
AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
GROUP BY referencia_tipo;

-- Usuários que ficaram sem créditos
SELECT 
    u.id,
    u.name,
    u.creditos,
    COALESCE(MAX(tc.created_at), 'Nunca') as ultima_transacao
FROM users u
LEFT JOIN transacoes_creditos tc ON u.id = tc.user_id
WHERE u.creditos < 5  -- Menos que custo mínimo
GROUP BY u.id;
```

---

## 📱 Frontend - Ajustes Necessários

### 1. **Atualizar Custos Exibidos**

```javascript
// Custos atualizados
const CUSTOS = {
  questao_simples: 3,    // era 1
  questao_variacao: 5,   // era 2  
  questao_imagem: 8,     // era 3
  simulado: 10           // era 0 (não cobrava)
};

// Verificação antes de ações
const verificarCreditos = (acao, quantidade = 1) => {
  const custo = CUSTOS[acao] * quantidade;
  const creditosAtuais = user.creditos;
  
  if (creditosAtuais < custo) {
    throw new Error(`Créditos insuficientes. Necessário: ${custo}, Atual: ${creditosAtuais}`);
  }
  
  return custo;
};
```

### 2. **Tela de Simulado - Nova Verificação**

```jsx
const IniciarSimulado = ({ simulado }) => {
  const [verificandoCreditos, setVerificandoCreditos] = useState(false);
  
  const iniciarSimulado = async () => {
    setVerificandoCreditos(true);
    
    try {
      // ✅ Nova verificação
      if (user.creditos < 10) {
        showError('Você precisa de 10 créditos para iniciar um simulado');
        navigate('/comprar-creditos');
        return;
      }

      const response = await api.post(`/simulados/${simulado.id}/iniciar`);
      
      if (response.data.success) {
        showSuccess(`Simulado iniciado! ${response.data.creditos_debitados} créditos debitados`);
        // Atualizar créditos do usuário
        updateUserCredits(response.data.creditos_restantes);
      }
    } catch (error) {
      if (error.response?.status === 422) {
        showError('Créditos insuficientes para iniciar simulado');
        navigate('/comprar-creditos');
      }
    } finally {
      setVerificandoCreditos(false);
    }
  };

  return (
    <Card>
      <h3>{simulado.titulo}</h3>
      <p>Custo: 10 créditos</p>
      <p>Seus créditos: {user.creditos}</p>
      
      <Button 
        onClick={iniciarSimulado}
        disabled={verificandoCreditos || user.creditos < 10}
        loading={verificandoCreditos}
      >
        {user.creditos >= 10 ? 'Iniciar Simulado' : 'Créditos Insuficientes'}
      </Button>
    </Card>
  );
};
```

### 3. **Alertas de Custo**

```jsx
const CustoAlert = ({ tipo, quantidade = 1 }) => {
  const custo = CUSTOS[tipo] * quantidade;
  const podeExecutar = user.creditos >= custo;
  
  return (
    <Alert severity={podeExecutar ? "info" : "warning"}>
      <AlertTitle>
        {podeExecutar ? "Custo da Operação" : "Créditos Insuficientes"}
      </AlertTitle>
      
      <p>Serão debitados <strong>{custo} créditos</strong></p>
      <p>Seus créditos atuais: <strong>{user.creditos}</strong></p>
      
      {!podeExecutar && (
        <Button variant="outlined" onClick={() => navigate('/comprar-creditos')}>
          Comprar Créditos
        </Button>
      )}
    </Alert>
  );
};
```

---

## ⚠️ Comunicação com Usuários

### Email/Notificação para Usuários Existentes

**Assunto**: Importante: Atualização do Sistema de Créditos

**Conteúdo**:
```
Olá [Nome],

Implementamos melhorias significativas na qualidade de nossas questões e simulados!

🔄 O que mudou:
• Questões mais inteligentes e precisas
• Simulados agora consomem créditos (funcionalidade premium)
• Novos custos refletem a qualidade aprimorada

💰 Novos custos (a partir de [data]):
• Questão simples: 3 créditos (era 1)
• Questão variação: 5 créditos (era 2)  
• Questão com imagem: 8 créditos (era 3)
• Simulado: 10 créditos (nova cobrança)

🎁 Para compensar, oferecemos 20% de desconto em todos os pacotes até [data+7dias]!

[Botão: Comprar Créditos com Desconto]

Qualquer dúvida, estamos aqui para ajudar!

Equipe [App]
```

---

## 📊 Métricas de Acompanhamento

### KPIs Importantes

1. **Taxa de Conversão de Compras**
   - Meta: Manter >80% da taxa atual
   - Monitorar: Primeiros 7 dias após implementação

2. **Churn de Usuários Ativos**
   - Meta: <10% de churn adicional
   - Monitorar: Usuários que pararam de usar

3. **Receita por Usuário**
   - Meta: Aumento de 50-80%
   - Resultado esperado dos custos maiores

4. **Satisfação do Usuário**
   - Meta: Manter NPS >7
   - Pesquisa de satisfação 1 semana após

### Dashboard de Monitoramento

```sql
-- KPI Dashboard
SELECT 
    'Receita Semanal' as metrica,
    SUM(valor) as valor_atual,
    LAG(SUM(valor)) OVER (ORDER BY WEEK(created_at)) as valor_anterior,
    ROUND((SUM(valor) / LAG(SUM(valor)) OVER (ORDER BY WEEK(created_at)) - 1) * 100, 2) as variacao_percentual
FROM pagamentos_pix 
WHERE status = 'CONCLUIDA'
GROUP BY WEEK(created_at)
ORDER BY WEEK(created_at) DESC
LIMIT 4;
```

---

## ✅ Checklist de Deploy

### Pré-Deploy
- [x] Atualizar constantes de custo no CreditoService
- [x] Implementar cobrança de créditos nos simulados
- [x] Adicionar validação de créditos
- [x] Documentar mudanças

### Deploy
- [ ] Fazer backup do banco de dados
- [ ] Deploy da versão 3.0
- [ ] Verificar se custos estão corretos
- [ ] Testar fluxo completo de cobrança

### Pós-Deploy
- [ ] Monitorar logs de erro nas primeiras 2 horas
- [ ] Verificar se simulados estão cobrando corretamente
- [ ] Acompanhar métricas de uso
- [ ] Coletar feedback dos usuários
- [ ] Enviar comunicação oficial para usuários

---

## 🎯 Resultados Esperados

### Curto Prazo (7 dias)
- ✅ Redução de 60-70% no volume de operações
- ✅ Aumento de 50-80% na receita por transação
- ✅ Usuários mais engajados comprando pacotes maiores

### Médio Prazo (30 dias)  
- ✅ Melhoria na qualidade das questões geradas
- ✅ Uso mais consciente da plataforma
- ✅ Aumento do ticket médio dos pacotes

### Longo Prazo (90 dias)
- ✅ Sustentabilidade financeira melhorada
- ✅ Possibilidade de investir em mais recursos de IA
- ✅ Base de usuários mais qualificada

---

**Status**: ✅ **Implementado e Pronto para Deploy**  
**Próxima Revisão**: 7 dias após go-live  
**Responsável**: Equipe de Produto + Desenvolvimento

---

**Logs de Monitoramento**:
```bash
# Acompanhar logs em tempo real
tail -f storage/logs/laravel.log | grep -E "debitar|creditos|simulado"

# Query de verificação
SELECT tipo, COUNT(*), AVG(quantidade) FROM transacoes_creditos 
WHERE created_at >= CURDATE() GROUP BY tipo;
```