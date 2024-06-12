<template>
  <div class="w-fit bg-color-alma rounded-md px-8 py-6 flex flex-col">
    <div class="flex flex-row items-center mb-4 w-full mr-5">
      <img src="/images/alma_logo.png" class="h-16 w-16 mr-8" />
      <div class="text-2xl w-full flex justify-start mr-16">
        {{ $t("slskey_user.alma_details") }}
      </div>
      <div>
        <div v-if="loading" class="btn-spinner-black" />
      </div>
    </div>
    <div v-if="!loading">
      <div v-if="this.almaUser" class="flex flex-row">
        <div class="flex flex-col text-left mr-8">
          <span>{{ $t("slskey_user.first_name") }}:</span>
          <span>{{ $t("slskey_user.last_name") }}:</span>
          <span>{{ $t("slskey_user.preferred_language") }}:</span>
          <span>{{ $t("slskey_user.email") }}:</span>
          <span>{{ $t("slskey_user.primary_id") }}:</span>
        </div>
        <div class="flex flex-col text-left">
          <span class="font-bo"> {{ this.almaUser?.first_name }} </span>
          <span class="font-bo"> {{ this.almaUser?.last_name }} </span>
          <span class="font-bo"> {{ this.almaUser?.preferred_language }} </span>
          <span class="font-bo"> {{ this.almaUser?.preferred_email }} </span>
          <span class="font-bo"> {{ this.almaUser?.primary_id }} </span>
        </div>
      </div>
      <div v-if="!this.almaUser" class="text-color-blocked italic font-italic my-8">
        {{ $t("slskey_user.not_found") }}
      </div>
    </div>
    <div v-if="loading" class="h-12" />
    
  </div>
</template>

<script>
export default {
  components: {},
  props: {
    almaUser: Object,
    loading: Boolean
  },
  data() {
    return {};
  },
  methods: {},
};
</script>
