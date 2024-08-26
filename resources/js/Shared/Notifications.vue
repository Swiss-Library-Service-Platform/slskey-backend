<template>
  <notifications position="center" width=500 :pauseOnHover="true" />
</template>

<script>

export default {
  watch: {
    '$page.props.flash': {
      handler(newVal, oldVal) {
        this.handleFlash(newVal);
      },
      deep: true,
      immediate: true
    },
  },
  mounted() {
    if (this.$page.props.flash.success) {
      this.handleFlash(this.$page.props.flash);
    }
  },
  methods: {
    handleFlash(flash) {
      if (flash.success) {
        this.$notify({
          title: this.$t('notifications.success'),
          text: flash.success,
          type: 'success',
          duration: 100000
        });
      } else if (flash.error || this.$page.props.errors.length > 0) {
        setTimeout(() => {
          this.$notify({
            title: this.$t('notifications.error'),
            text: flash.error || this.$page.props.errors,
            type: 'error',
            duration: 100000
          });
        }, 1); // FIXME: this is a hack to make sure the notification is shown after the page is rendered. Dont ask me why.
      }
    }
  }
}
</script>
<style>
.vue-notification {
  font-size: 1.2rem;
}

.vue-notification-group {

  top: 0.5rem;
  .vue-notification {
    border-left-width: 10px;
    border-radius: 10px;
    padding-left: 1rem;
    padding-right: 1rem;

    &.success {
      background-color: var(--color-active-bg);
      border-color: var(--color-active);
      color: #587943;
      .notification-title {
        color: var(--color-active);
      }
    }

    &.error {
      background-color: var(--color-blocked-bg);
      border-color: var(--color-blocked);
      color: #8a1f11;
      .notification-title {
        color: var(--color-blocked);
      }
    }

    --tw-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --tw-shadow-colored: 0 4px 6px -1px var(--tw-shadow-color), 0 2px 4px -2px var(--tw-shadow-color);
    box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
  }
}
</style>
