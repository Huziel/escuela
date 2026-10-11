<template>
  <div class="page-container">
    <div class="page-header">
      <div class="page-header__left">
        <i class="fas fa-file-signature page-header__icon"></i>
        <h1 class="page-title">Inscripciones</h1>
      </div>
      <button class="btn btn--primary" @click="openCreateModal">
        <i class="fas fa-plus btn__icon"></i>
        <span>Nueva Inscripción</span>
      </button>
    </div>

    <div class="card">
      <div class="card-body">
        <div class="filter-bar">
          <div class="filter-group">
            <label class="form-label">Estatus</label>
            <select v-model="filters.estatus" class="form-control" @change="fetchData">
              <option value="">Todos los estatus</option>
              <option value="pendiente">Pendiente</option>
              <option value="aprobada">Aprobada</option>
              <option value="rechazada">Rechazada</option>
            </select>
          </div>
          <div class="filter-group">
            <label class="form-label">Periodo</label>
            <select v-model="filters.periodo" class="form-control" @change="fetchData">
              <option value="">Todos los periodos</option>
              <option v-for="p in periodos" :key="p" :value="p">{{ p }}</option>
            </select>
          </div>
        </div>

        <BaseTable :columns="columns" :items="items" :loading="loading">
          <template #actions="{ item }">
            <div class="table-actions">
              <button class="btn-icon btn-icon--edit" @click="openEditModal(item)">
                <i class="fas fa-edit"></i>
              </button>
              <button
                v-if="item.estatus === 'pendiente'"
                class="btn-icon btn-icon--approve"
                @click="confirmAprobar(item)"
              >
                <i class="fas fa-check"></i>
              </button>
              <button
                v-if="item.estatus === 'pendiente'"
                class="btn-icon btn-icon--reject"
                @click="confirmRechazar(item)"
              >
                <i class="fas fa-times"></i>
              </button>
              <button class="btn-icon btn-icon--delete-outline" @click="confirmDelete(item)">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </template>
          <template #cell-alumno="{ value }">
            {{ value?.nombre || value?.name || value }}
          </template>
          <template #cell-grupo="{ value }">
            {{ value?.nombre || value?.name || value }}
          </template>
          <template #cell-estatus="{ value }">
            <span :class="statusClass(value)">{{ value }}</span>
          </template>
        </BaseTable>

        <BasePagination
          v-if="pagination.total > 0"
          :pagination="pagination"
          @page-change="onPageChange"
        />
      </div>
    </div>

    <BaseModal ref="formModal" :title="formTitle" modalId="inscripcionModal" size="lg">
      <div class="form-grid">
        <div class="form-group">
          <label for="alumno_id" class="form-label">Alumno <span class="required">*</span></label>
          <select v-model="form.alumno_id" id="alumno_id" class="form-control" required>
            <option value="">Seleccione...</option>
            <option v-for="a in alumnos" :key="a.id" :value="a.id">{{ a.nombre }}</option>
          </select>
        </div>
        <div class="form-group">
          <label for="grupo_id" class="form-label">Grupo <span class="required">*</span></label>
          <select v-model="form.grupo_id" id="grupo_id" class="form-control" required>
            <option value="">Seleccione...</option>
            <option v-for="g in grupos" :key="g.id" :value="g.id">{{ g.nombre }}</option>
          </select>
        </div>
        <div class="form-group">
          <label for="tipo" class="form-label">Tipo <span class="required">*</span></label>
          <select v-model="form.tipo" id="tipo" class="form-control" required>
            <option value="">Seleccione...</option>
            <option value="regular">Regular</option>
            <option value="extraordinario">Extraordinario</option>
            <option value="reingreso">Reingreso</option>
          </select>
        </div>
        <div class="form-group">
          <label for="periodo" class="form-label">Periodo <span class="required">*</span></label>
          <input v-model="form.periodo" id="periodo" type="text" class="form-control" required />
        </div>
        <div class="form-group">
          <label for="estatus" class="form-label">Estatus <span class="required">*</span></label>
          <select v-model="form.estatus" id="estatus" class="form-control" required>
            <option value="pendiente">Pendiente</option>
            <option value="aprobada">Aprobada</option>
            <option value="rechazada">Rechazada</option>
          </select>
        </div>
        <div class="form-group">
          <label for="documentos_completos" class="form-label">Documentos Completos</label>
          <select v-model="form.documentos_completos" id="documentos_completos" class="form-control">
            <option :value="false">No</option>
            <option :value="true">Sí</option>
          </select>
        </div>
        <div class="form-group form-group--full">
          <label for="observaciones" class="form-label">Observaciones</label>
          <textarea v-model="form.observaciones" id="observaciones" class="form-control form-control--textarea" rows="3"></textarea>
        </div>
      </div>
      <template #footer>
        <button class="btn btn--secondary" @click="closeModal">Cancelar</button>
        <button class="btn btn--primary" :disabled="saving" @click="save">
          <i v-if="saving" class="fas fa-spinner fa-spin"></i>
          Guardar
        </button>
      </template>
    </BaseModal>
  </div>
