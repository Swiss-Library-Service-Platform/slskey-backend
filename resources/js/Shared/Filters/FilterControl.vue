<template>
    <div class="flex flex-wrap gap-2 items-end">
        <slot />
        <button  @click.prevent="$emit('reset')" class="w-fit h-10 bg-color-header-bg text-white ml-1 items-center justify-center px-3 rounded-sm">
            <MaterialIcon icon="filter_remove" :size="18" />
        </button>
    </div>
</template>

<script>
import MaterialIcon from '@/Shared/MaterialIcon.vue';
export default {
    components: {
        MaterialIcon
    },
    emits: ['reset'],
}
</script>
