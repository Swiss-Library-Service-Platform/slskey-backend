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

        <div class="XXXh-screen h-full flex flex-col ">
            <div class="h-full flex flex-col">
                
                <!-- Page Heading -->
                <AppHeader :modePublic="modePublic" :breadCrumbs="breadCrumbs" :toggleNavbar="toggleNavbar" />

                <div class="flex justify-center h-full">
                    <div class="flex flex-row w-full"
                        :class="{ 'Xmax-w-screen-3xl': !modePublic, 'max-w-screen-xl': modePublic }">
                        <!-- Sidebar -->
                        <SideBar v-if="!modePublic" :showNavbar="showNavbar" :route="route" />

                        <!-- Page Contents -->
                        <main class="flex-1 Xoverflow-x-clip overflow-x-auto bg-gray-background pl-8 pr-8"> 

                            <slot />

                        </main>
                    </div>
                </div>
            </div>
        </div>
</template>

<style>

</style>
