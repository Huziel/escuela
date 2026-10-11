import api from '@/api/axios'

const GrupoService = {
  getAll(params = {}) {
    return api.get('/grupos', { params })
  },

  listAll() {
    return api.get('/grupos/all')
  },

  getById(id) {
    return api.get(`/grupos/${id}`)
  },

  create(data) {
    return api.post('/grupos', data)
  },

  update(id, data) {
    return api.put(`/grupos/${id}`, data)
  },

  delete(id) {
    return api.delete(`/grupos/${id}`)
  },
}

export default GrupoService
