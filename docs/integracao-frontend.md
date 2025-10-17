# Guia de Integração Frontend

## 🎯 Visão Geral

Este guia mostra como integrar o frontend com a API de geração de questões, sistema de créditos e autenticação.

## 📋 Índice

1. [Autenticação](#autenticação)
2. [Sistema de Créditos](#sistema-de-créditos)
3. [Geração de Questões](#geração-de-questões)
4. [Componentes React](#componentes-react)
5. [Gerenciamento de Estado](#gerenciamento-de-estado)

---

## 1. Autenticação

### 1.1 Login

```javascript
// services/auth.js
const API_URL = 'http://localhost/api';

export async function login(email, password) {
  try {
    const response = await fetch(`${API_URL}/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ email, password }),
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Erro ao fazer login');
    }

    // Salvar token no localStorage
    localStorage.setItem('auth_token', data.data.token);
    localStorage.setItem('user', JSON.stringify(data.data.user));

    return data.data;
  } catch (error) {
    console.error('Erro no login:', error);
    throw error;
  }
}
```

### 1.2 Registro

```javascript
// services/auth.js
export async function register(name, email, password, passwordConfirmation) {
  try {
    const response = await fetch(`${API_URL}/register`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
      }),
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Erro ao registrar');
    }

    // Salvar token
    localStorage.setItem('auth_token', data.data.token);
    localStorage.setItem('user', JSON.stringify(data.data.user));

    return data.data;
  } catch (error) {
    console.error('Erro no registro:', error);
    throw error;
  }
}
```

### 1.3 Logout

```javascript
// services/auth.js
export async function logout() {
  const token = localStorage.getItem('auth_token');

  try {
    await fetch(`${API_URL}/logout`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    });
  } catch (error) {
    console.error('Erro no logout:', error);
  } finally {
    // Limpar dados locais sempre
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
  }
}
```

### 1.4 Helper para Requisições Autenticadas

```javascript
// services/api.js
export async function apiRequest(endpoint, options = {}) {
  const token = localStorage.getItem('auth_token');

  const config = {
    ...options,
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Authorization': `Bearer ${token}`,
      ...options.headers,
    },
  };

  try {
    const response = await fetch(`${API_URL}${endpoint}`, config);
    const data = await response.json();

    if (!response.ok) {
      // Se token expirou (401), fazer logout
      if (response.status === 401) {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        window.location.href = '/login';
        throw new Error('Sessão expirada. Faça login novamente.');
      }

      throw new Error(data.message || 'Erro na requisição');
    }

    return data;
  } catch (error) {
    console.error('Erro na API:', error);
    throw error;
  }
}
```

---

## 2. Sistema de Créditos

### 2.1 Consultar Saldo

```javascript
// services/creditos.js
import { apiRequest } from './api';

export async function consultarSaldo() {
  try {
    const response = await apiRequest('/creditos/saldo');
    return response.data;
  } catch (error) {
    console.error('Erro ao consultar saldo:', error);
    throw error;
  }
}

// Exemplo de uso:
// const saldo = await consultarSaldo();
// console.log(saldo);
// {
//   "creditos": 85,
//   "creditos_semanais": 100,
//   "dias_para_renovacao": 3,
//   "proxima_renovacao": "2025-10-21 00:00:00",
//   "ultima_renovacao": "2025-10-14 00:00:00"
// }
```

### 2.2 Histórico de Transações

```javascript
// services/creditos.js
export async function consultarHistorico(limite = 50) {
  try {
    const response = await apiRequest(`/creditos/historico?limite=${limite}`);
    return response.data;
  } catch (error) {
    console.error('Erro ao consultar histórico:', error);
    throw error;
  }
}
```

### 2.3 Estatísticas de Uso

```javascript
// services/creditos.js
export async function consultarEstatisticas() {
  try {
    const response = await apiRequest('/creditos/estatisticas');
    return response.data;
  } catch (error) {
    console.error('Erro ao consultar estatísticas:', error);
    throw error;
  }
}
```

### 2.4 Consultar Custos

```javascript
// services/creditos.js
export async function consultarCustos() {
  try {
    const response = await apiRequest('/creditos/custos');
    return response.data;
  } catch (error) {
    console.error('Erro ao consultar custos:', error);
    throw error;
  }
}

