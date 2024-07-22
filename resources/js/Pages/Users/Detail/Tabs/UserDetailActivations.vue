<template>
  <ActionButton class="text-lg w-fit mb-8 px-8 shadow" @click.prevent="activate()" icon="key" :loading="loading">
      {{ $t('user_management.new_activation') }}
  </ActionButton>
  <div class="bg-white rounded-md shadow w-full overflow-x-auto">
    <table class="table-auto w-full divide-y divide-gray-table rounded-md">
      <tbody class="divide-y divide-gray-table">
        <template v-if="slskeyUser.slskey_activations.length > 0">
          <!-- SLSKey Group Name -->
          <tr class="h-20">
            <td class="py-4 px-8 text-left whitespace-nowrap font-bold pr-14 w-auto">
              <div class="flex flex-row items-center">
                <Icon icon="users" class="h-4 w-4 mr-2"></Icon>
                {{ $t("slskey_groups.slskey_code_description") }}:
              </div>
            </td>
            <template v-for="(activation, index) in slskeyUser.slskey_activations" :key="'user' + activation.id">
              <td class="px-6 py-4" :class="{ 'w-full': index == slskeyUser.slskey_activations.length - 1 }">
                <div class="flex flex-row">
                  <SlskeyGroupNameAndIcon :workflow="activation.slskey_group.workflow"
                    :slskeyGroupName="activation.slskey_group.name" />
                </div>
              </td>
            </template>
          </tr>
          <!-- Status -->
          <tr class="h-20">
            <td class="py-4 px-8 text-left whitespace-nowrap font-bold pr-14">
              <div class="flex flex-row items-center">
                <Icon icon="information-circle" class="h-4 w-4 mr-2"></Icon>
                {{ $t("user_management.status") }}:
              </div>
            </td>
            <template v-for="(activation, index) in slskeyUser.slskey_activations" :key="'user' + activation.id">
              <td class="px-6 py-4 ">
                <div class="flex flex-col gap-y-1">
                  <div class="flex flex-row gap-x-2">
                    <!-- Status Chip-->
                    <UserStatusChip :activation="activation" />
                    <!-- Edit Button -->
                    <JetDropdown align="right" width="48">
                      <template #trigger>
                        <span class="inline-flex rounded-md">
                          <DefaultIconButton :loading="activation.loading" class="bg-white py-1 text-color-header-bg"
                            icon="pencil" tooltip="Actions" />
                        </span>
                      </template>
                      <!-- Dropdown links -->
                      <template #content>

                        <!-- Disable Expiration -->
                        <DefaultConfirmDropdownLink class="bg-color-deactivated py-1"
                          v-if="activation.activated && !activation.expiration_disabled" :activation="activation"
                          :enterRemark="false" :confirmText="$t('user_management.confirm_disable_expiration')"
                          @confirmed="disableExpiration" :disabled="activation.slskey_group.workflow === 'Webhook'">
                          <Icon icon="clock" class="h-4 w-4"></Icon>
                          {{ $t('user_management.disable_expiration') }}
                        </DefaultConfirmDropdownLink>

                        <!-- Enable Expiration -->
                        <DefaultConfirmDropdownLink class="bg-color-deactivated py-1"
                          v-if="activation.activated && !!activation.expiration_disabled" :activation="activation"
                          :enterRemark="false" :confirmText="$t('user_management.confirm_enable_expiration')"
                          :confirmText2="$t('user_management.confirm_enable_expiration_2') + activation.slskey_group.days_activation_duration + $t('user_management.confirm_enable_expiration_3')"
                          @confirmed="enableExpiration" :disabled="activation.slskey_group.workflow === 'Webhook'">
                          <Icon icon="clock-solid" class="h-4 w-4"></Icon>
                          {{ $t('user_management.enable_expiration') }}
                        </DefaultConfirmDropdownLink>

                        <!-- Deactivate User -->
                        <DefaultConfirmDropdownLink v-if="activation.activated" class="bg-color-deactivated py-1"
                          :activation="activation" :confirmText="$t('user_management.confirm_deactivate_user')"
                          @confirmed="deactivate" :disabled="activation.slskey_group.workflow === 'Webhook'">
                          <Icon icon="x" class="h-4 w-4"></Icon>
                          {{ $t('user_management.deactivate') }}
                        </DefaultConfirmDropdownLink>

                        <!-- Block User -->
                        <DefaultConfirmDropdownLink v-if="!activation.blocked" class="bg-color-blocked py-1"
                          :activation="activation" :confirmText="$t('user_management.confirm_block_user')"
                          @confirmed="block" :confirmText2="$t('user_management.confirm_block_user_2')">
                          <Icon icon="ban" class="h-4 w-4"></Icon>
                          {{ $t('user_management.block') }}
                        </DefaultConfirmDropdownLink>

                        <!-- Unblock User -->
                        <DefaultConfirmDropdownLink v-if="!!activation.blocked" class="bg-color-blocked py-1"
                          :activation="activation" :confirmText="$t('user_management.confirm_unblock_user')"
                          @confirmed="unblock">
                          <Icon icon="ban" class="h-4 w-4"></Icon>
                          {{ $t('user_management.unblock') }}
                        </DefaultConfirmDropdownLink>
                      </template>
                    </JetDropdown>
                  </div>
                  <!-- Action Date -->
                  <div class="text-xs italic">
                    {{ activation?.activation_date ? 'activated on ' + formatDate(activation.activation_date) : '' }}
                    {{ activation?.deactivation_date ? 'deactivated on ' + formatDate(activation.deactivation_date) : ''
                    }}
                    {{ activation?.blocked_date ? 'blocked on ' + formatDate(activation.blocked_date) : '' }}
                  </div>
                </div>
              </td>
            </template>
          </tr>
          <!-- Activation Mail -->
          <tr v-if="isWebhookMailActivation" class="h-20">
            <td class="py-4 px-8 text-left whitespace-nowrap font-bold pr-14">
              <div class="flex flex-row items-center">
                <Icon icon="mail" class="h-4 w-4 mr-2"></Icon>
                {{ $t("user_management.webhook_activation_mail") }}:
              </div>
            </td>
            <template v-for="(activation, index) in slskeyUser.slskey_activations" :key="'user' + activation.id">
              <td class="px-6 py-4">
                <div v-if="activation.slskey_group.webhook_mail_activation && activation.webhook_activation_mail">
                  {{ activation.webhook_activation_mail }}
                </div>
                <div v-if="activation.slskey_group.webhook_mail_activation && !activation.webhook_activation_mail"
                  class="italic text-gray-disabled flex flex-row items-center">
                  <Icon icon="mail" class="h-4 w-4 mr-2"></Icon>
                  {{ $t("user_management.no_webhook_activation_mail") }}
                </div>
              </td>
            </template>
          </tr>
          <!-- 
          <tr class="h-20">
            <td class="py-4 px-8 text-left whitespace-nowrap font-bold pr-14">
              <div class="flex flex-row items-center">
                <Icon icon="key" class="h-4 w-4 mr-2"></Icon>
                {{ $t("user_management.activation_date") }}:
              </div>
            </td>
            <template v-for="(activation, index) in slskeyUser.slskey_activations" :key="'user' + activation.id">
              <td class="px-6 py-4">
                <div v-if="activation.activation_date">
                  {{ formatDate(activation.activation_date) }}
                </div>
              </td>
            </template>
          </tr>
           -->
          <!-- Expiration Date -->
          <tr class="h-20">
            <td class="py-4 px-8 text-left whitespace-nowrap font-bold pr-14">
              <div class="flex flex-row items-center">
                <Icon icon="clock" class="h-4 w-4 mr-2"></Icon>
                {{ $t("user_management.expiration_date") }}:
              </div>
            </td>
            <template v-for="(activation, index) in slskeyUser.slskey_activations" :key="'user' + activation.id">

              <td class="px-6 py-4">
                <div v-if="activation.activated && activation.expiration_disabled" class="italic text-gray-disabled">
                  {{ $t("user_management.no_expiry_deactivated") }}
                </div>
                <div v-if="activation.activated && !activation.expiration_date && !activation.expiration_disabled"
                  class="italic text-gray-disabled">
                  {{ $t("user_management.no_expiry_webhook") }}
                </div>
                <div v-else-if="activation.expiration_date && !activation.expiration_disabled">
                  {{ formatDate(activation.expiration_date) }}
                </div>
              </td>
            </template>
          </tr>
          <!-- Remark -->
          <tr class="h-20">
            <td class="py-4 px-8 text-left whitespace-nowrap font-bold pr-14">
              <div class="flex flex-row items-center">
                <Icon icon="annotation" class="h-4 w-4 mr-2"></Icon>
                {{ $t("user_management.remark") }}:
              </div>
            </td>
            <template v-for="(activation, index) in slskeyUser.slskey_activations" :key="'user' + activation.id">
              <td class="px-6 py-4 break-all">
                <div v-if="activation.remark">
                  {{ activation.remark }}
                </div>
                <div v-else class="italic text-gray-disabled">
                  {{ $t("user_management.no_remark") }}
                </div>
              </td>
            </template>
          </tr>
          <!-- Switch Status -->
          <tr class="h-20" v-if="$page.props.slskeyadmin">
            <td class="py-4 px-8 text-left whitespace-nowrap font-bold">
              <div class="flex flex-row items-center">
                <Icon icon="link" class="h-4 w-4 mr-2"></Icon>
                {{ $t("user_management.switch_status") }}:
              </div>
            </td>
            <template v-for="(activation, index) in slskeyUser.slskey_activations" :key="'user' + activation.id">
              <td class="px-6 py-4">
                <div class=" flex flex-row items-center gap-x-2">
                  <div v-if="activation.switchLoading" class="btn-spinner-black h-4 w-4 mt-1" />
                  <button @click="goToSwitchAdminGui()" v-else>
                    <div v-if="!activation.switchStatus" class="flex flex-row items-center gap-4">
                      <div class="bg-color-deactivated-bg rounded-full h-4 w-4" />
                      {{ $t("user_management.switch_status_inactive") }}
                    </div>
                    <div v-else class="text-color-active flex flex-row items-center gap-4">
                      <div class="bg-color-active rounded-full h-4 w-4" />
                      {{ $t("user_management.switch_status_active") }}
                    </div>
                  </button>
                </div>

              </td>
            </template>
          </tr>
        </template>
      </tbody>
    </table>
  </div>

