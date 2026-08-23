<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '../api'
import PageHeader from '../components/PageHeader.vue'

const loading        = ref(false)
const resumo         = ref(null)
const porCartorio    = ref([])
const relImoveis     = ref([])
const relMeta        = ref(null)
const error          = ref('')
const activeTab      = ref('resumo')

const filters = reactive({
    status:      '',
    cartorio_id: '',
    cidade:      '',
    estado:      '',
    valor_min:   '',
    valor_max:   '',
    page:        1,
})

const cartorios = ref([])
const STATUS_OPTIONS = ['ativo', 'inativo', 'pendente', 'cancelado']

async function loadResumo() {
    loading.value = true
    try {
        const [r1, r2] = await Promise.all([
            api.get('/relatorios/resumo'),
            api.get('/relatorios/imoveis-por-cartorio'),
        ])
        resumo.value      = r1.data
        porCartorio.value = r2.data
    } catch {
        error.value = 'Não foi possível carregar os relatórios.'
    } finally {
        loading.value = false
    }
}

async function loadRelImoveis() {
    loading.value = true
    try {
        const { data } = await api.get('/relatorios/imoveis', { params: filters })
        relImoveis.value = data.data
        relMeta.value = { current_page: data.current_page, last_page: data.last_page }
    } catch {
        error.value = 'Erro ao carregar relatório de imóveis.'
    } finally {
        loading.value = false
    }
}

async function loadCartorios() {
    try {
        const { data } = await api.get('/cartorios', { params: { per_page: 200 } })
        cartorios.value = data.data
    } catch { /* silencioso */ }
}

function switchTab(tab) {
    activeTab.value = tab
    if (tab === 'imoveis') loadRelImoveis()
}

function onFilterImoveis() {
    filters.page = 1
    loadRelImoveis()
}

function formatCurrency(v) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v ?? 0)
}

onMounted(() => { loadResumo(); loadCartorios() })
</script>

