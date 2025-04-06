
<template>
    <AppLayout :title="slskeyGroup.data.name" :breadCrumbs="[
        { name: $t('slskey_groups.title'), link: '/admin/groups' },
        { name: slskeyGroup.data.name }
    ]">
        <div class="w-max my-8 mb-20 bg-white shadow-md rounded-sm">
            <SlskeyGroupForm :isCreating="false" :modelValue="form" :availableSwitchGroups="availableSwitchGroups"
                :availableWorkflows="availableWorkflows" :availableWebhookCustomVerifiers="availableWebhookCustomVerifiers"
                :availableWebhookMailActivationDomains="availableWebhookMailActivationDomains"
                @submit="saveSlskeyGroup" @cancel="cancel" @delete="deleteSlskeyGroup" />
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import SelectFilter from '@/Shared/Filters/SelectFilter.vue';
import { Inertia } from '@inertiajs/inertia';
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
        Inertia,
        DefaultButton,
        TextInput,
        SelectInput,
        NumberInput,
        CheckboxInput,
        NumberFormat,
        SlskeyGroupForm
    },
    props: {
        slskeyGroup: Object,
        availableSwitchGroups: Object,
        availableWorkflows: Object,
        availableWebhookCustomVerifiers: Object,
        availableWebhookMailActivationDomains: Object
    },
    data() {
        return {
            form: this.$inertia.form({
                name: this.slskeyGroup.data.name,
                slskey_code: this.slskeyGroup.data.slskey_code,
                workflow: this.slskeyGroup.data.workflow,
                send_activation_mail: this.slskeyGroup.data.send_activation_mail,
                show_member_educational_institution: this.slskeyGroup.data.show_member_educational_institution,
                alma_iz: this.slskeyGroup.data.alma_iz,
                mail_sender_address: this.slskeyGroup.data.mail_sender_address,
                webhook_custom_verifier_activation: this.slskeyGroup.data.webhook_custom_verifier_activation,
                webhook_custom_verifier_class: this.slskeyGroup.data.webhook_custom_verifier_class,
                webhook_custom_verifier_deactivation: this.slskeyGroup.data.webhook_custom_verifier_deactivation,
                webhook_secret: this.slskeyGroup.data.webhook_secret,
                webhook_persistent: this.slskeyGroup.data.webhook_persistent,
                webhook_token_reactivation: this.slskeyGroup.data.webhook_token_reactivation,
                days_activation_duration: this.slskeyGroup.data.days_activation_duration,
                days_expiration_reminder: this.slskeyGroup.data.days_expiration_reminder,
                webhook_mail_activation: this.slskeyGroup.data.webhook_mail_activation,
                webhook_mail_activation_domains: this.slskeyGroup.data.webhook_mail_activation_domains,
                webhook_token_reactivation_days_send_before_expiry: this.slskeyGroup.data.webhook_token_reactivation_days_send_before_expiry,
                webhook_token_reactivation_days_token_validity: this.slskeyGroup.data.webhook_token_reactivation_days_token_validity,
                cloud_app_allow: this.slskeyGroup.data.cloud_app_allow,
                cloud_app_roles: this.slskeyGroup.data.cloud_app_roles,
                cloud_app_roles_scopes: this.slskeyGroup.data.cloud_app_roles_scopes,
                switchGroups: this.slskeyGroup.data.switchGroups
            })
        }
    },
    methods: {
        saveSlskeyGroup() {
            this.form.put(`/admin/groups/${this.slskeyGroup.data.id}`);
        },
        cancel() {
            this.$inertia.visit('/admin/groups');
        },
        deleteSlskeyGroup() {
            if (confirm(this.$t('slskey_groups.delete_confirm'))) {
                this.form.delete(`/admin/groups/${this.slskeyGroup.data.id}`);
            }
        },
    }


}
</script>