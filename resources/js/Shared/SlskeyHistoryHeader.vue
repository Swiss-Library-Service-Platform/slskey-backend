
<template>
    <tr>
        <th v-if="showRelativeDate" class="py-4 px-6 pl-8 text-left whitespace-nowrap"> {{ $t('history.time') }} </th>
        <th class="py-4 px-4 text-left whitespace-nowrap"> {{ $t('history.date') }} </th>
        <th v-if="$page.props.slskeyadmin" class="py-4 px-6 text-left whitespace-nowrap"> </th>
        <th class="py-4 px-4 text-left whitespace-nowrap"> {{ $t('slskey_groups.slskey_code_description') }} </th>
        <th v-if="showPrimaryId" class="py-4 px-4 text-left whitespace-nowrap"> {{ $t('slskey_user.primary_id') }} </th>
        <th class="py-4 px-4 text-left whitespace-nowrap"> {{ $t('history.action') }} </th>
        <th class="py-4 px-4 text-left whitespace-nowrap"> {{ $t('history.trigger') }} </th>
        <th class="py-4 px-4 pr-8 text-left whitespace-nowrap"> {{ $t('history.author') }} </th>
        <th v-if="$page.props.slskeyadmin" class="py-4 px-6 text-left whitespace-nowrap"> {{ $t('history.message') }} </th>
    </tr>
</template>

<script>
import UserActionChip from '@/Shared/UserActionChip.vue'
import Icon from '@/Shared/Icon.vue'
import { Link } from '@inertiajs/inertia-vue3'
import moment from 'moment';
export default {
    components: {
        Icon,
        UserActionChip,
        Link
    },
    props: {
        showRelativeDate: Boolean,
        showPrimaryId: Boolean
    },
}
</script>