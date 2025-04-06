<template>
  <notifications position="center" width=500 :pauseOnHover="true" />
</template>

<script>

export default {

  mounted() {
    /*
    if (this.$page.props.flash.success || this.$page.props.flash.error) {
      console.log('flash', this.$page.props.flash);
      this.handleFlash(this.$page.props.flash);
    } else 
    if (this.$page.props.errors && Object.keys(this.$page.props.errors).length > 0) {
      this.handleErrors(this.$page.props.errors);
    }
    */
  },
  watch: {
    // Flash messages
    '$page.props.flash': {
      handler(newVal, oldVal) {
        this.handleFlash(newVal);
      },
      deep: true,
      immediate: true
    },
    // Input errors / validations
    '$page.props.errors': {
      handler(newVal, oldVal) {
        if (newVal && Object.keys(newVal).length > 0) {
          this.handleErrors(newVal);
        }
      },
      deep: true,
      immediate: true
    }
  },
  methods: {
    handleFlash(flash) {
      if (flash.success) {
        setTimeout(() => {
          this.showSuccessNotification(flash.success);
        }, 1); // timeout is a hack to make sure the notification is shown after the page is rendered. Dont ask me why.
      } else if (flash.error) {
        setTimeout(() => {
          this.showErrorNotification(flash.error);
        }, 1); // timeout is a hack to make sure the notification is shown after the page is rendered. Dont ask me why.
      } else if (flash.info) {
        setTimeout(() => {
          this.showInfoNotification(flash.info);
        }, 1); // timeout is a hack to make sure the notification is shown after the page is rendered. Dont ask me why.
      }
    },
    handleErrors(errors) {
      Object.keys(errors).forEach(key => {
        this.showErrorNotification(errors[key]);
      });
    },
    showInfoNotification(message) {
      this.$notify({
        title: this.$t('notifications.info'),
        text: message,
        type: 'info',
        duration: 100000
      });
    },
    showErrorNotification(message) {
      this.$notify({
        title: this.$t('notifications.error'),
        text: message,
        type: 'error',
        duration: 10000
      });
    },
    showSuccessNotification(message) {
      this.$notify({
        title: this.$t('notifications.success'),
        text: message,
        type: 'success',
        duration: 10000
      });
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
    border-radius: 4px;
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

    --tw-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1),
    0 2px 4px -2px rgb(0 0 0 / 0.1);
    --tw-shadow-colored: 0 4px 6px -1px var(--tw-shadow-color),
    0 2px 4px -2px var(--tw-shadow-color);
    box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000),
    var(--tw-ring-shadow, 0 0 #0000),
    var(--tw-shadow);
  }
}
</style>
