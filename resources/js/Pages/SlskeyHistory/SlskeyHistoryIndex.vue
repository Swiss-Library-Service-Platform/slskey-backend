
<template>
    <AppLayout :title="$t('history.title')" :breadCrumbs="[{ name: $t('history.title') }]">
        <div class="flex bg-white p-4 rounded-b shadow items-end justify-between flex-wrap">
            <FilterControl @reset="reset">
                <SearchFilter v-model="form.primaryId" :label="$t('slskey_user.primary_id')" :placeholder="$t('history.search')" />
                <DatePickerFilter :label="$t('history.date')" v-model="form.date" />
                <SelectFilter v-model="form.slskeyCode" :label="$t('slskey_groups.slskey_code_description')"
                    :options="slskeyGroups.data" />
                <SelectFilter v-model="form.trigger" :label="$t('history.trigger')" :options="triggers" />
            </FilterControl>
        </div>

        <div class="my-8 overflow-x-auto bg-white shadow-md rounded-md">
            <table class="table-auto  min-w-full divide-y divide-gray-table rounded-md">
                <thead class="">
                    <SlskeyHistoryHeader :showPrimaryId="true" />
                </thead>
                <tbody class="divide-y divide-gray-table">
                    <template v-if="slskeyHistories.data.length > 0">
                        <tr v-for="history in slskeyHistories.data" :key="'user' + history.id"
                            class="hover:bg-gray-100 focus-within:bg-gray-100">
                            <SlskeyHistoryRow :showPrimaryId="true" :history="history" />
                        </tr>
                    </template>
                    <template v-else>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $t('history.no_history') }}.</td>
                        </tr>
                    </template>
                </tbody>
            </table>

        </div>
        <div class="my-8">
            <Pagination :pages="slskeyHistories" v-model="form.perPage" />
        </div>
    </AppLayout>
</template>


<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import SelectFilter from '@/Shared/Filters/SelectFilter.vue';
import DefaultButton from '@/Shared/Buttons/DefaultButton.vue';
import { Inertia } from '@inertiajs/inertia';
import TextInput from '../../Shared/Forms/TextInput.vue';
import Pagination from '@/Shared/Pagination.vue';
import Icon from '@/Shared/Icon.vue';
import { createPopper } from '@popperjs/core';
import UserActionChip from '@/Shared/UserActionChip.vue';
import SlskeyHistoryRow from '../../Shared/SlskeyHistoryRow.vue';
import SlskeyHistoryHeader from '../../Shared/SlskeyHistoryHeader.vue';
import FilterControl from '../../Shared/Filters/FilterControl.vue';
import SearchFilter from '@/Shared/Filters/SearchFilter.vue';
import DatePickerFilter from '@/Shared/Filters/DatePickerFilter.vue';
import throttle from 'lodash/throttle';
import omitBy from 'lodash/omitBy'
import axios from 'axios';

export default {
    components: {
        AppLayout,
        DatePickerFilter,
        DefaultButton,
        SelectFilter,
        Inertia,
        TextInput,
        Pagination,
        Icon,
        UserActionChip,
        SlskeyHistoryRow,
        SlskeyHistoryHeader,
        FilterControl,
        SearchFilter
    },
    props: {
        slskeyHistories: Object,
        filters: Object,
        slskeyGroups: Object,
        triggers: Object
    },
    data() {
        return {
            tooltipShow: false,
            form: {
                perPage: this.perPage,
                primaryId: this.filters.primaryId,
                date: this.filters.date,
                slskeyCode: this.filters.slskeyCode,
                trigger: this.filters.trigger
            }
        };
    },
    methods: {
        reset() {
            this.form = {
                perPage: this.perPage,
                primaryId: null,
                date: null,
                slskeyCode: null,
                trigger: null
            }
        },
        toggleTooltip: function () {
            if (this.tooltipShow) {
                this.tooltipShow = false;
            } else {
                this.tooltipShow = true;
                createPopper(this.$refs.btnRef, this.$refs.tooltipRef, {
                    placement: "right"
                });
            }
        }
    },
    watch: {
        form: {
            deep: true,
            handler: throttle(function (new_value, old_value) {
                Inertia.get('/admin/history', omitBy(this.form, _.overSome([_.isNil, _.isNaN])),
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