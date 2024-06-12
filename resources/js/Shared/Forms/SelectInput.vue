<template>
  <div class="vue-select-container" :class="$attrs.class">
    <div class="flex flex-row">
      <label v-if="label" class="form-label mt-1" :for="id">{{ label }}</label>
      <button v-if="helpText" v-on:mouseenter="toggleTooltip()" v-on:mouseleave="toggleTooltip()"
        class="text-color-one italic cursor-default mt-1 mb-2 ml-2" tabindex="-1" type="button" ref="btnRef">
        <span class="flex items-center">
          <Icon class="h-5 w-5" icon="question-mark" />
        </span>
      </button>
    </div>
    <vue-select :id="id" ref="input" v-model="selected" v-bind="{ ...$attrs, class: null }" :options="options"
      :searchable="true" label="name" :class="{ error: error, ...$attrs.inputclass }" />
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
import VueSelect from 'vue-select';
import Icon from '@/Shared/Icon.vue';
import { createPopper } from '@popperjs/core';

export default {
  components: {
    VueSelect,
    Icon
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
    helpText: String,
    modelValue: [String, Number, Boolean],
  },
  emits: ['update:modelValue'],
  data() {
    return {
      selected: this.modelValue,
      tooltipShow: false,
    }
  },
  watch: {
    selected(selected) {
      if (selected && selected.value) {
        this.$emit('update:modelValue', selected.value)
      } else {
        this.$emit('update:modelValue', null)
      }
    },
  },
  methods: {
    focus() {
      this.$refs.input.focus()
    },
    select() {
      this.$refs.input.select()
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
