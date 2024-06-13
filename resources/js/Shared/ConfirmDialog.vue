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

            <div class="flex justify-between mt-6 gap-x-8">
                <div class=" w-28 flex items-center">
                    <button @click="cancel" class="
                        w-full
                        inline-flex
                        items-center
                        px-3
                        py-2
                        bg-color-header-bg
                        bg-opacity-60
                        rounded-md
                        font-bold 
                        text-sm
                        text-white
                        hover:bg-opacity-40
                        justify-center
                        ">
                        <Icon icon="x" class="text-white h-4 w-4 mr-2" />

                        {{ $t('confirm_dialog.cancel') }}
                    </button>
                </div>


                <div class="w-28 flex items-center">
                    <button @click="confirm" class="
                    
                                    inline-flex
                                    items-center
                                    px-3
                                    py-2
                                    rounded-md
                                    font-bold 
                                    text-sm
                                    bg-color-header-bg
                                    text-white
                                    w-full
                                    justify-center
                                    hover:bg-opacity-40
                                    ">
                        <Icon icon="check" class="text-white h-4 w-4 mr-2" />

                        {{ $t('confirm_dialog.confirm') }}
                    </button>
                </div>
            </div>
        </template>
    </DialogModal>
</template>
<script>
import DialogModal from '../Jetstream/DialogModal.vue';
import Icon from '@/Shared/Icon.vue';
import TextAreaInput from "@/Shared/Forms/TextAreaInput.vue";

export default {
    components: {
        Icon,
        DialogModal,
        TextAreaInput
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