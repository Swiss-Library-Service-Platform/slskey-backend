<template>
    <div class="flex flex-wrap gap-2 items-end w-full">
        <slot />
        <button @click.prevent="$emit('reset')"
            class="w-fit h-10 bg-color-header-bg text-white ml-1 items-center justify-center px-3 rounded-sm">
            <MaterialIcon icon="filter_remove" :size="18" />
        </button>
        <DefaultButton v-if="onDownload" icon="documentDownload" :loading="export_loading" @click="handleDownload"
            class="w-fit py-2 ml-auto mt-4">
            {{ $t('user_management.export') }}
        </DefaultButton>
    </div>
</template>

<script>
import MaterialIcon from '@/Shared/MaterialIcon.vue';
import DefaultButton from '../Buttons/DefaultButton.vue';

export default {
    components: {
        MaterialIcon,
        DefaultButton,
    },
    props: {
        onDownload: {
            type: Function,
            default: null
        }
    },
    data: () => ({
        export_loading: false,
    }),
    methods: {
        async handleDownload() {
            if (this.export_loading) return;
            this.export_loading = true;

            try {
                await this.onDownload();
            } finally {
                this.export_loading = false;
            }
        }
    },
    emits: ['reset']
}
</script>