<template>
  <div :class="$attrs.class">
    <SwitchGroup>
      <div class="">
        <div class="flex flex-row items-center">
          <SwitchLabel v-if="label" class="mr-4 form-label">{{ label }}</SwitchLabel>
          <HelpIconWithPopup class="mt-1 mb-2" v-if="helpText">
            {{ helpText }}
          </HelpIconWithPopup>
        </div>
        <Switch v-model="selected" name :id="id" v-bind="{ ...$attrs, class: null }" :class="{
          error: error,
          'bg-one-1': !selected,
          'bg-one': selected
        }"
          class="inline-flex h-[30px] w-[62px] shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75">
          <span class="sr-only">Use setting</span>
          <span aria-hidden="true" :class="!selected ? 'translate-x-0' : 'translate-x-8'"
            class="pointer-events-none inline-block h-[26px] w-[26px] transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out" />
        </Switch>
      </div>
    </SwitchGroup>
    <slot />
    <div v-if="error" class="form-error">{{ error }}</div>
  </div>
</template>

<script>
import { v4 as uuid } from 'uuid';
import { Switch, SwitchLabel, SwitchGroup } from '@headlessui/vue';
import HelpIconWithPopup from '../HelpIconWithPopup.vue';

export default {
  inheritAttrs: false,
  props: {
    id: {
      type: String,
      default() {
        return `select-input-${uuid()}`;
      },
    },
    error: String,
    label: String,
    modelValue: Number,
    helpText: String,
  },
  emits: ["update:modelValue"],
  data() {
    return {
      selected: this.modelValue === 1 ? true : false,
    };
  },
  watch: {
    selected(selected) {
      this.$emit("update:modelValue", selected ? 1 : 0);
    },
    modelValue(newValue) {
      this.selected = newValue === 1 ? true : false;
    },
  },
  components: { Switch, SwitchLabel, SwitchGroup, HelpIconWithPopup },
}
</script>
