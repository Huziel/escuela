<template>
  <div>
    <div class="page-header">
      <div class="page-title">
        <i class="fas fa-chart-simple"></i>Reportes
      </div>
    </div>

    <ul class="nav nav-tabs mb-4">
      <li class="nav-item">
        <button class="nav-link" :class="{ active: activeTab === 'indicadores' }" @click="activeTab = 'indicadores'">
          <i class="fas fa-chart-pie me-1"></i>Indicadores
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" :class="{ active: activeTab === 'kardex' }" @click="activeTab = 'kardex'">
          <i class="fas fa-file-lines me-1"></i>Kardex
        </button>
      </li>
      <li class="nav-item">
        <button class="nav-link" :class="{ active: activeTab === 'boleta' }" @click="activeTab = 'boleta'">
          <i class="fas fa-graduation-cap me-1"></i>Boleta
        </button>
      </li>
    </ul>

    <div v-if="activeTab === 'indicadores'">
      <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-4 col-sm-6" v-for="(card, i) in indicadores" :key="i">
          <div class="stat-card hover-lift animate-fade-in-up">
            <div>
              <div class="stat-value" :style="{ color: card.color || '#1e3c72' }">{{ card.valor }}</div>
              <div class="stat-label">{{ card.label }}</div>
            </div>
            <div class="stat-icon" :style="{ background: (card.color || '#1e3c72') + '20', color: card.color || '#1e3c72' }">
              <i class="fas fa-chart-simple"></i>
            </div>
          </div>
        </div>
        <div v-if="!indicadores.length" class="col-12">
          <div class="table-empty">
            <i class="fas fa-chart-column"></i>
            <p>No hay indicadores disponibles.</p>
          </div>
        </div>
      </div>
    </div>

    <div v-if="activeTab === 'kardex'">
      <div class="card">
        <div class="card-header">
          <i class="fas fa-file-lines"></i>Kardex del Alumno
        </div>
        <div class="card-body">
          <div class="row g-3 align-items-end mb-3">
            <div class="col-md-5">
              <label class="form-label">Alumno</label>
              <select class="form-select" v-model="kardex.alumnoId">
                <option value="">-- Seleccionar Alumno --</option>
                <option v-for="a in alumnos" :key="a.id" :value="a.id">{{ a.nombre }} {{ a.apellido_paterno }} {{ a.apellido_materno }}</option>
              </select>
            </div>
            <div class="col-md-3">
              <button class="btn btn-primary" @click="generarKardex" :disabled="!kardex.alumnoId || kardex.loading">
                <span v-if="kardex.loading" class="spinner-border spinner-border-sm me-1"></span>
                Generar
              </button>
            </div>
            <div class="col-md-4 text-md-end">
              <button v-if="kardex.data.length" class="btn btn-outline-primary" @click="descargarKardexPDF">
                <i class="fas fa-file-pdf me-1"></i>Descargar PDF
              </button>
            </div>
          </div>

          <div v-if="kardex.data.length" class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Ciclo</th>
                  <th>Materia</th>
                  <th>Calificacion</th>
                  <th>Docente</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(row, i) in kardex.data" :key="i">
                  <td><span class="badge badge-neutral">{{ row.ciclo || row.ciclo_nombre || '-' }}</span></td>
                  <td>{{ row.materia || row.materia_nombre || '-' }}</td>
                  <td><span class="badge" :class="calificacionClass(row.calificacion)">{{ row.calificacion || '-' }}</span></td>
                  <td>{{ row.docente || row.docente_nombre || '-' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else-if="kardex.searched" class="table-empty">
            <i class="fas fa-file-lines"></i>
            <p>No se encontraron registros para el alumno seleccionado.</p>
          </div>
        </div>
      </div>
    </div>

    <div v-if="activeTab === 'boleta'">
      <div class="card">
        <div class="card-header">
          <i class="fas fa-graduation-cap"></i>Generar Boleta
        </div>
        <div class="card-body">
          <div class="row g-3 align-items-end mb-3">
            <div class="col-md-4">
              <label class="form-label">Alumno</label>
              <select class="form-select" v-model="boleta.alumnoId">
                <option value="">-- Seleccionar Alumno --</option>
                <option v-for="a in alumnos" :key="a.id" :value="a.id">{{ a.nombre }} {{ a.apellido_paterno }} {{ a.apellido_materno }}</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Periodo</label>
              <select class="form-select" v-model="boleta.periodoId">
                <option value="">-- Seleccionar Periodo --</option>
                <option v-for="p in periodos" :key="p.id" :value="p.id">{{ p.nombre || p.periodo }}</option>
              </select>
            </div>
            <div class="col-md-4">
              <button class="btn btn-outline-primary" @click="generarBoleta" :disabled="!boleta.alumnoId || !boleta.periodoId || boleta.loading">
                <span v-if="boleta.loading" class="spinner-border spinner-border-sm me-1"></span>
                <i class="fas fa-file-pdf me-1"></i>Generar PDF
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/api/axios'
import Swal from 'sweetalert2'

export default {
  name: 'ReportesView',
  data() {
    return {
      activeTab: 'indicadores',
      alumnos: [],
      periodos: [],
      indicadores: [],
      kardex: {
        alumnoId: '',
        data: [],
        loading: false,
        searched: false,
      },
      boleta: {
        alumnoId: '',
        periodoId: '',
        loading: false,
      },
    }
  },
  methods: {
    async fetchAlumnos() {
      try {
        const { data } = await api.get('/alumnos')
        this.alumnos = data.data || data || []
      } catch (e) {
        console.error('Error al cargar alumnos', e)
      }
    },
    async fetchPeriodos() {
      try {
        const { data } = await api.get('/configuracion/ciclos')
        this.periodos = data.data || data || []
      } catch (e) {
        console.error('Error al cargar periodos', e)
      }
    },
    async fetchIndicadores() {
      try {
        const { data } = await api.get('/reportes/indicadores')
        this.indicadores = data.data || data || []
      } catch (e) {
        console.error('Error al cargar indicadores', e)
      }
    },
    async generarKardex() {
      if (!this.kardex.alumnoId) return
      this.kardex.loading = true
      this.kardex.searched = false
      try {
        const { data } = await api.get(`/reportes/kardex?alumno_id=${this.kardex.alumnoId}`)
        this.kardex.data = data.data || data || []
        this.kardex.searched = true
      } catch (e) {
        Swal.fire('Error', 'No se pudo generar el kardex.', 'error')
      } finally {
        this.kardex.loading = false
      }
    },
    descargarKardexPDF() {
      const alumnoId = this.kardex.alumnoId
      window.open(`${api.defaults.baseURL}/reportes/kardex/pdf?alumno_id=${alumnoId}`, '_blank')
    },
    async generarBoleta() {
      if (!this.boleta.alumnoId || !this.boleta.periodoId) return
      this.boleta.loading = true
      const alumnoId = this.boleta.alumnoId
      const periodoId = this.boleta.periodoId
      window.open(`${api.defaults.baseURL}/reportes/boleta/pdf?alumno_id=${alumnoId}&periodo_id=${periodoId}`, '_blank')
      this.boleta.loading = false
    },
    calificacionClass(cal) {
      const num = parseFloat(cal)
      if (isNaN(num)) return 'badge-neutral'
      if (num >= 7) return 'badge-success'
      return 'badge-danger'
    },
  },
  mounted() {
    this.fetchAlumnos()
    this.fetchPeriodos()
    this.fetchIndicadores()
  },
}
</script>
