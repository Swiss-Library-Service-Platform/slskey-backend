<template>
  <div :class="$attrs.class">
    <div class="flex flex-row">
      <label v-if="label" class="form-label mt-1" :for="id">{{ label }}</label>
      <button v-if="helpText" v-on:mouseenter="toggleTooltip()" v-on:mouseleave="toggleTooltip()"
        class="text-color-one italic cursor-default mt-1 mb-2 ml-2" tabindex="-1" type="button" ref="btnRef">
        <span class="flex items-center">
          <QuestionMarkCircleIcon class="h-5 w-5 mr-2" aria-hidden="true" />
        </span>
      </button>
    </div>

    <input :id="id" ref="input" v-bind="{ ...$attrs, class: null }"
      :disabled="disabled"
      :placeholder="placeholder"
      class="form-input w-full  focus:ring-0 shadow-sm border-gray-300 focus:border-gray-500 "
      :class="{ error: error }" :type="type" :value="modelValue"
      autocomplete="off"
      @keypress.enter="$emit('enter');"
      @input="$emit('update:modelValue', $event.target.value)" />
    <div v-if="error" class="form-error">{{ error }}</div>
  </div>

  <div ref="tooltipRef" v-bind:class="{ 'hidden': !tooltipShow, 'block': tooltipShow }"
    class="bg-white border-color-one border-2 ml-3 block z-50 font-normal leading-normal text-sm max-w-xs text-left no-underline break-words rounded-lg">
    <div class="text-black p-3">
      {{ helpText }}
      <br>
    </div>
  </div>
</template>

<script>
import { v4 as uuid } from 'uuid'
import { createPopper } from '@popperjs/core';
import { QuestionMarkCircleIcon } from '@heroicons/vue/solid';

export default {
  components: {
    QuestionMarkCircleIcon
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
