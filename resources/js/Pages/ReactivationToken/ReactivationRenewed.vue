<template>
    <AppLayout :modePublic="true" :title="$t('reactivation_token.title')" :breadCrumbs="[{
        name: slskeyGroup.data.name
    }]">
        <div
            class="w-full mt-5 mb-10 justify-center align-center flex flex-col py-8 bg-white gap-5 rounded-sm gap-y-4 px-8 shadow-md">
            <div class="text-xl"> {{ $t("reactivation_token.token_renewed.headline") }} </div>
            <div>
                {{ $t("reactivation_token.token_renewed.renewed") }}
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
        slskeyGroup: Object
    },
    data() {
        return {
        };
    },
};
</script>