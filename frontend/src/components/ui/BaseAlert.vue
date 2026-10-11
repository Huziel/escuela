<template>
  <div v-if="visible" :class="alertClass" role="alert">
    <i v-if="icon" :class="icon" class="me-2"></i>
    <slot />
  </div>
</template>

<script>
export default {
  name: 'BaseAlert',
  props: {
    variant: { type: String, default: 'info' },
    icon: { type: String, default: '' },
    visible: { type: Boolean, default: true },
    dismissible: { type: Boolean, default: false },
  },
  computed: {
    alertClass() {
      const classes = ['alert', `alert-${this.variant}`]
      if (this.dismissible) classes.push('alert-dismissible', 'fade', 'show')
      return classes.join(' ')
    },
  },
}
</script>
