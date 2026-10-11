import api from '@/api/axios'

const DashboardService = {
  getStats() {
    return api.get('/dashboard/stats')
  },

  getCharts() {
    return api.get('/dashboard/charts')
  },
}

export default DashboardService
