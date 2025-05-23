<template>
    <Listbox v-model="selectedData">
        <div class="relative mt-1">
            <ListboxLabel v-if="label" class="form-label">{{ label }}:</ListboxLabel>
            <ListboxButton
                class="relative w-full cursor-default rounded-md bg-white py-2 pl-3 pr-10 text-left shadow-md focus:outline-none focus-visible:border-indigo-500 focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75 focus-visible:ring-offset-2 focus-visible:ring-offset-orange-300 sm:text-sm">
                <span class="block truncate">{{ selectedData.name }}</span>
                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                    <SelectorIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                </span>
            </ListboxButton>

            <transition leave-active-class="transition duration-100 ease-in" leave-from-class="opacity-100"
                leave-to-class="opacity-0">
                <ListboxOptions
                    class="absolute mt-1 max-h-60 w-full overflow-auto rounded-sm bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm">
                    <ListboxOption v-slot="{ active, selected }" v-for="item in data" :key="item.name" :value="item"
                        as="template">
                        <li :class="[
                            active ? 'bg-amber-100 text-amber-900' : 'text-gray-900',
                            'relative cursor-default select-none py-2 pl-10 pr-4',
                        ]">
                            <span :class="[
                                selected ? 'font-medium' : 'font-normal',
                                'block truncate',
                            ]">{{ item.name }}</span>
                            <span v-if="selected"
                                class="absolute inset-y-0 left-0 flex items-center pl-3 text-amber-600">
                                <CheckIcon class="h-5 w-5" aria-hidden="true" />
                            </span>
                        </li>
                    </ListboxOption>
                </ListboxOptions>
            </transition>
        </div>
    </Listbox>
</template>

<script>
import {
    Listbox,
    ListboxLabel,
    ListboxButton,
    ListboxOptions,
    ListboxOption,
} from '@headlessui/vue'

import { SelectorIcon, CheckIcon } from '@heroicons/vue/solid';

export default {
    props: {
        modelValue: String,
        data: Array,
        label: String
    },
    data() {
        return {
            selectedData: this.data[0]
        }
    },
    watch: {
        selectedData(selected) {
            this.$emit("update:modelValue", selected);
        },
    },
    components: {
        Listbox,
        ListboxLabel,
        ListboxButton,
        ListboxOptions,
        ListboxOption,
        SelectorIcon,
        CheckIcon
    }
}
</script>