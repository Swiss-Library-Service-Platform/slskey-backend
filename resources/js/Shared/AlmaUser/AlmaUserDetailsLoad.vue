<template>
  <div class="w-fit bg-color-alma rounded-sm p-8 flex flex-col">
    <div class="flex flex-row">
      <div class="flex flex-col text-left mr-8">
        <img class="h-16 w-16 mb-4" src="/images/alma_logo.png" />
        <span>{{ $t("alma_user.full_name") }}:</span>
        <span>{{ $t("alma_user.preferred_language") }}:</span>
        <span>{{ $t("alma_user.email") }}:</span>
        <span>{{ $t("slskey_user.primary_id") }}:</span>
        <span class="mt-4">{{ $t("alma_user.user_group") }}: </span>
        <span class="mt-4">{{ $t("alma_user.addresses") }}: </span>
      </div>
      <div class="flex flex-col text-left">
        <div class="w-60 h-16 mb-4 flex items-center justify-between"> 
          <div class="flex flex-col">
            <span class="text-2xl font-semibold"> {{ $t("alma_user.alma_details") }} </span>
            <Shimmer class="shimmer-iz" />
          </div>
        </div>
        <div class="flex flex-col text-left">
          <Shimmer class="shimmer" />
          <Shimmer class="shimmer" />
          <Shimmer class="shimmer" />
          <Shimmer class="shimmer" />
          <div class=" mt-4">
            <Shimmer class="shimmer" />
          </div>
          <div class="font-bo mt-4 flex flex-col">
            <Shimmer class="shimmer" />
            <Shimmer class="shimmer" />
            <Shimmer class="shimmer" />
            <Shimmer class="shimmer" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import Shimmer from "vue3-loading-shimmer";

export default {
  components: {
    Shimmer
  },
  props: {
  },
  data() {
    return {};
  },
  methods: {},
};
</script>
<style>
.shimmer-iz {
  height: 24px;
  margin-top: 2px;
  margin-bottom: 2px;
  width: 100%;
  border-radius: 5px;
}
.shimmer {
  height: 20px;
  margin-top: 2px;
  margin-bottom: 2px;
  width: 100%;
  border-radius: 5px;
}
</style>
