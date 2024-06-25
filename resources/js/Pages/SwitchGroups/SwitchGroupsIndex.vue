<template>
    <AppLayout :title="$t('switch_groups.title')" :breadCrumbs="[{ name: $t('switch_groups.title') }]">
        <div class="flex py-5 items-end justify-between flex-wrap">
            <FilterControl @reset="reset">
                <SelectFilter v-if="slskeyGroups.data.length > 1" v-model="form.slskeyCode"
                    :label="$t('slskey_groups.slskey_code_description')" :options="slskeyGroups.data" />
            </FilterControl>
            <DefaultButton @click="createGroup" icon="plus" class="w-fit bg-color-slsp text-white py-2 mt-5">
                {{ $t('switch_groups.create_new') }}
            </DefaultButton>
        </div>

        <div class="mt-5 overflow-x-auto mb-10 bg-white shadow-md rounded-md">
            <table class="table-fixed min-w-full divide-y divide-gray-table rounded-md">
                <thead>
                    <tr>
                        <th class="w-24 py-4 px-4 text-left whitespace-nowrap"> {{ $t('switch_groups.name') }} </th>
                        <!-- <th class="py-4 px-4 text-left whitespace-nowrap"> {{ $t('switch_groups.switch_group_id') }} </th> -->
                        <th class="py-4 px-4 text-left whitespace-nowrap"> {{ $t('switch_groups.slskey_groups') }} </th>
                        <th class="py-4 px-4 text-left whitespace-nowrap"> {{ $t('switch_groups.publishers_title') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-table">
                    <template v-if="switchGroups.data.length > 0">
                        <tr v-for="group in switchGroups.data" :key="'group' + group.id"
                            class="hover:bg-gray-100 focus-within:bg-gray-100">
                            <td class="align-top w-24 truncate">
                                <Link class="flex flex-col px-6 py-3 whitespace-nowrap"
                                    :href="`/admin/switchgroups/${group.id}`">
                                {{ group.name }}
                                </Link>
                            </td>
                            <td class="align-top">
                                <Link class="flex flex-col px-6 py-3 whitespace-nowrap gap-y-1"
                                    :href="`/admin/switchgroups/${group.id}`">
                                <div class="w-fit p-1 rounded-md bg-color-slsp-bg"
                                    v-for="slskeyGroup in group.slskeyGroups" :key="slskeyGroup.id">
                                    {{ slskeyGroup.slskey_code }}
                                </div>
                                </Link>
                            </td>
                            <td class="align-top">
                                <Link class="flex flex-col px-6 py-3 whitespace-nowrap gap-y-1"
                                    :href="`/admin/switchgroups/${group.id}`">
                                <div class="w-fit p-1 rounded-md bg-color-alma" v-for="publisher in group.publishers"
                                    :key="publisher.id">
                                    {{ publisher }}
                                </div>
                                </Link>
                            </td>
                            <!-- 
            <td class="align-top">
              <Link class="flex flex-col px-6 py-3 whitespace-nowrap gap-y-4" :href="`/admin/switchgroups/${group.id}`">
                {{ group.switch_group_id }}
              </Link>
            </td>
            -->
                        </tr>
                    </template>
                    <template v-else>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $t('switch_groups.no_records') }}.</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

    </AppLayout>
</template>


<script>
import throttle from "lodash/throttle"
import omitBy from 'lodash/omitBy'
import axios from 'axios';
import AppLayout from '@/Layouts/AppLayout.vue';
import SelectFilter from '@/Shared/Filters/SelectFilter.vue';
import { Inertia } from '@inertiajs/inertia';
import DefaultButton from '@/Shared/Buttons/DefaultButton.vue';
import FilterControl from '../../Shared/Filters/FilterControl.vue';
export default {
    components: {
        AppLayout,
        SelectFilter,
        Inertia,
        DefaultButton,
        FilterControl
    },
    props: {
        switchGroups: Object,
        slskeyGroups: Object,
        filters: Object,

    },
    data() {
        return {
            form: {
                slskeyCode: this.filters.slskeyCode,
            }
        }
    },
    methods: {
        createGroup() {
            Inertia.visit('/admin/switchgroups/create');
        },
        reset() {
            this.form = {
                slskeyCode: null,
            }
        },
    },
    watch: {
        form: {
            deep: true,
            handler: throttle(function (new_value, old_value) {
                Inertia.get('/admin/switchgroups', omitBy(this.form, _.overSome([_.isNil, _.isNaN])),
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