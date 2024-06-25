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
    <div>

        <Head :title="title" />
        <JetBanner />

        <div class="XXXh-screen flex flex-col ">
            <div class="h-full flex flex-col">
                
                <!-- Page Heading -->
                <AppHeader :modePublic="modePublic" :breadCrumbs="breadCrumbs" :toggleNavbar="toggleNavbar" />

                <div class="flex justify-center h-full">
                    <div class="flex flex-row w-full pl-4 pr-16"
                        :class="{ 'max-w-screen-3xl': !modePublic, 'max-w-screen-xl': modePublic }">
                        <!-- Sidebar -->
                        <SideBar v-if="!modePublic" :showNavbar="showNavbar" :route="route" />

                        <!-- Page Contents -->
                        <main class="flex-1 Xoverflow-x-clip overflow-x-auto"> 

                            <slot />

                        </main>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
/*
.app-background {
    
    background-image: linear-gradient(to top, #e6e9f0 0%, #eef1f5 100%);

}

.app-background:before {
    content: '';
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: -1;
    old_background: url(/images/watermark.png) 0 0 repeat;
    background-position: center;
    opacity: 0.5;
}
*/

</style>
