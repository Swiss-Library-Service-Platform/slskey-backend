<template>
  <div class="flex flex-col gap-2" :class="$attrs.class">
    <label v-if="label" class="form-label mb-0" :for="id">{{ label }}</label>
    <vue-number-input :model-value="modelValue" :min="1" :max="max" @update:model-value="onChange" controls
      size="small"
      :placeholder="placeholder"></vue-number-input>
    <div v-if="error" class="form-error">{{ error }}</div>

  </div>
</template>

<script>
import { v4 as uuid } from 'uuid'

export default {
  inheritAttrs: false,
  props: {
    id: {
      type: String,
      default() {
        return `number-input-${uuid()}`
      },
    },
    error: String,
    label: String,
    modelValue: Number,
    max: {
      type: Number,
      default: 999
    },
    placeholder: String
  },
  emits: ['update:modelValue'],
  methods: {
    onChange(val) {
      this.$emit('update:modelValue', val);
    },
  },
}
</script>
