<script setup>
import Icon from '../Icon.vue';
import { v4 as uuid } from 'uuid'
import { computed } from 'vue';

const props = defineProps({
    id: {
        type: String,
        default() {
            return `nav-link-${uuid()}`
        },
    },
    onClick: {
        type: Function,
        default: () => {},
    },
    icon: String,
    active: Boolean
});

const linkClasses = computed(() => {
    return props.active
        ? 'text-one cursor-pointer inline-flex bg-slsp-bg items-center px-8 py-2 my-1 font-semibold text-lg active:text-gray-800 active:bg-opacity-95 disabled:opacity-25 rounded '
        : 'text-one cursor-pointer inline-flex items-center px-8 py-2 my-1 font-medium text-lg hover:bg-slsp-bg disabled:opacity-25 rounded';
});
const iconClasses = computed(() => {
    return props.active
        ? 'mr-2 h-5 w-5'
        : 'mr-2 h-5 w-5'
});

</script>

<template>
    <button :id=id :class="linkClasses" @click="onClick">
    <span v-if="icon" :class="iconClasses">
        <Icon :icon="icon" />
    </span>
    <slot />
    </button>

</template>
