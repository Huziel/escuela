<template>
  <div>
    <div class="page-header">
      <div class="page-title">
        <i class="fas fa-bell"></i>Notificaciones
      </div>
      <div class="page-actions">
        <button class="btn btn-outline-primary btn-sm" @click="marcarTodasLeidas" :disabled="loadingAll">
          <span v-if="loadingAll" class="spinner-border spinner-border-sm me-1"></span>
          <i v-else class="fas fa-check-double me-1"></i>
          Marcar todas como leidas
        </button>
      </div>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
    </div>

    <div v-else-if="!notificaciones.length" class="table-empty mt-3">
      <i class="fas fa-bell-slash"></i>
      <p>No tienes notificaciones</p>
    </div>

    <div v-else class="card">
      <div class="card-body p-0">
        <div class="list-group list-group-flush">
          <a
            v-for="item in notificaciones"
            :key="item.id"
            href="#"
            class="list-group-item list-group-item-action d-flex align-items-start gap-3 px-4 py-3 border-0 border-bottom"
            :class="{ 'bg-light': item.leida }"
            @click.prevent="handleClick(item)"
          >
            <span class="fs-5 mt-1 flex-shrink-0" :style="{ color: iconColor(item.tipo) }">
              <i :class="iconClass(item.tipo)"></i>
            </span>
            <div class="flex-grow-1 min-w-0">
              <div class="d-flex w-100 justify-content-between align-items-start">
                <h6 class="mb-1" :class="{ 'fw-bold': !item.leida }">{{ item.titulo || item.title }}</h6>
                <span class="badge flex-shrink-0 ms-2" :class="item.leida ? 'badge-secondary' : 'badge-primary'">
                  {{ item.leida ? 'Leido' : 'Nuevo' }}
                </span>
              </div>
              <p class="mb-1 small text-muted text-truncate">{{ item.mensaje || item.message }}</p>
              <small class="text-muted">
                <i class="fas fa-clock me-1"></i>{{ formatDate(item.created_at || item.fecha) }}
              </small>
            </div>
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/api/axios'
import Swal from 'sweetalert2'

export default {
  name: 'NotificacionesList',
  data() {
    return {
      notificaciones: [],
      loading: false,
      loadingAll: false,
    }
  },
  methods: {
    async fetchNotificaciones() {
      this.loading = true
      try {
        const { data } = await api.get('/notificaciones')
        this.notificaciones = data.data || data || []
      } catch (e) {
        console.error('Error al cargar notificaciones', e)
      } finally {
        this.loading = false
      }
    },
    async handleClick(item) {
      if (!item.leida) {
        try {
          await api.post(`/notificaciones/${item.id}/leida`)
          item.leida = true
        } catch (e) {
          console.error('Error al marcar como leida', e)
        }
      }
      this.$emit('notification-read', item)
      if (item.url) {
        this.$router.push(item.url)
      }
    },
    async marcarTodasLeidas() {
      this.loadingAll = true
      try {
        await api.post('/notificaciones/marcar-todas')
        this.notificaciones.forEach((n) => (n.leida = true))
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Todas las notificaciones marcadas como leidas', showConfirmButton: false, timer: 2000, timerProgressBar: true })
      } catch (e) {
        Swal.fire('Error', 'No se pudieron marcar como leidas.', 'error')
      } finally {
        this.loadingAll = false
      }
    },
    formatDate(dateStr) {
      if (!dateStr) return '-'
      const d = new Date(dateStr)
      return d.toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
    },
    iconClass(tipo) {
      const map = {
        info: 'fas fa-circle-info',
        warning: 'fas fa-triangle-exclamation',
        error: 'fas fa-circle-xmark',
        success: 'fas fa-circle-check',
      }
      return map[tipo] || 'fas fa-circle-info'
    },
    iconColor(tipo) {
      const map = {
        info: '#0284C7',
        warning: '#D97706',
        error: '#DC2626',
        success: '#059669',
      }
      return map[tipo] || '#0284C7'
    },
  },
  mounted() {
    this.fetchNotificaciones()
  },
}
</script>