</template>

<script>
import api from '@/api/axios'
import Swal from 'sweetalert2'
import { Modal } from 'bootstrap'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseTable from '@/components/ui/BaseTable.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import BasePagination from '@/components/ui/BasePagination.vue'

export default {
  name: 'InscripcionesList',
  components: { BaseCard, BaseTable, BaseModal, BaseButton, BaseInput, BasePagination },
  data() {
    return {
      items: [],
      alumnos: [],
      grupos: [],
      periodos: [],
      loading: false,
      saving: false,
      editingId: null,
      columns: [
        { key: 'alumno', label: 'Alumno' },
        { key: 'grupo', label: 'Grupo' },
        { key: 'periodo', label: 'Periodo' },
        { key: 'tipo', label: 'Tipo' },
        { key: 'estatus', label: 'Estatus' },
        { key: 'fecha_solicitud', label: 'Fecha Solicitud' },
      ],
      filters: {
        estatus: '',
        periodo: '',
        page: 1,
      },
      pagination: {
        current_page: 1,
        last_page: 1,
        total: 0,
        per_page: 15,
      },
      form: {
        alumno_id: '',
        grupo_id: '',
        tipo: '',
        periodo: '',
        estatus: 'pendiente',
        documentos_completos: false,
        observaciones: '',
      },
    }
  },
  computed: {
    formTitle() {
      return this.editingId ? 'Editar Inscripción' : 'Nueva Inscripción'
    },
  },
  mounted() {
    this.fetchData()
    this.fetchAlumnos()
    this.fetchGrupos()
  },
  methods: {
    statusClass(estatus) {
      const map = {
        pendiente: 'badge bg-warning',
        aprobada: 'badge bg-success',
        rechazada: 'badge bg-danger',
      }
      return map[estatus] || 'badge bg-secondary'
    },
    async fetchData() {
      this.loading = true
      try {
        const { data } = await api.get('/inscripciones', { params: this.filters })
        this.items = data.data?.data || data.data || []
        this.pagination = data.data?.pagination || { current_page: 1, last_page: 1, total: 0, per_page: 15 }
        this.periodos = data.data?.periodos || []
      } catch (e) {
        console.error(e)
      } finally {
        this.loading = false
      }
    },
    async fetchAlumnos() {
      try {
        const { data } = await api.get('/alumnos', { params: { per_page: 500 } })
        this.alumnos = data.data || data || []
      } catch (e) {
        console.error(e)
      }
    },
    async fetchGrupos() {
      try {
        const { data } = await api.get('/grupos/all')
        this.grupos = data.data || data || []
      } catch (e) {
        console.error(e)
      }
    },
    onPageChange(page) {
      this.filters.page = page
      this.fetchData()
    },
    openCreateModal() {
      this.editingId = null
      this.resetForm()
      this.$refs.formModal.show()
    },
    openEditModal(item) {
      this.editingId = item.id
      this.form = {
        alumno_id: item.alumno_id || (item.alumno?.id) || '',
        grupo_id: item.grupo_id || (item.grupo?.id) || '',
        tipo: item.tipo || '',
        periodo: item.periodo || '',
        estatus: item.estatus || 'pendiente',
        documentos_completos: item.documentos_completos !== undefined ? item.documentos_completos : false,
        observaciones: item.observaciones || '',
      }
      this.$refs.formModal.show()
    },
    resetForm() {
      this.form = {
        alumno_id: '',
        grupo_id: '',
        tipo: '',
        periodo: '',
        estatus: 'pendiente',
        documentos_completos: false,
        observaciones: '',
      }
    },
    closeModal() {
      this.$refs.formModal.hide()
    },
    async save() {
      this.saving = true
      try {
        if (this.editingId) {
          await api.put(`/inscripciones/${this.editingId}`, this.form)
          Swal.fire('Actualizada', 'Inscripción actualizada correctamente.', 'success')
        } else {
          await api.post('/inscripciones', this.form)
          Swal.fire('Creada', 'Inscripción registrada correctamente.', 'success')
        }
        this.closeModal()
        this.fetchData()
      } catch (e) {
        console.error(e)
      } finally {
        this.saving = false
      }
    },
    confirmAprobar(item) {
      Swal.fire({
        title: 'Aprobar inscripción',
        text: `¿Estás seguro de aprobar la inscripción de ${item.alumno?.nombre || item.alumno_id}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, aprobar',
        cancelButtonText: 'Cancelar',
      }).then((result) => {
        if (result.isConfirmed) {
          this.aprobar(item.id)
        }
      })
    },
    async aprobar(id) {
      try {
        await api.post(`/inscripciones/${id}/aprobar`)
        Swal.fire('Aprobada', 'Inscripción aprobada correctamente.', 'success')
        this.fetchData()
      } catch (e) {
        console.error(e)
      }
    },
    confirmRechazar(item) {
      Swal.fire({
        title: 'Rechazar inscripción',
        text: `¿Estás seguro de rechazar la inscripción de ${item.alumno?.nombre || item.alumno_id}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, rechazar',
        cancelButtonText: 'Cancelar',
      }).then((result) => {
        if (result.isConfirmed) {
          this.rechazar(item.id)
        }
      })
    },
    async rechazar(id) {
      try {
        await api.post(`/inscripciones/${id}/rechazar`)
        Swal.fire('Rechazada', 'Inscripción rechazada correctamente.', 'success')
        this.fetchData()
      } catch (e) {
        console.error(e)
      }
    },
    confirmDelete(item) {
      Swal.fire({
        title: 'Eliminar inscripción',
        text: '¿Estás seguro de eliminar esta inscripción?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
      }).then((result) => {
        if (result.isConfirmed) {
          this.deleteItem(item.id)
        }
      })
    },
    async deleteItem(id) {
      try {
        await api.delete(`/inscripciones/${id}`)
        Swal.fire('Eliminada', 'Inscripción eliminada correctamente.', 'success')
        this.fetchData()
      } catch (e) {
        console.error(e)
      }
    },
  },
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

:root {
  --color-primary: #A91E22;
  --color-secondary: #B99A53;
  --color-text: #3E3E3E;
  --color-border: #E5E7EB;
}

*,
*::before,
*::after {
  box-sizing: border-box;
}

.page-container {
  font-family: 'Inter', sans-serif;
  color: var(--color-text);
  padding: 1.5rem;
}

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.page-header__left {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.page-header__icon {
  font-size: 1.5rem;
  color: var(--color-primary);
}

.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--color-primary);
  margin: 0;
  letter-spacing: -0.02em;
}

