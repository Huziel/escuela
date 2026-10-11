import api from '@/api/axios'

const NotificacionService = {
  getAll() {
    return api.get('/notificaciones')
  },

  getNoLeidas() {
    return api.get('/notificaciones/no-leidas')
  },

  marcarLeida(id) {
    return api.post(`/notificaciones/${id}/leida`)
  },

  marcarTodasLeidas() {
    return api.post('/notificaciones/marcar-todas')
  },
}

export default NotificacionService
