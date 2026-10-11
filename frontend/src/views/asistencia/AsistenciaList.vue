<template>
  <div class="asistencia-page">
    <div class="page-header">
      <div class="page-header-left">
        <span class="page-header-icon">
          <i class="fas fa-calendar-check"></i>
        </span>
        <div class="page-header-info">
          <h3 class="page-header-title">Asistencia</h3>
          <p class="page-header-subtitle">Control de asistencia, pase de lista y justificaci&oacute;n de faltas</p>
        </div>
      </div>
      <div class="page-header-actions">
        <button class="btn btn-outline-custom" @click="openPaseListaModal">
          <i class="fas fa-list-check"></i>
          <span>Pase de Lista</span>
        </button>
        <button class="btn btn-primary-custom" @click="openCreateModal">
          <i class="fas fa-plus"></i>
          <span>Nuevo Registro</span>
        </button>
      </div>
    </div>

    <div class="card filter-card">
      <div class="filter-bar">
        <div class="filter-group">
          <label class="filter-label"><i class="fas fa-users"></i> Grupo</label>
          <SearchSelect v-model="filters.grupo_id" :options="grupoFilterOptions" placeholder="Todos" @update:model-value="applyFilters" />
        </div>
        <div class="filter-group">
          <label class="filter-label"><i class="fas fa-book"></i> Materia</label>
          <SearchSelect v-model="filters.materia_id" :options="materiaFilterOptions" placeholder="Todas" @update:model-value="applyFilters" />
        </div>
        <div class="filter-group">
          <label class="filter-label"><i class="fas fa-calendar-alt"></i> Fecha</label>
          <input type="date" class="filter-input" v-model="filters.fecha" @change="applyFilters" />
        </div>
        <div class="filter-group">
          <label class="filter-label"><i class="fas fa-flag"></i> Estatus</label>
          <SearchSelect v-model="filters.estatus" :options="estatusFilterOptions" placeholder="Todos" @update:model-value="applyFilters" />
        </div>
      </div>
    </div>

    <div class="card table-card">
      <div class="table-responsive">
        <table class="table-custom">
          <thead>
            <tr>
              <th>Alumno</th>
              <th>Grupo</th>
              <th>Materia</th>
              <th class="cell-center">Fecha</th>
              <th class="cell-center">Estatus</th>
              <th>Justificaci&oacute;n</th>
              <th>Registrado Por</th>
              <th class="cell-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="8" class="cell-empty">
                <div class="empty-state">
                  <div class="spinner-custom"></div>
                  <span>Cargando...</span>
                </div>
              </td>
            </tr>
            <tr v-else-if="items.length === 0">
              <td colspan="8" class="cell-empty">
                <div class="empty-state">
                  <i class="fas fa-calendar-check empty-icon"></i>
                  <span>Sin registros</span>
                </div>
              </td>
            </tr>
            <tr v-for="item in items" :key="item.id" v-else>
              <td class="cell-strong" data-label="Alumno">{{ item.alumno?.nombre_completo || item.alumno?.nombre || '&mdash;' }}</td>
              <td data-label="Grupo">{{ item.grupo?.nombre || '&mdash;' }}</td>
              <td data-label="Materia">{{ item.materia?.nombre || '&mdash;' }}</td>
              <td class="cell-center" data-label="Fecha">{{ formatDate(item.fecha) }}</td>
              <td class="cell-center" data-label="Estatus">
                <span :class="estatusBadge(item.estatus)">{{ estatusLabel(item.estatus) }}</span>
              </td>
              <td data-label="Justificación">{{ item.justificacion || '&mdash;' }}</td>
              <td data-label="Registrado Por">{{ item.docente?.nombre_completo || item.docente?.nombre || '&mdash;' }}</td>
              <td class="cell-end" data-label="Acciones">
                <div class="action-group">
                  <button
                    v-if="item.estatus === 'falta'"
                    class="btn-icon btn-icon-info"
                    title="Justificar"
                    @click="openJustificarModal(item)"
                  >
                    <i class="fas fa-pen-to-square"></i>
                  </button>
                  <button class="btn-icon" title="Editar" @click="openEditModal(item)">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button class="btn-icon btn-icon-danger" title="Eliminar" @click="confirmDelete(item)">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="modal fade" ref="formModalRef" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header-custom">
            <div class="modal-header-left">
              <span class="modal-header-icon"><i class="fas fa-calendar-check"></i></span>
              <h5 class="modal-title" id="formModalLabel">
                {{ isEditing ? 'Editar Asistencia' : 'Nuevo Registro de Asistencia' }}
              </h5>
            </div>
            <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Close">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="modal-body-custom">
            <div class="form-grid form-grid-2col">
              <div class="form-field">
                <label class="field-label">Alumno <span class="required">*</span></label>
                <SearchSelect v-model="form.alumno_id" :options="alumnoFormOptions" placeholder="Seleccionar..." :class="{ 'field-error': formErrors.alumno_id }" />
                <div class="field-feedback" v-if="formErrors.alumno_id">{{ formErrors.alumno_id }}</div>
              </div>
              <div class="form-field">
                <label class="field-label">Grupo <span class="required">*</span></label>
                <SearchSelect v-model="form.grupo_id" :options="grupoFormOptions" placeholder="Seleccionar..." :class="{ 'field-error': formErrors.grupo_id }" />
                <div class="field-feedback" v-if="formErrors.grupo_id">{{ formErrors.grupo_id }}</div>
              </div>
              <div class="form-field">
                <label class="field-label">Materia <span class="required">*</span></label>
                <SearchSelect v-model="form.materia_id" :options="materiaFormOptions" placeholder="Seleccionar..." :class="{ 'field-error': formErrors.materia_id }" />
                <div class="field-feedback" v-if="formErrors.materia_id">{{ formErrors.materia_id }}</div>
              </div>
              <div class="form-field">
                <label class="field-label">Docente</label>
                <SearchSelect v-model="form.docente_id" :options="docenteFormOptions" placeholder="Seleccionar..." />
              </div>
              <div class="form-field">
                <label class="field-label">Fecha <span class="required">*</span></label>
                <input type="date" class="field-input" v-model="form.fecha" :class="{ 'field-error': formErrors.fecha }" />
                <div class="field-feedback" v-if="formErrors.fecha">{{ formErrors.fecha }}</div>
              </div>
              <div class="form-field">
                <label class="field-label">Estatus <span class="required">*</span></label>
                <SearchSelect v-model="form.estatus" :options="estatusFormOptions" placeholder="Seleccionar..." :class="{ 'field-error': formErrors.estatus }" />
                <div class="field-feedback" v-if="formErrors.estatus">{{ formErrors.estatus }}</div>
              </div>
              <div class="form-field form-field-full">
                <label class="field-label">Justificaci&oacute;n</label>
                <textarea class="field-textarea" rows="2" v-model="form.justificacion" placeholder="Opcional..."></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer-custom">
            <button type="button" class="btn btn-secondary-custom" @click="hideFormModal">Cancelar</button>
            <button type="button" class="btn btn-primary-custom" :disabled="saving" @click="handleSave">
              <span v-if="saving"><span class="spinner-custom spinner-custom-sm"></span> Guardando...</span>
              <span v-else><i class="fas fa-save"></i> Guardar</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" ref="paseListaModalRef" tabindex="-1" aria-labelledby="paseListaModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header-custom">
            <div class="modal-header-left">
              <span class="modal-header-icon"><i class="fas fa-list-check"></i></span>
              <h5 class="modal-title" id="paseListaModalLabel">Pase de Lista</h5>
            </div>
            <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Close">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="modal-body-custom">
            <div class="pase-config">
              <div class="form-field">
                <label class="field-label">Grupo <span class="required">*</span></label>
                <SearchSelect v-model="paseLista.grupo_id" :options="paseGrupoOptions" placeholder="Seleccionar..." @update:model-value="onPaseListaGrupoChange" />
              </div>
              <div class="form-field">
                <label class="field-label">Materia <span class="required">*</span></label>
                <SearchSelect v-model="paseLista.materia_id" :options="paseMateriaOptions" placeholder="Seleccionar..." />
              </div>
              <div class="form-field">
                <label class="field-label">Fecha <span class="required">*</span></label>
                <input type="date" class="field-input" v-model="paseLista.fecha" />
              </div>
            </div>

            <div v-if="paseListaAlumnos.length > 0" class="pase-table-section">
              <div class="pase-table-header">
                <span class="pase-count">{{ paseListaAlumnos.length }} alumnos</span>
                <div class="pase-bulk-actions">
                  <button type="button" class="btn-bulk btn-bulk-success" @click="marcarTodos('presente')">
                    <i class="fas fa-check"></i> Todos Presentes
                  </button>
                  <button type="button" class="btn-bulk btn-bulk-danger" @click="marcarTodos('falta')">
                    <i class="fas fa-times"></i> Todos Falta
                  </button>
                  <button type="button" class="btn-bulk btn-bulk-info" @click="marcarTodos('justificada')">
                    <i class="fas fa-file-alt"></i> Todos Justificada
                  </button>
                </div>
              </div>
              <div class="table-responsive pase-table-scroll">
                <table class="table-custom">
                  <thead>
                    <tr>
                      <th style="width:44px;">#</th>
                      <th>Alumno</th>
                      <th class="cell-center" style="width:100px;">Presente</th>
                      <th class="cell-center" style="width:80px;">Falta</th>
                      <th class="cell-center" style="width:100px;">Justificada</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, idx) in paseListaAlumnos" :key="item.alumno_id">
                      <td class="cell-muted" data-label="#">{{ idx + 1 }}</td>
                      <td class="cell-strong" data-label="Alumno">{{ item.alumno_nombre }}</td>
                      <td class="cell-center" data-label="Presente">
                        <input
                          type="radio"
                          :name="'estatus_' + item.alumno_id"
                          :value="'presente'"
                          v-model="item.estatus"
                          class="radio-custom"
                        />
                      </td>
                      <td class="cell-center" data-label="Falta">
                        <input
                          type="radio"
                          :name="'estatus_' + item.alumno_id"
                          :value="'falta'"
                          v-model="item.estatus"
                          class="radio-custom"
                        />
                      </td>
                      <td class="cell-center" data-label="Justificada">
                        <input
                          type="radio"
                          :name="'estatus_' + item.alumno_id"
                          :value="'justificada'"
                          v-model="item.estatus"
                          class="radio-custom"
                        />
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div v-else class="pase-empty">
              <i class="fas fa-users pase-empty-icon"></i>
              <span>Selecciona un grupo y materia para cargar los alumnos</span>
            </div>
          </div>
          <div class="modal-footer-custom">
            <button type="button" class="btn btn-secondary-custom" @click="hidePaseListaModal">Cancelar</button>
            <button
              type="button"
              class="btn btn-primary-custom"
              :disabled="paseListaSaving || paseListaAlumnos.length === 0"
              @click="handlePaseListaSave"
            >
              <span v-if="paseListaSaving"><span class="spinner-custom spinner-custom-sm"></span> Guardando...</span>
              <span v-else><i class="fas fa-save"></i> Guardar Pase de Lista</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" ref="justificarModalRef" tabindex="-1" aria-labelledby="justificarModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header-custom">
            <div class="modal-header-left">
              <span class="modal-header-icon"><i class="fas fa-pen-to-square"></i></span>
              <h5 class="modal-title" id="justificarModalLabel">Justificar Falta</h5>
            </div>
            <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Close">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="modal-body-custom">
            <div class="form-field">
              <label class="field-label">Justificaci&oacute;n <span class="required">*</span></label>
              <textarea
                class="field-textarea"
                rows="3"
                v-model="justificarForm.justificacion"
                placeholder="Motivo de la justificaci&oacute;n..."
              ></textarea>
            </div>
          </div>
          <div class="modal-footer-custom">
            <button type="button" class="btn btn-secondary-custom" @click="hideJustificarModal">Cancelar</button>
            <button type="button" class="btn btn-primary-custom" :disabled="justificarSaving" @click="handleJustificar">
              <span v-if="justificarSaving"><span class="spinner-custom spinner-custom-sm"></span></span>
              Guardar
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
  name: 'AsistenciaList',
  components: { SearchSelect },
  data() {
    return {
      items: [],
      alumnos: [],
      grupos: [],
      materias: [],
      docentes: [],
      loading: false,
      saving: false,
      isEditing: false,
      editingId: null,
      formModalInstance: null,
      paseListaModalInstance: null,
      justificarModalInstance: null,

      paseListaSaving: false,
      paseLista: {
        grupo_id: '',
        materia_id: '',
        fecha: new Date().toISOString().split('T')[0],
      },
      paseListaAlumnos: [],

      justificarSaving: false,
      justificarId: null,
      justificarForm: {
        justificacion: '',
      },

      filters: {
        grupo_id: '',
        materia_id: '',
        fecha: '',
        estatus: '',
      },
      form: {
        alumno_id: '',
        grupo_id: '',
        materia_id: '',
        docente_id: '',
        fecha: new Date().toISOString().split('T')[0],
        estatus: 'presente',
        justificacion: '',
      },
      formErrors: {},
    }
  },
  computed: {
    grupoFilterOptions() {
      return [{ value: '', label: 'Todos' }, ...this.grupos.map(g => ({ value: g.id, label: g.nombre }))]
    },
    materiaFilterOptions() {
      return [{ value: '', label: 'Todas' }, ...this.materias.map(m => ({ value: m.id, label: m.nombre }))]
    },
    estatusFilterOptions() {
      return [
        { value: '', label: 'Todos' },
        { value: 'presente', label: 'Presente' },
        { value: 'falta', label: 'Falta' },
        { value: 'justificada', label: 'Justificada' },
      ]
    },
    alumnoFormOptions() {
      return [{ value: '', label: 'Seleccionar...' }, ...this.alumnos.map(a => ({ value: a.id, label: a.nombre_completo || a.nombre }))]
    },
    grupoFormOptions() {
      return [{ value: '', label: 'Seleccionar...' }, ...this.grupos.map(g => ({ value: g.id, label: g.nombre }))]
    },
    materiaFormOptions() {
      return [{ value: '', label: 'Seleccionar...' }, ...this.materias.map(m => ({ value: m.id, label: m.nombre }))]
    },
    docenteFormOptions() {
      return [{ value: '', label: 'Seleccionar...' }, ...this.docentes.map(d => ({ value: d.id, label: d.nombre_completo || d.nombre }))]
    },
    estatusFormOptions() {
      return [
        { value: '', label: 'Seleccionar...' },
        { value: 'presente', label: 'Presente' },
        { value: 'falta', label: 'Falta' },
        { value: 'justificada', label: 'Justificada' },
      ]
    },
    paseGrupoOptions() {
      return [{ value: '', label: 'Seleccionar...' }, ...this.grupos.map(g => ({ value: g.id, label: g.nombre }))]
    },
    paseMateriaOptions() {
      return [{ value: '', label: 'Seleccionar...' }, ...this.materias.map(m => ({ value: m.id, label: m.nombre }))]
    },
  },
  mounted() {
    this.formModalInstance = new Modal(this.$refs.formModalRef)
    this.paseListaModalInstance = new Modal(this.$refs.paseListaModalRef)
    this.justificarModalInstance = new Modal(this.$refs.justificarModalRef)
    this.fetchAll()
  },
  methods: {
    async fetchAll() {
      this.loading = true
      try {
        await Promise.all([
          this.fetchAsistencia(),
          this.fetchAlumnos(),
          this.fetchGrupos(),
          this.fetchMaterias(),
          this.fetchDocentes(),
        ])
      } catch (e) {
        Swal.fire('Error', 'No se pudieron cargar los datos', 'error')
      } finally {
        this.loading = false
      }
    },
    async fetchAsistencia() {
      const params = {}
      if (this.filters.grupo_id) params.grupo_id = this.filters.grupo_id
      if (this.filters.materia_id) params.materia_id = this.filters.materia_id
      if (this.filters.fecha) params.fecha = this.filters.fecha
      if (this.filters.estatus) params.estatus = this.filters.estatus
      const { data } = await api.get('/asistencia', { params })
      this.items = data.data || data || []
    },
    async fetchAlumnos() {
      const { data } = await api.get('/alumnos')
      this.alumnos = data.data || data || []
    },
    async fetchGrupos() {
      const { data } = await api.get('/grupos')
      this.grupos = data.data || data || []
    },
    async fetchMaterias() {
      const { data } = await api.get('/materias')
      this.materias = data.data || data || []
    },
    async fetchDocentes() {
      const { data } = await api.get('/docentes')
      this.docentes = data.data || data || []
    },
    async fetchAlumnosByGrupo(grupoId) {
      try {
        const { data } = await api.get('/alumnos', { params: { grupo_id: grupoId } })
        return data.data || data || []
      } catch {
        return []
      }
    },
    applyFilters() {
      this.fetchAsistencia()
    },
    formatDate(dateStr) {
      if (!dateStr) return '—'
      const d = new Date(dateStr)
      return d.toLocaleDateString('es-MX', { year: 'numeric', month: '2-digit', day: '2-digit' })
    },
    estatusBadge(estatus) {
      return {
        'badge-custom': true,
        'badge-success': estatus === 'presente',
        'badge-danger': estatus === 'falta',
        'badge-info': estatus === 'justificada',
        'badge-muted': !estatus,
      }
    },
    estatusLabel(estatus) {
      const labels = { presente: 'Presente', falta: 'Falta', justificada: 'Justificada' }
      return labels[estatus] || estatus || '—'
    },
    openCreateModal() {
      this.isEditing = false
      this.editingId = null
      this.formErrors = {}
      this.form = {
        alumno_id: '',
        grupo_id: '',
        materia_id: '',
        docente_id: '',
        fecha: new Date().toISOString().split('T')[0],
        estatus: 'presente',
        justificacion: '',
      }
      this.formModalInstance.show()
    },
    openEditModal(item) {
      this.isEditing = true
      this.editingId = item.id
      this.formErrors = {}
      this.form = {
        alumno_id: item.alumno_id,
        grupo_id: item.grupo_id,
        materia_id: item.materia_id,
        docente_id: item.docente_id || '',
        fecha: item.fecha,
        estatus: item.estatus || 'presente',
        justificacion: item.justificacion || '',
      }
      this.formModalInstance.show()
    },
    hideFormModal() {
      this.formModalInstance.hide()
    },
    validateForm() {
      this.formErrors = {}
      if (!this.form.alumno_id) this.formErrors.alumno_id = 'Seleccione un alumno'
      if (!this.form.grupo_id) this.formErrors.grupo_id = 'Seleccione un grupo'
      if (!this.form.materia_id) this.formErrors.materia_id = 'Seleccione una materia'
      if (!this.form.fecha) this.formErrors.fecha = 'Seleccione una fecha'
      if (!this.form.estatus) this.formErrors.estatus = 'Seleccione un estatus'
      return Object.keys(this.formErrors).length === 0
    },
    async handleSave() {
      if (!this.validateForm()) return
      this.saving = true
      try {
        const payload = { ...this.form }
        if (this.isEditing) {
          await api.put(`/asistencia/${this.editingId}`, payload)
          Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Asistencia actualizada', showConfirmButton: false, timer: 2000, timerProgressBar: true })
        } else {
          await api.post('/asistencia', payload)
          Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Asistencia registrada', showConfirmButton: false, timer: 2000, timerProgressBar: true })
        }
        this.hideFormModal()
        this.fetchAsistencia()
      } catch (e) {
        const msg = e.response?.data?.message || 'Error al guardar'
        Swal.fire('Error', msg, 'error')
      } finally {
        this.saving = false
      }
    },
    async confirmDelete(item) {
      const nombre = item.alumno?.nombre_completo || item.alumno?.nombre || 'este alumno'
      const result = await Swal.fire({
        title: '¿Eliminar registro?',
        text: `¿Deseas eliminar el registro de asistencia de ${nombre}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
      })
      if (!result.isConfirmed) return
      try {
        await api.delete(`/asistencia/${item.id}`)
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Registro eliminado', showConfirmButton: false, timer: 2000, timerProgressBar: true })
        this.fetchAsistencia()
      } catch (e) {
        const msg = e.response?.data?.message || 'Error al eliminar'
        Swal.fire('Error', msg, 'error')
      }
    },
    openJustificarModal(item) {
      this.justificarId = item.id
      this.justificarForm.justificacion = item.justificacion || ''
      this.justificarModalInstance.show()
    },
    hideJustificarModal() {
      this.justificarModalInstance.hide()
    },
    async handleJustificar() {
      if (!this.justificarForm.justificacion.trim()) {
        Swal.fire('Validación', 'Ingrese una justificación', 'warning')
        return
      }
      this.justificarSaving = true
      try {
        await api.put(`/asistencia/${this.justificarId}`, {
          estatus: 'justificada',
          justificacion: this.justificarForm.justificacion,
        })
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Falta justificada', showConfirmButton: false, timer: 2000, timerProgressBar: true })
        this.hideJustificarModal()
        this.fetchAsistencia()
      } catch (e) {
        const msg = e.response?.data?.message || 'Error al justificar'
        Swal.fire('Error', msg, 'error')
      } finally {
        this.justificarSaving = false
      }
    },
    openPaseListaModal() {
      this.paseLista = {
        grupo_id: '',
        materia_id: '',
        fecha: new Date().toISOString().split('T')[0],
      }
      this.paseListaAlumnos = []
      this.paseListaModalInstance.show()
    },
    hidePaseListaModal() {
      this.paseListaModalInstance.hide()
    },
    async onPaseListaGrupoChange() {
      if (!this.paseLista.grupo_id) {
        this.paseListaAlumnos = []
        return
      }
      try {
        const list = await this.fetchAlumnosByGrupo(this.paseLista.grupo_id)
        this.paseListaAlumnos = list.map((a) => ({
          alumno_id: a.id,
          alumno_nombre: a.nombre_completo || a.nombre,
          estatus: 'presente',
        }))
      } catch {
        this.paseListaAlumnos = []
      }
    },
    marcarTodos(estatus) {
      this.paseListaAlumnos.forEach((a) => {
        a.estatus = estatus
      })
    },
    async handlePaseListaSave() {
      if (!this.paseLista.grupo_id || !this.paseLista.materia_id || !this.paseLista.fecha) {
        Swal.fire('Validación', 'Complete grupo, materia y fecha', 'warning')
        return
      }
      this.paseListaSaving = true
      try {
        const payload = {
          grupo_id: this.paseLista.grupo_id,
          materia_id: this.paseLista.materia_id,
          fecha: this.paseLista.fecha,
          alumnos: this.paseListaAlumnos.map((a) => ({
            alumno_id: a.alumno_id,
            estatus: a.estatus,
          })),
        }
        await api.post('/asistencia/pase-lista', payload)
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Pase de lista guardado', showConfirmButton: false, timer: 2500, timerProgressBar: true })
        this.hidePaseListaModal()
        this.fetchAsistencia()
      } catch (e) {
        const msg = e.response?.data?.message || 'Error al guardar pase de lista'
        Swal.fire('Error', msg, 'error')
      } finally {
        this.paseListaSaving = false
      }
    },
  },
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

.asistencia-page {
  --primary: #A91E22;
  --primary-light: #C53030;
  --primary-bg: #FEF2F2;
  --success: #059669;
  --success-bg: #ECFDF5;
  --danger: #DC2626;
  --danger-light: #FEE2E2;
  --warning: #D97706;
  --warning-bg: #FFFBEB;
  --info: #0EA5E9;
  --info-bg: #F0F9FF;
  --text: #1F2937;
  --text-secondary: #6B7280;
  --text-muted: #9CA3AF;
  --border: #E5E7EB;
  --bg: #F9FAFB;
  --surface: #FFFFFF;
  --radius-sm: 6px;
  --radius: 10px;
  --radius-lg: 14px;
  --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.04);
  --shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.04);
  --font: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
  font-family: var(--font);
  color: var(--text);
}

.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 24px;
  flex-wrap: wrap;
}
.page-header-left {
  display: flex;
  align-items: center;
  gap: 14px;
}
.page-header-icon {
  width: 46px;
  height: 46px;
  border-radius: var(--radius);
  background: var(--primary-bg);
  color: var(--primary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  flex-shrink: 0;
}
.page-header-info {
  display: flex;
  flex-direction: column;
}
.page-header-title {
  margin: 0;
  font-size: 22px;
  font-weight: 700;
  color: var(--text);
  line-height: 1.3;
}
.page-header-subtitle {
  margin: 0;
  font-size: 13px;
  color: var(--text-secondary);
  font-weight: 400;
}
.page-header-actions {
  display: flex;
  gap: 8px;
  flex-shrink: 0;
}

.btn-primary-custom {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  background: var(--primary);
  color: #fff;
  border: none;
  padding: 9px 18px;
  border-radius: var(--radius-sm);
  font-size: 14px;
  font-weight: 600;
  font-family: var(--font);
  cursor: pointer;
  transition: background 0.15s, box-shadow 0.15s;
  box-shadow: 0 1px 2px rgba(169, 30, 34, 0.25);
}
.btn-primary-custom:hover {
  background: var(--primary-light);
}
.btn-primary-custom:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}
.btn-outline-custom {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  background: var(--surface);
  color: var(--primary);
  border: 1px solid var(--primary);
  padding: 9px 18px;
  border-radius: var(--radius-sm);
  font-size: 14px;
  font-weight: 600;
  font-family: var(--font);
  cursor: pointer;
  transition: all 0.15s;
}
.btn-outline-custom:hover {
  background: var(--primary-bg);
}
.btn-secondary-custom {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  background: var(--bg);
  color: var(--text);
  border: 1px solid var(--border);
  padding: 9px 18px;
  border-radius: var(--radius-sm);
  font-size: 14px;
  font-weight: 600;
  font-family: var(--font);
  cursor: pointer;
  transition: background 0.15s;
}
.btn-secondary-custom:hover {
  background: var(--border);
}

.filter-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  margin-bottom: 20px;
  padding: 0;
}
.filter-bar {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  padding: 16px 20px;
  align-items: flex-end;
}
.filter-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
  min-width: 160px;
  flex: 1;
}
.filter-label {
  font-size: 12px;
  font-weight: 600;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.04em;
  display: flex;
  align-items: center;
  gap: 5px;
}
.filter-label i {
  font-size: 11px;
  color: var(--primary);
}
.filter-select {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  font-size: 13px;
  font-family: var(--font);
  color: var(--text);
  background: var(--surface);
  transition: border-color 0.15s;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236B7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 10px center;
  padding-right: 30px;
}
.filter-select:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px var(--primary-bg);
}
.filter-input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  font-size: 13px;
  font-family: var(--font);
  color: var(--text);
  background: var(--surface);
  transition: border-color 0.15s;
}
.filter-input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px var(--primary-bg);
}

.table-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  overflow: hidden;
}
.table-custom {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}
.table-custom thead th {
  background: var(--bg);
  color: var(--text-secondary);
  font-weight: 600;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  padding: 12px 16px;
  border-bottom: 2px solid var(--border);
  white-space: nowrap;
  text-align: left;
}
.table-custom tbody td {
  padding: 12px 16px;
  border-bottom: 1px solid var(--border);
  color: var(--text);
  vertical-align: middle;
}
.table-custom tbody tr:last-child td {
  border-bottom: none;
}
.table-custom tbody tr:hover {
  background: #FAFAFA;
}
.cell-center {
  text-align: center !important;
}
.cell-end {
  text-align: right !important;
}
.cell-strong {
  font-weight: 600;
}
.cell-muted {
  color: var(--text-muted);
}
.cell-empty {
  text-align: center;
  padding: 40px 16px !important;
}
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
  color: var(--text-secondary);
  font-size: 14px;
}
.empty-icon {
  font-size: 32px;
  opacity: 0.35;
}

.badge-custom {
  display: inline-flex;
  align-items: center;
  padding: 4px 12px;
  border-radius: 100px;
  font-size: 12px;
  font-weight: 600;
  line-height: 1.4;
  letter-spacing: 0.02em;
}
.badge-success {
  background: var(--success-bg);
  color: var(--success);
}
.badge-danger {
  background: var(--danger-light);
  color: var(--danger);
}
.badge-info {
  background: var(--info-bg);
  color: var(--info);
}
.badge-muted {
  background: #F3F4F6;
  color: #6B7280;
}

.action-group {
  display: flex;
  gap: 4px;
  justify-content: flex-end;
}
.btn-icon {
  width: 34px;
  height: 34px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border);
  background: var(--surface);
  color: var(--text-secondary);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s;
  font-size: 13px;
  padding: 0;
}
.btn-icon:hover {
  background: var(--primary-bg);
  color: var(--primary);
  border-color: var(--primary);
}
.btn-icon-info {
  color: var(--info);
  border-color: var(--info);
}
.btn-icon-info:hover {
  background: var(--info-bg);
  color: var(--info);
  border-color: var(--info);
}
.btn-icon-danger:hover {
  background: var(--danger-light);
  color: var(--danger);
  border-color: var(--danger);
}

.spinner-custom {
  display: inline-block;
  width: 18px;
  height: 18px;
  border: 2px solid transparent;
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}
.spinner-custom-sm {
  width: 14px;
  height: 14px;
  border-width: 2px;
}
@keyframes spin {
  to { transform: rotate(360deg); }
}

.modal-content {
  border: none;
  border-radius: var(--radius-lg) !important;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  overflow: hidden;
}
.modal-header-custom {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 24px;
  border-bottom: 1px solid var(--border);
  background: var(--surface);
}
.modal-header-left {
  display: flex;
  align-items: center;
  gap: 12px;
}
.modal-header-icon {
  width: 38px;
  height: 38px;
  border-radius: var(--radius-sm);
  background: var(--primary-bg);
  color: var(--primary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 16px;
}
.modal-header-custom .modal-title {
  font-size: 17px;
  font-weight: 700;
  color: var(--text);
  margin: 0;
  font-family: var(--font);
}
.btn-close-custom {
  width: 34px;
  height: 34px;
  border-radius: var(--radius-sm);
  border: none;
  background: transparent;
  color: var(--text-secondary);
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 16px;
  transition: background 0.15s;
}
.btn-close-custom:hover {
  background: var(--bg);
}
.modal-body-custom {
  padding: 24px;
  background: var(--surface);
}
.modal-footer-custom {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 16px 24px;
  border-top: 1px solid var(--border);
  background: var(--bg);
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr 1fr;
  gap: 16px;
}
.form-grid-2col {
  grid-template-columns: 1fr 1fr;
}
.form-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.form-field-full {
  grid-column: 1 / -1;
}
.field-label {
  font-size: 13px;
  font-weight: 600;
  color: var(--text);
}
.required {
  color: var(--danger);
}
.field-select,
.field-input {
  width: 100%;
  padding: 9px 12px;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  font-size: 13px;
  font-family: var(--font);
  color: var(--text);
  background: var(--surface);
  transition: border-color 0.15s;
}
.field-select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236B7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 10px center;
  padding-right: 30px;
}
.field-select:focus,
.field-input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px var(--primary-bg);
}
.field-error {
  border-color: var(--danger) !important;
}
.field-feedback {
  font-size: 12px;
  color: var(--danger);
  font-weight: 500;
}
.field-textarea {
  width: 100%;
  padding: 9px 12px;
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  font-size: 13px;
  font-family: var(--font);
  color: var(--text);
  resize: vertical;
  transition: border-color 0.15s;
}
.field-textarea:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px var(--primary-bg);
}

.pase-config {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 14px;
  margin-bottom: 24px;
}
.pase-table-section {
  margin-top: 4px;
}
.pase-table-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 10px;
  flex-wrap: wrap;
  gap: 8px;
}
.pase-count {
  font-size: 13px;
  font-weight: 600;
  color: var(--text-secondary);
}
.pase-bulk-actions {
  display: flex;
  gap: 6px;
}
.btn-bulk {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 5px 12px;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border);
  font-size: 12px;
  font-weight: 600;
  font-family: var(--font);
  cursor: pointer;
  transition: all 0.15s;
  background: var(--surface);
}
.btn-bulk-success {
  color: var(--success);
  border-color: var(--success);
}
.btn-bulk-success:hover {
  background: var(--success-bg);
}
.btn-bulk-danger {
  color: var(--danger);
  border-color: var(--danger);
}
.btn-bulk-danger:hover {
  background: var(--danger-light);
}
.btn-bulk-info {
  color: var(--info);
  border-color: var(--info);
}
.btn-bulk-info:hover {
  background: var(--info-bg);
}
.pase-table-scroll {
  border: 1px solid var(--border);
  border-radius: var(--radius-sm);
  max-height: 340px;
  overflow-y: auto;
}
.pase-table-scroll .table-custom thead {
  position: sticky;
  top: 0;
  z-index: 1;
}
.pase-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  padding: 40px 16px;
  color: var(--text-secondary);
  font-size: 14px;
}
.pase-empty-icon {
  font-size: 36px;
  opacity: 0.3;
}

.radio-custom {
  accent-color: var(--primary);
  width: 16px;
  height: 16px;
  cursor: pointer;
}
</style>
