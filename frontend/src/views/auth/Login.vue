<template>
  <div class="login-wrapper">
    <div class="login-card animate-slide-up">
      <!-- Header -->
      <div class="login-header">
        <div class="login-logo">
          <i class="fas fa-school"></i>
        </div>
        <h1 class="login-title">CETIS</h1>
        <p class="login-subtitle">Sistema Integral de Control Escolar</p>
      </div>

      <!-- Error -->
      <div v-if="error" class="login-error animate-fade-in">
        <i class="fas fa-circle-exclamation"></i>
        <span>{{ error }}</span>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleLogin" class="login-form">
        <!-- Username -->
        <div class="login-field">
          <label class="login-label">Usuario o correo</label>
          <div class="login-input-group">
            <span class="login-input-icon"><i class="fas fa-user"></i></span>
            <input
              type="text"
              class="login-input"
              placeholder="admin"
              v-model="credentials.username"
              required
              autofocus
              autocomplete="username"
              inputmode="text"
            />
          </div>
        </div>

        <!-- Password -->
        <div class="login-field">
          <label class="login-label">Contraseña</label>
          <div class="login-input-group">
            <span class="login-input-icon"><i class="fas fa-lock"></i></span>
            <input
              :type="showPassword ? 'text' : 'password'"
              class="login-input"
              placeholder="••••••••"
              v-model="credentials.password"
              required
              autocomplete="current-password"
            />
            <button
              type="button"
              class="login-input-eye"
              @click="showPassword = !showPassword"
              tabindex="-1"
            >
              <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
            </button>
          </div>
        </div>

        <!-- Remember & Forgot -->
        <div class="login-extras">
          <label class="login-check">
            <input type="checkbox" v-model="rememberMe" />
            <span class="login-check-mark"></span>
            Recordarme
          </label>
          <router-link to="/forgot-password" class="login-link">¿Olvidaste tu contraseña?</router-link>
        </div>

        <!-- Submit -->
        <button type="submit" class="login-btn" :disabled="loading">
          <span v-if="loading" class="login-spinner"></span>
          <span v-if="loading">Iniciando sesión...</span>
          <span v-else>
            <i class="fas fa-right-to-bracket"></i> Iniciar Sesión
          </span>
        </button>
      </form>
    </div>

    <!-- Footer -->
    <p class="login-footer">DGETI &copy; {{ new Date().getFullYear() }} &mdash; Todos los derechos reservados</p>
  </div>
</template>

<script>
import Swal from 'sweetalert2'
import { useAuthStore } from '@/store/auth'

export default {
  name: 'LoginView',
  data() {
    return {
      credentials: { username: '', password: '' },
      rememberMe: false,
      showPassword: false,
      loading: false,
      error: '',
    }
  },
  methods: {
    async handleLogin() {
      this.error = ''
      this.loading = true
      console.log('[SICE Login] Starting login...')
      try {
        await useAuthStore().login(this.credentials)
        console.log('[SICE Login] Auth store login OK, redirecting to /')
        await Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: 'Inicio de sesión exitoso',
          showConfirmButton: false,
          timer: 2000,
          timerProgressBar: true,
        })
        this.$router.push('/')
      } catch (e) {
        console.error('[SICE Login] Error:', e.message, e.response?.status, e.response?.data)
        const msg = e.response?.data?.message || e.message || 'Error al iniciar sesión'
        this.error = msg
      } finally {
        this.loading = false
      }
    },
  },
  mounted() {
    console.log('[SICE Login] Page mounted. VITE_API_URL:', import.meta.env.VITE_API_URL)
  },
}
</script>

<style scoped>
/* ============================================================
   KEYFRAMES
   ============================================================ */
