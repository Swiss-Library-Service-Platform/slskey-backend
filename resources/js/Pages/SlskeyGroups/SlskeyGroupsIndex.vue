<template>
    <AppLayout :title="$t('slskey_groups.title')" :breadCrumbs="[{ name: $t('slskey_groups.title') }]">

        <div class="flex bg-white p-4 rounded-b shadow items-end justify-between flex-wrap">
            <FilterControl @reset="reset">
                <SearchFilter v-model="form.search" :label="$t('slskey_groups.title')" />
            </FilterControl>
            <DefaultButton @click="createGroup" icon="plus" class="w-fit py-2">
                {{ $t('slskey_groups.create_new') }}
            </DefaultButton>
        </div>

        <div class="my-8 overflow-x-auto bg-white shadow-md rounded-sm">
            <table class="table-auto min-w-full divide-y divide-gray-table rounded-sm">
                <thead class="bg-color-slsp-bg-lighter px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-4 px-4 text-left whitespace-nowrap"> {{ $t('slskey_groups.slskey_code') }} </th>
                        <th class="py-4 px-4 text-left whitespace-nowrap"> {{ $t('slskey_groups.name') }} </th>
                        <th class="py-4 px-4 text-left whitespace-nowrap"> {{ $t('slskey_groups.alma_iz') }} </th>
                        <!-- 
                        <th class="py-4 px-4 text-left whitespace-nowrap"> {{
                            $t('slskey_groups.webhook_persistent_title') }} </th>
                        <th class="py-4 px-4 text-left whitespace-nowrap"> {{
                            $t('slskey_groups.send_activation_mail_title') }} </th>
                        <th class="py-4 px-4 text-left whitespace-nowrap"> {{
                            $t('slskey_groups.webhook_custom_verifier_class') }} </th>
                            -->
                        <th class="py-4 px-4 text-left whitespace-nowrap"> {{ $t('slskey_groups.switch_groups_count') }}
                        </th>
                        <!--<th class="py-4 px-4 text-left whitespace-nowrap"> {{ $t('switch_groups.publishers_title') </th> }}-->
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
                                {{ group.alma_iz }}
                                </Link>
                            </td>
                            <!-- 
                            <td class="align-top">
                                <Link v-if="group.workflow === 'Webhook'"
                                    class=" flex flex-col px-6 py-3 whitespace-nowrap gap-y-4"
                                    :href="`/admin/groups/${group.slskey_code}`">
                                <template v-if="group.webhook_persistent">
                                    Yes
                                </template>
                                <template v-else>
                                    No
                                </template>
                                </Link>
                            </td>

                            <td class="align-top">
                                <Link v-if="group.send_activation_mail"
                                    class="flex flex-col px-6 py-3 whitespace-nowrap gap-y-4"
                                    :href="`/admin/groups/${group.slskey_code}`">
                                Yes
                                </Link>
                            </td>

                            <td class="align-top">
                                <Link v-if="group.webhook_custom_verifier_class"
                                    class="flex flex-col px-6 py-3 whitespace-nowrap gap-y-4"
                                    :href="`/admin/groups/${group.slskey_code}`">
                                {{ group.webhook_custom_verifier_class }}
                                </Link>
                                <Link v-if="group.webhook_mail_activation"
                                    class="flex flex-col px-6 py-3 whitespace-nowrap gap-y-4"
                                    :href="`/admin/groups/${group.slskey_code}`">
                                {{ $t('slskey_groups.webhook_mail_activation_title') }}
                                </Link>
                            </td>
                            -->
                            <td class="align-top">
                                <Link class="flex flex-col px-6 py-3 whitespace-nowrap gap-y-4"
                                    :href="`/admin/groups/${group.slskey_code}`">
                                {{ group.switchGroupsCount }}
                                </Link>
                            </td>
                            <!--
                            <td class="align-top">
                                <Link class="flex flex-col px-6 py-3 whitespace-nowrap"
                                    :href="`/admin/groups/${group.slskey_code}`">

                                <span v-for="publisher in group.publishers" :key="publisher.id">
                                    {{ publisher }}
                                </span>
                                </Link>
                            </td>
                            -->

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
import SlskeyGroupNameAndIcon from '@/Shared/SlskeyGroupNameAndIcon.vue';
import FilterControl from '@/Shared/Filters/FilterControl.vue';
import SearchFilter from '@/Shared/Filters/SearchFilter.vue';
import throttle from "lodash/throttle"
import omitBy from 'lodash/omitBy'
import axios from 'axios';

export default {
    components: {
        AppLayout,
        SearchFilter,
        FilterControl,
        Inertia,
        DefaultButton,
        SlskeyGroupNameAndIcon
    },
    props: {
        slskeyGroups: Object,
        filters: Object
    },
    data() {
        return {
            form: {
                search: this.filters.search
            }
        }
    },
    methods: {
        createGroup() {
            Inertia.visit('/admin/groups/create');
        },
        reset() {
            this.form = {
                search: ''
            }
        }
    },
    watch: {
        form: {
            deep: true,
            handler: throttle(function (new_value, old_value) {
                Inertia.get('/admin/groups', omitBy(this.form, _.overSome([_.isNil, _.isNaN])),
                    {
                        preserveState: true,
                        replace: true
                    }
                )
            }, 500)
        }
    }
}
</script>