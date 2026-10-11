<template>
  <div>
    <div class="module-header">
      <div class="header-info">
        <div class="header-icon"><i class="fas fa-graduation-cap"></i></div>
        <div>
          <h1 class="header-title">Especialidades y Materias</h1>
          <p class="header-subtitle">Gestión académica</p>
        </div>
      </div>
    </div>

    <div class="tabs-nav">
      <button :class="['tab-btn', { active: activeTab === 'materias' }]" @click="activeTab = 'materias'"><i class="fas fa-book"></i> Materias</button>
      <button :class="['tab-btn', { active: activeTab === 'especialidades' }]" @click="activeTab = 'especialidades'"><i class="fas fa-graduation-cap"></i> Especialidades</button>
      <button :class="['tab-btn', { active: activeTab === 'planes' }]" @click="activeTab = 'planes'; fetchPlanes()"><i class="fas fa-layer-group"></i> Planes de Estudio</button>
    </div>

    <!-- Materias -->
    <div v-if="activeTab === 'materias'" class="card">
      <div class="card-header"><i class="fas fa-book"></i> Materias
        <button class="btn btn-sm btn-secondary ms-auto me-2" @click="$refs.bulkUpload?.show()"><i class="fas fa-file-import"></i> Importar</button>
        <button class="btn btn-primary btn-sm" @click="openMateriaModal(null)"><i class="fas fa-plus"></i> Nueva</button></div>
      <div class="card-body p-0">
        <div class="p-3 border-bottom">
          <div class="filter-chips">
            <SearchSelect v-model="filtroEspecialidad" :options="especialidadFilterOptions" placeholder="Todas las especialidades" @update:model-value="fetchMaterias" />
            <SearchSelect v-model="filtroSemestre" :options="semestreFilterOptions" placeholder="Todos los semestres" @update:model-value="fetchMaterias" />
          </div>
        </div>
        <div v-if="materias.length === 0" class="empty-state">
          <div class="empty-illustration"><i class="fas fa-book"></i></div>
          <h3>Sin materias</h3>
        </div>
        <table v-else class="data-table">
          <thead><tr><th>Nombre</th><th>Especialidad</th><th>Semestre</th><th></th></tr></thead>
          <tbody>
            <tr v-for="m in materias" :key="m.id">
              <td class="fw-semibold">{{ m.nombre }}</td>
              <td>{{ getEspecialidadName(m.especialidad_id) }}</td>
              <td><span class="badge badge-neutral" v-if="m.semestre">{{ m.semestre }}°</span></td>
              <td><div class="action-group"><button class="action-btn" @click="openMateriaModal(m)"><i class="fas fa-pen-to-square"></i></button><button class="action-btn action-delete" @click="deleteMateria(m)"><i class="fas fa-trash-can"></i></button></div></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Especialidades -->
    <div v-if="activeTab === 'especialidades'" class="card">
      <div class="card-header"><i class="fas fa-graduation-cap"></i> Especialidades <button class="btn btn-primary btn-sm ms-auto" @click="openEspecialidadModal(null)"><i class="fas fa-plus"></i> Nueva</button></div>
      <div class="card-body p-0">
        <div v-if="especialidades.length === 0" class="empty-state">
          <div class="empty-illustration"><i class="fas fa-graduation-cap"></i></div>
          <h3>Sin especialidades</h3>
        </div>
        <table v-else class="data-table">
          <thead><tr><th>Nombre</th><th>Descripción</th><th>Materias</th><th></th></tr></thead>
          <tbody>
            <tr v-for="e in especialidades" :key="e.id">
              <td class="fw-semibold">{{ e.nombre }}</td>
              <td class="text-muted small">{{ e.descripcion || '-' }}</td>
              <td><span class="badge badge-neutral">{{ getMateriasCount(e.id) }}</span></td>
              <td><div class="action-group"><button class="action-btn" @click="openEspecialidadModal(e)"><i class="fas fa-pen-to-square"></i></button><button class="action-btn action-delete" @click="deleteEspecialidad(e)"><i class="fas fa-trash-can"></i></button></div></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Materia Modal -->
    <div class="modal fade" ref="materiaModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <div class="modal-icon" :class="editMateriaId ? 'icon-edit' : 'icon-create'"><i :class="editMateriaId ? 'fas fa-pen-to-square' : 'fas fa-plus'"></i></div>
            <div><h5 class="modal-title">{{ editMateriaId ? 'Editar Materia' : 'Nueva Materia' }}</h5></div>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="form-field mb-3"><label>Especialidad <span class="req">*</span></label><SearchSelect v-model="materiaForm.especialidad_id" :options="especialidadFormOptions" placeholder="Todas las especialidades" /></div>
            <div class="form-field mb-3"><label>Nombre <span class="req">*</span></label><input v-model="materiaForm.nombre" class="field-input" /></div>
            <div class="form-field mb-3"><label>Semestre <span class="req">*</span></label><SearchSelect v-model="materiaForm.semestre" :options="semestreFormOptions" placeholder="Seleccionar" /></div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary" @click="saveMateria" :disabled="saving"><i v-if="saving" class="fas fa-circle-notch fa-spin"></i><i v-else class="fas fa-floppy-disk"></i> Guardar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Especialidad Modal -->
    <div class="modal fade" ref="especialidadModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <div class="modal-icon" :class="editEspecialidadId ? 'icon-edit' : 'icon-create'"><i :class="editEspecialidadId ? 'fas fa-pen-to-square' : 'fas fa-plus'"></i></div>
            <div><h5 class="modal-title">{{ editEspecialidadId ? 'Editar Especialidad' : 'Nueva Especialidad' }}</h5></div>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="form-field mb-3"><label>Nombre <span class="req">*</span></label><input v-model="espForm.nombre" class="field-input" /></div>
            <div class="form-field mb-3"><label>Descripción</label><textarea v-model="espForm.descripcion" class="field-input field-textarea" rows="2"></textarea></div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary" @click="saveEspecialidad" :disabled="saving"><i v-if="saving" class="fas fa-circle-notch fa-spin"></i><i v-else class="fas fa-floppy-disk"></i> Guardar</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Planes -->
    <div v-if="activeTab === 'planes'" class="card">
      <div class="card-header"><i class="fas fa-layer-group"></i> Planes de Estudio <button class="btn btn-primary btn-sm ms-auto" @click="openPlanModal(null)"><i class="fas fa-plus"></i> Nuevo</button></div>
      <div class="card-body p-0">
        <div v-if="planes.length === 0" class="empty-state"><div class="empty-illustration"><i class="fas fa-layer-group"></i></div><h3>Sin planes</h3></div>
        <table v-else class="data-table">
          <thead><tr><th>Nombre</th><th>Especialidad</th><th>Materias</th><th></th></tr></thead>
          <tbody>
            <tr v-for="p in planes" :key="p.id">
              <td class="fw-semibold">{{ p.nombre }}</td>
              <td>{{ p.especialidad?.nombre || '-' }}</td>
              <td><span class="badge badge-neutral">{{ (p.materias||[]).length }}</span></td>
              <td><div class="action-group"><button class="action-btn" @click="openPlanModal(p)"><i class="fas fa-pen-to-square"></i></button><button class="action-btn" @click="openPlanMaterias(p)"><i class="fas fa-book"></i></button><button class="action-btn action-delete" @click="deletePlan(p)"><i class="fas fa-trash-can"></i></button></div></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Plan Modal -->
    <div class="modal fade" ref="planModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header"><div class="modal-icon" :class="editPlanId?'icon-edit':'icon-create'"><i :class="editPlanId?'fas fa-pen-to-square':'fas fa-plus'"></i></div><div><h5 class="modal-title">{{ editPlanId?'Editar':'Nuevo' }} Plan</h5></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
          <div class="modal-body">
            <div class="form-field mb-3"><label>Especialidad <span class="req">*</span></label><SearchSelect v-model="planForm.especialidad_id" :options="especialidadPlanOptions" placeholder="Seleccionar" /></div>
            <div class="form-field mb-3"><label>Nombre <span class="req">*</span></label><input v-model="planForm.nombre" class="field-input" /></div>
            <div class="form-field mb-3"><label>Descripción</label><textarea v-model="planForm.descripcion" class="field-input field-textarea" rows="2"></textarea></div>
          </div>
          <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary" @click="savePlan" :disabled="saving"><i v-if="saving" class="fas fa-circle-notch fa-spin"></i><i v-else class="fas fa-floppy-disk"></i> Guardar</button></div>
        </div>
      </div>
    </div>

    <!-- Materias del Plan Modal -->
    <div class="modal fade" ref="planMateriasModal" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header"><div class="modal-icon icon-create"><i class="fas fa-book"></i></div><div><h5 class="modal-title">Materias - {{ planSelected?.nombre }}</h5></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
          <div class="modal-body">
            <div v-for="m in materiasDelPlan()" :key="m.id" class="plan-materia-row">
              <label class="plan-check"><input type="checkbox" :value="m.id" v-model="planMateriasSelected" /> {{ m.nombre }}</label>
            </div>
          </div>
          <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-primary" @click="savePlanMaterias" :disabled="saving"><i v-if="saving" class="fas fa-circle-notch fa-spin"></i><i v-else class="fas fa-floppy-disk"></i> Guardar Materias</button></div>
        </div>
      </div>
    </div>
    <BulkUploadModal ref="bulkUpload" type="materias" title="Materias" templateLabel="Descargar plantilla Excel" @imported="fetchMaterias" />
  </div>
