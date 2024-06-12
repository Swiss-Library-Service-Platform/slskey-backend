<script setup>
import Modal from './Modal.vue';
import Icon from '../Shared/Icon.vue';

const emit = defineEmits(['close']);

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
    confirmText: {
        type: String,
        default: 'do this',
    },
});

const close = () => {
    emit('close');
};
</script>

<template>
    <Modal :show="show" :max-width="maxWidth" :closeable="closeable" @close="close">
        <div class="flex flex-row justify-between px-6 py-4 bg-color-header-bg text-right">
            <div class="text-lg font-bold text-white">
                <slot name="title" />
            </div>
            <button v-if="closeable" @click="close">
                <span class="flex items-center gap-1 font-bold overflow-ellipsis">
                    <Icon :inline="true" icon="x" class="h5 w-5 text-white" />
                </span>
            </button>
        </div>

        <div class="px-6 py-4">
            <div class="mt-2">
                <slot name="content" />
            </div>
        </div>
        <!--
                    <div class="flex flex-row justify-end px-6 py-4 bg-color-one text-right">
                        <slot name="footer" />
                    </div>
                -->
    </Modal>
</template>
