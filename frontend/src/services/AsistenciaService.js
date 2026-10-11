import api from '@/api/axios'

const AsistenciaService = {
  getAll(params = {}) {
    return api.get('/asistencia', { params })
  },

  getById(id) {
    return api.get(`/asistencia/${id}`)
  },

  create(data) {
    return api.post('/asistencia', data)
  },

  update(id, data) {
    return api.put(`/asistencia/${id}`, data)
  },

  delete(id) {
    return api.delete(`/asistencia/${id}`)
  },

  paseLista(data) {
    return api.post('/asistencia/pase-lista', data)
  },

  justificar(id) {
    return api.post(`/asistencia/${id}/justificar`)
  },
}

export default AsistenciaService
