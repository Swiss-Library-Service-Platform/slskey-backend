<template>
    <div class="flex flex-col">
        <label class="form-label">{{ this.label }}</label>

        <div class="flex w-full">
            <text-input v-model="inputValue" :placeholder="this.placeholder" class="w-full" />

            <a class=" p-2 rounded-r border-y border-r border-gray-50 text-white" :disabled="!inputValue" :class="{
                'bg-gray-200': !inputValue,
                'bg-green-700': inputValue
            }" @click="!inputValue ? () => { } : addValue(inputValue)">
                <PlusIcon class="h-5 w-5" />
            </a>
        </div>

        <!-- <label v-if="this.values.length > 0" class="mt-4 form-label">{{ $t('maintainers_current')}}</label> -->

        <div class="mt-2" v-for="(value, index) in this.values">
            <div class="bg-one-2 w-auto rounded-sm p-2 transition duration-200 ease-out flex justify-start">
                <p class="flex gap-2 overflow-visible text-m w-full whitespace-nowrap place-items-center justify-between">
                    
                    <span class="truncate">
                        {{ value }}
                    </span>
                    <span class="bg-red-700 rounded-full ring-1 ring-one-2 ">
                        <a class="w-6 h-6 text-white bg-red-300" @click="removeValue(value)">
                            <MinusIcon class="h-5 w-5" />
                        </a>
                    </span>

                </p>
            </div>
            <div v-if="formErrors.hasOwnProperty(this.error + '.' + index)" class="form-error">{{ formErrors[error + '.' + index] }}</div>

        </div>

    </div>
</template>
  
<script>
import { v4 as uuid } from 'uuid'
import TextInput from '@/Shared/Forms/TextInput.vue'
import { MinusIcon, PlusIcon } from '@heroicons/vue/outline'

export default {
    inheritAttrs: false,
    components: {
        TextInput,
        PlusIcon,
        MinusIcon,
    },
    props: {
        id: {
            type: String,
            default() {
                return `multiple-input-${uuid()}`
            },
        },
        label: String,
        placeholder: String,
        values: Array,
        formErrors: Object,
        error: String
    },
    //emits: ['update:modelValue'],
    data() {
        return {
            inputValue: "",
        }
    },
    methods: {
        addValue(val) {
            this.$emit('onAdd', val);
            this.inputValue = "";
        },
        removeValue(val) {
            this.$emit('onRemove', val)
        }
    },
    
}
</script>
  