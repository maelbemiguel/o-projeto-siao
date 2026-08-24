import axios from 'axios'

const api = axios.create({ //faz a conversão do form em um json para ser enviado, como a base é /api, qualquer endereço vem /api antes
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
    },
})

// Injeta o token Bearer em todas as requisições
api.interceptors.request.use((config) => {  //intercepta a request antes de ela sair do navegador, busca o o toekn no localstore do navegador do usuario e colocar no header `Bearer ${token}`
    const token = localStorage.getItem('token')
    if (token) config.headers.Authorization = `Bearer ${token}`
    return config
})

// Redireciona para login quando o token expirar
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('token')
            localStorage.removeItem('user')
            window.location.href = '/'
        }
        return Promise.reject(error)
    },
)

export default api
