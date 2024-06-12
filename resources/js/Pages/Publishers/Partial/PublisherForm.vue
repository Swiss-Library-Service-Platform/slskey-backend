<template>
    <form @submit.prevent="store">
        <div class="flex flex-col">
            <h3 class="text-2xl px-4 py-4 m-4 text-color-slsp bg-color-slsp-bg rounded-md">{{ $t('publishers.general') }}
            </h3>
            <div class="grid grid-cols-2 px-8 pb-8 gap-8">
                <!-- Name -->
                <text-input v-model="form.name" :error="form.errors.name" :label="`${$t('publishers.slskey_groups')} *`" />
                <!-- Login Url -->
                <text-input v-model="form.entity_id" :error="form.errors.entity_id"
                    :label="`${$t('publishers.entity_id')}`" />
                <!-- Protocol -->
                <select-input-value :label="$t('publishers.protocol')" :error="form.errors.protocol" v-model="form.protocol"
                    :options="availableProtocolOptions" />
                <!-- Status -->
                <select-input-value :label="$t('publishers.status')" :error="form.errors.status" v-model="form.status"
                    :options="availableStatusOptions" />
                <!-- Note -->
                <text-area-input v-model="form.internal_note" :error="form.errors.internal_note"
                    :label="`${$t('publishers.internal_note')}`" />

            </div>
            <div class="border-t border-b border-default-gray"></div>
            <h3 class="text-2xl px-4 py-4 m-4 text-color-slsp bg-color-slsp-bg rounded-md">{{ $t('publishers.switch_groups')
            }}</h3>
            <div class="grid grid-cols-1 px-8 pb-8 gap-8">

                <!-- Switch Groups -->
                <table class="table-auto min-w-full rounded-md">
                    <tbody class="">
                        <template v-if="form.switchGroups.length > 0">
                            <tr v-for="switchGroup in form.switchGroups" :key="'switchgroup' + switchGroup.id"
                                class="hover:bg-gray-100 focus-within:bg-gray-100">

                                <td class="pr-6 py-2 ">
                                    {{ switchGroup.name }}
                                </td>

                                <td class="pr-6 py-2 ">
                                    {{ switchGroup.switch_group_id }}
                                </td>

                                <td class="pl-6 py-2 text-right">
                                    <DefaultIconButton class="bg-color-blocked py-1" icon="x" :tooltip="$t('publishers.delete_switch_group')"
                                        @click="removeGroup(switchGroup.id)" />
                                </td>
                            </tr>
                        </template>
                        <template v-else>
                            <tr>
                                <td class="pl-6 py-2 whitespace-nowrap italic">{{ $t('publishers.no_switch_groups')
                                }}.</td>
                            </tr>
                        </template>

                    </tbody>
                </table>
                <div class="flex flex-row w-full items-center gap-x-8">
                    <SelectInput class="w-full" v-model="newSwitchGroup" :options="this.availableSwitchGroups.data"
                        :placeholder="$t('publishers.switch_groups')">
                    </SelectInput>
                    <DefaultIconButton @click="addGroup()" class="bg-color-active py-1 text-white shrink-0" icon="plus"
                        :disabled="!isNewSwitchGroupToAdd" :tooltip="$t('publishers.add_switch_group')" />
                </div>
            </div>
            <div class="border-t border-b border-default-gray"></div>
            <div class="flex">
                <div class="flex w-full flex-row justify-between gap-4 px-4 py-4">
                    <div class="flex flex-row gap-4">
                        <DefaultButton @click="cancel()" class="bg-color-one py-1 text-white w-fit"
                            :tooltip="$t('publishers.cancel')">
                            {{ $t('publishers.cancel') }}
                        </DefaultButton>
                        <DefaultButton v-if="!isCreating" @click="deletePublisher()" class="py-1 text-color-blocked w-fit"
                            icon="trash" :tooltip="$t('publishers.delete')">
                            {{ $t('publishers.delete') }}
                        </DefaultButton>
                    </div>
                    <DefaultButton @click="submit()" class="bg-color-slsp py-1 text-white w-fit" icon="save"
                        :tooltip="$t('publishers.save')">
                        <span v-if="isCreating">{{ $t('publishers.create_new') }}</span>
                        <span v-else>
                            {{ $t('publishers.save') }}
                        </span>
                    </DefaultButton>
                </div>
            </div>
        </div>
    </form>
</template>

<script>
import CheckboxInput from '@/Shared/Forms/CheckboxInput.vue'
import TextAreaInput from '@/Shared/Forms/TextAreaInput.vue'
import TextInput from '@/Shared/Forms/TextInput.vue'
import LoadingButton from '@/Shared/Buttons/LoadingButton.vue'
import CheckboxClassicInput from '@/Shared/Forms/CheckboxClassicInput.vue'
import { KeyIcon } from '@heroicons/vue/solid';
import NumberInput from '@/Shared/Forms/NumberInput.vue'
import SelectInput from '@/Shared/Forms/SelectInput.vue';
import SelectInputValue from '@/Shared/Forms/SelectInputValue.vue';
import DefaultButton from '@/Shared/Buttons/DefaultButton.vue';
import DefaultIconButton from '@/Shared/Buttons/DefaultIconButton.vue';
export default {
    components: {
        TextInput,
        CheckboxInput,
        LoadingButton,
        CheckboxClassicInput,
        KeyIcon,
        SelectInputValue,
        NumberInput,
        DefaultIconButton,
        DefaultButton,
        TextInput,
        SelectInput,
        NumberInput,
        CheckboxInput,
        TextAreaInput
    },
    props: {
        modelValue: {
            type: Object,
            required: true
        },
        availableSwitchGroups: Object,
        isCreating: Boolean,
        availableProtocolOptions: Object,
        availableStatusOptions: Object
    },
    data() {
        return {
            newSwitchGroup: '',
            form: this.modelValue,
        }
    },
    emits: ['update:modelValue', 'submit'],
    computed: {
        isNewSwitchGroupToAdd() {
            return this.newSwitchGroup && !this.form.switchGroups.map(group => group.id).includes(this.newSwitchGroup);
        }
    },
    watch: {
        'form.workflow': function (workflow) {
            if (workflow === 'Manual') {
                this.form.webhook_custom_verifier = '';
                this.form.webhook_secret = '';
            } else {
                this.form.days_activation_duration = null;
                this.form.days_expiration_reminder = null;
            }
        },
        'form.webhook_mail_activation': function (mail_activation) {
            if (!mail_activation) {
                this.form.days_activation_duration = null;
            }
        }
    },
    methods: {
        submit(store) {
            this.$emit('submit', store);
        },
        cancel() {
            this.$emit('cancel');
        },
        deletePublisher() {
            this.$emit('delete');
        },
        getWorkflowOptions() {
            return [
                { value: 'Manual', name: 'Manual' },
                { value: 'Webhook', name: 'Webhook' }
            ]
        },
        removeGroup(id) {
            this.form.switchGroups = this.form.switchGroups.filter(group => group.id !== id);
        },
        addGroup() {
            if (this.newSwitchGroup) {
                // Add Group to SLSKey Group
                // check if not already in the list
                if (!this.form.switchGroups.map(group => group.id).includes(this.newSwitchGroup)) {
                    this.form.switchGroups.push(this.availableSwitchGroups.data.find(group => group.id === this.newSwitchGroup));
                }
                this.newSwitchGroup = '';
            }
        },
    }
}
</script>

<style>
.legendInnerDiv {
    display: flex;
    align-items: center;
}
</style>