<template>
  <header class="mobile-header" aria-label="Barra superior móvil">
    <!-- Hamburger: abre drawer -->
    <button
      class="mobile-header-btn"
      @click="$emit('toggle-sidebar')"
      aria-label="Abrir menú lateral"
    >
      <i class="fas fa-bars"></i>
    </button>

    <!-- Título de página actual -->
    <span class="mobile-header-title">
      {{ title }}
    </span>

    <!-- Acciones derechas -->
    <div class="mobile-header-actions">
      <!-- Búsqueda global -->
      <button
        class="mobile-header-btn"
        @click="$emit('open-search')"
        aria-label="Buscar"
      >
        <i class="fas fa-magnifying-glass"></i>
      </button>

      <!-- Notificaciones -->
      <button
        class="mobile-header-btn"
        :class="{ 'has-badge': notifCount > 0 }"
        @click="$emit('open-notifications')"
        aria-label="Notificaciones"
      >
        <i class="fas fa-bell"></i>
        <span v-if="notifCount > 0" class="badge-dot"></span>
      </button>

      <!-- Avatar / Perfil -->
      <button
        class="mobile-header-btn"
        @click="$emit('open-user-menu')"
        aria-label="Menú de usuario"
      >
        <div class="avatar-sm">{{ userInitial }}</div>
      </button>
    </div>
  </header>
</template>

<script>
import { useAuthStore } from '@/store/auth'

export default {
  name: 'MobileHeader',
  emits: ['toggle-sidebar', 'open-search', 'open-notifications', 'open-user-menu'],
  props: {
    title: { type: String, default: 'SICE' },
    notifCount: { type: Number, default: 0 },
  },
  computed: {
    authStore() {
      return useAuthStore()
    },
    userInitial() {
      return this.authStore.nombreCompleto?.charAt(0) || 'U'
    },
  },
}
</script>

<style scoped>
/* Mobile header styles are in mobile-shell.css.
   This component uses those global classes. */

.avatar-sm {
  width: 28px;
  height: 28px;
  border-radius: var(--radius-sm);
  background: var(--color-primary);
  color: var(--color-text-inverse);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-bold);
}
</style>
