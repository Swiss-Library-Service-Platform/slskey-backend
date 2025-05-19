
<template>
    <AppLayout :title="switchGroup.data.name" :breadCrumbs="[
        { name: $t('switch_groups.title'), link: '/admin/switchgroups' },
        { name: switchGroup.data.name }
    ]">
        <div class="w-max my-8 bg-white shadow-md rounded-sm">
            <SwitchGroupForm :modelValue="form" @submit="saveSwitchGroup"
                @cancel="cancel"  />
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
import SwitchGroupForm from '@/Pages/SwitchGroups/Partial/SwitchGroupForm.vue';
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
        SwitchGroupForm
    },
    props: {
        switchGroup: Object,
    },
    data() {
        return {
            form: this.$inertia.form({
                id: this.switchGroup.data.id,
                name: this.switchGroup.data.name,
                switch_group_id: this.switchGroup.data.switch_group_id,
                publishers: this.switchGroup.data.publishers,
                slskeyGroups: this.switchGroup.data.slskeyGroups,
                members_count: this.switchGroup.data.members_count,
            })
        }
    },
    methods: {
        saveSwitchGroup() {
            this.form.put(`/admin/switchgroups/${this.switchGroup.data.id}`);
        },
        cancel() {
            this.$inertia.visit('/admin/switchgroups');
        },
    },


}
</script>