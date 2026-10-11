import api from '@/api/axios'

const DocenteService = {
  getAll(params = {}) {
    return api.get('/docentes', { params })
  },

  listAll() {
    return api.get('/docentes/all')
  },

  getById(id) {
    return api.get(`/docentes/${id}`)
  },

  create(data) {
    return api.post('/docentes', data)
  },

  update(id, data) {
    return api.put(`/docentes/${id}`, data)
  },

  delete(id) {
    return api.delete(`/docentes/${id}`)
  },
}

export default DocenteService
