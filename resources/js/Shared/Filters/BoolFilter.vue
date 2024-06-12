<template>
    <div :class="$attrs.class">
        <label v-if="label" class="form-label accent-gray-500" :for="id">
            <input :id="id" ref="input" v-bind="{ ...$attrs, class: null }" class="form-checkbox rounded shadow-sm border-gray-300 
                focus:border-color-one-1 focus:ring focus:ring-color-one-1 focus:ring-opacity-50 text-color-one"
                :class="{ error: error }" :type="type" v-model="selected"
                @change="$emit('update:modelValue', this.selected ? 'true' : 'false')" />
            <span class="ml-2">{{ label }}</span>
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
        modelValue: String,
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
    },
}
</script>
