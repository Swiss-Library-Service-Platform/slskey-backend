<template>
    <Menu as="div" class="relative inline-block text-left">
        <div>
            <MenuButton v-on:pointerenter="isShowing = true" v-on:pointerleave="isShowing = false"
                class="gtouch-none text-white leading-4 rounded-sm flex items-center w-full justify-center py-1.5 px-3 text-md hover:text-opacity-70">
                <Icon  icon="chevron-down" class="w-6 h-6 mr-2"></Icon>
                {{ this.$i18n.locale }}
            </MenuButton>
        </div>

        <transition v-show="isShowing" v-on:pointerenter="isShowing = true" v-on:pointerleave="isShowing = false"
            class="touch-none" enter-active-class="transition duration-200 ease-out"
            enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-200 ease-in" leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0">
            <MenuItems static
                class="absolute right-0 mt-1 origin-top-right divide-y divide-gray-100  rounded-sm bg-white shadow-lg border-gray-table ring-opacity-5 focus:outline-none">
                <div class="px-1 py-1">
                    <MenuItem class=" hover:bg-one-1 hover:text-one" v-slot="{ active }"
                        v-for="(lang, i) in languages" :key="`Lang${i}`">

                    <button @click="selectLang(lang.short)" class="flex items-center gap-2" :class="[
                        active ? 'bg-violet-500 text-white' : 'text-gray-900',
                        'group flex w-full items-center px-2 py-2 text-sm',]">
                        <div v-if="lang.short === this.$i18n.locale" class="bg-one rounded-full h-3 w-3" />
                        <div v-else class="bg-white rounded-full h-3 w-3" />
                        {{ $t(lang.long) }}
                    </button>

                    </MenuItem>
                </div>
            </MenuItems>
        </transition>
    </Menu>
</template>

<script>
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue';
import Icon from '../../Shared/Icon.vue';
import axios from 'axios'

export default {
    components: { Menu, MenuButton, MenuItems, MenuItem, Icon },
    data() {
        return {
            isShowing: false,
            languages: [
                { long: this.$i18n.t('languages.german'), short: 'de' },
                { long: this.$i18n.t('languages.french'), short: 'fr' },
                { long: this.$i18n.t('languages.italian'), short: 'it' },
                { long: this.$i18n.t('languages.english'), short: 'en' }
            ]
        }
    },
    mounted() {
        this.$i18n.locale = this.$page.props.locale;
        this.$moment.locale(this.$page.props.locale);
    },
    methods: {
        selectLang(lang) {
            this.$i18n.locale = lang;
            this.$moment.locale(lang);
            axios.put(route('changeLocale', lang));
            this.isShowing = false;
        }
    },
}
</script>
