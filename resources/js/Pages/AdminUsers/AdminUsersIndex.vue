
<template>
    <AppLayout title="Users" :breadCrumbs="[{ name: $t('admin_users.title') }]">

        <DefaultButton icon="plus" @click="createUser" class="w-fit mt-5 bg-color-slsp text-white py-2 ">
            {{ $t('admin_users.create_new') }}
        </DefaultButton>

        <div class="mt-5 mb-10 bg-white shadow-md rounded-md">
            <table class="table-auto  min-w-full divide-y divide-gray-table rounded-md">
                <thead class="">
                    <tr>
                        <th class="py-4 px-4 text-left whitespace-nowrap"> {{ $t('admin_users.user_identifier') }} </th>
                        <th class="py-4 px-4 text-left whitespace-nowrap"> {{ $t('admin_users.display_name') }} </th>
                        <th class="py-4 px-4 text-left whitespace-nowrap"> {{ $t('admin_users.permissions') }} </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-table">
                    <template v-if="adminUsers.data.length > 0">
                        <tr v-for="user in adminUsers.data" :key="'user' + user.id"
                            @click="navigateTo(user.user_identifier)"
                            class="focus-within:bg-gray-100 cursor-pointer hover:bg-gray-100">
                            <td class="align-top">
                                <div class="flex px-6 py-3 whitespace-nowrap">
                                    {{ user.user_identifier }}
                                </div>
                            </td>
                            <td class="align-top">
                                <div class="flex px-6 py-3 whitespace-nowrap">
                                    {{ user.display_name }}
                                </div>
                            </td>
                            <td class="align-top">
                                <div v-if="user.is_slsp_admin" class="text-color-slsp font-bold flex px-6 py-3 whitespace-nowrap">
                                    {{ $t('admin_users.slsp_admin') }}
                                </div>
                                <div v-else class="flex flex-col px-6 py-3 gap-2">
                                    <div v-for="slskeyGroup in user.slskeyGroups" :key="slskeyGroup.id">
                                        {{ slskeyGroup.name }}
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template v-else>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $t('no_records') }}.</td>
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
import TextInput from '../../Shared/Forms/TextInput.vue';

export default {
    components: {
        AppLayout,
        DefaultButton,
        SelectFilter,
        Inertia,
        TextInput
    },
    props: {
        adminUsers: Object,
        slskeyGroups: Object
    },
    data() {
        return {
            export_loading: false,
            selectedUser: null,
            selectedGroup: null
        }
    },
    methods: {
        reset() {
            this.form = {

            }
        },
        createUser() {
            Inertia.get("/admin/users/create");
        },
        navigateTo(user_identifier) {
            Inertia.get(`/admin/users/${user_identifier}`);
        }
    },
    computed: {

    },

}
</script>