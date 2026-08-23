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

const loading    = ref(false)
const cartorios  = ref([])
const meta       = ref(null)
const search     = ref('')
const page       = ref(1)
const showModal  = ref(false)
const isEditing  = ref(false)
const saving     = ref(false)
const errors     = ref({})

const form = reactive({
    idcartorio:       null,
    nome:             '',
    cnpj:             '',
    telefone:         '',
    email:            '',
    logradouro:       '',
    numero:           '',
    bairro:           '',
    cidade:           '',
    estado:           '',
    cep:              '',
    responsavel_nome: '',
    responsavel_cpf:  '',
})

const columns = [
    { key: 'nome',      label: 'Nome' },
    { key: 'cnpj',      label: 'CNPJ' },
    { key: 'cidade',    label: 'Cidade / UF' },
    { key: 'telefone',  label: 'Telefone' },
    { key: 'email',     label: 'E-mail' },
]

async function loadCartorios() {
    loading.value = true
    try {
        const { data } = await api.get('/cartorios', {
            params: { search: search.value || undefined, page: page.value },
        })
        cartorios.value = data.data
        meta.value = { current_page: data.current_page, last_page: data.last_page }
    } catch {
        notifyError('Erro ao carregar cartórios.')
    } finally {
        loading.value = false
    }
}

function openCreate() {
    isEditing.value = false
    errors.value = {}
    Object.assign(form, {
        idcartorio: null, nome: '', cnpj: '', telefone: '', email: '',
        logradouro: '', numero: '', bairro: '', cidade: '', estado: '',
        cep: '', responsavel_nome: '', responsavel_cpf: '',
    })
    showModal.value = true
}

function openEdit(row) {
    isEditing.value = true
    errors.value = {}
    Object.assign(form, {
        idcartorio:       row.idcartorio,
        nome:             row.nome             ?? '',
        cnpj:             row.cnpj             ?? '',
        telefone:         row.telefone         ?? '',
        email:            row.email            ?? '',
        logradouro:       row.logradouro       ?? '',
        numero:           row.numero           ?? '',
        bairro:           row.bairro           ?? '',
        cidade:           row.cidade           ?? '',
        estado:           row.estado           ?? '',
        cep:              row.cep              ?? '',
        responsavel_nome: row.responsavel_nome ?? '',
        responsavel_cpf:  row.responsavel_cpf  ?? '',
    })
    showModal.value = true
}

async function save() {
    saving.value = true
    errors.value = {}
    try {
        if (isEditing.value) {
            await api.put(`/cartorios/${form.idcartorio}`, form)
            success('Cartório atualizado com sucesso.')
        } else {
            await api.post('/cartorios', form)
            success('Cartório criado com sucesso.')
        }
        showModal.value = false
        loadCartorios()
    } catch (err) {
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors ?? {}
            notifyError('Corrija os erros no formulário.')
        } else {
            notifyError('Erro ao salvar cartório.')
        }
    } finally {
        saving.value = false
    }
}

async function remove(row) {
    if (!confirm(`Remover o cartório "${row.nome}"?`)) return
    try {
        await api.delete(`/cartorios/${row.idcartorio}`)
        success('Cartório removido.')
        loadCartorios()
    } catch {
        notifyError('Erro ao remover cartório.')
    }
}

function onSearch() { page.value = 1; loadCartorios() }
function onPage(p)  { page.value = p; loadCartorios() }

onMounted(loadCartorios)
</script>

<template>
    <div class="page-wrap">
        <PageHeader title="Cartórios" subtitle="Gestão dos cartórios cadastrados">
            <button class="btn-primary" @click="openCreate">+ Novo cartório</button>
        </PageHeader>

        <!-- Busca -->
        <div class="search-bar">
            <input v-model="search" type="search" placeholder="Buscar por nome, CNPJ ou cidade..." @keyup.enter="onSearch" />
            <button class="btn-search" @click="onSearch">Buscar</button>
        </div>

        <DataTable
            :columns="columns"
            :rows="cartorios"
            :loading="loading"
            @edit="openEdit"
            @delete="remove"
        >
            <template #cell-cidade="{ row }">
                {{ row.cidade }}{{ row.estado ? ` / ${row.estado}` : '' }}
            </template>
        </DataTable>

        <Pagination :meta="meta" @change="onPage" />

        <!-- Modal -->
        <ModalDialog :open="showModal" :title="isEditing ? 'Editar Cartório' : 'Novo Cartório'" size="lg" @close="showModal = false">
            <form class="form-grid" @submit.prevent="save">
                <FormField label="Nome"   :required="true"  v-model="form.nome"     :error="errors.nome?.[0]" />
                <FormField label="CNPJ"   :required="true"  v-model="form.cnpj"     :error="errors.cnpj?.[0]" placeholder="00.000.000/0001-00" />
                <FormField label="Telefone"   v-model="form.telefone"  :error="errors.telefone?.[0]" />
                <FormField label="E-mail"     v-model="form.email"     :error="errors.email?.[0]" type="email" />
                <FormField label="Logradouro" v-model="form.logradouro" :error="errors.logradouro?.[0]" class="col-span-2" />
                <FormField label="Número"     v-model="form.numero"     :error="errors.numero?.[0]" type="number" />
                <FormField label="Bairro"     v-model="form.bairro"     :error="errors.bairro?.[0]" />
                <FormField label="Cidade"     v-model="form.cidade"     :error="errors.cidade?.[0]" />
                <FormField label="Estado (UF)" v-model="form.estado"   :error="errors.estado?.[0]" placeholder="SP" />
                <FormField label="CEP"        v-model="form.cep"        :error="errors.cep?.[0]" placeholder="00000-000" />
                <FormField label="Responsável — Nome" v-model="form.responsavel_nome" :error="errors.responsavel_nome?.[0]" />
                <FormField label="Responsável — CPF"  v-model="form.responsavel_cpf"  :error="errors.responsavel_cpf?.[0]" placeholder="000.000.000-00" />

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
.page-wrap  { padding: 32px 36px; }
.search-bar { display: flex; gap: 10px; margin-bottom: 20px; }
.search-bar input { flex: 1; height: 42px; border: 1px solid var(--line); border-radius: 10px; padding: 0 14px; font-size: 14px; outline: none; }
.search-bar input:focus { border-color: var(--green); }
.btn-search { height: 42px; padding: 0 20px; border-radius: 10px; border: 1px solid var(--line); background: #fff; cursor: pointer; font-weight: 600; font-size: 13px; }
.btn-primary   { padding: 10px 20px; border-radius: 10px; border: none; background: var(--green); color: #001d04; font-weight: 700; font-size: 14px; cursor: pointer; }
.btn-secondary { padding: 10px 20px; border-radius: 10px; border: 1px solid var(--line); background: #fff; font-weight: 600; font-size: 14px; cursor: pointer; }
.btn-primary:disabled { opacity: .6; cursor: wait; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.col-span-2 { grid-column: span 2; }
.form-actions { grid-column: span 2; display: flex; justify-content: flex-end; gap: 10px; padding-top: 8px; border-top: 1px solid var(--line); }
</style>
