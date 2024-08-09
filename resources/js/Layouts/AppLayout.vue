<script setup>
import { ref } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import { Head, Link } from '@inertiajs/inertia-vue3';
import JetBanner from '@/Jetstream/Banner.vue';
import SideBar from './SideBar.vue'
import AppHeader from './AppHeader.vue'

defineProps({
    title: String,
    breadCrumbs: Array,
    modePublic: {
        type: Boolean,
        default: false,
    }
});

const showNavbar = ref(true);
const toggleNavbar = () => {
    showNavbar.value = !showNavbar.value;
};

</script>

<template>

    <Head :title="title" />
    <JetBanner />

    <!-- Page Heading -->
    <AppHeader :modePublic="modePublic" :breadCrumbs="breadCrumbs" :toggleNavbar="toggleNavbar" />

    <div class="flex flex-1" :class="{ 'bg-white': !modePublic, 'max-w-screen-xl m-auto': modePublic }">
        <!-- Sidebar -->
        <SideBar class="flex" v-if="!modePublic" :showNavbar="showNavbar" :route="route" />

        <!-- Page Contents -->
        <!-- <main class="flex-1 Xoverflow-x-clip overflow-x-auto bg-gray-background pl-8 pr-8"> -->
        <main class="flex-1 overflow-hidden pl-8 pr-8 bg-gray-background">

            <slot />

        </main>
    </div>
</template>

<style></style>