.card {
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 1px 2px rgba(0, 0, 0, 0.04);
  overflow: hidden;
}

.card-body {
  padding: 1.5rem;
}

.filter-bar {
  display: flex;
  align-items: flex-end;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.input-group {
  position: relative;
  display: flex;
  align-items: center;
}

.input-group__icon {
  position: absolute;
  left: 0.75rem;
  color: #9CA3AF;
  font-size: 0.875rem;
  pointer-events: none;
  z-index: 1;
}

.input-group .form-control {
  padding-left: 2.25rem;
}

.form-label {
  font-size: 0.8125rem;
  font-weight: 600;
  color: var(--color-text);
  text-transform: uppercase;
  letter-spacing: 0.025em;
}

.form-control {
  display: block;
  width: 100%;
  padding: 0.5rem 0.75rem;
  font-family: 'Inter', sans-serif;
  font-size: 0.875rem;
  color: var(--color-text);
  background-color: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: 8px;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
  outline: none;
  line-height: 1.5;
}

.form-control:focus {
  border-color: var(--color-primary);
  box-shadow: 0 0 0 3px rgba(169, 30, 34, 0.1);
}

.form-control::placeholder {
  color: #9CA3AF;
}

.form-control--textarea {
  resize: vertical;
  min-height: 80px;
}

select.form-control {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%233E3E3E' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  padding-right: 2.25rem;
  cursor: pointer;
}

.required {
  color: var(--color-primary);
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.5rem 1.25rem;
  font-family: 'Inter', sans-serif;
  font-size: 0.875rem;
  font-weight: 600;
  border-radius: 8px;
  border: 1px solid transparent;
  cursor: pointer;
  transition: all 0.15s ease;
  line-height: 1.5;
  white-space: nowrap;
}

.btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
}

