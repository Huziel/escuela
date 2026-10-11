<template>
  <div class="portal-page">
    <div class="page-header"><h1 class="page-title"><i class="fas fa-calendar-check"></i> Mis Asistencias</h1></div>
    <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
    <template v-else>
      <div class="att-bar mb-3">
        <div class="att-ring"><span class="att-ring-val">{{ porcentaje }}%</span><span class="att-ring-lbl">Asistencia</span></div>
        <div class="att-detail"><span>{{ total }} registros</span><span class="text-danger">{{ faltas }} faltas</span></div>
      </div>
      <div class="section-card">
        <div class="table-responsive">
          <table class="att-table">
            <thead><tr><th>Fecha</th><th>Materia</th><th>Estatus</th></tr></thead>
            <tbody>
              <tr v-for="a in asistencias" :key="a.id">
                <td>{{ a.fecha }}</td>
                <td>{{ a.materia?.nombre || '—' }}</td>
                <td><span class="att-badge" :class="'att-' + (a.estatus || '')">{{ a.estatus }}</span></td>
              </tr>
              <tr v-if="!asistencias.length"><td colspan="3" class="text-center text-muted py-3">Sin registros</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import PortalAlumnoService from '@/services/PortalAlumnoService'
export default {
  name: 'PortalAsistencias',
  data() { return { loading: true, porcentaje: 100, total: 0, faltas: 0, asistencias: [] } },
  async mounted() {
    try { const { data } = await PortalAlumnoService.getAsistencias(); const d = data.data || data; this.porcentaje = d.porcentaje; this.total = d.total; this.faltas = d.faltas; this.asistencias = d.asistencias?.data || d.asistencias || [] } catch (e) { console.error(e) }
    this.loading = false
  }
}
</script>

<style scoped>
.portal-page { max-width: 800px; margin: 0 auto; }
.att-bar { display: flex; align-items: center; gap: 1.5rem; padding: 1.25rem; background: var(--glass-bg, rgba(255,255,255,.03)); border: 1px solid var(--glass-border, rgba(255,255,255,.06)); border-radius: 14px; }
.att-ring { text-align: center; }
.att-ring-val { font-size: 2rem; font-weight: 800; color: var(--color-primary); }
.att-ring-lbl { display: block; font-size: .6875rem; color: var(--color-text-muted); }
.att-detail { display: flex; flex-direction: column; gap: .25rem; font-size: .875rem; color: var(--color-text); }
.section-card { padding: 1rem; background: var(--glass-bg, rgba(255,255,255,.02)); border: 1px solid var(--glass-border, rgba(255,255,255,.06)); border-radius: 12px; }
.att-table { width: 100%; font-size: .8125rem; color: var(--color-text); }
.att-table th, .att-table td { padding: .5rem .75rem; border-bottom: 1px solid var(--color-border-light); }
.att-table th { font-weight: 600; color: var(--color-text-muted); font-size: .6875rem; text-transform: uppercase; }
.att-badge { padding: .125rem .5rem; border-radius: 20px; font-size: .6875rem; font-weight: 600; }
.att-presente { background: rgba(16,185,129,.15); color: #10B981; }
.att-falta { background: rgba(239,68,68,.15); color: #EF4444; }
.att-justificada { background: rgba(245,158,11,.15); color: #F59E0B; }
.att-retardo { background: rgba(139,92,246,.15); color: #8B5CF6; }
</style>
