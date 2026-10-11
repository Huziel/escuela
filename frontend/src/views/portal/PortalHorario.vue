<template>
  <div class="portal-page">
    <div class="page-header">
      <h1 class="page-title"><i class="fas fa-clock"></i> Mi Horario</h1>
      <span class="badge badge-secondary">{{ grupo }} · {{ turno }}</span>
    </div>
    <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
    <template v-else>
      <div class="schedule-grid">
        <div v-for="(bloques, dia) in horario" :key="dia" class="schedule-day">
          <div class="schedule-day-header">{{ dia }}</div>
          <div v-if="bloques.length" class="schedule-blocks">
            <div v-for="h in bloques" :key="h.id" class="schedule-block" :style="{background: colorFor(h.materia)}">
              <span class="block-materia">{{ h.materia || '—' }}</span>
              <span class="block-hora">{{ h.hora_inicio }} – {{ h.hora_fin }}</span>
              <span class="block-docente">{{ h.docente || '—' }}</span>
              <span class="block-aula" v-if="h.aula">{{ h.aula }}</span>
            </div>
          </div>
          <div v-else class="schedule-empty">Sin clase</div>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import PortalAlumnoService from '@/services/PortalAlumnoService'
export default {
  name: 'PortalHorario',
  data() { return { loading: true, horario: {}, grupo: '', turno: '' } },
  async mounted() {
    try { const { data } = await PortalAlumnoService.getHorario(); const d = data.data || data; this.horario = d.horario || {}; this.grupo = d.grupo; this.turno = d.turno } catch (e) { console.error(e) }
    this.loading = false
  },
  methods: {
    colorFor(name) { const colors = ['#C62828','#1565C0','#2E7D32','#F57F17','#6A1B9A','#00838F','#BF360C','#4E342E']; let hash = 0; if (name) for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash); return colors[Math.abs(hash || 0) % colors.length] }
  }
}
</script>

<style scoped>
.portal-page { max-width: 900px; margin: 0 auto; }
.schedule-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: .5rem; }
.schedule-day { background: var(--glass-bg, rgba(255,255,255,.02)); border: 1px solid var(--glass-border, rgba(255,255,255,.06)); border-radius: 10px; overflow: hidden; }
.schedule-day-header { padding: .5rem; text-align: center; font-size: .75rem; font-weight: 700; text-transform: capitalize; color: var(--color-text); background: var(--color-bg-secondary, rgba(255,255,255,.04)); }
.schedule-blocks { display: flex; flex-direction: column; gap: .375rem; padding: .5rem; }
.schedule-block { padding: .5rem; border-radius: 8px; color: #fff; font-size: .6875rem; display: flex; flex-direction: column; gap: .125rem; }
.block-materia { font-weight: 600; }
.block-hora { opacity: .85; }
.block-docente { font-size: .625rem; opacity: .7; }
.block-aula { font-size: .625rem; opacity: .7; }
.schedule-empty { padding: 1rem .5rem; text-align: center; color: var(--color-text-muted); font-size: .75rem; }
@media (max-width: 768px) {
  .schedule-grid { grid-template-columns: 1fr; }
  .schedule-day { display: flex; }
  .schedule-day-header { width: 80px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
  .schedule-blocks { flex: 1; flex-direction: row; flex-wrap: wrap; }
  .schedule-block { flex: 1; min-width: 120px; }
}
</style>
