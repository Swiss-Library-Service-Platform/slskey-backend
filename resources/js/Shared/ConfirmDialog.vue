<template>
    <DialogModal :confirmText="this.confirmText" :closeable="false" :show="this.showConfirmModal">
        <template #title>
            {{ $t('confirm_dialog.title') }}
        </template>
        <template #content>
            <p>
                {{ $t('user_management.confirm_start') }} {{ this.confirmText }}?
            </p>
            <p v-if="this.confirmText2" class="mt-6">
                {{ this.confirmText2 }}
            </p>

            <TextAreaInput v-if="this.enterRemark" class="mt-6" v-model="remark"
                :label="$t('user_management.remark')" />
        </template>
        <template #footer>
            <div class="flex justify-between w-full">
                <div class="w-32 flex items-center">
                    <DefaultButton @click="confirm" icon="check" class="w-full text-md">
                        {{ $t('confirm_dialog.confirm') }}
                    </DefaultButton>
                </div>
                <div class="w-32 flex items-center">
                    <DefaultButton @click="cancel" icon="x" class="w-full !bg-white font-normal shadow-none text-black">
                        {{ $t('confirm_dialog.cancel') }}
                    </DefaultButton>
                </div>
            </div>
        </template>
    </DialogModal>
</template>
<script>
import DialogModal from './DialogModal.vue';
import Icon from '@/Shared/Icon.vue';
import TextAreaInput from "@/Shared/Forms/TextAreaInput.vue";
import DefaultButton from './Buttons/DefaultButton.vue';

export default {
    components: {
        Icon,
        DialogModal,
        TextAreaInput,
        DefaultButton
    },
    props: {
        showConfirmModal: Boolean,
        inputRemark: String,
        confirmText: String,
        confirmText2: String,
        enterRemark: {
            type: Boolean,
            default: true,
        }
    },
    data() {
        return {
            remark: this.inputRemark
        }
    },
    watch: {
        inputRemark() {
            this.remark = this.inputRemark;
        }
    },

    emits: ['confirmed', 'canceled'],
    methods: {
        cancel() {
            this.$emit('canceled');
        },
        confirm() {
            this.$emit('confirmed', this.remark);
        },
    },
};
</script>