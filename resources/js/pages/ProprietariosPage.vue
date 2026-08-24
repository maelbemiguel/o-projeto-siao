<script setup>
import { ref, reactive, onMounted } from 'vue'
import api from '../api'
import { useNotify } from '../composables/useNotify'
import PageHeader  from '../components/PageHeader.vue'
import DataTable   from '../components/DataTable.vue'
import Pagination  from '../components/Pagination.vue'
import ModalDialog from '../components/ModalDialog.vue'
import FormField   from '../components/FormField.vue'

const { success, error: notifyError } = useNotify()

const loading       = ref(false)
const proprietarios = ref([])
const meta          = ref(null)
const search        = ref('')
const filterEstado  = ref('')
const page          = ref(1)
const showModal     = ref(false)
const isEditing     = ref(false)
const saving        = ref(false)
const errors        = ref({})

const cpfFormatado      = ref('')
const telefoneFormatado = ref('')
const cepFormatado      = ref('')

// ── Painel de imóveis vinculados ──────────────────────────────────────────────
const showPainel         = ref(false)
const painelProprietario = ref(null)   // proprietário selecionado
const imoveisVinculados  = ref([])
const loadingImoveis     = ref(false)

const form = reactive({
    idproprietario: null,
    nome:           '',
    cpf:            '',
    email:          '',
    telefone:       '',
    logradouro:     '',
    numero:         '',
    bairro:         '',
    cidade:         '',
    estado:         '',
    cep:            '',
})

const columns = [
    { key: 'nome',     label: 'Nome' },
    { key: 'cpf',      label: 'CPF' },
    { key: 'telefone', label: 'Telefone' },
    { key: 'email',    label: 'E-mail' },
    { key: 'cidade',   label: 'Cidade / UF' },
    { key: 'imoveis',  label: 'Imóveis' },
]

const ESTADOS_BR = [
    'AC','AL','AP','AM','BA','CE','DF','ES','GO',
    'MA','MT','MS','MG','PA','PB','PR','PE','PI',
    'RJ','RN','RS','RO','RR','SC','SP','SE','TO',
]

// ── Carregamento ──────────────────────────────────────────────────────────────

async function loadProprietarios() {
    loading.value = true
    try {
        const { data } = await api.get('/proprietarios', {
            params: {
                search: search.value       || undefined,
                estado: filterEstado.value || undefined,
                page:   page.value,
            },
        })
        proprietarios.value = data.data
        meta.value = { current_page: data.current_page, last_page: data.last_page }
    } catch {
        notifyError('Erro ao carregar proprietários.')
    } finally {
        loading.value = false
    }
}

async function abrirPainel(row) {
    painelProprietario.value = row
    showPainel.value         = true
    imoveisVinculados.value  = []
    loadingImoveis.value     = true
    try {
        const { data } = await api.get('/imoveis', {
            params: { proprietario_id: row.idproprietario, per_page: 100 },
        })
        imoveisVinculados.value = data.data
    } catch {
        notifyError('Erro ao carregar imóveis do proprietário.')
    } finally {
        loadingImoveis.value = false
    }
}

function fecharPainel() {
    showPainel.value         = false
    painelProprietario.value = null
    imoveisVinculados.value  = []
}

// ── CRUD ──────────────────────────────────────────────────────────────────────

function openCreate() {
    isEditing.value = false
    errors.value = {}
    Object.assign(form, {
        idproprietario: null,
        nome: '', cpf: '', email: '', telefone: '',
        logradouro: '', numero: '', bairro: '',
        cidade: '', estado: '', cep: '',
    })
    cpfFormatado.value      = ''
    telefoneFormatado.value = ''
    cepFormatado.value      = ''
    showModal.value = true
}

function openEdit(row) {
    isEditing.value = true
    errors.value = {}
    Object.assign(form, {
        idproprietario: row.idproprietario,
        nome:           row.nome       ?? '',
        cpf:            row.cpf        ?? '',
        email:          row.email      ?? '',
        telefone:       row.telefone   ?? '',
        logradouro:     row.logradouro ?? '',
        numero:         row.numero     ?? '',
        bairro:         row.bairro     ?? '',
        cidade:         row.cidade     ?? '',
        estado:         row.estado     ?? '',
        cep:            row.cep        ?? '',
    })
    digitarCpf(form.cpf)
    digitarTelefone(form.telefone)
    digitarCep(form.cep)
    showModal.value = true
}