.btn__icon {
  font-size: 0.8125rem;
}

.btn--primary {
  background-color: var(--color-primary);
  color: #ffffff;
  border-color: var(--color-primary);
}

.btn--primary:hover:not(:disabled) {
  background-color: #8E1A1D;
  border-color: #8E1A1D;
}

.btn--secondary {
  background-color: #ffffff;
  color: var(--color-text);
  border-color: var(--color-border);
}

.btn--secondary:hover:not(:disabled) {
  background-color: #F9FAFB;
  border-color: #D1D5DB;
}

.table-actions {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  justify-content: flex-end;
}

.btn-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: 1px solid var(--color-border);
  border-radius: 6px;
  background: #ffffff;
  color: var(--color-text);
  font-size: 0.8125rem;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-icon:hover {
  border-color: var(--color-primary);
  color: #ffffff;
  background: var(--color-primary);
}

.btn-icon--edit:hover {
  border-color: var(--color-primary);
  background: var(--color-primary);
}

.btn-icon--approve:hover {
  border-color: #059669;
  background: #059669;
}

.btn-icon--reject:hover {
  border-color: #DC2626;
  background: #DC2626;
}

.btn-icon--delete-outline {
  border-color: var(--color-border);
  background: transparent;
}

.btn-icon--delete-outline:hover {
  border-color: #DC2626;
  color: #DC2626;
  background: rgba(220, 38, 38, 0.08);
}

.badge {
  display: inline-block;
  padding: 0.2rem 0.625rem;
  font-size: 0.75rem;
  font-weight: 600;
  border-radius: 9999px;
  line-height: 1.4;
  text-transform: capitalize;
}

.badge.bg-warning {
  background-color: rgba(245, 158, 11, 0.12);
  color: #92400E;
}

.badge.bg-success {
  background-color: rgba(16, 185, 129, 0.12);
  color: #065F46;
}

.badge.bg-danger {
  background-color: rgba(220, 38, 38, 0.12);
  color: #991B1B;
}

.badge.bg-secondary {
  background-color: rgba(107, 114, 128, 0.12);
  color: #374151;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.375rem;
}

.form-group--full {
  grid-column: 1 / -1;
}

@media (max-width: 640px) {
  .form-grid {
    grid-template-columns: 1fr;
  }

  .form-group--full {
    grid-column: 1;
  }

  .page-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .filter-bar {
    flex-direction: column;
    align-items: stretch;
  }
}

:deep() .table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.875rem;
}

:deep() .table thead {
  background-color: #F9FAFB;
}

:deep() .table thead th {
  padding: 0.75rem 1rem;
  font-size: 0.75rem;
  font-weight: 600;
  color: #6B7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  text-align: left;
  border-bottom: 2px solid var(--color-border);
}

:deep() .table tbody td {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid var(--color-border);
  color: var(--color-text);
}

:deep() .table tbody tr:hover {
  background-color: #F9FAFB;
}

:deep() .table tbody tr:last-child td {
  border-bottom: none;
}

:deep() .modal-content {
  border: 1px solid var(--color-border);
  border-radius: 12px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
}

:deep() .modal-header {
  padding: 1.25rem 1.5rem;
  border-bottom: 1px solid var(--color-border);
}

:deep() .modal-title {
  font-size: 1.125rem;
  font-weight: 700;
  color: var(--color-primary);
}

:deep() .modal-body {
  padding: 1.5rem;
}

:deep() .modal-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--color-border);
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}

:deep() .modal-footer > * {
  margin: 0;
}
</style>
