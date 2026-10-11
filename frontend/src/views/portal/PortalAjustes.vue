<template>
  <div class="ajustes-page">
    <h2 class="ajustes-title"><i class="fas fa-gear"></i> Ajustes</h2>

    <!-- Theme -->
    <div class="ajustes-card">
      <h6 class="ajustes-card-title">Tema</h6>
      <div class="theme-toggle">
        <button :class="['theme-option', { active: studentTheme === 'dark' }]" @click="setTheme('dark')">
          <i class="fas fa-moon"></i><span>Oscuro</span>
        </button>
        <button :class="['theme-option', { active: studentTheme === 'light' }]" @click="setTheme('light')">
          <i class="fas fa-sun"></i><span>Claro</span>
        </button>
      </div>
    </div>

    <!-- Change Password -->
    <div class="ajustes-card">
      <h6 class="ajustes-card-title">Cambiar Contraseña</h6>
      <div class="pass-form" v-if="!passSuccess">
        <div class="pass-field">
          <label>Contraseña actual</label>
          <input v-model="passForm.current" type="password" class="pass-input" placeholder="••••••••" />
        </div>
        <div class="pass-field">
          <label>Nueva contraseña</label>
          <input v-model="passForm.new" type="password" class="pass-input" placeholder="Mínimo 8 caracteres" />
        </div>
        <div class="pass-field">
          <label>Confirmar nueva</label>
          <input v-model="passForm.confirm" type="password" class="pass-input" placeholder="Repite la contraseña" />
        </div>
        <p v-if="passError" class="pass-error">{{ passError }}</p>
        <button class="btn btn-primary w-100" @click="changePassword" :disabled="passSaving">
          <i v-if="passSaving" class="fas fa-circle-notch fa-spin"></i>
          <i v-else class="fas fa-lock"></i>
          Actualizar contraseña
        </button>
      </div>
      <div v-else class="pass-success">
        <i class="fas fa-check-circle"></i>
        <p>Contraseña actualizada correctamente.</p>
      </div>
    </div>

    <!-- Logout -->
    <button class="btn btn-outline-danger w-100 mt-3" @click="logout">
      <i class="fas fa-right-from-bracket"></i> Cerrar sesión
    </button>
  </div>
</template>

<script>
import api from '@/api/axios'
import Swal from 'sweetalert2'
import { useAuthStore } from '@/store/auth'

export default {
  name: 'PortalAjustes',
  data() {
    return {
      studentTheme: localStorage.getItem('sice-student-theme') || 'dark',
      passForm: { current: '', new: '', confirm: '' },
      passError: '', passSaving: false, passSuccess: false,
    }
  },
  methods: {
    setTheme(t) {
      this.studentTheme = t
      const userId = useAuthStore().user?.id
      if (userId) localStorage.setItem('sice-theme-' + userId, t)
      localStorage.setItem('sice-student-theme', t)
      document.documentElement.setAttribute('data-theme-type', t === 'light' ? 'light' : 'dark')
      if (t === 'light') {
        document.documentElement.style.setProperty('--color-bg', '#F8FAFC')
        document.documentElement.style.setProperty('--color-text', '#1E293B')
        document.documentElement.style.setProperty('--color-text-muted', '#64748B')
        document.documentElement.style.setProperty('--glass-bg', 'rgba(255,255,255,0.8)')
        document.documentElement.style.setProperty('--glass-border', 'rgba(0,0,0,0.08)')
        document.documentElement.style.setProperty('--color-bg-secondary', '#F1F5F9')
        document.documentElement.style.setProperty('--color-border', 'rgba(0,0,0,0.1)')
        document.documentElement.style.setProperty('--color-border-light', 'rgba(0,0,0,0.05)')
      } else {
        document.documentElement.removeAttribute('data-theme-type')
        ;['--color-bg','--color-text','--color-text-muted','--glass-bg','--glass-border','--color-bg-secondary','--color-border','--color-border-light'].forEach(v => document.documentElement.style.removeProperty(v))
      }
    },
    async changePassword() {
      this.passError = ''
      if (!this.passForm.current || !this.passForm.new) {
        this.passError = 'Todos los campos son requeridos.'
        return
      }
      if (this.passForm.new.length < 8) {
        this.passError = 'La nueva contraseña debe tener al menos 8 caracteres.'
        return
      }
      if (this.passForm.new !== this.passForm.confirm) {
        this.passError = 'Las contraseñas no coinciden.'
        return
      }
      this.passSaving = true
      try {
        await api.post('/portal-alumno/change-password', {
          current_password: this.passForm.current,
          password: this.passForm.new,
          password_confirmation: this.passForm.confirm,
        })
        this.passSuccess = true; this.passForm = { current: '', new: '', confirm: '' }
      } catch (e) {
        this.passError = e.response?.data?.message || 'Error al cambiar la contraseña.'
      }
      this.passSaving = false
    },
    async logout() {
      const result = await Swal.fire({
        title: 'Cerrar sesión', text: '¿Estás seguro?', icon: 'question',
        showCancelButton: true, confirmButtonText: 'Salir', cancelButtonText: 'Cancelar',
      })
      if (result.isConfirmed) {
        try { await api.post('/logout') } catch (e) {}
        localStorage.removeItem('token')
        window.location.href = '/login'
      }
    },
  },
}
</script>

