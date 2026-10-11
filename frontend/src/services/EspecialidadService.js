import api from '@/api/axios'

const EspecialidadService = {
  getAll(params = {}) {
    return api.get('/especialidades', { params })
  },

  listAll() {
    return api.get('/especialidades/all')
  },

  getById(id) {
    return api.get(`/especialidades/${id}`)
  },

  create(data) {
    return api.post('/especialidades', data)
  },

  update(id, data) {
    return api.put(`/especialidades/${id}`, data)
  },

  delete(id) {
    return api.delete(`/especialidades/${id}`)
  },
}

export default EspecialidadService
