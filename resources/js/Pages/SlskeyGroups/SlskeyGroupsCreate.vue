
<template>
    <AppLayout :title="$t('slskey_groups.create_new')" :breadCrumbs="[
        { name: $t('slskey_groups.title'), link: '/admin/groups' },
        { name: $t('slskey_groups.create_new') }
    ]">
        <div class="w-auto my-8 bg-white shadow-md rounded-sm">
            <SlskeyGroupForm :isCreating="true" :modelValue="form" :availableSwitchGroups="availableSwitchGroups" @submit="saveSlskeyGroup"
                :availableWorkflows="availableWorkflows" :availableWebhookCustomVerifiers="availableWebhookCustomVerifiers"
                :availableWebhookMailActivationDomains="availableWebhookMailActivationDomains"
                @cancel="cancel" />
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import SelectFilter from '@/Shared/Filters/SelectFilter.vue';
import TextInput from '@/Shared/Forms/TextInput.vue';
import SelectInput from '@/Shared/Forms/SelectInput.vue';
import CheckboxInput from '@/Shared/Forms/CheckboxInput.vue';
import DefaultButton from '@/Shared/Buttons/DefaultButton.vue';
import DefaultIconButton from '@/Shared/Buttons/DefaultIconButton.vue';
import NumberInput from '@/Shared/Forms/NumberInput.vue';
import SlskeyGroupForm from '@/Pages/SlskeyGroups/Partial/SlskeyGroupForm.vue';
import { NumberFormat } from 'vue-i18n';
export default {
    components: {
        AppLayout,
        SelectFilter,
        DefaultIconButton,
        DefaultButton,
        TextInput,
        SelectInput,
        NumberInput,
        CheckboxInput,
        NumberFormat,
        SlskeyGroupForm
    },
    props: {
        availableSwitchGroups: Object,
        availableWorkflows: Object,
        availableWebhookCustomVerifiers: Object,
        availableWebhookMailActivationDomains: Object
    },
    data() {
        return {
            form: this.$inertia.form({
                name: null,
                slskey_code: null,
                workflow: null,
                send_activation_mail: 0,
                show_member_educational_institution: 0,
                alma_iz: null,
                mail_sender_address: null,
                webhook_custom_verifier_activation: 0,
                webhook_custom_verifier_class: null,
                webhook_custom_verifier_deactivation: 0,
                webhook_token_reactivation: 0,
                webhook_secret: null,
                webhook_persistent: 0,
                days_activation_duration: null,
                days_expiration_reminder: null,
                webhook_mail_activation: 0,
                webhook_mail_activation_domains: null,
                webhook_token_reactivation_days_send_before_expiry: null,
                webhook_token_reactivation_days_token_validity: null,
                cloud_app_allow: 0,
                cloud_app_roles: null,
                cloud_app_roles_scopes: null,
                switchGroups: []
            })
        }
    },
    methods: {
        saveSlskeyGroup() {
            this.form.post(`/admin/groups`);
        },
        cancel() {
            this.$inertia.visit('/admin/groups');
        },

    },

}
</script>