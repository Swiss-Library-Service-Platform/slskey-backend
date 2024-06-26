<template>
  <div class="w-fit bg-color-alma rounded-md px-8 py-6 flex flex-col">
    <div class="flex flex-row items-center justify-between mb-4 w-full">
      <img src="/images/alma_logo.png" class="h-16 w-16" />
      <div class="text-2xl w-full flex justify-center align-center">
        <span>
          {{ $t("alma_user.alma_details") }}
        </span>
      </div>
      <div>
        <div v-if="loading" class="btn-spinner-black" />
      </div>
    </div>
    <div v-if="!loading">
      <div v-if="this.almaUser" class="flex flex-row">
        <div class="flex flex-col text-left mr-8">
          <span>{{ $t("alma_user.full_name") }}:</span>
          <span>{{ $t("alma_user.preferred_language") }}:</span>
          <span>{{ $t("alma_user.email") }}:</span>
          <span>{{ $t("slskey_user.primary_id") }}:</span>
          <span class="mt-4">{{ $t("alma_user.user_group") }}: </span>
          <span class="mt-4">{{ $t("alma_user.addresses") }}: </span>
        </div>
        <div class="flex flex-col text-left">
          <span class="font-bo"> {{ this.almaUser?.full_name }} </span>
          <span class="font-bo"> {{ this.almaUser?.preferred_language }} </span>
          <span class="font-bo"> {{ this.almaUser?.preferred_email }} </span>
          <span class="font-bo"> {{ this.almaUser?.primary_id }} </span>
          <span class="font-bo mt-4"> {{ this.almaUser?.user_group?.desc }} </span>
          <div class="font-bo mt-4 flex flex-col" v-for="address in this.almaUser?.addresses">
            <span class="underline" v-for="address_type in address.address_type"> {{ address_type.desc }}</span>
            <span> {{ address.line1 }} </span> 
            <span> {{ address.postal_code }} {{ address.city }} </span> 
            <span> {{ address.state_province }} {{ address.country.desc }}</span>
          </div>
        </div>
      </div>
      <div v-if="!this.almaUser" class="text-color-blocked italic font-italic my-8">
        {{ $t("alma_user.not_found") }}
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
