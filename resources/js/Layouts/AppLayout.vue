<script>
import { Head, Link } from '@inertiajs/inertia-vue3';
import SideBar from './SideBar.vue';
import AppHeader from './AppHeader.vue';
import Notifications from '../Shared/Notifications.vue';

export default {
    components: {
        Head,
        Link,
        SideBar,
        AppHeader,
        Notifications
    },
    props: {
        title: String,
        breadCrumbs: Array,
        modePublic: {
            type: Boolean,
            default: false,
        }
    },

}
</script>

<template>
    <Head :title="title" />

    <!-- Page Heading -->
    <AppHeader :modePublic="modePublic" :breadCrumbs="breadCrumbs" />

    <!-- Flash Notifications-->
    <Notifications />

    <div class="flex flex-1" :class="{ '': !modePublic, 'w-[1280px] m-auto': modePublic }">
        <!-- Sidebar -->
        <SideBar class="flex" v-if="!modePublic" :route="route" />

        <!-- Page Contents -->
        <!-- <main class="flex-1 Xoverflow-x-clip overflow-x-auto bg-gray-background pl-8 pr-8"> -->
        <!-- <main class="flex-1 overflow-hidden pl-8 pr-8 bg-gray-background"> -->
        <main class="flex-1 overflow-x-hidden pl-8 pr-8 bg-gray-background max-w-screen-3xl">     
        <slot />
        </main>
    </div>
</template>