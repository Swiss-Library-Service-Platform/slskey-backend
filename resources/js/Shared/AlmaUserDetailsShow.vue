<template>
  <div class="w-fit bg-color-alma rounded-md p-8 flex flex-col">
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
            <span class="text-lg"> {{ this.almaUser?.alma_iz }} </span>
          </div>
        </div>
        <div class="flex flex-col text-left" v-if="this.almaUser">
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
    </div>
    <div v-if="!this.almaUser" class="text-color-blocked italic font-italic my-8">
      {{ $t("alma_user.not_found") }}
    </div>
  </div>
</template>

<script>
export default {
  components: {},
  props: {
    almaUser: Object,
  },
  data() {
    return {};
  },
  methods: {},
};
</script>
