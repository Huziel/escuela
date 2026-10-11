import { defineStore } from 'pinia'
import DashboardService from '@/services/DashboardService'

export const useDashboardStore = defineStore('dashboard', {
  state: () => ({
    stats: {},
    charts: {},
    loading: false,
  }),

  actions: {
    async fetchStats() {
      this.loading = true
      try {
        const response = await DashboardService.getStats()
        this.stats = response.data
        return response
      } finally {
        this.loading = false
      }
    },

    async fetchCharts() {
      this.loading = true
      try {
        const response = await DashboardService.getCharts()
        this.charts = response.data
        return response
      } finally {
        this.loading = false
      }
    },
  },
})
