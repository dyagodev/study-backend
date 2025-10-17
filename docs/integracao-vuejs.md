# Guia de Integração Vue.js

## 🎯 Visão Geral

Este guia mostra como integrar o sistema com Vue.js 3 (Composition API).

---

## 1. Composables

### 1.1 useAuth

```javascript
// composables/useAuth.js
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api';

const user = ref(null);
const token = ref(null);
const loading = ref(true);

export function useAuth() {
  const router = useRouter();

  // Inicializar
  function init() {
    const storedToken = localStorage.getItem('auth_token');
    const storedUser = localStorage.getItem('user');
    
    if (storedToken && storedUser) {
      token.value = storedToken;
      user.value = JSON.parse(storedUser);
    }
    loading.value = false;
  }

  // Login
  async function login(email, password) {
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

      // Salvar
      token.value = data.data.token;
      user.value = data.data.user;
      localStorage.setItem('auth_token', data.data.token);
      localStorage.setItem('user', JSON.stringify(data.data.user));

      return data.data;
    } catch (error) {
      console.error('Erro no login:', error);
      throw error;
    }
  }

  // Logout
  async function logout() {
    try {
      await fetch(`${API_URL}/logout`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token.value}`,
          'Accept': 'application/json',
        },
      });
    } catch (error) {
      console.error('Erro no logout:', error);
    } finally {
      token.value = null;
      user.value = null;
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      router.push('/login');
    }
  }

  // Computed
  const isAuthenticated = computed(() => !!token.value);

  return {
    user,
    token,
    loading,
    isAuthenticated,
    init,
    login,
    logout,
  };
}
```

### 1.2 useCreditos

```javascript
// composables/useCreditos.js
import { ref } from 'vue';
import { useApi } from './useApi';

export function useCreditos() {
  const { apiRequest } = useApi();
  
  const saldo = ref(null);
  const historico = ref([]);
  const estatisticas = ref(null);
  const loading = ref(false);

  // Consultar saldo
  async function consultarSaldo() {
    try {
      loading.value = true;
      const response = await apiRequest('/creditos/saldo');
      saldo.value = response.data;
      return response.data;
    } catch (error) {
      console.error('Erro ao consultar saldo:', error);
      throw error;
    } finally {
      loading.value = false;
    }
  }

  // Consultar histórico
  async function consultarHistorico(limite = 50) {
    try {
      loading.value = true;
      const response = await apiRequest(`/creditos/historico?limite=${limite}`);
      historico.value = response.data;
      return response.data;
    } catch (error) {
      console.error('Erro ao consultar histórico:', error);
      throw error;
    } finally {
      loading.value = false;
    }
  }

  // Consultar estatísticas
  async function consultarEstatisticas() {
    try {
      loading.value = true;
      const response = await apiRequest('/creditos/estatisticas');
      estatisticas.value = response.data;
      return response.data;
    } catch (error) {
      console.error('Erro ao consultar estatísticas:', error);
      throw error;
    } finally {
      loading.value = false;
    }
  }

  return {
    saldo,
    historico,
    estatisticas,
    loading,
    consultarSaldo,
    consultarHistorico,
    consultarEstatisticas,
  };
}
```

### 1.3 useQuestoes

```javascript
// composables/useQuestoes.js
import { ref } from 'vue';
import { useApi } from './useApi';

