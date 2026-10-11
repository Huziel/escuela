<template>
  <nav class="mobile-bottomnav" aria-label="Navegación principal">
    <button
      v-for="item in navItems"
      :key="item.id"
      class="bottomnav-item"
      :class="{
        active: isActive(item.route),
        'bottomnav-more': item.id === 'more',
      }"
      @click="item.id === 'more' ? $emit('open-more') : navigate(item.route)"
      :aria-label="item.label"
      :aria-current="isActive(item.route) ? 'page' : undefined"
    >
      <i :class="item.icon"></i>
      <span class="bottomnav-label">{{ item.label }}</span>
    </button>
  </nav>
</template>

<script>
import { useAuthStore } from '@/store/auth'

export default {
  name: 'BottomNav',
  emits: ['open-more'],
  data() {
    return {
      lastTap: 0,
    }
  },
  computed: {
    authStore() {
      return useAuthStore()
    },
    navItems() {
      const items = [
        {
          id: 'dashboard',
          label: 'Inicio',
          icon: 'fas fa-chart-pie',
          route: '/',
        },
        {
          id: 'alumnos',
          label: 'Alumnos',
          icon: 'fas fa-user-graduate',
          route: '/alumnos',
          permission: 'alumnos.listar',
        },
        {
          id: 'calificaciones',
          label: 'Notas',
          icon: 'fas fa-star',
          route: '/calificaciones',
          permission: 'calificaciones.listar',
        },
        {
          id: 'grupos',
          label: 'Grupos',
          icon: 'fas fa-users',
          route: '/grupos',
          permission: 'grupos.listar',
        },
      ]

      // Filtrar items que requieren permiso
      const filtered = items.filter(item => {
        if (!item.permission) return true
        return this.authStore.hasPermission(item.permission)
      })

      // Agregar "Más" siempre al final
      filtered.push({
        id: 'more',
        label: 'Más',
        icon: 'fas fa-ellipsis-h',
        route: null,
      })

      return filtered
    },
  },
  methods: {
    isActive(route) {
      if (!route) return false
      if (route === '/') return this.$route.path === '/'
      return this.$route.path.startsWith(route)
    },
    navigate(route) {
      if (route && this.$route.path !== route) {
        this.$router.push(route)
      }
    },
  },
}
</script>

<style scoped>
/* All styles are in mobile-shell.css for consistency.
   Scoped styles here handle only component-specific behavior. */
</style>