async function save() {
    saving.value = true
    errors.value = {}
    try {
        if (isEditing.value) {
            await api.put(`/proprietarios/${form.idproprietario}`, form)
            success('Proprietário atualizado com sucesso.')
        } else {
            await api.post('/proprietarios', form)
            success('Proprietário criado com sucesso.')
        }
        showModal.value = false
        loadProprietarios()
    } catch (err) {
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors ?? {}
            notifyError('Corrija os erros no formulário.')
        } else {
            notifyError('Erro ao salvar proprietário.')
        }
    } finally {
        saving.value = false
    }
}

async function remove(row) {
    if (!confirm(`Remover o proprietário "${row.nome}"?`)) return
    try {
        await api.delete(`/proprietarios/${row.idproprietario}`)
        success('Proprietário removido.')
        if (painelProprietario.value?.idproprietario === row.idproprietario) {
            fecharPainel()
        }
        loadProprietarios()
    } catch {
        notifyError('Erro ao remover proprietário.')
    }
}

function onSearch() { page.value = 1; loadProprietarios() }
function onPage(p)  { page.value = p; loadProprietarios() }

onMounted(loadProprietarios)

// ── Formatação ────────────────────────────────────────────────────────────────

function formatarCpf(cpf) {
    const n = String(cpf ?? '').replace(/\D/g, '').slice(0, 11)
    return n
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d{1,2})$/, '$1-$2')
}

function digitarCpf(valorDigitado) {
    const n = String(valorDigitado ?? '').replace(/\D/g, '').slice(0, 11)
    form.cpf = n
    cpfFormatado.value = formatarCpf(n)
}

function formatarTelefone(telefone) {
    const n = String(telefone ?? '').replace(/\D/g, '').slice(0, 11)
    if (n.length <= 10) {
        return n.replace(/^(\d{2})(\d)/, '($1) $2').replace(/(\d{4})(\d)/, '$1-$2')
    }
    return n.replace(/^(\d{2})(\d)/, '($1) $2').replace(/(\d{5})(\d)/, '$1-$2')
}

function digitarTelefone(valorDigitado) {
    const n = String(valorDigitado ?? '').replace(/\D/g, '').slice(0, 11)
    form.telefone = n
    telefoneFormatado.value = formatarTelefone(n)
}

function digitarCep(valorDigitado) {
    const n = String(valorDigitado ?? '').replace(/\D/g, '').slice(0, 8)
    form.cep = n
    cepFormatado.value = n.replace(/(\d{5})(\d)/, '$1-$2')
}

function formatCurrency(v) {
    return v ? new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v) : '—'
}
</script>

