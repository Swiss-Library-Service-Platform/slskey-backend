<template>
    <div  :class="$attrs.class">
        <!-- <CursorDefaultClickIcon v-if="icon == 'cursor_default_click'"/> -->
        <RefreshAutoIcon v-if="icon == 'auto_mode'"/>
        <AccountSuper visorCircleOutline v-if="icon == 'cursor_default_click'"/>
        <FilterRemove :size="size" v-if="icon == 'filter_remove'"/>
        <Help :size="size" v-if="icon == 'help'"/>
        <Sort size="18" v-if="icon == 'sort'"/>
        <SortAscending size="18" v-if="icon == 'sort_ascending'"/>
        <SortDescending size="18" v-if="icon == 'sort_descending'"/>
    </div>
</template>

<script>
import CursorDefaultClickIcon from 'vue-material-design-icons/CursorDefaultClick.vue';
import RefreshAutoIcon from 'vue-material-design-icons/RefreshAuto.vue';
import AccountSupervisorCircleOutline from 'vue-material-design-icons/AccountSupervisorCircleOutline.vue';
import FilterRemove from 'vue-material-design-icons/FilterRemove.vue';
import Help from 'vue-material-design-icons/Help.vue';
import Sort from 'vue-material-design-icons/Sort.vue';
import SortAscending from 'vue-material-design-icons/SortAscending.vue';
import SortDescending from 'vue-material-design-icons/SortDescending.vue';

 
export default {
    components: {
        RefreshAutoIcon,
        CursorDefaultClickIcon,
        AccountSupervisorCircleOutline,
        FilterRemove,
        Help,
        Sort,
        SortAscending,
        SortDescending
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