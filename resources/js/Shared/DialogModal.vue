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
        <div class="flex flex-row justify-between p-4 bg-slsp-bg shadow">
            <div class=" text-slsp font-semibold text-lg flex flex-row items-center">
                <Icon :inline="true" icon="exclamation-circle" class="mr-2 h-5 min-w-5" />
                <slot name="title" />
            </div>
            <button v-if="closeable" @click="close">
                <span class="flex items-center gap-1 font-bold overflow-ellipsis">
                    <Icon :inline="true" icon="x" class="h-5 min-w-5 text-white" />
                </span>
            </button>
        </div>

        <div class="p-6">
            <slot name="content" />
        </div>
        
        <div class="p-4 flex flex-row border-t">
            <slot name="footer" />
        </div>
                
    </Modal>
</template>
