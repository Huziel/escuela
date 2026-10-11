<template>
  <div class="regularizacion-page">
    <div class="page-header">
      <div class="page-header-left">
        <span class="page-header-icon">
          <i class="fas fa-rotate-right"></i>
        </span>
        <div class="page-header-info">
          <h3 class="page-header-title">Regularizaci&oacute;n</h3>
          <p class="page-header-subtitle">Control de ex&aacute;menes de regularizaci&oacute;n y pagos</p>
        </div>
      </div>
      <div class="page-header-actions">
        <button class="btn btn-primary-custom" @click="openCreateModal">
          <i class="fas fa-plus"></i>
          <span>Nueva Regularizaci&oacute;n</span>
        </button>
      </div>
    </div>

    <div class="card filter-card">
      <div class="filter-bar">
        <div class="filter-group">
          <label class="filter-label"><i class="fas fa-search"></i> Buscar</label>
          <input
            type="text"
            class="filter-input"
            v-model="searchText"
            placeholder="Buscar por alumno, materia, estatus..."
          />
        </div>
        <div class="filter-group">
          <label class="filter-label"><i class="fas fa-flag"></i> Estatus</label>
          <SearchSelect v-model="statusFilter" :options="statusFilterOptions" placeholder="Todos" />
        </div>
        <div class="filter-group">
          <label class="filter-label"><i class="fas fa-money-bill"></i> Pago</label>
          <SearchSelect v-model="paymentFilter" :options="paymentFilterOptions" placeholder="Todos" />
        </div>
      </div>
    </div>

    <div class="card table-card">
      <div class="table-wrap">
        <table class="table-custom">
          <thead>
            <tr>
              <th>Alumno</th>
              <th>Materia</th>
              <th class="cell-center">Periodo</th>
              <th class="cell-center">Fecha Examen</th>
              <th class="cell-center">Calificaci&oacute;n</th>
              <th class="cell-center">Estatus</th>
              <th class="cell-end">Pago</th>
              <th class="cell-center">Pagado</th>
              <th class="cell-end">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
              <td colspan="9" class="cell-empty">
                <div class="empty-state">
                  <div class="spinner-custom"></div>
                  <span>Cargando...</span>
                </div>
              </td>
            </tr>
            <tr v-else-if="filteredItems.length === 0">
              <td colspan="9" class="cell-empty">
                <div class="empty-state">
                  <i class="fas fa-rotate-right empty-icon"></i>
                  <span>Sin registros</span>
                </div>
              </td>
            </tr>
            <tr v-for="item in filteredItems" :key="item.id" v-else>
              <td class="cell-strong">{{ item.alumno?.nombre_completo || item.alumno?.nombre || '&mdash;' }}</td>
              <td>{{ item.materia?.nombre || '&mdash;' }}</td>
              <td class="cell-center">{{ item.periodo || '&mdash;' }}</td>
              <td class="cell-center">{{ formatDate(item.fecha_examen) }}</td>
              <td class="cell-center cell-highlight">{{ item.calificacion != null ? Number(item.calificacion).toFixed(2) : '&mdash;' }}</td>
              <td class="cell-center">
                <span :class="estatusBadge(item.estatus)">{{ item.estatus || '&mdash;' }}</span>
              </td>
              <td class="cell-end cell-highlight">${{ formatCurrency(item.pago) }}</td>
              <td class="cell-center">
                <span :class="pagadoBadge(item.pagado)">
                  <i :class="item.pagado ? 'fas fa-check-circle' : 'fas fa-times-circle'"></i>
                  {{ item.pagado ? ' Pagado' : ' Pendiente' }}
                </span>
              </td>
              <td class="cell-end">
                <div class="action-group">
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
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header-custom">
            <div class="modal-header-left">
              <span class="modal-header-icon"><i class="fas fa-rotate-right"></i></span>
              <h5 class="modal-title" id="formModalLabel">
                {{ isEditing ? 'Editar Regularizaci&oacute;n' : 'Nueva Regularizaci&oacute;n' }}
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
                <label class="field-label">Materia <span class="required">*</span></label>
                <SearchSelect v-model="form.materia_id" :options="materiaFormOptions" placeholder="Seleccionar..." :class="{ 'field-error': formErrors.materia_id }" />
                <div class="field-feedback" v-if="formErrors.materia_id">{{ formErrors.materia_id }}</div>
              </div>
              <div class="form-field">
                <label class="field-label">Calificaci&oacute;n</label>
                <SearchSelect v-model="form.calificacion_id" :options="calificacionFormOptions" placeholder="Seleccionar..." />
              </div>
              <div class="form-field">
                <label class="field-label">Periodo <span class="required">*</span></label>
                <SearchSelect v-model="form.periodo" :options="periodoFormOptions" placeholder="Seleccionar..." :class="{ 'field-error': formErrors.periodo }" />
                <div class="field-feedback" v-if="formErrors.periodo">{{ formErrors.periodo }}</div>
              </div>
              <div class="form-field">
                <label class="field-label">Fecha Examen</label>
                <input type="date" class="field-input" v-model="form.fecha_examen" />
              </div>
              <div class="form-field">
                <label class="field-label">Calificaci&oacute;n</label>
                <input type="number" step="0.01" min="0" max="10" class="field-input" v-model.number="form.calificacion" placeholder="0.00" />
              </div>
              <div class="form-field">
                <label class="field-label">Estatus</label>
                <SearchSelect v-model="form.estatus" :options="estatusFormOptions" placeholder="Seleccionar..." />
              </div>
              <div class="form-field">
                <label class="field-label">Pago $</label>
                <input type="number" step="0.01" min="0" class="field-input" v-model.number="form.pago" placeholder="0.00" />
              </div>
              <div class="form-field form-field-check">
                <div class="checkbox-custom">
                  <input type="checkbox" id="formPagado" v-model="form.pagado" />
                  <label for="formPagado">Pagado</label>
                </div>
              </div>
              <div class="form-field form-field-full">
                <label class="field-label">Observaciones</label>
                <textarea class="field-textarea" rows="2" v-model="form.observaciones"></textarea>
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
  </div>
