import { defineStore } from 'pinia'

export const useAppStore = defineStore('app', {
  state: () => ({
    sidebarCollapsed: false,
    loading: false,
    notificaciones: [],
    notifCount: 0,
  }),

  actions: {
    toggleSidebar() {
      this.sidebarCollapsed = !this.sidebarCollapsed
    },

    setLoading(val) {
      this.loading = val
    },
  },
})
