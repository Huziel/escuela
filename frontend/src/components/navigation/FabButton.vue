<template>
  <button
    v-if="fabConfig"
    class="mobile-fab"
    :style="{ background: fabConfig.color || undefined }"
    @click="handleClick"
    :aria-label="fabConfig.label"
  >
    <i :class="fabConfig.icon"></i>
  </button>
</template>

<script>
/**
 * Floating Action Button — context-aware primary action.
 *
 * Changes icon, color, and action depending on the current route.
 * Falls back to a generic "quick create" menu if no specific action matches.
 */
const FAB_CONFIG = {
  '/alumnos': {
    icon: 'fas fa-user-plus',
    label: 'Nuevo alumno',
    route: '/alumnos',
    action: 'create',
  },
  '/docentes': {
    icon: 'fas fa-user-plus',
    label: 'Nuevo docente',
    route: '/docentes',
    action: 'create',
  },
  '/grupos': {
    icon: 'fas fa-plus',
    label: 'Nuevo grupo',
    route: '/grupos',
    action: 'create',
  },
  '/calificaciones': {
    icon: 'fas fa-star',
    label: 'Registrar calificaciones',
    route: '/calificaciones',
    action: 'create',
  },
  '/especialidades': {
    icon: 'fas fa-plus',
    label: 'Nueva especialidad',
    route: '/especialidades',
    action: 'create',
  },
  '/materias': {
    icon: 'fas fa-plus',
    label: 'Nueva materia',
    route: '/materias',
    action: 'create',
  },
  '/inscripciones': {
    icon: 'fas fa-file-signature',
    label: 'Nueva inscripción',
    route: '/inscripciones',
    action: 'create',
  },
  '/asistencia': {
    icon: 'fas fa-clipboard-check',
    label: 'Registrar asistencia',
    route: '/asistencia',
    action: 'create',
  },
  '/horarios': {
    icon: 'fas fa-plus',
    label: 'Nuevo horario',
    route: '/horarios',
    action: 'create',
  },
  '/tutores': {
    icon: 'fas fa-user-plus',
    label: 'Nuevo tutor',
    route: '/tutores',
    action: 'create',
  },
}

export default {
  name: 'FabButton',
  emits: ['action'],
  computed: {
    fabConfig() {
      // Buscar coincidencia exacta primero
      if (FAB_CONFIG[this.$route.path]) {
        return FAB_CONFIG[this.$route.path]
      }
      // Luego coincidencia por prefijo
      for (const [route, config] of Object.entries(FAB_CONFIG)) {
        if (route !== '/' && this.$route.path.startsWith(route)) {
          return config
        }
      }
      // Dashboard: FAB como acceso rápido a alumnos (la entidad principal)
      if (this.$route.path === '/') {
        return {
          icon: 'fas fa-user-plus',
          label: 'Nuevo alumno',
          route: '/alumnos',
          action: 'navigate',
          color: '#C62828',
        }
      }
      // No FAB en rutas que no tienen acción principal (detalle, reportes, etc.)
      return null
    },
  },
  methods: {
    handleClick() {
      if (!this.fabConfig) return

      if (this.fabConfig.action === 'create') {
        // Emitir evento para que la vista padre abra el modal/formulario
        this.$emit('action', {
          type: 'create',
          route: this.fabConfig.route,
        })
      } else if (this.fabConfig.action === 'navigate') {
        this.$router.push(this.fabConfig.route)
      }
    },
  },
}
</script>

<style scoped>
/* FAB styles are defined globally in mobile-shell.css */
</style>
