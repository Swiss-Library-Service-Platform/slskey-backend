<template>
    <div class="flex flex-wrap gap-2 items-end">
        <slot />
        <!--
        <button class="ml-2 self-end text-gray-500 focus:text-indigo-500 text-sm p-3 rounded-lg hover:bg-gray-300 hover:text-color-one" type="button"
            @click="$emit('reset')"></button>
        -->
        <button  @click.prevent="$emit('reset')" class="w-fit h-10 bg-color-header-bg text-white ml-1 items-center justify-center px-3 rounded-md">
            <MaterialIcon icon="filter_remove" :size="18" />
        </button>
    </div>
</template>

<script>
import DefaultButton from '@/Shared/Buttons/DefaultButton.vue';
import MaterialIcon from '@/Shared/MaterialIcon.vue';
export default {
    components: {
        DefaultButton,
        MaterialIcon
    },
    emits: ['reset'],
}
</script>