// Retorna:
// {
//   "questao_simples": { "custo_unitario": 1, "custo_10_questoes": 10 },
//   "variacao": { "custo_unitario": 2, "custo_10_questoes": 20 },
//   "imagem": { "custo_unitario": 3, "custo_10_questoes": 30 },
//   "simulado": { "custo_unitario": 5, "custo_10_questoes": 50 }
// }
```

---

## 3. Geração de Questões

### 3.1 Gerar Questões por Tema

```javascript
// services/questoes.js
import { apiRequest } from './api';

export async function gerarQuestoesPorTema(temaId, assunto, quantidade, nivel = 'medio') {
  try {
    const response = await apiRequest('/questoes/gerar-por-tema', {
      method: 'POST',
      body: JSON.stringify({
        tema_id: temaId,
        assunto: assunto,
        quantidade: quantidade,
        nivel: nivel, // facil, medio, dificil, muito_dificil
      }),
    });

    return response;
  } catch (error) {
    // Tratamento especial para créditos insuficientes
    if (error.message.includes('insuficientes')) {
      throw new Error('CREDITOS_INSUFICIENTES');
    }
    throw error;
  }
}

// Exemplo de uso:
// try {
//   const result = await gerarQuestoesPorTema(1, 'Direito Constitucional', 5, 'dificil');
//   console.log(`${result.data.length} questões geradas`);
//   console.log(`Custo: ${result.custo} créditos`);
//   console.log(`Saldo restante: ${result.saldo_restante} créditos`);
// } catch (error) {
//   if (error.message === 'CREDITOS_INSUFICIENTES') {
//     alert('Você não tem créditos suficientes!');
//   }
// }
```

### 3.2 Gerar Variações de Questão

```javascript
// services/questoes.js
export async function gerarVariacoes(questaoExemplo, temaId, assunto, quantidade, nivel = 'medio') {
  try {
    const response = await apiRequest('/questoes/gerar-variacao', {
      method: 'POST',
      body: JSON.stringify({
        questao_exemplo: questaoExemplo,
        tema_id: temaId,
        assunto: assunto,
        quantidade: quantidade,
        nivel: nivel,
      }),
    });

    return response;
  } catch (error) {
    if (error.message.includes('insuficientes')) {
      throw new Error('CREDITOS_INSUFICIENTES');
    }
    throw error;
  }
}
```

### 3.3 Gerar Questões por Imagem

```javascript
// services/questoes.js
export async function gerarQuestoesPorImagem(imagem, temaId, assunto, contexto = '', nivel = 'medio') {
  try {
    const formData = new FormData();
    formData.append('imagem', imagem);
    formData.append('tema_id', temaId);
    formData.append('assunto', assunto);
    formData.append('nivel', nivel);
    if (contexto) {
      formData.append('contexto', contexto);
    }

    const token = localStorage.getItem('auth_token');

    const response = await fetch(`${API_URL}/questoes/gerar-por-imagem`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        // NÃO incluir Content-Type - deixar o browser definir com boundary
      },
      body: formData,
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Erro ao gerar questões');
    }

    return data;
  } catch (error) {
    if (error.message.includes('insuficientes')) {
      throw new Error('CREDITOS_INSUFICIENTES');
    }
    throw error;
  }
}
```

### 3.4 Listar Temas

```javascript
// services/temas.js
import { apiRequest } from './api';

export async function listarTemas() {
  try {
    const response = await apiRequest('/temas');
    return response.data;
  } catch (error) {
    console.error('Erro ao listar temas:', error);
    throw error;
  }
}
```

---

## 4. Componentes React

### 4.1 Widget de Créditos

```jsx
// components/CreditosWidget.jsx
import React, { useState, useEffect } from 'react';
import { consultarSaldo } from '../services/creditos';

