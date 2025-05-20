<template>
    <AppLayout :modePublic="true" :title="$t('reactivation_token.title')" :breadCrumbs="[{
        name: 'Error'
    }]">

        <div
            class="w-full my-8 justify-center align-center flex flex-col py-8 bg-white gap-5 rounded-sm gap-y-4 px-8 shadow-md">

            <div class="text-xl"> {{ $t("reactivation_token.token_failed.headline") }} </div>
            <div>
                {{ error }}
            </div>
            <div>
                {{ $t("reactivation_token.token_failed.failed_token") }}: {{ token }}
            </div>
            <div>
                {{ $t("reactivation_token.token_failed.contact") }}
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from "@/Layouts/AppLayout.vue";

// register globally
export default {
    components: {
        AppLayout,
    },
    props: {
        token: String,
        error: String
    },
    data() {
        return {
        };
    },
};
</script>