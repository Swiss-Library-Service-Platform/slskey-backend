<template>
    <div  :class="$attrs.class">
        <!-- <CursorDefaultClickIcon v-if="icon == 'cursor_default_click'"/> -->
        <RefreshAutoIcon v-if="icon == 'auto_mode'"/>
        <AccountSupervisorCircleOutline v-if="icon == 'cursor_default_click'"/>
        <FilterRemove :size="size" v-if="icon == 'filter_remove'"/>
        <Help :size="size" v-if="icon == 'help'"/>
    </div>
</template>

<script>
import CursorDefaultClickIcon from 'vue-material-design-icons/CursorDefaultClick.vue';
import RefreshAutoIcon from 'vue-material-design-icons/RefreshAuto.vue';
import AccountSupervisorCircleOutline from 'vue-material-design-icons/AccountSupervisorCircleOutline.vue';
import FilterRemove from 'vue-material-design-icons/FilterRemove.vue';
import Help from 'vue-material-design-icons/Help.vue';

 
export default {
    components: {
        RefreshAutoIcon,
        CursorDefaultClickIcon,
        AccountSupervisorCircleOutline,
        FilterRemove,
        Help
    },
    props: {
        icon: {
            type: String,
            required: true
        },
        size: {
            type: Number,
            default: 1
        }
    }
}
</script>