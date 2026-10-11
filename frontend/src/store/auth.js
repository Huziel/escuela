import { defineStore } from 'pinia'
import AuthService from '@/services/AuthService'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token && !!state.user,

    userPermissions: (state) => {
      if (!state.user || !state.user.roles) return []
      const perms = new Set()
      state.user.roles.forEach((role) => {
        if (role.permissions) {
          role.permissions.forEach((p) => perms.add(p))
        }
      })
      return [...perms]
    },

    userRole: (state) => {
      if (!state.user || !state.user.roles || !state.user.roles.length) return null
      return state.user.roles[0].name
    },

    nombreCompleto: (state) => {
      if (!state.user) return ''
      return state.user.nombre_completo || state.user.name || ''
    },
  },

  actions: {
    async login(credentials) {
      console.log('[SICE Auth] Login attempt:', { username: credentials.username })
      const response = await AuthService.login(credentials)
      console.log('[SICE Auth] Login success:', { user: response.data.data.user?.username, hasToken: !!response.data.data.token, hasAlumno: !!response.data.data.user?.alumno })
      this.token = response.data.data.token
      this.user = response.data.data.user
      localStorage.setItem('token', this.token)
      return response
    },

    async fetchUser() {
      const response = await AuthService.fetchUser()
      this.user = response.data.data
      return response
    },

    async logout() {
      try {
        await AuthService.logout()
      } catch {
        // ignore logout errors
      } finally {
        this.user = null
        this.token = null
        localStorage.removeItem('token')
      }
    },

    async refreshToken() {
      const response = await AuthService.refreshToken()
      this.token = response.data.data.token
      localStorage.setItem('token', this.token)
      return response
    },

    hasPermission(perm) {
      if (!this.user) return false
      const roles = this.user.roles || []
      const isSuperAdmin = roles.some((r) => r.name === 'super_admin')
      if (isSuperAdmin) return true
      return this.userPermissions.includes(perm)
    },

    hasRole(role) {
      if (!this.user || !this.user.roles) return false
      return this.user.roles.some((r) => r.name === role)
    },
  },
})