export function useQuestoes() {
  const { apiRequest } = useApi();
  
  const questoes = ref([]);
  const loading = ref(false);
  const error = ref(null);

  // Gerar por tema
  async function gerarPorTema(temaId, assunto, quantidade, nivel = 'medio') {
    try {
      loading.value = true;
      error.value = null;

      const response = await apiRequest('/questoes/gerar-por-tema', {
        method: 'POST',
        body: JSON.stringify({
          tema_id: temaId,
          assunto,
          quantidade,
          nivel,
        }),
      });

      questoes.value = [...response.data, ...questoes.value];
      return response;
    } catch (err) {
      error.value = err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  }

  // Gerar variação
  async function gerarVariacao(questaoExemplo, temaId, assunto, quantidade, nivel = 'medio') {
    try {
      loading.value = true;
      error.value = null;

      const response = await apiRequest('/questoes/gerar-variacao', {
        method: 'POST',
        body: JSON.stringify({
          questao_exemplo: questaoExemplo,
          tema_id: temaId,
          assunto,
          quantidade,
          nivel,
        }),
      });

      questoes.value = [...response.data, ...questoes.value];
      return response;
    } catch (err) {
      error.value = err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  }

  // Gerar por imagem
  async function gerarPorImagem(imagem, temaId, assunto, contexto = '', nivel = 'medio') {
    try {
      loading.value = true;
      error.value = null;

      const formData = new FormData();
      formData.append('imagem', imagem);
      formData.append('tema_id', temaId);
      formData.append('assunto', assunto);
      formData.append('nivel', nivel);
      if (contexto) formData.append('contexto', contexto);

      const token = localStorage.getItem('auth_token');
      const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api';

      const res = await fetch(`${API_URL}/questoes/gerar-por-imagem`, {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Accept': 'application/json',
        },
        body: formData,
      });

      const data = await res.json();

      if (!res.ok) {
        throw new Error(data.message || 'Erro ao gerar questões');
      }

      questoes.value = [...data.data, ...questoes.value];
      return data;
    } catch (err) {
      error.value = err.message;
      throw err;
    } finally {
      loading.value = false;
    }
  }

  // Limpar questões
  function limpar() {
    questoes.value = [];
  }

  return {
    questoes,
    loading,
    error,
    gerarPorTema,
    gerarVariacao,
    gerarPorImagem,
    limpar,
  };
}
```

### 1.4 useApi

```javascript
// composables/useApi.js
import { useRouter } from 'vue-router';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/api';

export function useApi() {
  const router = useRouter();

  async function apiRequest(endpoint, options = {}) {
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
        // Token expirado
        if (response.status === 401) {
          localStorage.removeItem('auth_token');
          localStorage.removeItem('user');
          router.push('/login');
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

  return { apiRequest };
}
```

---

## 2. Componentes

### 2.1 CreditosWidget.vue

```vue
<template>
  <div class="creditos-widget" :class="{ loading: loading }">
    <div v-if="loading" class="skeleton">
      <div class="skeleton-line"></div>
      <div class="skeleton-line"></div>
    </div>

    <div v-else-if="saldo" class="content">
      <div class="saldo">
        <div class="numero">{{ saldo.creditos }}</div>
        <div class="label">créditos disponíveis</div>
      </div>

      <div class="renovacao">
        <div class="info">
          <small>
            {{ diasParaRenovacaoTexto }}
          </small>
        </div>
        <div class="progress-bar">
          <div 
            class="progress-fill" 
            :style="{ width: progressoSemana + '%' }"
          ></div>
        </div>
      </div>

      <div class="detalhes">
        <small>💰 Você recebe {{ saldo.creditos_semanais }} créditos toda segunda-feira</small>
      </div>

      <div v-if="saldo.creditos < 10" class="alerta">
        ⚠️ Poucos créditos restantes!
      </div>
    </div>

    <div v-else class="error">
      Erro ao carregar créditos
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useCreditos } from '../composables/useCreditos';

const { saldo, loading, consultarSaldo } = useCreditos();

onMounted(() => {
  consultarSaldo();
});

const progressoSemana = computed(() => {
  if (!saldo.value) return 0;
  return ((7 - saldo.value.dias_para_renovacao) / 7) * 100;
});

const diasParaRenovacaoTexto = computed(() => {
  if (!saldo.value) return '';
  return saldo.value.dias_para_renovacao === 0 
    ? '🎉 Créditos renovam hoje!' 
    : `Renova em ${saldo.value.dias_para_renovacao} dias`;
});
</script>

<style scoped>
.creditos-widget {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 16px;
  padding: 24px;
  color: white;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.saldo {
  text-align: center;
  margin-bottom: 20px;
}

.numero {
  font-size: 48px;
  font-weight: bold;
}

.label {
  font-size: 14px;
  opacity: 0.9;
}

.progress-bar {
  height: 8px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 4px;
  overflow: hidden;
  margin-top: 8px;
}

.progress-fill {
  height: 100%;
  background: white;
  transition: width 0.3s ease;
}

.alerta {
  background: rgba(255, 255, 255, 0.2);
  padding: 12px;
  border-radius: 8px;
  margin-top: 16px;
  text-align: center;
  font-size: 14px;
}

.skeleton-line {
  height: 20px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 4px;
  margin-bottom: 12px;
  animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 0.5; }
  50% { opacity: 1; }
}
</style>
```

### 2.2 GerarQuestoesForm.vue

```vue
<template>
  <form @submit.prevent="handleSubmit" class="gerar-questoes-form">
    <h2>Gerar Questões de Concurso</h2>

    <div class="form-group">
      <label>Tema *</label>
      <select v-model="formData.tema_id" required>
        <option value="">Selecione um tema</option>
        <option v-for="tema in temas" :key="tema.id" :value="tema.id">
          {{ tema.nome }}
        </option>
      </select>
    </div>

    <div class="form-group">
      <label>Assunto *</label>
      <input
        v-model="formData.assunto"
        type="text"
        placeholder="Ex: Princípios Administrativos"
        required
      />
    </div>

    <div class="form-group">
      <label>Quantidade (1-10)</label>
      <input
        v-model.number="formData.quantidade"
        type="number"
        min="1"
        max="10"
      />
    </div>

    <div class="form-group">
      <label>Nível de Dificuldade</label>
      <select v-model="formData.nivel">
        <option value="facil">⭐ Fácil - Conceitos básicos</option>
        <option value="medio">⭐⭐ Médio - Interpretação moderada</option>
        <option value="dificil">⭐⭐⭐ Difícil - Análise crítica</option>
        <option value="muito_dificil">⭐⭐⭐⭐ Muito Difícil - Raciocínio expert</option>
      </select>
    </div>

    <div class="custo-info">
      <span>💰 Custo total: <strong>{{ custoTotal }} créditos</strong></span>
    </div>

    <button type="submit" :disabled="loadingQuestoes">
      {{ loadingQuestoes ? 'Gerando questões...' : `Gerar ${formData.quantidade} Questões` }}
    </button>
  </form>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useQuestoes } from '../composables/useQuestoes';
import { useCreditos } from '../composables/useCreditos';
import { useApi } from '../composables/useApi';

const emit = defineEmits(['questoesGeradas']);

const { apiRequest } = useApi();
const { gerarPorTema, loading: loadingQuestoes } = useQuestoes();
const { consultarSaldo } = useCreditos();

const temas = ref([]);

const formData = reactive({
  tema_id: '',
  assunto: '',
  quantidade: 5,
  nivel: 'medio',
});

const custoTotal = computed(() => formData.quantidade);

onMounted(async () => {
  try {
    const response = await apiRequest('/temas');
    temas.value = response.data;
  } catch (error) {
    console.error('Erro ao carregar temas:', error);
  }
});

async function handleSubmit() {
  if (!formData.tema_id || !formData.assunto) {
    alert('Preencha todos os campos obrigatórios');
    return;
  }

  try {
    const resultado = await gerarPorTema(
      formData.tema_id,
      formData.assunto,
      formData.quantidade,
      formData.nivel
    );

    alert(`✅ ${resultado.data.length} questões geradas com sucesso!\nCusto: ${resultado.custo} créditos\nSaldo restante: ${resultado.saldo_restante} créditos`);
    
    emit('questoesGeradas', resultado.data);
    
    // Recarregar saldo
    await consultarSaldo();

    // Resetar formulário
    formData.assunto = '';
  } catch (error) {
    if (error.message.includes('insuficientes')) {
      alert(`❌ Créditos insuficientes!\n\nVocê precisa de ${custoTotal.value} créditos para gerar essas questões.`);
    } else {
      alert('Erro ao gerar questões: ' + error.message);
    }
  }
}
</script>

<style scoped>
.gerar-questoes-form {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 14px;
}

.custo-info {
  background: #fff3cd;
  padding: 12px;
  border-radius: 8px;
  margin: 16px 0;
  text-align: center;
}

button {
  width: 100%;
  padding: 14px;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.3s;
}

button:hover:not(:disabled) {
  background: #5568d3;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>
```

### 2.3 ListaQuestoes.vue

```vue
<template>
  <div class="lista-questoes">
    <h2>Questões Geradas ({{ questoes.length }})</h2>

    <div v-if="questoes.length === 0" class="lista-vazia">
      Nenhuma questão gerada ainda.
    </div>

    <div
      v-for="(questao, index) in questoes"
      :key="questao.id || index"
      class="questao-card"
    >
      <div class="questao-header">
        <span class="numero">Questão {{ index + 1 }}</span>
        <span class="nivel">{{ questao.nivel }}</span>
        <span class="dificuldade">{{ questao.nivel_dificuldade || 'medio' }}</span>
      </div>

      <div class="enunciado">
        {{ questao.enunciado }}
      </div>

      <div class="alternativas">
        <div
          v-for="(alt, idx) in questao.alternativas"
          :key="alt.id || idx"
          :class="['alternativa', { correta: alt.correta }]"
        >
          <span class="letra">{{ String.fromCharCode(65 + idx) }})</span>
          <span class="texto">{{ alt.texto }}</span>
          <span v-if="alt.correta" class="badge">✓ Correta</span>
        </div>
      </div>

      <div v-if="questao.explicacao" class="explicacao">
        <strong>Explicação:</strong>
        <p>{{ questao.explicacao }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  questoes: {
    type: Array,
    default: () => [],
  },
});
</script>

