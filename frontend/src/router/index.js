import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/views/auth/Login.vue'),
    meta: { guest: true, layout: 'blank' },
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: () => import('@/views/auth/ForgotPassword.vue'),
    meta: { guest: true, layout: 'blank' },
  },
  {
    path: '/',
    component: () => import('@/layouts/DashboardLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'Dashboard',
        component: () => import('@/views/dashboard/Dashboard.vue'),
        meta: { title: 'Dashboard' },
      },
      {
        path: 'alumnos',
        name: 'Alumnos',
        component: () => import('@/views/alumnos/AlumnosList.vue'),
        meta: { title: 'Registro de Alumnos', permission: 'alumnos.listar' },
      },
      {
        path: 'alumnos/seguimiento',
        name: 'AlumnosSeguimiento',
        component: () => import('@/views/alumnos/AlumnosSeguimiento.vue'),
        meta: { title: 'Seguimiento de Alumnos', permission: 'alumnos.listar' },
      },
      {
        path: 'alumnos/:id',
        name: 'AlumnoDetail',
        component: () => import('@/views/alumnos/AlumnoDetail.vue'),
        meta: { title: 'Detalle Alumno', permission: 'alumnos.listar' },
      },
      {
        path: 'docentes',
        name: 'Docentes',
        component: () => import('@/views/docentes/DocentesList.vue'),
        meta: { title: 'Registro de Docentes', permission: 'docentes.listar' },
      },
      {
        path: 'docentes/seguimiento',
        name: 'DocentesSeguimiento',
        component: () => import('@/views/docentes/DocentesSeguimiento.vue'),
        meta: { title: 'Seguimiento de Docentes', permission: 'docentes.listar' },
      },
      {
        path: 'docentes/:id',
        name: 'DocenteDetail',
        component: () => import('@/views/docentes/DocenteDetail.vue'),
        meta: { title: 'Detalle Docente', permission: 'docentes.listar' },
      },
      {
        path: 'grupos',
        name: 'Grupos',
        component: () => import('@/views/grupos/GruposList.vue'),
        meta: { title: 'Grupos', permission: 'grupos.listar' },
      },
      {
        path: 'especialidades',
        name: 'Especialidades',
        component: () => import('@/views/especialidades/EspecialidadesList.vue'),
        meta: { title: 'Especialidades', permission: 'especialidades.listar' },
      },
      {
        path: 'materias',
        name: 'Materias',
        component: () => import('@/views/materias/MateriasList.vue'),
        meta: { title: 'Materias', permission: 'materias.listar' },
      },
      {
        path: 'inscripciones',
        name: 'Inscripciones',
        component: () => import('@/views/inscripciones/InscripcionesList.vue'),
        meta: { title: 'Inscripciones', permission: 'inscripciones.listar' },
      },
      {
        path: 'calificaciones',
        name: 'Calificaciones',
        component: () => import('@/views/calificaciones/CalificacionesList.vue'),
        meta: { title: 'Calificaciones', permission: 'calificaciones.listar' },
      },
      {
        path: 'asistencia',
        name: 'Asistencia',
        component: () => import('@/views/asistencia/AsistenciaList.vue'),
        meta: { title: 'Asistencia', permission: 'asistencia.listar' },
      },
      {
        path: 'horarios',
        name: 'Horarios',
        component: () => import('@/views/horarios/HorariosList.vue'),
        meta: { title: 'Horarios' },
      },
      {
        path: 'tutores',
        name: 'Tutores',
        component: () => import('@/views/tutores/TutoresList.vue'),
        meta: { title: 'Tutores', permission: 'tutores.listar' },
      },
      {
        path: 'regularizacion',
        name: 'Regularizacion',
        component: () => import('@/views/regularizacion/RegularizacionList.vue'),
        meta: { title: 'Regularización', permission: 'regularizacion.listar' },
      },
      {
        path: 'reportes',
        name: 'Reportes',
        component: () => import('@/views/reportes/Reportes.vue'),
        meta: { title: 'Reportes', permission: 'reportes.ver' },
      },
      {
        path: 'notificaciones',
        name: 'Notificaciones',
        component: () => import('@/views/notificaciones/NotificacionesList.vue'),
        meta: { title: 'Notificaciones' },
      },
      {
        path: 'auditoria',
        name: 'Auditoria',
        component: () => import('@/views/auditoria/AuditoriaList.vue'),
        meta: { title: 'Auditoría', permission: 'auditoria.ver' },
      },
      {
        path: 'configuracion',
        name: 'Configuracion',
        component: () => import('@/views/configuracion/Configuracion.vue'),
        meta: { title: 'Configuración', permission: 'configuracion.ver' },
      },
      {
        path: 'usuarios',
        name: 'Usuarios',
        component: () => import('@/views/usuarios/UsuariosList.vue'),
        meta: { title: 'Usuarios', permission: 'usuarios.listar' },
      },
    ],
  },
  // Portal del Alumno — mobile-first, separate layout, no admin sidebar
  {
    path: '/portal',
    component: () => import('@/layouts/StudentLayout.vue'),
    meta: { permission: 'portal.ver' },
    children: [
      {
        path: '',
        name: 'PortalDashboard',
        component: () => import('@/views/portal/PortalDashboard.vue'),
        meta: { title: 'Mi Portal' },
      },
      {
        path: 'ajustes',
        name: 'PortalAjustes',
        component: () => import('@/views/portal/PortalAjustes.vue'),
        meta: { title: 'Ajustes' },
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to, from, next) => {
  const appStore = useAppStore()
  appStore.setLoading(true)

  const token = localStorage.getItem('token')
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !token) {
    return next('/login')
  }

  if (to.meta.guest && token) {
    return next('/')
  }

  if (token && !authStore.user) {
    try {
      await authStore.fetchUser()
    } catch (e) {
      localStorage.removeItem('token')
      return next('/login')
    }
  }

  if (to.meta.permission && authStore.user) {
    if (!authStore.hasPermission(to.meta.permission)) {
      return next(authStore.hasRole('alumno') ? '/portal' : '/')
    }
  }

  if (to.path === '/' && authStore.user && authStore.hasRole('alumno')) {
    return next('/portal')
  }

  next()
})

router.afterEach(() => {
  const appStore = useAppStore()
  setTimeout(() => appStore.setLoading(false), 200)
})

export default router
