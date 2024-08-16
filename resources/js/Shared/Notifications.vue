<template>
  <notifications width=500 position="center" :pauseOnHover="true" />
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
          title: 'Success',
          text: flash.success,
          type: 'success',
          duration: 1000000
        });
      } else if (flash.error || this.$page.props.errors.length > 0) {
        this.$notify({
          title: 'Error',
          text: flash.error || this.$page.props.errors,
          type: 'error',
          duration: 10000
        });
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
}
</style>
