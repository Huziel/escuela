import api from '@/api/axios'

const UsuarioService = {
  getAll(params = {}) {
    return api.get('/usuarios', { params })
  },

  getById(id) {
    return api.get(`/usuarios/${id}`)
  },

  create(data) {
    return api.post('/usuarios', data)
  },

  update(id, data) {
    return api.put(`/usuarios/${id}`, data)
  },

  delete(id) {
    return api.delete(`/usuarios/${id}`)
  },

  bloquear(id) {
    return api.post(`/usuarios/${id}/bloquear`)
  },

  desbloquear(id) {
    return api.post(`/usuarios/${id}/desbloquear`)
  },

  bitacora(id) {
    return api.get(`/usuarios/${id}/bitacora`)
  },
}

export default UsuarioService
