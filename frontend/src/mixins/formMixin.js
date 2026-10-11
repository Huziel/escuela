export const formMixin = {
  data() {
    return {
      saving: false,
      formErrors: {},
    }
  },
  methods: {
    setErrors(errors) {
      this.formErrors = errors || {}
    },
    clearErrors() {
      this.formErrors = {}
    },
    hasError(field) {
      return !!this.formErrors[field]
    },
    getError(field) {
      return this.formErrors[field] ? this.formErrors[field][0] : ''
    },
  },
}
