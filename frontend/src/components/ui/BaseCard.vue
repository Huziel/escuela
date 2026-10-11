<template>
  <div class="card" :class="{ 'h-100': fullHeight }">
    <div v-if="title || $slots.header" class="card-header">
      <slot name="header">
        <i v-if="headerIcon" :class="headerIcon" class="me-2"></i>
        {{ title }}
      </slot>
    </div>
    <div class="card-body">
      <slot />
    </div>
    <div v-if="$slots.footer" class="card-footer">
      <slot name="footer" />
    </div>
  </div>
</template>

<script>
export default {
  name: 'BaseCard',
  props: {
    title: { type: String, default: '' },
    headerIcon: { type: String, default: '' },
    fullHeight: { type: Boolean, default: false },
  },
}
</script>
