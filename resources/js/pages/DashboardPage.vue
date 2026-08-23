<script setup>
import { ref, onMounted } from 'vue'
import api from '../api'
import PageHeader from '../components/PageHeader.vue'

const loading = ref(true)
const resumo  = ref(null)
const error   = ref('')

onMounted(async () => {
    try {
        const { data } = await api.get('/relatorios/resumo')
        resumo.value = data
    } catch {
        error.value = 'Não foi possível carregar o resumo.'
    } finally {
        loading.value = false
    }
})

function formatCurrency(value) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value ?? 0)
}
</script>

<template>
    <div class="page-wrap">
        <PageHeader title="Dashboard" subtitle="Visão geral do sistema cartorial" />

        <div v-if="loading" class="loading-state">Carregando dados...</div>
        <div v-else-if="error" class="error-state">{{ error }}</div>

        <template v-else-if="resumo">
            <!-- Cards de totais -->
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
                <div class="stat-card-2 stat-card--accent ">
                    <span class="stat-icon material-symbols-outlined">attach_money</span>
                    <div>
                        <span class="stat-label">Valor total avaliado</span>
                        <span class="stat-value-2 stat-value--sm">{{ formatCurrency(resumo.valor_total_avaliado) }}</span>
                    </div>
                </div>
            </div>

            <!-- Imóveis por status -->
            <div class="section-card">
                <h2 class="section-title">Imóveis por status</h2>
                <div v-if="!resumo.imoveis_por_status?.length" class="empty-text">Nenhum imóvel registrado.</div>
                <div v-else class="status-list">
                    <div
                        v-for="item in resumo.imoveis_por_status"
                        :key="item.status"
                        class="status-item"
                    >
                        <span class="status-badge" :class="`status-${item.status}`">{{ item.status }}</span>
                        <span class="status-count">{{ item.total }}</span>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<style scoped>
.page-wrap { padding: 32px 36px; }
.loading-state, .error-state { padding: 40px; text-align: center; color: var(--muted); }
.error-state { color: #b42318; }

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
}
.stat-card {
    display: flex; align-items: center; gap: 16px;
    background: #fff; border: 1px solid var(--line);
    border-radius: 14px; padding: 20px 22px;
}
.stat-card-2 {
    display: flex; align-items: center; gap: 16px;
    background: #fff; border: 1px solid var(--line);
    border-radius: 14px; padding: 20px 22px;
}
.stat-card--accent { border-color: rgba(0,239,31,.3); background: #f0fff2; min-width: 300px}
.stat-icon { font-size: 28px; }
.stat-label { display: block; font-size: 12px; color: var(--muted); font-weight: 600; letter-spacing: .04em; }
.stat-value { display: block; font-size: 30px; font-weight: 800; letter-spacing: -.04em; color: var(--ink); line-height: 1.1; margin-top: 4px; }
.stat-value-2 { display: block; font-size: 30px; font-weight: 800; letter-spacing: -.04em; color: var(--ink); line-height: 1.1; margin-top: 4px; }
.stat-value--sm { font-size: 20px; }

.section-card { background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 24px; }
.section-title { margin: 0 0 18px; font-size: 16px; font-weight: 700; color: var(--ink); }
.empty-text { color: var(--muted); font-size: 14px; }
.status-list { display: flex; flex-wrap: wrap; gap: 12px; }
.status-item { display: flex; align-items: center; gap: 10px; padding: 10px 16px; background: #f9fafb; border-radius: 10px; border: 1px solid var(--line); }
.status-badge { font-size: 12px; font-weight: 700; letter-spacing: .05em; text-transform: capitalize; }
.status-ativo    { color: #087a1a; }
.status-inativo  { color: #b45309; }
.status-pendente { color: #1d4ed8; }
.status-count { font-size: 20px; font-weight: 800; color: var(--ink); }
</style>
