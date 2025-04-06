<template>
    <div
        class="bg-white shadow-md sm:rounded-md px-4 py-3 flex items-center justify-center border-t border-gray-200 sm:px-6">
        <div class="grow flex sm:hidden justify-between">
            <a :href="pages.links[0].url"
                :class="pages.links[0].url ? 'hover:bg-gray-50 text-gray-700' : 'disabled text-gray-500'"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-sm  bg-white">
                {{ $t('pagination.previous') }} </a>
            <a :href="pages.links[pages.links.length - 1].url"
                :class="pages.links[pages.links.length - 1].url ? 'hover:bg-gray-50 text-gray-700' : 'disabled text-gray-500'"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-sm bg-white">
                {{ $t('pagination.next') }} </a>
        </div>
        <div class="hidden grow sm:flex">
            <div class="hidden w-full lg:grid-cols-3 sm:grid sm:grid-cols-4 items-center flex-wrap gap-y-2">
                <div class="lg:order-1 lg:col-start-1 lg:col-span-1 order-2 col-start-1 col-span-2">
                    <p class="text-sm text-gray-700 font-medium">
                        {{ pages.total !== 0 ? $t('pagination.show', [pages.from, pages.to, pages.total]) :
                            $t('pagination.empty', [pages.total])
                        }}
                    </p>
                </div>
                <div class="lg:order-2 lg:col-start-2 lg:col-span-1 order-1 col-start-2 col-span-2  place-self-center">
                    <nav class="flex z-0 -space-x-px rounded-sm shadow-sm place-items-center" aria-label="Pagination">
                        <template v-for="(link, index) in pages.links" :href="link.url"
                            :key="'pagination' + link.url + index">
                            <Link :href="link.url" v-if="index === 0"
                                class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 "
                                :class="link.url ? 'hover:bg-gray-50' : 'disabled text-gray-200'">
                            <span class="sr-only">{{ $t('pagination.previous') }}</span>
                            <ChevronLeftIcon class="h-5 w-5" aria-hidden="true" />
                            </Link>
                            <Link :href="link.url" v-else-if="index === pages.links.length - 1"
                                class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500"
                                :class="link.url ? 'hover:bg-gray-50' : 'disabled text-gray-200'">
                            <span class="sr-only">{{ $t('next') }}</span>
                            <ChevronRightIcon class="h-5 w-5" aria-hidden="true" />
                            </Link>
                            <Link v-else :href="link.url" aria-current="page" v-html="link.label"
                                class="flex justify-center text-center w-8 px-3 py-2 border text-sm font-medium" :class="{
                                    'z-10 bg-indigo-50 border-color-one text-color-one': link.url && link.active,
                                    'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': link,
                                }" />
                        </template>
                    </nav>
                </div>
                <div
                    class="lg:order-3 lg:col-start-3 lg:col-span-1 order-3 col-start-3 col-span-2 place-self-end flex place-items-center gap-2 ">
                    <p class="text-sm text-gray-700 font-medium">
                        {{ $t('pagination.perPage') }}:
                    </p>
                    <select class="rounded text-sm" v-model="value">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>

                    </select>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/solid'

export default {
    components: { ChevronLeftIcon, ChevronRightIcon },
    props: {
        pages: Object,
        modelValue: Number,
    },
    emits: ['update:modelValue'],
    data() {
        return {
            value: this.modelValue,
        }
    },
    watch: {
        value() {
            this.$emit('update:modelValue', parseInt(this.value));
        },
        modelValue(newValue) {
            this.value = newValue;
        },
    }

}
</script>