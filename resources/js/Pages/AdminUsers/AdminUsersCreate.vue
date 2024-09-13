
<template>
    <AppLayout :title="$t('admin_users.create_new')" :breadCrumbs="[
        { name: $t('admin_users.title'), link: '/admin/users' },
        { name: $t('admin_users.create_new') }
    ]">
        <div class="w-max my-8 bg-white shadow-md rounded-md">
            <AdminUserForm 
                :isCreating="true"
                :availableSlskeyGroups="availableSlskeyGroups" :modelValue="form" @submit="saveSlskeyGroup"
                @cancel="cancel" />
        </div>
    </AppLayout>
</template>


<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import SelectFilter from '@/Shared/Filters/SelectFilter.vue';
import DefaultButton from '@/Shared/Buttons/DefaultButton.vue';
import { Inertia } from '@inertiajs/inertia';
import TextInput from '../../Shared/Forms/TextInput.vue';
import AdminUserForm from './Partial/AdminUserForm.vue';

export default {
    components: {
        AppLayout,
        DefaultButton,
        SelectFilter,
        Inertia,
        TextInput,
        AdminUserForm
    },
    props: {
        adminUsers: Object,
        availableSlskeyGroups: Object,
    },
    data() {
        return {
            form: this.$inertia.form({
                is_edu_id: 1,
                user_identifier: "",
                display_name: "",
                password: "",
                is_slsp_admin: 0,
                slskeyGroups: []
            })
        }
    },
    methods: {
        saveSlskeyGroup() {
            this.form.post(`/admin/users`);
        },
        cancel() {
            this.$inertia.visit('/admin/users');
        }
    },
    computed: {

    },

}
</script>