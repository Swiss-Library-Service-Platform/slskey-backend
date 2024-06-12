<template>
    <div class="flex flex-col">
        <label class="block font-medium text-sm text-gray-700">
            <span>{{ label }}</span>
        </label>
        <Datepicker :model-value="modelValue" @update:model-value="handleUpdate" class="ml-auto w-40" :auto-apply="true"
            :utc="true" :enable-time-picker="false" />
    </div>
</template>

<script>
import Datepicker from '@vuepic/vue-datepicker';
export default {
    components: { Datepicker },
    data() {
        return {
            date: this.modelValue
        }
    },
    props: {
        label: String,
        modelValue: String,
    },
    methods: {
        handleUpdate(modelDate) {
            let date = this.$moment(modelDate).startOf('day').format('YYYY-MM-DD')
            this.$emit('update:modelValue', date === 'Invalid date' ? null : date)
        }
    },
    emits: ['update:modelValue'],
}
</script>

<style>
.dp__input {
    height: 2.5rem;
}
</style>