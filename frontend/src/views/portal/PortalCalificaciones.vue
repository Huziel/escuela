<template>
  <div class="portal-page">
    <div class="page-header">
      <h1 class="page-title"><i class="fas fa-star"></i> Mis Calificaciones</h1>
      <select v-model="filtroPeriodo" class="field-select-sm" @change="load">
        <option value="">Todos los periodos</option>
        <option v-for="p in periodos" :key="p" :value="p">{{ p }}</option>
      </select>
    </div>

    <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>

    <template v-else-if="calificaciones.length">
      <!-- Average bar -->
      <div class="promedio-bar">
        <div class="promedio-big">{{ promedioDisplay }}</div>
        <div class="promedio-label">Promedio General</div>
        <div class="promedio-sub">{{ calificaciones.filter(c => c.promedio_final).length }} materia(s)</div>
      </div>

      <!-- Grouped by periodo like AlumnoDetail -->
      <div v-for="(materias, periodo) in groupedByPeriodo" :key="periodo" class="semester-block mb-3">
        <h6 class="semester-title">
          <i class="fas fa-layer-group"></i> {{ periodo }}
          <span class="badge badge-neutral ms-2">{{ materias.length }} materia(s)</span>
        </h6>
        <div class="cal-cards">
          <div v-for="cal in materias" :key="cal.id" class="cal-card-item">
            <div class="cal-card-header">
              <span class="cal-materia-name">{{ cal.materia || '-' }}</span>
              <span :class="['badge', (cal.promedio_final || 0) >= 7 ? 'badge-success' : cal.promedio_final ? 'badge-danger' : 'badge-neutral']">
                {{ cal.promedio_final || '-' }}
              </span>
            </div>
            <div class="cal-parciales">
              <div class="cal-parcial">
                <span class="cal-parcial-label">P1</span>
                <span class="cal-parcial-value">{{ cal.parcial1 ?? '-' }}</span>
                <span class="cal-asistencia">Asist: {{ cal.asistencia_p1 ?? '-' }}%</span>
              </div>
              <div class="cal-parcial">
                <span class="cal-parcial-label">P2</span>
                <span class="cal-parcial-value">{{ cal.parcial2 ?? '-' }}</span>
                <span class="cal-asistencia">Asist: {{ cal.asistencia_p2 ?? '-' }}%</span>
              </div>
              <div class="cal-parcial">
                <span class="cal-parcial-label">P3</span>
                <span class="cal-parcial-value">{{ cal.parcial3 ?? '-' }}</span>
                <span class="cal-asistencia">Asist: {{ cal.asistencia_p3 ?? '-' }}%</span>
              </div>
            </div>
            <div class="cal-card-footer">
              <span :class="['badge', estatusBadge(cal.estatus)]">{{ cal.estatus || '-' }}</span>
            </div>
          </div>
        </div>
      </div>
    </template>

    <p v-else class="text-center py-5 text-muted">Sin calificaciones registradas.</p>
  </div>
</template>

<script>
import PortalAlumnoService from '@/services/PortalAlumnoService'
export default {
  name: 'PortalCalificaciones',
  data() { return { loading: true, calificaciones: [], periodos: [], filtroPeriodo: '' } },
  computed: {
    groupedByPeriodo() {
      const groups = {}
      this.calificaciones.forEach(c => {
        const p = c.periodo || 'Sin periodo'
        if (!groups[p]) groups[p] = []
        groups[p].push(c)
      })
      return groups
    },
    promedioDisplay() {
      const withGrade = this.calificaciones.filter(c => c.promedio_final != null && c.promedio_final > 0)
      if (!withGrade.length) return '—'
      return (withGrade.reduce((s, c) => s + c.promedio_final, 0) / withGrade.length).toFixed(1)
    },
  },
  async mounted() { await this.load() },
  methods: {
    async load() {
      this.loading = true
      try {
        const { data } = await PortalAlumnoService.getCalificaciones({ periodo: this.filtroPeriodo || undefined })
        const d = data.data || data
        this.periodos = d.periodos || []
        // Flatten from grouped format
        if (d.calificaciones && typeof d.calificaciones === 'object' && !Array.isArray(d.calificaciones)) {
          this.calificaciones = Object.values(d.calificaciones).flat()
        } else {
          this.calificaciones = d.calificaciones || []
        }
      } catch (e) { console.error(e) }
      this.loading = false
    },
    estatusBadge(s) {
      if (s === 'aprobada') return 'badge-success'
      if (s === 'reprobada') return 'badge-danger'
      return 'badge-neutral'
    },
  }
}
</script>

<style scoped>
.portal-page { max-width: 900px; margin: 0 auto; }
.field-select-sm { padding: .375rem .75rem; border: 1.5px solid var(--color-border); border-radius: 8px; font-size: .8125rem; background: var(--color-bg); color: var(--color-text); }
.promedio-bar { text-align: center; padding: 1.25rem; background: var(--glass-bg, rgba(255,255,255,.03)); border: 1px solid var(--glass-border, rgba(255,255,255,.06)); border-radius: 14px; margin-bottom: 1.25rem; }
.promedio-big { font-size: 2.5rem; font-weight: 800; color: var(--color-primary); }
.promedio-label { font-size: .75rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: .05em; }
.promedio-sub { font-size: .6875rem; color: var(--color-text-muted); margin-top: .125rem; }

/* Replicating AlumnoDetail calificaciones design */
.semester-block { background: var(--glass-bg, rgba(255,255,255,.02)); border: 1px solid var(--glass-border, rgba(255,255,255,.06)); border-radius: 14px; padding: 1.25rem; }
.semester-title { font-size: .875rem; font-weight: 700; color: var(--color-text); margin-bottom: .875rem; display: flex; align-items: center; gap: .5rem; }
.cal-cards { display: flex; flex-direction: column; gap: .5rem; }
.cal-card-item { background: var(--color-bg-secondary, rgba(255,255,255,.03)); border-radius: 10px; padding: .75rem 1rem; border: 1px solid var(--color-border-light, rgba(255,255,255,.04)); }
.cal-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: .625rem; }
.cal-materia-name { font-size: .875rem; font-weight: 600; color: var(--color-text); }
.cal-parciales { display: flex; gap: 1rem; margin-bottom: .5rem; }
.cal-parcial { flex: 1; text-align: center; padding: .375rem; background: var(--color-bg, rgba(0,0,0,.1)); border-radius: 8px; }
.cal-parcial-label { display: block; font-size: .625rem; color: var(--color-text-muted); text-transform: uppercase; margin-bottom: .125rem; }
.cal-parcial-value { display: block; font-size: 1rem; font-weight: 700; color: var(--color-text); }
.cal-asistencia { display: block; font-size: .625rem; color: var(--color-text-muted); margin-top: .125rem; }
.cal-card-footer { display: flex; justify-content: flex-end; }

.badge-neutral { background: var(--color-bg-tertiary); color: var(--color-text-muted); font-size: .6875rem; padding: .15rem .5rem; border-radius: 6px; }
.badge-success { background: rgba(16,185,129,.15); color: #10B981; font-size: .6875rem; padding: .15rem .5rem; border-radius: 6px; }
.badge-danger { background: rgba(239,68,68,.15); color: #EF4444; font-size: .6875rem; padding: .15rem .5rem; border-radius: 6px; }

@media (max-width: 600px) {
  .cal-parciales { flex-direction: column; gap: .375rem; }
  .promedio-big { font-size: 2rem; }
}
</style>
