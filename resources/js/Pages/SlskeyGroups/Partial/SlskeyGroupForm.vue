<template>
    <form @submit.prevent="store">
        <div class="flex flex-col">
            <h3 class="text-2xl px-4 py-4 m-4 text-color-slsp bg-color-slsp-bg rounded-md">{{ $t('slskey_groups.general') }}
            </h3>
            <div class="grid grid-cols-2 px-8 pb-8 pt-4 gap-8">
                <!-- Name -->
                <text-input v-model="form.name" :error="form.errors.name" :label="`${$t('slskey_groups.name')} *`" />
                <!-- SLSKey Code -->
                <text-input v-model="form.slskey_code" :error="form.errors.slskey_code" :disabled="!isCreating"
                    :label="`${$t('slskey_groups.slskey_code')} *`" />
                <!-- Workflow -->
                <select-input :options="availableWorkflows" :error="form.errors.workflow"
                    :label="`${$t('slskey_groups.workflow')} *`" v-model="form.workflow">
                </select-input>
                <!-- Activation Mail -->
                <checkbox-input class="w-full" :error="form.errors.send_activation_mail" v-model="form.send_activation_mail"
                    :label="$t('slskey_groups.send_activation_mail')" />
                <!-- Alma IZ -->
                <text-input v-model="form.alma_iz" :error="form.errors.alma_iz"
                    :label="`${$t('slskey_groups.alma_iz')} *`" />
            </div>

            <!-- Webhook -->
            <template v-if="form.workflow">
                <div class="border-t border-b border-default-gray"></div>
                <h3 class="text-2xl px-4 py-4 m-4 text-color-slsp bg-color-slsp-bg rounded-md">
                    {{ form.workflow === 'Webhook' ? $t('slskey_groups.webhook_details') :
                        $t('slskey_groups.expiration_details') }}</h3>
                <div class="grid grid-cols-2 px-8 pb-8 pt-4 gap-8">
                    <!-- Webhook Activation -->
                    <template v-if="form.workflow === 'Webhook'">
                        <text-input v-model="form.webhook_secret" :error="form.errors.webhook_secret"
                            :label="`${$t('slskey_groups.webhook_secret')} *`" />
                            <!-- 
                        <select-input v-model="form.webhook_custom_verifier" :error="form.errors.webhook_custom_verifier"
                            :options="availableWebhookCustomVerifiers"
                            :helpText="$t('slskey_groups.webhook_custom_verifier_help')"
                            :label="$t('slskey_groups.webhook_custom_verifier')" />
                        <div></div>
                        -->
                    </template>
                    <!-- Manual Activation -->
                    <template v-else>
                        <number-input v-model="form.days_activation_duration" :error="form.errors.days_activation_duration"
                            :label="`${$t('slskey_groups.days_activation_duration')} *`" />
                        <number-input v-model="form.days_expiration_reminder" :error="form.errors.days_expiration_reminder"
                            :label="$t('slskey_groups.days_expiration_reminder')"
                            :placeholder="$t('slskey_groups.days_expiration_reminder_placeholder')" />
                    </template>
                </div>
            </template>
            <!-- Webhook Email Actviation -->
            <template v-if="form.workflow == 'Webhook'"
                class="border-t border-b border-default-gray">
                <div class="border-t border-b border-default-gray"></div>
                <h3 class="text-2xl px-4 py-4 m-4 text-color-slsp bg-color-slsp-bg rounded-md">
                    {{ $t('slskey_groups.webhook_activation_details') }}</h3>
                <div class="grid grid-cols-2 px-8 pb-8 pt-4 gap-8">

                    <!-- Custom Verifier -->
                    <checkbox-input v-model="form.webhook_custom_verifier"
                        :error="form.errors.webhook_custom_verifier_class" :label="$t('slskey_groups.webhook_custom_verifier')" />
                    <select-input v-if="form.webhook_custom_verifier" v-model="form.webhook_custom_verifier_class" :error="form.errors.webhook_custom_verifier_class"
                            :options="availableWebhookCustomVerifiers"
                            :helpText="$t('slskey_groups.webhook_custom_verifier_help')"
                            :label="$t('slskey_groups.webhook_custom_verifier_class')" />
                    <div v-if="!form.webhook_custom_verifier" />

                    <!-- Mail Activation -->
                    <checkbox-input class="w-full" v-model="form.webhook_mail_activation"
                        :error="form.errors.webhook_mail_activation" :label="$t('slskey_groups.webhook_mail_activation')" />
                    <div/>
                    <select-input v-if="form.webhook_mail_activation" v-model="form.webhook_mail_activation_domains"
                        :helpText="$t('slskey_groups.webhook_mail_activation_domains_help')"
                        :error="form.errors.webhook_mail_activation_domains"
                        :options="availableWebhookMailActivationDomains"
                        :label="$t('slskey_groups.webhook_mail_activation_domains')" />
                    <number-input v-if="form.webhook_mail_activation" v-model="form.webhook_mail_activation_days_send_before_expiry"
                        :error="form.errors.webhook_mail_activation_days_send_before_expiry"
                        :label="$t('slskey_groups.webhook_mail_activation_days_send_before_expiry')" />
                    <number-input v-if="form.webhook_mail_activation" v-model="form.webhook_mail_activation_days_token_validity"
                        :error="form.errors.webhook_mail_activation_days_token_validity"
                        :label="$t('slskey_groups.webhook_mail_activation_days_token_validity')" />
                     <number-input v-if="form.webhook_mail_activation" v-model="form.days_activation_duration"
                        :error="form.errors.days_activation_duration"
                        :label="$t('slskey_groups.days_activation_duration')" />
                </div>
            </template>
            <!-- Switch Groups -->
            <div class="border-t border-b border-default-gray"></div>
            <h3 class="text-2xl px-4 py-4 m-4 text-color-slsp bg-color-slsp-bg rounded-md">{{
                $t('slskey_groups.switch_groups') }}</h3>
            <div class="grid grid-cols-1 px-8 pb-8 pt-4 gap-8">
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
                                    <DefaultIconButton class="bg-color-blocked py-1" icon="x" :tooltip="$t('delete')"
                                        @click="removeGroup(switchGroup.id)" />
                                </td>
                            </tr>
                        </template>
                        <template v-else>
                            <tr>
                                <td class="pl-6 py-2 whitespace-nowrap italic">{{ $t('slskey_groups.no_switch_groups')
                                }}.</td>
                            </tr>
                        </template>

                    </tbody>
                </table>
                <div class="flex flex-row w-full items-center gap-x-8">
                    <SelectInput class="w-full" v-model="newSwitchGroup" :options="this.availableSwitchGroups.data"
                        :placeholder="$t('switch_groups.name')">
                    </SelectInput>
                    <DefaultIconButton @click="addGroup()" class="bg-color-active py-1 text-white shrink-0" icon="plus"
                        :disabled="!isNewSwitchGroupToAdd" :tooltip="$t('reporting.settings.add')" />
                </div>
            </div>
            <div class="border-t border-b border-default-gray"></div>
            <div class="flex">
                <div class="flex w-full flex-row justify-between gap-4 px-4 py-4">
                    <div class="flex flex-row gap-4">
                        <DefaultButton @click="cancel()" class="bg-color-one py-1 text-white w-fit"
                            :tooltip="$t('slskey_groups.cancel')">
                            {{ $t('slskey_groups.cancel') }}
                        </DefaultButton>
                        <DefaultButton v-if="!isCreating" @click="deleteGroup()" class="py-1 text-color-blocked w-fit"
                            icon="trash" :tooltip="$t('slskey_groups.delete')">
                            {{ $t('slskey_groups.delete') }}
                        </DefaultButton>
                    </div>
                    <DefaultButton @click="submit()" class="bg-color-slsp py-1 text-white w-fit" icon="save"
                        :tooltip="$t('slskey_groups.save')">
                        <span v-if="isCreating">{{ $t('slskey_groups.create_new') }}</span>
                        <span v-else>
                            {{ $t('slskey_groups.save') }}
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
import DefaultButton from '@/Shared/Buttons/DefaultButton.vue';
import DefaultIconButton from '@/Shared/Buttons/DefaultIconButton.vue';

