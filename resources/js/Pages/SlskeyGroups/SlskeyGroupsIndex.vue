<template>
    <AppLayout :title="$t('slskey_groups.title')" :breadCrumbs="[{ name: $t('slskey_groups.title') }]">

        <!--
        <div class="flex py-5 gap-4 items-end">
            <SelectFilter v-model="selectedUser" :label="$t('admin_users.title')" :options="adminUsers" />
            <SelectFilter v-model="selectedGroup" :label="$t('slskey_code')" :options="slskeyGroups" />
        </div>
        -->
        <DefaultButton @click="createGroup" icon="plus" class="w-fit bg-color-slsp text-white py-2 mt-5">
            {{ $t('slskey_groups.create_new') }}
        </DefaultButton>
        <div class="mt-5 mb-10 bg-white shadow-md rounded-md">
            <table class="table-auto min-w-full divide-y divide-gray-table rounded-md">
                <thead class="">
                    <tr>
                        <th class="py-4 px-4 text-left whitespace-nowrap"> {{ $t('slskey_groups.slskey_code') }} </th>
                        <th class="py-4 px-4 text-left whitespace-nowrap"> {{ $t('slskey_groups.name') }} </th>
                        <th class="py-4 px-4 text-left whitespace-nowrap"> {{ $t('slskey_groups.webhook_custom_verifier_class') }} </th>
                        <!-- <th class="py-4 px-4 text-left whitespace-nowrap"> {{ $t('slskey_groups.switch_groups_count') }} </th> -->
                        <th class="py-4 px-4 text-left whitespace-nowrap"> {{ $t('switch_groups.publishers_title') }}
                        </th>
                        <th class="py-4 px-4 text-left whitespace-nowrap"> {{ $t('slskey_groups.active_user_count') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-table">
                    <template v-if="slskeyGroups.data.length > 0">
                        <tr v-for="group in slskeyGroups.data" :key="'group' + group.id"
                            class="hover:bg-gray-100 focus-within:bg-gray-100">
                            <td class="align-top">
                                <Link class="flex flex-col px-6 py-3 whitespace-nowrap gap-y-4"
                                    :href="`/admin/groups/${group.slskey_code}`">
                                {{ group.slskey_code }}
                                </Link>
                            </td>

                            <td class="align-top">
                                <Link class="flex flex-col px-6 py-3 whitespace-nowrap gap-y-4"
                                    :href="`/admin/groups/${group.slskey_code}`">
                                <SlskeyGroupNameAndIcon :slskeyGroupName="group.name" :workflow="group.workflow" />

                                </Link>
                            </td>

                            <td class="align-top">
                                <Link class="flex flex-col px-6 py-3 whitespace-nowrap gap-y-4"
                                    :href="`/admin/groups/${group.slskey_code}`">
                                {{ group.webhook_custom_verifier_class }}
                                </Link>
                            </td>
                            <!--
                            <td class="align-top">
                                <Link class="flex flex-col px-6 py-3 whitespace-nowrap gap-y-4"
                                    :href="`/admin/groups/${group.slskey_code}`">
                                    {{ group.switchGroupsCount }}
                                </Link>
                            </td>
                            -->
                            <td class="align-top">
                                <Link class="flex flex-col px-6 py-3 whitespace-nowrap"
                                    :href="`/admin/groups/${group.slskey_code}`">

                                <span v-for="publisher in group.publishers" :key="publisher.id">
                                    {{ publisher }}
                                </span>
                                </Link>
                            </td>

                            <td class="align-top">
                                <Link class="flex flex-col px-6 py-3 whitespace-nowrap gap-y-4"
                                    :href="`/admin/groups/${group.slskey_code}`">
                                {{ group.activeUserCount }}
                                </Link>
                            </td>


                        </tr>
                    </template>
                    <template v-else>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $t('slskey_groups.no_records') }}.</td>
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
import SlskeyGroupNameAndIcon from '../../Shared/SlskeyGroupNameAndIcon.vue';
export default {
    components: {
        AppLayout,
        SelectFilter,
        Inertia,
        DefaultButton,
        SlskeyGroupNameAndIcon
    },
    props: {
        slskeyGroups: Object
    },
    data() {
        return {

        }
    },
    methods: {
        createGroup() {
            Inertia.visit('/admin/groups/create');
        }
    }
}
</script>