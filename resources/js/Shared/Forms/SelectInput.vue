<template>
  <div class="vue-select-container" :class="$attrs.class">
    <div class="flex flex-row items-center">
      <label v-if="label" class="form-label mt-1" :for="id">{{ label }}</label>
      <HelpIconWithPopup class="mt-1 mb-2" v-if="helpText">
        {{ helpText }}
      </HelpIconWithPopup>
    </div>
    <vue-select :id="id" ref="input" v-model="selected" v-bind="{ ...$attrs, class: null }" :options="options"
      :searchable="true" label="name" :class="{ error: error, ...$attrs.inputclass }" />
    <div v-if="error" class="form-error">{{ error }}</div>
  </div>
</template>

<script>
import { v4 as uuid } from 'uuid'
import VueSelect from 'vue-select';
import Icon from '@/Shared/Icon.vue';
import { createPopper } from '@popperjs/core';
import HelpIconWithPopup from '../HelpIconWithPopup.vue';

export default {
  components: {
    VueSelect,
    Icon,
    HelpIconWithPopup
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
        this.$emit('update:modelValue', selected.value);
        this.$emit('change', selected.value);
      } else {
        this.$emit('update:modelValue', null)
        this.$emit('change', null)
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
