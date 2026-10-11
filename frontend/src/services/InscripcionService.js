import api from '@/api/axios'

const InscripcionService = {
  getAll(params = {}) {
    return api.get('/inscripciones', { params })
  },

  getById(id) {
    return api.get(`/inscripciones/${id}`)
  },

  create(data) {
    return api.post('/inscripciones', data)
  },

  update(id, data) {
    return api.put(`/inscripciones/${id}`, data)
  },

  delete(id) {
    return api.delete(`/inscripciones/${id}`)
  },

  aprobar(id) {
    return api.post(`/inscripciones/${id}/aprobar`)
  },

  rechazar(id) {
    return api.post(`/inscripciones/${id}/rechazar`)
  },
}

export default InscripcionService
