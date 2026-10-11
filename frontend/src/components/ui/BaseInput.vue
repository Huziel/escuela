<template>
  <div :class="{ 'mb-3': !noMargin }">
    <label v-if="label" :for="id" class="form-label">
      {{ label }} <span v-if="required" class="text-danger">*</span>
    </label>
    <div v-if="hasIcon" class="input-group">
      <span class="input-group-text" v-if="iconLeft"><i :class="iconLeft"></i></span>
      <component
        :is="isTextarea ? 'textarea' : 'input'"
        :id="id"
        :value="modelValue"
        :type="type"
        :class="inputClass"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :rows="rows"
        v-bind="$attrs"
        @input="$emit('update:modelValue', $event.target.value)"
        @blur="$emit('blur', $event)"
      />
      <span class="input-group-text" v-if="iconRight"><i :class="iconRight"></i></span>
    </div>
    <component
      v-else
      :is="isTextarea ? 'textarea' : 'input'"
      :id="id"
      :value="modelValue"
      :type="type"
      :class="inputClass"
      :placeholder="placeholder"
      :disabled="disabled"
      :readonly="readonly"
      :rows="rows"
      v-bind="$attrs"
      @input="$emit('update:modelValue', $event.target.value)"
      @blur="$emit('blur', $event)"
    />
    <div v-if="error" class="invalid-feedback d-block">{{ error }}</div>
  </div>
</template>

<script>
export default {
  name: 'BaseInput',
  props: {
    modelValue: { type: [String, Number], default: '' },
    label: { type: String, default: '' },
    id: { type: String, default: '' },
    type: { type: String, default: 'text' },
    placeholder: { type: String, default: '' },
    error: { type: String, default: '' },
    required: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    readonly: { type: Boolean, default: false },
    rows: { type: Number, default: 3 },
    iconLeft: { type: String, default: '' },
    iconRight: { type: String, default: '' },
    noMargin: { type: Boolean, default: false },
    isTextarea: { type: Boolean, default: false },
  },
  emits: ['update:modelValue', 'blur'],
  computed: {
    hasIcon() {
      return this.iconLeft || this.iconRight
    },
    inputClass() {
      const classes = ['form-control']
      if (this.error) classes.push('is-invalid')
      if (this.size) classes.push(`form-control-${this.size}`)
      return classes.join(' ')
    },
  },
}
</script>