export default function CreditosWidget() {
  const [saldo, setSaldo] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    carregarSaldo();
  }, []);

  async function carregarSaldo() {
    try {
      setLoading(true);
      const dados = await consultarSaldo();
      setSaldo(dados);
    } catch (error) {
      console.error('Erro ao carregar saldo:', error);
    } finally {
      setLoading(false);
    }
  }

  if (loading) {
    return <div className="creditos-widget loading">Carregando...</div>;
  }

  if (!saldo) {
    return <div className="creditos-widget error">Erro ao carregar créditos</div>;
  }

  const progressoSemana = ((7 - saldo.dias_para_renovacao) / 7) * 100;

  return (
    <div className="creditos-widget">
      <div className="saldo">
        <div className="numero">{saldo.creditos}</div>
        <div className="label">créditos disponíveis</div>
      </div>

      <div className="renovacao">
        <div className="info">
          <small>
            {saldo.dias_para_renovacao === 0 
              ? '🎉 Créditos renovam hoje!' 
              : `Renova em ${saldo.dias_para_renovacao} dias`}
          </small>
        </div>
        <div className="progress-bar">
          <div 
            className="progress-fill" 
            style={{ width: `${progressoSemana}%` }}
          />
        </div>
      </div>

      <div className="detalhes">
        <small>💰 Você recebe {saldo.creditos_semanais} créditos toda segunda-feira</small>
      </div>

      {saldo.creditos < 10 && (
        <div className="alerta">
          ⚠️ Poucos créditos restantes!
        </div>
      )}
    </div>
  );
}
```

### 4.2 Formulário de Geração de Questões

```jsx
// components/GerarQuestoesForm.jsx
import React, { useState, useEffect } from 'react';
import { listarTemas } from '../services/temas';
import { gerarQuestoesPorTema } from '../services/questoes';
import { consultarCustos } from '../services/creditos';

export default function GerarQuestoesForm({ onQuestoesGeradas }) {
  const [temas, setTemas] = useState([]);
  const [custos, setCustos] = useState(null);
  const [loading, setLoading] = useState(false);
  
  const [formData, setFormData] = useState({
    tema_id: '',
    assunto: '',
    quantidade: 5,
    nivel: 'medio',
  });

  useEffect(() => {
    carregarDados();
  }, []);

  async function carregarDados() {
    try {
      const [temasData, custosData] = await Promise.all([
        listarTemas(),
        consultarCustos(),
      ]);
      setTemas(temasData);
      setCustos(custosData);
    } catch (error) {
      console.error('Erro ao carregar dados:', error);
    }
  }

  const custoTotal = custos 
    ? custos.questao_simples.custo_unitario * formData.quantidade 
    : 0;

  async function handleSubmit(e) {
    e.preventDefault();
    
    if (!formData.tema_id || !formData.assunto) {
      alert('Preencha todos os campos obrigatórios');
      return;
    }

    try {
      setLoading(true);
      const resultado = await gerarQuestoesPorTema(
        formData.tema_id,
        formData.assunto,
        formData.quantidade,
        formData.nivel
      );

      alert(`✅ ${resultado.data.length} questões geradas com sucesso!\nCusto: ${resultado.custo} créditos\nSaldo restante: ${resultado.saldo_restante} créditos`);
      
      if (onQuestoesGeradas) {
        onQuestoesGeradas(resultado.data);
      }

      // Resetar formulário
      setFormData({ ...formData, assunto: '' });
    } catch (error) {
      if (error.message === 'CREDITOS_INSUFICIENTES') {
        alert('❌ Créditos insuficientes!\n\nVocê precisa de ' + custoTotal + ' créditos para gerar essas questões.');
      } else {
        alert('Erro ao gerar questões: ' + error.message);
      }
    } finally {
      setLoading(false);
    }
  }

  return (
    <form onSubmit={handleSubmit} className="gerar-questoes-form">
      <h2>Gerar Questões de Concurso</h2>

      <div className="form-group">
        <label>Tema *</label>
        <select
          value={formData.tema_id}
          onChange={(e) => setFormData({ ...formData, tema_id: e.target.value })}
          required
        >
          <option value="">Selecione um tema</option>
          {temas.map((tema) => (
            <option key={tema.id} value={tema.id}>
              {tema.nome}
            </option>
          ))}
        </select>
      </div>

      <div className="form-group">
        <label>Assunto *</label>
        <input
          type="text"
          value={formData.assunto}
          onChange={(e) => setFormData({ ...formData, assunto: e.target.value })}
          placeholder="Ex: Princípios Administrativos"
          required
        />
      </div>

      <div className="form-group">
        <label>Quantidade (1-10)</label>
        <input
          type="number"
          min="1"
          max="10"
          value={formData.quantidade}
          onChange={(e) => setFormData({ ...formData, quantidade: parseInt(e.target.value) })}
        />
      </div>

      <div className="form-group">
        <label>Nível de Dificuldade</label>
        <select
          value={formData.nivel}
          onChange={(e) => setFormData({ ...formData, nivel: e.target.value })}
        >
          <option value="facil">⭐ Fácil - Conceitos básicos</option>
          <option value="medio">⭐⭐ Médio - Interpretação moderada</option>
          <option value="dificil">⭐⭐⭐ Difícil - Análise crítica</option>
          <option value="muito_dificil">⭐⭐⭐⭐ Muito Difícil - Raciocínio expert</option>
        </select>
      </div>

      <div className="custo-info">
        <span>💰 Custo total: <strong>{custoTotal} créditos</strong></span>
      </div>

      <button type="submit" disabled={loading}>
        {loading ? 'Gerando questões...' : `Gerar ${formData.quantidade} Questões`}
      </button>
    </form>
  );
}
```

### 4.3 Lista de Questões

```jsx
// components/ListaQuestoes.jsx
import React from 'react';

