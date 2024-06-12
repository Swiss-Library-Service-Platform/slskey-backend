<template>
	<div class="text-center text-md rounded-md p-1 w-32 flex items-center px-2" :class="getClasses()">
		
		<Icon :icon="this.getStatusIcon()" class="w-6" />
		<div class="flex w-full justify-center">
			{{ getStatus() }}
		</div>
	</div>
</template>

<script>

import Icon from '@/Shared/Icon.vue';

export default {
	components: {
		Icon
	},
	props: {
		activation: Object,
	},
	data() {
		return {

		}
	},
	methods: {
		getStatusIcon: function () {
			return this.activation?.blocked ?
				'ban' :
				(this.activation?.activated ?
					'check-circle' :
					'x-circle');
		},
		getStatus() {
			return !this.activation ?
			'No Status'
			: this.activation?.blocked ?
				'Blocked' :
				(this.activation?.activated ?
					'Activated' :
					'Inactive');
		},
		getClasses: function () {
			return this.activation?.blocked ?
				'text-color-blocked border border-color-blocked bg-color-blocked-bg' :
				(this.activation?.activated ?
				'text-color-active border border-color-active bg-color-active-bg ' :
				'text-color-deactivated border border-color-deactivated bg-color-deactivated-bg');
		}
	}


}
</script>