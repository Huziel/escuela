<template>
  <nav v-if="totalPages > 1">
    <ul class="pagination pagination-sm justify-content-end mb-0">
      <li class="page-item" :class="{ disabled: currentPage <= 1 }">
        <button class="page-link" @click="goTo(currentPage - 1)">&laquo;</button>
      </li>
      <li
        v-for="p in pages"
        :key="p"
        class="page-item"
        :class="{ active: p === currentPage }"
      >
        <button class="page-link" @click="goTo(p)">{{ p }}</button>
      </li>
      <li class="page-item" :class="{ disabled: currentPage >= totalPages }">
        <button class="page-link" @click="goTo(currentPage + 1)">&raquo;</button>
      </li>
    </ul>
  </nav>
</template>

<script>
export default {
  name: 'BasePagination',
  props: {
    currentPage: { type: Number, default: 1 },
    totalPages: { type: Number, default: 1 },
  },
  emits: ['page-change'],
  computed: {
    pages() {
      const arr = []
      for (let i = 1; i <= this.totalPages; i++) arr.push(i)
      return arr
    },
  },
  methods: {
    goTo(page) {
      if (page >= 1 && page <= this.totalPages) {
        this.$emit('page-change', page)
      }
    },
  },
}
</script>
