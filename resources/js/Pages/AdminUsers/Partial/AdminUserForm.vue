<template>
    <form @submit.prevent="store">
        <div class="flex flex-col">
            <!-- General -->
            <h3 class="text-2xl px-4 py-4 m-4 text-slsp bg-slsp-bg rounded-sm">{{ $t('admin_users.general') }}
            </h3>
            <div class="grid grid-cols-2 px-8 pb-8 pt-4 gap-8">
                <checkbox-input class="w-full" :error="form.errors.is_edu_id" v-model="form.is_edu_id"
                    :label="$t('admin_users.is_edu_id')" />
                <div v-if="!form.is_edu_id" /> <!--Placeholder -->
                <TextInput v-if="form.is_edu_id" :label="$t('admin_users.search_eduid')" v-model="inputUserEmail"
                    @keydown.enter="searchPrimaryId" />
                
                <TextInput :label="`${$t('admin_users.display_name')} *`" v-model="form.display_name"
                    :error="form.errors.display_name" />
                <TextInput  v-if="form.is_edu_id" :label="`${$t('admin_users.user_identifier_eduid')} *`"
                    v-model="form.user_identifier" :error="form.errors.user_identifier" />

                <TextInput v-if="!form.is_edu_id" :label="`${$t('admin_users.user_identifier_username')} *`"
                    v-model="form.user_identifier" :error="form.errors.user_identifier" />
                <TextInput v-if="isCreating && !form.is_edu_id" :label="`${$t('admin_users.initial_password')} *`"
                    v-model="form.password" :error="form.errors.password" />
            </div>
            <!-- Permissions SLSKey Groups -->
            <div class="border-t border-b border-gray-table"></div>
            <h3 class="text-2xl px-4 py-4 m-4 text-slsp bg-slsp-bg rounded-sm">{{ $t('admin_users.permissions')
            }}</h3>
            <div class="grid grid-cols-2 px-8 pb-8 pt-4 gap-8">
                <checkbox-input class="w-full" :error="form.errors.is_slsp_admin" v-model="form.is_slsp_admin"
                    :label="$t('admin_users.slsp_admin')" />
                <div /> <!--Placeholder -->
            </div>
            <div v-if="!form.is_slsp_admin" class="grid grid-cols-1 px-8 pb-8 gap-8">
                <h1 class="text-lg underline">{{ $t('admin_users.permission_groups') }}</h1>
                <!-- Slskey Groups -->
                <table class="table-auto min-w-full rounded-sm">
                    <tbody class="">
                        <template v-if="form.slskeyGroups.length > 0">
                            <tr v-for="slskeyGroup in form.slskeyGroups" :key="'slskeygroup' + slskeyGroup.id"
                                class="hover:bg-gray-100 focus-within:bg-gray-100">
                                <td class="pr-6 py-2 ">
                                    {{ slskeyGroup.name }}
                                </td>
                                <td class="pl-6 py-2 text-right">
                                    <DefaultIconButton class="bg-blocked py-1" icon="x" :tooltip="$t('admin_users.delete_slskey_group')"
                                        @click="removeGroup(slskeyGroup.id)" />
                                </td>
                            </tr>
                        </template>
                        <template v-else>
                            <tr>
                                <td class="py-2 whitespace-nowrap italic">{{ $t('admin_users.no_slskeygroups')
                                }}.</td>
                            </tr>
                        </template>

                    </tbody>
                </table>
                <h1 class="text-lg underline">{{ $t('admin_users.permission_groups_add') }}</h1>
                <div class="flex flex-row w-full items-center gap-x-8">
                    <SelectInput class="w-full" v-model="this.newSlskeyGroup" :options="this.availableSlskeyGroups.data"
                        @change="addGroup()">
                    </SelectInput>
                
                </div>
            </div>
            <!-- Bottom Buttons-->
            <div class="border-t border-b border-gray-table"></div>
            <div class="flex">
                <div class="flex w-full flex-row justify-between gap-4 px-4 py-4">
                    <div class="flex flex-row gap-4">
                        <DefaultButton @click="cancel()" class="text-black w-fit"
                            :tooltip="$t('admin_users.cancel')">
                            {{ $t('admin_users.cancel') }}
                        </DefaultButton>
                        <DefaultButton v-if="!isCreating" @click="deleteUser()" class="text-blocked w-fit"
                            icon="trash" :tooltip="$t('admin_users.delete')">
                            {{ $t('admin_users.delete') }}
                        </DefaultButton>

                    </div>
                    <DefaultButton @click="submit()" class="w-fit" icon="save"
                        :tooltip="$t('admin_users.create_new')">
                        <span v-if="isCreating">{{ $t('admin_users.create_new') }}</span>
                        <span v-else>
                            {{ $t('admin_users.save') }}
                        </span>
                    </DefaultButton>
                </div>
            </div>
        </div>
    </form>
