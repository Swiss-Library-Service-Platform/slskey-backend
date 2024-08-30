

<script>
import Icon from '@/Shared/Icon.vue';
import { v4 as uuid } from 'uuid';
import ConfirmDialog from '../ConfirmDialog.vue';
export default {
    components: {
        ConfirmDialog,
        Icon
    },
    data() {
        return {
            showModal: false
        }
    },
    props: {
        class: String,
        id: {
            type: String,
            default() {
                return `primary-button-${uuid()}`
            },
        },
        type: {
            type: String,
            default: 'button',
        },
        href: {
            type: String
        },
        icon: String,
        disabled: Boolean,
        loading: Boolean,
        tooltip: String,
        confirmText: String
    },
    emits: ['confirmed'],
    methods: {
        showConfirmModal() {
            this.showModal = true;
        },
        confirm() {
            this.showModal = false;
            this.$emit('confirmed');
        },
        cancel() {
            this.showModal = false;
        }
        
    }
}
</script>

<template>
    <ConfirmDialog :enterRemark="false" :confirmText="this.confirmText" @confirmed="this.confirm" @canceled="this.cancel" :show="this.showModal"></ConfirmDialog>

    <button :class="class" @click.prevent="showConfirmModal" :disabled="disabled || loading" :id=id :type="type" :as="type" :href="href" class="
        inline-flex
        items-center
        w-8
        h-8
        rounded-lg
        justify-center
        font-bold 
        text-md
        bg-white
        hover:bg-opacity-80
        focus:outline-none
        focus:ring 
        focus:ring-color-slsp
        active:bg-opacity-70
        disabled:opacity-25
        " :title="tooltip">
        <span v-if="icon && !loading" class="h-4 w-4">
            <Icon :icon="icon" />
        </span>
        <div v-if="loading" class="btn-spinner" />
    </button>
</template>
