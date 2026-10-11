<template>
  <div class="usuarios-page">
    <div class="page-header">
      <h1 class="page-title">
        <i class="fa-solid fa-users-gear"></i> Usuarios
      </h1>
      <div class="page-actions">
        <button class="btn btn-primary" @click="openModal('create')">
          <i class="fa-solid fa-plus"></i> Nuevo Usuario
        </button>
      </div>
    </div>

    <div class="card usuarios-card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table mb-0">
            <thead>
              <tr>
                <th>Username</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Ultimo Acceso</th>
                <th class="text-end">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in usuarios" :key="user.id">
                <td>
                  <span class="fw-semibold">{{ user.username }}</span>
                </td>
                <td>{{ user.nombre }} {{ user.apellido_paterno }} {{ user.apellido_materno }}</td>
                <td>{{ user.email }}</td>
                <td>
                  <span class="badge" :class="rolBadgeClass(user.rol)">{{ user.rol }}</span>
                </td>
                <td>
                  <span class="badge" :class="user.activo ? 'badge-success' : 'badge-danger'">
                    <i class="fa-solid" :class="user.activo ? 'fa-circle-check' : 'fa-circle-xmark'"></i>
                    {{ user.activo ? 'Activo' : 'Inactivo' }}
                  </span>
                </td>
                <td>
                  <span class="text-muted small">{{ formatDate(user.ultimo_acceso) }}</span>
                </td>
                <td>
                  <div class="btn-group btn-group-sm">
                    <button class="btn btn-sm btn-outline-info btn-icon" title="Bitacora" @click="openBitacora(user)">
                      <i class="fa-solid fa-clock-rotate-left"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary btn-icon" title="Permisos" @click="openPermisos(user)">
                      <i class="fa-solid fa-shield-halved"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-primary btn-icon" title="Editar" @click="openModal('edit', user)">
                      <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button
                      class="btn btn-sm btn-icon"
                      :class="user.activo ? 'btn-outline-warning' : 'btn-outline-success'"
                      :title="user.activo ? 'Bloquear' : 'Desbloquear'"
                      @click="toggleBlock(user)"
                    >
                      <i class="fa-solid" :class="user.activo ? 'fa-lock' : 'fa-lock-open'"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger btn-icon" title="Eliminar" @click="confirmDelete(user)">
                      <i class="fa-solid fa-trash-can"></i>
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!usuarios.length && !loading">
                <td colspan="7" class="table-empty">
                  <i class="fa-solid fa-users-slash"></i>
                  <p class="mb-0 mt-2">Sin usuarios registrados</p>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="modal fade" ref="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="userModalLabel">
              <i class="fa-solid" :class="modalMode === 'edit' ? 'fa-user-pen' : 'fa-user-plus'"></i>
              {{ modalMode === 'edit' ? 'Editar' : 'Nuevo' }} Usuario
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Username <span class="text-danger">*</span></label>
                <input class="form-control" v-model="userForm.username" :disabled="modalMode === 'edit'" placeholder="Nombre de usuario" />
              </div>
              <div class="col-md-6">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" class="form-control" v-model="userForm.email" placeholder="correo@ejemplo.com" />
              </div>
              <div class="col-md-4">
                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                <input class="form-control" v-model="userForm.nombre" placeholder="Nombre(s)" />
              </div>
              <div class="col-md-4">
                <label class="form-label">Apellido Paterno <span class="text-danger">*</span></label>
                <input class="form-control" v-model="userForm.apellido_paterno" placeholder="Paterno" />
              </div>
              <div class="col-md-4">
                <label class="form-label">Apellido Materno</label>
                <input class="form-control" v-model="userForm.apellido_materno" placeholder="Materno" />
              </div>
              <div class="col-md-6">
                <label class="form-label">Contraseña <span class="text-danger" v-if="modalMode !== 'edit'">*</span></label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                  <input :type="showPass ? 'text' : 'password'" class="form-control" v-model="userForm.password" placeholder="Contrasena" />
                  <button class="btn btn-outline-secondary btn-sm" type="button" @click="showPass = !showPass" style="border-radius: 0 var(--radius-md) var(--radius-md) 0; height: auto; min-height: 38px;">
                    <i class="fa-solid" :class="showPass ? 'fa-eye-slash' : 'fa-eye'"></i>
                  </button>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Rol <span class="text-danger">*</span></label>
                <select class="form-select" v-model="userForm.rol">
                  <option value="admin">Admin</option>
                  <option value="docente">Docente</option>
                  <option value="alumno">Alumno</option>
                  <option value="padre">Padre</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Sexo</label>
                <select class="form-select" v-model="userForm.sexo">
                  <option value="">-- Seleccionar --</option>
                  <option value="M">Masculino</option>
                  <option value="F">Femenino</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">Telefono</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                  <input class="form-control" v-model="userForm.telefono" placeholder="Telefono" />
                </div>
              </div>
              <div class="col-12">
                <label class="form-label">Direccion</label>
                <div class="input-group">
                  <span class="input-group-text"><i class="fa-solid fa-location-dot"></i></span>
                  <textarea class="form-control" rows="2" v-model="userForm.direccion" placeholder="Direccion completa"></textarea>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="fa-solid fa-xmark"></i> Cancelar
            </button>
            <button type="button" class="btn btn-primary" @click="saveUser" :disabled="savingUser">
              <span v-if="savingUser" class="spinner-border spinner-border-sm me-1"></span>
              <i v-else class="fa-solid fa-floppy-disk"></i>
              Guardar
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" ref="bitacoraModal" tabindex="-1" aria-labelledby="bitacoraModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="bitacoraModalLabel">
              <i class="fa-solid fa-clock-rotate-left"></i>
              Bitacora de Accesos &mdash; {{ bitacoraUser?.username }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
          </div>
          <div class="modal-body p-0">
            <div class="table-responsive">
              <table class="table table-sm mb-0">
                <thead>
                  <tr>
                    <th>Fecha / Hora</th>
                    <th>Accion</th>
                    <th>IP</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="log in bitacoraLogs" :key="log.id">
                    <td>{{ formatDate(log.created_at || log.fecha) }}</td>
                    <td>{{ log.accion || log.action || '-' }}</td>
                    <td><code>{{ log.ip || '-' }}</code></td>
                  </tr>
                  <tr v-if="!bitacoraLogs.length">
                    <td colspan="3" class="table-empty">
                      <i class="fa-solid fa-inbox"></i>
                      <p class="mb-0 mt-2">Sin registros</p>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" ref="permisosModal" tabindex="-1">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <div class="modal-icon" style="background:rgba(59,130,246,0.15);color:#60A5FA">
              <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
              <h5 class="modal-title">Permisos — {{ permisosUser?.username }}</h5>
              <p class="modal-subtitle">
                Rol <span class="badge" :class="rolBadgeClass(permisosUser?.rol)">{{ permisosUser?.rol }}</span>
              </p>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div v-if="permisosLoading" class="text-center py-5">
              <div class="spinner-border text-primary"></div>
              <p class="text-muted mt-2">Cargando permisos...</p>
            </div>
            <div v-else>
              <div class="permisos-toolbar">
                <span class="permisos-count">{{ permisosSeleccionados.length }} / {{ totalPermisos }} permisos</span>
                <div>
                  <button class="btn btn-sm btn-outline-secondary me-1" @click="toggleAllPermisos(true)">Todo</button>
                  <button class="btn btn-sm btn-outline-secondary" @click="toggleAllPermisos(false)">Nada</button>
                </div>
              </div>
              <div class="permisos-accordion">
                <div v-for="(perms, modulo) in permisosDisponibles" :key="modulo" class="permiso-group">
                  <div class="permiso-group-header" @click="toggleModulo(modulo)">
                    <div class="permiso-group-left">
                      <i class="fa-solid fa-chevron-right permiso-chevron" :class="{open: modulosAbiertos[modulo]}"></i>
                      <span class="permiso-group-icon"><i :class="moduloIcon(modulo)"></i></span>
                      <span class="permiso-group-name text-capitalize">{{ modulo }}</span>
                    </div>
                    <span class="permiso-group-count">{{ seleccionadosEnModulo(perms) }}/{{ perms.length }}</span>
                  </div>
                  <div class="permiso-group-body" v-show="modulosAbiertos[modulo]">
                    <div class="permiso-chips">
                      <label v-for="p in perms" :key="p" class="permiso-chip" :class="{active: permisosSeleccionados.includes(p)}">
                        <input type="checkbox" :value="p" v-model="permisosSeleccionados" hidden />
                        {{ p.split('.').slice(1).join('.') }}
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-primary" @click="savePermisos" :disabled="permisosSaving">
              <i v-if="permisosSaving" class="fa-solid fa-circle-notch fa-spin"></i>
              <i v-else class="fa-solid fa-floppy-disk"></i>
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

export default {
  name: 'UsuariosList',
  data() {
    return {
      usuarios: [],
      loading: false,
      userForm: {},
      modalMode: 'create',
      savingUser: false,
      userModalInstance: null,
      showPass: false,
      bitacoraLogs: [],
      bitacoraUser: null,
      bitacoraModalInstance: null,
      permisosUser: null,
      permisosDisponibles: {},
      permisosSeleccionados: [],
      permisosRoles: [],
      permisosLoading: false,
      permisosSaving: false,
      permisosModalInstance: null,
      modulosAbiertos: {},
    }
  },
  computed: {
    totalPermisos() {
      return Object.values(this.permisosDisponibles).flat().length
    },
  },
  methods: {
    rolBadge(rol) {
      const map = { admin: 'bg-danger', docente: 'bg-primary', alumno: 'bg-success', padre: 'bg-info text-dark' }
      return map[rol] || 'bg-secondary'
    },
    rolBadgeClass(rol) {
      const map = { admin: 'badge-danger', docente: 'badge-primary', alumno: 'badge-success', padre: 'badge-info' }
      return map[rol] || 'badge-secondary'
    },
    formatDate(d) {
      if (!d) return '-'
      return new Date(d).toLocaleString('es-MX', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
    },

    async fetchUsuarios() {
      this.loading = true
      try {
        const { data } = await api.get('/usuarios')
        this.usuarios = data.data || data || []
      } catch (e) {
        console.error('Error al cargar usuarios', e)
      } finally {
        this.loading = false
      }
    },

    openModal(mode, user = null) {
      this.modalMode = mode
      this.showPass = false
      if (mode === 'edit' && user) {
        this.userForm = { ...user }
      } else {
        this.userForm = {
          username: '',
          nombre: '',
          apellido_paterno: '',
          apellido_materno: '',
          email: '',
          password: '',
          rol: 'docente',
          sexo: '',
          telefono: '',
          direccion: '',
        }
      }
      if (!this.userModalInstance) {
        this.userModalInstance = new Modal(this.$refs.userModal)
      }
      this.userModalInstance.show()
    },

    async saveUser() {
      this.savingUser = true
      try {
        if (this.modalMode === 'edit') {
          const payload = { ...this.userForm }
          if (!payload.password) delete payload.password
          await api.put(`/usuarios/${this.userForm.id}`, payload)
        } else {
          await api.post('/usuarios', this.userForm)
        }
        this.userModalInstance.hide()
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Usuario guardado', showConfirmButton: false, timer: 2000, timerProgressBar: true })
        this.fetchUsuarios()
      } catch (e) {
        Swal.fire('Error', 'No se pudo guardar el usuario.', 'error')
      } finally {
        this.savingUser = false
      }
    },

    async toggleBlock(user) {
      const action = user.activo ? 'bloquear' : 'desbloquear'
      const result = await Swal.fire({
        title: user.activo ? 'Bloquear Usuario' : 'Desbloquear Usuario',
        text: `Estas seguro de ${action} a ${user.username}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: `Si, ${action}`,
        cancelButtonText: 'Cancelar',
      })
      if (!result.isConfirmed) return
      try {
        const endpoint = user.activo ? 'bloquear' : 'desbloquear'
        await api.post(`/usuarios/${user.id}/${endpoint}`)
        user.activo = !user.activo
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: `Usuario ${action}`, showConfirmButton: false, timer: 2000, timerProgressBar: true })
      } catch (e) {
        Swal.fire('Error', 'No se pudo cambiar el estado.', 'error')
      }
    },

    async confirmDelete(user) {
      const result = await Swal.fire({
        title: 'Eliminar Usuario',
        text: `Estas seguro de eliminar a ${user.username}? Esta accion no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Si, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
      })
      if (!result.isConfirmed) return
      try {
        await api.delete(`/usuarios/${user.id}`)
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Usuario eliminado', showConfirmButton: false, timer: 2000, timerProgressBar: true })
        this.fetchUsuarios()
      } catch (e) {
        Swal.fire('Error', 'No se pudo eliminar el usuario.', 'error')
      }
    },

    async openBitacora(user) {
      this.bitacoraUser = user
      this.bitacoraLogs = []
      try {
        const { data } = await api.get(`/usuarios/${user.id}/bitacora`)
        this.bitacoraLogs = data.data || data || []
      } catch (e) {
        console.error('Error al cargar bitacora', e)
      }
      if (!this.bitacoraModalInstance) {
        this.bitacoraModalInstance = new Modal(this.$refs.bitacoraModal)
      }
      this.bitacoraModalInstance.show()
    },

    async openPermisos(user) {
      this.permisosUser = user
      this.permisosSeleccionados = []
      this.permisosRoles = []
      this.permisosLoading = true
      this.modulosAbiertos = {}
      try {
        const [disponibles, userPerms] = await Promise.all([
          api.get('/usuarios/permisos-disponibles'),
          api.get(`/usuarios/${user.id}/permisos`),
        ])
        this.permisosDisponibles = disponibles.data.data || disponibles.data || {}
        this.permisosSeleccionados = [...(userPerms.data.data?.all || userPerms.data?.all || [])]
        this.permisosRoles = userPerms.data.data?.roles || userPerms.data?.roles || []
        Object.keys(this.permisosDisponibles).forEach(m => { this.modulosAbiertos[m] = true })
      } catch (e) {
        Swal.fire('Error', 'No se pudieron cargar los permisos.', 'error')
        return
      } finally {
        this.permisosLoading = false
      }
      if (!this.permisosModalInstance) {
        this.permisosModalInstance = new Modal(this.$refs.permisosModal)
      }
      this.permisosModalInstance.show()
    },
    moduloIcon(modulo) {
      const icons = {
        usuarios: 'fa-solid fa-users-gear', alumnos: 'fa-solid fa-user-graduate',
        docentes: 'fa-solid fa-chalkboard-user', grupos: 'fa-solid fa-layer-group',
        especialidades: 'fa-solid fa-graduation-cap', materias: 'fa-solid fa-book-open',
        inscripciones: 'fa-solid fa-file-signature', calificaciones: 'fa-solid fa-star',
        asistencia: 'fa-solid fa-calendar-check', tutores: 'fa-solid fa-user-tie',
        regularizacion: 'fa-solid fa-rotate', reportes: 'fa-solid fa-chart-bar',
        configuracion: 'fa-solid fa-gear', auditoria: 'fa-solid fa-magnifying-glass',
        dashboard: 'fa-solid fa-gauge-high', horarios: 'fa-solid fa-clock',
        notificaciones: 'fa-solid fa-bell',
      }
      return icons[modulo] || 'fa-solid fa-circle'
    },
    seleccionadosEnModulo(perms) {
      return perms.filter(p => this.permisosSeleccionados.includes(p)).length
    },
    toggleModulo(modulo) {
      this.modulosAbiertos[modulo] = !this.modulosAbiertos[modulo]
    },
    toggleAllPermisos(select) {
      if (select) {
        const all = Object.values(this.permisosDisponibles).flat()
        this.permisosSeleccionados = [...new Set([...this.permisosSeleccionados, ...all])]
      } else {
        this.permisosSeleccionados = []
      }
    },
    async savePermisos() {
      this.permisosSaving = true
      try {
        await api.put(`/usuarios/${this.permisosUser.id}/permisos`, { permissions: this.permisosSeleccionados })
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Permisos actualizados', showConfirmButton: false, timer: 2000, timerProgressBar: true })
        this.permisosModalInstance.hide()
      } catch (e) {
        Swal.fire('Error', 'No se pudieron guardar los permisos.', 'error')
      } finally {
        this.permisosSaving = false
      }
    },
  },
  mounted() {
    this.fetchUsuarios()
  },
}
</script>

<style scoped>
.usuarios-card {
  border: 1px solid var(--color-border-light);
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
}

.btn-group {
  display: flex;
  gap: var(--space-1);
}

.input-group .form-control:first-child {
  border-radius: var(--radius-md) 0 0 var(--radius-md);
}

.modal-dialog-centered {
  min-height: calc(100% - 3.5rem);
}

.fw-semibold {
  font-weight: var(--font-weight-semibold);
}

.text-muted {
  color: var(--color-text-muted);
}

code {
  font-family: var(--font-family-mono);
  font-size: var(--font-size-xs);
  color: var(--color-secondary);
  background: var(--color-secondary-light);
  padding: 2px 6px;
  border-radius: var(--radius-sm);
}

.small {
  font-size: var(--font-size-xs);
}

.permisos-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: .625rem .875rem;
  margin-bottom: .75rem;
  background: var(--color-bg-secondary, rgba(255,255,255,0.03));
  border-radius: 10px;
  border: 1px solid var(--color-border-light, rgba(255,255,255,0.05));
}
.permisos-count {
  font-size: .8125rem;
  font-weight: 600;
  color: var(--color-text-muted);
}
.permisos-accordion {
  max-height: 55vh;
  overflow-y: auto;
}
.permiso-group {
  margin-bottom: .5rem;
  border: 1px solid var(--color-border-light, rgba(255,255,255,0.06));
  border-radius: 10px;
  overflow: hidden;
  background: var(--color-bg-secondary, rgba(255,255,255,0.02));
  transition: border-color .2s;
}
.permiso-group:hover {
  border-color: var(--color-border, rgba(255,255,255,0.12));
}
.permiso-group-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: .625rem .875rem;
  cursor: pointer;
  user-select: none;
  transition: background .15s;
}
.permiso-group-header:hover {
  background: var(--color-bg-tertiary, rgba(255,255,255,0.04));
}
.permiso-group-left {
  display: flex;
  align-items: center;
  gap: .625rem;
}
.permiso-chevron {
  font-size: .625rem;
  color: var(--color-text-muted);
  transition: transform .2s;
}
.permiso-chevron.open {
  transform: rotate(90deg);
}
.permiso-group-icon {
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 7px;
  background: var(--color-primary);
  color: #fff;
  font-size: .75rem;
}
.permiso-group-name {
  font-size: .8125rem;
  font-weight: 600;
  color: var(--color-text);
}
.permiso-group-count {
  font-size: .6875rem;
  font-weight: 600;
  color: var(--color-text-muted);
  background: var(--color-bg, rgba(0,0,0,0.15));
  padding: .125rem .5rem;
  border-radius: 20px;
}
.permiso-group-body {
  padding: .5rem .875rem .75rem;
}
.permiso-chips {
  display: flex;
  flex-wrap: wrap;
  gap: .375rem;
}
.permiso-chip {
  display: inline-flex;
  align-items: center;
  padding: .25rem .625rem;
  font-size: .75rem;
  font-weight: 500;
  border-radius: 6px;
  cursor: pointer;
  user-select: none;
  border: 1.5px solid var(--color-border, rgba(255,255,255,0.1));
  color: var(--color-text-muted);
  background: transparent;
  transition: all .15s;
}
.permiso-chip:hover {
  border-color: var(--color-primary);
  color: var(--color-text);
}
.permiso-chip.active {
  background: var(--color-primary);
  border-color: var(--color-primary);
  color: #fff;
  box-shadow: 0 2px 8px rgba(198,40,40,0.25);
}
</style>
