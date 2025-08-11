<template>
    <div :class="$attrs.class">
        <label class="block font-medium text-sm text-gray-700">
            <span>{{ label }}</span>
        </label>
        <div
            class="h-10 cursor-pointer bg-white flex flex-row rounded shadow-sm border border-gray-300 focus:border-one-1 focus:ring focus:ring-one-1 focus:ring-opacity-50 text-one whitespace-nowrap">
            <div class="flex items-center p-2 px-4 rounded" @click="selectTab(0)"
                :class="modelValue == 0 ? 'bg-header-bg text-white' : ''">
                <Icon :icon="icon1" class="h-4 w-4 mr-2" />
                {{ tab1 }}
            </div>
            <div class="flex items-center border-l p-2 px-4 rounded" @click="selectTab(1)"
                :class="modelValue == 1 ? 'bg-header-bg text-white' : ''">
                <Icon :icon="icon2" class="h-4 w-4 mr-2" />
                {{ tab2 }}
            </div>
        </div>
        <div v-if="error" class="form-error">{{ error }}</div>
    </div>
</template>

<script>
import { v4 as uuid } from 'uuid';
import Icon from '@/Shared/Icon.vue';

export default {
    components: {
        Icon
    },
    inheritAttrs: false,
    props: {
        id: {
            type: String,
            default() {
                return `checkbox-input-${uuid()}`
            },
        },
        type: {
            type: String,
            default: 'checkbox',
        },
        error: String,
        label: String,
        modelValue: Number,
        tab1: String,
        tab2: String,
        icon1: String,
        icon2: String

    },
    data() {
        return {
            selected: this.modelValue === 'false' ? false : true
        }
    },
    emits: ['update:modelValue'],
    watch: {
        modelValue() {
            this.selected = this.modelValue === 'false' ? false : true
        }
    },
    methods: {
        focus() {
            this.$refs.input.focus()
        },
        select() {
            this.$refs.input.select()
        },
        setSelectionRange(start, end) {
            this.$refs.input.setSelectionRange(start, end)
        },
        selectTab(tab) {
            this.$emit('update:modelValue', tab)
        }
    },
}
</script>
