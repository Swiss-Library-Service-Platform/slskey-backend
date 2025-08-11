<template>
    <Combobox v-model="selected">
        <div class="relative sm:w-9/12 md:w-96 w-full">
            <div
                class="flex items-center relative w-full cursor-default p-2 overflow-hidden sm:rounded-full ring-2 ring-gray-400 focus-within:ring-one-1 bg-white text-left shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75 focus-visible:ring-offset-2 focus-visible:ring-offset-teal-300">
                <SearchIcon class="h-6 w-6 ml-2 fill-one-1" />
                <ComboboxInput :placeholder="$t('search_placeholder')" class="
                    w-full
                    border-none 
                    leading-5 
                    font-bold
                    focus:text-one-1 focus:ring-0
                    text-gray-400 
                    placeholder:text-one-1 placeholder:font-semibold" :displayValue="(item) => item.name"
                    @change="form.search = $event.target.value" />
            </div>
            <TransitionRoot enter="transition ease-out duration-300" enterFrom="opacity-0" enterTo="opacity-100"
                leave="transition ease-in duration-100" leaveFrom="opacity-100" leaveTo="opacity-0">
                <ComboboxOptions v-if="form.search !== ''" class="
                    absolute 
                    mt-3
                    w-full
                    sm:rounded-xl
                    shadow-lg
                    border border-gray-200
                    overflow-auto
                    bg-white
                    py-2
                    text-base
                    focus:outline-none
                    ">

                    <div v-if="results.length > 0 && form.search !== ''" class="p-2 text-center mb-2 ">
                        {{ $t('autocomplete.displaying') }}
                        <span class="font-bold">{{ results.length }} </span>
                        {{ $t('autocomplete.out_of') }}
                        <span class="font-bold">{{ total }}</span>
                        {{ $t('autocomplete.results') }}
                    </div>
                    <div v-if="results.length === 0 && form.search !== ''"
                        class="relative cursor-default select-none py-2 px-4 text-gray-700">
                        {{ $t('no_results') }}.
                    </div>
                    <ComboboxOption v-for="item in results" as="template" :key="item.id + item.entity + keyAppend"
                        :value="item" v-slot="{ selected, active }">
                        <li class="relative sm:px-2">
                            <div class="flex justify-between sm:rounded-sm px-2 py-1 cursor-pointer select-none" :class="{
                                'ring-2 ring-one-1 bg-one-2': active
                            }">
                                <span class="truncate"
                                    :class="{ 'font-medium': selected, 'font-normal': !selected, 'text-gray-900': !active }">
                                    {{ item.name }}
                                </span>
                                <span class="text-right ml-2 uppercase text-gray-600"
                                    :class="{ 'text-gray-400': !active }">{{
                                            $t(item.entity)
                                    }}</span>
                            </div>
                        </li>
                    </ComboboxOption>
                </ComboboxOptions>
            </TransitionRoot>
        </div>
    </Combobox>
</template>

<script>
import throttle from 'lodash/throttle';
import pickBy from 'lodash/pickBy';
import { Inertia } from '@inertiajs/inertia';
import { SearchIcon, CheckIcon } from '@heroicons/vue/solid';
import {
    Combobox,
    ComboboxInput,
    ComboboxOptions,
    ComboboxOption,
    ComboboxButton,
    TransitionRoot,
} from '@headlessui/vue'

export default {
    components: {
        SearchIcon,
        CheckIcon,
        Combobox,
        ComboboxInput,
        ComboboxOptions,
        ComboboxOption,
        ComboboxButton,
        TransitionRoot
    },
    props: {
        results: Object,
        total: Number,
        filters: Object,
    },
    data() {
        return {
            keyAppend: 0,
            selected: {},
            form: {
                search: this.filters.search,
            },
        }
    },
    watch: {
        selected(value) {
            Inertia.get(value.urlPath);
        },
        form: {
            deep: true,
            handler: throttle(function (new_value, old_value) {
                Inertia.get('/home', pickBy(this.form),
                    {
                        preserveState: true,
                        replace: true,
                        onFinish: visit => {
                            this.keyAppend += 1;
                        }
                    }
                )
            }, 500)
        }
    }
}
</script>