<template>
    <div class="page-wrap">
        <PageHeader title="Relatórios" subtitle="Análises e dados consolidados do sistema" />

        <!-- Tabs -->
        <div class="tabs">
            <button
                v-for="tab in [
                    { key: 'resumo',    label: 'Resumo Geral' },
                    { key: 'cartorios', label: 'Por Cartório' },
                    { key: 'imoveis',   label: 'Imóveis Detalhado' },
                ]"
                :key="tab.key"
                class="tab-btn"
                :class="{ 'tab-btn--active': activeTab === tab.key }"
                @click="switchTab(tab.key)"
            >
                {{ tab.label }}
            </button>
        </div>

        <div v-if="loading" class="loading-state">Carregando...</div>
        <div v-else-if="error" class="error-state">{{ error }}</div>

        <!-- Aba: Resumo -->
        <template v-else-if="activeTab === 'resumo' && resumo">
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-icon material-symbols-outlined">account_balance</span>
                    <div>
                        <span class="stat-label">Cartórios</span>
                        <span class="stat-value">{{ resumo.total_cartorios }}</span>
                    </div>
                </div>
                <div class="stat-card">
                    <span class="stat-icon material-symbols-outlined">user_attributes</span>
                    <div>
                        <span class="stat-label">Usuários</span>
                        <span class="stat-value">{{ resumo.total_usuarios }}</span>
                    </div>
                </div>
                <div class="stat-card">
                    <span class="stat-icon material-symbols-outlined">apartment</span>
                    <div>
                        <span class="stat-label">Imóveis</span>
                        <span class="stat-value">{{ resumo.total_imoveis }}</span>
                    </div>
                </div>
                <div class="stat-card stat-card--accent">
                    <span class="stat-icon material-symbols-outlined">attach_money</span>
                    <div>
                        <span class="stat-label">Valor total avaliado</span>
                        <span class="stat-value stat-value--sm">{{ formatCurrency(resumo.valor_total_avaliado) }}</span>
                    </div>
                </div>
            </div>

            <div class="section-card">
                <h2 class="section-title">Imóveis por status</h2>
                <div class="status-list">
                    <div v-for="item in resumo.imoveis_por_status" :key="item.status" class="status-item">
                        <span class="badge" :class="`badge--${item.status}`">{{ item.status }}</span>
                        <strong>{{ item.total }}</strong>
                    </div>
                </div>
            </div>
        </template>

        <!-- Aba: Por Cartório -->
        <template v-else-if="activeTab === 'cartorios'">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Cartório</th>
                            <th>Total de Imóveis</th>
                            <th>Valor Total Avaliado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!porCartorio.length">
                            <td colspan="3" class="empty-cell">Nenhum dado disponível.</td>
                        </tr>
                        <tr v-else v-for="item in porCartorio" :key="item.cartorio_id">
                            <td>{{ item.cartorio_nome }}</td>
                            <td>{{ item.total_imoveis }}</td>
                            <td>{{ formatCurrency(item.valor_total) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>

        <!-- Aba: Imóveis detalhado -->
        <template v-else-if="activeTab === 'imoveis'">
            <div class="filters">
                <select v-model="filters.status" @change="onFilterImoveis">
                    <option value="">Todos os status</option>
                    <option v-for="s in STATUS_OPTIONS" :key="s" :value="s">{{ s }}</option>
                </select>
                <select v-model="filters.cartorio_id" @change="onFilterImoveis">
                    <option value="">Todos os cartórios</option>
                    <option v-for="c in cartorios" :key="c.idcartorio" :value="c.idcartorio">{{ c.nome }}</option>
                </select>
                <input v-model="filters.cidade"   type="text"   placeholder="Cidade" @keyup.enter="onFilterImoveis" />
                <input v-model="filters.estado"   type="text"   placeholder="UF"     @keyup.enter="onFilterImoveis" maxlength="2" style="max-width:80px" />
                <input v-model="filters.valor_min" type="number" placeholder="Valor mín." @change="onFilterImoveis" />
                <input v-model="filters.valor_max" type="number" placeholder="Valor máx." @change="onFilterImoveis" />
                <button class="btn-primary" @click="onFilterImoveis">Filtrar</button>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Matrícula</th>
                            <th>Tipo</th>
                            <th>Cidade / UF</th>
                            <th>Status</th>
                            <th>Valor Avaliado</th>
                            <th>Cartório</th>
                            <th>Proprietário</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!relImoveis.length">
                            <td colspan="7" class="empty-cell">Nenhum imóvel encontrado.</td>
                        </tr>
                        <tr v-else v-for="row in relImoveis" :key="row.idimovel">
                            <td>{{ row.matricula }}</td>
                            <td>{{ row.tipo ?? '—' }}</td>
                            <td>{{ row.cidade }}{{ row.estado ? `/${row.estado}` : '' }}</td>
                            <td><span class="badge" :class="`badge--${row.status}`">{{ row.status }}</span></td>
                            <td>{{ formatCurrency(row.valor_avaliado) }}</td>
                            <td>{{ row.cartorio?.nome ?? '—' }}</td>
                            <td>{{ row.proprietario_nome ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="relMeta && relMeta.last_page > 1" class="pagination">
                <button :disabled="filters.page === 1" @click="filters.page--; loadRelImoveis()" class="page-btn">‹ Anterior</button>
                <span class="page-info">Página {{ filters.page }} de {{ relMeta.last_page }}</span>
                <button :disabled="filters.page === relMeta.last_page" @click="filters.page++; loadRelImoveis()" class="page-btn">Próxima ›</button>
            </div>
        </template>
    </div>
</template>

<style scoped>
.page-wrap { padding: 32px 36px; }
.loading-state, .error-state { padding: 40px; text-align: center; color: var(--muted); }
.error-state { color: #b42318; }
.tabs { display: flex; gap: 4px; margin-bottom: 24px; }
.tab-btn { padding: 9px 20px; border: 1px solid var(--line); border-radius: 10px; background: #fff; cursor: pointer; font-size: 13px; font-weight: 600; color: var(--muted); }
.tab-btn--active { background: var(--ink); color: #fff; border-color: var(--ink); }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
.stat-card { display: flex; align-items: center; gap: 16px; background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 20px; }
.stat-card--accent { border-color: rgba(0,239,31,.3); background: #f0fff2; min-width: 300px}
.stat-icon { font-size: 26px; }
.stat-label { display: block; font-size: 12px; color: var(--muted); font-weight: 600; }
.stat-value { display: block; font-size: 28px; font-weight: 800; letter-spacing: -.04em; color: var(--ink); }
.stat-value--sm { font-size: 18px; }
.section-card { background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 24px; }
.section-title { margin: 0 0 16px; font-size: 16px; font-weight: 700; }
.status-list { display: flex; flex-wrap: wrap; gap: 12px; }
.status-item { display: flex; align-items: center; gap: 10px; padding: 10px 16px; background: #f9fafb; border-radius: 10px; border: 1px solid var(--line); }
.badge { padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: capitalize; }
.badge--ativo    { background: #d1fae5; color: #065f46; }
.badge--inativo  { background: #fef3c7; color: #92400e; }
.badge--pendente { background: #dbeafe; color: #1e40af; }
.badge--cancelado { background: #fce7f3; color: #9d174d; }
.filters { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 18px; }
.filters input, .filters select { height: 40px; border: 1px solid var(--line); border-radius: 10px; padding: 0 12px; font-size: 13px; outline: none; background: #fff; }
.filters input:focus, .filters select:focus { border-color: var(--green); }
.btn-primary { height: 40px; padding: 0 18px; border-radius: 10px; border: none; background: var(--green); color: #001d04; font-weight: 700; font-size: 13px; cursor: pointer; }
.table-wrap { overflow-x: auto; background: #fff; border-radius: 14px; border: 1px solid var(--line); }
table { width: 100%; border-collapse: collapse; font-size: 14px; }
thead th { padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 700; color: var(--muted); border-bottom: 1px solid var(--line); background: #fafbfa; white-space: nowrap; }
tbody td { padding: 12px 16px; border-bottom: 1px solid #f0f2f0; }
tbody tr:last-child td { border-bottom: none; }
tbody tr:hover td { background: #f7fdf7; }
.empty-cell { text-align: center; padding: 32px; color: var(--muted); }
.pagination { display: flex; align-items: center; justify-content: center; gap: 16px; padding: 16px 0 4px; }
.page-btn { padding: 8px 16px; border: 1px solid var(--line); border-radius: 8px; background: #fff; cursor: pointer; font-size: 13px; font-weight: 600; }
.page-btn:disabled { opacity: .4; cursor: not-allowed; }
.page-info { font-size: 13px; color: var(--muted); }
</style>
