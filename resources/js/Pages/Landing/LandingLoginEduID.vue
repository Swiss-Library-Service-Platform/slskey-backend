<script setup>
import SwitchLoginButton from '@/Shared/Buttons/SwitchLoginButton'
import Notifications from '@/Shared/Notifications.vue';


</script>

<template>
    <div class="min-h-screen flex flex-col justify-center items-center gap-y-4">
        <Notifications />

        <div class="flex flex-row items-stretch bg-white shadow-xlrounded-xl rounded-md shadow-lg">
            <div class="w-80 p-8 flex flex-col justify-between items-start bg-color-slsp-bg">
                <!--<div class=""></div> -->
                <img class="h-auto pb-16" src="/images/slskey_logo_full_black.png" />
            </div>

            <div class="w-100 p-8 flex flex-col h-full items-center justify-between border-l">

                <div class="flex w-full flex-col items-center xtext-gray-table mb-4">
                    <span class="text-xl text-gray-500">
                        {{ $t('landing.welcome_to') }}
                    </span>
                     <span class="text-2xl text-gray-500 font-semibold">
                        {{ $t('landing.admin_portal') }}
                    </span>
                </div>

                <div class="flex flex-col w-full items-center">
                    <SwitchLoginButton href="/login/eduid" class="w-[270px] mb-1">
                        {{ $t('landing.eduid') }}
                    </SwitchLoginButton>

                    <span class="text-sm italic text-gray-500">
                        {{ $t('landing.clicktologin') }}
                    </span>
                </div>

                <div class="flex flex-col w-full items-center gap-y-4 mt-8">
                    <span class="text-sm text-gray-500">
                        {{ $t('landing.more_info') }}
                        <a class="text-blue-800" href="https://slsp.ch/slskey" target="_blank">{{ $t('landing.slsphere') }}</a>.
                    </span>
                </div>

            </div>
        </div>
    </div>
</template>
