<script setup>
defineProps({
    columns: { type: Array, required: true },
    rows:    { type: Array, required: true },
    loading: { type: Boolean, default: false },
})
defineEmits(['edit', 'delete'])
</script>

<template>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th v-for="col in columns" :key="col.key">{{ col.label }}</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="loading">
                    <td :colspan="columns.length + 1" class="loading-cell">Carregando...</td>
                </tr>
                <tr v-else-if="!rows.length">
                    <td :colspan="columns.length + 1" class="empty-cell">Nenhum registro encontrado.</td>
                </tr>
                <tr v-else v-for="row in rows" :key="row.id ?? row.idcartorio ?? row.idimovel ?? row.idusuario ?? row.idproprietario">
                    <td v-for="col in columns" :key="col.key">
                        <slot :name="`cell-${col.key}`" :row="row">
                            {{ row[col.key] ?? '—' }}
                        </slot>
                    </td>
                    <td class="actions-cell">
                        <button class="btn-icon btn-edit"   @click="$emit('edit', row)"   aria-label="Editar">✎</button>
                        <button class="btn-icon btn-delete" @click="$emit('delete', row)" aria-label="Excluir">✕</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<style scoped>
.table-wrap { overflow-x: auto; background: #fff; border-radius: 14px; border: 1px solid var(--line); }
table { width: 100%; border-collapse: collapse; font-size: 14px; }
thead th { padding: 13px 16px; text-align: left; font-size: 12px; font-weight: 700; color: var(--muted); letter-spacing: .05em; border-bottom: 1px solid var(--line); background: #fafbfa; white-space: nowrap; }
tbody td { padding: 13px 16px; border-bottom: 1px solid #f0f2f0; color: var(--ink); }
tbody tr:last-child td { border-bottom: none; }
tbody tr:hover td { background: #f7fdf7; }
.loading-cell, .empty-cell { text-align: center; padding: 36px; color: var(--muted); font-size: 14px; }
.actions-cell { white-space: nowrap; }
.btn-icon { padding: 5px 9px; border: none; border-radius: 7px; cursor: pointer; font-size: 14px; margin-right: 4px; transition: background .15s; }
.btn-edit   { background: #f0f9f0; color: #087a1a; }
.btn-edit:hover { background: #d1fae5; }
.btn-delete { background: #fef2f2; color: #b91c1c; }
.btn-delete:hover { background: #fecaca; }
</style>
