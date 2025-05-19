
<template>
    <AppLayout :title="$t('publishers.title')" :breadCrumbs="[{ name: $t('publishers.title') }]">

        <DefaultButton @click="createPublisher" icon="plus" class="w-fit bg-color-slsp text-white py-2 my-8">
            {{ $t('publishers.create_new') }}
        </DefaultButton>
        <div class="overflow-x-auto bg-white shadow-md rounded-sm">
            <table class="table-auto min-w-full divide-y divide-gray-table rounded-sm">
                <thead class="">
                    <tr>
                        <th class="py-4 px-4 text-left whitespace-nowrap"> {{ $t('publishers.name') }} </th>
                        <th class="py-4 px-4 text-left whitespace-nowrap"> {{ $t('publishers.entity_id') }} </th>
                        <th class="py-4 px-4 text-left whitespace-nowrap"> {{ $t('publishers.protocol') }} </th>
                        <th class="py-4 px-4 text-left whitespace-nowrap"> {{ $t('publishers.status') }} </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-table">
                    <template v-if="publishers.data.length > 0">
                        <tr v-for="publisher in publishers.data" :key="'publisher' + publisher.id"
                        class="hover:bg-gray-100 focus-within:bg-gray-100">
                            <td class="align-top">
                                <Link class="flex flex-col px-6 py-3 whitespace-nowrap gap-y-4"
                                    :href="`/admin/publishers/${publisher.id}`">
                                    {{ publisher.name }}
                                </Link>
                            </td>

                            <td class="align-top">
                                <Link class="flex flex-col px-6 py-3 whitespace-nowrap gap-y-4"
                                    :href="`/admin/publishers/${publisher.id}`">
                                    {{ publisher.entity_id }}
                                </Link>
                            </td>

                            <td class="align-top">
                                <Link class="flex flex-col px-6 py-3 whitespace-nowrap gap-y-4"
                                    :href="`/admin/publishers/${publisher.id}`">
                                    {{ publisher.protocol }}
                                </Link>
                            </td>

                            <td class="align-top">
                                <Link class="flex flex-col px-6 py-3 whitespace-nowrap gap-y-4"
                                    :href="`/admin/publishers/${publisher.id}`">
                                    {{ publisher.status }}
                                </Link>
                            </td>

                        </tr>
                    </template>
                    <template v-else>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $t('publishers.no_records') }}.</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

    </AppLayout>
</template>


<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import SelectFilter from '@/Shared/Filters/SelectFilter.vue';
import { Inertia } from '@inertiajs/inertia';
import DefaultButton from '@/Shared/Buttons/DefaultButton.vue';
export default {
    components: {
        AppLayout,
        SelectFilter,
        Inertia,
        DefaultButton
    },
    props: {
        publishers: Object
    },
    data() {
        return {

        }
    },
    methods: {
        createPublisher() {
            Inertia.visit('/admin/publishers/create');
        }
    }
}
</script>