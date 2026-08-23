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

const loading   = ref(false)
const usuarios  = ref([])
const cartorios = ref([])
const meta      = ref(null)
const search    = ref('')
const filterCartorio = ref('')
const page      = ref(1)
const showModal = ref(false)
const isEditing = ref(false)
const saving    = ref(false)
const errors    = ref({})

const form = reactive({
    idusuario:             null,
    nome:                  '',
    cpf:                   '',
    email:                 '',
    password:              '',
    password_confirmation: '',
    telefone:              '',
    endereco:              '',
    cidade:                '',
    estado:                '',
    cep:                   '',
    cartorio_id:           '',
})

const columns = [
    { key: 'nome',     label: 'Nome' },
    { key: 'cpf',      label: 'CPF' },
    { key: 'email',    label: 'E-mail' },
    { key: 'telefone', label: 'Telefone' },
    { key: 'cidade',   label: 'Cidade / UF' },
    { key: 'cartorio', label: 'Cartório' },
]

async function loadUsuarios() {
    loading.value = true
    try {
        const { data } = await api.get('/usuarios', {
            params: {
                search:      search.value         || undefined,
                cartorio_id: filterCartorio.value || undefined,
                page:        page.value,
            },
        })
        usuarios.value = data.data
        meta.value = { current_page: data.current_page, last_page: data.last_page }
    } catch {
        notifyError('Erro ao carregar usuários.')
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

function openCreate() {
    isEditing.value = false
    errors.value = {}
    Object.assign(form, {
        idusuario: null, nome: '', cpf: '', email: '', password: '',
        password_confirmation: '', telefone: '', endereco: '',
        cidade: '', estado: '', cep: '', cartorio_id: '',
    })
    showModal.value = true
}

function openEdit(row) {
    isEditing.value = true
    errors.value = {}
    Object.assign(form, {
        idusuario:             row.idusuario,
        nome:                  row.nome        ?? '',
        cpf:                   row.cpf         ?? '',
        email:                 row.email       ?? '',
        password:              '',
        password_confirmation: '',
        telefone:              row.telefone    ?? '',
        endereco:              row.endereco    ?? '',
        cidade:                row.cidade      ?? '',
        estado:                row.estado      ?? '',
        cep:                   row.cep         ?? '',
        cartorio_id:           row.cartorio_id ?? '',
    })
    showModal.value = true
}

async function save() {
    saving.value = true
    errors.value = {}
    try {
        if (isEditing.value) {
            await api.put(`/usuarios/${form.idusuario}`, form)
            success('Usuário atualizado com sucesso.')
        } else {
            await api.post('/usuarios', form)
            success('Usuário criado com sucesso.')
        }
        showModal.value = false
        loadUsuarios()
    } catch (err) {
        if (err.response?.status === 422) {
            errors.value = err.response.data.errors ?? {}
            notifyError('Corrija os erros no formulário.')
        } else {
            notifyError('Erro ao salvar usuário.')
        }
    } finally {
        saving.value = false
    }
}

async function remove(row) {
    if (!confirm(`Remover o usuário "${row.nome}"?`)) return
    try {
        await api.delete(`/usuarios/${row.idusuario}`)
        success('Usuário removido.')
        loadUsuarios()
    } catch {
        notifyError('Erro ao remover usuário.')
    }
}

function onSearch() { page.value = 1; loadUsuarios() }
function onPage(p)  { page.value = p; loadUsuarios() }

onMounted(() => { loadUsuarios(); loadCartorios() })
</script>

<template>
    <div class="page-wrap">
        <PageHeader title="Usuários" subtitle="Gestão dos usuários do sistema">
            <button class="btn-primary" @click="openCreate">+ Novo usuário</button>
        </PageHeader>

        <div class="filters">
            <input v-model="search" type="search" placeholder="Buscar por nome, CPF ou e-mail..." @keyup.enter="onSearch" />
            <select v-model="filterCartorio" @change="onSearch">
                <option value="">Todos os cartórios</option>
                <option v-for="c in cartorios" :key="c.idcartorio" :value="c.idcartorio">{{ c.nome }}</option>
            </select>
            <button class="btn-search" @click="onSearch">Buscar</button>
        </div>

        <DataTable :columns="columns" :rows="usuarios" :loading="loading" @edit="openEdit" @delete="remove">
            <template #cell-cidade="{ row }">
                {{ row.cidade }}{{ row.estado ? ` / ${row.estado}` : '' }}
            </template>
            <template #cell-cartorio="{ row }">{{ row.cartorio?.nome ?? '—' }}</template>
        </DataTable>

        <Pagination :meta="meta" @change="onPage" />

        <!-- Modal -->
        <ModalDialog :open="showModal" :title="isEditing ? 'Editar Usuário' : 'Novo Usuário'" size="lg" @close="showModal = false">
            <form class="form-grid" @submit.prevent="save">
                <FormField label="Nome"  :required="true" v-model="form.nome"  :error="errors.nome?.[0]" class="col-span-2" />
                <FormField label="CPF"   :required="true" v-model="form.cpf"   :error="errors.cpf?.[0]"  placeholder="000.000.000-00" />
                <FormField label="E-mail" :required="true" v-model="form.email" :error="errors.email?.[0]" type="email" />
                <FormField
                    :label="isEditing ? 'Nova Senha (opcional)' : 'Senha'"
                    :required="!isEditing"
                    v-model="form.password"
                    :error="errors.password?.[0]"
                    type="password"
                />
                <FormField
                    label="Confirmar Senha"
                    :required="!isEditing"
                    v-model="form.password_confirmation"
                    type="password"
                />
                <FormField label="Telefone" v-model="form.telefone" :error="errors.telefone?.[0]" />
                <FormField label="Endereço" v-model="form.endereco" :error="errors.endereco?.[0]" />
                <FormField label="Cidade"   v-model="form.cidade"   :error="errors.cidade?.[0]" />
                <FormField label="Estado (UF)" v-model="form.estado" :error="errors.estado?.[0]" placeholder="SP" />
                <FormField label="CEP"      v-model="form.cep"       :error="errors.cep?.[0]" placeholder="00000-000" />

                <div class="form-field">
                    <label>Cartório</label>
                    <select v-model="form.cartorio_id">
                        <option value="">Nenhum</option>
                        <option v-for="c in cartorios" :key="c.idcartorio" :value="c.idcartorio">{{ c.nome }}</option>
                    </select>
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
.filters input, .filters select { height: 42px; border: 1px solid var(--line); border-radius: 10px; padding: 0 14px; font-size: 14px; outline: none; background: #fff; }
.filters input { flex: 1; min-width: 200px; }
.filters input:focus, .filters select:focus { border-color: var(--green); }
.btn-search  { height: 42px; padding: 0 20px; border-radius: 10px; border: 1px solid var(--line); background: #fff; cursor: pointer; font-weight: 600; font-size: 13px; }
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
</style>
