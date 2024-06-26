<template>
    <div class="flex justify-between pl-4">
        <h2 class="flex items-center leading-tight text-2xl justify-self-start text-color-header-text">
            <template v-for="(crumb, index) in breadCrumbs">
                <template v-if="crumb.link">
                    <Link class="text-gray-400 hover:text-white" :href="crumb.link" :key="crumb.link">
                    {{ crumb.name }}</Link>
                    <span class="text-gray-400 font-medium" :key="'span' + crumb.link">
                        <Icon icon="chevron-right" class="w-4 h-4" :key="'icon' + crumb.link"></Icon>
                    </span>
                </template>
                <template v-else>
                    {{ crumb.name }}
                </template>
                <Transition>
                    <p v-if="crumb.show" class="px-2 gap-2">
                        - {{ crumb.transitionedText }}
                    </p>
                </Transition>
            </template>


        </h2>
    </div>
</template>

<script>
import Icon from '@/Shared/Icon.vue';

export default {
    components: {
        Icon
    },
    props: {
        breadCrumbs: Array,
    },
    data() {
        return {
            show: this.breadCrumbs[this.breadCrumbs.length - 1].show
        }
    }
}


</script>

<style>
/* we will explain what these classes do next! */
.v-enter-active,
.v-leave-active {
    transition: opacity 0.5s ease;
}

.v-enter-from,
.v-leave-to {
    opacity: 0;
}
</style>