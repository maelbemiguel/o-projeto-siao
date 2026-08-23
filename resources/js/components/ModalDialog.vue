<script setup>
defineProps({
    open:  { type: Boolean, default: false },
    title: { type: String,  default: '' },
    size:  { type: String,  default: 'md' }, // sm | md | lg
})
defineEmits(['close'])
</script>

<template>
    <teleport to="body">
        <transition name="modal">
            <div v-if="open" class="modal-overlay" @click.self="$emit('close')">
                <div class="modal-box" :class="`modal-box--${size}`" role="dialog" :aria-label="title">
                    <header class="modal-header">
                        <h3>{{ title }}</h3>
                        <button class="modal-close" @click="$emit('close')" aria-label="Fechar">✕</button>
                    </header>
                    <div class="modal-body">
                        <slot />
                    </div>
                </div>
            </div>
        </transition>
    </teleport>
</template>

<style scoped>
.modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,.45);
    display: flex; align-items: center; justify-content: center;
    z-index: 1000; padding: 20px;
}
.modal-box { background: #fff; border-radius: 16px; width: 100%; box-shadow: 0 24px 60px rgba(0,0,0,.2); overflow: hidden; }
.modal-box--sm { max-width: 420px; }
.modal-box--md { max-width: 620px; }
.modal-box--lg { max-width: 860px; }
.modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 20px 24px 16px; border-bottom: 1px solid var(--line);
}
.modal-header h3 { margin: 0; font-size: 17px; font-weight: 700; color: var(--ink); }
.modal-close { background: none; border: none; font-size: 16px; cursor: pointer; color: var(--muted); padding: 4px 8px; border-radius: 6px; }
.modal-close:hover { background: #f3f4f6; color: var(--ink); }
.modal-body { padding: 24px; }

.modal-enter-active, .modal-leave-active { transition: opacity .2s; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-active .modal-box { transition: transform .2s; }
.modal-enter-from .modal-box { transform: scale(.96) translateY(8px); }
</style>
