<script setup>
import Icon from '../Icon.vue';
import { v4 as uuid } from 'uuid'
defineProps({
    id: {
      type: String,
      default() {
        return `primary-button-${uuid()}`
      },
    },
    type: {
        type: String,
        default: 'button',
    },
    href: {
        type: String
    },
    icon: String,
    disabled: Boolean,
    loading: Boolean,
    tooltip: String,
});
</script>

<template>

    <button :disabled="disabled || loading" :id=id :type="type" :as="type" :href="href" :title="tooltip" class="
        inline-flex
        items-center
        w-8
        h-8
        rounded-md
        justify-center
        font-bold 
        text-md
        text-white
        cursor-pointer
        hover:bg-opacity-80
        focus:outline-none
        focus:ring 
        focus:ring-slsp
        active:bg-opacity-70
        disabled:opacity-25
        border
            ">
        <span v-if="icon && !loading" class="Xtext-white h-4 w-4">
        <Icon :icon="icon" />
    </span>
    <div v-if="loading" class="btn-spinner-black h-4 w-4" />
    <slot />
</button>
</template>
