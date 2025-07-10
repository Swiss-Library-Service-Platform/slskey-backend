<template>
  <div class="w-fit bg-color-alma rounded-sm p-8 flex flex-col ">

    <table class="min-w-full text-left border-separate whitespace-nowrap border-spacing-0">
      <tbody>
        <tr>
          <td>
            <img class="h-16 w-16 mb-4 self-start" src="/images/alma_logo.png" />
          </td>
          <td class="w-[17rem] pl-8 flex flex-col items-start">
            <span class="text-2xl font-semibold">{{ $t("alma_user.alma_details") }}</span>
            <span class="text-lg">{{ almaUser?.alma_iz }}</span>
          </td>
        </tr>
        <tr>
          <td class="">{{ $t("alma_user.full_name") }}:</td>
          <td class="pl-8 font-bo">{{ almaUser?.full_name }}</td>
        </tr>
        <tr>
          <td class="">{{ $t("alma_user.preferred_language") }}:</td>
          <td class="pl-8 font-bo">{{ almaUser?.preferred_language }}</td>
        </tr>
        <tr>
          <td class="">{{ $t("alma_user.email") }}:</td>
          <td class="pl-8 font-bo">{{ almaUser?.preferred_email }}</td>
        </tr>
        <tr>
          <td class="">{{ $t("slskey_user.primary_id") }}</td>
          <td class="pl-8 font-bo">{{ almaUser?.primary_id }}</td>
        </tr>
        <tr >
          <td class="pt-4 align-top">{{ $t("alma_user.user_group") }}:</td>
          <td class="pt-4 pl-8 font-bo">{{ almaUser?.user_group?.desc }}</td>
        </tr>
        <tr>
          <td class="pt-4 align-top">{{ $t("alma_user.barcodes") }}:</td>
          <td class="pt-4 pl-8 font-bo">
            <div v-if="almaUser?.barcodes && almaUser.barcodes.length">
              <span v-for="barcode in almaUser.barcodes" :key="barcode">{{ barcode }}<br /></span>
            </div>
            <div v-else class="italic text-color-blocked">-</div>
          </td>
        </tr>
        <tr >
          <td class="pt-4 align-top">{{ $t("alma_user.addresses") }}:</td>
          <td class="pt-4 pl-8 font-bo">
            <div v-if="almaUser?.addresses && almaUser.addresses.length">
              <div v-for="(address, idx) in almaUser.addresses" :key="idx" class="mb-2">
                <span class="underline" v-for="address_type in address.address_type" :key="address_type.desc">{{
                  address_type.desc }}</span><br />
                <span v-if="address.line1">{{ address.line1 }}<br /></span>
                <span v-if="address.line2">{{ address.line2 }}<br /></span>
                <span v-if="address.line3">{{ address.line3 }}<br /></span>
                <span v-if="address.line4">{{ address.line4 }}<br /></span>
                <span v-if="address.postal_code || address.city">{{ address.postal_code }} {{ address.city
                }}<br /></span>
                <span v-if="address.state_province || address.country">{{ address.state_province }} {{
                  address.country?.desc }}</span>
              </div>
            </div>
            <div v-else class="italic text-color-blocked">{{ $t("alma_user.no_address") }}</div>
          </td>
        </tr>
      </tbody>
    </table>
    <!-- show block flag if user is blocked -->
    <div v-if="almaUser?.blocks && almaUser.blocks.length"
      class="mt-5 flex flex-col rounded text-center border border-color-blocked bg-color-blocked-bg font-semibold text-color-blocked">
      {{ $t("alma_user.blocked") }}
    </div>
    <div v-if="!almaUser" class="text-color-blocked italic font-italic my-8">
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
