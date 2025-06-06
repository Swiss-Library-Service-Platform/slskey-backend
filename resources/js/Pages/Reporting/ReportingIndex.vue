<template>
    <AppLayout :title="$t('reporting.title')" :breadCrumbs="[{ name: $t('reporting.title') }]">
        <!-- <div class="flex flex-col w-fit min-w-3/4 overflow-x-auto">-->
            <div class="flex bg-white p-4 rounded-b shadow items-end justify-between flex-wrap">
                <div class="flex gap-x-16">
                    <FilterControl @reset="reset" v-if="$page.props.numberOfPermittedSlskeyGroups > 1">
                        <SelectFilter v-model="form.slskeyCode" :label="$t('slskey_groups.slskey_code_description')"
                            :options="slskeyGroups.data" />
                    </FilterControl>
                    <TabFilter :tab1="$t('reporting.display_tab1')" :tab2="$t('reporting.display_tab2')"
                        icon1="view-list" icon2="chart-square-bar" :label="$t('reporting.display')"
                        v-model="displayTab" />
                </div>
                <div class="flex gap-x-4">
                    <DefaultButton :disabled="!form.slskeyCode" icon="mail" @click="changeSettings()"
                        class="w-fit py-2 mt-4">
                        {{ $t('reporting.change_settings') }}
                    </DefaultButton>
                    <DefaultButton icon="documentDownload" :loading="export_loading" @click.prevent="this.export"
                        class="w-fit py-2 mt-4">
                        {{ $t('reporting.export') }}
                    </DefaultButton>
                </div>
            </div>

            <div v-show="displayTab == 0" class="my-8 overflow-x-auto bg-white shadow-md rounded-sm">
                <table class="table-auto min-w-full divide-y divide-gray-table rounded-sm">
                    <thead class="bg-color-slsp-bg-lighter px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <tr>
                            <th
                                class="py-4 px-10 text-center whitespace-nowrap cursor-pointer border-r border-gray-table">
                                {{ $t('reporting.month') }}
                            </th>
                            <th colspan="3"
                                class="pt-4 pb-2 text-center whitespace-nowrap cursor-pointer border-r border-gray-table">
                                {{ $t('reporting.activations') }}
                            </th>
                            <th colspan="2"
                                class="pt-4 pb-2 text-center whitespace-nowrap cursor-pointer border-r border-gray-table">
                                {{ $t('reporting.deactivations') }}
                            </th>
                            <th class="py-4 px-10 text-center whitespace-nowrap border-r border-gray-table">
                                {{ $t('reporting.monthly_change') }}
                            </th>
                            <th colspan="2" class="py-4 px-10 text-center whitespace-nowrap">
                                {{ $t('reporting.total_activations') }}
                            </th>
                        </tr>
                        <tr class="normal-case">
                            <th
                                class="pt-2 pb-4 px-6 text-center whitespace-nowrap cursor-pointer border-r border-gray-table">
                            </th>

                            <th class="pt-2 pb-4 px-6 pl-10 text-center font-normal italic whitespace-nowrap ">
                                {{ $t('reporting.new_users') }}
                            </th>
                            <th class="pt-2 pb-4 px-6 text-center font-normal italic whitespace-nowrap ">
                                {{ $t('reporting.reactivations') }}
                            </th>
                            <th
                                class="pt-2 pb-4 px-6 pr-10 text-center font-normal italic whitespace-nowrap  border-r border-gray-table">
                                {{ $t('reporting.extensions') }}
                            </th>

                            <th class="pt-2 pb-4 px-6 pl-10 text-center font-normal italic whitespace-nowrap ">
                                {{ $t('reporting.deactivations') }}
                            </th>
                            <th
                                class="pt-2 pb-4 px-6 text-center font-normal italic whitespace-nowrap  border-r border-gray-table">
                                {{ $t('reporting.blocks') }}
                            </th>
                            <th class="pt-2 pb-4 px-6 pr-10 text-center whitespace-nowrap border-r border-gray-table">
                            </th>
                            <th v-if="isAnyEducationalUsers"
                                class="pt-2 pb-4 px-6 pl-10 text-center font-normal italic whitespace-nowrap ">
                                {{ $t('reporting.total_active_users') }}
                            </th>
                            <th v-if="isAnyEducationalUsers"
                                class="pt-2 pb-4 px-6 text-center font-normal italic whitespace-nowrap ">
                                {{ $t('reporting.total_active_educational_users') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-table">
                        <template v-if="reportCounts.length > 0">
                            <tr v-for="(reportCount, index) in reportCounts" :key="'user' + reportCount.id"
                                class=" focus-within:bg-gray-100" :class="{ 'bg-color-lightgreen': index == 0 }">

                                <td class="px-6 py-3 text-left whitespace-nowrap gap-y-4 border-r border-gray-table">
                                    {{ formatMonth(reportCount.month, reportCount.year) }}
                                </td>
                                <td class="px-6 py-3 text-center whitespace-nowrap gap-y-4">
                                    {{ reportCount.activated_count }}
                                </td>
                                <td class="px-6 py-3 text-center whitespace-nowrap gap-y-4 ">
                                    {{ reportCount.reactivated_count }}
                                </td>
                                <td class="px-6 py-3 text-center whitespace-nowrap gap-y-4 border-r border-gray-table">
                                    {{ reportCount.extended_count }}
                                </td>
                                <td class="px-6 py-3 text-center whitespace-nowrap gap-y-4">
                                    {{ reportCount.deactivated_count }}
                                </td>
                                <td class="px-6 py-3 text-center whitespace-nowrap gap-y-4 border-r border-gray-table">
                                    {{ reportCount.blocked_active_count }}
                                </td>
                                <td
                                    class="px-6 py-3 text-center whitespace-nowrap gap-y-4 font-semibold border-r border-gray-table">
                                    <div v-if="reportCount.monthly_change_count < 0">
                                        <span class="text-red-500">
                                            -
                                        </span>
                                        {{ Math.abs(reportCount.monthly_change_count) }}
                                    </div>
                                    <div v-if="reportCount.monthly_change_count == 0">
                                        -
                                    </div>
                                    <div v-if="reportCount.monthly_change_count > 0">
                                        <span class="text-green-500">
                                            +
                                        </span>
                                        {{ reportCount.monthly_change_count }}
                                    </div>
                                </td>
                                <td class="px-6 py-3 text-center whitespace-nowrap gap-y-4 font-semibold">
                                    {{ reportCount.total_active_users }}
                                </td>
                                <td v-if="isAnyEducationalUsers"
                                    class="px-6 py-3 text-center whitespace-nowrap gap-y-4 font-semibold">
                                    {{ reportCount.total_active_educational_users }}
                                </td>
                            </tr>
                        </template>
                        <template v-else>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"> No reporting data found.</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div v-show="displayTab == 1" class="my-8 p-5 overflow-x-auto bg-white shadow-md rounded-sm">
                <canvas id="userChart"></canvas>
            </div>
        <!--</div>-->
    </AppLayout>
</template>


<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import FilterControl from '../../Shared/Filters/FilterControl.vue';
import SelectFilter from '@/Shared/Filters/SelectFilter.vue';
import TabFilter from '@/Shared/Filters/TabFilter.vue';
import { Inertia } from '@inertiajs/inertia';
import throttle from "lodash/throttle";
import omitBy from 'lodash/omitBy';
import DefaultButton from '@/Shared/Buttons/DefaultButton.vue';
import { Chart, LineController, LinearScale, CategoryScale, PointElement, LineElement } from 'chart.js';
import colors from 'tailwindcss/colors'

export default {
    components: {
        AppLayout,
        FilterControl,
        SelectFilter,
        Inertia,
        DefaultButton,
        TabFilter,
        Chart,
        LinearScale,
        LineController,
        CategoryScale,
        PointElement,
        LineElement,
        colors
    },
    props: {
        reportCounts: Object,
        slskeyGroups: Object,
        selectedSlskeyCode: String,
        isAnyEducationalUsers: Boolean,
    },
    data() {
        return {
            chart: null,
            displayTab: 0,
            export_loading: false,
            form: {
                slskeyCode: this.slskeyGroups.data.length === 1 ? this.slskeyGroups.data[0].value :
                    this.selectedSlskeyCode,
            }
        }
    },
    methods: {
        formatMonth(month, year) {
            var inputDate = this.$moment(month + '-' + year, 'MM-YYYY');
            var formattedDate = inputDate.format('MMMM YYYY');
            return formattedDate
        },
        reset() {
            this.form = {
                slskeyCode: null,
            }
        },
        changeSettings() {
            if (this.form.slskeyCode) {
                Inertia.get("/reporting/" + this.form.slskeyCode);
            }
        },
        async export() {
            this.export_loading = true;
            axios({
                url: 'reporting/export',
                method: 'GET',
                responseType: 'blob', // important
                params: {
                    slskeyCode: this.form.slskeyCode,
                }
            }).then((response) => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                console.log(response)
                link.setAttribute('download', 'reporting.xlsx');
                document.body.appendChild(link);
                link.click();
                this.export_loading = false;
            }).catch((error) => {
                console.log(error);
                this.export_loading = false;
            });
        },
        initGraph() {
            Chart.register(LineController, LinearScale, CategoryScale, PointElement, LineElement);
            const ctx = document.getElementById('userChart').getContext('2d');
            if (this.chart) {
                this.chart.destroy();
            }

            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: this.reportCounts.map(history => this.formatMonth(history.month, history.year)).reverse(),
                    datasets: [{
                        label: 'Number of users',
                        data: this.reportCounts.map(history => history.total_active_users).reverse(),
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: '#4e4a99',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    },
    watch: {
        form: {
            deep: true,
            handler: throttle(function (new_value, old_value) {
                Inertia.get('/reporting', omitBy(this.form, _.overSome([_.isNil, _.isNaN])),
                    {
                        preserveState: true,
                        replace: true,
                        onFinish: () => {
                            this.initGraph();
                        }
                    }
                );
            }, 500)

        }
    },
    mounted() {
        this.initGraph();
    }


}
</script>