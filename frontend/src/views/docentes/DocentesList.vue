<template>
  <div>
    <div class="module-header">
      <div class="header-info">
        <div class="header-icon"><i class="fas fa-chalkboard-user"></i></div>
        <div>
          <h1 class="header-title">Docentes</h1>
          <p class="header-subtitle" v-if="hasSearched">{{ items.length }} de {{ totalItems }} docentes</p>
          <p class="header-subtitle" v-else>Gestión de docentes</p>
        </div>
      </div>
      <button class="btn-create" v-if="hasSearched" @click="openCreate"><i class="fas fa-plus"></i><span>Nuevo Docente</span></button>
      <button class="btn btn-sm btn-secondary" @click="$refs.bulkUpload?.show()">
        <i class="fas fa-file-import"></i> Importar
      </button>
    </div>

    <div class="filter-strip" v-if="hasSearched">
      <div class="filter-search">
        <i class="fas fa-search"></i>
        <input v-model="filters.search" type="text" placeholder="Buscar por nombre, email o número de control..." @input="debounceSearch" />
        <button v-if="filters.search" class="filter-clear" @click="filters.search = ''; fetchData()"><i class="fas fa-times"></i></button>
      </div>
      <div class="filter-chips">
        <SearchSelect v-model="filters.especialidad_id" :options="especialidadFilterOptions" placeholder="Todas las especialidades" @update:model-value="fetchData" />
        <SearchSelect v-model="filters.estatus" :options="estatusFilterOptions" placeholder="Todos los estados" @update:model-value="fetchData" />
        <button v-if="filters.estatus || filters.search || filters.especialidad_id" class="filter-reset" @click="filters = { search: '', estatus: '', especialidad_id: '', page: 1 }; fetchData()"><i class="fas fa-arrows-rotate"></i> Limpiar</button>
      </div>
    </div>

    <div v-if="!hasSearched" class="initial-state">
      <div class="initial-illustration"><i class="fas fa-search"></i></div>
      <h3>Busca un docente</h3>
      <p>Escribe un nombre o aplica filtros para encontrar docentes</p>
      <div class="initial-actions">
        <button class="btn btn-primary" @click="fetchData"><i class="fas fa-list"></i> Mostrar todos</button>
        <button class="btn btn-outline-secondary" @click="openCreate"><i class="fas fa-plus"></i> Nuevo docente</button>
      </div>
    </div>

    <div class="table-card" v-if="hasSearched">
      <div class="table-loading" v-if="loading"><div class="loading-pulse"></div><p>Cargando docentes...</p></div>
      <template v-else>
        <div v-if="items.length > 0" class="table-responsive">
          <table class="data-table">
          <thead>
            <tr>
              <th>N° Control</th>
              <th>Docente</th>
              <th>Grupos</th>
              <th>Estado</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in items" :key="item.id" class="table-row" @click="$router.push('/docentes/' + item.id)">
              <td data-label="N° Control"><span class="matricula-code">{{ item.numero_empleado }}</span></td>
              <td data-label="Docente">
                <div class="student-cell">
                  <img v-if="item.usuario?.fotografia" :src="item.usuario.fotografia" class="student-photo" />
                  <div v-else class="student-avatar" :style="{ background: avatarColor(item.usuario?.nombre_completo || '') }">{{ initials(item.usuario?.nombre_completo || '') }}</div>
                  <div class="student-info">
                    <span class="student-name">{{ item.usuario?.nombre_completo || '-' }}</span>
                    <span class="student-email">{{ (item.especialidades||[]).map(e=>e.nombre).join(', ') || item.especialidad_nombre || item.especialidad || item.grado_academico || '-' }}</span>
                  </div>
                </div>
              </td>
              <td data-label="Grupos">
                <div class="grupos-tags">
                  <span v-if="!item.grupos || item.grupos.length === 0" class="text-muted small">Sin grupo</span>
                  <span v-for="g in item.grupos" :key="g.id" class="grupo-tag">{{ g.nombre }}</span>
                </div>
              </td>
              <td data-label="Estado"><span class="status-dot" :class="item.estatus"></span> {{ item.estatus }}</td>
              <td class="col-acciones" data-label="Acciones" @click.stop>
                <div class="action-group">
                  <button class="action-btn" title="Editar" @click="edit(item)"><i class="fas fa-pen-to-square"></i></button>
                  <button class="action-btn action-delete" title="Eliminar" @click="confirmDelete(item)"><i class="fas fa-trash-can"></i></button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        </div>
        <div v-else class="empty-state">
          <div class="empty-illustration"><i class="fas fa-users-slash"></i></div>
          <h3>No se encontraron docentes</h3>
          <p v-if="filters.estatus || filters.especialidad_id || filters.search">Intenta ajustar los filtros</p>
          <p v-else>Aún no hay docentes registrados</p>
        </div>
      </template>
      <div v-if="totalPages > 1 && !loading" class="table-pagination">
        <div class="pagination-info">Página {{ page }} de {{ totalPages }}</div>
        <div class="pagination-buttons">
          <button :disabled="page <= 1" @click="page--; fetchData()"><i class="fas fa-chevron-left"></i></button>
          <button v-for="p in visiblePages" :key="p" :class="{ active: p === page }" @click="page = p; fetchData()">{{ p }}</button>
          <button :disabled="page >= totalPages" @click="page++; fetchData()"><i class="fas fa-chevron-right"></i></button>
        </div>
      </div>
    </div>

    <div class="modal fade" ref="modal" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <div class="modal-icon" :class="editando ? 'icon-edit' : 'icon-create'"><i :class="editando ? 'fas fa-pen-to-square' : 'fas fa-plus'"></i></div>
            <div>
              <h5 class="modal-title">{{ editando ? 'Editar Docente' : 'Nuevo Docente' }}</h5>
              <p class="modal-subtitle">{{ editando ? 'Modifica los datos del docente' : 'Registra un nuevo docente en el sistema' }}</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="form-section" v-if="editando">
              <h6 class="section-label">Foto del Docente</h6>
              <div class="photo-upload-row">
                <div class="photo-preview-lg" v-if="editPhotoPreview || form.fotoActual">
                  <img :src="editPhotoPreview || form.fotoActual" alt="Foto" />
                  <button class="photo-remove" @click="editPhoto = null; editPhotoPreview = null; form.fotoActual = null"><i class="fas fa-times"></i></button>
                </div>
                <label class="photo-placeholder-lg" v-else>
                  <i class="fas fa-camera fa-2x"></i><span>Subir foto</span>
                  <input type="file" accept="image/*" hidden @change="onEditPhotoSelect" />
                </label>
                <label v-if="editPhotoPreview || form.fotoActual" class="photo-change-btn">
                  <i class="fas fa-arrow-up-from-bracket"></i> Cambiar
                  <input type="file" accept="image/*" hidden @change="onEditPhotoSelect" />
                </label>
              </div>
            </div>

            <div class="form-section" v-if="!editando">
              <h6 class="section-label">Datos de Usuario</h6>
              <div class="form-grid">
                <div class="form-field">
                  <label>Foto</label>
                  <div class="photo-upload">
                    <div class="photo-preview" v-if="newUserPhotoPreview">
                      <img :src="newUserPhotoPreview" alt="Preview" />
                      <button class="photo-remove" @click="newUserPhoto = null; newUserPhotoPreview = null"><i class="fas fa-times"></i></button>
                    </div>
                    <label v-else class="photo-placeholder">
                      <i class="fas fa-camera"></i><span>Subir foto</span>
                      <input type="file" accept="image/*" hidden @change="onPhotoSelect" />
                    </label>
                  </div>
                </div>
                <div class="form-field">
                  <label>Nombre(s) <span class="req">*</span></label>
                  <input v-model="newUser.nombre" class="field-input" :class="{ 'has-error': newUserErrors.nombre }" placeholder="Nombre(s)" @blur="generateUsername" />
                  <span v-if="newUserErrors.nombre" class="field-error">{{ newUserErrors.nombre }}</span>
                </div>
                <div class="form-field">
                  <label>Apellido Paterno <span class="req">*</span></label>
                  <input v-model="newUser.apellido_paterno" class="field-input" :class="{ 'has-error': newUserErrors.apellido_paterno }" placeholder="Paterno" @blur="generateUsername" />
                  <span v-if="newUserErrors.apellido_paterno" class="field-error">{{ newUserErrors.apellido_paterno }}</span>
                </div>
                <div class="form-field">
                  <label>Apellido Materno</label>
                  <input v-model="newUser.apellido_materno" class="field-input" placeholder="Materno" @blur="generateUsername" />
                </div>
                <div class="form-field">
                  <label>Email <span class="req">*</span></label>
                  <input v-model="newUser.email" type="email" class="field-input" :class="{ 'has-error': newUserErrors.email }" placeholder="correo@cetis.edu.mx" />
                  <span v-if="newUserErrors.email" class="field-error">{{ newUserErrors.email }}</span>
                </div>
                <div class="form-field">
                  <label>Username <span class="req">*</span></label>
                  <input v-model="newUser.username" class="field-input" :class="{ 'has-error': newUserErrors.username }" placeholder="Se genera automáticamente" />
                  <span v-if="newUserErrors.username" class="field-error">{{ newUserErrors.username }}</span>
                </div>
                <div class="form-field">
                  <label>Contraseña <span class="req">*</span></label>
                  <input v-model="newUser.password" type="password" class="field-input" :class="{ 'has-error': newUserErrors.password }" placeholder="Mínimo 8 caracteres" />
                  <span v-if="newUserErrors.password" class="field-error">{{ newUserErrors.password }}</span>
                </div>
              </div>
            </div>

            <div class="form-section">
              <h6 class="section-label">Información Laboral</h6>
              <div class="form-grid">
                <div class="form-field">
                  <label>CURP</label>
                  <input v-model="form.curp" class="field-input" placeholder="CURP" />
                </div>
                <div class="form-field">
                  <label>RFC</label>
                  <input v-model="form.rfc" class="field-input" placeholder="RFC" />
                </div>
                <div class="form-field">
                  <label>Especialidades</label>
                  <div class="especialidad-checks">
                    <label class="esp-check esp-todas">
                      <input type="checkbox" :checked="allEspSelected" @change="toggleAllEsp" />
                      <span>Todas</span>
                    </label>
                    <label v-for="e in especialidades" :key="e.id" class="esp-check">
                      <input type="checkbox" :value="e.id" v-model="form.especialidad_ids" />
                      <span>{{ e.nombre }}</span>
                    </label>
                  </div>
                </div>
                <div class="form-field">
                  <label>Grado Académico</label>
                  <input v-model="form.grado_academico" class="field-input" placeholder="Licenciatura, Maestría..." />
                </div>
              </div>
            </div>
            <div class="form-section">
              <h6 class="section-label">Estado</h6>
              <div class="form-grid cols-2">
                <div class="form-field">
                  <label>Estatus <span class="req">*</span></label>
                  <SearchSelect v-model="form.estatus" :options="estatusFormOptions" placeholder="Seleccionar estatus" />
                </div>
                <div class="form-field">
                  <label>Fecha Ingreso <span class="req">*</span></label>
                  <input v-model="form.fecha_ingreso" type="date" class="field-input" :class="{ 'has-error': errors.fecha_ingreso }" />
                  <span v-if="errors.fecha_ingreso" class="field-error">{{ errors.fecha_ingreso }}</span>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary btn-save" :disabled="saving" @click="save">
              <i v-if="saving" class="fas fa-circle-notch fa-spin"></i>
              <i v-else class="fas fa-floppy-disk"></i>
              {{ saving ? 'Guardando...' : 'Guardar Docente' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <BulkUploadModal ref="bulkUpload" type="docentes" title="Docentes" templateLabel="Descargar plantilla Excel" @imported="fetchData" />
  </div>
</template>

<script>
import api from '@/api/axios'
import Swal from 'sweetalert2'
import { Modal } from 'bootstrap'
import SearchSelect from '@/components/ui/SearchSelect.vue'
import BulkUploadModal from '@/components/ui/BulkUploadModal.vue'

export default {
  name: 'DocentesList',
  components: { SearchSelect, BulkUploadModal },
  data() {
    return {
      items: [], totalItems: 0, loading: false, saving: false, editando: false,
      editingId: null, page: 1, totalPages: 1, perPage: 12,
      hasSearched: false, searchTimer: null,
      filters: { search: '', estatus: '', especialidad_id: '', page: 1 },
      newUser: { nombre: '', apellido_paterno: '', apellido_materno: '', email: '', username: '', password: '' },
      newUserPhoto: null, newUserPhotoPreview: null,
      editPhoto: null, editPhotoPreview: null, editingUserId: null,
      newUserErrors: {},
      form: { id: null, numero_empleado: '', curp: '', rfc: '', especialidad_id: '', especialidad_ids: [], grado_academico: '', estatus: 'activo', fecha_ingreso: '' },
      especialidades: [],
      errors: {},
      modalInstance: null,
    }
  },
  computed: {
    visiblePages() { const p = []; for (let i = Math.max(1, this.page - 2); i <= Math.min(this.totalPages, this.page + 2); i++) p.push(i); return p },
    estatusFilterOptions() {
      return [
        { value: '', label: 'Todos los estados' },
        { value: 'activo', label: 'Activo' },
        { value: 'inactivo', label: 'Inactivo' },
      ]
    },
    especialidadFilterOptions() {
      return [
        { value: '', label: 'Todas las especialidades' },
        ...(this.especialidades || []).map(e => ({ value: e.id, label: e.nombre })),
      ]
    },
    allEspSelected() {
      return this.especialidades.length > 0 && this.form.especialidad_ids.length === this.especialidades.length
    },
    estatusFormOptions() {
      return [
        { value: 'activo', label: 'Activo' },
        { value: 'inactivo', label: 'Inactivo' },
      ]
    },
  },
  mounted() { this.modalInstance = new Modal(this.$refs.modal); this.fetchEspecialidades() },
  methods: {
    initials(name) {
      if (!name) return '?'
      const parts = name.trim().split(' ')
      return (parts[0][0] + (parts.length > 1 ? parts[parts.length - 1][0] : '')).toUpperCase()
    },
    avatarColor(name) {
      const colors = ['#A91E22', '#B99A53', '#1E40AF', '#059669', '#7C3AED', '#D97706', '#0D9488']
      let hash = 0; for (let i = 0; i < (name || '').length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
      return colors[Math.abs(hash) % colors.length]
    },
    generateUsername() {
      const n = (this.newUser.nombre || '').trim().toLowerCase().replace(/\s+/g, '')
      const a = ((this.newUser.apellido_paterno || '') + (this.newUser.apellido_materno || '')).trim().toLowerCase().replace(/\s+/g, '')
      if (n && a) {
        const username = n + '.' + a + (Math.floor(Math.random() * 900000) + 100000)
        this.newUser.username = username
        this.newUser.email = username + '@cetis.edu.mx'
      }
    },
    debounceSearch() { clearTimeout(this.searchTimer); this.searchTimer = setTimeout(() => { this.page = 1; this.fetchData() }, 350) },
    async fetchEspecialidades() {
      try {
        const { data } = await api.get('/especialidades?per_page=100')
        this.especialidades = data.data || data || []
      } catch (e) { /* */ }
    },
    async fetchData() {
      this.hasSearched = true; this.loading = true
      try {
        const params = { page: this.page, per_page: this.perPage }
        if (this.filters.search) params.search = this.filters.search
        if (this.filters.estatus) params.estatus = this.filters.estatus
        if (this.filters.especialidad_id) params.especialidad_id = this.filters.especialidad_id
        const res = await api.get('/docentes', { params })
        this.items = res.data.data || []
        this.totalPages = res.data.meta?.last_page || 1
        this.totalItems = res.data.meta?.total || 0
        this.page = res.data.meta?.current_page || 1
      } catch (e) { this.items = [] }
      this.loading = false
    },
    openCreate() { this.editando = false; this.newUser = { nombre: '', apellido_paterno: '', apellido_materno: '', email: '', username: '', password: '' }; this.newUserPhoto = null; this.newUserPhotoPreview = null; this.newUserErrors = {}; this.resetForm(); this.modalInstance.show() },
    toggleAllEsp() {
      if (this.allEspSelected) this.form.especialidad_ids = []
      else this.form.especialidad_ids = this.especialidades.map(e => e.id)
    },
    onPhotoSelect(e) {
      const file = e.target.files[0]; if (!file) return; this.newUserPhoto = file
      const reader = new FileReader(); reader.onload = (ev) => { this.newUserPhotoPreview = ev.target.result }; reader.readAsDataURL(file)
    },
    onEditPhotoSelect(e) {
      const file = e.target.files[0]; if (!file) return; this.editPhoto = file
      const reader = new FileReader(); reader.onload = (ev) => { this.editPhotoPreview = ev.target.result }; reader.readAsDataURL(file)
    },
    edit(item) {
      this.editando = true; this.editingId = item.id
      this.editPhoto = null; this.editPhotoPreview = null
      this.editingUserId = item.usuario?.id || null
      this.form = {
        id: item.id, numero_empleado: item.numero_empleado || '', curp: item.curp || '', rfc: item.rfc || '',
        especialidad_id: item.especialidad_id || '', especialidad_ids: (item.especialidades||[]).map(e=>e.id), grado_academico: item.grado_academico || '',
        estatus: item.estatus || 'activo', fecha_ingreso: item.fecha_ingreso || '',
        fotoActual: item.usuario?.fotografia || null,
      }
      this.modalInstance.show()
    },
    resetForm() {
      this.form = { id: null, numero_empleado: '', curp: '', rfc: '', especialidad_id: '', especialidad_ids: [], grado_academico: '', estatus: 'activo', fecha_ingreso: '' }
      this.errors = {}
    },
    async save() {
      this.errors = {}; this.newUserErrors = {}
      if (!this.editando) {
        if (!this.newUser.nombre) this.newUserErrors.nombre = 'Requerido'
        if (!this.newUser.apellido_paterno) this.newUserErrors.apellido_paterno = 'Requerido'
        if (!this.newUser.email) this.newUserErrors.email = 'Requerido'
        if (!this.newUser.username) this.newUserErrors.username = 'Requerido'
        if (!this.newUser.password || this.newUser.password.length < 8) this.newUserErrors.password = 'Mínimo 8 caracteres'
        if (Object.keys(this.newUserErrors).length) return
      }
      this.saving = true
      try {
        if (!this.editando) {
          const userRes = await api.post('/usuarios', {
            nombre: this.newUser.nombre, apellido_paterno: this.newUser.apellido_paterno,
            apellido_materno: this.newUser.apellido_materno || null, email: this.newUser.email,
            username: this.newUser.username, password: this.newUser.password, rol: 'docente', activo: true,
          })
          this.form.user_id = userRes.data.data?.id || userRes.data.id
          if (this.newUserPhoto) {
            const fd = new FormData(); fd.append('foto', this.newUserPhoto)
            await api.post('/usuarios/' + this.form.user_id + '/foto', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
          }
          await api.post('/docentes', this.form)
        } else {
          const payload = { ...this.form }; delete payload.id; delete payload.fotoActual
          await api.put('/docentes/' + this.editingId, payload)
          if (this.editPhoto && this.editingUserId) {
            const fd = new FormData(); fd.append('foto', this.editPhoto)
            await api.post('/usuarios/' + this.editingUserId + '/foto', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
          }
        }
        this.modalInstance.hide(); this.fetchData()
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: this.editando ? 'Docente actualizado' : 'Docente creado', showConfirmButton: false, timer: 2000, timerProgressBar: true })
      } catch (e) {
        if (e.response?.status === 422) {
          const errs = e.response.data.errors || {}
          for (const key in errs) this.errors[key] = Array.isArray(errs[key]) ? errs[key][0] : errs[key]
        }
      }
      this.saving = false
    },
    async confirmDelete(item) {
      const result = await Swal.fire({ title: '¿Eliminar docente?', html: `<p>Se eliminará a <strong>${item.usuario?.nombre_completo || item.numero_empleado}</strong>.</p>`, icon: 'warning', showCancelButton: true, confirmButtonColor: '#DC2626', confirmButtonText: 'Eliminar', cancelButtonText: 'Cancelar' })
      if (!result.isConfirmed) return
      try { await api.delete('/docentes/' + item.id); this.fetchData(); Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Docente eliminado', showConfirmButton: false, timer: 2000 }) } catch (e) { Swal.fire('Error', 'No se pudo eliminar.', 'error') }
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
.filter-search { position: relative; flex: 1; min-width: 220px; max-width: 380px; }
.filter-search i { position: absolute; left: .875rem; top: 50%; transform: translateY(-50%); color: var(--color-text-muted); font-size: .875rem; }
.filter-search input { width: 100%; padding: .5rem .875rem .5rem 2.5rem; border: none; border-radius: 10px; background: var(--color-bg-secondary); font-size: .875rem; color: var(--color-text); outline: none; transition: all .2s; }
.filter-search input:focus { background: var(--color-bg); box-shadow: 0 0 0 3px rgba(169,30,34,0.08); }
.filter-clear { position: absolute; right: .5rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--color-text-muted); cursor: pointer; padding: .25rem; border-radius: 50%; font-size: .75rem; }
.filter-chips { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
.filter-select { padding: .5rem 2rem .5rem .75rem; border: 1px solid var(--color-border); border-radius: 10px; font-size: .8125rem; color: var(--color-text); background: var(--color-bg); cursor: pointer; outline: none; appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236B7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right .6rem center; }
.filter-reset { display: inline-flex; align-items: center; gap: .375rem; padding: .5rem .875rem; border-radius: 10px; border: 1px dashed var(--color-border); background: none; font-size: .8125rem; color: var(--color-text-secondary); cursor: pointer; transition: all .2s; }
.filter-reset:hover { border-color: var(--color-primary); color: var(--color-primary); background: var(--color-primary-light); }
.table-card { background: var(--color-bg); border: 1px solid var(--color-border-light); border-radius: 18px; overflow: hidden; box-shadow: var(--shadow-xs); }
.table-loading { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4rem 2rem; color: var(--color-text-muted); gap: 1rem; }
.loading-pulse { width: 40px; height: 40px; border-radius: 50%; border: 3px solid var(--color-border); border-top-color: var(--color-primary); animation: spin .7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.data-table { width: 100%; border-collapse: collapse; }
.data-table thead th { text-align: left; padding: .75rem 1rem; font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--color-text-muted); background: var(--color-bg-tertiary); border-bottom: 1px solid var(--color-border-light); }
.data-table tbody td { padding: .75rem 1rem; font-size: .875rem; color: var(--color-text); border-bottom: 1px solid var(--color-border-light); vertical-align: middle; }
.table-row { cursor: pointer; transition: background .15s; }
.table-row:hover { background: var(--color-bg-tertiary); }
.table-row:last-child td { border-bottom: none; }
.matricula-code { font-weight: 600; font-family: var(--font-family-mono); font-size: .8125rem; color: var(--color-primary); background: var(--color-primary-light); padding: .2rem .5rem; border-radius: 6px; }
.student-cell { display: flex; align-items: center; gap: .625rem; }
.student-photo { width: 36px; height: 36px; border-radius: 10px; object-fit: cover; flex-shrink: 0; }
.student-avatar { width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; color: #fff; font-size: .8125rem; font-weight: 700; }
.student-info { display: flex; flex-direction: column; min-width: 0; }
.student-name { font-weight: 600; font-size: .875rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.student-email { font-size: .75rem; color: var(--color-text-muted); }
.status-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: .375rem; }
.status-dot.activo { background: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,0.15); }
.status-dot.inactivo { background: #6B7280; box-shadow: 0 0 0 3px rgba(107,114,128,0.15); }
.action-group { display: flex; gap: .25rem; justify-content: flex-end; }
.action-btn { width: 34px; height: 34px; border: none; border-radius: 8px; background: none; color: var(--color-text-secondary); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: .875rem; transition: all .15s; }
.action-btn:hover { background: var(--color-bg-secondary); color: var(--color-text); transform: scale(1.08); }
.action-delete:hover { color: #DC2626; background: #FEF2F2; }

.grupos-tags { display: flex; flex-wrap: wrap; gap: .25rem; }
.grupo-tag { padding: .15rem .5rem; border-radius: 6px; background: var(--color-bg-secondary); font-size: .75rem; color: var(--color-text-secondary); font-weight: 500; }

.photo-upload { display: flex; align-items: center; }
.photo-preview { position: relative; width: 64px; height: 64px; border-radius: 12px; overflow: hidden; }
.photo-preview img { width: 100%; height: 100%; object-fit: cover; }
.photo-remove { position: absolute; top: -4px; right: -4px; width: 20px; height: 20px; border-radius: 50%; border: none; background: var(--color-danger); color: #fff; cursor: pointer; font-size: .625rem; display: flex; align-items: center; justify-content: center; }
.photo-placeholder { width: 64px; height: 64px; border-radius: 12px; border: 2px dashed var(--color-border); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; cursor: pointer; font-size: .75rem; color: var(--color-text-muted); transition: all .15s; }
.photo-placeholder:hover { border-color: var(--color-primary); color: var(--color-primary); background: var(--color-primary-light); }
.photo-upload-row { display: flex; align-items: center; gap: 1rem; }
.photo-preview-lg { position: relative; width: 96px; height: 96px; border-radius: 16px; overflow: hidden; flex-shrink: 0; }
.photo-preview-lg img { width: 100%; height: 100%; object-fit: cover; }
.photo-placeholder-lg { width: 96px; height: 96px; border-radius: 16px; border: 2px dashed var(--color-border); display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; cursor: pointer; transition: all .15s; color: var(--color-text-muted); }
.photo-placeholder-lg:hover { border-color: var(--color-primary); color: var(--color-primary); background: var(--color-primary-light); }
.photo-change-btn { padding: .5rem 1rem; border-radius: 8px; border: 1px solid var(--color-border); font-size: .8125rem; cursor: pointer; color: var(--color-primary); display: inline-flex; align-items: center; gap: .375rem; transition: all .15s; }
.photo-change-btn:hover { background: var(--color-primary-light); border-color: var(--color-primary); }

.empty-state { display: flex; flex-direction: column; align-items: center; padding: 4rem 2rem; text-align: center; }
.empty-illustration { width: 80px; height: 80px; border-radius: 50%; background: var(--color-bg-secondary); display: flex; align-items: center; justify-content: center; margin-bottom: 1.25rem; font-size: 2rem; color: var(--color-text-muted); }
.empty-state h3 { font-size: 1.125rem; font-weight: 600; color: var(--color-text); margin: 0 0 .25rem; }
.empty-state p { font-size: .875rem; color: var(--color-text-secondary); margin: 0; }
.table-pagination { display: flex; align-items: center; justify-content: space-between; padding: .875rem 1.25rem; border-top: 1px solid var(--color-border-light); flex-wrap: wrap; gap: .75rem; }
.pagination-info { font-size: .8125rem; color: var(--color-text-muted); }
.pagination-buttons { display: flex; gap: .25rem; }
.pagination-buttons button { min-width: 34px; height: 34px; border: 1px solid var(--color-border); border-radius: 8px; background: var(--color-bg); cursor: pointer; font-size: .8125rem; font-weight: 500; color: var(--color-text-secondary); display: flex; align-items: center; justify-content: center; transition: all .15s; }
.pagination-buttons button:hover:not(:disabled) { background: var(--color-bg-secondary); }
.pagination-buttons button.active { background: var(--color-primary); color: #fff; border-color: var(--color-primary); }
.pagination-buttons button:disabled { opacity: .35; cursor: not-allowed; }
.initial-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4rem 2rem; text-align: center; }
.initial-illustration { width: 96px; height: 96px; border-radius: 50%; background: var(--color-bg-secondary); display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem; font-size: 2.5rem; color: var(--color-text-muted); }
.initial-state h3 { font-size: 1.25rem; font-weight: 600; margin: 0 0 .375rem; }
.initial-state p { font-size: .875rem; color: var(--color-text-secondary); margin: 0 0 1.5rem; max-width: 380px; }
.initial-actions { display: flex; gap: .625rem; }
.modal-content { border: none; border-radius: 20px; overflow: hidden; }
.modal-header { padding: 1.5rem 1.75rem 1rem; border-bottom: none; display: flex; align-items: flex-start; gap: .875rem; }
.modal-icon { width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; color: #fff; }
.icon-create { background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover)); }
.icon-edit { background: linear-gradient(135deg, #D97706, #B45309); }
.modal-title { font-size: 1.125rem; font-weight: 700; margin: 0; }
.modal-subtitle { font-size: .8125rem; color: var(--color-text-muted); margin: 2px 0 0; }
.modal-body { padding: 0 1.75rem 1.5rem; }
.modal-footer { padding: 1rem 1.75rem; border-top: 1px solid var(--color-border-light); background: var(--color-bg-tertiary); display: flex; justify-content: flex-end; gap: .625rem; }
.btn-save { padding: .5625rem 1.5rem; font-weight: 600; }
.form-section { margin-bottom: 1.5rem; }
.form-section:last-child { margin-bottom: 0; }
.section-label { font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--color-text-muted); margin: 0 0 .875rem; padding-bottom: .5rem; border-bottom: 1px solid var(--color-border-light); }
.form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
.form-grid.cols-2 { grid-template-columns: repeat(2, 1fr); }
.form-field { display: flex; flex-direction: column; gap: .3rem; }
.form-field label { font-size: .8125rem; font-weight: 500; color: var(--color-text); }
.form-field .req { color: var(--color-danger); }
.field-input { padding: .5625rem .75rem; border: 1.5px solid var(--color-border); border-radius: 10px; font-size: .875rem; font-family: var(--font-family); color: var(--color-text); background: var(--color-bg); outline: none; transition: border-color .2s, box-shadow .2s; }
.field-input:focus { border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(169,30,34,0.08); }
.field-input.has-error { border-color: var(--color-danger); }
.field-error { font-size: .75rem; color: var(--color-danger); }
@media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } }

.especialidad-checks{display:flex;flex-wrap:wrap;gap:.375rem}
.esp-check{display:flex;align-items:center;gap:.375rem;padding:.375rem .625rem;border:1px solid var(--color-border);border-radius:8px;cursor:pointer;font-size:.8125rem;transition:all .15s}
.esp-check:hover{border-color:var(--color-primary)}
.esp-check input:checked+span{color:var(--color-primary);font-weight:600}
.esp-check:has(input:checked){background:var(--color-primary-light);border-color:var(--color-primary)}
</style>
