<template>
  <div class="horarios-module">
    <div class="module-header">
      <div class="header-info">
        <div class="header-icon"><i class="fas fa-clock"></i></div>
        <div>
          <h1 class="header-title">Horarios</h1>
          <p class="header-subtitle">Configuración de clases, docentes y horas por grupo</p>
        </div>
      </div>
      <button class="btn-create" @click="openModal('create')"><i class="fas fa-plus"></i><span>Nuevo Horario</span></button>
    </div>

    <div class="filter-strip">
      <div class="filter-chips">
        <SearchSelect v-model="filtroGrupoId" :options="grupoFilterOptions" placeholder="Todos los grupos" @update:model-value="fetchHorarios" />
        <div class="horas-summary" v-if="horasResumen && filtroGrupoId">
          <span v-for="h in horasResumen" :key="h.dia" class="horas-chip" :class="{ 'horas-exceso': h.total_horas > maxHorasDiarias }">
            {{ h.dia }}: {{ h.total_horas }}h
          </span>
        </div>
      </div>
    </div>

    <div class="schedule-card">
      <div class="table-loading" v-if="loading"><div class="loading-pulse"></div><p>Cargando horarios...</p></div>
      <template v-else>
        <div v-if="horarios.length === 0" class="empty-state">
          <div class="empty-illustration"><i class="fas fa-clock"></i></div>
          <h3>Sin horarios</h3>
          <p>{{ filtroGrupoId ? 'Este grupo no tiene horarios asignados' : 'Selecciona un grupo o crea un nuevo horario' }}</p>
        </div>
        <div v-else class="schedule-grid">
          <div v-for="item in horarios" :key="item.id" class="schedule-item">
            <div class="sch-day" :class="'day-' + item.dia?.toLowerCase()">
              <span>{{ item.dia }}</span>
              <small>{{ item.hora_inicio }} - {{ item.hora_fin }}</small>
            </div>
            <div class="sch-body">
              <div class="sch-subject">{{ item.materia?.nombre || item.materia_nombre || '-' }}</div>
              <div class="sch-teacher" v-if="item.docente">
                <i class="fas fa-chalkboard-user"></i> {{ item.docente?.user?.nombre_completo || item.docente?.nombre || item.docente_nombre || '-' }}
              </div>
              <div class="sch-meta">
                <span v-if="item.aula"><i class="fas fa-door-open"></i> {{ item.aula }}</span>
                <span><i class="fas fa-users"></i> {{ item.grupo?.nombre || item.grupo_nombre || '-' }}</span>
              </div>
            </div>
            <div class="sch-actions">
              <button class="action-btn" title="Editar" @click="openModal('edit', item)"><i class="fas fa-pen-to-square"></i></button>
              <button class="action-btn action-delete" title="Eliminar" @click="confirmDelete(item)"><i class="fas fa-trash-can"></i></button>
            </div>
          </div>
        </div>
      </template>
    </div>

    <div class="modal fade" ref="horarioModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <div class="modal-icon" :class="modalMode === 'edit' ? 'icon-edit' : 'icon-create'">
              <i :class="modalMode === 'edit' ? 'fas fa-pen-to-square' : 'fas fa-plus'"></i>
            </div>
            <div>
              <h5 class="modal-title">{{ modalMode === 'edit' ? 'Editar Horario' : 'Nuevo Horario' }}</h5>
              <p class="modal-subtitle">Asigna materia, docente y horario a un grupo</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="form-grid">
              <div class="form-field">
                <label>Grupo <span class="req">*</span></label>
                <SearchSelect v-model="horarioForm.grupo_id" :options="grupoFormOptions" placeholder="Seleccionar grupo" @update:model-value="onGrupoChange" />
              </div>
              <div class="form-field">
                <label>Materia <span class="req">*</span></label>
                <SearchSelect v-model="horarioForm.materia_id" :options="materiaFormOptions" placeholder="Seleccionar materia" @update:model-value="onMateriaChange" />
              </div>
              <div class="form-field">
                <label>Docente <span class="req">*</span></label>
                <SearchSelect v-model="horarioForm.docente_id" :options="docenteFormOptions" placeholder="Seleccionar docente calificado" />
                <small v-if="horarioForm.materia_id && docentesFiltrados.length === 0" class="text-danger mt-1">
                  <i class="fas fa-triangle-exclamation"></i> No hay docentes calificados para esta materia
                </small>
              </div>
              <div class="form-field">
                <label>Día <span class="req">*</span></label>
                <SearchSelect v-model="horarioForm.dia" :options="diaFormOptions" placeholder="Seleccionar día" />
              </div>
              <div class="form-field">
                <label>Hora Inicio <span class="req">*</span></label>
                <input type="time" v-model="horarioForm.hora_inicio" class="field-input" />
              </div>
              <div class="form-field">
                <label>Hora Fin <span class="req">*</span></label>
                <input type="time" v-model="horarioForm.hora_fin" class="field-input" />
              </div>
              <div class="form-field full-width" v-if="horasCalculadas !== null">
                <div :class="['horas-alert', horasCalculadas > maxHorasDiarias ? 'alert-danger' : 'alert-success']">
                  <i :class="horasCalculadas > maxHorasDiarias ? 'fas fa-triangle-exclamation' : 'fas fa-circle-check'"></i>
                  {{ horasCalculadas > maxHorasDiarias ? '¡Excede el límite!' : 'Dentro del límite' }}
                  — Total estimado en este día: <strong>{{ horasCalculadas }}h</strong> / {{ maxHorasDiarias }}h máximo
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary btn-save" :disabled="savingHorario" @click="saveHorario">
              <i v-if="savingHorario" class="fas fa-circle-notch fa-spin"></i>
              <i v-else class="fas fa-floppy-disk"></i>
              {{ savingHorario ? 'Guardando...' : 'Guardar Horario' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/api/axios'
import Swal from 'sweetalert2'
import { Modal } from 'bootstrap'
import SearchSelect from '@/components/ui/SearchSelect.vue'

export default {
  name: 'HorariosList',
  components: { SearchSelect },
  data() {
    return {
      horarios: [], grupos: [], materias: [], docentesGrupo: [], docentesFiltrados: [],
      loading: false, filtroGrupoId: '',
      horarioForm: { grupo_id: '', materia_id: '', docente_id: '', dia: '', hora_inicio: '', hora_fin: '', aula: '' },
      modalMode: 'create', savingHorario: false, horarioModalInstance: null,
      horasResumen: null, maxHorasDiarias: 8, horasCalculadas: null,
    }
  },
  computed: {
    grupoFilterOptions() {
      return [{ value: '', label: 'Todos los grupos' }, ...this.grupos.map(g => ({ value: g.id, label: g.nombre }))]
    },
    grupoFormOptions() {
      return [{ value: '', label: 'Seleccionar grupo' }, ...this.grupos.map(g => ({ value: g.id, label: g.nombre }))]
    },
    materiaFormOptions() {
      return [{ value: '', label: 'Seleccionar materia' }, ...this.materias.map(m => ({ value: m.id, label: m.nombre }))]
    },
    docenteFormOptions() {
      return [{ value: '', label: 'Seleccionar docente calificado' }, ...this.docentesFiltrados.map(d => ({ value: d.id, label: d.usuario?.nombre_completo || d.nombre_completo }))]
    },
    diaFormOptions() {
      return [
        { value: '', label: 'Seleccionar día' },
        { value: 'Lunes', label: 'Lunes' },
        { value: 'Martes', label: 'Martes' },
        { value: 'Miércoles', label: 'Miércoles' },
        { value: 'Jueves', label: 'Jueves' },
        { value: 'Viernes', label: 'Viernes' },
      ]
    },
  },
  mounted() {
    this.fetchHorarios(); this.fetchGrupos(); this.fetchMaterias()
  },
  methods: {
    async fetchHorarios() {
      this.loading = true
      try {
        const params = {}
        if (this.filtroGrupoId) params.grupo_id = this.filtroGrupoId
        const { data } = await api.get('/horarios', { params })
        this.horarios = data.data || data || []
        if (this.filtroGrupoId) await this.verificarResumen()
      } catch (e) { this.horarios = [] }
      this.loading = false
    },
    async verificarResumen() {
      try {
        const { data } = await api.get('/horarios/resumen?grupo_id=' + this.filtroGrupoId)
        this.horasResumen = data.data || data
      } catch (e) { this.horasResumen = null }
    },
    async fetchGrupos() {
      try { const { data } = await api.get('/grupos?per_page=100'); this.grupos = data.data || data || [] } catch (e) { /* */ }
    },
    async fetchMaterias() {
      try { const { data } = await api.get('/materias?per_page=100'); this.materias = data.data || data || [] } catch (e) { /* */ }
    },
    async onGrupoChange() {
      this.docentesGrupo = []
      this.docentesFiltrados = []
      this.horasCalculadas = null
      if (!this.horarioForm.grupo_id) return
      try {
        const { data } = await api.get('/grupos/' + this.horarioForm.grupo_id + '/docentes')
        this.docentesGrupo = data.data || data || []
      } catch (e) { /* */ }
      this.calcularHoras()
    },
    async onMateriaChange() {
      this.horarioForm.docente_id = ''
      this.docentesFiltrados = []
      if (!this.horarioForm.materia_id) return
      try {
        const { data } = await api.get('/horarios/docentes-por-materia', { params: { materia_id: this.horarioForm.materia_id } })
        this.docentesFiltrados = data.data || data || []
      } catch (e) { /* */ }
    },
    async calcularHoras() {
      if (!this.horarioForm.grupo_id || !this.horarioForm.docente_id || !this.horarioForm.dia) {
        this.horasCalculadas = null; return
      }
      try {
        const { data } = await api.post('/grupos/verificar-horario', {
          docente_id: this.horarioForm.docente_id,
          grupo_id: this.horarioForm.grupo_id,
        })
        const dia = data.data?.horas_por_dia?.find(h => h.dia === this.horarioForm.dia)
        let base = dia?.total_horas || 0
        if (this.horarioForm.hora_inicio && this.horarioForm.hora_fin) {
          const [hi, mi] = this.horarioForm.hora_inicio.split(':').map(Number)
          const [hf, mf] = this.horarioForm.hora_fin.split(':').map(Number)
          base += ((hf * 60 + mf) - (hi * 60 + mi)) / 60
        }
        this.horasCalculadas = Math.round(base * 10) / 10
        this.maxHorasDiarias = data.data?.max_horas_permitidas || 8
      } catch (e) { this.horasCalculadas = null }
    },
    openModal(mode, item = null) {
      this.modalMode = mode; this.docentesGrupo = []; this.horasCalculadas = null
      if (mode === 'edit' && item) {
        this.horarioForm = { ...item }
        if (item.grupo_id) this.onGrupoChange()
      } else {
        this.horarioForm = { grupo_id: '', materia_id: '', docente_id: '', dia: '', hora_inicio: '', hora_fin: '', aula: '' }
      }
      if (!this.horarioModalInstance) this.horarioModalInstance = new Modal(this.$refs.horarioModal)
      this.horarioModalInstance.show()
    },
    async saveHorario() {
      this.savingHorario = true
      try {
        const payload = { ...this.horarioForm }
        if (this.modalMode === 'edit') {
          await api.put('/horarios/' + payload.id, payload)
        } else {
          await api.post('/horarios', payload)
        }
        this.horarioModalInstance.hide(); this.fetchHorarios()
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Horario guardado', showConfirmButton: false, timer: 2000, timerProgressBar: true })
      } catch (e) {
        const msg = e.response?.data?.message || 'No se pudo guardar el horario.'
        Swal.fire('Error', msg, 'error')
      }
      this.savingHorario = false
    },
    async confirmDelete(item) {
      const result = await Swal.fire({ title: 'Eliminar Horario', text: '¿Estás seguro de eliminar este horario?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Eliminar', cancelButtonText: 'Cancelar', confirmButtonColor: '#DC2626' })
      if (!result.isConfirmed) return
      try { await api.delete('/horarios/' + item.id); this.fetchHorarios(); Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Horario eliminado', showConfirmButton: false, timer: 2000 }) } catch (e) { Swal.fire('Error', 'No se pudo eliminar.', 'error') }
    },
  },
}
</script>

<style scoped>
.module-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; gap: 1rem; flex-wrap: wrap; }
.header-info { display: flex; align-items: center; gap: 1rem; }
.header-icon { width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover)); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.35rem; box-shadow: 0 4px 12px rgba(169,30,34,0.25); }
.header-title { font-size: 1.5rem; font-weight: 700; color: var(--color-text); margin: 0; line-height: 1.2; }
.header-subtitle { font-size: .8125rem; color: var(--color-text-secondary); margin: 2px 0 0; }
.btn-create { display: inline-flex; align-items: center; gap: .5rem; padding: .625rem 1.25rem; border-radius: 12px; border: none; background: var(--color-primary); color: #fff; font-size: .875rem; font-weight: 600; cursor: pointer; transition: all .2s; box-shadow: 0 2px 8px rgba(169,30,34,0.2); }
.btn-create:hover { background: var(--color-primary-hover); transform: translateY(-1px); box-shadow: 0 4px 16px rgba(169,30,34,0.3); }
.filter-strip { display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; background: var(--color-bg); border: 1px solid var(--color-border-light); border-radius: 16px; padding: .625rem 1rem; margin-bottom: 1.25rem; }
.filter-chips { display: flex; align-items: center; gap: .75rem; flex-wrap: wrap; }
.filter-select { padding: .5rem 2rem .5rem .75rem; border: 1px solid var(--color-border); border-radius: 10px; font-size: .8125rem; color: var(--color-text); background: var(--color-bg); cursor: pointer; outline: none; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236B7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right .6rem center; }
.horas-summary { display: flex; gap: .375rem; }
.horas-chip { padding: .2rem .625rem; border-radius: 20px; font-size: .75rem; font-weight: 600; background: var(--color-success-light); color: var(--color-success); }
.horas-chip.horas-exceso { background: var(--color-danger-light); color: var(--color-danger); }

.schedule-card { background: var(--color-bg); border: 1px solid var(--color-border-light); border-radius: 18px; overflow: hidden; box-shadow: var(--shadow-xs); }
.table-loading { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4rem 2rem; color: var(--color-text-muted); gap: 1rem; }
.loading-pulse { width: 40px; height: 40px; border-radius: 50%; border: 3px solid var(--color-border); border-top-color: var(--color-primary); animation: spin .7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.schedule-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1px; background: var(--color-border-light); }
.schedule-item { background: var(--color-bg); padding: 1rem 1.25rem; display: flex; gap: .875rem; align-items: flex-start; }
.sch-day { min-width: 70px; text-align: center; }
.sch-day span { display: block; font-weight: 700; font-size: .75rem; text-transform: uppercase; }
.sch-day small { font-size: .6875rem; color: var(--color-text-muted); display: block; margin-top: 2px; }
.day-lunes span { color: #C62828; } .day-martes span { color: #E53935; } .day-miércoles span { color: #EF5350; } .day-jueves span { color: #FF8A80; } .day-viernes span { color: #FF5252; }
.sch-body { flex: 1; min-width: 0; }
.sch-subject { font-weight: 700; font-size: .9375rem; color: var(--color-text); margin-bottom: 2px; }
.sch-teacher { font-size: .8125rem; color: var(--color-text-secondary); margin-bottom: 4px; }
.sch-meta { display: flex; gap: .75rem; font-size: .75rem; color: var(--color-text-muted); }
.sch-meta i { margin-right: 2px; }
.sch-actions { display: flex; flex-direction: column; gap: .25rem; }
.action-btn { width: 32px; height: 32px; border: none; border-radius: 8px; background: none; color: var(--color-text-secondary); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: .8125rem; transition: all .15s; }
.action-btn:hover { background: var(--color-bg-secondary); color: var(--color-text); transform: scale(1.08); }
.action-delete:hover { color: var(--color-danger); background: var(--color-danger-light); }

.empty-state { display: flex; flex-direction: column; align-items: center; padding: 4rem 2rem; text-align: center; }
.empty-illustration { width: 80px; height: 80px; border-radius: 50%; background: var(--color-bg-secondary); display: flex; align-items: center; justify-content: center; margin-bottom: 1.25rem; font-size: 2rem; color: var(--color-text-muted); }
.empty-state h3 { font-size: 1.125rem; font-weight: 600; color: var(--color-text); margin: 0 0 .25rem; }
.empty-state p { font-size: .875rem; color: var(--color-text-secondary); margin: 0; }

.modal-content { border: none; border-radius: 20px; overflow: hidden; }
.modal-header { padding: 1.5rem 1.75rem 1rem; border-bottom: none; display: flex; align-items: flex-start; gap: .875rem; }
.modal-icon { width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; color: #fff; }
.icon-create { background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover)); }
.icon-edit { background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover)); }
.modal-title { font-size: 1.125rem; font-weight: 700; margin: 0; }
.modal-subtitle { font-size: .8125rem; color: var(--color-text-muted); margin: 2px 0 0; }
.modal-body { padding: 0 1.75rem 1.5rem; }
.modal-footer { padding: 1rem 1.75rem; border-top: 1px solid var(--color-border-light); background: var(--color-bg-tertiary); display: flex; justify-content: flex-end; gap: .625rem; }
.btn-save { padding: .5625rem 1.5rem; font-weight: 600; }
.form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
.form-field { display: flex; flex-direction: column; gap: .3rem; }
.form-field label { font-size: .8125rem; font-weight: 500; color: var(--color-text); }
.form-field .req { color: var(--color-danger); }
.form-field.full-width { grid-column: 1 / -1; }
.field-input { padding: .5625rem .75rem; border: 1.5px solid var(--color-border); border-radius: 10px; font-size: .875rem; font-family: var(--font-family); color: var(--color-text); background: var(--color-bg); outline: none; transition: border-color .2s, box-shadow .2s; }
.field-input:focus { border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(169,30,34,0.08); }
.horas-alert { padding: .625rem .875rem; border-radius: 10px; font-size: .8125rem; display: flex; align-items: center; gap: .5rem; }
.horas-alert.alert-success { background: var(--color-success-light); color: var(--color-success); border: 1px solid rgba(46,204,113,0.25); }
.horas-alert.alert-danger { background: var(--color-danger-light); color: var(--color-danger); border: 1px solid rgba(231,76,60,0.25); }
@media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } }
</style>
