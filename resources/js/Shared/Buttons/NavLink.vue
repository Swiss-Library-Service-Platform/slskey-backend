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
    type: {
        type: String,
        default: 'button',
    },
    href: {
        type: String
    },
    isExternal: {
        type: Boolean,
        default: false
    },
    icon: String,
    active: Boolean
});

const linkClasses = computed(() => {
    return props.active
        ? 'text-color-one inline-flex bg-color-slsp-bg items-center px-8 py-2 my-1 font-semibold text-lg text-white active:text-gray-800 active:bg-opacity-95 disabled:opacity-25 rounded '
        : 'text-color-one inline-flex items-center px-8 py-2 my-1 font-medium text-lg text-white hover:bg-color-slsp-bg disabled:opacity-25 rounded';
});
const iconClasses = computed(() => {
    return props.active
        ? 'mr-2 h-5 w-5'
        : 'mr-2 h-5 w-5'
});

</script>

<template>
    <Link v-if="!isExternal" :id=id :type="type" :as="type" :href="href" :class="linkClasses">
    <span v-if="icon" :class="iconClasses">
        <Icon :icon="icon" />
    </span>
    <slot />
    </Link>
    <a v-else :id=id :type="type" target="_blank" :class="linkClasses" :href="href">
        <span v-if="icon" :class="iconClasses">
            <Icon :icon="icon" />
        </span>
        <slot />
    </a>

</template>
