<template>
  <div class="config-page">
    <div class="module-header">
      <div class="header-info">
        <div class="header-icon"><i class="fas fa-gear"></i></div>
        <div>
          <h1 class="header-title">Configuración</h1>
          <p class="header-subtitle">Administración general del sistema</p>
        </div>
      </div>
    </div>

    <div class="tabs-nav">
      <button :class="['tab-btn', { active: activeTab === 'general' }]" @click="activeTab = 'general'"><i class="fas fa-sliders"></i> General</button>
      <button :class="['tab-btn', { active: activeTab === 'aulas' }]" @click="activeTab = 'aulas'; fetchAulas()"><i class="fas fa-door-open"></i> Aulas</button>
      <button :class="['tab-btn', { active: activeTab === 'noticias' }]" @click="activeTab = 'noticias'"><i class="fas fa-newspaper"></i> Noticias</button>
      <button :class="['tab-btn', { active: activeTab === 'avisos' }]" @click="activeTab = 'avisos'"><i class="fas fa-bullhorn"></i> Avisos</button>
      <button :class="['tab-btn', { active: activeTab === 'ciclos' }]" @click="activeTab = 'ciclos'"><i class="fas fa-calendar"></i> Periodos</button>
      <button :class="['tab-btn', { active: activeTab === 'generaciones' }]" @click="activeTab = 'generaciones'; fetchGeneraciones()"><i class="fas fa-users"></i> Generaciones</button>
      <button :class="['tab-btn', { active: activeTab === 'apariencia' }]" @click="activeTab = 'apariencia'"><i class="fas fa-palette"></i> Apariencia</button>
    </div>

    <!-- General -->
    <div v-if="activeTab === 'general'" class="card">
      <div class="card-header"><i class="fas fa-sliders"></i> Parámetros del Sistema</div>
      <div class="card-body">
        <div class="brand-section mb-4">
          <h6 class="section-label">Identidad Visual</h6>
          <div class="brand-row">
            <div class="brand-item">
              <span class="config-label">Logo del Sitio</span>
              <label class="brand-preview" for="logoInput">
                <img v-if="logoPreview" :src="logoPreview" />
                <div v-else class="brand-placeholder"><i class="fas fa-image"></i><span>Seleccionar logo</span></div>
              </label>
              <input id="logoInput" type="file" accept="image/*" hidden @change="onLogoSelect" />
              <button v-if="logoPreview" class="btn btn-sm btn-outline-secondary mt-2" @click="logoPreview='';logoFile=null"><i class="fas fa-trash"></i> Quitar</button>
            </div>
            <div class="brand-item">
              <span class="config-label">Favicon</span>
              <label class="brand-preview brand-favicon" for="favInput">
                <img v-if="faviconPreview" :src="faviconPreview" />
                <div v-else class="brand-placeholder"><i class="fas fa-star"></i><span>Seleccionar favicon</span></div>
              </label>
              <input id="favInput" type="file" accept="image/png,image/x-icon,image/vnd.microsoft.icon" hidden @change="onFaviconSelect" />
              <button v-if="faviconPreview" class="btn btn-sm btn-outline-secondary mt-2" @click="faviconPreview='';faviconFile=null"><i class="fas fa-trash"></i> Quitar</button>
            </div>
          </div>
          <button class="btn btn-primary btn-sm mt-3" @click="guardarBranding" :disabled="savingBrand">
            <i v-if="savingBrand" class="fas fa-circle-notch fa-spin"></i>
            <i v-else class="fas fa-floppy-disk"></i> Guardar Imágenes
          </button>
        </div>
        <div class="config-grid">
          <div class="config-item" v-for="cfg in configuracion" :key="cfg.id || cfg.clave">
            <label class="config-label">{{ formatKey(cfg.clave || cfg.key) }}</label>
            <textarea v-if="isTextArea(cfg)" class="field-input field-textarea" rows="2" v-model="cfg.valor"></textarea>
            <input v-else :type="isPassword(cfg) ? 'password' : 'text'" class="field-input" v-model="cfg.valor" />
            <span class="config-desc" v-if="cfg.descripcion">{{ cfg.descripcion }}</span>
          </div>
        </div>
        <div class="mt-4 text-end">
          <button class="btn btn-primary" @click="guardarGeneral" :disabled="savingGeneral">
            <i v-if="savingGeneral" class="fas fa-circle-notch fa-spin"></i>
            <i v-else class="fas fa-floppy-disk"></i>
            Guardar Cambios
          </button>
        </div>
      </div>
    </div>

    <!-- Aulas -->
    <div v-if="activeTab === 'aulas'" class="card">
      <div class="card-header">
        <i class="fas fa-door-open"></i> Aulas
        <button class="btn btn-primary btn-sm ms-auto" @click="openAulaModal(null)"><i class="fas fa-plus"></i> Nueva</button>
      </div>
      <div class="card-body p-0">
        <div v-if="aulas.length === 0" class="empty-state">
          <div class="empty-illustration"><i class="fas fa-door-open"></i></div>
          <h3>Sin aulas</h3>
          <p>Registra las aulas disponibles en el plantel</p>
        </div>
        <div v-else class="table-responsive">
        <table class="data-table">
          <thead><tr><th>Nombre</th><th>Edificio</th><th>Capacidad</th><th>Grupos</th><th>Activo</th><th></th></tr></thead>
          <tbody>
            <tr v-for="a in aulas" :key="a.id">
              <td data-label="Nombre"><span class="fw-semibold">{{ a.nombre }}</span></td>
              <td data-label="Edificio">{{ a.edificio || '-' }}</td>
              <td data-label="Capacidad">{{ a.capacidad }}</td>
              <td data-label="Grupos">
                <span v-if="!a.grupos || !a.grupos.length" class="text-muted">—</span>
                <span v-else v-for="g in a.grupos" :key="g.id" class="badge badge-info me-1">{{ g.nombre }} ({{ g.turno }})</span>
              </td>
              <td data-label="Activo"><span class="status-dot" :class="a.activo ? 'activo' : 'inactivo'"></span> {{ a.activo ? 'Sí' : 'No' }}</td>
              <td data-label="Acciones">
                <div class="action-group">
                  <button class="action-btn" @click="openAulaModal(a)"><i class="fas fa-pen-to-square"></i></button>
                  <button class="action-btn action-delete" @click="deleteAula(a)"><i class="fas fa-trash-can"></i></button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
        </div>
      </div>
    </div>

    <!-- Noticias -->
    <div v-if="activeTab === 'noticias'" class="card">
      <div class="card-header"><i class="fas fa-newspaper"></i> Noticias <button class="btn btn-primary btn-sm ms-auto" @click="openCrudModal('noticia', 'create')"><i class="fas fa-plus"></i> Nueva</button></div>
      <div class="card-body p-0">
        <div v-if="noticias.length === 0" class="empty-state"><div class="empty-illustration"><i class="fas fa-newspaper"></i></div><h3>Sin noticias</h3></div>
        <div v-else class="table-responsive">
        <table class="data-table">
          <thead><tr><th>Título</th><th>Fecha</th><th>Publicado</th><th></th></tr></thead>
          <tbody>
            <tr v-for="n in noticias" :key="n.id">
              <td data-label="Título">{{ n.titulo || n.title }}</td>
              <td data-label="Fecha">{{ formatDate(n.fecha || n.created_at) }}</td>
              <td data-label="Publicado"><span class="status-dot" :class="(n.publicado || n.activo) ? 'activo' : 'inactivo'"></span> {{ (n.publicado || n.activo) ? 'Sí' : 'No' }}</td>
              <td data-label="Acciones"><div class="action-group"><button class="action-btn" @click="openCrudModal('noticia','edit',n)"><i class="fas fa-pen-to-square"></i></button><button class="action-btn action-delete" @click="deleteItem('noticia',n.id)"><i class="fas fa-trash-can"></i></button></div></td>
            </tr>
          </tbody>
        </table>
        </div>
      </div>
    </div>

    <!-- Avisos -->
    <div v-if="activeTab === 'avisos'" class="card">
      <div class="card-header"><i class="fas fa-bullhorn"></i> Avisos <button class="btn btn-primary btn-sm ms-auto" @click="openCrudModal('aviso', 'create')"><i class="fas fa-plus"></i> Nuevo</button></div>
      <div class="card-body p-0">
        <div v-if="avisos.length === 0" class="empty-state"><div class="empty-illustration"><i class="fas fa-bullhorn"></i></div><h3>Sin avisos</h3></div>
        <div v-else class="table-responsive">
        <table class="data-table">
          <thead><tr><th>Título</th><th>Fecha</th><th>Publicado</th><th></th></tr></thead>
          <tbody>
            <tr v-for="a in avisos" :key="a.id">
              <td data-label="Título">{{ a.titulo || a.title }}</td>
              <td data-label="Fecha">{{ formatDate(a.fecha || a.created_at) }}</td>
              <td data-label="Publicado"><span class="status-dot" :class="a.publicado ? 'activo' : 'inactivo'"></span> {{ a.publicado ? 'Sí' : 'No' }}</td>
              <td data-label="Acciones"><div class="action-group"><button class="action-btn" @click="openCrudModal('aviso','edit',a)"><i class="fas fa-pen-to-square"></i></button><button class="action-btn action-delete" @click="deleteItem('aviso',a.id)"><i class="fas fa-trash-can"></i></button></div></td>
            </tr>
          </tbody>
        </table>
        </div>
      </div>
    </div>

    <!-- Ciclos -->
    <div v-if="activeTab === 'ciclos'" class="card">
      <div class="card-header"><i class="fas fa-calendar"></i> Periodos <button class="btn btn-primary btn-sm ms-auto" @click="openCrudModal('ciclo', 'create')"><i class="fas fa-plus"></i> Nuevo</button></div>
      <div class="card-body p-0">
        <div v-if="ciclos.length === 0" class="empty-state"><div class="empty-illustration"><i class="fas fa-calendar"></i></div><h3>Sin periodos</h3></div>
        <div v-else class="table-responsive">
        <table class="data-table">
          <thead><tr><th>Periodo</th><th>Inicio</th><th>Fin</th><th>Activo</th><th></th></tr></thead>
          <tbody>
            <tr v-for="c in ciclos" :key="c.id">
              <td data-label="Periodo"><span class="fw-semibold">{{ c.nombre }}</span></td>
              <td data-label="Inicio">{{ formatDate(c.fecha_inicio) }}</td>
              <td data-label="Fin">{{ formatDate(c.fecha_fin) }}</td>
              <td data-label="Activo"><span class="status-dot" :class="c.activo ? 'activo' : 'inactivo'"></span> {{ c.activo ? 'Sí' : 'No' }}</td>
              <td data-label="Acciones"><div class="action-group"><button class="action-btn" @click="openCrudModal('ciclo','edit',c)"><i class="fas fa-pen-to-square"></i></button><button class="action-btn action-delete" @click="deleteItem('ciclo',c.id)"><i class="fas fa-trash-can"></i></button></div></td>
            </tr>
          </tbody>
        </table>
        </div>
      </div>
    </div>

    <!-- Generaciones -->
    <div v-if="activeTab === 'generaciones'" class="card">
      <div class="card-header"><i class="fas fa-users"></i> Generaciones <button class="btn btn-primary btn-sm ms-auto" @click="openCrudModal('generacion', 'create')"><i class="fas fa-plus"></i> Nueva</button></div>
      <div class="card-body p-0">
        <div v-if="generaciones.length === 0" class="empty-state"><div class="empty-illustration"><i class="fas fa-users"></i></div><h3>Sin generaciones</h3></div>
        <div v-else class="table-responsive">
        <table class="data-table">
          <thead><tr><th>Generación</th><th>Inicio</th><th>Fin</th><th>Activo</th><th></th></tr></thead>
          <tbody>
            <tr v-for="g in generaciones" :key="g.id">
              <td data-label="Generación"><span class="fw-semibold">{{ g.nombre }}</span></td>
              <td data-label="Inicio">{{ formatDate(g.fecha_inicio) }}</td>
              <td data-label="Fin">{{ formatDate(g.fecha_termino) }}</td>
              <td data-label="Activo"><span class="status-dot" :class="g.activo ? 'activo' : 'inactivo'"></span> {{ g.activo ? 'Sí' : 'No' }}</td>
              <td data-label="Acciones"><div class="action-group"><button class="action-btn" @click="openCrudModal('generacion','edit',g)"><i class="fas fa-pen-to-square"></i></button><button class="action-btn action-delete" @click="deleteItem('generacion',g.id)"><i class="fas fa-trash-can"></i></button></div></td>
            </tr>
          </tbody>
        </table>
        </div>
      </div>
    </div>

    <!-- Apariencia -->
    <div v-if="activeTab === 'apariencia'" class="card">
      <div class="card-header"><i class="fas fa-palette"></i> Apariencia</div>
      <div class="card-body">
        <ThemeSwitcher />
      </div>
    </div>

    <!-- CRUD Modal (Noticias/Avisos/Ciclos) -->
    <div class="modal fade" ref="crudModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <div class="modal-icon icon-create"><i :class="modalIcon"></i></div>
            <div><h5 class="modal-title">{{ modalTitle }}</h5></div>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <template v-if="crudType === 'noticia' || crudType === 'aviso'">
              <div class="form-field mb-3"><label>Título</label><input class="field-input" v-model="crudForm.titulo" /></div>
              <div class="form-field mb-3"><label>Contenido</label><textarea class="field-input field-textarea" rows="3" v-model="crudForm.contenido"></textarea></div>
              <div class="form-field mb-3" v-if="crudType === 'noticia'">
                <label>Imagen</label>
                <div class="d-flex gap-2 align-items-center">
                  <input class="field-input flex-grow-1" v-model="crudForm.imagen" placeholder="URL de imagen o subir archivo" />
                  <label class="btn btn-sm btn-outline-primary mb-0" style="white-space:nowrap;cursor:pointer">
                    <i class="fas fa-upload"></i> Subir
                    <input type="file" accept="image/*" hidden @change="uploadNoticiaImg" />
                  </label>
                </div>
                <img v-if="crudForm.imagen" :src="crudForm.imagen" class="mt-2 rounded" style="max-width:200px;max-height:120px;object-fit:cover" />
              </div>
              <div class="form-field mb-3"><label>Fecha</label><input type="date" class="field-input" v-model="crudForm.fecha" /></div>
              <div class="form-check mb-3"><input type="checkbox" class="form-check-input" id="crudPublicado" v-model="crudForm.publicado" /><label class="form-check-label" for="crudPublicado">Publicado</label></div>
            </template>
            <template v-if="crudType === 'ciclo'">
              <div class="form-field mb-3"><label>Nombre</label><input class="field-input" v-model="crudForm.nombre" placeholder="Ej: 2026-1" /></div>
              <div class="form-field mb-3"><label>Fecha Inicio</label><input type="date" class="field-input" v-model="crudForm.fecha_inicio" /></div>
              <div class="form-field mb-3"><label>Fecha Fin</label><input type="date" class="field-input" v-model="crudForm.fecha_fin" /></div>
              <div class="form-check mb-3"><input type="checkbox" class="form-check-input" id="crudActivo" v-model="crudForm.activo" /><label class="form-check-label" for="crudActivo">Periodo activo</label></div>
            </template>
            <template v-if="crudType === 'generacion'">
              <div class="form-field mb-3"><label>Nombre</label><input class="field-input" v-model="crudForm.nombre" placeholder="Ej: 2026-2030" /></div>
              <div class="form-field mb-3"><label>Fecha Inicio</label><input type="date" class="field-input" v-model="crudForm.fecha_inicio" /></div>
              <div class="form-field mb-3"><label>Fecha Fin</label><input type="date" class="field-input" v-model="crudForm.fecha_termino" /></div>
              <div class="form-field mb-3"><label>Descripción</label><textarea class="field-input field-textarea" rows="2" v-model="crudForm.descripcion"></textarea></div>
              <div class="form-check mb-3"><input type="checkbox" class="form-check-input" id="crudActivo" v-model="crudForm.activo" /><label class="form-check-label" for="crudActivo">Activo</label></div>
            </template>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary" @click="saveCrud" :disabled="savingCrud">
              <i v-if="savingCrud" class="fas fa-circle-notch fa-spin"></i><i v-else class="fas fa-floppy-disk"></i> Guardar
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Aula Modal -->
    <div class="modal fade" ref="aulaModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <div class="modal-icon" :class="editAulaId ? 'icon-edit' : 'icon-create'"><i :class="editAulaId ? 'fas fa-pen-to-square' : 'fas fa-plus'"></i></div>
            <div><h5 class="modal-title">{{ editAulaId ? 'Editar Aula' : 'Nueva Aula' }}</h5></div>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="form-field mb-3"><label>Nombre <span class="req">*</span></label><input class="field-input" v-model="aulaForm.nombre" placeholder="Ej: A-101" /></div>
            <div class="form-field mb-3"><label>Edificio</label><input class="field-input" v-model="aulaForm.edificio" placeholder="Ej: Edificio A" /></div>
            <div class="form-field mb-3"><label>Capacidad</label><input type="number" class="field-input" v-model="aulaForm.capacidad" /></div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary" @click="saveAula" :disabled="savingCrud">
              <i v-if="savingCrud" class="fas fa-circle-notch fa-spin"></i><i v-else class="fas fa-floppy-disk"></i> Guardar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/api/axios'
