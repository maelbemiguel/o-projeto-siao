<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const { login } = useAuth()

const email       = ref('')
const password    = ref('')
const remember    = ref(false)
const showPassword = ref(false)
const loading     = ref(false)
const message     = ref('')
const messageType = ref('error')

async function handleLogin() {
    if (!email.value.trim() || !password.value) {
        messageType.value = 'error'
        message.value = 'Preencha o e-mail e a senha para continuar.'
        return
    }

    loading.value = true
    message.value = ''

    try {
        await login(email.value, password.value)
        messageType.value = 'success'
        message.value = 'Acesso autorizado. Redirecionando...'
        router.push('/dashboard')
    } catch (err) {
        messageType.value = 'error'
        const errors = err.response?.data?.errors
        if (errors?.email) {
            message.value = errors.email[0]
        } else {
            message.value = err.response?.data?.message || 'Credenciais inválidas.'
        }
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <main class="login-shell">
        <section class="brand-panel" aria-label="Apresentação do sistema">
            <div class="pattern" aria-hidden="true"></div>

            <a class="brand" href="#" aria-label="Sião Cartórios - início">
                <span class="brand-mark" aria-hidden="true"><i></i><i></i></span>
                <span>sião</span>
            </a>

            <div class="brand-copy">
                <p class="eyebrow"><span></span> GESTÃO CARTORIAL</p>
                <h1>Tecnologia que protege cada <em>registro.</em></h1>
                <p class="brand-description">
                    Acesse o ambiente de gestão do seu cartório com segurança,
                    organização e simplicidade.
                </p>
            </div>

            <div class="security-card">
                <div class="security-icon" aria-hidden="true">✓</div>
                <div>
                    <strong>Ambiente seguro</strong>
                    <span>Seus dados protegidos em todos os acessos.</span>
                </div>
            </div>

            <p class="brand-footer">Sião Tecnologia • Soluções para cartórios</p>
        </section>

        <section class="form-panel">
            <div class="mobile-brand" aria-label="Sião">
                <span class="brand-mark" aria-hidden="true"><i></i><i></i></span>
                <span>sião</span>
            </div>

            <div class="form-wrap">
                <header class="form-heading">
                    <span class="welcome-dot" aria-hidden="true"></span>
                    <p>ÁREA RESTRITA</p>
                    <h2>Bem-vindo de volta</h2>
                    <span>Entre com seus dados para acessar o sistema.</span>
                </header>

                <form novalidate @submit.prevent="handleLogin">
                    <div class="field-group">
                        <label for="email">E-mail</label>
                        <div class="field">
                            <span class="field-symbol" aria-hidden="true">@</span>
                            <input
                                id="email"
                                v-model="email"
                                name="email"
                                type="email"
                                autocomplete="email"
                                placeholder="seuemail@cartorio.com.br"
                                aria-describedby="login-message"
                            />
                        </div>
                    </div>

                    <div class="field-group">
                        <label for="password">Senha</label>
                        <div class="field">
                            <span class="field-symbol lock" aria-hidden="true">●</span>
                            <input
                                id="password"
                                v-model="password"
                                name="password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="current-password"
                                placeholder="Digite sua senha"
                                aria-describedby="login-message"
                            />
                            <button
                                class="password-toggle"
                                type="button"
                                :aria-label="showPassword ? 'Ocultar senha' : 'Mostrar senha'"
                                @click="showPassword = !showPassword"
                            >
                                {{ showPassword ? 'Ocultar' : 'Mostrar' }}
                            </button>
                        </div>
                    </div>

                    <label class="remember">
                        <input v-model="remember" type="checkbox" name="remember" />
                        <span>Lembrar meu acesso neste dispositivo</span>
                    </label>

                    <button class="submit-button" type="submit" :disabled="loading">
                        <span>{{ loading ? 'Entrando...' : 'Entrar no sistema' }}</span>
                        <b v-if="!loading" aria-hidden="true">→</b>
                    </button>

                    <p
                        id="login-message"
                        class="form-message"
                        :class="messageType"
                        aria-live="polite"
                    >
                        {{ message }}
                    </p>
                </form>

                <div class="help-text">
                    <span>Não consegue acessar?</span>
                    <p>Entre em contato com o administrador do seu cartório.</p>
                </div>
            </div>

            <footer class="form-footer">
                <span>© 2026 Sião Tecnologia</span>
                <span>Privacidade e segurança</span>
            </footer>
        </section>
    </main>
</template>
