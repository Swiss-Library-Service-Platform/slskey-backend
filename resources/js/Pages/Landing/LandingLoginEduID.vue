<script setup>
import SwitchLoginButton from '@/Shared/Buttons/SwitchLoginButton'
import JetApplicationLogo from '@/Jetstream/ApplicationLogo.vue';
import FlashMessages from '@/Shared/FlashMessages.vue';
import JetLabel from '@/Jetstream/Label.vue';
import JetInput from '@/Jetstream/Input.vue';
import DefaultButton from '@/Shared/Buttons/DefaultButton';
import { Head, Link, useForm } from '@inertiajs/inertia-vue3';
import JetValidationErrors from '@/Jetstream/ValidationErrors.vue';
import Notifications from '@/Shared/Notifications.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    user_identifier: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('loginform'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="min-h-screen flex flex-col justify-center items-center gap-y-4">
        <!-- <FlashMessages />  because we are not inside Applayout here and use notifications -->
        <Notifications />

        <div class="flex flex-row items-stretch bg-white shadow-xlrounded-xl rounded-lg shadow-lg">
            <div class="w-80 px-8 pb-8 pt-16 flex flex-col justify-between items-start"> 
                <!--<div class=""></div> -->
                <JetApplicationLogo class="h-auto" />
                <span class="text-sm italic">
                   {{ $t('landing.service') }} <a class="text-blue-800" href="https://slsp.ch">SLSP</a>.
                </span>
            </div>

            <div class="w-80 p-8 flex flex-col h-fit justify-between border-l">

                <div class="flex flex-col items-center justify-center">

                    <SwitchLoginButton href="/login/eduid">
                        {{ $t('landing.eduid') }}
                    </SwitchLoginButton>



                    <!-- Divider element "OR" -->
                    <div class="flex w-full items-center py-4">
                        <div class="flex flex-col w-full items-center my-4">
                            <div class="flex-grow"></div>
                            <div class="border-t w-full mx-2"></div>
                            <div class="flex-grow"></div>
                        </div>
                        <div class="border uppercase p-1 text-gray-table text-sm">
                            {{ $t('landing.orvia') }}
                        </div>
                        <div class="flex flex-col w-full items-center my-4">
                            <div class="flex-grow"></div>
                            <div class="border-t w-full mx-2"></div>
                            <div class="flex-grow"></div>
                        </div>
                    </div>


                    <form class="w-full" @submit.prevent="submit" @keydown.enter="submit">

                        <div class="w-full mb-2">
                            <JetLabel for="user_identifier" value="Username" />
                            <JetInput id="user_identifier" v-model="form.user_identifier" type="text"
                                class="mt-1 block w-full" required autofocus />
                        </div>

                        <div class="w-full">
                            <JetLabel for="password" value="Password" />
                            <JetInput id="password" v-model="form.password" type="password" class="mt-1 block w-full"
                                required autocomplete="current-password" />
                        </div>
                        <!-- <JetValidationErrors class="mb-4" /> -->

                        <div class="w-full">
                            <DefaultButton @click="submit"
                                class="bg-color-header-bg mt-4 w-full py-2 px-3 items-center text-white rounded-2xl	text-lg">
                                {{ $t('landing.login') }}
                            </DefaultButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
