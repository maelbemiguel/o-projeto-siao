<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'
import { useNotify } from '../composables/useNotify'
import NotificationStack from '../components/NotificationStack.vue'

const router = useRouter()
const { user, logout } = useAuth()
const { notifications } = useNotify()

const sidebarOpen = ref(true)

const navItems = [
    { to: '/dashboard',  label: 'Dashboard',  icon: 'dashboard' },
    { to: '/cartorios',  label: 'Cartórios',   icon: 'account_balance' },
    { to: '/imoveis',    label: 'Imóveis',     icon: 'apartment' },
    { to: '/usuarios',   label: 'Usuários',    icon: 'user_attributes' },
    { to: '/relatorios', label: 'Relatórios',  icon: 'analytics' },
]

async function handleLogout() {
    await logout()
    router.push('/')
}
</script>

<template>
    <div class="app-shell" :class="{ 'sidebar-collapsed': !sidebarOpen }">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a class="brand" href="#">
                    <span class="brand-mark" aria-hidden="true"><i></i><i></i></span>
                    <span v-if="sidebarOpen" class="brand-name">sião</span>
                </a>
                <button class="toggle-btn" @click="sidebarOpen = !sidebarOpen" aria-label="Recolher menu">
                    {{ sidebarOpen ? '◀' : '▶' }}
                </button>
            </div>

            <nav class="sidebar-nav">
                <router-link
                    v-for="item in navItems"
                    :key="item.to"
                    :to="item.to"
                    class="nav-item"
                    active-class="nav-item--active"
                >
                    <span class="nav-icon material-symbols-outlined">{{ item.icon }}</span>
                    <span v-if="sidebarOpen" class="nav-label">{{ item.label }}</span>
                </router-link>
            </nav>

            <div class="sidebar-footer">
                <div class="user-info" v-if="sidebarOpen">
                    <span class="user-name">{{ user?.nome }}</span>
                    <span class="user-email">{{ user?.email }}</span>
                </div>
                <button class="logout-btn" @click="handleLogout" aria-label="Sair">
                    <span>{{ sidebarOpen ? 'Sair' : '⏏' }}</span>
                </button>
            </div>
        </aside>

        <!-- Conteúdo principal -->
        <main class="main-content">
            <router-view />
        </main>

        <!-- Notificações toast -->
        <NotificationStack :notifications="notifications" />
    </div>
</template>

<style scoped>
.app-shell {
    display: grid;
    grid-template-columns: 240px 1fr;
    min-height: 100vh;
    transition: grid-template-columns .25s;
}
.app-shell.sidebar-collapsed {
    grid-template-columns: 64px 1fr;
}

.sidebar {
    display: flex;
    flex-direction: column;
    background: var(--ink);
    color: #e8f0e9;
    overflow: hidden;
}

.sidebar-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 16px 16px;
    border-bottom: 1px solid rgba(255,255,255,.08);
}
.brand { display: flex; align-items: center; gap: 8px; text-decoration: none; color: #fff; font-size: 22px; font-weight: 700; letter-spacing: -.05em; }
.brand-mark { position: relative; display: inline-block; width: 18px; height: 28px; flex-shrink: 0; }
.brand-mark i { position: absolute; left: 2px; width: 14px; height: 18px; border: 4px solid var(--green); border-radius: 999px; transform: rotate(-33deg); }
.brand-mark i:first-child { top: 0; border-bottom-color: transparent; }
.brand-mark i:last-child  { bottom: 0; border-top-color: transparent; }
.brand-name { white-space: nowrap; }

.toggle-btn { background: none; border: none; color: #8fa896; cursor: pointer; font-size: 13px; padding: 4px 6px; border-radius: 6px; }
.toggle-btn:hover { background: rgba(255,255,255,.07); color: #fff; }

.sidebar-nav { flex: 1; padding: 12px 8px; display: flex; flex-direction: column; gap: 2px; }
.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-radius: 10px;
    color: #8fa896;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    white-space: nowrap;
    transition: background .15s, color .15s;
}
.nav-item:hover  { background: rgba(255,255,255,.07); color: #d8eeda; }
.nav-item--active { background: rgba(0,239,31,.12); color: var(--green); }
.nav-icon { font-size: 16px; flex-shrink: 0; width: 20px; text-align: center; }

.sidebar-footer { padding: 12px 8px; border-top: 1px solid rgba(255,255,255,.08); }
.user-info { padding: 0 12px 10px; display: grid; gap: 2px; }
.user-name  { font-size: 13px; font-weight: 600; color: #d8eeda; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-email { font-size: 11px; color: #5e7060; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.logout-btn { width: 100%; padding: 9px 12px; border-radius: 10px; background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.07); color: #8fa896; cursor: pointer; font-size: 13px; text-align: left; transition: background .15s, color .15s; }
.logout-btn:hover { background: rgba(220,38,38,.15); color: #fca5a5; }

.main-content { background: #f4f6f4; overflow-y: auto; }

@media (max-width: 700px) {
    .app-shell { grid-template-columns: 1fr; }
    .sidebar { display: none; }
}
</style>
