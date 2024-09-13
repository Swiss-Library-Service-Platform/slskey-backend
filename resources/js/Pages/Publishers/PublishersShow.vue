
<template>
    <AppLayout :title="publisher.data.name" :breadCrumbs="[
        { name: $t('publishers.title'), link: '/admin/publishers' },
        { name: publisher.data.name }
    ]">
        <div class="my-8 bg-white shadow-md rounded-md">
            <PublisherForm :isCreating="false" :modelValue="form" :availableSwitchGroups="availableSwitchGroups"
                :availableProtocolOptions="availableProtocolOptions" :availableStatusOptions="availableStatusOptions"
                    @submit=" savePublisher" @cancel="cancel" @delete="deletePublisher" />
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
        publisher: Object,
        availableSwitchGroups: Object,
        availableProtocolOptions: Object,
        availableStatusOptions: Object
    },
    data() {
        return {
            form: this.$inertia.form({
                name: this.publisher.data.name,
                entity_id: this.publisher.data.entity_id,
                protocol: this.publisher.data.protocol,
                internal_note: this.publisher.data.internal_note,
                status: this.publisher.data.status,
                switchGroups: this.publisher.data.switchGroups
            })
        }
    },
    methods: {
        savePublisher() {
            this.form.put(`/admin/publishers/${this.publisher.data.id}`);
        },
        cancel() {
            this.$inertia.visit('/admin/publishers');
        },
        deletePublisher() {
            if (confirm(this.$t('publishers.delete_confirm'))) {
                this.form.delete(`/admin/publishers/${this.publisher.data.id}`);
            }
        }
    },


}
</script>