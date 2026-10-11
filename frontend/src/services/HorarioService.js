import api from '@/api/axios'

const HorarioService = {
  getAll(params = {}) {
    return api.get('/horarios', { params })
  },

  getById(id) {
    return api.get(`/horarios/${id}`)
  },

  create(data) {
    return api.post('/horarios', data)
  },

  update(id, data) {
    return api.put(`/horarios/${id}`, data)
  },

  delete(id) {
    return api.delete(`/horarios/${id}`)
  },
}

export default HorarioService
