<template>
  <div class="cal-page">
    <!-- ==================== HEADER ==================== -->
    <div class="module-header">
      <div class="header-info">
        <div class="header-icon"><i class="fas fa-star"></i></div>
        <div>
          <h1 class="header-title">Calificaciones</h1>
          <p class="header-subtitle">Gestión de calificaciones por grupo</p>
        </div>
      </div>
      <button class="btn btn-sm btn-secondary" @click="$refs.bulkUpload?.show()">
        <i class="fas fa-file-import"></i> Importar
      </button>
    </div>

    <!-- ==================== FILTER TOGGLE (MOBILE) ==================== -->
    <button
      class="filter-toggle"
      type="button"
      @click="showFilters = !showFilters"
      :aria-expanded="showFilters"
    >
      <i class="fas fa-sliders"></i>
      <span>Filtros</span>
      <span v-if="activeFilterCount" class="filter-count-badge">{{ activeFilterCount }}</span>
      <i :class="showFilters ? 'fas fa-chevron-up' : 'fas fa-chevron-down'" class="filter-toggle-arrow"></i>
    </button>

    <!-- ==================== FILTER BAR ==================== -->
    <div class="filter-bar" :class="{ 'filter-bar--open': showFilters }">
      <div class="filter-item">
        <SearchSelect v-model="filtroCiclo" :options="cicloOptions" placeholder="Ciclo Escolar" @update:model-value="onCicloChange" />
      </div>
      <div class="filter-item">
        <SearchSelect v-model="filtroSemestre" :options="semestreOptions" placeholder="Semestre" @update:model-value="onSemestreChange" />
      </div>
      <div class="filter-item">
        <SearchSelect v-model="filtroEspecialidad" :options="especialidadOptions" placeholder="Especialidad" @update:model-value="onEspecialidadChange" />
      </div>
      <div class="filter-item">
        <SearchSelect v-model="filtroGrupo" :options="grupoOptions" placeholder="Grupo" @update:model-value="onGrupoChange" />
      </div>
      <div class="filter-item">
        <SearchSelect v-model="filtroMateria" :options="materiaOptions" placeholder="Materia" @update:model-value="fetchCalificaciones" />
      </div>
    </div>

    <!-- ==================== EMPTY STATE ==================== -->
    <div v-if="!hasAnyFilter" class="empty-state">
      <div class="empty-illustration"><i class="fas fa-star"></i></div>
      <h3>Selecciona los filtros</h3>
      <p>Elige ciclo, semestre, especialidad o grupo para ver los alumnos</p>
    </div>

    <!-- ==================== LOADING ==================== -->
    <div v-else-if="loading" class="text-center py-5">
      <div class="loading-pulse"></div>
    </div>

    <!-- ==================== ALUMNOS (sin materia) ==================== -->
    <template v-else-if="!filtroMateria">
      <!-- DESKTOP table -->
      <div class="desktop-only">
        <div class="card">
          <div class="card-header"><i class="fas fa-users"></i> Alumnos ({{ alumnos.length }})</div>
          <div class="card-body p-0">
            <div v-if="!alumnos.length" class="p-4 text-center text-muted">Sin alumnos con estos filtros</div>
            <div v-else class="table-responsive">
              <table class="data-table">
                <thead>
                  <tr><th>Matrícula</th><th>Nombre</th><th>Grupo</th><th>Especialidad</th><th>Semestre</th><th>Ciclo</th></tr>
                </thead>
                <tbody>
                  <tr v-for="a in alumnos" :key="a.id">
                    <td data-label="Matrícula"><span class="fw-semibold">{{ a.matricula }}</span></td>
                    <td data-label="Nombre">{{ a.usuario?.nombre_completo || a.user?.nombre_completo || '-' }}</td>
                    <td data-label="Grupo">{{ a.grupo?.nombre || '-' }}</td>
                    <td data-label="Especialidad">{{ a.especialidad?.nombre || '-' }}</td>
                    <td data-label="Semestre">{{ a.semestre }}°</td>
                    <td data-label="Ciclo">{{ a.ciclo_escolar || '-' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- MOBILE cards -->
      <div class="mobile-only">
        <div v-if="!alumnos.length" class="empty-inline">Sin alumnos con estos filtros</div>
        <div v-else class="alumno-cards">
          <div v-for="a in alumnos" :key="a.id" class="alumno-card">
            <div class="alumno-card__avatar">
              <i class="fas fa-user-graduate"></i>
            </div>
            <div class="alumno-card__info">
              <div class="alumno-card__name">{{ a.usuario?.nombre_completo || a.user?.nombre_completo || '-' }}</div>
              <div class="alumno-card__meta">
                <span class="alumno-card__matricula">{{ a.matricula }}</span>
                <span v-if="a.grupo?.nombre" class="alumno-card__separator">·</span>
                <span v-if="a.grupo?.nombre">{{ a.grupo.nombre }}</span>
                <span v-if="a.especialidad?.nombre" class="alumno-card__separator">·</span>
                <span v-if="a.especialidad?.nombre">{{ a.especialidad.nombre }}</span>
              </div>
            </div>
            <div class="alumno-card__badges">
              <span class="alumno-chip">{{ a.semestre }}°</span>
              <span v-if="a.ciclo_escolar" class="alumno-chip alumno-chip--cycle">{{ a.ciclo_escolar }}</span>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- ==================== CALIFICACIONES (con materia) ==================== -->
    <template v-else>
      <!-- DESKTOP table -->
      <div class="desktop-only">
        <div class="card">
          <div class="card-header">
            <i class="fas fa-star"></i> {{ materiaNombre }} — {{ grupoNombre }}
            <button class="btn btn-primary btn-sm ms-auto" @click="guardarCalificaciones" :disabled="saving">
              <i v-if="saving" class="fas fa-circle-notch fa-spin"></i>
              <i v-else class="fas fa-floppy-disk"></i> Guardar
            </button>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive" v-if="calificaciones.length">
              <table class="data-table">
                <thead>
                  <tr><th>Alumno</th><th>P1</th><th>A1</th><th>P2</th><th>A2</th><th>P3</th><th>A3</th><th>Prom</th><th>Asist</th><th>Estatus</th></tr>
                </thead>
                <tbody>
                  <tr v-for="c in calificaciones" :key="c.id">
                    <td data-label="Alumno">
                      <div class="fw-semibold">{{ c.alumno?.nombre_completo || '-' }}</div>
                      <small class="text-muted">{{ c.alumno?.matricula }}</small>
                    </td>
                    <td data-label="P1"><input v-model="c.parcial1" type="number" min="5" max="10" class="grade-input" :class="{invalid:isInvalidGrade(c.parcial1)}" @change="clampGrade(c,'parcial1')" @input="calcPromedio(c)" /></td>
                    <td data-label="A1"><input v-model="c.asistencia_p1" type="number" min="0" max="100" class="grade-input grade-att" :class="{invalid:isInvalidAtt(c.asistencia_p1)}" @change="clampAtt(c,'asistencia_p1')" /></td>
                    <td data-label="P2"><input v-model="c.parcial2" type="number" min="5" max="10" class="grade-input" :class="{invalid:isInvalidGrade(c.parcial2)}" @change="clampGrade(c,'parcial2')" @input="calcPromedio(c)" /></td>
                    <td data-label="A2"><input v-model="c.asistencia_p2" type="number" min="0" max="100" class="grade-input grade-att" :class="{invalid:isInvalidAtt(c.asistencia_p2)}" @change="clampAtt(c,'asistencia_p2')" /></td>
                    <td data-label="P3"><input v-model="c.parcial3" type="number" min="5" max="10" class="grade-input" :class="{invalid:isInvalidGrade(c.parcial3)}" @change="clampGrade(c,'parcial3')" @input="calcPromedio(c)" /></td>
                    <td data-label="A3"><input v-model="c.asistencia_p3" type="number" min="0" max="100" class="grade-input grade-att" :class="{invalid:isInvalidAtt(c.asistencia_p3)}" @change="clampAtt(c,'asistencia_p3')" /></td>
                    <td data-label="Prom"><span :class="['badge',promedioColor(c)]">{{ c.promedio_final || '-' }}</span></td>
                    <td data-label="Asist"><span :class="['badge',asistenciaColor(c)]">{{ asistenciaPromedio(c) }}</span></td>
                    <td data-label="Estatus"><SearchSelect v-model="c.estatus" :options="estatusOptions" /></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-else class="p-4 text-center text-muted">Sin alumnos en este grupo</div>
          </div>
        </div>
      </div>

      <!-- MOBILE cards -->
      <div class="mobile-only">
        <div class="mobile-section-header">
          <h2 class="mobile-section-title">{{ materiaNombre }}</h2>
          <p class="mobile-section-subtitle">{{ grupoNombre }}</p>
        </div>

        <div v-if="!calificaciones.length" class="empty-inline">Sin alumnos en este grupo</div>

        <div v-else class="grade-cards">
          <div v-for="c in calificaciones" :key="c.id" class="grade-card">
            <!-- Card header: student info -->
            <div class="grade-card__header">
              <span class="grade-card__student-name">{{ c.alumno?.nombre_completo || '-' }}</span>
              <span class="grade-card__matricula">{{ c.alumno?.matricula || '' }}</span>
            </div>

            <!-- Card body: 3 parciales -->
            <div class="grade-card__body">
              <!-- Parcial 1 -->
              <div class="parcial-row">
                <span class="parcial-label">Parcial 1</span>
                <div class="input-pair">
                  <div class="input-group">
                    <input
                      v-model="c.parcial1"
                      type="number"
                      min="5"
                      max="10"
                      step="0.1"
                      inputmode="decimal"
                      class="grade-input-mobile"
                      :class="{ invalid: isInvalidGrade(c.parcial1) }"
                      placeholder="Cal."
                      @change="clampGrade(c, 'parcial1')"
                      @input="calcPromedio(c)"
                    />
                    <span class="input-suffix">/10</span>
                  </div>
                  <div class="input-group">
                    <input
                      v-model="c.asistencia_p1"
                      type="number"
                      min="0"
                      max="100"
                      inputmode="numeric"
                      class="grade-input-mobile grade-input-mobile--att"
                      :class="{ invalid: isInvalidAtt(c.asistencia_p1) }"
                      placeholder="Asist."
                      @change="clampAtt(c, 'asistencia_p1')"
                    />
                    <span class="input-suffix">%</span>
                  </div>
                </div>
              </div>

              <!-- Parcial 2 -->
              <div class="parcial-row">
                <span class="parcial-label">Parcial 2</span>
                <div class="input-pair">
                  <div class="input-group">
                    <input
                      v-model="c.parcial2"
                      type="number"
                      min="5"
                      max="10"
                      step="0.1"
                      inputmode="decimal"
                      class="grade-input-mobile"
                      :class="{ invalid: isInvalidGrade(c.parcial2) }"
                      placeholder="Cal."
                      @change="clampGrade(c, 'parcial2')"
                      @input="calcPromedio(c)"
                    />
                    <span class="input-suffix">/10</span>
                  </div>
                  <div class="input-group">
                    <input
                      v-model="c.asistencia_p2"
                      type="number"
                      min="0"
                      max="100"
                      inputmode="numeric"
                      class="grade-input-mobile grade-input-mobile--att"
                      :class="{ invalid: isInvalidAtt(c.asistencia_p2) }"
                      placeholder="Asist."
                      @change="clampAtt(c, 'asistencia_p2')"
                    />
                    <span class="input-suffix">%</span>
                  </div>
                </div>
              </div>

              <!-- Parcial 3 -->
              <div class="parcial-row">
                <span class="parcial-label">Parcial 3</span>
                <div class="input-pair">
                  <div class="input-group">
                    <input
                      v-model="c.parcial3"
                      type="number"
                      min="5"
                      max="10"
                      step="0.1"
                      inputmode="decimal"
                      class="grade-input-mobile"
                      :class="{ invalid: isInvalidGrade(c.parcial3) }"
                      placeholder="Cal."
                      @change="clampGrade(c, 'parcial3')"
                      @input="calcPromedio(c)"
                    />
                    <span class="input-suffix">/10</span>
                  </div>
                  <div class="input-group">
                    <input
                      v-model="c.asistencia_p3"
                      type="number"
                      min="0"
                      max="100"
                      inputmode="numeric"
                      class="grade-input-mobile grade-input-mobile--att"
                      :class="{ invalid: isInvalidAtt(c.asistencia_p3) }"
                      placeholder="Asist."
                      @change="clampAtt(c, 'asistencia_p3')"
                    />
                    <span class="input-suffix">%</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Card footer: badges + estatus -->
            <div class="grade-card__footer">
              <div class="grade-card__badges">
                <span :class="['badge-mobile', promedioColor(c)]">
                  <i class="fas fa-calculator"></i> {{ c.promedio_final || '-' }}
                </span>
                <span :class="['badge-mobile', asistenciaColor(c)]">
                  <i class="fas fa-calendar-check"></i> {{ asistenciaPromedio(c) }}
                </span>
              </div>
              <div class="grade-card__status">
                <SearchSelect v-model="c.estatus" :options="estatusOptions" />
              </div>
            </div>
          </div>
        </div>

        <!-- Sticky save bar (mobile only) -->
        <div v-if="calificaciones.length" class="save-bar">
          <div class="save-bar__info">
            <span class="save-bar__count">{{ calificaciones.length }} alumno{{ calificaciones.length !== 1 ? 's' : '' }}</span>
            <span class="save-bar__hint">Verifica antes de guardar</span>
          </div>
          <button
            class="save-bar__btn"
            @click="guardarCalificaciones"
            :disabled="saving"
          >
            <i v-if="saving" class="fas fa-circle-notch fa-spin"></i>
            <i v-else class="fas fa-floppy-disk"></i>
            Guardar
          </button>
        </div>
      </div>
    </template>
    <BulkUploadModal ref="bulkUpload" type="calificaciones" title="Calificaciones" templateLabel="Descargar plantilla Excel" @imported="fetchCalificaciones" />
  </div>
</template>

<script>
import api from '@/api/axios'
import Swal from 'sweetalert2'
import SearchSelect from '@/components/ui/SearchSelect.vue'
import BulkUploadModal from '@/components/ui/BulkUploadModal.vue'

export default {
  name: 'CalificacionesList',
  components: { SearchSelect, BulkUploadModal },
  data() {
    return {
      calificaciones: [],
      alumnos: [],
      ciclos: [],
      especialidades: [],
      gruposFiltrados: [],
      materiasFiltradas: [],
      filtroCiclo: '',
      filtroSemestre: '',
      filtroEspecialidad: '',
      filtroGrupo: '',
      filtroMateria: '',
      loading: false,
      saving: false,
      showFilters: true, // mobile filter panel (open by default so user sees filters immediately)
    }
  },
  computed: {
    hasAnyFilter() {
      return this.filtroCiclo || this.filtroSemestre || this.filtroEspecialidad || this.filtroGrupo
    },
    grupoNombre() {
      const g = this.gruposFiltrados.find(x => x.id == this.filtroGrupo)
      return g?.nombre || ''
    },
    materiaNombre() {
      const m = this.materiasFiltradas.find(x => x.id == this.filtroMateria)
      return m?.nombre || ''
    },
    cicloOptions() {
      return [{ value: '', label: 'Ciclo Escolar' }, ...this.ciclos.map(c => ({ value: c.nombre, label: c.nombre }))]
    },
    semestreOptions() {
      let semestres = [1, 2, 3, 4, 5, 6]
      if (this.filtroCiclo) {
        const parte = this.filtroCiclo.split('-')[1]
        if (parte === '1') semestres = [1, 3, 5]
        else if (parte === '2') semestres = [2, 4, 6]
      }
      return [{ value: '', label: 'Semestre' }, ...semestres.map(s => ({ value: s, label: s + '°' }))]
    },
    especialidadOptions() {
      return [{ value: '', label: 'Especialidad' }, ...this.especialidades.map(e => ({ value: e.id, label: e.nombre }))]
    },
    grupoOptions() {
      return [{ value: '', label: 'Grupo' }, ...this.gruposFiltrados.map(g => ({ value: g.id, label: g.nombre }))]
    },
    materiaOptions() {
      return [{ value: '', label: 'Materia' }, ...this.materiasFiltradas.map(m => ({ value: m.id, label: m.nombre }))]
    },
    estatusOptions() {
      return [
        { value: 'cursando', label: 'Cursando' },
        { value: 'aprobada', label: 'Aprobada' },
        { value: 'reprobada', label: 'Reprobada' },
      ]
    },
    activeFilterCount() {
      let count = 0
      if (this.filtroCiclo) count++
      if (this.filtroSemestre) count++
      if (this.filtroEspecialidad) count++
      if (this.filtroGrupo) count++
      if (this.filtroMateria) count++
      return count
    },
  },
  mounted() {
    this.fetchCiclos()
    this.fetchEspecialidades()
  },
  methods: {
    async fetchCiclos() {
      try {
        const { data } = await api.get('/configuracion/ciclos')
        this.ciclos = data.data || data || []
      } catch (e) {}
    },
    async fetchEspecialidades() {
      try {
        const { data } = await api.get('/especialidades?per_page=100')
        this.especialidades = data.data || data || []
      } catch (e) {}
    },
    async onCicloChange() {
      this.filtroSemestre = ''
      this.filtroEspecialidad = ''
      this.filtroGrupo = ''
      this.filtroMateria = ''
      this.gruposFiltrados = []
      this.materiasFiltradas = []
      this.calificaciones = []
      this.fetchAlumnos()
    },
    async onSemestreChange() {
      this.filtroEspecialidad = ''
      this.filtroGrupo = ''
      this.filtroMateria = ''
      this.gruposFiltrados = []
      this.materiasFiltradas = []
      this.calificaciones = []
      if (this.filtroSemestre && !this.filtroCiclo) {
        const s = Number(this.filtroSemestre)
        const year = new Date().getFullYear()
        this.filtroCiclo = (s % 2 === 1) ? (year + '-1') : (year + '-2')
      }
      this.fetchAlumnos()
    },
    async onEspecialidadChange() {
      this.filtroGrupo = ''
      this.filtroMateria = ''
      this.materiasFiltradas = []
      this.calificaciones = []
      if (!this.filtroEspecialidad) {
        this.gruposFiltrados = []
        this.fetchAlumnos()
        return
      }
      const params = { especialidad_id: this.filtroEspecialidad, per_page: 100 }
      if (this.filtroSemestre) params.semestre = this.filtroSemestre
      try {
        const { data } = await api.get('/grupos', { params })
        this.gruposFiltrados = data.data || data || []
      } catch (e) {}
      this.fetchAlumnos()
    },
    async onGrupoChange() {
      this.filtroMateria = ''
      this.calificaciones = []
      this.fetchAlumnos()
      if (!this.filtroGrupo) return
      try {
        const grupo = this.gruposFiltrados.find(g => g.id == this.filtroGrupo)
        const materiasMap = new Map()

        // Materias del horario
        const { data: hData } = await api.get('/horarios', { params: { grupo_id: this.filtroGrupo, per_page: 50 } })
        const horarios = hData.data || hData || []
        horarios.forEach(h => {
          if (h.materia_id && !materiasMap.has(h.materia_id)) {
            materiasMap.set(h.materia_id, {
              id: h.materia_id,
              nombre: h.materia?.nombre || h.materia_nombre || ('Materia ' + h.materia_id),
            })
          }
        })

        // Materias del plan de estudio de la especialidad del grupo
        if (grupo?.especialidad_id) {
          try {
            const { data: pData } = await api.get('/planes-estudio', { params: { especialidad_id: grupo.especialidad_id, per_page: 10 } })
            const planes = pData.data || pData || []
            for (const plan of planes) {
              if (plan.materias) {
                plan.materias.forEach(m => {
                  if (!materiasMap.has(m.id)) {
                    materiasMap.set(m.id, { id: m.id, nombre: m.nombre })
                  }
                })
              }
            }
          } catch (e) {}
        }

        this.materiasFiltradas = Array.from(materiasMap.values())
      } catch (e) {
        this.materiasFiltradas = []
      }
    },
    async fetchCalificaciones() {
      if (!this.filtroGrupo || !this.filtroMateria) return
      this.loading = true
      try {
        await api.post('/calificaciones/sincronizar-grupo/' + this.filtroGrupo + '/materia/' + this.filtroMateria)
        const { data } = await api.get('/calificaciones/grupo/' + this.filtroGrupo + '/materia/' + this.filtroMateria)
        this.calificaciones = data.data || data || []
      } catch (e) {
        this.calificaciones = []
      }
      this.loading = false
    },
    async fetchAlumnos() {
      if (!this.hasAnyFilter) return
      this.loading = true
      const params = { per_page: 200 }
      if (this.filtroCiclo) params.ciclo_escolar = this.filtroCiclo
      if (this.filtroSemestre) params.semestre = this.filtroSemestre
      if (this.filtroEspecialidad) params.especialidad_id = this.filtroEspecialidad
      if (this.filtroGrupo) params.grupo_id = this.filtroGrupo
      try {
        const { data } = await api.get('/alumnos', { params })
        this.alumnos = data.data || data || []
      } catch (e) {
        this.alumnos = []
      }
      this.loading = false
    },
    calcPromedio(c) {
      const vals = [c.parcial1, c.parcial2, c.parcial3]
        .map(x => (x === '' || x === null || x === undefined ? null : Number(x)))
        .filter(x => x !== null)
      if (vals.length === 3) {
        c.promedio_final = Math.round(((vals[0] + vals[1] + vals[2]) / 3) * 10) / 10
      } else if (vals.length === 0) {
        c.promedio_final = null
      }
    },
    clampGrade(c, field) {
      const v = c[field]
      if (v !== '' && v !== null && v !== undefined) {
        const n = Number(v)
        if (n < 5) c[field] = 5
        if (n > 10) c[field] = 10
      }
    },
    clampAtt(c, field) {
      const v = c[field]
      if (v !== '' && v !== null && v !== undefined) {
        const n = Number(v)
        if (n < 0) c[field] = 0
        if (n > 100) c[field] = 100
      }
    },
    isInvalidGrade(v) {
      if (v === '' || v === null || v === undefined) return false
      const n = Number(v)
      return isNaN(n) || n < 5 || n > 10
    },
    isInvalidAtt(v) {
      if (v === '' || v === null || v === undefined) return false
      const n = Number(v)
      return isNaN(n) || n < 0 || n > 100
    },
    promedioColor(c) {
      const v = c.promedio_final
      if (!v) return 'badge-neutral'
      return v >= 7 ? 'badge-success' : 'badge-danger'
    },
    asistenciaPromedio(c) {
      const a = [c.asistencia_p1, c.asistencia_p2, c.asistencia_p3]
        .map(x => (x === '' || x === null || x === undefined ? null : Number(x)))
        .filter(x => x !== null)
      if (!a.length) return '-'
      return Math.round(a.reduce((s, x) => s + x, 0) / a.length) + '%'
    },
    asistenciaColor(c) {
      const v = parseInt(this.asistenciaPromedio(c))
      if (isNaN(v)) return 'badge-neutral'
      return v >= 80 ? 'badge-success' : v >= 60 ? 'badge-warning' : 'badge-danger'
    },
    async guardarCalificaciones() {
      const result = await Swal.fire({
        title: '¿Guardar todas las calificaciones?',
        text: `Se guardarán ${this.calificaciones.length} registros.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
      })
      if (!result.isConfirmed) return
      this.saving = true
      const payload = this.calificaciones.map(c => ({
        id: c.id,
        parcial1: c.parcial1 === '' ? null : Number(c.parcial1),
        parcial2: c.parcial2 === '' ? null : Number(c.parcial2),
        parcial3: c.parcial3 === '' ? null : Number(c.parcial3),
        asistencia_p1: c.asistencia_p1 === '' ? null : Number(c.asistencia_p1),
        asistencia_p2: c.asistencia_p2 === '' ? null : Number(c.asistencia_p2),
        asistencia_p3: c.asistencia_p3 === '' ? null : Number(c.asistencia_p3),
        estatus: c.estatus,
      }))
      try {
        const { data } = await api.post('/calificaciones/guardar-lote', { calificaciones: payload })
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: (data.data?.actualizadas || payload.length) + ' calificaciones guardadas',
          showConfirmButton: false,
          timer: 2000,
          timerProgressBar: true,
        })
        this.fetchCalificaciones()
      } catch (e) {
        Swal.fire('Error', 'No se pudo guardar.', 'error')
      }
      this.saving = false
    },
  },
}
</script>

<style scoped>
/* ================================================================
   MOBILE-FIRST (base styles for < 1024px)
   ================================================================ */

/* --- Page Wrapper --- */
.cal-page {
  padding-bottom: 100px; /* space for sticky save bar */
}

/* --- Module Header --- */
.module-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 1.25rem;
  gap: 1rem;
}
.header-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}
.header-icon {
  width: 44px;
  height: 44px;
  border-radius: 13px;
  background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover));
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 1.2rem;
  box-shadow: 0 4px 12px rgba(169, 30, 34, 0.25);
  flex-shrink: 0;
}
.header-title {
  font-size: 1.35rem;
  font-weight: 700;
  margin: 0;
  line-height: 1.2;
}
.header-subtitle {
  font-size: 0.8125rem;
  color: var(--color-text-secondary);
  margin: 2px 0 0;
}

/* --- Filter Toggle Button (mobile only) --- */
.filter-toggle {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  width: 100%;
  padding: 0.7rem 1rem;
  background: var(--color-bg);
  border: 1.5px solid var(--color-border);
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--color-text);
  cursor: pointer;
  transition: border-color 0.2s, box-shadow 0.2s;
  user-select: none;
  -webkit-tap-highlight-color: transparent;
}
.filter-toggle:active {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(169, 30, 34, 0.1);
}
.filter-toggle i:first-child {
  color: var(--color-primary);
  font-size: 0.9375rem;
}
.filter-toggle-arrow {
  margin-left: auto;
  font-size: 0.75rem;
  color: var(--color-text-muted);
  transition: transform 0.25s ease;
}
.filter-count-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 22px;
  height: 22px;
  padding: 0 6px;
  border-radius: 11px;
  background: var(--color-primary);
  color: #fff;
  font-size: 0.6875rem;
  font-weight: 700;
  line-height: 1;
}

/* --- Filter Bar --- */
.filter-bar {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  max-height: 0;
  overflow: hidden;
  opacity: 0;
  transition: max-height 0.35s ease, opacity 0.25s ease, margin-top 0.35s ease;
  margin-top: 0;
}
.filter-bar--open {
  max-height: 600px;
  opacity: 1;
  margin-top: 0.75rem;
}
.filter-item {
  width: 100%;
}

/* --- Empty State --- */
.empty-state {
  text-align: center;
  padding: 4rem 1.5rem;
}
.empty-illustration {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: var(--color-bg-secondary);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.25rem;
  font-size: 2rem;
  color: var(--color-text-muted);
}
.empty-state h3 {
  font-size: 1.15rem;
  font-weight: 600;
  margin: 0 0 0.375rem;
}
.empty-state p {
  font-size: 0.875rem;
  color: var(--color-text-secondary);
  max-width: 320px;
  margin: 0 auto;
}

/* --- Loading Pulse --- */
.loading-pulse {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  border: 3px solid var(--color-border);
  border-top-color: var(--color-primary);
  animation: spin 0.7s linear infinite;
  margin: 0 auto;
}
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* --- Empty Inline --- */
.empty-inline {
  text-align: center;
  padding: 2.5rem 1rem;
  color: var(--color-text-muted);
  font-size: 0.875rem;
}

/* ================================================================
   ALUMNO CARDS (mobile — no materia selected)
   ================================================================ */
.alumno-cards {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}
.alumno-card {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1rem;
  background: var(--color-bg);
  border: 1px solid var(--color-border-light);
  border-radius: 12px;
  transition: box-shadow 0.15s;
}
.alumno-card:active {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}
.alumno-card__avatar {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  background: var(--color-bg-tertiary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  color: var(--color-text-muted);
  flex-shrink: 0;
}
.alumno-card__info {
  flex: 1;
  min-width: 0;
}
.alumno-card__name {
  font-weight: 600;
  font-size: 0.875rem;
  color: var(--color-text);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}
.alumno-card__meta {
  font-size: 0.75rem;
  color: var(--color-text-secondary);
  display: flex;
  gap: 0.25rem;
  flex-wrap: wrap;
  margin-top: 0.125rem;
}
.alumno-card__matricula {
  font-weight: 500;
  color: var(--color-primary);
}
.alumno-card__separator {
  color: var(--color-border);
  user-select: none;
}
.alumno-card__badges {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.25rem;
  flex-shrink: 0;
}
.alumno-chip {
  display: inline-block;
  padding: 0.2rem 0.5rem;
  border-radius: 6px;
  font-size: 0.6875rem;
  font-weight: 700;
  background: var(--color-bg-tertiary);
  color: var(--color-text-secondary);
  white-space: nowrap;
}
.alumno-chip--cycle {
  background: rgba(169, 30, 34, 0.08);
  color: var(--color-primary);
}

/* ================================================================
   MOBILE SECTION HEADER
   ================================================================ */
.mobile-section-header {
  padding: 0.25rem 0 0.875rem;
}
.mobile-section-title {
  font-size: 1.125rem;
  font-weight: 700;
  margin: 0;
  color: var(--color-text);
  line-height: 1.3;
}
.mobile-section-subtitle {
  font-size: 0.8125rem;
  color: var(--color-text-secondary);
  margin: 0.125rem 0 0;
}

/* ================================================================
   GRADE CARDS (mobile — calificaciones)
   ================================================================ */
.grade-cards {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.grade-card {
  background: var(--color-bg);
  border: 1px solid var(--color-border-light);
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  transition: box-shadow 0.2s;
}

/* --- Card Header --- */
.grade-card__header {
  padding: 0.75rem 1rem;
  background: var(--color-bg-tertiary);
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
  border-bottom: 1px solid var(--color-border-light);
}
.grade-card__student-name {
  font-weight: 700;
  font-size: 0.9375rem;
  color: var(--color-text);
  line-height: 1.3;
}
.grade-card__matricula {
  font-size: 0.75rem;
  color: var(--color-text-muted);
  font-weight: 500;
}

/* --- Card Body --- */
.grade-card__body {
  padding: 0.75rem 0.875rem;
  display: flex;
  flex-direction: column;
  gap: 0.625rem;
}

.parcial-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}
.parcial-label {
  min-width: 64px;
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--color-text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.04em;
  flex-shrink: 0;
}

.input-pair {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex: 1;
}
.input-group {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  flex: 1;
}

/* --- Grade Input (mobile) --- */
.grade-input-mobile {
  width: 100%;
  min-width: 0;
  height: 48px;
  padding: 0 0.5rem;
  border: 2px solid var(--color-border);
  border-radius: 10px;
  text-align: center;
  font-size: 1rem;
  font-weight: 700;
  font-family: var(--font-family);
  color: var(--color-text);
  background: var(--color-bg);
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  -moz-appearance: textfield;
  -webkit-appearance: none;
  appearance: none;
}
.grade-input-mobile::-webkit-outer-spin-button,
.grade-input-mobile::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
.grade-input-mobile:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(169, 30, 34, 0.1);
}
.grade-input-mobile.invalid {
  border-color: var(--color-danger);
  background: #fef2f2;
  animation: shake 0.3s;
}
.grade-input-mobile--att {
  background: var(--color-bg-secondary);
  font-size: 0.9375rem;
}
.grade-input-mobile::placeholder {
  color: var(--color-border);
  font-weight: 400;
  font-size: 0.75rem;
}

@keyframes shake {
  0%,
  100% {
    transform: translateX(0);
  }
  25% {
    transform: translateX(-3px);
  }
  75% {
    transform: translateX(3px);
  }
}

.input-suffix {
  font-size: 0.75rem;
  color: var(--color-text-muted);
  font-weight: 500;
  flex-shrink: 0;
  user-select: none;
}

/* --- Card Footer --- */
.grade-card__footer {
  padding: 0.75rem 1rem;
  border-top: 1px solid var(--color-border-light);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  flex-wrap: wrap;
}
.grade-card__badges {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}
.grade-card__status {
  min-width: 120px;
  flex-shrink: 0;
}

/* --- Badges (mobile) --- */
.badge-mobile {
  display: inline-flex;
  align-items: center;
  gap: 0.3rem;
  padding: 0.35rem 0.65rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.01em;
  white-space: nowrap;
  line-height: 1;
}
.badge-mobile i {
  font-size: 0.625rem;
  opacity: 0.7;
}
.badge-mobile.badge-success {
  background: #ecfdf5;
  color: #065f46;
}
.badge-mobile.badge-danger {
  background: #fef2f2;
  color: #991b1b;
}
.badge-mobile.badge-warning {
  background: #fffbeb;
  color: #92400e;
}
.badge-mobile.badge-neutral {
  background: #f3f4f6;
  color: #6b7280;
}

/* ================================================================
   STICKY SAVE BAR (mobile only)
   ================================================================ */
.save-bar {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 0.75rem 1rem;
  padding-bottom: calc(0.75rem + env(safe-area-inset-bottom, 0px));
  background: var(--color-bg);
  border-top: 1px solid var(--color-border-light);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  z-index: 100;
  box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.08);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}
.save-bar__info {
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}
.save-bar__count {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--color-text);
}
.save-bar__hint {
  font-size: 0.6875rem;
  color: var(--color-text-muted);
}
.save-bar__btn {
  padding: 0.7rem 1.5rem;
  min-height: 48px;
  background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover));
  color: #fff;
  border: none;
  border-radius: 12px;
  font-size: 0.9375rem;
  font-weight: 700;
  font-family: var(--font-family);
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  white-space: nowrap;
  flex-shrink: 0;
  box-shadow: 0 4px 14px rgba(169, 30, 34, 0.3);
  transition: opacity 0.2s, transform 0.15s;
  -webkit-tap-highlight-color: transparent;
}
.save-bar__btn:active {
  transform: scale(0.97);
}
.save-bar__btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
  transform: none;
}

/* ================================================================
   VISIBILITY: mobile / desktop
   ================================================================ */
.mobile-only {
  display: block;
}
.desktop-only {
  display: none;
}

/* ================================================================
   TABLET: 2-column grade cards (768px - 1023px)
   ================================================================ */
@media (min-width: 768px) {
  .grade-cards {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
  }
  .alumno-cards {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.625rem;
  }
  .save-bar {
    padding-left: 2rem;
    padding-right: 2rem;
  }
  .cal-page {
    padding-bottom: 90px;
  }
}

/* ================================================================
   DESKTOP (>= 1024px)
   ================================================================ */
@media (min-width: 1024px) {
  /* --- Hide mobile-only elements --- */
  .mobile-only {
    display: none !important;
  }
  .desktop-only {
    display: block !important;
  }

  /* --- Remove mobile bottom padding --- */
  .cal-page {
    padding-bottom: 0;
  }

  /* --- Hide filter toggle --- */
  .filter-toggle {
    display: none;
  }

  /* --- Filter bar: horizontal row --- */
  .filter-bar {
    flex-direction: row;
    flex-wrap: wrap;
    max-height: none;
    opacity: 1;
    overflow: visible;
    margin-top: 0;
    margin-bottom: 1.25rem;
    gap: 0.5rem;
  }
  .filter-item {
    width: auto;
    min-width: 150px;
  }

  /* --- Card (desktop wrapper) --- */
  .card {
    border: 1px solid var(--color-border-light);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-xs);
    overflow: hidden;
    background: var(--color-bg);
  }
  .card-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 1.25rem;
    background: var(--color-bg-tertiary);
    border-bottom: 1px solid var(--color-border-light);
    font-weight: 600;
    font-size: 0.875rem;
  }
  .card-header i {
    color: var(--color-primary);
  }
  .card-body {
    padding: 0;
  }

  /* --- Data table (desktop) --- */
  .data-table {
    width: 100%;
    border-collapse: collapse;
  }
  .data-table thead th {
    text-align: center;
    padding: 0.5rem 0.375rem;
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--color-text-muted);
    background: var(--color-bg-tertiary);
    border-bottom: 1px solid var(--color-border-light);
  }
  .data-table tbody td {
    text-align: center;
    padding: 0.375rem;
    font-size: 0.8125rem;
    color: var(--color-text);
    border-bottom: 1px solid var(--color-border-light);
    vertical-align: middle;
  }
  .data-table tbody td:first-child {
    text-align: left;
    padding-left: 0.75rem;
  }
  .data-table tbody tr:hover {
    background: var(--color-bg-tertiary);
  }

  /* --- Grade inputs (desktop) --- */
  .grade-input {
    width: 48px;
    padding: 0.25rem 0.125rem;
    border: 1.5px solid var(--color-border);
    border-radius: 6px;
    text-align: center;
    font-size: 0.8125rem;
    font-family: var(--font-family);
    color: var(--color-text);
    background: var(--color-bg-primary);
    outline: none;
  }
  .grade-input:focus {
    border-color: var(--color-primary);
    background: var(--color-bg-tertiary);
  }
  .grade-input.invalid {
    border-color: var(--color-danger);
    background: rgba(239,68,68,0.15);
    animation: shake 0.3s;
  }
  .grade-att {
    width: 42px;
    font-size: 0.75rem;
    background: var(--color-bg-secondary);
  }
  .grade-att:focus {
    background: var(--color-bg-tertiary);
  }

  /* --- Utility --- */
  .fw-semibold {
    font-weight: 600;
  }
  .text-muted {
    color: var(--color-text-muted);
  }
  .table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }
}
</style>
