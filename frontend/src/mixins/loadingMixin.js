export const loadingMixin = {
  data() {
    return {
      loading: false,
    }
  },
  methods: {
    setLoading(val) {
      this.loading = val
    },
    async withLoading(fn) {
      this.loading = true
      try {
        return await fn()
      } finally {
        this.loading = false
      }
    },
  },
}