<template>
    <div class="page-wrap">
        <PageHeader title="Proprietários" subtitle="Gestão dos proprietários cadastrados">
            <button class="btn-primary" @click="openCreate">+ Novo proprietário</button>
        </PageHeader>

        <div class="content-layout" :class="{ 'has-painel': showPainel }">
            <!-- Coluna principal -->
            <div class="main-col">
                <!-- Filtros -->
                <div class="filters">
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Buscar por nome, CPF ou e-mail..."
                        @keyup.enter="onSearch"
                    />
                    <select v-model="filterEstado" @change="onSearch">
                        <option value="">Todos os estados</option>
                        <option v-for="uf in ESTADOS_BR" :key="uf" :value="uf">{{ uf }}</option>
                    </select>
                    <button class="btn-search" @click="onSearch">Buscar</button>
                </div>

                <DataTable
                    :columns="columns"
                    :rows="proprietarios"
                    :loading="loading"
                    @edit="openEdit"
                    @delete="remove"
                >
                    <template #cell-cpf="{ row }">
                        {{ row.cpf ? formatarCpf(row.cpf) : '—' }}
                    </template>
                    <template #cell-telefone="{ row }">
                        {{ row.telefone ? formatarTelefone(row.telefone) : '—' }}
                    </template>
                    <template #cell-cidade="{ row }">
                        {{ row.cidade }}{{ row.estado ? ` / ${row.estado}` : '' }}
                    </template>
                    <template #cell-imoveis="{ row }">
                        <button class="btn-ver-imoveis" @click.stop="abrirPainel(row)">
                            Ver imóveis
                        </button>
                    </template>
                </DataTable>

                <Pagination :meta="meta" @change="onPage" />
            </div>

            <!-- Painel lateral de imóveis vinculados -->
            <aside v-if="showPainel" class="imoveis-painel">
                <div class="painel-header">
                    <div class="painel-titulo">
                        <span class="painel-label">Imóveis de</span>
                        <strong>{{ painelProprietario?.nome }}</strong>
                        <span class="painel-cpf">{{ formatarCpf(painelProprietario?.cpf) }}</span>
                    </div>
                    <button class="painel-fechar" @click="fecharPainel" aria-label="Fechar painel">✕</button>
                </div>

                <div v-if="loadingImoveis" class="painel-estado">
                    Carregando imóveis...
                </div>
                <div v-else-if="!imoveisVinculados.length" class="painel-estado painel-vazio">
                    Nenhum imóvel vinculado a este proprietário.
                </div>
                <ul v-else class="imoveis-lista">
                    <li
                        v-for="imovel in imoveisVinculados"
                        :key="imovel.idimovel"
                        class="imovel-card"
                    >
                        <div class="imovel-card-top">
                            <span class="imovel-matricula">{{ imovel.matricula }}</span>
                            <span class="badge" :class="`badge--${imovel.status}`">{{ imovel.status }}</span>
                        </div>
                        <div class="imovel-tipo">{{ imovel.tipo || 'Tipo não informado' }}</div>
                        <div class="imovel-endereco">
                            <template v-if="imovel.logradouro">
                                {{ imovel.logradouro }}{{ imovel.numero ? `, ${imovel.numero}` : '' }}
                                — {{ imovel.cidade }}{{ imovel.estado ? `/${imovel.estado}` : '' }}
                            </template>
                            <template v-else>Endereço não informado</template>
                        </div>
                        <div class="imovel-valor">{{ formatCurrency(imovel.valor_avaliado) }}</div>
                    </li>
                </ul>

                <div v-if="imoveisVinculados.length" class="painel-rodape">
                    {{ imoveisVinculados.length }} imóvel(is) vinculado(s)
                </div>
            </aside>
        </div>

        <!-- Modal de criação/edição -->
        <ModalDialog
            :open="showModal"
            :title="isEditing ? 'Editar Proprietário' : 'Novo Proprietário'"
            size="lg"
            @close="showModal = false"
        >
            <form class="form-grid" @submit.prevent="save">
                <FormField
                    label="Nome"
                    :required="true"
                    v-model="form.nome"
                    :error="errors.nome?.[0]"
                    class="col-span-2"
                />
                <FormField
                    label="CPF"
                    :required="true"
                    :model-value="cpfFormatado"
                    @update:model-value="digitarCpf"
                    :error="errors.cpf?.[0]"
                    placeholder="000.000.000-00"
                    maxlength="14"
                />
                <FormField
                    label="E-mail"
                    v-model="form.email"
                    :error="errors.email?.[0]"
                    type="email"
                />
                <FormField
                    label="Telefone"
                    :model-value="telefoneFormatado"
                    @update:model-value="digitarTelefone"
                    :error="errors.telefone?.[0]"
                    placeholder="(00) 00000-0000"
                    maxlength="15"
                />
                <FormField
                    label="Logradouro"
                    v-model="form.logradouro"
                    :error="errors.logradouro?.[0]"
                    class="col-span-2"
                />
                <FormField
                    label="Número"
                    v-model="form.numero"
                    :error="errors.numero?.[0]"
                    type="number"
                />
                <FormField
                    label="Bairro"
                    v-model="form.bairro"
                    :error="errors.bairro?.[0]"
                />
                <FormField
                    label="Cidade"
                    v-model="form.cidade"
                    :error="errors.cidade?.[0]"
                />
                <div class="form-field">
                    <label>Estado (UF)</label>
                    <select v-model="form.estado">
                        <option value="">Selecione</option>
                        <option v-for="uf in ESTADOS_BR" :key="uf" :value="uf">{{ uf }}</option>
                    </select>
                    <span v-if="errors.estado?.[0]" class="field-error">{{ errors.estado[0] }}</span>
                </div>
                <FormField
                    label="CEP"
                    :model-value="cepFormatado"
                    @update:model-value="digitarCep"
                    :error="errors.cep?.[0]"
                    placeholder="00000-000"
                    maxlength="9"
                />

                <div class="form-actions">
                    <button type="button" class="btn-secondary" @click="showModal = false">Cancelar</button>
                    <button type="submit" class="btn-primary" :disabled="saving">
                        {{ saving ? 'Salvando...' : 'Salvar' }}
                    </button>
                </div>
            </form>
        </ModalDialog>
    </div>
</template>

<style scoped>
.page-wrap { padding: 32px 36px; }

/* ── Layout com painel lateral ──────────────────────────────────────────────── */
.content-layout { display: block; }
.content-layout.has-painel {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 20px;
    align-items: start;
}
.main-col { min-width: 0; }

