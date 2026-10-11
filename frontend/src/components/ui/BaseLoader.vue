<template>
  <div v-if="loading" class="text-center py-4">
    <div class="spinner-border" :class="sizeClass" role="status">
      <span class="visually-hidden">Cargando...</span>
    </div>
    <p v-if="text" class="mt-2 text-muted">{{ text }}</p>
  </div>
</template>

<script>
export default {
  name: 'BaseLoader',
  props: {
    loading: { type: Boolean, default: true },
    size: { type: String, default: 'sm' },
    text: { type: String, default: '' },
  },
  computed: {
    sizeClass() {
      return `spinner-border-${this.size}`
    },
  },
}
</script>
