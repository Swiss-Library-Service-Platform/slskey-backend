<script setup>
import SwitchLoginButton from '@/Shared/Buttons/SwitchLoginButton'
import JetApplicationLogo from '@/Jetstream/ApplicationLogo.vue';
import FlashMessages from '@/Shared/FlashMessages.vue';
</script>

<template>
    <div class="min-h-screen flex flex-col justify-center items-center gap-y-4">

        <div class="flex flex-col bg-white shadow-xlrounded-xl w-96 rounded-lg shadow-lg">
            <FlashMessages />
            <JetApplicationLogo class="block h-80 w-auto px-16 pb-8 pt-12" />

            <div class="border mb-8"></div>

            <div class="flex flex-col h-full mb-8 px-8 justify-between">
                <!-- 
                <div>
                    <div class="text-2xl font-bold">
                        {{ $t('welcome') }}.
                    </div>

                    <div class="text-md font-bold">
                        {{ $t('landing.login') }}
                    </div>
                </div>
                -->

                <div class="flex flex-col items-center justify-center">
                    Login via

                    <SwitchLoginButton class="mb-4" href="/login/eduid">
                        {{ $t('landing.eduid') }}
                    </SwitchLoginButton>

                    <div>
                        {{ $t('landing.orvia') }}
                        <a href="/login" class="text-color-switch font-bold "> {{ $t('landing.usernamepassword') }} </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
