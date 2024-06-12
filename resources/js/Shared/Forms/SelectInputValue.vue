<template>
  <div class="vue-select-container" :class="$attrs.class">
    <label v-if="label" class="form-label mt-1" :for="id">{{ label }}</label>
    <vue-select 
      :id="id" ref="input"
      v-model="selected" 
      v-bind="{ ...$attrs, class: null }"
      :options="options"  
      :searchable="true"
      label="name"
      :class="{ error: error, ...$attrs.inputclass }"/>

    <div v-if="error" class="form-error">{{ error }}</div>
  </div>
</template>

<script>
import { v4 as uuid } from 'uuid'
import VueSelect from 'vue-select';

export default {
  components: {
    VueSelect
  },
  inheritAttrs: false,
  props: {
    id: {
      type: String,
      default() {
        return `select-input-${uuid()}`
      },
    },
    options: {
        type: Object,
        required: true
    },
    error: String,
    label: String,
    modelValue: [String, Number, Boolean],
  },
  emits: ['update:modelValue'],
  data() {
    return {
      selected: this.options.find(option => option.value === this.modelValue) || null,
    }
  },
  watch: {
    selected(selected) {
      this.$emit('update:modelValue', selected ? selected.value : null)
    },
  },
  methods: {
    focus() {
      this.$refs.input.focus()
    },
    select() {
      this.$refs.input.select()
    },
  },
}
</script>