</template>

<script>
import api from '@/api/axios'
import Swal from 'sweetalert2'
import { Modal } from 'bootstrap'
import SearchSelect from '@/components/ui/SearchSelect.vue'

export default {
  name: 'RegularizacionList',
  components: { SearchSelect },
  data() {
    const currentYear = new Date().getFullYear()
    return {
      items: [],
      alumnos: [],
      materias: [],
      calificaciones: [],
      periodos: [
        `${currentYear}-${currentYear + 1}`,
        `${currentYear - 1}-${currentYear}`,
      ],
      loading: false,
      saving: false,
      isEditing: false,
      editingId: null,
      formModalInstance: null,
      searchText: '',
      statusFilter: '',
      paymentFilter: '',
      form: {
        alumno_id: '',
        materia_id: '',
        calificacion_id: '',
        periodo: '',
        fecha_examen: '',
        calificacion: null,
        estatus: 'pendiente',
        pago: null,
        pagado: false,
        observaciones: '',
      },
      formErrors: {},
    }
  },
  computed: {
    filteredItems() {
      let list = this.items
      const q = this.searchText.toLowerCase()
      if (q) {
        list = list.filter((item) => {
          const alumno = (item.alumno?.nombre_completo || item.alumno?.nombre || '').toLowerCase()
          const materia = (item.materia?.nombre || '').toLowerCase()
          const estatus = (item.estatus || '').toLowerCase()
          return alumno.includes(q) || materia.includes(q) || estatus.includes(q)
        })
      }
      if (this.statusFilter) {
        list = list.filter((item) => item.estatus === this.statusFilter)
      }
      if (this.paymentFilter === 'pagado') {
        list = list.filter((item) => item.pagado)
      } else if (this.paymentFilter === 'pendiente') {
        list = list.filter((item) => !item.pagado)
      }
      return list
    },
    statusFilterOptions() {
      return [
        { value: '', label: 'Todos' },
        { value: 'pendiente', label: 'Pendiente' },
        { value: 'aprobado', label: 'Aprobado' },
        { value: 'reprobado', label: 'Reprobado' },
      ]
    },
    paymentFilterOptions() {
      return [
        { value: '', label: 'Todos' },
        { value: 'pagado', label: 'Pagado' },
        { value: 'pendiente', label: 'Pendiente' },
      ]
    },
    alumnoFormOptions() {
      return [{ value: '', label: 'Seleccionar...' }, ...this.alumnos.map(a => ({ value: a.id, label: a.nombre_completo || a.nombre }))]
    },
    materiaFormOptions() {
      return [{ value: '', label: 'Seleccionar...' }, ...this.materias.map(m => ({ value: m.id, label: m.nombre }))]
    },
    calificacionFormOptions() {
      return [{ value: '', label: 'Seleccionar...' }, ...this.calificaciones.map(c => ({
        value: c.id,
        label: (c.alumno?.nombre_completo || c.alumno?.nombre) + ' | ' + (c.materia?.nombre || '') + ' (' + (c.periodo || '') + ')',
      }))]
    },
    periodoFormOptions() {
      return [{ value: '', label: 'Seleccionar...' }, ...this.periodos.map(p => ({ value: p, label: p }))]
    },
    estatusFormOptions() {
      return [
        { value: 'pendiente', label: 'Pendiente' },
        { value: 'aprobado', label: 'Aprobado' },
        { value: 'reprobado', label: 'Reprobado' },
      ]
    },
  },
  mounted() {
    this.formModalInstance = new Modal(this.$refs.formModalRef)
    this.fetchAll()
  },
  methods: {
    async fetchAll() {
      this.loading = true
      try {
        await Promise.all([
          this.fetchRegularizacion(),
          this.fetchAlumnos(),
          this.fetchMaterias(),
          this.fetchCalificaciones(),
        ])
      } catch (e) {
        Swal.fire('Error', 'No se pudieron cargar los datos', 'error')
      } finally {
        this.loading = false
      }
    },
    async fetchRegularizacion() {
      const { data } = await api.get('/regularizacion')
      this.items = data.data || data || []
    },
    async fetchAlumnos() {
      const { data } = await api.get('/alumnos')
      this.alumnos = data.data || data || []
    },
    async fetchMaterias() {
      const { data } = await api.get('/materias')
      this.materias = data.data || data || []
    },
    async fetchCalificaciones() {
      const { data } = await api.get('/calificaciones')
      this.calificaciones = data.data || data || []
    },
    formatDate(dateStr) {
      if (!dateStr) return '&mdash;'
      const d = new Date(dateStr)
      return d.toLocaleDateString('es-MX', { year: 'numeric', month: '2-digit', day: '2-digit' })
    },
    formatCurrency(value) {
      if (value == null || value === '') return '0.00'
      return Number(value).toFixed(2)
    },
    estatusBadge(estatus) {
      return {
        'badge-custom': true,
        'badge-warning': estatus === 'pendiente',
        'badge-success': estatus === 'aprobado',
        'badge-danger': estatus === 'reprobado',
        'badge-muted': !estatus,
      }
    },
    pagadoBadge(pagado) {
      return {
        'badge-custom': true,
        'badge-success': pagado,
        'badge-muted': !pagado,
      }
    },
    openCreateModal() {
      this.isEditing = false
      this.editingId = null
      this.formErrors = {}
      this.form = {
        alumno_id: '',
        materia_id: '',
        calificacion_id: '',
        periodo: '',
        fecha_examen: '',
        calificacion: null,
        estatus: 'pendiente',
        pago: null,
        pagado: false,
        observaciones: '',
      }
      this.formModalInstance.show()
    },
    openEditModal(item) {
      this.isEditing = true
      this.editingId = item.id
      this.formErrors = {}
      this.form = {
        alumno_id: item.alumno_id,
        materia_id: item.materia_id,
        calificacion_id: item.calificacion_id || '',
        periodo: item.periodo || '',
        fecha_examen: item.fecha_examen || '',
        calificacion: item.calificacion,
        estatus: item.estatus || 'pendiente',
        pago: item.pago,
        pagado: !!item.pagado,
        observaciones: item.observaciones || '',
      }
      this.formModalInstance.show()
    },
    hideFormModal() {
      this.formModalInstance.hide()
    },
    validateForm() {
      this.formErrors = {}
      if (!this.form.alumno_id) this.formErrors.alumno_id = 'Seleccione un alumno'
      if (!this.form.materia_id) this.formErrors.materia_id = 'Seleccione una materia'
      if (!this.form.periodo) this.formErrors.periodo = 'Seleccione un periodo'
      return Object.keys(this.formErrors).length === 0
    },
    async handleSave() {
      if (!this.validateForm()) return
      this.saving = true
      try {
        const payload = { ...this.form }
        if (this.isEditing) {
          await api.put(`/regularizacion/${this.editingId}`, payload)
          Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Regularizaci&oacute;n actualizada', showConfirmButton: false, timer: 2000, timerProgressBar: true })
        } else {
          await api.post('/regularizacion', payload)
          Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Regularizaci&oacute;n creada', showConfirmButton: false, timer: 2000, timerProgressBar: true })
        }
        this.hideFormModal()
        this.fetchRegularizacion()
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
        title: '¿Eliminar regularizaci&oacute;n?',
        text: `¿Deseas eliminar la regularizaci&oacute;n de ${nombre}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'S&iacute;, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
      })
      if (!result.isConfirmed) return
      try {
        await api.delete(`/regularizacion/${item.id}`)
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Regularizaci&oacute;n eliminada', showConfirmButton: false, timer: 2000, timerProgressBar: true })
        this.fetchRegularizacion()
      } catch (e) {
        const msg = e.response?.data?.message || 'Error al eliminar'
        Swal.fire('Error', msg, 'error')
      }
    },
  },
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

.regularizacion-page {
  --primary: #A91E22;
  --primary-light: #C53030;
  --primary-bg: #FEF2F2;
  --success: #059669;
  --success-bg: #ECFDF5;
  --danger: #DC2626;
  --danger-light: #FEE2E2;
  --warning: #D97706;
  --warning-bg: #FFFBEB;
  --text: #1F2937;
  --text-secondary: #6B7280;
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
  min-width: 170px;
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
.table-wrap {
  overflow-x: auto;
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
.cell-highlight {
  font-weight: 700;
  color: var(--primary);
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
.badge-warning {
  background: var(--warning-bg);
  color: var(--warning);
}
.badge-danger {
  background: var(--danger-light);
  color: var(--danger);
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
  grid-template-columns: 1fr 1fr;
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
.form-field-check {
  display: flex;
  align-items: flex-end;
  padding-bottom: 2px;
}
.checkbox-custom {
  display: flex;
  align-items: center;
  gap: 8px;
}
.checkbox-custom input[type="checkbox"] {
  accent-color: var(--primary);
  width: 17px;
  height: 17px;
  cursor: pointer;
}
.checkbox-custom label {
  font-size: 13px;
  font-weight: 600;
  color: var(--text);
  cursor: pointer;
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
</style>
