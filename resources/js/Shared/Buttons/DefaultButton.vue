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
    loading: Boolean,
});
</script>

<template>

    <button :disabled="loading" :id=id :type="type" :as="type" :href="href" class="
            inline-flex
            items-center
            h-fit
            px-4
            rounded-md
            justify-center
            font-bold 
            text-md
            hover:bg-opacity-80
            focus:outline-none
            focus:ring 
            focus:ring-color-slsp
            active:bg-opacity-70
            disabled:opacity-25
            w-full
            ">
    <span v-if="icon && !loading" class=" mr-1 h-4 w-4">
        <Icon :icon="icon" />
    </span>
    <div v-if="loading" class="btn-spinner mr-2" />
    <slot />
</button>
</template>
