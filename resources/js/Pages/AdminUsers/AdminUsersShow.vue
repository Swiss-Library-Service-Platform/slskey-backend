
<template>
    <AppLayout :title="adminUser.data.display_name" :breadCrumbs="[
        { name: $t('admin_users.title'), link: '/admin/users' },
        { name: adminUser.data.display_name }
    ]">
        <div class="mt-5 mb-10 bg-white shadow-md rounded-md">
            <AdminUserForm :availableSlskeyGroups="availableSlskeyGroups" :modelValue="form" @submit="saveAdminUser"
                :isCreating="false" @cancel="cancel" @delete="deleteAdminUser" @resetPassword="resetPassword"/>
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
import AdminUserForm from './Partial/AdminUserForm.vue';
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
        SlskeyGroupForm,
        AdminUserForm
    },
    props: {
        adminUser: Object,
        availableSlskeyGroups: Object
    },
    data() {
        return {
            form: this.$inertia.form({
                is_edu_id: this.adminUser.data.is_edu_id,
                user_identifier: this.adminUser.data.user_identifier,
                display_name: this.adminUser.data.display_name,
                password: this.adminUser.data.password,
                is_slsp_admin: this.adminUser.data.is_slsp_admin,
                slskeyGroups: this.adminUser.data.slskeyGroups
            })
        }
    },
    methods: {
        saveAdminUser() {
            this.form.put(`/admin/users/${this.adminUser.data.user_identifier}`);
        },
        cancel() {
            this.$inertia.visit('/admin/users');
        },
        deleteAdminUser() {
            if (confirm(this.$t('admin_users.delete_confirm'))) {
                this.form.delete(`/admin/users/${this.adminUser.data.user_identifier}`);
            }
        },
        resetPassword() {
            let newPassword = prompt('Type new password here:');
            if (newPassword) {
                this.$inertia.put(`/admin/users/${this.adminUser.data.user_identifier}/resetpassword`, {
                    password: newPassword
                });
            }
        }
    }


}
</script>