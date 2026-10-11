import api from '@/api/axios'

const ReporteService = {
  kardex(params = {}) {
    return api.get('/reportes/kardex', { params })
  },

  kardexPdf(params = {}) {
    return api.get('/reportes/kardex/pdf', { params, responseType: 'blob' })
  },

  boleta(params = {}) {
    return api.get('/reportes/boleta', { params })
  },

  asistencia(params = {}) {
    return api.get('/reportes/asistencia', { params })
  },

  indicadores() {
    return api.get('/reportes/indicadores')
  },
}

export default ReporteService
