import api from '@/api/axios'

const RegularizacionService = {
  getAll(params = {}) {
    return api.get('/regularizacion', { params })
  },

  getById(id) {
    return api.get(`/regularizacion/${id}`)
  },

  create(data) {
    return api.post('/regularizacion', data)
  },

  update(id, data) {
    return api.put(`/regularizacion/${id}`, data)
  },

  delete(id) {
    return api.delete(`/regularizacion/${id}`)
  },
}

export default RegularizacionService
