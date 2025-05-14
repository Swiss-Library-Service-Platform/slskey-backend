<template>
    <form @submit.prevent="store">
        <div class="flex flex-col">
            <h3 class="text-lg px-4 py-2 m-4 text-color-slsp bg-color-slsp-bg rounded-sm">{{
                $t('slskey_groups.general') }}
            </h3>
            <div class="grid grid-cols-2 px-8 pb-4  gap-4">
                <!-- Name -->
                <text-input v-model="form.name" :error="form.errors.name" :label="`${$t('slskey_groups.name')} *`" />
                <!-- SLSKey Code -->
                <text-input v-model="form.slskey_code" :error="form.errors.slskey_code" :disabled="!isCreating"
                    :label="`${$t('slskey_groups.slskey_code')} *`" />
                <!-- Alma IZ -->
                <text-input v-model="form.alma_iz" :error="form.errors.alma_iz"
                    :label="`${$t('slskey_groups.alma_iz')} *`" />
                <!-- Mail Sender -->
                <text-input v-model="form.mail_sender_address" :error="form.errors.mail_sender_address"
                    :label="$t('slskey_groups.mail_sender_address')"
                    :helpText="$t('slskey_groups.mail_sender_address_help')" />

                
            </div>

            <!-- Workflow -->
            <div class="border-t border-b border-default-gray"></div>
            <h3 class="text-lg px-4 py-2 m-4 text-color-slsp bg-color-slsp-bg rounded-sm">{{
                $t('slskey_groups.workflow') }}</h3>
            <div class="grid grid-cols-2 px-8 pb-4  gap-4">
                <!-- Workflow -->
                <select-input :options="availableWorkflows" :error="form.errors.workflow"
                    :label="`${$t('slskey_groups.workflow')} *`" v-model="form.workflow">
                </select-input>
            </div>

            <!-- Manual Activation Details -->
            <div class="border-t border-b border-default-gray"></div>
            <h3 class="text-lg px-4 py-2 m-4 text-color-slsp bg-color-slsp-bg rounded-sm">
                {{ $t('slskey_groups.activation_details') }}</h3>
            <div class="grid grid-cols-2 px-8 pb-4  gap-4">
                <!-- Switch for Manual Activation -->
                <checkbox-input v-model="form.send_activation_mail" :error="form.errors.send_activation_mail"
                :helpText="$t('slskey_groups.send_activation_mail_help')"
                    :label="$t('slskey_groups.send_activation_mail')" />
                <div />
                <!-- Switch for Show Member Educational Institution -->
                <checkbox-input v-model="form.show_member_educational_institution"
                    :error="form.errors.show_member_educational_institution"
                    :helpText="$t('slskey_groups.show_member_educational_institution_help')"
                    :label="$t('slskey_groups.show_member_educational_institution')" />
                <div />
            </div>

            <!-- Webhook -->
            <template v-if="form.workflow">
                <div class="border-t border-b border-default-gray"></div>
                <h3 class="text-lg px-4 py-2 m-4 text-color-slsp bg-color-slsp-bg rounded-sm">
                    {{ form.workflow === 'Webhook' ? $t('slskey_groups.webhook_details') :
                        $t('slskey_groups.expiration_details') }}</h3>
                <div class="grid grid-cols-2 px-8 pb-4  gap-4">
                    <!-- Webhook Activation -->
                    <template v-if="form.workflow === 'Webhook'">
                        <!-- switch for webhook non persistent -->
                        <checkbox-input v-model="form.webhook_persistent" :error="form.errors.webhook_persistent"
                            :label="$t('slskey_groups.webhook_persistent')"
                            :helpText="$t('slskey_groups.webhook_persistent_help')" />
                        <text-input v-model="form.webhook_secret" :error="form.errors.webhook_secret"
                            :label="`${$t('slskey_groups.webhook_secret')} *`" />
                        <div class="col-span-2 text-sm text-gray-500 italic">
                            Set the following as webhook URL in Alma: <br>
                            <span v-if="form.webhook_persistent" class="underline">
                                {{ $page.props.appUrl }}/api/v1/webhooks/{{ form.slskey_code }}
                            </span>
                            <span v-if="!form.webhook_persistent" class="underline">
                                {{ $page.props.appUrl }}/api/v1/webhooks-proxy/{{ form.slskey_code }}
                            </span>
                        </div>
                    </template>
                    <!-- Expiration: Manual Activation -->
                    <template v-else>
                        <number-input v-model="form.days_activation_duration"
                            :error="form.errors.days_activation_duration"
                            :label="$t('slskey_groups.days_activation_duration')"
                            :placeholder="$t('slskey_groups.days_activation_duration_placeholder')" />
                        <number-input v-model="form.days_expiration_reminder"
                            :error="form.errors.days_expiration_reminder"
                            :label="$t('slskey_groups.days_expiration_reminder')"
                            :placeholder="$t('slskey_groups.days_expiration_reminder_placeholder')" />
                    </template>
                </div>
            </template>
            <!-- Webhook Custom Verification -->
            <template v-if="form.workflow == 'Webhook' && form.webhook_persistent"
                class="border-t border-b border-default-gray">
                <div class="border-t border-b border-default-gray"></div>
                <h3 class="text-lg px-4 py-2 m-4 text-color-slsp bg-color-slsp-bg rounded-sm">
                    {{ $t('slskey_groups.webhook_activation_details') }}</h3>
                <div class="grid grid-cols-2 px-8 pb-4  gap-4">

                    <!-- Custom Verifier -->
                    <checkbox-input v-model="form.webhook_custom_verifier_activation"
                        :error="form.errors.webhook_custom_verifier_class"
                        :label="$t('slskey_groups.webhook_custom_verifier_activation')" />
                    <select-input v-if="form.webhook_custom_verifier_activation"
                        v-model="form.webhook_custom_verifier_class" :error="form.errors.webhook_custom_verifier_class"
                        :options="availableWebhookCustomVerifiers"
                        :helpText="$t('slskey_groups.webhook_custom_verifier_help')"
                        :label="$t('slskey_groups.webhook_custom_verifier_class')" />
                    <div v-if="!form.webhook_custom_verifier_activation" />

                    <!-- Mail Activation -->
                    <checkbox-input class="w-full" v-model="form.webhook_mail_activation"
                        :error="form.errors.webhook_mail_activation"
                        :label="$t('slskey_groups.webhook_mail_activation')" />
                    <select-input v-if="form.webhook_mail_activation" v-model="form.webhook_mail_activation_domains"
                        :helpText="$t('slskey_groups.webhook_mail_activation_domains_help')"
                        :error="form.errors.webhook_mail_activation_domains"
                        :options="availableWebhookMailActivationDomains"
                        :label="$t('slskey_groups.webhook_mail_activation_domains')" />

                    <div class="col-span-2 text-sm text-gray-500 italic">
                        {{ $t('slskey_groups.webhook_activation_info') }}
                    </div>
                </div>

                <!-- Expiration: Webhook -->
                <div class="border-t border-b border-default-gray"></div>
                <h3 class="text-lg px-4 py-2 m-4 text-color-slsp bg-color-slsp-bg rounded-sm">
                    {{ $t('slskey_groups.webhook_deactivation_details') }}</h3>
                <div class="grid grid-cols-2 px-8 pb-4  gap-4">

                    <!-- Verifier Deactivation -->
                    <checkbox-input v-model="form.webhook_custom_verifier_deactivation"
                        :disabled="!form.webhook_custom_verifier_activation"
                        :error="form.errors.webhook_custom_verifier_deactivation"
                        :label="$t('slskey_groups.webhook_custom_verifier_deactivation')" />

                    <div v-if="form.webhook_custom_verifier_deactivation"
                        class="col-span-1 text-sm text-gray-500 italic">
                        Uses same verifier, as for activation:
                        <span>{{ form.webhook_custom_verifier_class }}</span>
                    </div>
                    <div v-else />

                    <!-- Days Deactivation -->
                    <number-input v-model="form.days_activation_duration" :error="form.errors.days_activation_duration"
                        :label="$t('slskey_groups.days_activation_duration')"
                        :placeholder="$t('slskey_groups.days_activation_duration_placeholder')" />

                    <div class="col-span-2 text-sm text-gray-500 italic">
                        {{ $t('slskey_groups.webhook_deactivation_info') }}
                    </div>
                </div>

                <!-- Token Reactivation Details -->
                <template v-if="form.webhook_mail_activation || form.days_activation_duration">
                    <div class="border-t border-b border-default-gray"></div>
                    <h3 class="text-lg px-4 py-2 m-4 text-color-slsp bg-color-slsp-bg rounded-sm">
                        {{ $t('slskey_groups.webhook_token_reactivation_details') }}</h3>
                    <div class="grid grid-cols-2 px-8 pb-4  gap-4">

                        <!-- Token Reactivation -->
                        <checkbox-input v-model="form.webhook_token_reactivation"
                            :error="form.errors.webhook_token_reactivation"
                            :helpText="$t('slskey_groups.webhook_token_reactivation_help')"
                            :label="$t('slskey_groups.webhook_token_reactivation')" />
                        <div />
                        <template v-if="form.webhook_token_reactivation">
                            <number-input v-model="form.webhook_token_reactivation_days_send_before_expiry"
                                :error="form.errors.webhook_token_reactivation_days_send_before_expiry"
                                :label="$t('slskey_groups.webhook_token_reactivation_days_send_before_expiry')" />
                            <number-input v-model="form.webhook_token_reactivation_days_token_validity"
                                :error="form.errors.webhook_token_reactivation_days_token_validity"
                                :label="$t('slskey_groups.webhook_token_reactivation_days_token_validity')" />
                        </template>

                    </div>
                </template>
            </template>

            <!-- Cloud App Permissions -->
            <div class="border-t border-b border-default-gray"></div>
            <h3 class="text-lg px-4 py-2 m-4 text-color-slsp bg-color-slsp-bg rounded-sm">{{
                $t('slskey_groups.cloud_app_permissions') }}</h3>
            <div class="grid grid-cols-2 px-8 pb-4  gap-4">

                <checkbox-input v-model="form.cloud_app_allow" :error="form.errors.cloud_app_allow"
                    :label="$t('slskey_groups.cloud_app_allow')" />
                <div />
                <!-- Roles -->
                <text-input v-if="form.cloud_app_allow" v-model="form.cloud_app_roles"
                    :placeholder="$t('slskey_groups.cloud_app_roles_placeholder')" :error="form.errors.cloud_app_roles"
                    :label="$t('slskey_groups.cloud_app_roles')" />
                <text-input v-if="form.cloud_app_allow" v-model="form.cloud_app_roles_scopes"
                    :placeholder="$t('slskey_groups.cloud_app_roles_scopes_placeholder')"
                    :error="form.errors.cloud_app_roles_scopes" :label="$t('slskey_groups.cloud_app_roles_scopes')" />
            </div>

            <!-- Switch Groups -->
            <div class="border-t border-b border-default-gray"></div>
            <h3 class="text-lg px-4 py-2 m-4 text-color-slsp bg-color-slsp-bg rounded-sm">{{
                $t('slskey_groups.switch_groups') }}</h3>
            <div class="grid grid-cols-1 px-8 pb-4  gap-4">
                <table class="table-auto min-w-full rounded-sm">
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
                                    <DefaultIconButton class="bg-color-blocked py-1" icon="x"
                                        :tooltip="$t('slskey_groups.delete_switch_group')"
                                        @click="removeGroup(switchGroup.id)" />
                                </td>
                            </tr>
                        </template>
                        <template v-else>
                            <tr>
                                <td class="pl-6 py-2 whitespace-nowrap italic">{{
                                    $t('slskey_groups.no_switch_groups')
                                }}.</td>
                            </tr>
                        </template>

                    </tbody>
                </table>
                <div class="flex flex-row w-full items-center gap-x-8">
                    <SelectInput class="w-full" v-model="newSwitchGroup" :options="this.availableSwitchGroups.data"
                        :placeholder="$t('slskey_groups.switch_groups')" @change="addGroup()">
                    </SelectInput>
                </div>
            </div>
            <div class="border-t border-b border-default-gray"></div>
            <div class="flex">
                <div class="flex w-full flex-row justify-between gap-4 px-4 py-2">
                    <div class="flex flex-row gap-4">
                        <DefaultButton @click="cancel()" class="py-1 text-black w-fit"
                            :tooltip="$t('slskey_groups.cancel')">
                            {{ $t('slskey_groups.cancel') }}
                        </DefaultButton>
                        <DefaultButton v-if="!isCreating" @click="deleteGroup()" class="py-1 text-color-blocked w-fit"
                            icon="trash" :tooltip="$t('slskey_groups.delete')">
                            {{ $t('slskey_groups.delete') }}
                        </DefaultButton>
                    </div>
                    <DefaultButton @click="submit()" class="w-fit" icon="save" :tooltip="$t('slskey_groups.save')">
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
                this.form.webhook_custom_verifier_activation = 0;
                this.form.webhook_custom_verifier_deactivation = 0;
                this.form.webhook_custom_verifier_class = null;
                this.form.webhook_persistent = 0;
                this.form.webhook_token_reactivation = 0;
            } else {
                this.form.days_activation_duration = null;
                this.form.days_expiration_reminder = null;
                this.form.webhook_persistent = 1;
            }
        },
        'form.webhook_mail_activation': function (webhook_mail_activation) {
            if (webhook_mail_activation) {
                this.form.webhook_custom_verifier_activation = 0;
                this.form.webhook_custom_verifier_class = null;
                this.form.webhook_custom_verifier_deactivation = 0;
            }
        },
        'form.webhook_custom_verifier_activation': function (webhook_custom_verifier_activation) {
            if (webhook_custom_verifier_activation) {
                this.form.webhook_mail_activation = 0;
                this.form.webhook_mail_activation_domains = null;
            }
        },
        'form.webhook_token_reactivation': function (webhook_token_reactivation) {
            if (webhook_token_reactivation) {
                this.form.webhook_custom_verifier_deactivation = 0;
            }
            if (!webhook_token_reactivation) {
                this.form.webhook_token_reactivation_days_send_before_expiry = null;
                this.form.webhook_token_reactivation_days_token_validity = null;
            }
        },
        'form.webhook_custom_verifier_deactivation': function (webhook_custom_verifier_deactivation) {
            if (webhook_custom_verifier_deactivation) {
                this.form.webhook_token_reactivation = 0;
                this.form.days_activation_duration = null;
            }
        },
        'form.days_activation_duration': function (days_activation_duration) {
            if (days_activation_duration > 0 && this.form.workflow === 'Webhook') {
                this.form.webhook_custom_verifier_deactivation = 0;
                this.form.webhook_token_reactivation = 0;
            }
        },
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