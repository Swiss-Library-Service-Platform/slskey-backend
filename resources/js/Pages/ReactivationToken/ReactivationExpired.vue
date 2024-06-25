<template>
    <AppLayout :modePublic="true" :title="$t('reactivation_token.title')" :breadCrumbs="[{
        name: 'Token Expired'
    }]">
        <div
            class="w-full mt-5 mb-10 justify-center align-center flex flex-col py-8 bg-white gap-5 rounded-md gap-y-4 px-8 shadow-md">

            <div class="text-xl"> {{ $t("reactivation_token.token_expired.headline") }} </div>
            <div>
                {{ $t("reactivation_token.token_expired.token_expired_1") }}
            </div>
            <div>
                {{ $t("reactivation_token.token_expired.token_expired_1") }}
            </div>
            <div>
                <a class="text-blue-800" :href="renewTokenLink">
                    {{ $t("reactivation_token.token_expired.token_expired_link_1") }}
                </a> {{ $t("reactivation_token.token_expired.token_expired_link_2") }} {{ activationMail }}.
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Inertia } from "@inertiajs/inertia";

// register globally
export default {
    components: {
        AppLayout,
    },
    props: {
        renewTokenLink: String,
        activationMail: String
    },
    data() {
        return {
        };
    },
};
</script>