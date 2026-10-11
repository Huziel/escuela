<template>
  <div class="alumnos-module">
    <div class="module-header">
      <div class="header-info">
        <div class="header-icon">
          <i class="fas fa-user-graduate"></i>
        </div>
        <div>
          <h1 class="header-title">Alumnos</h1>
          <p class="header-subtitle" v-if="hasSearched">
            {{ items.length }} de {{ totalItems }} estudiantes
            <span class="header-divider">|</span>
            <span class="text-success">{{ activeCount }} activos</span>
          </p>
          <p class="header-subtitle" v-else>Gestión de estudiantes</p>
        </div>
      </div>
      <button class="btn-create" @click="openCreate" v-if="hasSearched">
        <i class="fas fa-plus"></i>
        <span>Nuevo Alumno</span>
      </button>
      <button class="btn btn-sm btn-secondary" @click="$refs.bulkUpload?.show()">
        <i class="fas fa-file-import"></i> Importar
      </button>
      <button class="btn btn-sm btn-warning" @click="promoverAlumnos" v-if="hasSearched">
        <i class="fas fa-arrow-right"></i> Promover
      </button>
    </div>

    <div class="filter-strip" v-if="hasSearched">
      <div class="filter-search">
        <i class="fas fa-search"></i>
        <input
          v-model="search"
          type="text"
          placeholder="Buscar por matrícula, nombre o CURP..."
          @input="debounceSearch"
        />
        <button v-if="search" class="filter-clear" @click="search = ''; fetchData()">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="filter-chips">
        <SearchSelect v-model="filtroEspecialidad" :options="especialidadFilterOptions" placeholder="Todas las especialidades" @update:model-value="fetchData" />
        <SearchSelect v-model="filtroSemestre" :options="semestreFilterOptions" placeholder="Todos los semestres" @update:model-value="fetchData" />
        <SearchSelect v-model="filtroEstatus" :options="estatusFilterOptions" placeholder="Todos los estados" @update:model-value="fetchData" />
        <button v-if="hasFilters" class="filter-reset" @click="resetFilters">
          <i class="fas fa-arrows-rotate"></i> Limpiar
        </button>
      </div>
      </div>
    </div>

    <div v-if="!hasSearched" class="initial-state">
      <div class="initial-illustration">
        <i class="fas fa-search"></i>
      </div>
      <h3>Busca un alumno</h3>
      <p>Escribe una matrícula, nombre o aplica filtros para encontrar estudiantes</p>
      <div class="initial-actions">
        <button class="btn btn-primary" @click="fetchData"><i class="fas fa-list"></i> Mostrar todos</button>
        <button class="btn btn-outline-secondary" @click="openCreate"><i class="fas fa-plus"></i> Nuevo alumno</button>
      </div>
    </div>

    <div class="table-card" v-if="hasSearched">
      <div class="table-loading" v-if="loading">
        <div class="loading-pulse"></div>
        <p>Cargando estudiantes...</p>
      </div>

      <template v-else>
        <div v-if="items.length > 0" class="table-responsive">
          <table class="data-table">
          <thead>
            <tr>
              <th class="col-matricula">Matrícula</th>
              <th class="col-nombre">Estudiante</th>
              <th class="col-especialidad">Especialidad</th>
              <th class="col-semestre">Semestre</th>
              <th class="col-grupo">Grupo</th>
              <th class="col-estatus">Estado</th>
              <th class="col-acciones"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in items" :key="item.id" class="table-row" @click="goToDetail(item.id)">
              <td class="col-matricula" data-label="Matrícula">
                <span class="matricula-code">{{ item.matricula }}</span>
              </td>
              <td class="col-nombre" data-label="Estudiante">
                <div class="student-cell">
                  <img v-if="item.usuario?.fotografia" :src="item.usuario.fotografia" class="student-photo" />
                  <div v-else class="student-avatar" :style="{ background: avatarColor(item.usuario?.nombre_completo || '') }">
                    {{ initials(item.usuario?.nombre_completo || '') }}
                  </div>
                  <div class="student-info">
                    <span class="student-name">{{ item.usuario?.nombre_completo || 'Sin nombre' }}</span>
                    <span class="student-email">{{ item.usuario?.email || '-' }}</span>
                  </div>
                </div>
              </td>
              <td class="col-especialidad" data-label="Especialidad">{{ item.especialidad?.nombre || '-' }}</td>
              <td class="col-semestre" data-label="Semestre">
                <span :class="['semestre-tag', 'semestre-' + item.semestre]">{{ item.semestre }}°</span>
              </td>
              <td class="col-grupo" data-label="Grupo">{{ item.grupo?.nombre || '-' }}</td>
              <td class="col-estatus" data-label="Estado">
                <span :class="['status-dot', item.estatus]"></span>
                {{ capitalize(item.estatus) }}
              </td>
              <td class="col-acciones" data-label="Acciones" @click.stop>
                <div class="action-group">
                  <button class="action-btn" title="Editar" @click="edit(item)">
                    <i class="fas fa-pen-to-square"></i>
                  </button>
                  <button v-if="item.estatus !== 'bloqueado'" class="action-btn action-warn" title="Bloquear" @click="toggleBlock(item, true)">
                    <i class="fas fa-lock"></i>
                  </button>
                  <button v-else class="action-btn action-ok" title="Desbloquear" @click="toggleBlock(item, false)">
                    <i class="fas fa-lock-open"></i>
                  </button>
                  <button class="action-btn action-delete" title="Eliminar" @click="confirmDelete(item)">
                    <i class="fas fa-trash-can"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        </div>

        <div v-else class="empty-state">
          <div class="empty-illustration">
            <i class="fas fa-users-slash"></i>
          </div>
          <h3>No se encontraron alumnos</h3>
          <p v-if="hasFilters">Intenta ajustar los filtros de búsqueda</p>
          <p v-else>Aún no hay alumnos registrados en el sistema</p>
          <button v-if="!hasFilters" class="btn btn-primary" @click="openCreate">
            <i class="fas fa-plus"></i> Registrar primer alumno
          </button>
        </div>
      </template>

      <div v-if="totalPages > 1 && !loading" class="table-pagination">
        <div class="pagination-info">
          Página {{ page }} de {{ totalPages }} ({{ totalItems }} registros)
        </div>
        <div class="pagination-buttons">
          <button :disabled="page <= 1" @click="page--; fetchData()">
            <i class="fas fa-chevron-left"></i>
          </button>
          <button
            v-for="p in visiblePages"
            :key="p"
            :class="{ active: p === page }"
            @click="page = p; fetchData()"
          >{{ p }}</button>
          <button :disabled="page >= totalPages" @click="page++; fetchData()">
            <i class="fas fa-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>

    <div class="modal fade" ref="modal" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <div class="modal-icon" :class="editando ? 'icon-edit' : 'icon-create'">
              <i :class="editando ? 'fas fa-pen-to-square' : 'fas fa-plus'"></i>
            </div>
            <div>
              <h5 class="modal-title">{{ editando ? 'Editar Alumno' : 'Nuevo Alumno' }}</h5>
              <p class="modal-subtitle">{{ editando ? 'Modifica los datos del estudiante' : 'Registra un nuevo estudiante en el sistema' }}</p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="form-section" v-if="editando">
              <h6 class="section-label">Foto del Estudiante</h6>
              <div class="photo-upload-row">
                <div class="photo-preview-lg" v-if="editPhotoPreview || form.fotoActual">
                  <img :src="editPhotoPreview || form.fotoActual" alt="Foto" />
                  <button class="photo-remove" @click="editPhoto = null; editPhotoPreview = null; form.fotoActual = null"><i class="fas fa-times"></i></button>
                </div>
                <label class="photo-placeholder-lg" v-else>
                  <i class="fas fa-camera fa-2x"></i>
                  <span>Subir foto</span>
                  <input type="file" accept="image/*" hidden @change="onEditPhotoSelect" />
                </label>
                <label v-if="editPhotoPreview || form.fotoActual" class="photo-change-btn">
                  <i class="fas fa-arrow-up-from-bracket"></i> Cambiar
                  <input type="file" accept="image/*" hidden @change="onEditPhotoSelect" />
                </label>
              </div>
            </div>

            <div class="form-section" v-if="!editando">
              <h6 class="section-label">Usuario Asociado</h6>
              <div class="form-grid">
                <div class="form-field">
                  <label>Foto</label>
                  <div class="photo-upload">
                    <div class="photo-preview" v-if="newUserPhotoPreview">
                      <img :src="newUserPhotoPreview" alt="Preview" />
                      <button class="photo-remove" @click="newUserPhoto = null; newUserPhotoPreview = null"><i class="fas fa-times"></i></button>
                    </div>
                    <label v-else class="photo-placeholder">
                      <i class="fas fa-camera"></i>
                      <span>Subir foto</span>
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
              <h6 class="section-label">Información Académica</h6>
              <div class="form-grid">
                <div class="form-field">
                  <label>Matrícula <span class="req">*</span></label>
                  <input v-model="form.matricula" class="field-input" :class="{ 'has-error': errors.matricula }" placeholder="Ej: A2024001" />
                  <span v-if="errors.matricula" class="field-error">{{ errors.matricula }}</span>
                </div>
                <div class="form-field">
                  <label>CURP</label>
                  <input v-model="form.curp" class="field-input" placeholder="18 caracteres" maxlength="18" />
                </div>
                <div class="form-field">
                  <label>Teléfono</label>
                  <input v-model="form.telefono" class="field-input" placeholder="5551234567" />
                </div>
                <div class="form-field full-width">
                  <label>Dirección</label>
                  <input v-model="form.direccion" class="field-input" placeholder="Calle, número, colonia..." />
                </div>
                <div class="form-field">
                  <label>Especialidad <span class="req">*</span></label>
                  <SearchSelect v-model="form.especialidad_id" :options="especialidadFormOptions" placeholder="Seleccionar especialidad" :class="{ 'field-error': errors.especialidad_id }" />
                  <span v-if="errors.especialidad_id" class="field-error">{{ errors.especialidad_id }}</span>
                </div>
                <div class="form-field">
                  <label>Semestre <span class="req">*</span></label>
                  <SearchSelect v-model="form.semestre" :options="semestreFormOptions" placeholder="Seleccionar semestre" :class="{ 'field-error': errors.semestre }" />
                  <span v-if="errors.semestre" class="field-error">{{ errors.semestre }}</span>
                </div>
              </div>
            </div>

            <div class="form-section">
              <h6 class="section-label">Asignación</h6>
              <div class="form-grid cols-2">
                <div class="form-field">
                  <label>Grupo</label>
                  <SearchSelect v-model="form.grupo_id" :options="grupoFormOptions" placeholder="Sin grupo asignado" />
                </div>
                <div class="form-field">
                  <label>Generación</label>
                  <SearchSelect v-model="form.generacion_id" :options="generacionFormOptions" placeholder="Sin generación" />
                </div>
                <div class="form-field">
                  <label>Fecha Ingreso <span class="req">*</span></label>
                  <input v-model="form.fecha_ingreso" type="date" class="field-input" :class="{ 'has-error': errors.fecha_ingreso }" />
                  <span v-if="errors.fecha_ingreso" class="field-error">{{ errors.fecha_ingreso }}</span>
                </div>
              </div>
            </div>

            <div class="form-section">
              <h6 class="section-label">Estado y Notas</h6>
              <div class="form-grid cols-2">
                <div class="form-field">
                  <label>Estatus <span class="req">*</span></label>
                  <SearchSelect v-model="form.estatus" :options="estatusFormOptions" placeholder="Seleccionar estatus" :class="{ 'field-error': errors.estatus }" />
                  <span v-if="errors.estatus" class="field-error">{{ errors.estatus }}</span>
                </div>
                <div class="form-field">
                  <label>Ciclo Escolar</label>
                  <SearchSelect v-model="form.ciclo_escolar" :options="cicloFormOptions" placeholder="Seleccionar ciclo" />
                </div>
                <div class="form-field">
                  <label>Observaciones</label>
                  <textarea v-model="form.observaciones" class="field-input field-textarea" rows="2" placeholder="Notas adicionales sobre el alumno..."></textarea>
                </div>
      </div>
    </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary btn-save" :disabled="saving" @click="save">
              <i v-if="saving" class="fas fa-circle-notch fa-spin"></i>
              <i v-else class="fas fa-floppy-disk"></i>
              {{ saving ? 'Guardando...' : 'Guardar Alumno' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <BulkUploadModal ref="bulkUpload" type="alumnos" title="Alumnos" templateLabel="Descargar plantilla Excel" @imported="fetchData" />
  </div>
</template>

<script>
import api from '@/api/axios'
import Swal from 'sweetalert2'
import { Modal } from 'bootstrap'
import SearchSelect from '@/components/ui/SearchSelect.vue'
import BulkUploadModal from '@/components/ui/BulkUploadModal.vue'

export default {
  name: 'AlumnosList',
  components: { SearchSelect, BulkUploadModal },
  data() {
    return {
      items: [],
      totalItems: 0,
      loading: false,
      saving: false,
      editando: false,
      search: '',
      filtroEspecialidad: '',
      filtroSemestre: '',
      filtroEstatus: '',
      page: 1,
      totalPages: 1,
      perPage: 12,
      hasSearched: false,
      especialidades: [],
      grupos: [],
      ciclos: [],
      generaciones: [],
      editPhoto: null,
      editPhotoPreview: null,
      editingUserId: null,
      newUser: { nombre: '', apellido_paterno: '', apellido_materno: '', email: '', username: '', password: '' },
      newUserPhoto: null,
      newUserPhotoPreview: null,
      newUserErrors: {},
      form: {
        id: null, user_id: '', matricula: '', curp: '', especialidad_id: '', semestre: '',
        grupo_id: '', tutor_id: '', estatus: 'activo', fecha_ingreso: '', observaciones: '',
        generacion_id: '',
      },
      errors: {},
      modalInstance: null,
    }
  },
  computed: {
    hasFilters() {
      return this.search || this.filtroEspecialidad || this.filtroSemestre || this.filtroEstatus
    },
    activeCount() {
      return this.items.filter(i => i.estatus === 'activo').length
    },
    visiblePages() {
      const pages = []
      const start = Math.max(1, this.page - 2)
      const end = Math.min(this.totalPages, this.page + 2)
      for (let i = start; i <= end; i++) pages.push(i)
      return pages
    },
    especialidadFilterOptions() {
      return [{ value: '', label: 'Todas las especialidades' }, ...this.especialidades.map(e => ({ value: e.id, label: e.nombre }))]
    },
    semestreFilterOptions() {
      return [{ value: '', label: 'Todos los semestres' }, ...[1, 2, 3, 4, 5, 6].map(s => ({ value: s, label: s + '° Semestre' }))]
    },
    estatusFilterOptions() {
      return [
        { value: '', label: 'Todos los estados' },
        { value: 'activo', label: 'Activo' },
        { value: 'inactivo', label: 'Inactivo' },
        { value: 'egresado', label: 'Egresado' },
        { value: 'baja', label: 'Baja' },
        { value: 'bloqueado', label: 'Bloqueado' },
      ]
    },
    especialidadFormOptions() {
      return [{ value: '', label: 'Seleccionar especialidad' }, ...this.especialidades.map(e => ({ value: e.id, label: e.nombre }))]
    },
    semestreFormOptions() {
      return [{ value: '', label: 'Seleccionar semestre' }, ...[1, 2, 3, 4, 5, 6].map(s => ({ value: s, label: s + '° Semestre' }))]
    },
    grupoFormOptions() {
      return [{ value: '', label: 'Sin grupo asignado' }, ...this.grupos.map(g => ({ value: g.id, label: g.nombre }))]
    },
    generacionFormOptions() {
      return [{ value: '', label: 'Sin generación' }, ...this.generaciones.map(g => ({ value: g.id, label: g.nombre }))]
    },
    estatusFormOptions() {
      return [
        { value: 'activo', label: 'Activo' },
        { value: 'inactivo', label: 'Inactivo' },
        { value: 'egresado', label: 'Egresado' },
        { value: 'baja', label: 'Baja' },
      ]
    },
    cicloFormOptions() {
      return [{ value: '', label: 'Seleccionar ciclo' }, ...this.ciclos.map(c => ({ value: c.nombre, label: c.nombre }))]
    },
  },
  async mounted() {
    this.modalInstance = new Modal(this.$refs.modal)
    await this.loadCatalogos()
  },
  methods: {
    async promoverAlumnos() {
      const Swal = (await import('sweetalert2')).default
      const { value: ciclo } = await Swal.fire({
        title: 'Promover Alumnos',
        text: 'Alumnos con 2 o menos materias reprobadas serán promovidos. Con 3 o más reprobadas se marcarán como irregulares.',
        icon: 'info',
        input: 'text',
        inputLabel: 'Ciclo Escolar',
        inputPlaceholder: 'Ej: 2025-1 (dejar vacío para todos)',
        showCancelButton: true,
        confirmButtonText: 'Promover',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#D97706',
      })
      if (ciclo === undefined) return
      this.loading = true
      try {
        const { data } = await api.post('/alumnos/promover', { ciclo_escolar: ciclo || null })
        const r = data.data || data
        let html = `<p><strong>Promovidos:</strong> ${r.promovidos}</p><p><strong>Irregulares:</strong> ${r.irregulares}</p>`
        if (r.egresados) html += `<p><strong>Egresados:</strong> ${r.egresados}</p>`
        if (r.detalle?.length) {
          html += '<div style="max-height:200px;overflow-y:auto;text-align:left;font-size:.75rem">'
          r.detalle.slice(0, 20).forEach(d => {
            html += `<p style="margin:4px 0"><strong>${d.alumno}</strong> (${d.matricula}) — ${d.reprobadas} reprobadas → ${d.accion}</p>`
          })
          if (r.detalle.length > 20) html += `<p>...y ${r.detalle.length - 20} más</p>`
          html += '</div>'
        }
        Swal.fire({ title: 'Promoción completada', html, icon: 'success', confirmButtonText: 'OK' })
        this.fetchData()
      } catch (e) {
        Swal.fire('Error', e.response?.data?.message || 'Error al promover', 'error')
      }
      this.loading = false
    },
    capitalize(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : '' },
    initials(name) {
      if (!name) return '?'
      const parts = name.trim().split(' ')
      return (parts[0][0] + (parts.length > 1 ? parts[parts.length - 1][0] : '')).toUpperCase()
    },
    avatarColor(name) {
      const colors = ['#A91E22', '#B99A53', '#1E40AF', '#059669', '#7C3AED', '#D97706', '#0D9488', '#DC2626']
      let hash = 0
      for (let i = 0; i < (name || '').length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash)
      return colors[Math.abs(hash) % colors.length]
    },
    debounceSearch() {
      clearTimeout(this.searchTimer)
      this.searchTimer = setTimeout(() => { this.page = 1; this.fetchData() }, 350)
    },
    async loadCatalogos() {
      try {
        const [eRes, gRes, cRes, genRes] = await Promise.all([
          api.get('/especialidades?per_page=100'),
          api.get('/grupos?per_page=100'),
          api.get('/configuracion/ciclos'),
          api.get('/generaciones/all'),
        ])
        this.especialidades = eRes.data.data || eRes.data || []
        this.grupos = gRes.data.data || gRes.data || []
        this.ciclos = cRes.data.data || cRes.data || []
        this.generaciones = genRes.data || []
      } catch (e) { /* */ }
    },
    async fetchData() {
      this.hasSearched = true
      this.loading = true
      try {
        const params = { page: this.page, per_page: this.perPage }
        if (this.search) params.search = this.search
        if (this.filtroEspecialidad) params.especialidad_id = this.filtroEspecialidad
        if (this.filtroSemestre) params.semestre = this.filtroSemestre
        if (this.filtroEstatus) params.estatus = this.filtroEstatus
        const res = await api.get('/alumnos', { params })
        this.items = res.data.data || []
        this.totalPages = res.data.meta?.last_page || 1
        this.totalItems = res.data.meta?.total || 0
        this.page = res.data.meta?.current_page || 1
      } catch (e) { this.items = [] }
      this.loading = false
    },
    resetFilters() {
      this.search = ''
      this.filtroEspecialidad = ''
      this.filtroSemestre = ''
      this.filtroEstatus = ''
      this.page = 1
      this.fetchData()
    },
    goToDetail(id) { this.$router.push('/alumnos/' + id) },
    openCreate() { this.editando = false; this.newUser = { nombre: '', apellido_paterno: '', apellido_materno: '', email: '', username: '', password: '' }; this.newUserPhoto = null; this.newUserPhotoPreview = null; this.newUserErrors = {}; this.resetForm(); this.modalInstance.show() },
    onPhotoSelect(e) {
      const file = e.target.files[0]
      if (!file) return
      this.newUserPhoto = file
      const reader = new FileReader()
      reader.onload = (ev) => { this.newUserPhotoPreview = ev.target.result }
      reader.readAsDataURL(file)
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
    onEditPhotoSelect(e) {
      const file = e.target.files[0]
      if (!file) return
      this.editPhoto = file
      const reader = new FileReader()
      reader.onload = (ev) => { this.editPhotoPreview = ev.target.result }
      reader.readAsDataURL(file)
    },
    edit(item) {
      this.editando = true
      this.editPhoto = null
      this.editPhotoPreview = null
      this.editingUserId = item.usuario?.id || null
      this.form = {
        id: item.id, matricula: item.matricula || '', curp: item.curp || '',
        especialidad_id: item.especialidad_id || '', semestre: item.semestre || '',
        grupo_id: item.grupo_id || '', tutor_id: item.tutor_id || '',
        generacion_id: item.generacion_id || '',
        estatus: item.estatus || 'activo', fecha_ingreso: item.fecha_ingreso || '',
        observaciones: item.observaciones || '',
        telefono: item.usuario?.telefono || '', direccion: item.usuario?.direccion || '',
        fotoActual: item.usuario?.fotografia || null,
      }
      this.modalInstance.show()
    },
    resetForm() {
      this.form = { id: null, user_id: '', matricula: '', curp: '', especialidad_id: '', semestre: '', grupo_id: '', tutor_id: '', estatus: 'activo', fecha_ingreso: '', observaciones: '', telefono: '', direccion: '', fotoActual: null, generacion_id: '' }
      this.errors = {}
    },
    async save() {
      this.errors = {}
      this.newUserErrors = {}

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
            nombre: this.newUser.nombre,
            apellido_paterno: this.newUser.apellido_paterno,
            apellido_materno: this.newUser.apellido_materno || null,
            email: this.newUser.email,
            username: this.newUser.username,
            password: this.newUser.password,
            rol: 'alumno',
            activo: true,
          })
          this.form.user_id = userRes.data.data?.id || userRes.data.id

          if (this.newUserPhoto) {
            const fd = new FormData()
            fd.append('foto', this.newUserPhoto)
            await api.post('/usuarios/' + this.form.user_id + '/foto', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
          }
        }
        const payload = { ...this.form }
        delete payload.id
        if (this.editando) { delete payload.user_id }
        if (!payload.grupo_id) payload.grupo_id = null
        if (!payload.tutor_id) payload.tutor_id = null
        if (!payload.curp) payload.curp = null
        if (!payload.observaciones) payload.observaciones = null
        if (this.editando) {
          await api.put('/alumnos/' + this.form.id, payload)
          if (this.editingUserId) {
            await api.put('/usuarios/' + this.editingUserId, { telefono: this.form.telefono || null, direccion: this.form.direccion || null })
          }
          if (this.editPhoto && this.editingUserId) {
            const fd = new FormData()
            fd.append('foto', this.editPhoto)
            await api.post('/usuarios/' + this.editingUserId + '/foto', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
          }
        } else {
          await api.post('/alumnos', payload)
        }
        this.modalInstance.hide()
        this.fetchData()
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: this.editando ? 'Alumno actualizado' : 'Alumno creado', showConfirmButton: false, timer: 2000, timerProgressBar: true })
      } catch (e) {
        if (e.response?.status === 422) {
          const errs = e.response.data.errors || {}
          if (!this.editando && errs.nombre) {
            for (const key in errs) this.newUserErrors[key] = Array.isArray(errs[key]) ? errs[key][0] : errs[key]
          } else {
            for (const key in errs) this.errors[key] = Array.isArray(errs[key]) ? errs[key][0] : errs[key]
          }
        }
      }
      this.saving = false
    },
    async confirmDelete(item) {
      const result = await Swal.fire({
        title: '¿Eliminar alumno?',
        html: `<p style="margin:0">Se eliminará a <strong>${item.usuario?.nombre_completo || item.matricula}</strong> permanentemente.</p>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DC2626',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
      })
      if (!result.isConfirmed) return
      try {
        await api.delete('/alumnos/' + item.id)
        this.fetchData()
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Alumno eliminado', showConfirmButton: false, timer: 2000, timerProgressBar: true })
      } catch (e) { Swal.fire('Error', 'No se pudo eliminar el alumno.', 'error') }
    },
    async toggleBlock(item, bloquear) {
      const result = await Swal.fire({
        title: bloquear ? '¿Bloquear alumno?' : '¿Desbloquear alumno?',
        text: bloquear ? 'El alumno no podrá acceder al sistema.' : 'El alumno podrá acceder nuevamente.',
        icon: bloquear ? 'warning' : 'info',
        showCancelButton: true,
        confirmButtonText: bloquear ? 'Bloquear' : 'Desbloquear',
        cancelButtonText: 'Cancelar',
      })
      if (!result.isConfirmed) return
      try {
        await api.post('/alumnos/' + item.id + '/' + (bloquear ? 'bloquear' : 'desbloquear'))
        this.fetchData()
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: bloquear ? 'Alumno bloqueado' : 'Alumno desbloqueado', showConfirmButton: false, timer: 2000, timerProgressBar: true })
      } catch (e) { Swal.fire('Error', 'Operación fallida.', 'error') }
    },
  },
}
</script>

<style scoped>
/* ── Module Header ── */
.module-header {
  display: flex; align-items: flex-start; justify-content: space-between;
  margin-bottom: 1.5rem; gap: 1rem; flex-wrap: wrap;
}
.header-info { display: flex; align-items: center; gap: 1rem; }
.header-icon {
  width: 48px; height: 48px; border-radius: 14px;
  background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover));
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 1.35rem; box-shadow: 0 4px 12px rgba(169,30,34,0.25);
}
.header-title { font-size: 1.5rem; font-weight: 700; color: var(--color-text); margin: 0; line-height: 1.2; }
.header-subtitle { font-size: .8125rem; color: var(--color-text-secondary); margin: 2px 0 0; }
.header-divider { margin: 0 .5rem; color: var(--color-border); }

.btn-create {
  display: inline-flex; align-items: center; gap: .5rem;
  padding: .625rem 1.25rem; border-radius: 12px; border: none;
  background: var(--color-primary); color: #fff;
  font-size: .875rem; font-weight: 600; cursor: pointer;
  transition: all .2s; box-shadow: 0 2px 8px rgba(169,30,34,0.2);
}
.btn-create:hover { background: var(--color-primary-hover); transform: translateY(-1px); box-shadow: 0 4px 16px rgba(169,30,34,0.3); }

/* ── Filter Strip ── */
.filter-strip {
  display: flex; align-items: center; gap: .75rem; flex-wrap: wrap;
  background: var(--color-bg); border: 1px solid var(--color-border-light);
  border-radius: 16px; padding: .625rem 1rem; margin-bottom: 1.25rem;
}
.filter-search {
  position: relative; flex: 1; min-width: 220px; max-width: 380px;
}
.filter-search i {
  position: absolute; left: .875rem; top: 50%; transform: translateY(-50%);
  color: var(--color-text-muted); font-size: .875rem; pointer-events: none;
}
.filter-search input {
  width: 100%; padding: .5rem .875rem .5rem 2.5rem;
  border: none; border-radius: 10px; background: var(--color-bg-secondary);
  font-size: .875rem; color: var(--color-text); outline: none;
  transition: all .2s;
}
.filter-search input:focus { background: var(--color-bg); box-shadow: 0 0 0 3px rgba(169,30,34,0.08); }
.filter-search input::placeholder { color: var(--color-text-muted); }
.filter-clear {
  position: absolute; right: .5rem; top: 50%; transform: translateY(-50%);
  background: none; border: none; color: var(--color-text-muted); cursor: pointer;
  padding: .25rem; border-radius: 50%; font-size: .75rem;
}
.filter-clear:hover { color: var(--color-text); background: var(--color-border-light); }
.filter-chips { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
.filter-select {
  padding: .5rem 2rem .5rem .75rem; border: 1px solid var(--color-border);
  border-radius: 10px; font-size: .8125rem; color: var(--color-text);
  background: var(--color-bg); cursor: pointer; outline: none;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236B7280' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right .6rem center;
  transition: border-color .2s;
}
.filter-select:focus { border-color: var(--color-primary); }
.filter-reset {
  display: inline-flex; align-items: center; gap: .375rem;
  padding: .5rem .875rem; border-radius: 10px; border: 1px dashed var(--color-border);
  background: none; font-size: .8125rem; color: var(--color-text-secondary);
  cursor: pointer; transition: all .2s;
}
.filter-reset:hover { border-color: var(--color-primary); color: var(--color-primary); background: var(--color-primary-light); }

/* ── Table Card ── */
.table-card {
  background: var(--color-bg); border: 1px solid var(--color-border-light);
  border-radius: 18px; overflow: hidden; box-shadow: var(--shadow-xs);
}
.table-loading {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  padding: 4rem 2rem; color: var(--color-text-muted); gap: 1rem;
}
.loading-pulse {
  width: 40px; height: 40px; border-radius: 50%;
  border: 3px solid var(--color-border); border-top-color: var(--color-primary);
  animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Data Table */
.data-table { width: 100%; border-collapse: collapse; }
.data-table thead th {
  text-align: left; padding: .75rem 1rem;
  font-size: .6875rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: .06em; color: var(--color-text-muted);
  background: var(--color-bg-tertiary); border-bottom: 1px solid var(--color-border-light);
  white-space: nowrap;
}
.data-table tbody td {
  padding: .75rem 1rem; font-size: .875rem; color: var(--color-text);
  border-bottom: 1px solid var(--color-border-light); vertical-align: middle;
}
.table-row { cursor: pointer; transition: background .15s; }
.table-row:hover { background: var(--color-bg-tertiary); }
.table-row:last-child td { border-bottom: none; }

.matricula-code {
  font-weight: 600; font-family: var(--font-family-mono);
  font-size: .8125rem; color: var(--color-primary);
  background: var(--color-primary-light); padding: .2rem .5rem; border-radius: 6px;
}
.student-cell { display: flex; align-items: center; gap: .625rem; }
.student-avatar {
  width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: .8125rem; font-weight: 700;
}
.student-info { display: flex; flex-direction: column; min-width: 0; }
.student-name { font-weight: 600; font-size: .875rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.student-email { font-size: .75rem; color: var(--color-text-muted); }

.semestre-tag {
  display: inline-block; padding: .2rem .625rem; border-radius: 20px;
  font-size: .75rem; font-weight: 600;
}
.semestre-1, .semestre-2 { background: #EFF6FF; color: #1E40AF; }
.semestre-3, .semestre-4 { background: #FFFBEB; color: #B45309; }
.semestre-5, .semestre-6 { background: #FEF2F2; color: #B91C1C; }

.status-dot {
  display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: .375rem;
}
.status-dot.activo { background: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,0.15); }
.status-dot.inactivo { background: #6B7280; box-shadow: 0 0 0 3px rgba(107,114,128,0.15); }
.status-dot.egresado { background: #0284C7; box-shadow: 0 0 0 3px rgba(2,132,199,0.15); }
.status-dot.baja { background: #DC2626; box-shadow: 0 0 0 3px rgba(220,38,38,0.15); }
.status-dot.bloqueado { background: #D97706; box-shadow: 0 0 0 3px rgba(217,119,6,0.15); }

/* Action buttons */
.action-group { display: flex; gap: .25rem; justify-content: flex-end; }
.action-btn {
  width: 34px; height: 34px; border: none; border-radius: 8px;
  background: none; color: var(--color-text-secondary); cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  font-size: .875rem; transition: all .15s;
}
.action-btn:hover { background: var(--color-bg-secondary); color: var(--color-text); transform: scale(1.08); }
.action-warn:hover { color: #D97706; background: #FFFBEB; }
.action-ok:hover { color: #059669; background: #ECFDF5; }
.action-delete:hover { color: #DC2626; background: #FEF2F2; }

/* Student photo in table */
.student-photo {
  width: 36px; height: 36px; border-radius: 10px; object-fit: cover; flex-shrink: 0;
}

/* Photo upload */
.photo-upload { display: flex; align-items: center; }
.photo-preview {
  position: relative; width: 64px; height: 64px; border-radius: 12px; overflow: hidden;
}
.photo-preview img { width: 100%; height: 100%; object-fit: cover; }
.photo-remove {
  position: absolute; top: -4px; right: -4px;
  width: 20px; height: 20px; border-radius: 50%; border: none;
  background: var(--color-danger); color: #fff; cursor: pointer;
  font-size: .625rem; display: flex; align-items: center; justify-content: center;
}
.photo-placeholder {
  width: 64px; height: 64px; border-radius: 12px;
  border: 2px dashed var(--color-border);
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  gap: 4px; cursor: pointer; transition: all .15s;
  font-size: .75rem; color: var(--color-text-muted);
}
.photo-placeholder:hover { border-color: var(--color-primary); color: var(--color-primary); background: var(--color-primary-light); }

/* Pagination */
.table-pagination {
  display: flex; align-items: center; justify-content: space-between;
  padding: .875rem 1.25rem; border-top: 1px solid var(--color-border-light);
  flex-wrap: wrap; gap: .75rem;
}
.pagination-info { font-size: .8125rem; color: var(--color-text-muted); }
.pagination-buttons { display: flex; gap: .25rem; }
.pagination-buttons button {
  min-width: 34px; height: 34px; border: 1px solid var(--color-border);
  border-radius: 8px; background: var(--color-bg); cursor: pointer;
  font-size: .8125rem; font-weight: 500; color: var(--color-text-secondary);
  display: flex; align-items: center; justify-content: center;
  transition: all .15s;
}
.pagination-buttons button:hover:not(:disabled) { background: var(--color-bg-secondary); border-color: var(--color-text-muted); }
.pagination-buttons button.active { background: var(--color-primary); color: #fff; border-color: var(--color-primary); }
.pagination-buttons button:disabled { opacity: .35; cursor: not-allowed; }

/* ── Empty State ── */
.empty-state {
  display: flex; flex-direction: column; align-items: center;
  padding: 4rem 2rem; text-align: center;
}
.empty-illustration {
  width: 80px; height: 80px; border-radius: 50%;
  background: var(--color-bg-secondary); display: flex;
  align-items: center; justify-content: center; margin-bottom: 1.25rem;
  font-size: 2rem; color: var(--color-text-muted);
}
.empty-state h3 { font-size: 1.125rem; font-weight: 600; color: var(--color-text); margin: 0 0 .25rem; }
.empty-state p { font-size: .875rem; color: var(--color-text-secondary); margin: 0 0 1rem; }

/* ── Modal ── */
.modal-content { border: none; border-radius: 20px; overflow: hidden; }
.modal-header {
  padding: 1.5rem 1.75rem 1rem; border-bottom: none; display: flex; align-items: flex-start; gap: .875rem;
}
.modal-icon {
  width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center; font-size: 1.1rem; color: #fff;
}
.icon-create { background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover)); }
.icon-edit { background: linear-gradient(135deg, #D97706, #B45309); }
.modal-title { font-size: 1.125rem; font-weight: 700; margin: 0; }
.modal-subtitle { font-size: .8125rem; color: var(--color-text-muted); margin: 2px 0 0; }
.modal-body { padding: 0 1.75rem 1.5rem; }
.modal-footer {
  padding: 1rem 1.75rem; border-top: 1px solid var(--color-border-light);
  background: var(--color-bg-tertiary); display: flex; justify-content: flex-end; gap: .625rem;
}
.btn-save { padding: .5625rem 1.5rem; font-weight: 600; }

/* Form Sections */
.form-section { margin-bottom: 1.5rem; }
.form-section:last-child { margin-bottom: 0; }
.section-label {
  font-size: .6875rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: .08em; color: var(--color-text-muted);
  margin: 0 0 .875rem; padding-bottom: .5rem; border-bottom: 1px solid var(--color-border-light);
}
.form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; }
.form-grid.cols-3 { grid-template-columns: repeat(3, 1fr); }
.form-field { display: flex; flex-direction: column; gap: .3rem; }
.form-field label { font-size: .8125rem; font-weight: 500; color: var(--color-text); }
.form-field .req { color: var(--color-danger); }
.form-field.full-width { grid-column: 1 / -1; }
.field-input {
  padding: .5625rem .75rem; border: 1.5px solid var(--color-border);
  border-radius: 10px; font-size: .875rem; font-family: var(--font-family);
  color: var(--color-text); background: var(--color-bg); outline: none;
  transition: border-color .2s, box-shadow .2s;
}
.field-input:focus { border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(169,30,34,0.08); }
.field-input.has-error { border-color: var(--color-danger); }
.field-input.has-error:focus { box-shadow: 0 0 0 3px rgba(220,38,38,0.08); }
.field-textarea { resize: vertical; min-height: 60px; }
.field-error { font-size: .75rem; color: var(--color-danger); }

@media (max-width: 768px) {
  .form-grid, .form-grid.cols-3, .form-grid.cols-2 { grid-template-columns: 1fr; }
  .filter-strip { flex-direction: column; align-items: stretch; }
  .filter-search { max-width: 100%; }
}

/* User toggle */
.user-toggle {
  display: flex; gap: .375rem; margin-bottom: .875rem;
  background: var(--color-bg-secondary); padding: .25rem; border-radius: 10px;
}
.toggle-option {
  flex: 1; padding: .4375rem .75rem; border: none; border-radius: 8px;
  font-size: .8125rem; font-weight: 500; cursor: pointer; color: var(--color-text-secondary);
  background: transparent; transition: all .15s; display: flex; align-items: center; justify-content: center; gap: .375rem;
}
.toggle-option.active { background: var(--color-bg); color: var(--color-primary); font-weight: 600; box-shadow: var(--shadow-sm); }
.toggle-option:hover:not(.active) { color: var(--color-text); }

/* Initial state */
.initial-state {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  padding: 4rem 2rem; text-align: center;
}
.initial-illustration {
  width: 96px; height: 96px; border-radius: 50%;
  background: var(--color-bg-secondary);
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 1.5rem; font-size: 2.5rem; color: var(--color-text-muted);
}
.initial-state h3 { font-size: 1.25rem; font-weight: 600; margin: 0 0 .375rem; }
.initial-state p { font-size: .875rem; color: var(--color-text-secondary); margin: 0 0 1.5rem; max-width: 380px; }
.initial-actions { display: flex; gap: .625rem; }
</style>
