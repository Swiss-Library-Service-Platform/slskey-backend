<template>
    <div class="flex flex-col rounded border border-gray-300">
        <div class="py-4 flex flex-row justify-between items-center px-4"
            :class="{ 'border-b border-gray-table': index < options.length - 1 }"
            v-for="(option, index) in options">
            <label v-if="option.name" :class="{
                'italic text-gray-disabled cursor-not-allowed border-gray-disabled':
                    (option.workflow == 'Webhook') || (option.activation && option.activation.blocked),
            }" class=" flex items-center cursor-pointer" :for="getId(option.value)">

                <input v-if="option.workflow != 'Webhook'" :checked="option.value == this.modelValue"
                    :id="getId(option.value)" ref="input" v-bind="{ ...$attrs, class: null }"
                    class="text-slsp rounded shadow-sm border-gray-300 focus:border-one-1 focus:ring focus:ring-one-1 focus:ring-opacity-50"
                    :class="{ error: error }" type="checkbox" @change="clickOption(option.value, $event)"
                    :disabled="(option.workflow == 'Webhook') || (option.activation && option.activation.blocked)" />

                <MaterialIcon v-else icon="auto_mode" />

                <span class="text-md ml-4">

                    <!-- 
                <SlskeyGroupNameAndIcon :disabled="option.workflow === 'Webhook'" :workflow="option.workflow" :slskeyGroupName="option.name" />
            -->
                    {{ option.name }}
                </span>
            </label>
            <div class="ml-4">
                <UserStatusChip v-if="option.activation" :activation="option.activation" />
            </div>
            <div v-if="error" class="form-error">{{ error }}</div>
        </div>
    </div>
</template>

<script>
import { v4 as uuid } from 'uuid';
import UserStatusChip from '@/Shared/UserStatusChip.vue';
import SlskeyGroupNameAndIcon from '../SlskeyGroupNameAndIcon.vue';
import MaterialIcon from '@/Shared/MaterialIcon.vue';
export default {
    components: {
        UserStatusChip,
        SlskeyGroupNameAndIcon,
        MaterialIcon
    },
    inheritAttrs: false,
    props: {
        id: {
            type: String,
            default() {
                return `checkbox-input-${uuid()}`
            },
        },
        error: String,
        //label: String,
        modelValue: String,

        options: Array

    },
    data() {
        return {
            // selected: this.modelValue ? true : false
        }
    },
    emits: ['update:modelValue'],
    methods: {
        getId(value) {
            return `checkbox-input-${value}`
        },
        clickOption(value, event) {
            this.$emit('update:modelValue', event.target.checked ? value : null);
        }
    },
}
</script>

<style scoped>
input {
    height: 1.5rem;
    width: 1.5rem;
}
</style>