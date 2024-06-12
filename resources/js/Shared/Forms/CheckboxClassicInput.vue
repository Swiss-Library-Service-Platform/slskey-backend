<template>
    <div :class="$attrs.class">
        <label v-if="label" class="form-label flex items-center" :for="id">
            <input :disabled="disabled" :id="id" ref="input" v-bind="{ ...$attrs, class: null }"
                class="form-checkbox rounded shadow-sm border-gray-300 focus:border-color-one-1 focus:ring focus:ring-color-one-1 focus:ring-opacity-50"
                :class="{ error: error }" :type="type" v-model="selected"
                @change="$emit('update:modelValue', this.selected ? 1 : 0)" />
            <span class="ml-2" :class="disabled ? 'text-gray-500' : ''">{{ label }}</span>
            <div v-if="error" class="form-error">{{ error }}</div>
        </label>
    </div>
</template>

<script>
import { v4 as uuid } from 'uuid'

export default {
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
        disabled: Boolean
    },
    data() {
        return {
            selected: this.modelValue ? true : false
        }
    },
    emits: ['update:modelValue'],
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
    },
}
</script>
