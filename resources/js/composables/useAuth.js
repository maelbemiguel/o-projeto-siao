import { ref, computed } from 'vue'
import api from '../api'

const user = ref(JSON.parse(localStorage.getItem('user') || 'null'))
const token = ref(localStorage.getItem('token') || null)

export function useAuth() {
    const isAuthenticated = computed(() => !!token.value) //transforma em true ou false

    async function login(email, password) {
        const { data } = await api.post('/auth/login', { email, password })
        token.value = data.token
        user.value = data.user
        localStorage.setItem('token', data.token)
        localStorage.setItem('user', JSON.stringify(data.user))
        return data
    }

    async function logout() {
        try {
            await api.post('/auth/logout')
        } finally {
            token.value = null
            user.value = null
            localStorage.removeItem('token')
            localStorage.removeItem('user')
        }
    }

    return { user, token, isAuthenticated, login, logout }
}
