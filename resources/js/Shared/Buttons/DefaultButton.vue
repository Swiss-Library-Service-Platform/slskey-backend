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
    class: {
        type: [String, Array, Object],
        default: ''
    }
});
</script>

<template>

    <button :disabled="loading" :id=id :type="type" :as="type" :href="href" :class="[
    // Internal static classes
        'inline-flex items-center shadow text-color-slsp bg-color-slsp-bg border-color-slsp border h-fit px-4 py-2 rounded-sm justify-center font-bold text-md focus:outline-none focus:ring focus:ring-color-slsp-bg active:bg-opacity-70 disabled:opacity-25',
        // Internal dynamic classes
        {
            'hover:bg-opacity-70': !loading,
            'opacity-70': loading,
        },
        // Class from parent component
        this.class
    ]">
        <span v-if="icon && !loading" class="mr-1 h-4 w-4">
            <Icon :icon="icon" />
        </span>
        <div v-if="loading" class="btn-spinner-black mr-2" />
        <slot />
    </button>
</template>
