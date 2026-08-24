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

const loading      = ref(false)
const proprietarios = ref([])
const meta         = ref(null)
const search       = ref('')
const filterEstado = ref('')
const page         = ref(1)
const showModal    = ref(false)
const isEditing    = ref(false)
const saving       = ref(false)
const errors       = ref({})

const cpfFormatado      = ref('')
const telefoneFormatado = ref('')
const cepFormatado      = ref('')

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
]

const ESTADOS_BR = [
    'AC','AL','AP','AM','BA','CE','DF','ES','GO',
    'MA','MT','MS','MG','PA','PB','PR','PE','PI',
    'RJ','RN','RS','RO','RR','SC','SP','SE','TO',
]

async function loadProprietarios() {
    loading.value = true
    try {
        const { data } = await api.get('/proprietarios', {
            params: {
                search: search.value  || undefined,
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
    const numeros = String(cpf ?? '').replace(/\D/g, '').slice(0, 11)
    return numeros
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d{1,2})$/, '$1-$2')
}

function digitarCpf(valorDigitado) {
    const numeros = String(valorDigitado ?? '').replace(/\D/g, '').slice(0, 11)
    form.cpf = numeros
    cpfFormatado.value = formatarCpf(numeros)
}

function formatarTelefone(telefone) {
    const numeros = String(telefone ?? '').replace(/\D/g, '').slice(0, 11)
    if (numeros.length <= 10) {
        return numeros
            .replace(/^(\d{2})(\d)/, '($1) $2')
            .replace(/(\d{4})(\d)/, '$1-$2')
    }
    return numeros
        .replace(/^(\d{2})(\d)/, '($1) $2')
        .replace(/(\d{5})(\d)/, '$1-$2')
}

function digitarTelefone(valorDigitado) {
    const numeros = String(valorDigitado ?? '').replace(/\D/g, '').slice(0, 11)
    form.telefone = numeros
    telefoneFormatado.value = formatarTelefone(numeros)
}

function digitarCep(valorDigitado) {
    const numeros = String(valorDigitado ?? '').replace(/\D/g, '').slice(0, 8)
    form.cep = numeros
    cepFormatado.value = numeros.replace(/(\d{5})(\d)/, '$1-$2')
}
</script>

<template>
    <div class="page-wrap">
        <PageHeader title="Proprietários" subtitle="Gestão dos proprietários cadastrados">
            <button class="btn-primary" @click="openCreate">+ Novo proprietário</button>
        </PageHeader>

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
        </DataTable>

        <Pagination :meta="meta" @change="onPage" />

        <!-- Modal -->
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

.filters { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
.filters input, .filters select {
    height: 42px; border: 1px solid var(--line); border-radius: 10px;
    padding: 0 14px; font-size: 14px; outline: none; background: #fff;
}
.filters input { flex: 1; min-width: 200px; }
.filters select { min-width: 160px; }
.filters input:focus, .filters select:focus { border-color: var(--green); }

.btn-search    { height: 42px; padding: 0 20px; border-radius: 10px; border: 1px solid var(--line); background: #fff; cursor: pointer; font-weight: 600; font-size: 13px; }
.btn-primary   { padding: 10px 20px; border-radius: 10px; border: none; background: var(--green); color: #001d04; font-weight: 700; font-size: 14px; cursor: pointer; }
.btn-secondary { padding: 10px 20px; border-radius: 10px; border: 1px solid var(--line); background: #fff; font-weight: 600; font-size: 14px; cursor: pointer; }
.btn-primary:disabled { opacity: .6; cursor: wait; }

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

.form-actions {
    grid-column: span 2;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 8px;
}
</style>
