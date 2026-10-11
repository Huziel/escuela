<template>
  <div>
    <div v-if="loading" class="text-center py-4">
      <div class="spinner-border spinner-border-sm text-primary"></div>
    </div>
    <div v-else class="table-responsive">
      <table class="table table-hover table-sm data-table mb-0">
        <thead class="table-light">
          <tr>
            <th v-for="col in columns" :key="col.key" :class="{ 'text-end': col.align === 'end' }">
              {{ col.label }}
            </th>
            <th v-if="$slots.actions" class="text-end">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in items" :key="item.id || index">
            <td v-for="col in columns" :key="col.key" :data-label="col.label">
              <slot :name="'cell-' + col.key" :item="item" :value="item[col.key]">
                {{ item[col.key] }}
              </slot>
            </td>
            <td v-if="$slots.actions" class="text-end" data-label="Acciones">
              <slot name="actions" :item="item" />
            </td>
          </tr>
          <tr v-if="items.length === 0">
            <td :colspan="columns.length + ($slots.actions ? 1 : 0)" class="text-center text-muted py-3">
              <slot name="empty">Sin registros</slot>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
export default {
  name: 'BaseTable',
  props: {
    columns: { type: Array, required: true },
    items: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
  },
}
</script>
