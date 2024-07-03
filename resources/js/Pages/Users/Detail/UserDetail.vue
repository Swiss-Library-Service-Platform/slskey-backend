<template>
    <AppLayout title="User" :breadCrumbs="[
        { name: $t('user_management.title'), link: '/users' },
        { name: slskeyUser.data.full_name ?? slskeyUser.data.primary_id}
    ]">
        <div class="mt-5 mb-10 bg-white gap-5 rounded-md shadow-md">
            <!-- component -->
            <div class="w-full flex justify-between border-b pl-4">
                <ActionButton class="w-fit my-3 mr-8 px-8" @click.prevent="activate()" icon="key" :loading="loading">
                    {{ $t('user_management.new_activation') }}
                </ActionButton>
                <ul class="flex flex-row">
                    <template v-for="(tab, index) in tabs" :key="index">
                        <li class="flex items-center cursor-pointer py-3 px-6 transition list-none"
                            :class="activeTab === index ? 'border-b border-b-4 border-color-slsp text-color-slsp font-bold bg-color-slsp-bg' : ''"
                            @click="setActiveTab(index)">
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

            <div class="bg-white rounded">
                <div v-show="activeTab === 0">
                    <UserDetailActivations :slskeyUser="slskeyUser.data" :isWebhookMailActivation="isWebhookMailActivation"/>
                </div>
                <div class="p-8" v-show="activeTab === 1">
                    <AlmaUserDetailsShow class="border" :loading="almaLoading" :almaUser="almaUser" />
                </div>
                <div class="py-4" v-show="activeTab === 2">
                    <UserDetailHistory :slskeyHistories="slskeyUser.data.slskey_histories"/>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import UserDetailActivations from './Tabs/UserDetailActivations.vue';
import AlmaUserDetailsShow from '@/Shared/AlmaUserDetailsShow.vue'
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
        UserDetailHistory,
        ActionButton,
        Icon
    },
    props: {
        slskeyUser: Object,
        slskeyHistories: Array,
        isWebhookMailActivation: Boolean
    },
    data() {
        return {
            loading: false,
            almaUser: null,
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
                this.almaUser = response.data.almaUser;
                this.almaLoading = false;
                this.showUserExlamation = !this.almaUser;
                // Update full name if it changed
                if (this.almaUser && this.almaUser.full_name !== this.slskeyUser.data.full_name) {
                    this.slskeyUser.data.full_name = this.almaUser.full_name;
                }
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