<template>
  <Teleport to="body">
    <div class="modal fade" ref="modalRef" tabindex="-1" :aria-labelledby="modalId + 'Label'" aria-hidden="true">
      <div class="modal-dialog modal-fullscreen-sm-down" :class="sizeClass">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" :id="modalId + 'Label'">{{ title }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <slot />
          </div>
          <div class="modal-footer" v-if="$slots.footer">
            <slot name="footer" />
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script>
import { Modal } from 'bootstrap'

export default {
  name: 'BaseModal',
  props: {
    title: { type: String, default: '' },
    modalId: { type: String, default: 'baseModal' },
    size: { type: String, default: '' },
  },
  data() {
    return {
      modalInstance: null,
    }
  },
  computed: {
    sizeClass() {
      const sizes = { lg: 'modal-lg', xl: 'modal-xl', sm: 'modal-sm' }
      return sizes[this.size] || ''
    },
  },
  mounted() {
    this.modalInstance = new Modal(this.$refs.modalRef)
  },
  methods: {
    show() { this.modalInstance.show() },
    hide() { this.modalInstance.hide() },
  },
}
</script>
