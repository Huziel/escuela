<template>
  <div class="search-select" ref="selectRef">
    <div class="ss-trigger" @click="open = !open">
      <span v-if="selectedLabel" class="ss-selected">{{ selectedLabel }}</span>
      <span v-else class="ss-placeholder">{{ placeholder }}</span>
      <i class="fas fa-chevron-down ss-arrow" :class="{ open }"></i>
    </div>
    <div class="ss-dropdown" v-if="open">
      <div class="ss-search">
        <i class="fas fa-search"></i>
        <input
          ref="searchInput"
          v-model="query"
          type="text"
          placeholder="Buscar..."
          @input="onSearch"
        />
      </div>
      <div class="ss-list">
        <div
          v-for="option in filteredOptions"
          :key="option.value"
          class="ss-option"
          :class="{ active: modelValue === option.value }"
          @click="select(option)"
        >
          <span class="ss-option-label">{{ option.label }}</span>
          <span v-if="option.sub" class="ss-option-sub">{{ option.sub }}</span>
        </div>
        <div v-if="filteredOptions.length === 0" class="ss-empty">
          Sin resultados
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SearchSelect',
  props: {
    modelValue: { type: [String, Number], default: '' },
    options: { type: Array, default: () => [] },
    placeholder: { type: String, default: 'Seleccionar...' },
  },
  emits: ['update:modelValue'],
  data() {
    return { open: false, query: '' }
  },
  computed: {
    selectedLabel() {
      const sel = this.options.find(o => o.value === this.modelValue)
      return sel ? sel.label : ''
    },
    filteredOptions() {
      if (!this.query) return this.options
      const q = this.query.toLowerCase()
      return this.options.filter(o =>
        o.label.toLowerCase().includes(q) ||
        (o.sub && o.sub.toLowerCase().includes(q))
      )
    },
  },
  methods: {
    select(option) {
      this.$emit('update:modelValue', option.value)
      this.open = false
      this.query = ''
    },
    onSearch() {},
    handleClickOutside(e) {
      if (this.$refs.selectRef && !this.$refs.selectRef.contains(e.target)) {
        this.open = false
      }
    },
  },
  mounted() {
    document.addEventListener('click', this.handleClickOutside)
  },
  beforeUnmount() {
    document.removeEventListener('click', this.handleClickOutside)
  },
  watch: {
    open(val) {
      if (val) {
        this.$nextTick(() => { this.$refs.searchInput?.focus() })
      } else {
        this.query = ''
      }
    },
  },
}
</script>

<style scoped>
.search-select { position: relative; }
.ss-trigger {
  display: flex; align-items: center; justify-content: space-between;
  padding: .5625rem .75rem; border: 1.5px solid var(--color-border);
  border-radius: 10px; cursor: pointer; background: var(--color-bg);
  font-size: .875rem; min-height: 40px; transition: border-color .2s;
}
.ss-trigger:hover { border-color: var(--color-text-muted); }
.ss-placeholder { color: var(--color-text-muted); }
.ss-selected { color: var(--color-text); font-weight: 500; }
.ss-arrow { color: var(--color-text-muted); font-size: .75rem; transition: transform .2s; }
.ss-arrow.open { transform: rotate(180deg); }
.ss-dropdown {
  position: absolute; top: calc(100% + 4px); left: 0;
  min-width: 100%;
  background: var(--glass-bg-elevated);
  backdrop-filter: var(--glass-blur-lg);
  -webkit-backdrop-filter: var(--glass-blur-lg);
  border: 1px solid var(--glass-border-highlight);
  border-radius: var(--radius-md);
  box-shadow: var(--glass-highlight), var(--shadow-lg);
  z-index: 1060;
  overflow: hidden;
  max-height: 300px;
  display: flex;
  flex-direction: column;
  animation: scaleIn 0.15s ease;
  transform-origin: top left;
}
.ss-search {
  display: flex; align-items: center; gap: .5rem;
  padding: .5rem .75rem; border-bottom: 1px solid var(--color-border-light);
  position: sticky; top: 0; background: var(--color-bg);
}
.ss-search i { color: var(--color-text-muted); font-size: .8125rem; }
.ss-search input {
  flex: 1; border: none; outline: none; font-size: .8125rem; color: var(--color-text);
  background: none;
}
.ss-list { overflow-y: auto; flex: 1; }
.ss-option {
  display: flex; flex-direction: column; padding: .5rem .75rem; cursor: pointer;
  transition: background .1s; border-bottom: 1px solid var(--color-border-light);
  white-space: nowrap;
}
.ss-option:last-child { border-bottom: none; }
.ss-option:hover { background: var(--color-bg-tertiary); }
.ss-option.active { background: var(--color-primary-light); }
.ss-option-label { font-size: .8125rem; font-weight: 500; color: var(--color-text); }
.ss-option-sub { font-size: .6875rem; color: var(--color-text-muted); margin-top: 1px; }
.ss-empty { padding: 1rem; text-align: center; color: var(--color-text-muted); font-size: .8125rem; }
</style>