@keyframes login-slide-up {
  from {
    opacity: 0;
    transform: translateY(28px) scale(0.97);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}
@keyframes login-fade-in {
  from { opacity: 0; transform: translateY(-6px); }
  to   { opacity: 1; transform: translateY(0); }
}
@keyframes login-spin {
  to { transform: rotate(360deg); }
}

/* ============================================================
   WRAPPER — fullscreen on all devices, no scroll
   ============================================================ */
.login-wrapper {
  min-height: 100vh;
  min-height: 100dvh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: var(--color-bg, #0A0E14);
  background: linear-gradient(135deg, var(--color-bg, #0A0E14) 0%, var(--color-bg-secondary, #0D1117) 40%, var(--color-bg-tertiary, #161B22) 100%);
  padding: 1rem;
  position: relative;
  overflow: hidden;
}

/* Ambient glow behind the card */
.login-wrapper::before {
  content: '';
  position: fixed;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background:
    radial-gradient(circle at 30% 20%, rgba(46, 155, 255, 0.10) 0%, transparent 45%),
    radial-gradient(circle at 70% 80%, rgba(198, 40, 40, 0.07) 0%, transparent 45%),
    radial-gradient(circle at 50% 50%, rgba(116, 247, 255, 0.04) 0%, transparent 50%);
  pointer-events: none;
  z-index: 0;
}

/* ============================================================
   CARD — glassmorphism
   ============================================================ */
.login-card {
  width: 100%;
  max-width: 380px;
  background: var(--glass-bg, rgba(22, 27, 34, 0.55));
  backdrop-filter: blur(20px) saturate(140%);
  -webkit-backdrop-filter: blur(20px) saturate(140%);
  border: 1px solid var(--glass-border-highlight, rgba(255,255,255,0.14));
  border-radius: 20px;
  box-shadow:
    0 4px 40px rgba(0, 0, 0, 0.35),
    0 0 60px rgba(var(--color-primary-rgb), 0.08),
    inset 0 1px 0 rgba(255, 255, 255, 0.04);
  padding: 1.75rem 1.5rem;
  position: relative;
  z-index: 1;
  animation: login-slide-up 0.5s cubic-bezier(0.22, 0.61, 0.36, 1) both;
}
/* Crystal border glow */
.login-card::before {
  content: '';
  position: absolute;
  inset: -1.5px;
  border-radius: 21px;
  padding: 1.5px;
  background: linear-gradient(135deg, rgba(0,210,255,0.35), rgba(var(--color-primary-rgb),0.25) 50%, rgba(180,76,255,0.3));
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
  pointer-events: none;
  z-index: -1;
}

/* ============================================================
   HEADER
   ============================================================ */
.login-header {
  text-align: center;
  margin-bottom: 1.25rem;
}
.login-logo {
  width: 48px;
  height: 48px;
  clip-path: polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%);
  background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover));
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 0.625rem;
  box-shadow: 0 0 20px rgba(var(--color-primary-rgb),0.4), 0 0 40px rgba(var(--color-primary-rgb),0.15);
  animation: materiaFloat 3s ease-in-out infinite;
}
.login-logo i {
  font-size: 1.375rem;
  color: #fff;
}
.login-title {
  font-size: 1.375rem;
  font-weight: 800;
  color: var(--color-text, #E6EDF3);
  margin: 0 0 0.25rem;
  letter-spacing: -0.03em;
}
.login-subtitle {
  font-size: 0.75rem;
  color: var(--color-text-secondary, #8B949E);
  margin: 0;
  font-weight: 400;
}

/* ============================================================
   ERROR
   ============================================================ */
.login-error {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 0.875rem;
  background: rgba(231, 76, 60, 0.12);
  border: 1px solid rgba(231, 76, 60, 0.25);
  border-radius: 10px;
  color: #F87171;
  font-size: 0.8125rem;
  margin-bottom: 1rem;
  animation: login-fade-in 0.25s ease both;
}
.login-error i {
  font-size: 0.9375rem;
  flex-shrink: 0;
}

/* ============================================================
   FORM
   ============================================================ */
.login-form {
  display: flex;
  flex-direction: column;
}

/* ============================================================
   FIELD
   ============================================================ */
.login-field {
  margin-bottom: 0.875rem;
}
.login-label {
  display: block;
  font-size: 0.6875rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--color-text-secondary, #8B949E);
  margin-bottom: 0.375rem;
}

/* Input group */
.login-input-group {
  position: relative;
  display: flex;
  align-items: center;
}
.login-input-icon {
  position: absolute;
  left: 0.875rem;
  color: var(--color-text-muted, #5A6570);
  font-size: 0.875rem;
  pointer-events: none;
  z-index: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 20px;
  height: 20px;
}
.login-input-eye {
  position: absolute;
  right: 0.25rem;
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: none;
  background: transparent;
  color: var(--color-text-muted, #5A6570);
  font-size: 0.9375rem;
  cursor: pointer;
  border-radius: 8px;
  transition: color 0.15s;
  z-index: 1;
  -webkit-tap-highlight-color: transparent;
}
.login-input-eye:hover { color: var(--color-text-secondary, #8B949E); }
.login-input-eye:active { color: var(--color-primary, #C62828); }

/* Input itself — 48px height */
.login-input {
  display: block;
  width: 100%;
  height: 48px;
  padding: 0 3rem 0 2.5rem;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  font-size: 0.9375rem;
  background: var(--color-bg, #151A22);
  color: var(--color-text, #E6EDF3);
  border: 1.5px solid var(--color-border, rgba(255, 255, 255, 0.1));
  border-radius: 12px;
  outline: none;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
  line-height: 1.4;
}
.login-input::placeholder {
  color: var(--color-text-muted, rgba(255, 255, 255, 0.3));
  font-weight: 400;
}
.login-input:focus {
  border-color: var(--crystal-cyan, rgba(0, 200, 255, 0.5));
  box-shadow: 0 0 0 1px rgba(0,200,255,0.15), 0 0 12px rgba(0,200,255,0.08), inset 0 0 20px rgba(0,180,255,0.03);
  background: var(--color-bg-secondary, #1C222E);
  animation: runeGlow 2.5s ease-in-out infinite;
}
.login-input:-webkit-autofill,
.login-input:-webkit-autofill:hover,
.login-input:-webkit-autofill:focus {
  -webkit-text-fill-color: var(--color-text, #E6EDF3) !important;
  -webkit-box-shadow: 0 0 0 1000px var(--color-bg, #151A22) inset !important;
  caret-color: var(--color-text, #E6EDF3);
}

/* ============================================================
   EXTRAS (remember + forgot)
   ============================================================ */
.login-extras {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
  gap: 0.75rem;
  flex-wrap: wrap;
}
.login-check {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  color: var(--color-text-secondary, #8B949E);
  cursor: pointer;
  user-select: none;
  -webkit-tap-highlight-color: transparent;
}
.login-check input {
  width: 18px;
  height: 18px;
  accent-color: var(--color-primary, #C62828);
  cursor: pointer;
  margin: 0;
  flex-shrink: 0;
}
.login-link {
  font-size: 0.75rem;
  color: var(--color-text-muted, #5A6570);
  text-decoration: none;
  transition: color 0.15s;
  white-space: nowrap;
}
.login-link:hover { color: var(--color-primary, #C62828); }

/* ============================================================
   SUBMIT BUTTON — 52px height
   ============================================================ */
.login-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  width: 100%;
  height: 52px;
  padding: 0 1.5rem;
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  font-size: 0.9375rem;
  font-weight: 700;
  color: #ffffff;
  background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover), var(--color-primary-dark));
  border: none;
  border-radius: 14px;
  cursor: pointer;
  transition: transform 0.12s ease, box-shadow 0.12s ease, filter 0.15s ease;
  box-shadow: 0 4px 16px rgba(var(--color-primary-rgb),0.35);
  -webkit-tap-highlight-color: transparent;
  letter-spacing: 0.01em;
  position: relative;
  overflow: hidden;
}
/* Crystal highlight stripe on top */
.login-btn::before {
  content: '';
  position: absolute; top: 0; left: 5%; right: 5%; height: 1px;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
  opacity: 0.7; pointer-events: none;
}
.login-btn:active:not(:disabled) {
  transform: scale(0.96);
  animation: aetherPulse 0.6s ease-out;
}
.login-btn:hover:not(:disabled) {
  filter: brightness(1.08);
  box-shadow: 0 6px 22px rgba(var(--color-primary-rgb),0.4), var(--rune-glow);
}
.login-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.login-btn i {
  font-size: 0.875rem;
}

/* Spinner */
.login-spinner {
  width: 20px;
  height: 20px;
  border: 2.5px solid rgba(255, 255, 255, 0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: login-spin 0.7s linear infinite;
}

/* ============================================================
   FOOTER
   ============================================================ */
.login-footer {
  text-align: center;
  font-size: 0.6875rem;
  color: var(--color-text-muted, rgba(255, 255, 255, 0.35));
  margin-top: 1.25rem;
  position: relative;
  z-index: 1;
}

/* ============================================================
   RESPONSIVE: slightly larger screens
   ============================================================ */
@media (min-width: 420px) {
  .login-card {
    padding: 2rem 1.75rem;
    border-radius: 22px;
  }
  .login-title { font-size: 1.5rem; }
  .login-logo {
    width: 52px;
    height: 52px;
    border-radius: 15px;
  }
  .login-logo i { font-size: 1.5rem; }
}

/* ============================================================
   RESPONSIVE: desktop
   ============================================================ */
@media (min-width: 768px) {
  .login-card {
    max-width: 400px;
    padding: 2.25rem 2rem;
    border-radius: 24px;
    box-shadow:
      0 8px 60px rgba(0, 0, 0, 0.4),
      inset 0 1px 0 rgba(255, 255, 255, 0.04);
  }
  .login-logo {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    margin-bottom: 0.75rem;
  }
  .login-logo i { font-size: 1.625rem; }
  .login-title { font-size: 1.625rem; }
  .login-subtitle { font-size: 0.8125rem; }
  .login-input { height: 48px; }
  .login-btn { height: 52px; }
  .login-footer { margin-top: 2rem; }
}

/* ============================================================
   RESPONSIVE: large desktop
   ============================================================ */
@media (min-width: 1024px) {
  .login-card { max-width: 420px; }
}

/* ============================================================
   SAFE AREA (notch / dynamic island)
   ============================================================ */
@supports (padding: env(safe-area-inset-top)) {
  .login-wrapper {
    padding-top: calc(1rem + env(safe-area-inset-top));
    padding-bottom: calc(1rem + env(safe-area-inset-bottom));
    padding-left: calc(1rem + env(safe-area-inset-left));
    padding-right: calc(1rem + env(safe-area-inset-right));
  }
}
</style>
