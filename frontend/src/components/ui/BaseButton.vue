<template>
  <button
    :type="type"
    :class="buttonClass"
    :disabled="disabled || loading"
    @click="$emit('click')"
  >
    <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
    <i v-if="icon && !loading" :class="[icon, { 'me-1': $slots.default }]"></i>
    <slot />
  </button>
</template>

<script>
export default {
  name: 'BaseButton',
  props: {
    variant: { type: String, default: 'primary' },
    type: { type: String, default: 'button' },
    size: { type: String, default: '' },
    loading: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    icon: { type: String, default: '' },
  },
  emits: ['click'],
  computed: {
    buttonClass() {
      const classes = ['btn']
      classes.push(`btn-${this.variant}`)
      if (this.size) classes.push(`btn-${this.size}`)
      return classes.join(' ')
    },
  },
}
</script>
