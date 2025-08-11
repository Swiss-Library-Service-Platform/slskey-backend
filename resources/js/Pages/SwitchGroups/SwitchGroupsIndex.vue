<template>
    <AppLayout :title="$t('switch_groups.title')" :breadCrumbs="[{ name: $t('switch_groups.title') }]">

        <div class="flex bg-white p-4 rounded-b shadow items-end justify-between flex-wrap">
            <div class="flex flex-row gap-x-16 w-full justify-between">
                <FilterControl @reset="reset">
                    <SelectFilter v-model="form.slskeyCode" :label="$t('slskey_groups.slskey_code_description')"
                        :options="slskeyGroups.data" />
                    <SearchFilter v-model="form.publisher" :label="$t('switch_groups.publishers_title')"
                        :placeholder="$t('switch_groups.publishers_title')" />
                </FilterControl>
                <DefaultButton icon="documentDownload" @click.prevent="this.export" class="w-fit py-2 mt-4 whitespace-nowrap">
                    {{ $t('switch_groups.export') }}
                </DefaultButton>
            </div>
            <div class="flex gap-x-4">
                <DefaultButton @click="createGroup" icon="plus" class="w-fit py-2 mt-4">
                    {{ $t('switch_groups.create_new') }}
                </DefaultButton>
            </div>
        </div>

        <div class="my-8 overflow-x-auto mb-10 bg-white shadow-md rounded-sm">
            <table class="table-fixed min-w-full divide-y divide-gray-table rounded-sm">
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
                                <div class="w-fit p-1 rounded-sm bg-slsp-bg"
                                    v-for="slskeyGroup in group.slskeyGroups" :key="slskeyGroup.id">
                                    {{ slskeyGroup.slskey_code }}
                                </div>
                                </Link>
                            </td>
                            <td class="align-top">
                                <Link class="flex flex-row px-6 py-3 whitespace-nowrap gap-x-1"
                                    :href="`/admin/switchgroups/${group.id}`">
                                <div class="w-fit p-1 rounded-sm bg-alma" v-for="publisher in group.publishers"
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
import SearchFilter from '@/Shared/Filters/SearchFilter.vue';

export default {
    components: {
        AppLayout,
        SelectFilter,
        Inertia,
        DefaultButton,
        FilterControl,
        SearchFilter
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
                publisher: this.filters.publisher,
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
        async export() {
            try {
                this.export_loading = true;
                const response = await axios({
                    url: '/admin/switchgroups/publishers/download',
                    method: 'GET',
                    responseType: 'blob'
                });

                // Create blob link to download
                const blob = new Blob([response.data], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', `publishers_${new Date().toISOString().split('T')[0]}.csv`);

                // Append to html link element page
                document.body.appendChild(link);

                // Start download
                link.click();

                // Clean up and remove the link
                link.parentNode.removeChild(link);
                window.URL.revokeObjectURL(url);
            } catch (error) {
                console.error('Download failed:', error);
                // You might want to add proper error handling here
            } finally {
                this.export_loading = false;
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