<script setup>
defineProps({ notifications: Array })
</script>

<template>
    <div class="notif-stack" aria-live="polite">
        <transition-group name="notif">
            <div
                v-for="n in notifications"
                :key="n.id"
                class="notif"
                :class="`notif--${n.type}`"
            >
                {{ n.message }}
            </div>
        </transition-group>
    </div>
</template>

<style scoped>
.notif-stack {
    position: fixed;
    bottom: 24px;
    right: 24px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    z-index: 9999;
}
.notif {
    padding: 12px 18px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    max-width: 340px;
    box-shadow: 0 8px 24px rgba(0,0,0,.18);
}
.notif--success { background: var(--green); color: #001d04; }
.notif--error   { background: #dc2626; color: #fff; }
.notif--info    { background: #1d4ed8; color: #fff; }

.notif-enter-active, .notif-leave-active { transition: all .3s ease; }
.notif-enter-from { opacity: 0; transform: translateY(16px); }
.notif-leave-to   { opacity: 0; transform: translateX(40px); }
</style>
