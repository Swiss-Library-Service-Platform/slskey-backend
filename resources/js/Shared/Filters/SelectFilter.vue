<template>
    <div class="flex flex-col">
        <label class="block font-medium text-sm text-gray-700">
            <span>{{ label }}</span>
        </label>
        <select class="
                px-3 py-2 border
                h-10 border-gray-300 rounded
                transition delay-75 
                hover:border-gray-400 focus:border-gray-400 
                focus:shadow-outline focus:ring-0 focus:border-1" v-model="value" :placeholder="placeholder"
            @change="$emit('update:modelValue', $event.target.value)">
            <option disabled value="">Please select one</option>

            <template v-for="option in options" :key="option.value">
                <option :value="option.value">{{ option.name }}</option>
            </template>

        </select>

    </div>
</template>

<script>
export default {
    components: {},
    data() {
        return {
            value: this.modelValue
        }
    },
    props: {
        label: String,
        modelValue: String,
        options: Array,
        placeholder: String
    },
    emits: ['update:modelValue'],
    watch: {
        modelValue(newValue) {
            this.value = newValue;
        },
    },
}
</script>
