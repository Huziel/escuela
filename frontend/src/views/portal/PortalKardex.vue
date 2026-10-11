<template>
  <div class="portal-page">
    <div class="page-header"><h1 class="page-title"><i class="fas fa-file-alt"></i> Mi Kardex</h1></div>
    <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
    <template v-else-if="kardex">
      <div class="stats-row">
        <div class="mini-stat"><span class="mini-val">{{ stats?.promedio_global || 0 }}</span><span class="mini-lbl">Promedio Global</span></div>
        <div class="mini-stat"><span class="mini-val">{{ stats?.total_materias || 0 }}</span><span class="mini-lbl">Materias</span></div>
        <div class="mini-stat text-success"><span class="mini-val">{{ stats?.aprobadas || 0 }}</span><span class="mini-lbl">Aprobadas</span></div>
        <div class="mini-stat text-danger"><span class="mini-val">{{ stats?.reprobadas || 0 }}</span><span class="mini-lbl">Reprobadas</span></div>
      </div>
      <div v-for="p in historial" :key="p.periodo" class="kardex-periodo mt-3">
        <h6 class="section-label">{{ p.periodo }} <span class="text-muted small">Prom: {{ p.promedio_periodo }}</span></h6>
        <div class="kardex-table-wrap">
          <table class="kardex-table">
            <thead><tr><th>Materia</th><th>Grupo</th><th>P1</th><th>P2</th><th>P3</th><th>Prom</th><th>Estatus</th></tr></thead>
            <tbody>
              <tr v-for="m in p.materias" :key="m.materia">
                <td>{{ m.materia }}</td><td>{{ m.grupo }}</td>
                <td>{{ m.parcial1 ?? '—' }}</td><td>{{ m.parcial2 ?? '—' }}</td><td>{{ m.parcial3 ?? '—' }}</td>
                <td class="fw-bold">{{ m.promedio_final ?? '—' }}</td>
                <td><span :class="m.estatus === 'aprobada' ? 'text-success' : 'text-danger'">{{ m.estatus }}</span></td>
              </tr>
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
  name: 'PortalKardex',
  data() { return { loading: true, stats: null, historial: [] } },
  async mounted() {
    try { const { data } = await PortalAlumnoService.getKardex(); const d = data.data || data; this.stats = d.estadisticas; this.historial = d.historial || [] } catch (e) { console.error(e) }
    this.loading = false
  }
}
</script>

<style scoped>
.portal-page { max-width: 900px; margin: 0 auto; }
.stats-row { display: flex; gap: 1rem; margin-bottom: 1rem; }
.mini-stat { flex: 1; text-align: center; padding: .75rem; background: var(--glass-bg, rgba(255,255,255,.03)); border: 1px solid var(--glass-border, rgba(255,255,255,.06)); border-radius: 10px; }
.mini-val { display: block; font-size: 1.5rem; font-weight: 700; color: var(--color-text); }
.mini-lbl { font-size: .6875rem; color: var(--color-text-muted); text-transform: uppercase; }
.kardex-periodo { background: var(--glass-bg, rgba(255,255,255,.02)); border: 1px solid var(--glass-border, rgba(255,255,255,.06)); border-radius: 12px; padding: 1rem; }
.kardex-table-wrap { overflow-x: auto; }
.kardex-table { width: 100%; font-size: .75rem; color: var(--color-text); }
.kardex-table th, .kardex-table td { padding: .375rem .5rem; border-bottom: 1px solid var(--color-border-light); text-align: left; }
.kardex-table th { font-weight: 600; color: var(--color-text-muted); font-size: .6875rem; text-transform: uppercase; }
.text-success { color: #10B981 !important; font-weight: 600; }
.text-danger { color: #EF4444 !important; font-weight: 600; }
@media (max-width: 600px) { .mini-stat { padding: .5rem; } .mini-val { font-size: 1.25rem; } }
</style>
