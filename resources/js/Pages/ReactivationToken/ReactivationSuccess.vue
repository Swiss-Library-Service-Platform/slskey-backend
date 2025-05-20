<template>
  <AppLayout :modePublic="true" :title="$t('reactivation_token.title')" :breadCrumbs="[
    {
      name: slskeyGroup.data.name,
    },
  ]">
    <div class="w-full mt-5 mb-10 align-center flex flex-row py-8 bg-white gap-5 rounded-sm gap-y-4 px-8 shadow-md">
      <Icon icon="check" class="w-12 h-12 mr-2"></Icon>
      <div class="flex flex-col">
        <div class="text-xl">{{ $t("reactivation_token.token_renewed.headline") }} </div>
        <div>
          {{ $t("reactivation_token.token_success.success_1") }}
        </div>
        <div>
          {{ $t("reactivation_token.token_success.success_2") }} <b> {{ formatDate(this.expirationDate) }} </b>.
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import Icon from '@/Shared/Icon.vue';

// register globally
export default {
  components: {
    AppLayout,
    Icon
  },
  props: {
    slskeyGroup: Object,
    expirationDate: Date,
  },
  data() {
    return {};
  },
  methods: {
    formatDate(date) {
      return this.$moment(date).format("ll");
    },
  },
};
</script>
