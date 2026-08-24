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

const loading        = ref(false)
const imoveis        = ref([])
const cartorios      = ref([])
const meta           = ref(null)
const search         = ref('')
const filterStatus   = ref('')
const filterCartorio = ref('')
const page           = ref(1)
const showModal      = ref(false)
const isEditing      = ref(false)
const saving         = ref(false)
const errors         = ref({})

// ── Formatação ────────────────────────────────────────────────────────────────
const cepFormatado           = ref('')
const valorAvaliadoFormatado = ref('')
const areaTotalFormatado     = ref('')

// ── Autocomplete de proprietário ──────────────────────────────────────────────
const proprietarioQuery      = ref('')       // texto digitado na busca
const proprietarioSugestoes  = ref([])       // resultados da API
const proprietarioSelecionado = ref(null)    // objeto { idproprietario, nome, cpf, ... }
const buscandoProprietario   = ref(false)
let   debounceTimer          = null

const STATUS_OPTIONS = ['ativo', 'inativo', 'pendente', 'cancelado']

const form = reactive({
    idimovel:       null,
    matricula:      '',
    tipo:           '',
    logradouro:     '',
    numero:         '',
    bairro:         '',
    cidade:         '',
    estado:         '',
    cep:            '',
    area_total:     '',
    valor_avaliado: '',
    status:         'ativo',
    proprietario_id:   null,
    proprietario_nome: '',
    proprietario_cpf:  '',
    cartorio_id:    '',
})

const columns = [
    { key: 'matricula',      label: 'Matrícula' },
    { key: 'tipo',           label: 'Tipo' },
    { key: 'proprietario',   label: 'Proprietário' },
    { key: 'cidade',         label: 'Cidade / UF' },
    { key: 'status',         label: 'Status' },
    { key: 'valor_avaliado', label: 'Valor Avaliado' },
    { key: 'cartorio',       label: 'Cartório' },
]

// ── Carregamento ──────────────────────────────────────────────────────────────

async function loadImoveis() {
    loading.value = true
    try {
        const { data } = await api.get('/imoveis', {
            params: {
                search:      search.value        || undefined,
                status:      filterStatus.value  || undefined,
                cartorio_id: filterCartorio.value || undefined,
                page:        page.value,
            },
        })
        imoveis.value = data.data
        meta.value = { current_page: data.current_page, last_page: data.last_page }
    } catch {
        notifyError('Erro ao carregar imóveis.')
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

// ── Autocomplete ──────────────────────────────────────────────────────────────

function formatarCpfDisplay(cpf) {
    const n = String(cpf ?? '').replace(/\D/g, '').slice(0, 11)
    return n
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d{1,2})$/, '$1-$2')
}

function formatarTelefoneDisplay(tel) {
    const n = String(tel ?? '').replace(/\D/g, '').slice(0, 11)
    if (n.length <= 10) {
        return n.replace(/^(\d{2})(\d)/, '($1) $2').replace(/(\d{4})(\d)/, '$1-$2')
    }
    return n.replace(/^(\d{2})(\d)/, '($1) $2').replace(/(\d{5})(\d)/, '$1-$2')
}

function digitarBuscaProprietario(valor) {
    proprietarioQuery.value = valor
    proprietarioSugestoes.value = []

    // Se o usuário apagou tudo, limpa o vínculo
    if (!valor.trim()) {
        limparProprietario()
        return
    }

    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => buscarProprietarios(valor), 300)
}

async function buscarProprietarios(termo) {
    if (!termo.trim()) { return }
    buscandoProprietario.value = true
    try {
        const { data } = await api.get('/proprietarios/busca', { params: { q: termo, limit: 10 } })
        proprietarioSugestoes.value = data
    } catch {
        proprietarioSugestoes.value = []
    } finally {
        buscandoProprietario.value = false
    }
}

