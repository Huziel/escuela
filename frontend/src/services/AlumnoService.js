import api from '@/api/axios'

const AlumnoService = {
  getAll(params = {}) {
    return api.get('/alumnos', { params })
  },

  getById(id) {
    return api.get(`/alumnos/${id}`)
  },

  create(data) {
    return api.post('/alumnos', data)
  },

  update(id, data) {
    return api.put(`/alumnos/${id}`, data)
  },

  delete(id) {
    return api.delete(`/alumnos/${id}`)
  },

  bloquear(id) {
    return api.post(`/alumnos/${id}/bloquear`)
  },

  desbloquear(id) {
    return api.post(`/alumnos/${id}/desbloquear`)
  },

  historial(id) {
    return api.get(`/alumnos/${id}/historial`)
  },
}

export default AlumnoService
