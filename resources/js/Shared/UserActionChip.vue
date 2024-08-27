<template>
	<div class="text-center text-md rounded-md items-center flex px-2" :class="getClasses()">

		<Icon :icon="this.getStatusIcon()" class="w-5 mr-2" />
		{{ getStatus() }}
	</div>
</template>

<script>

import Icon from '@/Shared/Icon.vue';

export default {
	components: {
		Icon
	},
	props: {
		action: String,
	},
	data() {
		return {

		}
	},
	methods: {
		getStatusIcon: function () {
			switch (this.action) {
				case 'BLOCKED_ACTIVE':
				case 'BLOCKED_INACTIVE':
					return 'ban';
				case 'ACTIVATED':
				case 'REACTIVATED':
				case 'EXTENDED':
					return 'check-circle';
				case 'DEACTIVATED':
					return 'x-circle';
				case 'TOKEN_SENT':
				case 'REMINDED':
				case 'NOTIFIED':
					return 'mail';
				case 'EXPIRATION_ENABLED':
				case 'EXPIRATION_DISABLED':
					return 'clock';
				default:
					return 'x-circle';
			}
		},
		getStatus() {
			switch (this.action) {
				case 'BLOCKED_ACTIVE':
				case 'BLOCKED_INACTIVE':
					return this.$i18n.t('action_chip.blocked');
				case 'UNBLOCKED':
					return this.$i18n.t('action_chip.unblocked');
				case 'REACTIVATED':
					return this.$i18n.t('action_chip.reactivated');
				case 'EXTENDED':
					return this.$i18n.t('action_chip.extended');
				case 'ACTIVATED':
					return this.$i18n.t('action_chip.activated');
				case 'DEACTIVATED':
					return this.$i18n.t('action_chip.deactivated');
				case 'TOKEN_SENT':
					return this.$i18n.t('action_chip.token_sent');
				case 'EXPIRATION_ENABLED':
					return this.$i18n.t('action_chip.expiration_enabled');
				case 'EXPIRATION_DISABLED':
					return this.$i18n.t('action_chip.expiration_disabled');
				case 'REMINDED':
					return this.$i18n.t('action_chip.reminded');
				case 'NOTIFIED':
					return this.$i18n.t('action_chip.notified');
				default:
					return this.action;
			}
		},
		getClasses: function () {
			switch (this.action) {
				case 'BLOCKED_ACTIVE':
				case 'BLOCKED_INACTIVE':
					return 'text-color-blocked';
				case 'ACTIVATED':
				case 'REACTIVATED':
				case 'EXTENDED':
					return 'text-color-active';
				case 'DEACTIVATED':
					return 'text-color-deactivated';
				default:
					return 'text-color-deactivated';
			}
		}
	}


}
</script>