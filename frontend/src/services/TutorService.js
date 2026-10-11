import api from '@/api/axios'

const TutorService = {
  getAll(params = {}) {
    return api.get('/tutores', { params })
  },

  listAll() {
    return api.get('/tutores/all')
  },

  getById(id) {
    return api.get(`/tutores/${id}`)
  },

  create(data) {
    return api.post('/tutores', data)
  },

  update(id, data) {
    return api.put(`/tutores/${id}`, data)
  },

  delete(id) {
    return api.delete(`/tutores/${id}`)
  },
}

export default TutorService
