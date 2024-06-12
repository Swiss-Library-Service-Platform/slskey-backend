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
    disabled: Boolean
});
</script>

<template>
    <button :disabled="disabled || loading" :id=id :type="type" :as="type" :href="href" :class="[
            'inline-flex',
            'items-center',
            'px-3',
            'py-2',
            'bg-color-slsp',
            'h-fit',
            'rounded-md',
            'justify-center',
            'font-bold',
            'text-xl',
            'text-white',
            'focus:outline-none',
            'focus:ring',
            'focus:ring-color-slsp',
            'disabled:opacity-25',
            'w-full',
            {'hover:bg-opacity-80': !disabled, 'active:bg-opacity-70': !disabled}
        ]">
        <span v-if="icon && !loading" class="text-white mr-2 h-5 w-5">
            <Icon :icon="icon" />
        </span>
        <div v-if="loading" class="btn-spinner mr-2" />
        <slot />
    </button>
</template>