export default function ListaQuestoes({ questoes }) {
  if (!questoes || questoes.length === 0) {
    return <div className="lista-vazia">Nenhuma questão gerada ainda.</div>;
  }

  return (
    <div className="lista-questoes">
      <h2>Questões Geradas ({questoes.length})</h2>

      {questoes.map((questao, index) => (
        <div key={questao.id || index} className="questao-card">
          <div className="questao-header">
            <span className="numero">Questão {index + 1}</span>
            <span className="nivel">{questao.nivel}</span>
            <span className="dificuldade">{questao.nivel_dificuldade || 'medio'}</span>
          </div>

          <div className="enunciado">
            {questao.enunciado}
          </div>

          <div className="alternativas">
            {questao.alternativas.map((alt, idx) => (
              <div
                key={alt.id || idx}
                className={`alternativa ${alt.correta ? 'correta' : ''}`}
              >
                <span className="letra">{String.fromCharCode(65 + idx)})</span>
                <span className="texto">{alt.texto}</span>
                {alt.correta && <span className="badge">✓ Correta</span>}
              </div>
            ))}
          </div>

          {questao.explicacao && (
            <div className="explicacao">
              <strong>Explicação:</strong>
              <p>{questao.explicacao}</p>
            </div>
          )}
        </div>
      ))}
    </div>
  );
}
```

---

## 5. Gerenciamento de Estado

### 5.1 Context para Autenticação

```jsx
// contexts/AuthContext.jsx
import React, { createContext, useState, useContext, useEffect } from 'react';
import { login as loginService, logout as logoutService } from '../services/auth';

