<template>
    <div class="rounded-sm rounded-md overflow-x-auto">
        <table class="table-auto min-w-full divide-y divide-gray-table rounded-sm">
            <thead class="bg-slsp-bg-lighter px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
               <SlskeyHistoryHeader :showRelativeDate="true" />
            </thead>
            <tbody class="divide-y divide-gray-table">
                <template v-if="slskeyHistories.length > 0">

                    <tr v-for="history in slskeyHistories" :key="'user' + history.id">
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
import UserActionChip from '@/Shared/UserActionChip.vue'
import Icon from '@/Shared/Icon.vue'
import SlskeyHistoryRow from '../../../../Shared/SlskeyHistoryRow.vue';
import SlskeyHistoryHeader from '../../../../Shared/SlskeyHistoryHeader.vue';
export default {
    components: {
        Icon,
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