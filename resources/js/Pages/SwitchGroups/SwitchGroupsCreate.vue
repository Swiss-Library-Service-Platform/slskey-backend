
<template>
    <AppLayout :title="$t('switch_groups.create')" :breadCrumbs="[
        { name: $t('switch_groups.title'), link: '/admin/switchgroups' },
        { name: $t('switch_groups.create') }
    ]">
        <div class="w-max my-8 bg-white shadow-md rounded-sm">
            <SwitchGroupForm :modelValue="form" @submit="saveSwitchGroup"
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
import SwitchGroupForm from '@/Pages/SwitchGroups/Partial/SwitchGroupForm.vue';
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
        SwitchGroupForm
    },
    props: {
    },
    data() {
        return {
            form: this.$inertia.form({
                name: null,
                switch_group_id: null,
                publishers: null
            })
        }
    },
    methods: {
        saveSwitchGroup() {
            this.form.post(`/admin/switchgroups`);
        },
        cancel() {
            this.$inertia.visit('/admin/groups');
        },

    },

}
</script>