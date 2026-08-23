<script setup>
defineProps({
    title:    { type: String, required: true },
    subtitle: { type: String, default: '' },
})
</script>

<template>
    <header class="page-header">
        <div>
            <h1>{{ title }}</h1>
            <p v-if="subtitle">{{ subtitle }}</p>
        </div>
        <slot />
    </header>
</template>

<style scoped>
.page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 28px;
}
.page-header h1 { margin: 0; font-size: 26px; font-weight: 700; letter-spacing: -.04em; color: var(--ink); }
.page-header p  { margin: 4px 0 0; font-size: 14px; color: var(--muted); }
</style>
