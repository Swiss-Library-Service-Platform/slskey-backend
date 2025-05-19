

<script>
import { v4 as uuid } from 'uuid';
import ConfirmDialog from '../ConfirmDialog.vue';
import DropdownLink from '@/Shared/DropdownLink.vue';

export default {
    components: {
        ConfirmDialog,
        DropdownLink
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
        confirmText: String,
        confirmText2: String,
        disabled: Boolean,
        activation: Object,
        enterRemark: {
            type: Boolean,
            default: true,
        }
    },
    emits: ['confirmed'],
    methods: {
        showConfirmModal() {
            this.showModal = true;
        },
        confirm(remark) {
            this.showModal = false;
            this.$emit('confirmed', this.activation, remark);
        },
        cancel() {
            this.showModal = false;
        }

    },
}
</script>

<template>
    <ConfirmDialog :confirmText="this.confirmText" :confirmText2="this.confirmText2" :enterRemark="this.enterRemark"
        :inputRemark="this.activation?.remark" @confirmed="this.confirm" @canceled="this.cancel"
        :show="this.showModal"></ConfirmDialog>

    <JetDropdownLink as="button" @click.prevent="showConfirmModal" :disabled="disabled">
        <slot />
    </JetDropdownLink>
</template>