function selecionarProprietario(prop) {
    proprietarioSelecionado.value = prop
    proprietarioQuery.value = prop.nome
    proprietarioSugestoes.value = []
    form.proprietario_id   = prop.idproprietario
    form.proprietario_nome = prop.nome
    form.proprietario_cpf  = prop.cpf ?? ''
}

function limparProprietario() {
    proprietarioSelecionado.value = null
    form.proprietario_id   = null
    form.proprietario_nome = ''
    form.proprietario_cpf  = ''
}

function fecharSugestoes() {
    // Pequeno delay para permitir que o clique na sugestão seja processado
    setTimeout(() => { proprietarioSugestoes.value = [] }, 150)
}

// ── CRUD ──────────────────────────────────────────────────────────────────────

function resetProprietarioAutocomplete(row = null) {
    if (row?.proprietario) {
        proprietarioSelecionado.value = row.proprietario
        proprietarioQuery.value = row.proprietario.nome ?? ''
        form.proprietario_id   = row.proprietario.idproprietario
        form.proprietario_nome = row.proprietario.nome ?? ''
        form.proprietario_cpf  = row.proprietario.cpf  ?? ''
    } else {
        proprietarioSelecionado.value = null
        proprietarioQuery.value = ''
        form.proprietario_id   = null
        form.proprietario_nome = ''
        form.proprietario_cpf  = ''
    }
    proprietarioSugestoes.value = []
}

function openCreate() {
    isEditing.value = false
    errors.value = {}
    Object.assign(form, {
        idimovel: null, matricula: '', tipo: '', logradouro: '', numero: '',
        bairro: '', cidade: '', estado: '', cep: '', area_total: '',
        valor_avaliado: '', status: 'ativo', proprietario_id: null,
        proprietario_nome: '', proprietario_cpf: '', cartorio_id: '',
    })
    cepFormatado.value           = ''
    valorAvaliadoFormatado.value = ''
    areaTotalFormatado.value     = ''
    resetProprietarioAutocomplete()
    showModal.value = true
}

function openEdit(row) {
    isEditing.value = true
    errors.value = {}
    Object.assign(form, {
        idimovel:          row.idimovel,
        matricula:         row.matricula      ?? '',
        tipo:              row.tipo           ?? '',
        logradouro:        row.logradouro     ?? '',
        numero:            row.numero         ?? '',
        bairro:            row.bairro         ?? '',
        cidade:            row.cidade         ?? '',
        estado:            row.estado         ?? '',
        cep:               row.cep            ?? '',
        area_total:        row.area_total     ?? '',
        valor_avaliado:    row.valor_avaliado ?? '',
        status:            row.status         ?? 'ativo',
        proprietario_id:   row.proprietario_id   ?? null,
        proprietario_nome: row.proprietario_nome ?? '',
        proprietario_cpf:  row.proprietario_cpf  ?? '',
        cartorio_id:       row.cartorio_id    ?? '',
    })
    digitarCep(form.cep)
    digitarValor(form.valor_avaliado)
    digitarArea(form.area_total)
    resetProprietarioAutocomplete(row)
    showModal.value = true
}

async function save() {
    saving.value = true
    errors.value = {}
    try {
        if (isEditing.value) {
            await api.put(`/imoveis/${form.idimovel}`, form)
            success('Imóvel atualizado com sucesso.')
        } else {
            await api.post('/imoveis', form)
            success('Imóvel criado com sucesso.')
        }
        showModal.value = false
        loadImoveis()
    } catch (err) {
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors ?? {}
            notifyError('Corrija os erros no formulário.')
        } else {
            notifyError('Erro ao salvar imóvel.')
        }
    } finally {
        saving.value = false
    }
}

async function remove(row) {
    if (!confirm(`Remover o imóvel "${row.matricula}"?`)) return
    try {
        await api.delete(`/imoveis/${row.idimovel}`)
        success('Imóvel removido.')
        loadImoveis()
    } catch {
        notifyError('Erro ao remover imóvel.')
    }
}