</template>


<script>
import UserStatusChip from "@/Shared/UserStatusChip.vue";
import { Inertia } from "@inertiajs/inertia";
import ConfirmDialog from "../../../../Shared/ConfirmDialog.vue";
import DefaultIconButton from "@/Shared/Buttons/DefaultIconButton.vue";
import JetDropdown from '@/Jetstream/Dropdown.vue';
import JetDropdownLink from '@/Jetstream/DropdownLink.vue';
import DefaultConfirmButton from "@/Shared/Buttons/DefaultConfirmButton.vue";
import DefaultConfirmDropdownLink from "@/Shared/Buttons/DefaultConfirmDropdownLink.vue";
import Icon from "@/Shared/Icon.vue";
import axios from 'axios';
import LetterIcon from "../../../../Shared/LetterIcon.vue";
import SlskeyGroupNameAndIcon from "../../../../Shared/SlskeyGroupNameAndIcon.vue";
import ActionButton from "@/Shared/Buttons/ActionButton.vue";

export default {
  components: {
    ActionButton,
    Inertia,
    UserStatusChip,
    DefaultIconButton,
    ConfirmDialog,
    DefaultConfirmButton,
    JetDropdown,
    JetDropdownLink,
    DefaultConfirmDropdownLink,
    Icon,
    LetterIcon,
    SlskeyGroupNameAndIcon
  },
  props: {
    slskeyUser: Object,
    isWebhookMailActivation: Boolean
  },
  data() {
    return {
      loading: false,
    };
  },
  mounted() {
    if (this.$page.props.slskeyadmin) {
      this.getSwitchStatus();
    }
  },
  methods: {
    activate: function () {
      this.loading = true;
      Inertia.get("/activation/" + this.slskeyUser.primary_id);
    },
    getSwitchStatus: async function () {
      this.slskeyUser.slskey_activations.forEach(async (activation) => {
        activation.switchLoading = true;
        const response = await axios.get(`/users/switch/${this.slskeyUser.primary_id}/${activation.slskey_group.slskey_code}`);
        activation.switchStatus = response.data.status;
        activation.switchLoading = false;
      });
    },
    goToSwitchAdminGui() {
      window.open(`https://eduid.ch/sg/gui/?user`, '_blank');
    },
    deactivate: function (activation, remark) {
      activation.loading = true;
      Inertia.delete("/activation/" + this.slskeyUser.primary_id, {
        data: {
          slskey_code: activation.slskey_group.slskey_code,
          remark: remark
        },
        onSuccess: () => {
          activation.loading = false;
          this.getSwitchStatus();
        },
      });
    },
    block: function (activation, remark) {
      activation.loading = true;
      Inertia.post("/activation/" + this.slskeyUser.primary_id + "/block", {
        slskey_code: activation.slskey_group.slskey_code,
        remark: remark
      }, {
        onSuccess: () => {
          activation.loading = false;
          this.getSwitchStatus();
        },
      });
    },
    unblock: function (activation, remark) {
      activation.loading = true;
      Inertia.delete("/activation/" + this.slskeyUser.primary_id + "/block", {
        data: {
          slskey_code: activation.slskey_group.slskey_code,
          remark: remark
        },
        onSuccess: () => {
          activation.loading = false;
          this.getSwitchStatus();
        },
      });

    },
    disableExpiration: function (activation, remark) {
      activation.loading = true;
      Inertia.post("/activation/" + this.slskeyUser.primary_id + "/expiration", {
        slskey_code: activation.slskey_group.slskey_code,
      }, {
        onSuccess: () => {
          activation.loading = false;
          this.getSwitchStatus();
        },
      });
    },
    enableExpiration: function (activation, remark) {
      activation.loading = true;
      Inertia.delete("/activation/" + this.slskeyUser.primary_id + "/expiration", {
        data: {
          slskey_code: activation.slskey_group.slskey_code,
        },
        onSuccess: () => {
          activation.loading = false;
          this.getSwitchStatus();
        },
      });
    },
    formatDate(date) {
      return date ? this.$moment(date).format('ll') : '';
    },
  },
};
</script>
