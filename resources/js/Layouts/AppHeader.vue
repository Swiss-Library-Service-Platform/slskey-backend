<script setup>

import JetDropdown from '@/Jetstream/Dropdown.vue';
import JetDropdownLink from '@/Jetstream/DropdownLink.vue';
import LangSwitch from './Partial/LangSwitch.vue';
import BreadCrumb from '@/Shared/BreadCrumb'
import FlashMessages from '@/Shared/FlashMessages.vue';
import Icon from '@/Shared/Icon.vue';
import { LogoutIcon } from '@heroicons/vue/solid';
import ProfileButton from './Partial/ProfileButton.vue';
import MaterialIcon from '../Shared/MaterialIcon.vue';

defineProps({
	title: String,
	toggleNavbar: Function,
	breadCrumbs: Array,
	modePublic: {
		type: Boolean,
		default: false,
	}
});
</script>

<template>
	<header class="bg-color-header-bg shadow shadow-color-header-bg py-2 mb-2">
		<FlashMessages></FlashMessages>
		<div class="mx-auto px-8 pr-16 flex flex-row gap-y-2 h-20 justify-between content-center"
			:class="{ 'max-w-screen-3xl': !modePublic, 'max-w-screen-xl': modePublic }">

			<div class="flex items-center">
				<!-- Logo -->
				<div class="mr-4 bg-color-header-bg flex w-64 items-center justify-start">
					<Link class="h-20 flex items-center" :href="modePublic ? route.path : route('activation.start')">
					<img src="/images/slskey_logo_small_white.png" class="h-9 w-auto mr-4" />
					<h2 class="text-3xl text-color-header-text font-extrabold">
						SLSKey
					</h2>

					</Link>
				</div>
				<!--
				<button @click="toggleNavbar" class="text-sm text-gray-400 bg-none mr-4">
					<MenuIcon class="text-black w-6 h-6"></MenuIcon>
				</button>
				-->
				<BreadCrumb :breadCrumbs="breadCrumbs" />

			</div>
			<div class="relative flex gap-4 items-center">
				<!-- Help Button -->

				<!--
				<a
					class="text-white leading-4 rounded-md flex items-center w-full justify-center py-1.5 px-3 text-md hover:text-opacity-70"
					:href="$page.props.helpUrl" target="_blank" rel="noopener noreferrer">
					<MaterialIcon class="mr-2" icon="help" size="18" />
					{{ $t('app_header.help') }}
			</a>
			-->
				<!-- Language Switch  -->
				<LangSwitch v-if="!modePublic"></LangSwitch>
				<!-- Profile Button -->
				<ProfileButton v-if="!modePublic"></ProfileButton>
			</div>
		</div>

	</header>
</template>
