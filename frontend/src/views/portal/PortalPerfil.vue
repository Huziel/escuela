<template>
  <div class="portal-page">
    <div class="page-header">
      <h1 class="page-title"><i class="fas fa-id-card"></i> Mi Perfil</h1>
    </div>

    <div v-if="loading" class="text-center py-5"><div class="spinner-border text-primary"></div></div>

    <div v-else-if="error" class="text-center py-5">
      <i class="fas fa-exclamation-triangle fa-2x mb-3" style="color:var(--color-danger)"></i>
      <p class="text-muted mb-3">{{ error }}</p>
      <button class="btn btn-primary" @click="load"><i class="fas fa-redo"></i> Reintentar</button>
    </div>

    <template v-else-if="perfil">
      <div class="profile-card">
        <div class="profile-photo-wrap">
          <img v-if="perfil.foto" :src="perfil.foto" class="profile-photo" />
          <div v-else class="profile-photo-empty"><i class="fas fa-user-graduate"></i></div>
        </div>
        <div class="profile-info">
          <h2>{{ perfil.nombre }}</h2>
          <span class="badge" :class="statusBadge(perfil.estatus)">{{ perfil.estatus }}</span>
          <div class="info-grid">
            <div class="info-item"><span class="info-label">Matrícula</span><span class="info-value">{{ perfil.matricula }}</span></div>
            <div class="info-item"><span class="info-label">CURP</span><span class="info-value">{{ perfil.curp || '—' }}</span></div>
            <div class="info-item"><span class="info-label">Especialidad</span><span class="info-value">{{ perfil.especialidad || '—' }}</span></div>
            <div class="info-item"><span class="info-label">Semestre</span><span class="info-value">{{ perfil.semestre }}°</span></div>
            <div class="info-item"><span class="info-label">Grupo</span><span class="info-value">{{ perfil.grupo || '—' }}</span></div>
            <div class="info-item"><span class="info-label">Turno</span><span class="info-value">{{ perfil.turno || '—' }}</span></div>
            <div class="info-item"><span class="info-label">Ciclo Escolar</span><span class="info-value">{{ perfil.ciclo_escolar || '—' }}</span></div>
            <div class="info-item"><span class="info-label">Fecha Ingreso</span><span class="info-value">{{ perfil.fecha_ingreso || '—' }}</span></div>
            <div class="info-item"><span class="info-label">Tutor</span><span class="info-value">{{ perfil.tutor || '—' }}</span></div>
          </div>
        </div>
      </div>

      <div class="section-card mt-3">
        <h6 class="section-label">Contacto</h6>
        <div class="info-grid">
          <div class="info-item"><span class="info-label">Email</span><span class="info-value">{{ perfil.email || '—' }}</span></div>
          <div class="info-item"><span class="info-label">Teléfono</span><span class="info-value">{{ perfil.telefono || '—' }}</span></div>
          <div class="info-item"><span class="info-label">Dirección</span><span class="info-value">{{ perfil.direccion || '—' }}</span></div>
        </div>
      </div>

      <div class="section-card mt-3">
        <h6 class="section-label">Tutor</h6>
        <div class="info-grid">
          <div class="info-item"><span class="info-label">Nombre</span><span class="info-value">{{ perfil.tutor || '—' }}</span></div>
          <div class="info-item"><span class="info-label">Teléfono</span><span class="info-value">{{ perfil.tutor_telefono || '—' }}</span></div>
          <div class="info-item"><span class="info-label">Parentesco</span><span class="info-value">{{ perfil.tutor_parentesco || '—' }}</span></div>
        </div>
      </div>
    </template>

    <div v-else class="text-center py-5 text-muted">No se pudo cargar la información.</div>
  </div>
</template>

<script>
import PortalAlumnoService from '@/services/PortalAlumnoService'
export default {
  name: 'PortalPerfil',
  data() { return { loading: true, perfil: null, error: null } },
  async mounted() { await this.load() },
  methods: {
    async load() {
      this.loading = true; this.error = null
      try { const { data } = await PortalAlumnoService.getPerfil(); this.perfil = data.data || data } catch (e) { this.error = 'Error al cargar el perfil.' }
      this.loading = false
    },
    statusBadge(s) { const m = { activo: 'badge-success', irregular: 'badge-warning', egresado: 'badge-info' }; return m[s] || 'badge-secondary' },
  }
}
</script>

<style scoped>
.portal-page { max-width: 800px; margin: 0 auto; }
.profile-card { display: flex; gap: 1.5rem; padding: 1.5rem; background: var(--glass-bg, rgba(255,255,255,.03)); border: 1px solid var(--glass-border, rgba(255,255,255,.06)); border-radius: 14px; }
.profile-photo-wrap { width: 100px; height: 100px; border-radius: 16px; overflow: hidden; flex-shrink: 0; }
.profile-photo { width: 100%; height: 100%; object-fit: cover; }
.profile-photo-empty { width: 100%; height: 100%; background: var(--color-primary); display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.5rem; }
.profile-info h2 { margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--color-text); }
.profile-info .badge { margin: .25rem 0 .75rem; }
.section-card { padding: 1.25rem; background: var(--glass-bg, rgba(255,255,255,.02)); border: 1px solid var(--glass-border, rgba(255,255,255,.06)); border-radius: 12px; }
.info-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: .75rem; }
.info-item { display: flex; flex-direction: column; gap: .125rem; }
.info-label { font-size: .6875rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: .04em; }
.info-value { font-size: .875rem; font-weight: 500; color: var(--color-text); }
@media (max-width: 600px) { .profile-card { flex-direction: column; align-items: center; text-align: center; } }
</style>
