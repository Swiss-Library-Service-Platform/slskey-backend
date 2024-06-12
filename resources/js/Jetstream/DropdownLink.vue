<script setup>
import { Link } from '@inertiajs/inertia-vue3';

defineProps({
    href: String,
    disabled: Boolean
});
</script>

<template>
    <div>
        <button v-if="!href" :disabled="disabled" type="submit"
            class="flex flex-row w-full px-4 py-4 gap-x-2 items-center text-md leading-5 text-gray-700 text-left hover:bg-gray-100 hover:cursor-pointer focus:outline-none focus:bg-gray-100 transition disabled:cursor-default disabled:text-gray-disabled  disabled:bg-white disabled:italic">
            <slot />
        </button>

        <a v-if="href" :href="href" class="flex flex-row items-center text-md px-4 py-2 leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition">
            <slot />
        </a>
<!--
        <Link v-else :href="href" class="block px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition">
            <slot />
        </Link>

-->

    </div>
</template>
