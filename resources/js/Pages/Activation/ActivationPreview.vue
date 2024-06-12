<template>
  <AppLayout :title="$t('activation.title')" :breadCrumbs="
    this.origin == 'ACTIVATION_START' ?
    [{ name: $t('activation.title'), link: '/activate'},
        { name: $t('activation.activate_new') }]
      :
      [{ name: $t('user_management.title'), link: '/users' },
        slskeyUser
          ? {
            name: slskeyUser.data.full_name,
            link: '/users/' + slskeyUser.data.primary_id,
          }
          : {},
        { name: $t('activation.activate') }]
  ">

    <div class="mt-5 mb-10 flex flex-row items-start gap-x-8">

      <div
        class="w-36remXXX justify-center align-center items-center flex flex-col py-8 bg-white gap-5 rounded-md gap-y-8 px-8 shadow-md">


        <div class="w-32remXXX flex flex-col justify-between gap-y-8">

          <!-- Selection SLSKey Code -->
          <div class="text-2xl w-full flex justify-start">
            {{ $t('slskey_groups.title') }}
          </div>

          <div Xv-if="this.slskeyGroups.length > 1">
            <SelectActivationInput v-model="this.selectedSlskeyCode" :options="this.slskeyGroups" />
          </div>

          <TextAreaInput v-model="inputRemark" :label="$t('user_management.remark_optional')" />

          <!-- Activation Button -->
          <ActionButton :disabled="!this.almaUser || !this.selectedSlskeyCode" :loading="loading"
            @click.prevent="activate" icon="key">
            {{ $t("activation.activate") }}
          </ActionButton>
        </div>

      </div>

      <div class="flex flex-col">
        <!--  SLSKey USer Details

       
        <div v-if="slskeyUser"
          class="flex mt-5 rounded-md bg-white p-4 w-full justify-between shadow border shadow-md">

          <div class="flex flex-row items-center ">
            <Icon icon="check-circle" class="w-4 mr-1"></Icon>

            User found in SLSKey
          </div>

          <a :href="'/users/' + slskeyUser.primary_id" class="flex flex-row items-center underline">
            <Icon icon="user" class="w-4"></Icon>
            Details
          </a>
        </div>
        -->

        <!-- Alma User Details -->
        
        <AlmaUserDetailsShow class="shadow-md " :almaUser="almaUser" />
     
      </div>
    </div>

  </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import TextInput from "../../Shared/Forms/TextInput.vue";
import ActionButton from "../../Shared/Buttons/ActionButton.vue";
import Listbox from "../../Shared/Listbox.vue";
import { Inertia } from "@inertiajs/inertia";
import SelectActivationInput from "@/Shared/Forms/SelectActivationInput.vue";
import AlmaUserDetailsShow from "@/Shared/AlmaUserDetailsShow.vue";
import TextAreaInput from "@/Shared/Forms/TextAreaInput.vue";
import Icon from "@/Shared/Icon.vue";

// register globally
export default {
  components: {
    AppLayout,
    SelectActivationInput,
    TextInput,
    ActionButton,
    Listbox,
    Inertia,
    AlmaUserDetailsShow,
    TextAreaInput,
    Icon
  },
  props: {
    identifier: String,
    almaUser: Object,
    slskeyGroups: Array,
    slskeyUser: Object,
    preselectedSlskeyCode: String,
    origin: String
  },
  data() {
    return {
      loading: false,
      selectedSlskeyCode: this.preselectedSlskeyCode,
      inputRemark: this.getCurrentRemark(this.preselectedSlskeyCode)
    };
  },
  methods: {
    activate: function () {
      this.loading = true;
      Inertia.post("/activation/" + this.almaUser.primary_id, {
        slskey_code: this.selectedSlskeyCode,
        remark: this.inputRemark,
        alma_user: this.almaUser,
      }, {
        onSuccess: () => {
          this.loading = false;
        },
      })
    },
    getCurrentRemark: function (selectedSlskeyCode) {
      const slskeyGroup = this.slskeyGroups.find((group) => group.value === selectedSlskeyCode);
      if (slskeyGroup && slskeyGroup.activation && slskeyGroup.activation.remark) {
        return slskeyGroup.activation.remark;
      }
      return "";
    }
  },
  watch: {
    selectedSlskeyCode: function (newVal) {
      // find remark from from slskey activation of currently selected group and if found, set it as inputRemark
      this.inputRemark = this.getCurrentRemark(newVal);
    }
  },
};
</script>
