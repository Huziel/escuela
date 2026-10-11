<template>
  <div>
    <div class="page-header">
      <div class="page-title">
        <i class="fas fa-magnifying-glass-chart"></i>Auditoria
      </div>
    </div>

    <div class="card">
      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
          </div>
        </div>

        <div v-else class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th style="width:150px">Fecha</th>
                <th>Usuario</th>
                <th>Accion</th>
                <th>Tabla</th>
                <th>Registro ID</th>
                <th>IP</th>
                <th style="width:60px"></th>
              </tr>
            </thead>
            <tbody>
              <template v-for="item in registros" :key="item.id">
                <tr
                  class="cursor-pointer"
                  :class="{ 'table-active': expandedId === item.id }"
                  @click="toggleExpand(item.id)"
                >
                  <td><small>{{ formatDate(item.created_at || item.fecha) }}</small></td>
                  <td>
                    <i class="fas fa-user-circle text-muted me-1"></i>
                    {{ item.usuario?.nombre || item.usuario || item.user || '-' }}
                  </td>
                  <td>
                    <span class="badge" :class="accionBadgeClass(item.accion || item.action)">
                      {{ item.accion || item.action }}
                    </span>
                  </td>
                  <td><span class="badge badge-neutral">{{ item.tabla || item.table_name || '-' }}</span></td>
                  <td><code class="text-muted small">{{ item.registro_id || item.record_id || '-' }}</code></td>
                  <td><small class="text-muted"><i class="fas fa-globe me-1"></i>{{ item.ip || '-' }}</small></td>
                  <td>
                    <button class="btn btn-sm btn-icon btn-outline-secondary" @click.stop="toggleExpand(item.id)">
                      <i class="fas" :class="expandedId === item.id ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>
                  </td>
                </tr>
                <tr v-if="expandedId === item.id" class="expand-row">
                  <td colspan="7" class="bg-light border-0 p-0">
                    <div class="p-3">
                      <div class="row g-3">
                        <div class="col-md-6">
                          <h6 class="text-uppercase small fw-bold text-muted mb-2">
                            <i class="fas fa-arrow-left-circle me-1"></i>Registro Anterior
                          </h6>
                          <pre class="bg-white border rounded p-2 small m-0" style="max-height:250px;overflow:auto">{{ jsonFormat(item.registro_anterior) }}</pre>
                        </div>
                        <div class="col-md-6">
                          <h6 class="text-uppercase small fw-bold text-muted mb-2">
                            <i class="fas fa-arrow-right-circle me-1"></i>Registro Nuevo
                          </h6>
                          <pre class="bg-white border rounded p-2 small m-0" style="max-height:250px;overflow:auto">{{ jsonFormat(item.registro_nuevo) }}</pre>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
              </template>
              <tr v-if="!registros.length && !loading">
                <td colspan="7">
                  <div class="table-empty">
                    <i class="fas fa-magnifying-glass-chart"></i>
                    <p>Sin registros de auditoria.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card-footer d-flex justify-content-between align-items-center" v-if="totalPages > 1">
        <small class="text-muted">Pagina {{ currentPage }} de {{ totalPages }}</small>
        <nav>
          <ul class="pagination mb-0">
            <li class="page-item" :class="{ disabled: currentPage <= 1 }">
              <button class="page-link" @click="changePage(currentPage - 1)">
                <i class="fas fa-chevron-left fa-xs"></i>
              </button>
            </li>
            <li
              v-for="p in visiblePages"
              :key="p"
              class="page-item"
              :class="{ active: p === currentPage }"
            >
              <button class="page-link" @click="changePage(p)">{{ p }}</button>
            </li>
            <li class="page-item" :class="{ disabled: currentPage >= totalPages }">
              <button class="page-link" @click="changePage(currentPage + 1)">
                <i class="fas fa-chevron-right fa-xs"></i>
              </button>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/api/axios'

export default {
  name: 'AuditoriaList',
  data() {
    return {
      registros: [],
      loading: false,
      currentPage: 1,
      perPage: 20,
      total: 0,
      expandedId: null,
    }
  },
  computed: {
    totalPages() {
      return Math.ceil(this.total / this.perPage) || 0
    },
    visiblePages() {
      const pages = []
      const total = this.totalPages
      const current = this.currentPage
      let start = Math.max(1, current - 2)
      let end = Math.min(total, current + 2)
      if (end - start < 4) {
        if (start === 1) end = Math.min(total, start + 4)
        else start = Math.max(1, end - 4)
      }
      for (let i = start; i <= end; i++) pages.push(i)
      return pages
    },
  },
  methods: {
    async fetchRegistros() {
      this.loading = true
      try {
        const { data } = await api.get('/auditoria', {
          params: { page: this.currentPage, per_page: this.perPage },
        })
        this.registros = data.data || data || []
        this.total = data.total || data.meta?.total || 0
      } catch (e) {
        console.error('Error al cargar auditoria', e)
      } finally {
        this.loading = false
      }
    },
    toggleExpand(id) {
      this.expandedId = this.expandedId === id ? null : id
    },
    changePage(page) {
      if (page < 1 || page > this.totalPages) return
      this.currentPage = page
      this.fetchRegistros()
    },
    accionBadgeClass(accion) {
      const a = (accion || '').toLowerCase()
      if (a === 'created' || a === 'creado' || a === 'crear') return 'badge-success'
      if (a === 'updated' || a === 'actualizado' || a === 'editar') return 'badge-warning'
      if (a === 'deleted' || a === 'eliminado' || a === 'eliminar') return 'badge-danger'
      return 'badge-secondary'
    },
    formatDate(dateStr) {
      if (!dateStr) return '-'
      const d = new Date(dateStr)
      return d.toLocaleString('es-MX', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' })
    },
    jsonFormat(val) {
      if (!val) return 'Sin datos'
      try {
        const parsed = typeof val === 'string' ? JSON.parse(val) : val
        return JSON.stringify(parsed, null, 2)
      } catch {
        return val
      }
    },
  },
  mounted() {
    this.fetchRegistros()
  },
}
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}
.expand-row td {
  padding: 0;
}
</style>
