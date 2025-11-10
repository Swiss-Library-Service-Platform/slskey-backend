

<template>
    <AppLayout :title="$t('reporting.title')"
        :breadCrumbs="[{ name: $t('reporting.title'), link: '/reporting' }, { name: slskeyGroup.data.name }]">

        <div class="w-fit my-8 justify-center align-center flex flex-col py-8 bg-white gap-8 rounded-sm gap-y-4 px-8 shadow-md">
            <div>
                <div class="text-2xl">
                    {{ $t('reporting.recipients') }}
                </div>
                <div class="italic">
                    {{ $t('reporting.info') }}
                </div>
            </div>

            <table class="table-auto min-w-full divide-y divide-gray-table rounded-sm">
                <thead class="bg-slsp-bg-lighter px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="py-4 px-6 text-left whitespace-nowrap"> {{ $t('reporting.email') }} </th>
                        <th class="py-4 px-6 text-left whitespace-nowrap"> {{ $t('reporting.date') }} </th>
                        <th class="py-4 px-6 text-left whitespace-nowrap"> </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-table">
                    <template v-if="slskeyGroup.data.emails.length > 0">
                        <tr v-for="email in slskeyGroup.data.emails" :key="'user' + email.id"
                            class="hover:bg-gray-100 focus-within:bg-gray-100">

                            <td class="px-6 py-4 align-center flex items-center">
                                <Icon icon="mail" class="h-4 w-4 mr-2"></Icon>
                                {{ email.email_address }}
                            </td>
                            <td class="px-6 py-4 align-center">
                                {{ formatDate(email.created_at) }}
                            </td>
                            <td class="px-6 py-4 align-center">
                                <DefaultConfirmIconButton class="bg-blocked text-deactivated py-1" icon="trash"
                                    :tooltip="$t('reporting.settings.delete_recipient')" :confirmText="$t('reporting.settings.delete_recipient')"
                                    @confirmed="removeEmail(email.id)" />
                            </td>
                        </tr>
                    </template>
                    <template v-else>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap italic">{{ $t('reporting.no_emails') }}.</td>
                        </tr>
                    </template>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap flex items-center">
                            <Icon icon="mail" class="h-4 w-4 mr-2"></Icon>
                            <TextInput type="email" :error="errors.email" v-model="newEmail"
                                :placeholder="$t('reporting.email')">
                            </TextInput>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap"></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <DefaultIconButton @click.prevent="addEmail" class="bg-active py-1 text-white" icon="plus"
                                :tooltip="$t('reporting.settings.add')" />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import FilterControl from '../../Shared/Filters/FilterControl.vue';
import SelectFilter from '@/Shared/Filters/SelectFilter.vue';
import { Inertia } from '@inertiajs/inertia';
import Icon from '@/Shared/Icon.vue';
import DefaultConfirmIconButton from '@/Shared/Buttons/DefaultConfirmIconButton.vue';
import DefaultIconButton from '@/Shared/Buttons/DefaultIconButton.vue';
import TextInput from '@/Shared/Forms/TextInput.vue';

export default {
    components: {
        AppLayout,
        FilterControl,
        SelectFilter,
        Inertia,
        Icon,
        DefaultConfirmIconButton,
        DefaultIconButton,
        TextInput
    },
    props: {
        slskeyGroup: Object,
        errors: Object
    },
    data() {
        return {
            newEmail: '',
        }
    },
    methods: {
        formatDate(date) {
            return this.$moment(date).format('ll');
        },
        addEmail() {
            Inertia.post("/reporting/" + this.slskeyGroup.data.slskey_code, {
                email: this.newEmail,
            }, {
                onSuccess: () => {
                    this.newEmail = '';
                },
            })
        },
        removeEmail(id) {
            Inertia.delete("/reporting/" + this.slskeyGroup.data.slskey_code + '/' + id, {
                onSuccess: () => {
                    this.newEmail = '';
                },
            });
        }
    },
}
</script>