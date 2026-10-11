import api from '@/api/axios'

const PortalAlumnoService = {
  getDashboard() { return api.get('/portal-alumno/dashboard') },
  getPerfil() { return api.get('/portal-alumno/perfil') },
  getCalificaciones(params) { return api.get('/portal-alumno/calificaciones', { params }) },
  getKardex() { return api.get('/portal-alumno/kardex') },
  getAsistencias(params) { return api.get('/portal-alumno/asistencias', { params }) },
  getHorario() { return api.get('/portal-alumno/horario') },
  getBoleta(params) { return api.get('/portal-alumno/boleta', { params }) },
}

export default PortalAlumnoService
