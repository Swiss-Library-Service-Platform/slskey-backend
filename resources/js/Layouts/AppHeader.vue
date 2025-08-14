<script>

import LangSwitch from './Partial/LangSwitch.vue';
import BreadCrumb from '@/Shared/BreadCrumb'
import ProfileButton from './Partial/ProfileButton.vue';
import { defineProps, watch } from 'vue';

export default {
	components: {
		LangSwitch,
		BreadCrumb,
		ProfileButton
	},
	props: {
		title: String,
		breadCrumbs: Array,
		modePublic: {
			type: Boolean,
			default: false,
		}
	},
	
}
</script>

<template>
	<header class="z-10 bg-header-bg shadow shadow-header-bg py-2">
		<div class="mx-auto pr-8 flex flex-row gap-y-2 h-20 justify-between content-center"
			:class="{ 'Xmax-w-screen-3xl': !modePublic, 'max-w-screen-xl': modePublic }">

			<div class="flex items-center gap-x-8">
				<!-- Logo -->
				<div class="w-76 flex items-center justify-start">
					<Link class="pl-8 h-20 flex items-center" :href="modePublic ? route.path : route('activation.start')">
					<img src="/images/slskey_logo_small_white.png" class="h-9 w-auto mr-4" />
					<h2 class="text-3xl text-header-text font-extrabold">
						SLSKey
						<span v-if="$page.props.appEnv != 'production'" class="text-xs text-red-500 uppercase">Test</span>
					</h2>

					</Link>
				</div>
				<BreadCrumb :breadCrumbs="breadCrumbs" />

			</div>
			<div class="relative flex gap-4 items-center">
				<!-- Help Button -->

				<!--
				<a
					class="text-white leading-4 rounded-sm flex items-center w-full justify-center py-1.5 px-3 text-md hover:text-opacity-70"
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