<style scoped>
.lista-questoes {
  margin-top: 32px;
}

.lista-vazia {
  text-align: center;
  padding: 48px;
  color: #999;
}

.questao-card {
  background: white;
  border-radius: 12px;
  padding: 24px;
  margin-bottom: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.questao-header {
  display: flex;
  gap: 12px;
  margin-bottom: 16px;
}

.numero {
  font-weight: bold;
  color: #667eea;
}

.nivel,
.dificuldade {
  background: #f0f0f0;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
}

.enunciado {
  font-size: 16px;
  line-height: 1.6;
  margin-bottom: 20px;
}

.alternativas {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.alternativa {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  transition: all 0.2s;
}

.alternativa:hover {
  border-color: #667eea;
  background: #f8f9ff;
}

.alternativa.correta {
  background: #d4edda;
  border-color: #28a745;
}

.letra {
  font-weight: bold;
  min-width: 30px;
}

.badge {
  margin-left: auto;
  background: #28a745;
  color: white;
  padding: 4px 12px;
  border-radius: 12px;
  font-size: 12px;
}

.explicacao {
  margin-top: 20px;
  padding: 16px;
  background: #f8f9fa;
  border-left: 4px solid #667eea;
  border-radius: 4px;
}
</style>
```

---

## 3. Views/Pages

### 3.1 Dashboard.vue

```vue
<template>
  <div class="dashboard">
    <header>
      <h1>Bem-vindo, {{ user?.name }}!</h1>
      <button @click="handleLogout">Sair</button>
    </header>

    <div class="content">
      <aside class="sidebar">
        <CreditosWidget />
      </aside>

      <main>
        <GerarQuestoesForm @questoesGeradas="handleQuestoesGeradas" />
        <ListaQuestoes :questoes="questoes" />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuth } from '../composables/useAuth';
import CreditosWidget from '../components/CreditosWidget.vue';
import GerarQuestoesForm from '../components/GerarQuestoesForm.vue';
import ListaQuestoes from '../components/ListaQuestoes.vue';

const { user, logout } = useAuth();
const questoes = ref([]);

function handleQuestoesGeradas(novasQuestoes) {
  questoes.value = [...novasQuestoes, ...questoes.value];
}

async function handleLogout() {
  if (confirm('Deseja realmente sair?')) {
    await logout();
  }
}
</script>

<style scoped>
.dashboard {
  min-height: 100vh;
  background: #f5f5f5;
}

header {
  background: white;
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.content {
  display: grid;
  grid-template-columns: 300px 1fr;
  gap: 24px;
  padding: 24px;
  max-width: 1400px;
  margin: 0 auto;
}

.sidebar {
  position: sticky;
  top: 24px;
  height: fit-content;
}

@media (max-width: 768px) {
  .content {
    grid-template-columns: 1fr;
  }
}
</style>
```

---

## 4. Router

```javascript
// router/index.js
import { createRouter, createWebHistory } from 'vue-router';
import LoginPage from '../views/LoginPage.vue';
import Dashboard from '../views/Dashboard.vue';

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: LoginPage,
    meta: { requiresGuest: true },
  },
  {
    path: '/',
    name: 'Dashboard',
    component: Dashboard,
    meta: { requiresAuth: true },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Guard de autenticação
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('auth_token');

  if (to.meta.requiresAuth && !token) {
    next('/login');
  } else if (to.meta.requiresGuest && token) {
    next('/');
  } else {
    next();
  }
});

export default router;
```

---

## 5. App.vue Principal

```vue
<template>
  <router-view />
</template>

<script setup>
import { onMounted } from 'vue';
import { useAuth } from './composables/useAuth';

const { init } = useAuth();

onMounted(() => {
  init();
});
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}
</style>
```

---

## 🚀 Resumo Vue.js

✅ **Composition API** moderna  
✅ **Composables reutilizáveis**  
✅ **Componentes SFC** (Single File Components)  
✅ **Vue Router** com guards  
✅ **Reatividade** nativa do Vue  

🎯 **Pronto para usar no seu projeto Vue.js 3!**
