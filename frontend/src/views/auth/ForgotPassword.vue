<template>
  <div class="auth-wrapper">
    <div class="auth-card animate-scale-in">
      <div class="auth-header">
        <div class="auth-logo">
          <i class="fas fa-lock"></i>
        </div>
        <h2 class="auth-title">Recuperar Contraseña</h2>
        <p class="auth-subtitle">Ingresa tu correo electrónico para recibir un enlace de restablecimiento</p>
      </div>

      <div v-if="successMsg" class="alert alert-success animate-fade-in mb-4">
        <i class="fas fa-circle-check"></i>
        <span>{{ successMsg }}</span>
      </div>

      <div v-if="error" class="alert alert-danger animate-fade-in mb-4">
        <i class="fas fa-circle-exclamation"></i>
        <span>{{ error }}</span>
      </div>

      <form @submit.prevent="handleSubmit">
        <div class="mb-4">
          <label class="form-label">Correo electrónico</label>
          <div class="input-group">
            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
            <input type="email" class="form-control" placeholder="usuario@cetis.edu.mx" v-model="email" required />
          </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3" :disabled="loading">
          <span v-if="loading" class="spinner-border spinner-border-sm"></span>
          <span v-if="loading">Enviando...</span>
          <span v-else><i class="fas fa-paper-plane me-2"></i>Enviar Enlace</span>
        </button>

        <div class="text-center">
          <router-link to="/login" class="small">
            <i class="fas fa-arrow-left me-1"></i>Volver al inicio de sesión
          </router-link>
        </div>
      </form>
    </div>

    <p class="auth-footer">DGETI &copy; {{ new Date().getFullYear() }} &mdash; Todos los derechos reservados</p>
  </div>
</template>

<script>
import AuthService from '@/services/AuthService'

export default {
  name: 'ForgotPasswordView',
  data() {
    return {
      email: '',
      loading: false,
      error: '',
      successMsg: '',
    }
  },
  methods: {
    async handleSubmit() {
      this.error = ''
      this.successMsg = ''
      this.loading = true
      try {
        const { data } = await AuthService.forgotPassword(this.email)
        this.successMsg = data.message || 'Se ha enviado un enlace a tu correo.'
      } catch (e) {
        this.error = e.response?.data?.message || 'Error al enviar el enlace.'
      } finally {
        this.loading = false
      }
    },
  },
}
</script>

<style scoped>
.auth-wrapper {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, var(--color-bg, #1a1a2e) 0%, var(--color-bg-secondary, #16213e) 40%, var(--color-bg-tertiary, #0f3460) 100%);
  padding: 1.5rem;
}
.auth-wrapper::before {
  content: '';
  position: fixed;
  top: -50%; left: -50%;
  width: 200%; height: 200%;
  background: radial-gradient(circle at 30% 20%, rgba(169,30,34,0.15) 0%, transparent 40%),
              radial-gradient(circle at 70% 80%, rgba(185,154,83,0.1) 0%, transparent 40%);
  pointer-events: none;
}
.auth-card {
  width: 100%; max-width: 420px;
  background: var(--color-bg);
  border-radius: var(--radius-2xl);
  box-shadow: 0 25px 50px rgba(0,0,0,0.25);
  padding: var(--space-8);
  position: relative; z-index: 1;
}
.auth-header { text-align: center; margin-bottom: var(--space-6); }
.auth-logo {
  width: 56px; height: 56px;
  border-radius: var(--radius-xl);
  background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover));
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto var(--space-4);
}
.auth-logo i { font-size: 1.5rem; color: #fff; }
.auth-title { font-size: var(--font-size-2xl); font-weight: var(--font-weight-extrabold); color: var(--color-text); margin-bottom: var(--space-1); }
.auth-subtitle { font-size: var(--font-size-sm); color: var(--color-text-secondary); margin: 0; }
.auth-footer { text-align: center; font-size: var(--font-size-xs); color: rgba(255,255,255,0.5); margin-top: var(--space-8); position: relative; z-index: 1; }
</style>
