<template>
  <div class="portal-page">
    <div class="page-header">
      <h1 class="page-title"><i class="fas fa-file-pdf"></i> Boleta</h1>
      <button class="btn btn-sm btn-primary" @click="window.print()"><i class="fas fa-print"></i> Imprimir</button>
    </div>
    <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
    <template v-else>
      <div class="boleta-card" id="boleta-print">
        <div class="boleta-header">
          <h3>CETIS No. 1</h3>
          <p>Boleta de Calificaciones</p>
        </div>
        <div class="boleta-info">
          <div><strong>Alumno:</strong> {{ boleta?.alumno?.nombre }}</div>
          <div><strong>Matrícula:</strong> {{ boleta?.alumno?.matricula }}</div>
          <div><strong>Grupo:</strong> {{ boleta?.alumno?.grupo }}</div>
          <div><strong>Especialidad:</strong> {{ boleta?.alumno?.especialidad }}</div>
          <div><strong>Periodo:</strong> {{ boleta?.periodo }}</div>
        </div>
        <table class="boleta-table">
          <thead><tr><th>Materia</th><th>P1</th><th>P2</th><th>P3</th><th>Prom</th><th>Asist</th><th>Estatus</th></tr></thead>
          <tbody>
            <tr v-for="m in boleta?.materias" :key="m.materia">
              <td>{{ m.materia }}</td>
              <td>{{ m.parcial1 ?? '—' }}</td><td>{{ m.parcial2 ?? '—' }}</td><td>{{ m.parcial3 ?? '—' }}</td>
              <td class="fw-bold">{{ m.promedio_final ?? '—' }}</td>
              <td>{{ Math.round(((m.asistencia_p1||0)+(m.asistencia_p2||0)+(m.asistencia_p3||0))/3) }}%</td>
              <td><span :class="m.estatus === 'aprobada' ? 'text-success' : 'text-danger'">{{ m.estatus }}</span></td>
            </tr>
          </tbody>
        </table>
        <div class="boleta-footer">
          <div>Promedio General: <strong>{{ boleta?.promedio_general }}</strong></div>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import PortalAlumnoService from '@/services/PortalAlumnoService'
export default {
  name: 'PortalBoleta',
  data() { return { loading: true, boleta: null } },
  async mounted() {
    try { const { data } = await PortalAlumnoService.getBoleta(); this.boleta = data.data || data } catch (e) { console.error(e) }
    this.loading = false
  }
}
</script>

<style scoped>
.portal-page { max-width: 800px; margin: 0 auto; }
.boleta-card { background: #fff; color: #111; border-radius: 8px; padding: 2rem; }
.boleta-header { text-align: center; margin-bottom: 1.5rem; }
.boleta-header h3 { margin: 0; font-size: 1.25rem; color: #111; }
.boleta-header p { margin: .125rem 0 0; font-size: .8125rem; color: #555; }
.boleta-info { display: flex; flex-wrap: wrap; gap: .5rem 2rem; margin-bottom: 1.25rem; font-size: .8125rem; border-bottom: 2px solid #eee; padding-bottom: 1rem; }
.boleta-table { width: 100%; font-size: .75rem; border-collapse: collapse; }
.boleta-table th, .boleta-table td { padding: .375rem .5rem; border: 1px solid #ddd; text-align: center; }
.boleta-table th { background: #f5f5f5; font-weight: 600; }
.boleta-table td:first-child { text-align: left; }
.boleta-footer { margin-top: 1.25rem; text-align: right; font-size: .875rem; border-top: 2px solid #eee; padding-top: .75rem; }
@media print {
  .page-header, .btn { display: none !important; }
  .boleta-card { padding: 1cm; }
  body { background: #fff !important; }
}
</style>
