<template>
  <teleport to="body">
    <transition name="search-transition">
      <div
        v-if="visible"
        class="mobile-search-overlay"
        :class="{ closing: isClosing }"
        @click.self="close"
      >
        <!-- Barra de búsqueda -->
        <div class="mobile-search-bar">
          <div class="mobile-search-input-wrapper">
            <i class="fas fa-magnifying-glass"></i>
            <input
              ref="searchInput"
              v-model="query"
              type="text"
              class="mobile-search-input"
              placeholder="Buscar módulo, alumno, docente..."
              autocomplete="off"
              @input="onSearch"
            />
          </div>
          <button class="mobile-search-cancel" @click="close">Cancelar</button>
        </div>

        <!-- Resultados -->
        <div class="mobile-search-results">
          <!-- Estado vacío: sin búsqueda -->
          <div v-if="!query.trim()" class="mobile-search-empty">
            <i class="fas fa-magnifying-glass"></i>
            <p>Busca módulos, alumnos, docentes, grupos...</p>
          </div>

          <!-- Sin resultados -->
          <div v-else-if="results.length === 0 && !searching" class="mobile-search-empty">
            <i class="fas fa-circle-exclamation"></i>
            <p>Sin resultados para "{{ query }}"</p>
          </div>

          <!-- Lista de resultados -->
          <template v-else>
            <a
              v-for="item in results"
              :key="item.id"
              class="mobile-search-result-item"
              :href="item.route || '#'"
              @click.prevent="select(item)"
            >
              <div class="mobile-search-result-icon">
                <i :class="item.icon"></i>
              </div>
              <div class="mobile-search-result-text">
                <div class="mobile-search-result-title">{{ item.title }}</div>
                <div class="mobile-search-result-subtitle">{{ item.subtitle }}</div>
              </div>
              <i class="fas fa-chevron-right" style="color: var(--color-text-muted); font-size: 0.75rem;"></i>
            </a>
          </template>
        </div>
      </div>
    </transition>
  </teleport>
</template>

<script>
import { useAuthStore } from '@/store/auth'

/**
 * Registro estático de rutas buscables.
 * En producción esto podría venir de un endpoint /api/search.
 */
const SEARCH_INDEX = [
  { id: 'dashboard', title: 'Dashboard', subtitle: 'Panel principal', icon: 'fas fa-chart-pie', route: '/', keywords: 'inicio home panel' },
  { id: 'alumnos', title: 'Alumnos', subtitle: 'Registro de alumnos', icon: 'fas fa-user-graduate', route: '/alumnos', keywords: 'estudiantes registro alta' },
  { id: 'alumnos-seg', title: 'Seguimiento Alumnos', subtitle: 'Seguimiento académico', icon: 'fas fa-chart-line', route: '/alumnos/seguimiento', keywords: 'trayectoria historial' },
  { id: 'docentes', title: 'Docentes', subtitle: 'Registro de docentes', icon: 'fas fa-chalkboard-user', route: '/docentes', keywords: 'profesores maestros registro' },
  { id: 'docentes-seg', title: 'Seguimiento Docentes', subtitle: 'Seguimiento de docentes', icon: 'fas fa-chart-line', route: '/docentes/seguimiento', keywords: 'evaluación desempeño' },
  { id: 'grupos', title: 'Grupos', subtitle: 'Gestión de grupos', icon: 'fas fa-users', route: '/grupos', keywords: 'clases aulas' },
  { id: 'especialidades', title: 'Especialidades', subtitle: 'Catálogo de especialidades', icon: 'fas fa-graduation-cap', route: '/especialidades', keywords: 'carreras' },
  { id: 'materias', title: 'Materias', subtitle: 'Catálogo de materias', icon: 'fas fa-book-open', route: '/materias', keywords: 'asignaturas' },
  { id: 'calificaciones', title: 'Calificaciones', subtitle: 'Gestión de calificaciones', icon: 'fas fa-star', route: '/calificaciones', keywords: 'notas evaluación' },
  { id: 'inscripciones', title: 'Inscripciones', subtitle: 'Gestión de inscripciones', icon: 'fas fa-file-signature', route: '/inscripciones', keywords: 'matrícula altas' },
  { id: 'asistencia', title: 'Asistencia', subtitle: 'Control de asistencia', icon: 'fas fa-clipboard-check', route: '/asistencia', keywords: 'pases lista faltas' },
  { id: 'horarios', title: 'Horarios', subtitle: 'Gestión de horarios', icon: 'fas fa-clock', route: '/horarios', keywords: 'clases tiempo' },
  { id: 'tutores', title: 'Tutores', subtitle: 'Gestión de tutores', icon: 'fas fa-user-tie', route: '/tutores', keywords: 'padres familia' },
  { id: 'reportes', title: 'Reportes', subtitle: 'Generación de reportes', icon: 'fas fa-file-pdf', route: '/reportes', keywords: 'informes estadísticas' },
  { id: 'notificaciones', title: 'Notificaciones', subtitle: 'Centro de notificaciones', icon: 'fas fa-bell', route: '/notificaciones', keywords: 'avisos alertas' },
  { id: 'usuarios', title: 'Usuarios', subtitle: 'Administración de usuarios', icon: 'fas fa-users-gear', route: '/usuarios', keywords: 'cuentas roles' },
  { id: 'configuracion', title: 'Configuración', subtitle: 'Configuración del sistema', icon: 'fas fa-gear', route: '/configuracion', keywords: 'ajustes sistema' },
  { id: 'auditoria', title: 'Auditoría', subtitle: 'Registro de actividad', icon: 'fas fa-shield-halved', route: '/auditoria', keywords: 'logs bitácora' },
  { id: 'regularizacion', title: 'Regularización', subtitle: 'Gestión de regularización', icon: 'fas fa-rotate', route: '/regularizacion', keywords: 'recursamiento' },
]

export default {
  name: 'SearchOverlay',
  emits: ['close'],
  props: {
    visible: { type: Boolean, default: false },
  },
  data() {
    return {
      query: '',
      results: [],
      searching: false,
      isClosing: false,
      searchTimeout: null,
    }
  },
  computed: {
    authStore() {
      return useAuthStore()
    },
  },
  watch: {
    visible(val) {
      if (val) {
        this.isClosing = false
        this.$nextTick(() => {
          this.$refs.searchInput?.focus()
        })
      }
    },
  },
  methods: {
    onSearch() {
      clearTimeout(this.searchTimeout)
      const q = this.query.trim().toLowerCase()

      if (!q) {
        this.results = []
        return
      }

      this.searchTimeout = setTimeout(() => {
        const terms = q.split(/\s+/)
        this.results = SEARCH_INDEX.filter(item => {
          // Filtrar por permisos
          if (item.permission && !this.authStore.hasPermission(item.permission)) {
            return false
          }
          const searchText = `${item.title} ${item.subtitle} ${item.keywords}`.toLowerCase()
          return terms.every(term => searchText.includes(term))
        }).slice(0, 12)
      }, 150)
    },
    select(item) {
      if (item.route) {
        this.$router.push(item.route)
      }
      this.close()
    },
    close() {
      this.isClosing = true
      setTimeout(() => {
        this.query = ''
        this.results = []
        this.isClosing = false
        this.$emit('close')
      }, 180)
    },
  },
}
</script>

<style scoped>
.search-transition-enter-active {
  animation: searchFadeIn 0.2s ease;
}
.search-transition-leave-active {
  animation: searchFadeOut 0.18s ease forwards;
}
@keyframes searchFadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
@keyframes searchFadeOut {
  from { opacity: 1; }
  to { opacity: 0; }
}
</style>