</template>

<script>
import CheckboxInput from '@/Shared/Forms/CheckboxInput.vue'
import TextAreaInput from '@/Shared/Forms/TextAreaInput.vue'
import TextInput from '@/Shared/Forms/TextInput.vue'
import LoadingButton from '@/Shared/Buttons/LoadingButton.vue'
import CheckboxClassicInput from '@/Shared/Forms/CheckboxClassicInput.vue'
import { KeyIcon } from '@heroicons/vue/solid';
import NumberInput from '@/Shared/Forms/NumberInput.vue'
import SelectInput from '@/Shared/Forms/SelectInput.vue';
import DefaultButton from '@/Shared/Buttons/DefaultButton.vue';
import DefaultIconButton from '@/Shared/Buttons/DefaultIconButton.vue';
export default {
    components: {
        TextInput,
        CheckboxInput,
        TextAreaInput,
        LoadingButton,
        CheckboxClassicInput,
        KeyIcon,
        NumberInput,
        DefaultIconButton,
        DefaultButton,
        TextInput,
        SelectInput,
        NumberInput,
        CheckboxInput,
    },
    props: {
        modelValue: {
            type: Object,
            required: true
        },
        isCreating: {
            type: Boolean,
            required: true
        },
        availableSlskeyGroups: Object,
    },
    data() {
        return {
            form: this.modelValue,
            newSlskeyGroup: null,
            inputUserEmail: null,
        }
    },
    emits: ['update:modelValue', 'submit'],
    computed: {
        isNewSlskeyGroupToAdd() {
            return this.newSlskeyGroup && !this.form.slskeyGroups.map(group => group.slskey_code).includes(this.newSlskeyGroup);
        }
    },
    methods: {
        submit(store) {
            this.$emit('submit', store);
        },
        cancel() {
            this.$emit('cancel');
        },
        deleteUser() {
            this.$emit('delete');
        },
        removeGroup(id) {
            this.form.slskeyGroups = this.form.slskeyGroups.filter(group => group.id !== id);
        },
        addGroup() {
            if (this.newSlskeyGroup) {
                // Add Group to SLSKey Group
                // check if not already in the list
                if (!this.form.slskeyGroups.map(group => group.slskey_code).includes(this.newSlskeyGroup)) {
                    this.form.slskeyGroups.push(this.availableSlskeyGroups.data.find(group => group.slskey_code === this.newSlskeyGroup));
                }
                this.newSlskeyGroup = null;
            }
        },
        async searchPrimaryId() {
            try {
                // Make an asynchronous request to fetch external user information
                const response = await axios.get('/admin/users/findeduid/' + this.inputUserEmail);
                if (response.data.user) {
                    this.form.user_identifier = response.data.user.primary_id;
                    this.form.display_name = response.data.user.first_name;
                } else if (response.data.message) {
                    this.$notify({
                        title: 'Error',
                        text: response.data.message,
                        type: 'error',
                        duration: 10000
                    });
                }
            } catch (error) {
                console.error('Error fetching external user information:', error);
            }
        },
    }
}
</script>

<style>
.legendInnerDiv {
    display: flex;
    align-items: center;
}
</style>
