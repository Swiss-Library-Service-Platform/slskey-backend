<template>
    <AppLayout :title="$t('log_jobs.title')" :breadCrumbs="[{ name: $t('log_jobs.title') }]">
        <div class="flex bg-white p-4 rounded-b shadow items-end justify-between flex-wrap">
            <FilterControl @reset="reset">
                <SelectFilter v-model="form.job" :label="$t('log_jobs.job')" :options="jobOptions" />
                <BoolBothFilter v-model="form.has_fail" :label="$t('log_jobs.has_fail')" />
            </FilterControl>
        </div>

        <div class="my-8 overflow-x-auto bg-white shadow-md rounded-sm">
            <table class="table-auto  min-w-full divide-y divide-gray-table rounded-sm">
                <thead class="">
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ $t('log_jobs.time') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ $t('log_jobs.job') }}
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        {{ $t('log_jobs.info') }}
                    </th>
                </thead>
                <tbody class="divide-y divide-gray-table">
                    <template v-if="logs.data.length > 0">
                        <tr v-for="log in logs.data" :key="'log' + log.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ formatDate(log.logged_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <template v-if="log.has_fail">
                                    <Icon icon="x-circle" class="w-5 h-5 text-color-blocked" />
                                </template>
                                <template v-else>
                                    <Icon icon="check-circle" class="w-5 h-5 text-color-active" />
                                </template>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ log.job }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <!-- info is a either a json object, or a list -->
                                <template v-if="Array.isArray(log.info)">
                                    <template v-for="info in log.info">
                                        <template v-for="attr in Object.keys(info)">
                                            <div class="flex">
                                                <div class="font-semibold">{{ attr }}:</div>
                                                <div class="ml-2">{{ info[attr] }}</div>
                                            </div>
                                        </template>
                                    </template>
                                </template>
                                <template v-else v-for="attr in Object.keys(log.info)">
                                    <div class="flex">
                                        <div class="font-semibold">{{ attr }}:</div>
                                        <div class="ml-2">{{ log.info[attr] }}</div>
                                    </div>
                                </template>

                            </td>
                        </tr>
                    </template>
                    <template v-else>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $t('log_jobs.no_logs') }}.</td>
                        </tr>
                    </template>
                </tbody>
            </table>

        </div>
        <div class="mb-8">
            <Pagination :pages="logs" v-model="form.perPage" />
        </div>
    </AppLayout>
</template>


<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import SelectFilter from '@/Shared/Filters/SelectFilter.vue';
import { Inertia } from '@inertiajs/inertia';
import Pagination from '@/Shared/Pagination.vue';
import Icon from '@/Shared/Icon.vue';
import FilterControl from '../../Shared/Filters/FilterControl.vue';
import throttle from 'lodash/throttle';
import omitBy from 'lodash/omitBy';
import BoolBothFilter from '../../Shared/Filters/BoolBothFilter.vue';

export default {
    components: {
        AppLayout,
        Icon,
        SelectFilter,
        Inertia,
        Pagination,
        FilterControl,
        BoolBothFilter
    },
    props: {
        logs: Object,
        filters: Object,
        jobOptions: Object
    },
    data() {
        return {
            form: {
                perPage: this.perPage,
                job: this.filters.job,
                has_fail: this.filters.has_fail
            }
        };
    },
    methods: {
        reset() {
            this.form = {
                perPage: this.perPage,
            }
        },
        formatDate(date) {
            // format moment date with date and time
            return this.$moment(date).format('lll');
        },
    },
    watch: {
        form: {
            deep: true,
            handler: throttle(function (new_value, old_value) {
                Inertia.get(route('admin.logjob.index'), omitBy(this.form, _.overSome([_.isNil, _.isNaN])),
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