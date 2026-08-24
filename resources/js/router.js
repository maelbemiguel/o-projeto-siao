import { createRouter, createWebHashHistory } from 'vue-router'
import { useAuth } from './composables/useAuth'

const routes = [
    {
        path: '/',
        component: () => import('./pages/LoginPage.vue'),
        meta: { guest: true },
    },
    {
        path: '/dashboard',
        component: () => import('./layouts/AppLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            { path: '',         component: () => import('./pages/DashboardPage.vue') },
            { path: '/cartorios', component: () => import('./pages/CartoriosPage.vue') },
            { path: '/imoveis',        component: () => import('./pages/ImoveisPage.vue') },
            { path: '/proprietarios', component: () => import('./pages/ProprietariosPage.vue') },
            { path: '/usuarios',       component: () => import('./pages/UsuariosPage.vue') },
            { path: '/relatorios',component: () => import('./pages/RelatoriosPage.vue') },
        ],
    },
    // fallback
    { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
    history: createWebHashHistory(),
    routes,
})

router.beforeEach((to) => {
    const { isAuthenticated } = useAuth()

    if (to.meta.requiresAuth && !isAuthenticated.value) return '/'
    if (to.meta.guest && isAuthenticated.value) return '/dashboard'
})

export default router
