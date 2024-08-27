<template>
    <AppLayout title="User" :breadCrumbs="[
        { name: $t('user_management.title'), link: '/users' },
        { name: slskeyUser.data.full_name ?? slskeyUser.data.primary_id }
    ]">
        <div class="w-full">
            <div class="flex flex-col gap-y-8 rounded-b shadow bg-white">
                <ul class="flex flex-row w-fit h-16 ">
                    <template v-for="(tab, index) in tabs" :key="index">
                        <li class="relative flex items-center cursor-pointer py-3 px-6 transition list-none" :class="{
                            'tabitem text-color-slsp font-bold bg-color-slsp-bg': activeTab === index,
                            'rounded-tr-md': index === tabs.length - 1
                        }" @click="setActiveTab(index)">
                            <div v-if="index == 0" class="flex items">
                                <Icon icon="key" class="w-6 h-6 mr-2" />
                            </div>
                            <div v-if="index == 1 && showUserExlamation" class="flex items">
                                <Icon icon="exclamation" class="w-6 h-6 mr-2 text-color-blocked" />
                            </div>
                            <div v-if="index == 1 && !showUserExlamation" class="flex items">
                                <Icon icon="user" class="w-6 h-6 mr-2" />
                            </div>
                            <div v-if="index == 2" class="flex items">
                                <Icon icon="book-open" class="w-6 h-6 mr-2" />
                            </div>
                            {{ tab }}
                        </li>
                    </template>
                </ul>

            </div>

            <div class="my-8">
                <div v-show="activeTab === 0">
                    <UserDetailActivations :slskeyUser="slskeyUser.data"
                        :isAnyWebhookMailActivation="isAnyWebhookMailActivation"
                        :isAnyShowMemberEducationalInstitution="isAnyShowMemberEducationalInstitution" />
                </div>
                <div class="bg-white rounded shadow p-8 flex gap-5" v-show="activeTab === 1">
                    <template v-if="almaLoading">
                        <AlmaUserDetailsLoad />
                    </template>
                    <template v-else>
                        <AlmaUserDetailsShow class="border" v-for="almaUser in almaUsers" :key="almaUser.primary_id"
                            :almaUser="almaUser" />
                    </template>
                </div>
                <div class="bg-white rounded shadow pt-4" v-show="activeTab === 2">
                    <UserDetailHistory :slskeyHistories="slskeyUser.data.slskey_histories" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import UserDetailActivations from './Tabs/UserDetailActivations.vue';
import AlmaUserDetailsShow from "@/Shared/AlmaUser/AlmaUserDetailsShow.vue";
import AlmaUserDetailsLoad from "@/Shared/AlmaUser/AlmaUserDetailsLoad.vue";
import UserDetailHistory from './Tabs/UserDetailHistory.vue';
import { Inertia } from '@inertiajs/inertia';
import axios from 'axios'
import { ref } from 'vue';
import ActionButton from '../../../Shared/Buttons/ActionButton.vue';
import Icon from '../../../Shared/Icon.vue';

export default {
    components: {
        AppLayout,
        Inertia,
        UserDetailActivations,
        AlmaUserDetailsShow,
        AlmaUserDetailsLoad,
        UserDetailHistory,
        ActionButton,
        Icon
    },
    props: {
        slskeyUser: Object,
        slskeyHistories: Array,
        isAnyWebhookMailActivation: Boolean,
        isAnyShowMemberEducationalInstitution: Boolean
    },
    data() {
        return {
            loading: false,
            almaUsers: null,
            almaLoading: true,
            activeTab: 0,
            showUserExlamation: false,
            tabs: [
                this.$i18n.t('user_management.tabs.activation'),
                this.$i18n.t('user_management.tabs.details'),
                this.$i18n.t('user_management.tabs.history'),
            ]
        }
    },
    mounted() {
        this.fetchExternalUserInfo();
        this.setInitialTab();
    },
    methods: {
        activate: function () {
            this.loading = true;
            Inertia.get("/activation/" + this.slskeyUser.data.primary_id);
        },
        async fetchExternalUserInfo() {
            try {
                // Make an asynchronous request to fetch external user information
                const response = await axios.get('/users/alma/' + this.slskeyUser.data.primary_id);
                this.almaUsers = response.data.almaUsers;
                this.almaLoading = false;
                this.showUserExlamation = !this.almaUsers;
            } catch (error) {
                console.error('Error fetching external user information:', error);
            }
        },
        setActiveTab(index) {
            this.activeTab = index;
            localStorage.setItem('activeTab', index);
        },
        setInitialTab() {
            const savedTab = localStorage.getItem('activeTab');
            if (savedTab !== null) {
                this.activeTab = parseInt(savedTab, 10);
            }
        }
    },

}
</script>
<style>
.tabitem::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 1.5rem;
    right: 1.5rem;
    height: 4px;
    background-color: #4e4a99;
    border-radius: 2px;
}
</style>