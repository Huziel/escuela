import api from '@/api/axios'

const ConfiguracionService = {
  getConfig() {
    return api.get('/configuracion')
  },

  updateConfig(data) {
    return api.put('/configuracion', data)
  },

  getNoticias() {
    return api.get('/configuracion/noticias')
  },

  createNoticia(data) {
    return api.post('/configuracion/noticias', data)
  },

  updateNoticia(id, data) {
    return api.put(`/configuracion/noticias/${id}`, data)
  },

  deleteNoticia(id) {
    return api.delete(`/configuracion/noticias/${id}`)
  },

  getAvisos() {
    return api.get('/configuracion/avisos')
  },

  createAviso(data) {
    return api.post('/configuracion/avisos', data)
  },

  updateAviso(id, data) {
    return api.put(`/configuracion/avisos/${id}`, data)
  },

  deleteAviso(id) {
    return api.delete(`/configuracion/avisos/${id}`)
  },

  getCiclos() {
    return api.get('/configuracion/ciclos')
  },

  createCiclo(data) {
    return api.post('/configuracion/ciclos', data)
  },

  updateCiclo(id, data) {
    return api.put(`/configuracion/ciclos/${id}`, data)
  },

  deleteCiclo(id) {
    return api.delete(`/configuracion/ciclos/${id}`)
  },
}

export default ConfiguracionService
