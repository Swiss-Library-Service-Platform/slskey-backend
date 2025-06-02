<template>
    <AppLayout title="Users" :breadCrumbs="[{ name: $t('user_management.title') }]">
        <div class="flex bg-white p-4 rounded-b shadow items-end justify-between flex-wrap">
            <FilterControl @reset="reset">
                <SearchFilter v-model="form.search" :label="$t('user_management.search')"
                    :placeholder="$t('user_management.search_placeholder')" />
                <SelectFilter v-if="$page.props.numberOfPermittedSlskeyGroups > 1" v-model="form.slskeyCode"
                    :label="$t('slskey_groups.slskey_code_description')" :options="slskeyGroups.data" />
                <SelectFilter v-model="form.status" :label="$t('user_management.status')" :options="getStatusOptions"
                    :placeholder="$t('user_management.status')" />
                <DatePickerFilter
                    :label="$t('user_management.activation') + ' ' + $t('user_management.activation_start')"
                    v-model="form.activation_start" />
                <DatePickerFilter :label="$t('user_management.activation') + ' ' + $t('user_management.activation_end')"
                    v-model="form.activation_end" />
            </FilterControl>
            <DefaultButton icon="documentDownload" :loading="export_loading" @click.prevent="this.export"
                class="!w-fit py-2 ml-2 mt-4">
                {{ $t('user_management.export') }}
            </DefaultButton>
        </div>

        <div class="mt-8 overflow-x-auto bg-white shadow-md rounded-sm">
            <table class="table-auto min-w-full divide-y divide-gray-table rounded-sm">
                <thead class="bg-color-slsp-bg-lighter px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th @click="sort('full_name')" class="py-4 px-6 text-left whitespace-nowrap cursor-pointer">
                            <div class="flex flex-row items-center gap-x-2">
                                <MaterialIcon v-if="form.sortBy === 'full_name' && !form.sortAsc"
                                    icon="sort_ascending" />
                                <MaterialIcon v-if="form.sortBy === 'full_name' && form.sortAsc"
                                    icon="sort_descending" />
                                <MaterialIcon v-if="form.sortBy !== 'full_name'" icon="sort" />
                                {{ $t('alma_user.full_name') }}
                            </div>
                        </th>
                        <!-- SLSKey Group Description -->
                        <th v-if="slskeyGroups.data.length > 1" class="py-4 px-6 text-left whitespace-nowrap"> {{
                            $t('slskey_groups.slskey_code_description') }}
                        </th>

                        <!-- Activation Date -->

                        <th @click="sort('activation_date')"
                            class="py-4 px-6 text-left whitespace-nowrap cursor-pointer">
                            <div class="flex flex-row items-center gap-x-2">
                                <MaterialIcon v-if="form.sortBy === 'activation_date' && !form.sortAsc"
                                    icon="sort_ascending" />
                                <MaterialIcon v-if="form.sortBy === 'activation_date' && form.sortAsc"
                                    icon="sort_descending" />
                                <MaterialIcon v-if="form.sortBy !== 'activation_date'" icon="sort" />
                                {{ $t('user_management.activation_date') }}
                            </div>
                        </th>

                        <!-- Expiration Date -->
                        <th @click="sort('expiration_date')"
                            class="py-4 px-6 text-left whitespace-nowrap cursor-pointer">
                            <div class="flex flex-row items-center gap-x-2">
                                <MaterialIcon v-if="form.sortBy === 'expiration_date' && !form.sortAsc"
                                    icon="sort_ascending" />
                                <MaterialIcon v-if="form.sortBy === 'expiration_date' && form.sortAsc"
                                    icon="sort_descending" />
                                <MaterialIcon v-if="form.sortBy !== 'expiration_date'" icon="sort" />
                                {{ $t('user_management.expiration_date') }}
                            </div>
                        </th>
                        <!-- Status -->
                        <th class="py-4 px-6 text-left whitespace-nowrap"> {{ $t('user_management.status') }} </th>
                    </tr>
                </thead>
                <tbody class="">
                    <template v-if="slskeyUsers.data.length > 0">
                        <template v-for="user, userIndex in slskeyUsers.data" :key="'user' + user.id">

                            <tr @click="navigateTo(user.primary_id)" class="focus-within:bg-gray-100 cursor-pointer"
                                v-for="activation, index in user.slskey_activations" :key="activation.id" :class="{
                                    'border-b border-b-gray-table': index === user.slskey_activations.length - 1,
                                    'bg-gray-100': isHighlighted(userIndex)
                                }" @mouseover="highlightRow(userIndex)" @mouseleave="unhighlightRow(userIndex)">

                                <!-- Primary ID
                                <td class="px-6 py-3" v-if="index == 0">
                                    {{ user.primary_id }}
                                </td>
                                <td class="px-6 py-3" v-else></td>
                                -->

                                <!-- Full Name -->
                                <td v-if="index == 0" class="px-6"
                                    :class="getVerticalPadding(user.slskey_activations.length, index)">
                                    {{ user.full_name }}
                                </td>
                                <td class="px-6 py-3" v-else></td>

                                <!-- SLSKey Group Description -->
                                <td v-if="slskeyGroups.data.length > 1" class="px-6"
                                    :class="getVerticalPadding(user.slskey_activations.length, index)">
                                    <div class="flex flex-row">
                                        <SlskeyGroupNameAndIcon :workflow="activation.slskey_group.workflow"
                                            :slskeyGroupName="activation.slskey_group.name" />
                                    </div>
                                </td>

                                <!-- Activation Date -->
                                <td class="px-6" :class="getVerticalPadding(user.slskey_activations.length, index)">
                                    <div v-if="activation.activation_date" class="flex flex-row items-center">
                                        <Icon icon="key" class="h-4 w-4 mr-2"></Icon>
                                        {{ formatDate(activation.activation_date) }}
                                    </div>
                                </td>

                                <!-- Expiration Date -->
                                <td class="px-6" :class="getVerticalPadding(user.slskey_activations.length, index)">
                                    <div v-if="activation.activated && activation.expiration_disabled"
                                        class="italic text-gray-disabled flex flex-row items-center">
                                        <Icon icon="clock" class="h-4 w-4 mr-2"></Icon>
                                        {{ $t("user_management.no_expiry_deactivated") }}
                                    </div>
                                    <div v-if="activation.activated && !activation.expiration_date && !activation.expiration_disabled"
                                        class="italic text-gray-disabled flex flex-row items-center">
                                        <Icon icon="clock" class="h-4 w-4 mr-2"></Icon>
                                        {{ $t("user_management.no_expiry_webhook") }}
                                    </div>
                                    <div v-else-if="activation.expiration_date && !activation.expiration_disabled"
                                        class="flex flex-row items-center">
                                        <Icon icon="clock" class="h-4 w-4 mr-2"></Icon>
                                        {{ formatDate(activation.expiration_date) }}
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-6" :class="getVerticalPadding(user.slskey_activations.length, index)">
                                    <UserStatusChip :activation="activation" />
                                </td>

                                <!--
                                    <td class="align-top">
                                        <Link class="flex flex-col px-6 py-3 whitespace-nowrap" :href="`/users/${user.primary_id}`">
                                            <div v-for="activation in user.activations" :key="activation.id">
                                            {{ activation.expiration_date }}
                                        </div>
                                        </Link>
                                    </td>
                                    <td class="align-top">
                                        <Link class="flex flex-col px-6 py-3 whitespace-nowrap gap-2" :href="`/users/${user.primary_id}`">
                                        <template v-for="activation in user.activations" :key="activation.id">
                                            <UserStatusChip :activation="activation"/>
                                        </template>
