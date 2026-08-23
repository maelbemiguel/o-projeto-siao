<script setup>
defineProps({
    meta: { type: Object, default: null },
})
defineEmits(['change'])
</script>

<template>
    <div v-if="meta && meta.last_page > 1" class="pagination">
        <button
            :disabled="meta.current_page === 1"
            @click="$emit('change', meta.current_page - 1)"
            class="page-btn"
        >‹ Anterior</button>

        <span class="page-info">Página {{ meta.current_page }} de {{ meta.last_page }}</span>

        <button
            :disabled="meta.current_page === meta.last_page"
            @click="$emit('change', meta.current_page + 1)"
            class="page-btn"
        >Próxima ›</button>
    </div>
</template>

<style scoped>
.pagination { display: flex; align-items: center; justify-content: center; gap: 16px; padding: 16px 0 4px; }
.page-btn { padding: 8px 16px; border: 1px solid var(--line); border-radius: 8px; background: #fff; cursor: pointer; font-size: 13px; font-weight: 600; color: var(--ink); transition: background .15s; }
.page-btn:hover:not(:disabled) { background: #f0fdf0; border-color: var(--green); }
.page-btn:disabled { opacity: .4; cursor: not-allowed; }
.page-info { font-size: 13px; color: var(--muted); }
</style>
