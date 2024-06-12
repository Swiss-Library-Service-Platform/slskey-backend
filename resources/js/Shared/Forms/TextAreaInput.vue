<template>
  <div :class="$attrs.class">
    <label v-if="label" class="form-label mb-1" :for="id">{{ label }}</label>
    <label v-if="sublabel" class="form-label italic" :for="id">{{ sublabel }}</label>
    <textarea :id="id" ref="input" v-bind="{ ...$attrs, class: null }" :rows="rows"
      class="form-input w-full block shadow-sm border-gray-300 focus:border-color-one-1 focus:ring focus:ring-color-one-1 focus:ring-opacity-50"
      :class="{ error: error }" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)" />
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
        return `text-input-${uuid()}`
      },
    },
    rows: {
      type: Number,
      default() {
        return 2
      }
    },
    error: String,
    label: String,
    sublabel: String,
    modelValue: String,
  },
  emits: ['update:modelValue'],
  methods: {
    focus() {
      this.$refs.input.focus()
    },
    select() {
      this.$refs.input.select()
    },
    setSelectionRange(start, end) {
      this.$refs.input.setSelectionRange(start, end)
    },
  },
}
</script>
