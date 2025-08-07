<template>
  <div class="flex flex-col">
    <label class="block font-medium text-sm text-gray-700">
      <span>{{ label }}</span>
    </label>
    <input :id="id" class="
            px-3 py-2
            h-10 border border-gray-300 rounded
            transition delay-75 
            hover:border-gray-400 focus:border-gray-500
            focus:shadow-outline focus:ring-0 focus:border-1" :class="$attrs.inputclass" autocomplete="off" type="text"
      name="search" :value="modelValue" :placeholder="placeholder"
      @input="$emit('update:modelValue', $event.target.value)" />
  </div>
</template>

<script>
import { v4 as uuid } from 'uuid'

export default {
  props: {
    id: {
      type: String,
      default() {
        return `search-filter-${uuid()}`
      },
    },
    placeholder: String,
    label: String,
    modelValue: String,
  },
  emits: ['update:modelValue'],
}
</script>
