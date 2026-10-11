import api from '@/api/axios'

const CalificacionService = {
  getAll(params = {}) {
    return api.get('/calificaciones', { params })
  },

  getById(id) {
    return api.get(`/calificaciones/${id}`)
  },

  create(data) {
    return api.post('/calificaciones', data)
  },

  update(id, data) {
    return api.put(`/calificaciones/${id}`, data)
  },

  delete(id) {
    return api.delete(`/calificaciones/${id}`)
  },

  kardex(alumnoId) {
    return api.get(`/calificaciones/kardex/${alumnoId}`)
  },
}

export default CalificacionService
