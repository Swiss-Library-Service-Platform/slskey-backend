<template>
    <Menu as="div" class="relative inline-block text-left">
        <div>
            <!-- Old Menu Button with Logout Menu -->
            <!-- 
            <MenuButton v-on:pointerenter="isShowing = true" v-on:pointerleave="isShowing = false"
                class="gtouch-none whitespace-nowrap text-white leading-4 rounded-sm flex items-center w-full justify-center py-1.5 px-3 text-md hover:text-opacity-70">
                <Icon icon="user-circle" class="w-6 mr-2"></Icon>

                {{ $page.props.auth.user?.display_name }}

            </MenuButton>
            -->

            <!-- New Profile without Logout Menu -->
            <MenuButton 
                class="gtouch-none cursor-default whitespace-nowrap text-white leading-4 rounded-sm flex items-center w-full justify-center py-1.5 px-3 text-md">
                <Icon icon="user-circle" class="w-6 mr-2"></Icon>

                {{ $page.props.auth.user?.display_name }}

            </MenuButton>

        </div>

        <transition v-show="isShowing" v-on:pointerenter="isShowing = true" v-on:pointerleave="isShowing = false"
            class="touch-none" enter-active-class="transition duration-200 ease-out"
            enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-200 ease-in" leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0">
            <MenuItems static
                class="w-36 absolute right-0 mt-1 origin-top-right divide-y divide-gray-100  rounded-sm bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                <div class="px-1 py-1 ">
                    <MenuItem class=" hover:bg-color-one-1 hover:text-color-one" v-slot="{ active }">
                        <form method="POST" @submit.prevent="logout">
							<DropdownLink href="/logout/eduid">
								<LogoutIcon class="h-4 w-4 mr-2" />
								<div class="py-2">
									{{ $t('app_header.logout') }}
								</div>
							</DropdownLink>
						</form>

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
import { LogoutIcon } from '@heroicons/vue/solid';
import DropdownLink from '@/Shared/DropdownLink.vue';

export default {
    components: { Menu, MenuButton, MenuItems, MenuItem, Icon, LogoutIcon, DropdownLink },
    data() {
        return {
            isShowing: false,
        }
    },
}
</script>
