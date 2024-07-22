<template>
  <AppLayout :title="$t('activation.title')" :breadCrumbs="this.origin == 'ACTIVATION_START' ?
      [{ name: $t('activation.title'), link: '/' },
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

    <div class="my-8 flex flex-row items-start gap-x-8">

      <div
        class="justify-center align-center items-center flex flex-col py-8 bg-white gap-5 rounded-md gap-y-8 px-8 shadow-md">


        <div class="flex flex-col justify-between gap-y-8" style="min-width: 450px;">
          <!-- Workaround: for some reason tailwinds "min-w-80" is not working -->
          <!-- Selection SLSKey Code -->
          <div class="" Xv-if="this.slskeyGroups.length > 1">
            
            <label class="form-label mb-1" :for="id">
              SLSKey Gruppe:
            </label>
            <SelectActivationInput v-model="this.selectedSlskeyCode" :options="this.slskeyGroups" />
          </div>

          <TextAreaInput v-model="inputRemark" :label="$t('activation.remark_optional')" />

          <!-- Activation Button -->
          <ActionButton :disabled="!this.almaUsers || !this.selectedSlskeyCode" :loading="loading"
            @click.prevent="activate" icon="key">
            {{ $t("activation.activate") }}
          </ActionButton>
        </div>

      </div>

      <!-- Alma User Details -->
      <div class="flex flex-row flex-wrap gap-8">
        <AlmaUserDetailsShow class="shadow-md " v-for="almaUser in almaUsers" :key="almaUser.primary_id"
          :almaUser="almaUser" />
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
import AlmaUserDetailsShow from "@/Shared/AlmaUser/AlmaUserDetailsShow.vue";
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
    almaUsers: Array,
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
      Inertia.post("/activation/" + this.almaUsers[0].primary_id, {
        slskey_code: this.selectedSlskeyCode,
        remark: this.inputRemark,
        alma_user: this.almaUsers[0],
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