const AuthContext = createContext();

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Carregar usuário do localStorage
    const storedUser = localStorage.getItem('user');
    if (storedUser) {
      setUser(JSON.parse(storedUser));
    }
    setLoading(false);
  }, []);

  async function login(email, password) {
    const data = await loginService(email, password);
    setUser(data.user);
  }

  async function logout() {
    await logoutService();
    setUser(null);
  }

  return (
    <AuthContext.Provider value={{ user, loading, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  return useContext(AuthContext);
}
```

### 5.2 Context para Créditos

```jsx
// contexts/CreditosContext.jsx
import React, { createContext, useState, useContext, useEffect } from 'react';
import { consultarSaldo } from '../services/creditos';

const CreditosContext = createContext();

export function CreditosProvider({ children }) {
  const [saldo, setSaldo] = useState(null);
  const [loading, setLoading] = useState(true);

  async function recarregarSaldo() {
    try {
      const dados = await consultarSaldo();
      setSaldo(dados);
    } catch (error) {
      console.error('Erro ao carregar saldo:', error);
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    recarregarSaldo();
  }, []);

  return (
    <CreditosContext.Provider value={{ saldo, loading, recarregarSaldo }}>
      {children}
    </CreditosContext.Provider>
  );
}

export function useCreditos() {
  return useContext(CreditosContext);
}
```

### 5.3 App.jsx Completo

```jsx
// App.jsx
import React from 'react';
import { AuthProvider, useAuth } from './contexts/AuthContext';
import { CreditosProvider } from './contexts/CreditosContext';
import LoginPage from './pages/LoginPage';
import Dashboard from './pages/Dashboard';

function AppContent() {
  const { user, loading } = useAuth();

  if (loading) {
    return <div className="loading">Carregando...</div>;
  }

  if (!user) {
    return <LoginPage />;
  }

  return (
    <CreditosProvider>
      <Dashboard />
    </CreditosProvider>
  );
}

export default function App() {
  return (
    <AuthProvider>
      <AppContent />
    </AuthProvider>
  );
}
```

---

## 6. Exemplo Completo de Página

```jsx
// pages/Dashboard.jsx
import React, { useState } from 'react';
import { useAuth } from '../contexts/AuthContext';
import { useCreditos } from '../contexts/CreditosContext';
import CreditosWidget from '../components/CreditosWidget';
import GerarQuestoesForm from '../components/GerarQuestoesForm';
import ListaQuestoes from '../components/ListaQuestoes';

export default function Dashboard() {
  const { user, logout } = useAuth();
  const { recarregarSaldo } = useCreditos();
  const [questoes, setQuestoes] = useState([]);

  function handleQuestoesGeradas(novasQuestoes) {
    setQuestoes([...novasQuestoes, ...questoes]);
    // Recarregar saldo após gerar questões
    recarregarSaldo();
  }

  return (
    <div className="dashboard">
      <header>
        <h1>Bem-vindo, {user.name}!</h1>
        <button onClick={logout}>Sair</button>
      </header>

      <div className="content">
        <aside className="sidebar">
          <CreditosWidget />
        </aside>

        <main>
          <GerarQuestoesForm onQuestoesGeradas={handleQuestoesGeradas} />
          <ListaQuestoes questoes={questoes} />
        </main>
      </div>
    </div>
  );
}
```

---

## 7. CSS Exemplo

```css
/* styles/app.css */
.creditos-widget {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 16px;
  padding: 24px;
  color: white;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.creditos-widget .saldo {
  text-align: center;
  margin-bottom: 20px;
}

.creditos-widget .numero {
  font-size: 48px;
  font-weight: bold;
  display: block;
}

.creditos-widget .label {
  font-size: 14px;
  opacity: 0.9;
}

.creditos-widget .progress-bar {
  height: 8px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 4px;
  overflow: hidden;
  margin-top: 8px;
}

.creditos-widget .progress-fill {
  height: 100%;
  background: white;
  transition: width 0.3s ease;
}

.creditos-widget .alerta {
  background: rgba(255, 255, 255, 0.2);
  padding: 12px;
  border-radius: 8px;
  margin-top: 16px;
  text-align: center;
  font-size: 14px;
}

.questao-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.alternativa.correta {
  background: #d4edda;
  border-left: 4px solid #28a745;
}

.custo-info {
  background: #fff3cd;
  padding: 12px;
  border-radius: 8px;
  margin: 16px 0;
  text-align: center;
}
```

---

## 🚀 Deploy e Produção

### Variáveis de Ambiente

```javascript
// .env.production
VITE_API_URL=https://api.seudominio.com/api
```

```javascript
// config.js
export const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api';
```

---

## 📱 Próximos Passos

1. ✅ Implementar sistema de cache para créditos
2. ✅ Adicionar notificações toast
3. ✅ Implementar filtros de questões
4. ✅ Adicionar exportação de questões (PDF/JSON)
5. ✅ Implementar modo offline
6. ✅ Adicionar estatísticas visuais (gráficos)

---

## 🎯 Resumo

Este guia fornece tudo que você precisa para integrar:

✅ **Autenticação completa** (login, registro, logout)  
✅ **Sistema de créditos** (saldo, histórico, estatísticas)  
✅ **Geração de questões** (tema, variação, imagem)  
✅ **Componentes React prontos** para usar  
✅ **Gerenciamento de estado** com Context API  
✅ **Tratamento de erros** (créditos insuficientes, sessão expirada)  

🚀 **Agora é só implementar no seu frontend!**
