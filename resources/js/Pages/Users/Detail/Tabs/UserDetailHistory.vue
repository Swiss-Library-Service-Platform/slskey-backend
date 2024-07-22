<template>
    <div class="rounded-md rounded-lg overflow-x-auto">
        <table class="table-auto min-w-full divide-y divide-gray-table rounded-md">
            <thead class="">
               <SlskeyHistoryHeader :showRelativeDate="true" />
            </thead>
            <tbody class="divide-y divide-gray-table">
                <template v-if="slskeyHistories.length > 0">

                    <tr v-for="history in slskeyHistories" :key="'user' + history.id"
                        class=""
                        :class="{ 'italic text-gray-table': !history.success }">
                        <SlskeyHistoryRow :showRelativeDate="true" :history="history" />
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
</template>

<script>

import UserStatusChip from '@/Shared/UserStatusChip.vue'
import { Inertia } from '@inertiajs/inertia';
import UserActionChip from '@/Shared/UserActionChip.vue'
import Icon from '@/Shared/Icon.vue'
import SlskeyHistoryRow from '../../../../Shared/SlskeyHistoryRow.vue';
import SlskeyHistoryHeader from '../../../../Shared/SlskeyHistoryHeader.vue';
export default {
    components: {
        Icon,
        Inertia,
        UserStatusChip,
        UserActionChip,
        SlskeyHistoryRow,
        SlskeyHistoryHeader
    },
    props: {
        slskeyHistories: Array,
    },
    data() {
        return {
            loading: false
        }
    },
    methods: {
        formatDateToString(date) {
            return this.$moment(date).fromNow();
        },
        formatDate(date) {
            return this.$moment(date).format('ll');
        },
    }

}
</script>