import axios from 'axios'
import Swal from 'sweetalert2'
import { Modal } from 'bootstrap'
import ThemeSwitcher from '@/components/ui/ThemeSwitcher.vue'

export default {
  name: 'ConfiguracionView',
  components: {
    ThemeSwitcher,
  },
  data() {
    return {
      activeTab: 'general',
      configuracion: [], noticias: [], avisos: [], ciclos: [], aulas: [], generaciones: [],
      savingGeneral: false, savingCrud: false, savingBrand: false,
      logoFile: null, logoPreview: null, faviconFile: null, faviconPreview: null,
      crudType: '', crudMode: 'create', crudForm: {}, crudModalInstance: null,
      aulaForm: { nombre: '', edificio: '', capacidad: 30 }, editAulaId: null, aulaModalInstance: null,
    }
  },
  computed: {
    modalTitle() { const labels = { noticia: 'Noticia', aviso: 'Aviso', ciclo: 'Ciclo Escolar', generacion: 'Generación' }; return `${this.crudMode === 'edit' ? 'Editar' : 'Nueva'} ${labels[this.crudType] || ''}` },
    modalIcon() { const icons = { noticia: 'fas fa-newspaper', aviso: 'fas fa-bullhorn', ciclo: 'fas fa-calendar', generacion: 'fas fa-users' }; return icons[this.crudType] || 'fas fa-edit' },
  },
  methods: {
    formatKey(k) { return k.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) },
    isTextArea(cfg) { const v = cfg.valor || ''; return v.length > 100 || (cfg.clave && cfg.clave.toLowerCase().includes('descripcion')) },
    isPassword(cfg) { return cfg.clave && cfg.clave.toLowerCase().includes('password') },
    formatDate(d) { if (!d) return '-'; return new Date(d).toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' }) },

    async fetchConfiguracion() { try { const { data } = await api.get('/configuracion'); this.configuracion = data.data || data || [] } catch (e) {} },
    async guardarGeneral() { this.savingGeneral = true; try { const configuraciones = this.configuracion.map(c => ({ clave: c.clave || c.key, valor: c.valor, tipo: c.tipo || 'string', descripcion: c.descripcion || '' })); await api.put('/configuracion', { configuraciones }); Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Guardado', showConfirmButton: false, timer: 2000, timerProgressBar: true }) } catch (e) { Swal.fire('Error', 'No se pudo guardar.', 'error') } finally { this.savingGeneral = false } },
    onLogoSelect(e){const f=e.target.files[0];if(!f)return;this.logoFile=f;const r=new FileReader();r.onload=ev=>{this.logoPreview=ev.target.result};r.readAsDataURL(f)},
    onFaviconSelect(e){const f=e.target.files[0];if(!f)return;this.faviconFile=f;const r=new FileReader();r.onload=ev=>{this.faviconPreview=ev.target.result};r.readAsDataURL(f)},
    async guardarBranding(){this.savingBrand=true;try{if(this.logoFile){const fd=new FormData();fd.append('file',this.logoFile);fd.append('tipo','logo');await api.post('/configuracion/upload',fd,{transformRequest:[(data,headers)=>{delete headers['Content-Type'];return data}]})}if(this.faviconFile){const fd=new FormData();fd.append('file',this.faviconFile);fd.append('tipo','favicon');await api.post('/configuracion/upload',fd,{transformRequest:[(data,headers)=>{delete headers['Content-Type'];return data}]})};Swal.fire({toast:true,position:'top-end',icon:'success',title:'Imágenes guardadas',showConfirmButton:false,timer:2000})}catch(e){Swal.fire('Error',e.response?.data?.message||'No se pudo guardar.','error')};this.savingBrand=false},

    async fetchNoticias() { try { const { data } = await api.get('/configuracion/noticias'); this.noticias = data.data || data || [] } catch (e) {} },
    async fetchAvisos() { try { const { data } = await api.get('/configuracion/avisos'); this.avisos = data.data || data || [] } catch (e) {} },
    async fetchCiclos() { try { const { data } = await api.get('/configuracion/ciclos'); this.ciclos = data.data || data || [] } catch (e) {} },
    async fetchGeneraciones() { try { const { data } = await api.get('/generaciones'); this.generaciones = data.data || data || [] } catch (e) {} },
    async fetchAulas() { try { const { data } = await api.get('/aulas'); this.aulas = data.data || data || [] } catch (e) {} },

    openCrudModal(type, mode, item = null) {
      this.crudType = type; this.crudMode = mode
        if (mode === 'edit' && item) {
        this.crudForm = { ...item, fecha: item.fecha_publicacion || item.fecha, publicado: item.activo !== undefined ? item.activo : true }
      } else {
        this.crudForm = { titulo: '', contenido: '', imagen: '', fecha: new Date().toISOString().slice(0,10), publicado: true, nombre: '', fecha_inicio: '', fecha_fin: '', activo: true }
      }
      if (!this.crudModalInstance) this.crudModalInstance = new Modal(this.$refs.crudModal)
      this.crudModalInstance.show()
    },
    async saveCrud() {
      this.savingCrud = true
      try {
        if (this.crudType === 'generacion') {
          const payload = { ...this.crudForm }
          if (this.crudMode === 'edit') { await api.put('/generaciones/' + this.crudForm.id, payload) }
          else { await api.post('/generaciones', payload) }
        } else {
          const map = { noticia: 'noticias', aviso: 'avisos', ciclo: 'ciclos' }
          const ep = '/configuracion/' + map[this.crudType]
          const payload = { ...this.crudForm }
          if (this.crudType === 'aviso') delete payload.imagen
          if (this.crudMode === 'edit') { await api.put(ep + '/' + this.crudForm.id, payload) }
          else { await api.post(ep, payload) }
        }
        this.crudModalInstance.hide(); this.refreshTab()
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Guardado', showConfirmButton: false, timer: 2000, timerProgressBar: true })
      } catch (e) { Swal.fire('Error', 'No se pudo guardar.', 'error') } finally { this.savingCrud = false }
    },
    async uploadNoticiaImg(e) {
      const file = e.target.files[0]; if (!file) return
      const fd = new FormData(); fd.append('imagen', file)
      try {
        const { data } = await api.post('/configuracion/noticias/upload', fd, { headers: { 'Content-Type': 'multipart/form-data' } })
        this.crudForm.imagen = data.data?.url || data.url || ''
      } catch (e) { Swal.fire('Error', 'No se pudo subir la imagen.', 'error') }
    },
    async deleteItem(type, id) {
      const result = await Swal.fire({ title: 'Eliminar', text: '¿Estás seguro?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Eliminar', cancelButtonText: 'Cancelar', confirmButtonColor: '#DC2626' })
      if (!result.isConfirmed) return
      try {
        if (type === 'generacion') { await api.delete('/generaciones/' + id) }
        else { const map = { noticia: 'noticias', aviso: 'avisos', ciclo: 'ciclos' }; await api.delete('/configuracion/' + map[type] + '/' + id) }
        this.refreshTab(); Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Eliminado', showConfirmButton: false, timer: 2000 }) } catch (e) { Swal.fire('Error', 'No se pudo eliminar.', 'error') }
    },
    refreshTab() { const f = { noticias: 'fetchNoticias', avisos: 'fetchAvisos', ciclos: 'fetchCiclos', generaciones: 'fetchGeneraciones' }; if (f[this.activeTab]) this[f[this.activeTab]]() },

    openAulaModal(item) {
      this.editAulaId = item?.id || null
      this.aulaForm = item ? { ...item } : { nombre: '', edificio: '', capacidad: 30 }
      if (!this.aulaModalInstance) this.aulaModalInstance = new Modal(this.$refs.aulaModal)
      this.aulaModalInstance.show()
    },
    async saveAula() {
      this.savingCrud = true
      try {
        if (this.editAulaId) { await api.put('/aulas/' + this.editAulaId, this.aulaForm) } else { await api.post('/aulas', this.aulaForm) }
        this.aulaModalInstance.hide(); this.fetchAulas()
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Aula guardada', showConfirmButton: false, timer: 2000, timerProgressBar: true })
      } catch (e) { Swal.fire('Error', 'No se pudo guardar el aula.', 'error') } finally { this.savingCrud = false }
    },
    async deleteAula(a) {
      const result = await Swal.fire({ title: 'Eliminar aula', text: `¿Eliminar ${a.nombre}?`, icon: 'warning', showCancelButton: true, confirmButtonText: 'Eliminar', cancelButtonText: 'Cancelar', confirmButtonColor: '#DC2626' })
      if (!result.isConfirmed) return
      try { await api.delete('/aulas/' + a.id); this.fetchAulas(); Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Aula eliminada', showConfirmButton: false, timer: 2000 }) } catch (e) { Swal.fire('Error', e.response?.data?.message || 'No se pudo eliminar.' , 'error') }
    },
  },
  mounted() { this.fetchConfiguracion(); this.fetchNoticias(); this.fetchAvisos(); this.fetchCiclos() },
}
</script>

<style scoped>
.module-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; gap: 1rem; }
.header-info { display: flex; align-items: center; gap: 1rem; }
.header-icon { width: 48px; height: 48px; border-radius: 14px; background: linear-gradient(135deg, var(--color-primary), var(--color-primary-hover)); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.35rem; box-shadow: 0 4px 12px rgba(169,30,34,0.25); }
.header-title { font-size: 1.5rem; font-weight: 700; margin: 0; }
.header-subtitle { font-size: .8125rem; color: var(--color-text-secondary); margin: 2px 0 0; }
.tabs-nav { display: flex; gap: .375rem; margin-bottom: 1.5rem; flex-wrap: wrap; background: var(--color-bg); border: 1px solid var(--color-border-light); border-radius: 14px; padding: .375rem; }
.tab-btn { padding: .5rem 1.125rem; border: none; border-radius: 10px; font-size: .8125rem; font-weight: 500; cursor: pointer; color: var(--color-text-secondary); background: none; display: flex; align-items: center; gap: .375rem; transition: all .15s; }
.tab-btn:hover { color: var(--color-text); }
.tab-btn.active { background: var(--color-primary); color: #fff; font-weight: 600; box-shadow: var(--shadow-glow); }
.card { border: 1px solid var(--color-border); border-radius: var(--radius-lg); box-shadow: var(--shadow-xs); overflow: hidden; background: var(--color-bg-secondary); }
.card-header { display: flex; align-items: center; gap: .5rem; padding: .875rem 1.25rem; background: var(--color-bg-tertiary); border-bottom: 1px solid var(--color-border); font-weight: 600; font-size: .875rem; }
.card-header i { color: var(--color-primary); }
.card-body { padding: 1.25rem; }
.config-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem; }
.config-item { display: flex; flex-direction: column; gap: .3rem; }
.config-label { font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--color-text-muted); }
.config-desc { font-size: .6875rem; color: var(--color-text-muted); font-style: italic; }
.field-input { padding: .5625rem .75rem; border: 1.5px solid var(--color-border); border-radius: 10px; font-size: .875rem; font-family: var(--font-family); color: var(--color-text); background: var(--color-bg); outline: none; transition: border-color .2s, box-shadow .2s; }
.field-input:focus { border-color: var(--color-primary); box-shadow: 0 0 0 3px var(--color-primary-light); }
.field-textarea { resize: vertical; min-height: 50px; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table thead th { text-align: left; padding: .75rem 1rem; font-size: .6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--color-text-muted); background: var(--color-bg-tertiary); border-bottom: 1px solid var(--color-border-light); }
.data-table tbody td { padding: .75rem 1rem; font-size: .875rem; color: var(--color-text); border-bottom: 1px solid var(--color-border-light); }
.data-table tbody tr:hover { background: var(--color-bg-tertiary); }
.data-table tbody tr:last-child td { border-bottom: none; }
.fw-semibold { font-weight: 600; }
.status-dot { display: inline-block; width: 8px; height: 8px; border-radius: 50%; margin-right: .375rem; }
.status-dot.activo { background: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,0.15); }
.status-dot.inactivo { background: #6B7280; box-shadow: 0 0 0 3px rgba(107,114,128,0.15); }
.action-group { display: flex; gap: .25rem; justify-content: flex-end; }
.action-btn { width: 32px; height: 32px; border: none; border-radius: 8px; background: none; color: var(--color-text-secondary); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: .8125rem; transition: all .15s; }
.action-btn:hover { background: var(--color-bg-secondary); color: var(--color-text); transform: scale(1.08); }
.action-delete:hover { color: var(--color-danger); background: var(--color-danger-light); }
.empty-state { display: flex; flex-direction: column; align-items: center; padding: 3rem 2rem; text-align: center; }
.empty-illustration { width: 64px; height: 64px; border-radius: 50%; background: var(--color-bg-secondary); display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; font-size: 1.5rem; color: var(--color-text-muted); }
.empty-state h3 { font-size: 1rem; font-weight: 600; margin: 0 0 .25rem; }
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
.brand-section { padding: 1rem; background: var(--color-bg-tertiary); border-radius: 12px; border: 1px solid var(--color-border-light); }
.section-label { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--color-text-secondary); margin: 0 0 1rem; }
.brand-row { display: flex; gap: 1.5rem; flex-wrap: wrap; align-items: flex-start; }
.brand-item { display: flex; flex-direction: column; align-items: center; gap: .375rem; }
.brand-preview { width: 160px; height: 100px; border: 2px dashed var(--color-border); border-radius: 12px; display: flex; align-items: center; justify-content: center; cursor: pointer; overflow: hidden; transition: border-color .2s; background: var(--color-bg); }
.brand-preview:hover { border-color: var(--color-primary); }
.brand-preview img { width: 100%; height: 100%; object-fit: contain; }
.brand-favicon { width: 100px; height: 100px; border-radius: 16px; }
.brand-placeholder { display: flex; flex-direction: column; align-items: center; gap: .375rem; color: var(--color-text-muted); font-size: .75rem; }
.brand-placeholder i { font-size: 1.5rem; }
</style>
