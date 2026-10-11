import api from '@/api/axios'

const MateriaService = {
  getAll(params = {}) {
    return api.get('/materias', { params })
  },

  listAll() {
    return api.get('/materias/all')
  },

  getById(id) {
    return api.get(`/materias/${id}`)
  },

  create(data) {
    return api.post('/materias', data)
  },

  update(id, data) {
    return api.put(`/materias/${id}`, data)
  },

  delete(id) {
    return api.delete(`/materias/${id}`)
  },
}

export default MateriaService
