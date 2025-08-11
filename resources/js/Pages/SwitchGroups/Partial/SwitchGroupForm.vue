<template>
    <form @submit.prevent="store">
        <div class="flex flex-col">
            <h3 class="text-2xl px-4 py-4 m-4 text-slsp bg-slsp-bg rounded-sm">{{
                $t('switch_groups.general') }}</h3>
            <div class="grid grid-cols-2 px-8 pb-8 gap-8">
                <!-- Name -->
                <text-input v-model="form.name" :error="form.errors.name" :label="`${$t('switch_groups.name')} *`" />
                <!-- Switch Group Id -->
                <text-input v-model="form.switch_group_id" :error="form.errors.switch_group_id"
                    :label="`${$t('switch_groups.switch_group_id')} *`" />
            </div>
            <div class="border-t border-b border-gray-table"></div>
            <h3 class="text-2xl px-4 py-4 m-4 text-slsp bg-slsp-bg rounded-sm">{{
                $t('switch_groups.slskey_groups') }}</h3>
            <div class="grid grid-cols-1 px-8 pb-8 gap-8">

                <!-- SLSKey Groups -->
                <table class="table-auto min-w-full rounded-sm">
                    <tbody class="">
                        <template v-if="form.slskeyGroups?.length > 0">
                            <tr v-for="slskeyGroup in form.slskeyGroups" :key="'slskeygroup' + slskeyGroup.id"
                                class="">

                                <td class="pr-6 py-2 ">
                                    - {{ slskeyGroup.slskey_code }}
                                </td>
                                <td class="pr-6 py-2 ">
                                    {{ slskeyGroup.name }}
                                </td>
                            </tr>
                        </template>
                        <template v-else>
                            <tr>
                                <td class="pl-6 py-2 whitespace-nowrap italic">{{ $t('slskey_groups.no_switch_groups')
                                    }}.</td>
                            </tr>
                        </template>

                    </tbody>
                </table>
            </div>
            <!-- Publishers -->
            <div class="border-t border-b border-gray-table"></div>
            <h3 class="text-2xl px-4 py-4 m-4 text-slsp bg-slsp-bg rounded-sm">{{
                $t('switch_groups.publishers_title') }}</h3>
            <div class="grid grid-cols-1 px-8 pb-8 gap-8">
                <text-area-input v-model="form.publishers" :error="form.errors.publishers"
                    :label="`${$t('switch_groups.publishers')}`" />
            </div>
            
            <!-- Member Count -->
            <div class="border-t border-b border-gray-table"></div>
            <h3 class="text-2xl px-4 py-4 m-4 text-slsp bg-slsp-bg rounded-sm">{{
                $t('switch_groups.members_count_title') }}</h3>
            <div class="grid grid-cols-2 px-8 pb-8 pt-4 gap-8">
                <div>
                    {{ $t('switch_groups.members_count') }}: {{ form.members_count }}
                </div>
            </div>

            <div class="border-t border-b border-gray-table"></div>
            <div class="flex">
                <div class="flex w-full flex-row justify-between gap-4 px-4 py-4">
                    <DefaultButton @click="cancel()" class="text-blocked w-fit"
                        :tooltip="$t('switch_groups.cancel')">
                        {{ $t('switch_groups.cancel') }}
                    </DefaultButton>
                    <DefaultButton @click="submit()" class="w-fit" icon="save"
                        :tooltip="$t('switch_groups.save')">
                        {{ $t('switch_groups.save') }}
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
    },
    data() {
        return {
            form: this.modelValue,
        }
    },
    emits: ['update:modelValue', 'submit'],
    watch: {
    },
    methods: {
        submit(store) {
            this.$emit('submit', store);
        },
        cancel() {
            this.$emit('cancel');
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