</Link>
</td>
-->
                            </tr>
                        </template>
                    </template>
                    <template v-else>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $t('user_management.no_records') }}.</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div class="my-8">
            <Pagination :pages="slskeyUsers.meta" v-model="form.perPage" />
        </div>
    </AppLayout>
</template>


<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Shared/Pagination.vue';
import { Inertia } from '@inertiajs/inertia';
import throttle from "lodash/throttle";
import debounce from "lodash/debounce";
import omitBy from 'lodash/omitBy'
import axios from 'axios';
import Datepicker from '@vuepic/vue-datepicker';
import SearchFilter from '@/Shared/Filters/SearchFilter.vue';
import SelectFilter from '@/Shared/Filters/SelectFilter.vue';
import DatePickerFilter from '@/Shared/Filters/DatePickerFilter.vue';
import moment from 'moment';
import BreadCrumb from '@/Shared/BreadCrumb.vue';
import FilterControl from '../../Shared/Filters/FilterControl.vue';
import BoolFilter from '../../Shared/Filters/BoolFilter.vue';
import BoolBothFilter from '../../Shared/Filters/BoolBothFilter.vue';
import UserStatusChip from '@/Shared/UserStatusChip.vue'
import Icon from '@/Shared/Icon.vue';
import DefaultButton from '@/Shared/Buttons/DefaultButton.vue';
import LetterIcon from '../../Shared/LetterIcon.vue';
import SlskeyGroupNameAndIcon from '../../Shared/SlskeyGroupNameAndIcon.vue';
import MaterialIcon from '../../Shared/MaterialIcon.vue';