/* ── Filtros ─────────────────────────────────────────────────────────────── */
.filters { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
.filters input, .filters select {
    height: 42px; border: 1px solid var(--line); border-radius: 10px;
    padding: 0 14px; font-size: 14px; outline: none; background: #fff;
}
.filters input { flex: 1; min-width: 200px; }
.filters select { min-width: 160px; }
.filters input:focus, .filters select:focus { border-color: var(--green); }

/* ── Botões ──────────────────────────────────────────────────────────────── */
.btn-search    { height: 42px; padding: 0 20px; border-radius: 10px; border: 1px solid var(--line); background: #fff; cursor: pointer; font-weight: 600; font-size: 13px; }
.btn-primary   { padding: 10px 20px; border-radius: 10px; border: none; background: var(--green); color: #001d04; font-weight: 700; font-size: 14px; cursor: pointer; }
.btn-secondary { padding: 10px 20px; border-radius: 10px; border: 1px solid var(--line); background: #fff; font-weight: 600; font-size: 14px; cursor: pointer; }
.btn-primary:disabled { opacity: .6; cursor: wait; }

.btn-ver-imoveis {
    padding: 4px 12px;
    border: 1px solid var(--green);
    border-radius: 20px;
    background: #f0fdf4;
    color: #065f46;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: background .15s;
}
.btn-ver-imoveis:hover { background: #d1fae5; }

/* ── Painel lateral ──────────────────────────────────────────────────────── */
.imoveis-painel {
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 14px;
    overflow: hidden;
    position: sticky;
    top: 20px;
}

.painel-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 16px 18px;
    border-bottom: 1px solid var(--line);
    background: #fafbfa;
    gap: 12px;
}

.painel-titulo {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 0;
}
.painel-label { font-size: 11px; color: var(--muted); font-weight: 600; letter-spacing: .05em; text-transform: uppercase; }
.painel-titulo strong { font-size: 14px; color: var(--ink); word-break: break-word; }
.painel-cpf { font-size: 12px; color: var(--muted); }

.painel-fechar {
    flex-shrink: 0;
    background: none;
    border: none;
    cursor: pointer;
    color: var(--muted);
    font-size: 14px;
    padding: 4px 6px;
    border-radius: 6px;
    line-height: 1;
}
.painel-fechar:hover { background: #fef2f2; color: #b91c1c; }

.painel-estado {
    padding: 32px 18px;
    text-align: center;
    color: var(--muted);
    font-size: 13px;
}
.painel-vazio { font-style: italic; }

.imoveis-lista {
    list-style: none;
    margin: 0;
    padding: 8px 0;
    max-height: 520px;
    overflow-y: auto;
}

.imovel-card {
    padding: 12px 18px;
    border-bottom: 1px solid #f0f2f0;
    transition: background .12s;
}
.imovel-card:last-child { border-bottom: none; }
.imovel-card:hover { background: #f7fdf7; }

.imovel-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 4px;
}
.imovel-matricula { font-size: 13px; font-weight: 700; color: var(--ink); }
.imovel-tipo      { font-size: 12px; color: var(--muted); margin-bottom: 3px; }
.imovel-endereco  { font-size: 12px; color: #555; margin-bottom: 4px; line-height: 1.4; }
.imovel-valor     { font-size: 13px; font-weight: 600; color: #065f46; }

.painel-rodape {
    padding: 10px 18px;
    border-top: 1px solid var(--line);
    font-size: 12px;
    color: var(--muted);
    text-align: right;
    background: #fafbfa;
}

/* ── Badges ──────────────────────────────────────────────────────────────── */
.badge { padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: capitalize; white-space: nowrap; }
.badge--ativo     { background: #d1fae5; color: #065f46; }
.badge--inativo   { background: #fef3c7; color: #92400e; }
.badge--pendente  { background: #dbeafe; color: #1e40af; }
.badge--cancelado { background: #fce7f3; color: #9d174d; }

/* ── Formulário ──────────────────────────────────────────────────────────── */
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.col-span-2 { grid-column: span 2; }
.form-field { display: flex; flex-direction: column; gap: 4px; }
.form-field label { font-size: 13px; font-weight: 600; color: var(--ink); }
.form-field select {
    height: 40px; border: 1px solid var(--line); border-radius: 8px;
    padding: 0 12px; font-size: 14px; outline: none; background: #fff;
}
.form-field select:focus { border-color: var(--green); }
.field-error { font-size: 12px; color: #b91c1c; }
.form-actions { grid-column: span 2; display: flex; justify-content: flex-end; gap: 10px; margin-top: 8px; }
</style>
