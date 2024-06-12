<script setup >

import { Head, Link } from '@inertiajs/inertia-vue3';

import NavLink from '../Shared/Buttons/NavLink.vue';
import Icon from '../Shared/Icon.vue';


defineProps({
	showNavbar: Boolean
});

</script>

<template>
	<nav v-if="showNavbar" class="w-64 min-w-fit mr-8">
		<!-- Primary Navigation Menu -->
		<div class="h-full">
			<div class="h-full flex flex-col ">

				<!-- Navigation Links -->
				<div class="h-full my-4 flex flex-col ">
					<!-- pl-2 sm:pl-4 lg:pl-6 pr-4 sm:pr-6 lg:pr-10  -->

					<div class="flex flex-col  pb-4">

						<!-- Activation -->
						<NavLink icon="key" :href="route('activation.start')"
							:active="route().current('activation.start') || route().current('activation.preview')">
							{{ $t('activation.title') }}
						</NavLink>

						<!-- User Management -->
						<NavLink icon="user" :href="route('users.index')"
							:active="route().current('users.index') || route().current('users.show')">
							{{ $t('user_management.title') }}
						</NavLink>

						<!-- Reporting -->
						<NavLink icon="report" :href="route('reporting.index')"
							:active="route().current('reporting.index') || route().current('reporting.show') || route().current('reporting.select')">
							{{ $t('reporting.title') }}
						</NavLink>

						<!-- Logout -->
						<!--
						<NavLink icon="logout" :href="route('logout_eduid')" :active="route().current('logout_eduid')">
							{{ $t('logout') }}
						</NavLink>
					-->

					</div>

					<div v-if="$page.props.slskeyadmin" class="flex flex-col pt-4 ">

						<div class="ml-8 mb-4 text-color-one">
							<Icon icon="lock-closed" class="w-3 h-3 inline-block mr-2" />
							SLSP area
						</div>
						<!-- SLSKey Groups -->
						<NavLink icon="book-open" :href="route('admin.history.index')"
							:active="route().current('admin.history.index') || route().current('admin.history.show')">
							{{ $t('slskey_histories.title') }}
						</NavLink>

						<!-- Admin Users -->
						<NavLink icon="user-circle" :href="route('admin.users.index')"
							:active="route().current('admin.users.index')  || route().current('admin.users.show') || route().current('admin.users.create')">
							{{ $t('admin_users.title') }}
						</NavLink>

						<!-- SLSKey Groups -->
						<NavLink icon="key" :href="route('admin.groups.index')"
							:active="route().current('admin.groups.index') || route().current('admin.groups.show') || route().current('admin.groups.create')">
							{{ $t('slskey_groups.title') }}
						</NavLink>

						<!-- Switch Groups -->
						<NavLink icon="link" :href="route('admin.switchgroups.index')"
							:active="route().current('admin.switchgroups.index') || route().current('admin.switchgroups.show') || route().current('admin.switchgroups.create')">
							{{ $t('switch_groups.title') }}
						</NavLink>

						<!-- Publishers
						<NavLink icon="globe-alt" :href="route('admin.publishers.index')"
							:active="route().current('admin.publishers.index') || route().current('admin.publishers.show') || route().current('admin.publishers.create')">
							{{ $t('publishers.title') }}
						</NavLink>
						-->
						
						<!-- Mass Import -->
						<NavLink icon="upload" :href="route('admin.import.index')"
							:active="route().current('admin.import.index') || route().current('admin.import.preview') || route().current('admin/import/store') ">
							{{ $t('admin_import.title') }}
						</NavLink>

						<!-- edu-ID Status --> 
						<NavLink icon="information-circle" href="https://status.eduid.ch/" :isExternal="true">
							{{ $t('eduid_status') }}
						</NavLink>
					</div>

				</div>

			</div>
		</div>
	</nav>
</template>
