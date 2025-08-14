<template>
  <div :class="$attrs.class">
    <div class="flex flex-row">
      <label v-if="label" class="form-label mt-1" :for="id">{{ label }}</label>
      <HelpIconWithPopup v-if="helpText">
        {{ helpText }}
      </HelpIconWithPopup>
    </div>

    <input :id="id" ref="input" v-bind="{ ...$attrs, class: null }" :disabled="disabled" :placeholder="placeholder"
      class="form-input w-full" :class="{ error: error }"
      :type="type" :value="modelValue" autocomplete="off" @keypress.enter="$emit('enter');"
      @input="$emit('update:modelValue', $event.target.value)" />
    <div v-if="error" class="form-error">{{ error }}</div>
  </div>
</template>

<script>
import { v4 as uuid } from 'uuid'
import { createPopper } from '@popperjs/core';
import { QuestionMarkCircleIcon } from '@heroicons/vue/solid';
import HelpIconWithPopup from '../HelpIconWithPopup.vue';

export default {
  components: {
    QuestionMarkCircleIcon,
    HelpIconWithPopup
  },
  data() {
    return {
      tooltipShow: false,
    }
  },
  inheritAttrs: false,
  props: {
    id: {
      type: String,
      default() {
        return `text-input-${uuid()}`
      },
    },
    type: {
      type: String,
      default: 'text',
    },
    error: String,
    label: String,
    modelValue: String,
    helpText: String,
    placeholder: String,
    disabled: Boolean,
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
    toggleTooltip: function () {
      if (this.tooltipShow) {
        this.tooltipShow = false;
      } else {
        this.tooltipShow = true;
        createPopper(this.$refs.btnRef, this.$refs.tooltipRef, {
          placement: "right"
        });
      }
    },
  },
}
</script>
