
<template>
    <td v-if="showRelativeDate" class="align-top">
        <div class="flex px-4 pl-8 py-3 whitespace-nowrap">
            {{ formatRelativeDate(history.created_at) }}
        </div>
    </td>
    <td class="align-top">
        <div class="flex px-4 py-3 whitespace-nowrap">
            {{ formatDate(history.created_at) }}
        </div>
    </td>
    <td class="align-top">
        <Link v-if="$page.props.slskeyadmin" :href="`/admin/groups/${history.slskey_group.slskey_code}`">
        <div class="underline flex px-4 py-3 whitespace-nowrap">
            {{ history.slskey_group.name }}
        </div>
        </Link>
        <div v-else class="flex px-4 py-3 whitespace-nowrap">
            {{ history.slskey_group.name }}
        </div>
    </td>
    <td v-if="showPrimaryId" class="align-top">
        <Link v-if="history.slskey_user" :href="`/users/${history.slskey_user.primary_id}`">
        <div class="underline flex px-4 py-3 whitespace-nowrap">
            {{ history.slskey_user.primary_id }}
        </div>
        </Link>
        <div v-else class="flex px-4 py-3 whitespace-nowrap">
            {{ history.primary_id }}
        </div>
    </td>
    <td class="align-top">
        <div class="flex px-4 py-3 whitespace-nowrap">
            <UserActionChip :action="history.action" />
        </div>
    </td>
    <td class="align-top">
        <div class="flex px-4 py-3 whitespace-nowrap">
            {{ history.trigger }}
        </div>
    </td>
    <td class="align-top">
        <Link v-if="$page.props.slskeyadmin" :href="`/admin/users/${history.author}`">
            <div class="underline flex px-4 py-3 pr-8 whitespace-nowrap">
            {{ history.author }}
        </div>
        </Link>
        <div v-else class="flex px-4 py-3 whitespace-nowrap">
            {{ history.author }}
        </div>
    </td>
   
</template>

<script>
import UserActionChip from '@/Shared/UserActionChip.vue'
import Icon from '@/Shared/Icon.vue'
import { Link } from '@inertiajs/inertia-vue3';

export default {
    components: {
        Icon,
        UserActionChip,
        Link
    },
    props: {
        history: Object,
        showRelativeDate: Boolean,
        showPrimaryId: Boolean
    },
    methods: {
        formatDate(date) {
            return this.$moment(date).format('lll');
        },
        formatRelativeDate(date) {
            return this.$moment(date).fromNow();
        }
    }
}
</script>