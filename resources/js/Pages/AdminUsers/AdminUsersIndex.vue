<template>
    <AppLayout title="Users" :breadCrumbs="[{ name: $t('admin_users.title') }]">
        <div class="flex bg-white p-4 rounded-b shadow items-end justify-between flex-wrap">
            <div class="flex flex-row gap-x-16 w-full justify-between">
                <FilterControl @reset="reset">
                    <SearchFilter v-model="form.search" :label="$t('user_management.search')"
                        :placeholder="$t('user_management.search_placeholder')" />
                    <SelectFilter v-model="form.slskeyCode" :label="$t('slskey_groups.slskey_code_description')"
                        :options="slskeyGroups.data" />
                </FilterControl>
                <TabFilter :tab1="$t('admin_users.admin_portal')" :tab2="$t('admin_users.alma_app')" icon1="user"
                    icon2="cloud" :label="$t('admin_users.user_type')" v-model="displayTab" />
            </div>
            <div class="flex gap-x-4">
                <DefaultButton v-show="displayTab == 0" icon="plus" @click="createUser"
                    class="w-fit py-2 mt-4">
                    {{ $t('admin_users.create_new') }}
                </DefaultButton>
            </div>
        </div>

        <!-- Admin Portal Users -->
        <div v-show="displayTab == 0" class="overflow-x-auto my-8 bg-white shadow-md rounded-sm">
            <table class="table-auto  min-w-full divide-y divide-gray-table rounded-sm">
                <thead class="bg-slsp-bg-lighter px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <tr class="text-left whitespace-nowrap">
                        <th class="py-4 px-4 "> {{ $t('admin_users.user_identifier') }} </th>
                        <th class="py-4 px-4 "> {{ $t('admin_users.display_name') }} </th>
                        <th class="py-4 px-4 "> {{ $t('admin_users.permissions') }} </th>
                        <th class ="py-4 px-4 text-left"> {{ $t('admin_users.created_at') }} </th>
                        <th class="py-4 px-4 "> {{ $t('admin_users.last_login') }} </th>
                        <th class="py-4 px-4 w-full"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-table">
                    <template v-if="adminUsersPortal.data.length > 0">
                        <tr v-for="user in adminUsersPortal.data" :key="'user' + user.id"
                            @click="navigateTo(user.user_identifier)"
                            class="focus-within:bg-gray-100 cursor-pointer hover:bg-gray-100 text-left whitespace-nowrap">
                            <td class="align-top">
                                <div class="flex px-6 py-3 ">
                                    {{ user.user_identifier }}
                                </div>
                            </td>
                            <td class="align-top">
                                <div class="flex px-6 py-3 ">
                                    {{ user.display_name }}
                                </div>
                            </td>
                            <td class="align-top">
                                <div v-if="user.is_slsp_admin"
                                    class="text-slsp font-bold flex px-6 py-3 ">
                                    {{ $t('admin_users.slsp_admin') }}
                                </div>
                                <div v-else class="flex flex-col px-6 py-3 gap-2">
                                    <div v-for="slskeyGroup in user.slskeyGroups" :key="slskeyGroup.id">
                                        {{ slskeyGroup.name }}
                                    </div>
                                </div>
                            </td>
                            <td class="align-top">
                                <div class="flex px-6 py-3 ">
                                    {{ user.created_at ? this.$moment(user.created_at).format('ll') : '-' }}
                                </div>
                            </td>
                            <td class="align-top">
                                <div class="flex px-6 py-3 "
                                                                :class="{ 'text-blocked': user.last_login && this.$moment(user.last_login).isBefore(this.$moment().subtract(6, 'months')) }">

                                    {{ formatDate(user.last_login) }}
                                </div>
                            </td>
                            <td class="align-top">
                               
                            </td>
                        </tr>
                    </template>
                    <template v-else>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $t('admin_users.no_records') }}.</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Alma Users -->
        <div v-show="displayTab == 1" class="overflow-x-auto my-8 bg-alma shadow-md rounded-sm text-deactivated">
            <table class="table-auto  min-w-full divide-y divide-gray-table rounded-sm">
                <thead class="bg-slsp-bg-lighter px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <tr class="text-left whitespace-nowrap">
                        <th class="py-4 px-4"> {{ $t('admin_users.user_identifier') }} </th>
                        <th class="py-4 px-4 text-left"> {{ $t('admin_users.permissions') }} </th>
                        <th class ="py-4 px-4 text-left"> {{ $t('admin_users.created_at') }} </th>
                        <th class="py-4 px-4 text-left"> {{ $t('admin_users.last_login') }} </th>
                        <th class="py-4 px-4 w-full"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-table whitespace-nowrap">
                    <template v-if="adminUsersAlma.data.length > 0">
                        <tr v-for="user in adminUsersAlma.data" :key="'user' + user.id" class="">
                            <td class="align-top">
                                <div class="flex px-6 py-3 ">
                                    {{ user.user_identifier }}
                                </div>
                            </td>
                            <td class="align-top">
                                <div v-if="user.is_slsp_admin"
                                    class="text-slsp font-bold flex px-6 py-3 ">
                                    {{ $t('admin_users.slsp_admin') }}
                                </div>
                                <div v-else class="flex flex-col px-6 py-3 gap-2">
                                    <div v-for="slskeyGroup in user.slskeyGroups" :key="slskeyGroup.id">
                                        {{ slskeyGroup.name }}
                                    </div>
                                </div>
                            </td>
                            <td class="align-top">
                                <div class="flex px-6 py-3 ">
                                    {{ user.created_at ? this.$moment(user.created_at).format('ll') : '-' }}
                                </div>
                            </td>
                            <td class="align-top">
                                <div class="flex px-6 py-3 ">
                                    {{ formatDate(user.last_login) }}
                                </div>
                            </td>
                            <td class="align-top">
                            </td>    
                        </tr>
                    </template>
                    <template v-else>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $t('admin_users.no_records') }}.</td>
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
import DefaultButton from '@/Shared/Buttons/DefaultButton.vue';
import { Inertia } from '@inertiajs/inertia';
import TextInput from '@/Shared/Forms/TextInput.vue';
import TabFilter from '@/Shared/Filters/TabFilter.vue';
import debounce from "lodash/debounce";
import SearchFilter from '@/Shared/Filters/SearchFilter.vue';
import omitBy from 'lodash/omitBy'
import FilterControl from '@/Shared/Filters/FilterControl.vue';

export default {
    components: {
        AppLayout,
        DefaultButton,
        SelectFilter,
        Inertia,
        TextInput,
        TabFilter,
        SearchFilter,
        FilterControl
    },
    props: {
        adminUsersPortal: Object,
        adminUsersAlma: Object,
        slskeyGroups: Object,
        filters: Object
    },
    data() {
        return {
            displayTab: 0,
            form: {
                search: this.filters.search,
                slskeyCode: this.filters.slskeyCode
            }
        }
    },
    methods: {
        reset() {
            this.form = {
                search: ''
            }
        },
        createUser() {
            Inertia.get("/admin/users/create");
        },
        navigateTo(user_identifier) {
            Inertia.get(`/admin/users/${user_identifier}`);
        },
        formatDate(date) {
            return date ? this.$moment(date).fromNow() : '-';
        },
    },
    watch: {
        form: {
            deep: true,
            handler: debounce(function (new_value, old_value) {
                Inertia.get('/admin/users', omitBy(this.form, _.overSome([_.isNil, _.isNaN])),
                    {
                        preserveState: true,
                        replace: true
                    }
                )
            }, 300)
        }
    }

}
</script>