export default {
    components: {
        TextInput,
        CheckboxInput,
        TextAreaInput,
        LoadingButton,
        CheckboxClassicInput,
        KeyIcon,
        NumberInput,
        DefaultIconButton,
        DefaultButton,
        TextInput,
        SelectInput,
        NumberInput,
        CheckboxInput,
    },
    props: {
        modelValue: {
            type: Object,
            required: true
        },
        availableSwitchGroups: Object,
        availableWorkflows: Object,
        availableWebhookCustomVerifiers: Object,
        availableWebhookMailActivationDomains: Object,
        isCreating: Boolean
    },
    data() {
        return {
            newSwitchGroup: '',
            form: this.modelValue,
        }
    },
    computed: {
        isNewSwitchGroupToAdd() {
            return this.newSwitchGroup && !this.form.switchGroups.map(group => group.id).includes(this.newSwitchGroup);
        }
    },
    emits: ['update:modelValue', 'submit'],
    watch: {
        'form.workflow': function (workflow) {
            if (workflow === 'Manual') {
                this.form.webhook_secret = null;
                this.form.webhook_custom_verifier = null;
                this.form.webhook_custom_verifier_class = null;
            } else {
                this.form.days_activation_duration = null;
                this.form.days_expiration_reminder = null;
            }
        },
        'form.webhook_mail_activation': function (webhook_mail_activation) {
            if (webhook_mail_activation) {
                this.form.webhook_custom_verifier = null;
                this.form.webhook_custom_verifier_class = null;
            }
        },
        'form.webhook_custom_verifier': function(webhook_custom_verifier) {
            if (webhook_custom_verifier) {
                this.form.webhook_mail_activation = 0;
                this.form.webhook_mail_activation_domains = null;
                this.form.webhook_mail_activation_days_send_before_expiry = null;
                this.form.webhook_mail_activation_days_token_validity = null;
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
        deleteGroup() {
            this.$emit('delete');
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