</template>

<script>
import api from '@/api/axios'
import Swal from 'sweetalert2'
import { Modal } from 'bootstrap'
import SearchSelect from '@/components/ui/SearchSelect.vue'
import BulkUploadModal from '@/components/ui/BulkUploadModal.vue'

export default {
  name: 'MateriasView',
  components: { SearchSelect, BulkUploadModal },
  data() {
    return {
      activeTab: 'materias',
      materias: [], especialidades: [],
      filtroEspecialidad: '', filtroSemestre: '',
      materiaForm: { nombre: '', clave: '', especialidad_id: '', semestre: '' }, editMateriaId: null,
      espForm: { nombre: '', clave: '', descripcion: '' }, editEspecialidadId: null,
      saving: false, materiaModal: null, especialidadModal: null,
      planes: [], planForm: { nombre: '', especialidad_id: '', descripcion: '' }, editPlanId: null, planModal: null,
      planSelected: null, planMateriasSelected: [], planMateriasSemestre: {}, planMateriasHoras: {}, planMateriasModal: null,
    }
  },
  computed: {
    especialidadFilterOptions() {
      return [{ value: '', label: 'Todas las especialidades' }, ...this.especialidades.map(e => ({ value: e.id, label: e.nombre }))]
    },
    semestreFilterOptions() {
      return [{ value: '', label: 'Todos los semestres' }, ...[1, 2, 3, 4, 5, 6].map(s => ({ value: s, label: s + '°' }))]
    },
    especialidadFormOptions() {
      return [{ value: '', label: 'Todas las especialidades' }, ...this.especialidades.map(e => ({ value: e.id, label: e.nombre }))]
    },
    semestreFormOptions() {
      return [{ value: '', label: 'Seleccionar' }, ...[1, 2, 3, 4, 5, 6].map(s => ({ value: s, label: s + '°' }))]
    },
    especialidadPlanOptions() {
      return [{ value: '', label: 'Seleccionar' }, ...this.especialidades.map(e => ({ value: e.id, label: e.nombre }))]
    },
  },
  mounted() { this.fetchMaterias(); this.fetchEspecialidades() },
  methods: {
    getEspecialidadName(id) { if (!id) return 'Todas'; const e = this.especialidades.find(x => x.id === id); return e ? e.nombre : '-' },
    getMateriasCount(espId) { return this.materias.filter(m => !m.especialidad_id || m.especialidad_id === espId).length },

    async fetchMaterias() {
      try {
        const params = { per_page: 100 }
        if (this.filtroEspecialidad) params.especialidad_id = this.filtroEspecialidad
        if (this.filtroSemestre) params.semestre = this.filtroSemestre
        const { data } = await api.get('/materias', { params })
        this.materias = data.data || data || []
      } catch (e) { /* */ }
    },
    async fetchEspecialidades() {
      try { const { data } = await api.get('/especialidades?per_page=100'); this.especialidades = data.data || data || [] } catch (e) { /* */ }
    },

    openMateriaModal(item) {
      this.editMateriaId = item?.id || null
      this.materiaForm = item ? { ...item } : { nombre: '', especialidad_id: '', semestre: '' }
      if (!this.materiaModal) this.materiaModal = new Modal(this.$refs.materiaModal)
      this.materiaModal.show()
    },
    async saveMateria() {
      this.saving = true
      try {
        if (this.editMateriaId) { await api.put('/materias/' + this.editMateriaId, this.materiaForm) }
        else { await api.post('/materias', this.materiaForm) }
        this.materiaModal.hide(); this.fetchMaterias()
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Materia guardada', showConfirmButton: false, timer: 2000, timerProgressBar: true })
      } catch (e) { Swal.fire('Error', 'No se pudo guardar.', 'error') }
      this.saving = false
    },
    async deleteMateria(m) {
      const r = await Swal.fire({ title: 'Eliminar', text: `¿Eliminar ${m.nombre}?`, icon: 'warning', showCancelButton: true, confirmButtonText: 'Eliminar', confirmButtonColor: '#DC2626' })
      if (!r.isConfirmed) return
      try { await api.delete('/materias/' + m.id); this.fetchMaterias(); Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Eliminada', showConfirmButton: false, timer: 2000 }) } catch (e) { /* */ }
    },

    openEspecialidadModal(item) {
      this.editEspecialidadId = item?.id || null
      this.espForm = item ? { ...item } : { nombre: '', descripcion: '' }
      if (!this.especialidadModal) this.especialidadModal = new Modal(this.$refs.especialidadModal)
      this.especialidadModal.show()
    },
    async saveEspecialidad() {
      this.saving = true
      try {
        if (this.editEspecialidadId) { await api.put('/especialidades/' + this.editEspecialidadId, this.espForm) }
        else { await api.post('/especialidades', this.espForm) }
        this.especialidadModal.hide(); this.fetchEspecialidades()
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Especialidad guardada', showConfirmButton: false, timer: 2000, timerProgressBar: true })
      } catch (e) { Swal.fire('Error', 'No se pudo guardar.', 'error') }
      this.saving = false
    },
    async deleteEspecialidad(e) {
      const r = await Swal.fire({ title: 'Eliminar', text: `¿Eliminar ${e.nombre}?`, icon: 'warning', showCancelButton: true, confirmButtonText: 'Eliminar', confirmButtonColor: '#DC2626' })
      if (!r.isConfirmed) return
      try { await api.delete('/especialidades/' + e.id); this.fetchEspecialidades(); this.fetchMaterias(); Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Eliminada', showConfirmButton: false, timer: 2000 }) } catch (e) { /* */ }
    },

    async fetchPlanes() { try { const { data } = await api.get('/planes-estudio'); this.planes = data.data || data || [] } catch (e) {} },
    materiasDelPlan() { return this.materias.filter(m => !m.especialidad_id || m.especialidad_id === this.planSelected?.especialidad_id) },
    openPlanModal(item) { this.editPlanId = item?.id || null; this.planForm = item ? { nombre: item.nombre, especialidad_id: item.especialidad_id, descripcion: item.descripcion || '' } : { nombre: '', especialidad_id: '', descripcion: '' }; if (!this.planModal) this.planModal = new Modal(this.$refs.planModal); this.planModal.show() },
    async savePlan() { this.saving = true; try { if (this.editPlanId) { await api.put('/planes-estudio/' + this.editPlanId, this.planForm) } else { await api.post('/planes-estudio', this.planForm) }; this.planModal.hide(); this.fetchPlanes(); Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Guardado', showConfirmButton: false, timer: 2000 }) } catch (e) { Swal.fire('Error', 'No se pudo guardar.', 'error') }; this.saving = false },
    async deletePlan(p) { const r = await Swal.fire({ title: 'Eliminar', text: `¿Eliminar ${p.nombre}?`, icon: 'warning', showCancelButton: true, confirmButtonText: 'Eliminar', confirmButtonColor: '#DC2626' }); if (!r.isConfirmed) return; try { await api.delete('/planes-estudio/' + p.id); this.fetchPlanes(); Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Eliminado', showConfirmButton: false, timer: 2000 }) } catch (e) {} },
    openPlanMaterias(p) { this.planSelected = p; this.planMateriasSelected = (p.materias || []).map(m => m.id); this.planMateriasSemestre = {}; this.planMateriasHoras = {}; (p.materias || []).forEach(m => { if (m.pivot) { this.planMateriasSemestre[m.id] = m.pivot.semestre || ''; this.planMateriasHoras[m.id] = m.pivot.horas || '' } }); if (!this.planMateriasModal) this.planMateriasModal = new Modal(this.$refs.planMateriasModal); this.planMateriasModal.show() },
    async savePlanMaterias() { this.saving = true; try { const materias = this.planMateriasSelected.map(id => ({ id })); await api.post('/planes-estudio/' + this.planSelected.id + '/materias', { materias }); this.planMateriasModal.hide(); this.fetchPlanes(); Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Materias actualizadas', showConfirmButton: false, timer: 2000 }) } catch (e) { Swal.fire('Error', 'No se pudo guardar.', 'error') }; this.saving = false },
  },
}
</script>

<style scoped>
.module-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; gap: 1rem; }
.header-info { display: flex; align-items: center; gap: 1rem; }
.header-icon { width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover)); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.35rem; box-shadow: 0 4px 12px rgba(169,30,34,0.25); }
.header-title { font-size: 1.5rem; font-weight: 700; margin: 0; }
.header-subtitle { font-size: .8125rem; color: var(--color-text-secondary); margin: 2px 0 0; }
.tabs-nav { display: flex; gap: .375rem; margin-bottom: 1.5rem; background: var(--color-bg); border: 1px solid var(--color-border-light); border-radius: 14px; padding: .375rem; }
.tab-btn { padding: .5rem 1.125rem; border: none; border-radius: 10px; font-size: .8125rem; font-weight: 500; cursor: pointer; color: var(--color-text-secondary); background: none; display: flex; align-items: center; gap: .375rem; transition: all .15s; }
.tab-btn:hover { color: var(--color-text); }
.tab-btn.active { background: var(--color-primary); color: #fff; font-weight: 600; }
.card { border: 1px solid var(--color-border-light); border-radius: var(--radius-xl); box-shadow: var(--shadow-xs); overflow: hidden; background: var(--color-bg); }
.card-header { display: flex; align-items: center; gap: .5rem; padding: .875rem 1.25rem; background: var(--color-bg-tertiary); border-bottom: 1px solid var(--color-border-light); font-weight: 600; font-size: .875rem; }
.card-header i { color: var(--color-primary); }
.card-body { padding: 1.25rem; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table thead th { text-align: left; padding: .75rem 1rem; font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--color-text-muted); background: var(--color-bg-tertiary); border-bottom: 1px solid var(--color-border-light); }
.data-table tbody td { padding: .75rem 1rem; font-size: .875rem; color: var(--color-text); border-bottom: 1px solid var(--color-border-light); }
.data-table tbody tr:hover { background: var(--color-bg-tertiary); }
.data-table tbody tr:last-child td { border-bottom: none; }
.fw-semibold { font-weight: 600; }
.matricula-code { font-weight: 600; font-size: .8125rem; color: var(--color-primary); background: var(--color-primary-light); padding: .2rem .5rem; border-radius: 6px; }
.action-group { display: flex; gap: .25rem; justify-content: flex-end; }
.action-btn { width: 32px; height: 32px; border: none; border-radius: 8px; background: none; color: var(--color-text-secondary); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: .8125rem; transition: all .15s; }
.action-btn:hover { background: var(--color-bg-secondary); color: var(--color-text); transform: scale(1.08); }
.action-delete:hover { color: #DC2626; background: #FEF2F2; }
.empty-state { display: flex; flex-direction: column; align-items: center; padding: 3rem 2rem; text-align: center; }
.empty-illustration { width: 64px; height: 64px; border-radius: 50%; background: var(--color-bg-secondary); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; font-size: 1.5rem; color: var(--color-text-muted); }
.empty-state h3 { font-size: 1rem; font-weight: 600; margin: 0; }
.filter-chips { display: flex; gap: .5rem; }
.filter-select { padding: .5rem 2rem .5rem .75rem; border: 1px solid var(--color-border); border-radius: 10px; font-size: .8125rem; color: var(--color-text); appearance: none; background: var(--color-bg) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%238B949E' d='M6 8L1 3h10z'/%3E%3C/svg%3E") no-repeat right .6rem center; cursor: pointer; }
.modal-content { border: none; border-radius: 20px; overflow: hidden; }
.modal-header { padding: 1.5rem 1.75rem 1rem; border-bottom: none; display: flex; align-items: flex-start; gap: .875rem; }
.modal-icon { width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; color: #fff; }
.icon-create { background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover)); }
.icon-edit { background: linear-gradient(135deg, #D97706, #B45309); }
.modal-title { font-size: 1.125rem; font-weight: 700; margin: 0; }
.modal-body { padding: 0 1.75rem 1.5rem; }
.modal-footer { padding: 1rem 1.75rem; border-top: 1px solid var(--color-border-light); background: var(--color-bg-tertiary); display: flex; justify-content: flex-end; gap: .625rem; }
.form-field { display: flex; flex-direction: column; gap: .3rem; }
.form-field label { font-size: .8125rem; font-weight: 500; }
.form-field .req { color: var(--color-danger); }
.form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
.field-input { padding: .5625rem .75rem; border: 1.5px solid var(--color-border); border-radius: 10px; font-size: .875rem; font-family: var(--font-family); color: var(--color-text); background: var(--color-bg); outline: none; transition: border-color .2s; }
.field-input:focus { border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(169,30,34,0.08); }
.field-textarea { resize: vertical; min-height: 50px; }
.plan-materia-row { display: flex; align-items: center; gap: .5rem; padding: .5rem 0; border-bottom: 1px solid var(--color-border-light); }
.plan-check { flex: 1; display: flex; align-items: center; gap: .375rem; font-size: .8125rem; cursor: pointer; }
.plan-check input { width: 16px; height: 16px; cursor: pointer; }
</style>