<style scoped>
.ajustes-page { max-width: 480px; }
.ajustes-title { font-size: 1.25rem; font-weight: 700; margin-bottom: 1.25rem; color: var(--color-text); }
.ajustes-card {
  background: var(--glass-bg, rgba(255,255,255,0.03)); border: 1px solid var(--glass-border, rgba(255,255,255,0.06));
  border-radius: 14px; padding: 1.25rem; margin-bottom: 1rem;
}
.ajustes-card-title { font-size: .8125rem; font-weight: 600; color: var(--color-text); margin-bottom: .875rem; }
.theme-toggle { display: flex; gap: .5rem; }
.theme-option {
  flex: 1; display: flex; flex-direction: column; align-items: center; gap: .375rem;
  padding: .75rem; border: 2px solid var(--color-border, rgba(255,255,255,0.08)); border-radius: 12px;
  background: transparent; color: var(--color-text-muted); cursor: pointer; transition: all .15s;
  font-size: .75rem; font-weight: 500;
}
.theme-option i { font-size: 1.25rem; }
.theme-option.active { border-color: var(--color-primary); color: var(--color-text); background: var(--color-bg-secondary); }
.theme-option:active { transform: scale(0.97); }
.pass-form { display: flex; flex-direction: column; gap: .75rem; }
.pass-field { display: flex; flex-direction: column; gap: .25rem; }
.pass-field label { font-size: .6875rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: .04em; }
.pass-input {
  padding: .625rem .75rem; border: 1.5px solid var(--color-border); border-radius: 10px;
  font-size: .875rem; background: var(--color-bg); color: var(--color-text); outline: none;
}
.pass-input:focus { border-color: var(--color-primary); }
.pass-error { font-size: .75rem; color: var(--color-danger); margin: 0; }
.pass-success { text-align: center; padding: 1rem; }
.pass-success i { font-size: 2rem; color: #10B981; margin-bottom: .5rem; display: block; }
.pass-success p { font-size: .875rem; color: var(--color-text); margin: 0; }
.btn { padding: .625rem 1rem; border-radius: 10px; font-size: .875rem; font-weight: 600; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: .5rem; }
.btn-primary { background: var(--color-primary); color: #fff; }
.btn-primary:disabled { opacity: .6; }
.btn-outline-danger { background: transparent; border: 1.5px solid rgba(239,68,68,0.3); color: #EF4444; }
.w-100 { width: 100%; }
.mt-3 { margin-top: 1rem; }
</style>
