<script setup>
import SwitchLoginButton from '@/Shared/Buttons/SwitchLoginButton'
import Notifications from '@/Shared/Notifications.vue';


</script>

<template>
    <div class="min-h-screen flex flex-col justify-center items-center gap-y-4">
        <Notifications />

        <div class="flex flex-row items-stretch bg-slsp-bg-logo rounded-md border shadow-xl border-gray-table">
            <div class="w-80 p-8 flex flex-col justify-between items-start">
                <!--<div class=""></div> -->
                <img class="h-auto pb-16 invert-20" src="/images/slskey_logo_full_black.png" />
            </div>

            <div class="w-100 p-8 flex flex-col h-full items-center rounded-r-md justify-between border-l border-gray-table bg-white">

                <div class="flex w-full flex-col items-center xtext-gray-table mb-4">
                    <span class="text-xl text-gray-500">
                        {{ $t('landing.welcome_to') }}
                    </span>
                     <span class="text-2xl text-gray-500 font-semibold">
                        {{ $t('landing.admin_portal') }}
                    </span>
                </div>

                <div class="flex flex-col w-full items-center">
                    <SwitchLoginButton href="/login/eduid" class="w-[280px] mb-1">
                        {{ $t('landing.eduid') }}
                    </SwitchLoginButton>

                    <span class="text-sm italic text-gray-500">
                        {{ $t('landing.clicktologin') }}
                    </span>
                </div>

                <div class="flex flex-col w-full items-center gap-y-4 mt-8">
                    <span class="text-sm text-gray-500">
                        {{ $t('landing.more_info') }}
                        <a class="text-blue-800" href="https://slsp.atlassian.net/wiki/spaces/slsporgserv/pages/1042415617" target="_blank">{{ $t('landing.slsphere') }}</a>.
                    </span>
                </div>

            </div>
        </div>
    </div>
</template>