function formatCurrency(v) {
    return v ? new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v) : '—'
}

function onSearch() { page.value = 1; loadImoveis() }
function onPage(p)  { page.value = p; loadImoveis() }

onMounted(() => { loadImoveis(); loadCartorios() })

// ── Formatação de campos ──────────────────────────────────────────────────────

function digitarCep(valorDigitado) {
    const numeros = String(valorDigitado ?? '').replace(/\D/g, '').slice(0, 8)
    form.cep = numeros
    cepFormatado.value = numeros.replace(/(\d{5})(\d)/, '$1-$2')
}

function digitarValor(valorDigitado) {
    const numeros = String(valorDigitado ?? '').replace(/\D/g, '').slice(0, 15)
    const valor = Number(numeros) / 100
    form.valor_avaliado = valor.toFixed(2)
    valorAvaliadoFormatado.value = valor.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })
}

function digitarArea(valorDigitado) {
    const numeros = String(valorDigitado ?? '').replace(/\D/g, '').slice(0, 12)
    const valor = Number(numeros) / 100
    form.area_total = valor.toFixed(2)
    areaTotalFormatado.value = valor.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })
}
</script>

<template>
    <div class="page-wrap">
        <PageHeader title="Imóveis" subtitle="Gestão dos imóveis registrados">
            <button class="btn-primary" @click="openCreate">+ Novo imóvel</button>
        </PageHeader>

        <!-- Filtros -->
        <div class="filters">
            <input v-model="search" type="search" placeholder="Buscar por matrícula, endereço..." @keyup.enter="onSearch" />
            <select v-model="filterStatus" @change="onSearch">
                <option value="">Todos os status</option>
                <option v-for="s in STATUS_OPTIONS" :key="s" :value="s">{{ s }}</option>
            </select>
            <select v-model="filterCartorio" @change="onSearch">
                <option value="">Todos os cartórios</option>
                <option v-for="c in cartorios" :key="c.idcartorio" :value="c.idcartorio">{{ c.nome }}</option>
            </select>
            <button class="btn-search" @click="onSearch">Buscar</button>
        </div>

        <DataTable :columns="columns" :rows="imoveis" :loading="loading" @edit="openEdit" @delete="remove">
            <template #cell-proprietario="{ row }">
                {{ row.proprietario?.nome ?? row.proprietario_nome ?? '—' }}
            </template>
            <template #cell-cidade="{ row }">
                {{ row.cidade }}{{ row.estado ? ` / ${row.estado}` : '' }}
            </template>
            <template #cell-status="{ row }">
                <span class="badge" :class="`badge--${row.status}`">{{ row.status }}</span>
            </template>
            <template #cell-valor_avaliado="{ row }">{{ formatCurrency(row.valor_avaliado) }}</template>
            <template #cell-cartorio="{ row }">{{ row.cartorio?.nome ?? '—' }}</template>
        </DataTable>

        <Pagination :meta="meta" @change="onPage" />

        <!-- Modal -->
        <ModalDialog :open="showModal" :title="isEditing ? 'Editar Imóvel' : 'Novo Imóvel'" size="lg" @close="showModal = false">
            <form class="form-grid" @submit.prevent="save">
                <FormField label="Matrícula" :required="true" v-model="form.matricula"  :error="errors.matricula?.[0]" />
                <FormField label="Tipo"      v-model="form.tipo"       :error="errors.tipo?.[0]" placeholder="Residencial, Comercial..." />
                <FormField label="Logradouro" v-model="form.logradouro" :error="errors.logradouro?.[0]" class="col-span-2" />
                <FormField label="Número"    v-model="form.numero"      :error="errors.numero?.[0]"  type="number" />
                <FormField label="Bairro"    v-model="form.bairro"      :error="errors.bairro?.[0]" />
                <FormField label="Cidade"    v-model="form.cidade"      :error="errors.cidade?.[0]" />
                <FormField label="Estado (UF)" v-model="form.estado"   :error="errors.estado?.[0]" placeholder="SP" />
                <FormField
                    label="CEP"
                    :model-value="cepFormatado"
                    @update:model-value="digitarCep"
                    :error="errors.cep?.[0]"
                    placeholder="00000-000"
                    maxlength="9"
                />
                <FormField
                    label="Área Total (m²)"
                    :model-value="areaTotalFormatado"
                    @update:model-value="digitarArea"
                    :error="errors.area_total?.[0]"
                    type="text"
                />
                <FormField
                    label="Valor Avaliado (R$)"
                    :model-value="valorAvaliadoFormatado"
                    @update:model-value="digitarValor"
                    :error="errors.valor_avaliado?.[0]"
                    type="text"
                />

                <!-- Status -->
                <div class="form-field">
                    <label>Status</label>
                    <select v-model="form.status">
                        <option v-for="s in STATUS_OPTIONS" :key="s" :value="s">{{ s }}</option>
                    </select>
                </div>

                <!-- Cartório -->
                <div class="form-field">
                    <label>Cartório</label>
                    <select v-model="form.cartorio_id">
                        <option value="">Nenhum</option>
                        <option v-for="c in cartorios" :key="c.idcartorio" :value="c.idcartorio">{{ c.nome }}</option>
                    </select>
                </div>

                <!-- Autocomplete de proprietário -->
                <div class="form-field col-span-2 autocomplete-wrap">
                    <label>Proprietário</label>
                    <div class="autocomplete-input-wrap">
                        <input
                            class="autocomplete-input"
                            type="text"
                            :value="proprietarioQuery"
                            @input="digitarBuscaProprietario($event.target.value)"
                            @blur="fecharSugestoes"
                            placeholder="Buscar por nome, CPF, telefone ou e-mail..."
                            autocomplete="off"
                        />
                        <button
                            v-if="proprietarioSelecionado"
                            type="button"
                            class="autocomplete-clear"
                            @click="limparProprietario(); proprietarioQuery = ''"
                            aria-label="Remover proprietário"
                        >✕</button>
                        <span v-if="buscandoProprietario" class="autocomplete-spinner">⟳</span>
                    </div>

                    <!-- Tag do proprietário selecionado -->
                    <div v-if="proprietarioSelecionado" class="proprietario-tag">
                        <span class="tag-nome">{{ proprietarioSelecionado.nome }}</span>
                        <span class="tag-detalhe">{{ formatarCpfDisplay(proprietarioSelecionado.cpf) }}</span>
                        <span v-if="proprietarioSelecionado.telefone" class="tag-detalhe">
                            {{ formatarTelefoneDisplay(proprietarioSelecionado.telefone) }}
                        </span>
                    </div>

                    <!-- Lista de sugestões -->
                    <ul v-if="proprietarioSugestoes.length" class="sugestoes-lista">
                        <li
                            v-for="prop in proprietarioSugestoes"
                            :key="prop.idproprietario"
                            class="sugestao-item"
                            @mousedown.prevent="selecionarProprietario(prop)"
                        >
                            <span class="sugestao-nome">{{ prop.nome }}</span>
                            <span class="sugestao-meta">
                                {{ formatarCpfDisplay(prop.cpf) }}
                                <template v-if="prop.telefone"> · {{ formatarTelefoneDisplay(prop.telefone) }}</template>
                                <template v-if="prop.email"> · {{ prop.email }}</template>
                            </span>
                        </li>
                    </ul>

                    <!-- Nenhum resultado -->
                    <p
                        v-else-if="proprietarioQuery.length >= 2 && !buscandoProprietario && !proprietarioSelecionado"
                        class="sugestao-vazia"
                    >
                        Nenhum proprietário encontrado para "{{ proprietarioQuery }}".
                    </p>

                    <span v-if="errors.proprietario_id?.[0]" class="field-error">{{ errors.proprietario_id[0] }}</span>
                </div>

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
.filters { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
.filters input, .filters select {
    height: 42px; border: 1px solid var(--line); border-radius: 10px;
    padding: 0 14px; font-size: 14px; outline: none; background: #fff;
}
.filters input { flex: 1; min-width: 200px; }
.filters input:focus, .filters select:focus { border-color: var(--green); }
.btn-search    { height: 42px; padding: 0 20px; border-radius: 10px; border: 1px solid var(--line); background: #fff; cursor: pointer; font-weight: 600; font-size: 13px; }
.btn-primary   { padding: 10px 20px; border-radius: 10px; border: none; background: var(--green); color: #001d04; font-weight: 700; font-size: 14px; cursor: pointer; }
.btn-secondary { padding: 10px 20px; border-radius: 10px; border: 1px solid var(--line); background: #fff; font-weight: 600; font-size: 14px; cursor: pointer; }
.btn-primary:disabled { opacity: .6; cursor: wait; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.col-span-2 { grid-column: span 2; }
.form-field { display: grid; gap: 6px; }
.form-field label { font-size: 13px; font-weight: 600; color: #273142; }
.form-field select { height: 44px; border: 1px solid var(--line); border-radius: 10px; padding: 0 14px; font-size: 14px; outline: none; }
.form-field select:focus { border-color: var(--green); }
.form-actions { grid-column: span 2; display: flex; justify-content: flex-end; gap: 10px; padding-top: 8px; border-top: 1px solid var(--line); }
.badge { padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: capitalize; }
.badge--ativo     { background: #d1fae5; color: #065f46; }
.badge--inativo   { background: #fef3c7; color: #92400e; }
.badge--pendente  { background: #dbeafe; color: #1e40af; }
.badge--cancelado { background: #fce7f3; color: #9d174d; }
.field-error { font-size: 12px; color: #b91c1c; }

/* ── Autocomplete ─────────────────────────────────────────────────────────── */
.autocomplete-wrap { position: relative; }

.autocomplete-input-wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.autocomplete-input {
    width: 100%;
    height: 44px;
    border: 1px solid var(--line);
    border-radius: 10px;
    padding: 0 40px 0 14px;
    font-size: 14px;
    outline: none;
    font-family: inherit;
    color: var(--ink);
    background: #fff;
    transition: border-color .2s;
}
.autocomplete-input:focus { border-color: var(--green); }

.autocomplete-clear {
    position: absolute;
    right: 10px;
    background: none;
    border: none;
    cursor: pointer;
    color: var(--muted);
    font-size: 13px;
    line-height: 1;
    padding: 4px;
    border-radius: 4px;
}
.autocomplete-clear:hover { color: #b91c1c; background: #fef2f2; }

.autocomplete-spinner {
    position: absolute;
    right: 12px;
    color: var(--muted);
    font-size: 16px;
    animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.proprietario-tag {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    margin-top: 2px;
}
.tag-nome   { font-size: 13px; font-weight: 700; color: #065f46; }
.tag-detalhe { font-size: 12px; color: #059669; }

.sugestoes-lista {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    z-index: 50;
    list-style: none;
    margin: 0;
    padding: 4px 0;
    background: #fff;
    border: 1px solid var(--line);
    border-radius: 10px;
    box-shadow: 0 8px 24px rgba(0,0,0,.1);
    max-height: 220px;
    overflow-y: auto;
}

.sugestao-item {
    display: flex;
    flex-direction: column;
    padding: 10px 14px;
    cursor: pointer;
    transition: background .12s;
}
.sugestao-item:hover { background: #f0fdf4; }

.sugestao-nome { font-size: 14px; font-weight: 600; color: var(--ink); }
.sugestao-meta { font-size: 12px; color: var(--muted); margin-top: 2px; }

.sugestao-vazia {
    font-size: 13px;
    color: var(--muted);
    padding: 6px 0 0;
    margin: 0;
}
</style>
