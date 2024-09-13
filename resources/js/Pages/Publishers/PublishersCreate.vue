
<template>
    <AppLayout :title="$t('publishers.create_new')" :breadCrumbs="[
        { name: $t('publishers.title'), link: '/admin/publishers' },
        { name: $t('publishers.create_new') }
    ]">
        <div class="my-8 bg-white shadow-md rounded-md">
            <PublisherForm :isCreating="true" :modelValue="form" :availableSwitchGroups="availableSwitchGroups"
                @submit="savePublisher" @cancel="cancel" :availableProtocolOptions="availableProtocolOptions"
                :availableStatusOptions="availableStatusOptions" />
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Inertia } from '@inertiajs/inertia';
import PublisherForm from './Partial/PublisherForm.vue';
export default {
    components: {
        AppLayout,
        PublisherForm
    },
    props: {
        availableSwitchGroups: Object,
        availableProtocolOptions: Object,
        availableStatusOptions: Object
    },
    data() {
        return {
            form: this.$inertia.form({
                name: null,
                entity_id: null,
                protocol: null,
                internal_note: null,
                status: null,
                switchGroups: []
            })
        }
    },
    methods: {
        savePublisher() {
            this.form.post(`/admin/publishers`);
        },
        cancel() {
            this.$inertia.visit('/admin/publishers');
        },

    },

}
</script>