export default {
    components: {
        AppLayout,
        Pagination,
        Datepicker,
        SearchFilter,
        DatePickerFilter,
        BreadCrumb,
        FilterControl,
        BoolFilter,
        BoolBothFilter,
        UserStatusChip,
        SelectFilter,
        Icon,
        DefaultButton,
        LetterIcon,
        SlskeyGroupNameAndIcon,
        MaterialIcon
    },
    props: {
        perPage: Number,
        slskeyUsers: Object,
        filters: Object,
        slskeyGroups: Object
    },
    data() {
        return {
            hoveredRow: null,
            export_loading: false,
            form: {
                perPage: this.perPage,
                search: this.filters.search,
                sort_by: this.filters.sort_by,
                sort_desc: this.filters.sort_desc,
                slskeyCode: this.filters.slskeyCode,
                status: this.filters.status,
                activation_start: this.filters.activation_start,
                activation_end: this.filters.activation_end,
            }
        }
    },
    methods: {
        reset() {
            this.form = {
                perPage: this.perPage,
                sortBy: null,
                sortAsc: null,
                search: null,
                slskeyCode: null,
                status: null,
                activation_start: null,
                activation_end: null,
            }
        },
        isHighlighted(index) {
            return this.hoveredRow === index;
        },
        highlightRow(index) {
            this.hoveredRow = index;
        },
        unhighlightRow(index) {
            this.hoveredRow = null;
        },
        navigateTo(primary_id) {
            this.$inertia.visit(`/users/${primary_id}`);
        },
        sort(field) {
            if (this.form.sortBy === field) {
                this.form.sortAsc = !this.form.sortAsc;
            } else {
                this.form.sortBy = field;
                this.form.sortAsc = false;
            }
            // Trigger the watch function to make the API call with sorting options
            this.$nextTick(() => this.$forceUpdate());
        },
        async export() {
            this.export_loading = true;
            axios({
                url: 'users/export',
                method: 'GET',
                responseType: 'blob', // important
                params: {
                    perPage: this.perPage,
                    sortBy: this.filters.sortBy,
                    sortAsc: this.filters.sortAsc,
                    search: this.filters.search,
                    slskeyCode: this.filters.slskeyCode,
                    status: this.filters.status,
                    activation_start: this.filters.activation_start,
                    activation_end: this.filters.activation_end,
                },
            }).then((response) => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'slskey_users.xlsx');
                document.body.appendChild(link);
                link.click();
                this.export_loading = false;
            }).catch((error) => {
                console.log(error);
                this.export_loading = false;
            });
        },
        formatDate(date) {
            return this.$moment(date).format('ll')
        },
        getVerticalPadding(activationsCount, index) {
            return activationsCount == 1 ? 'py-3' :
                index == 0 ? 'pt-3 pb-1' : index == activationsCount - 1 ? 'pt-1 pb-3' : 'py-1';
        }
    },
    computed: {
        getStatusOptions() {
            return [
                {
                    'name': 'Activated',
                    'value': 'ACTIVE'
                },
                {
                    'name': 'Inactive',
                    'value': 'DEACTIVATED'
                },
                {
                    'name': 'Blocked',
                    'value': 'BLOCKED'
                }
            ];
        },

    },
    watch: {
        form: {
            deep: true,
            handler: debounce(function (new_value, old_value) {
                Inertia.get('/users', omitBy(this.form, _.overSome([_.isNil, _.isNaN])),
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