export const paginationMixin = {
  data() {
    return {
      page: 1,
      totalPages: 1,
      perPage: 10,
    }
  },
  computed: {
    pages() {
      const arr = []
      for (let i = 1; i <= this.totalPages; i++) arr.push(i)
      return arr
    },
  },
  methods: {
    setPage(page) {
      this.page = page
    },
    updatePagination(meta) {
      if (meta) {
        this.page = meta.current_page || 1
        this.totalPages = meta.last_page || 1
        this.perPage = meta.per_page || 10
      }
    },
    resetPagination() {
      this.page = 1
    },
  },
}
