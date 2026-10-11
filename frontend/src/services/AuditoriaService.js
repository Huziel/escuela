import api from '@/api/axios'

const AuditoriaService = {
  getAll(params = {}) {
    return api.get('/auditoria', { params })
  },
}

export default AuditoriaService
