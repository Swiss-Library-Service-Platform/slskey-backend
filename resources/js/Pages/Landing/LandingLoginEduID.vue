<script setup>
import SwitchLoginButton from '@/Shared/Buttons/SwitchLoginButton'
import FlashMessages from '@/Shared/FlashMessages.vue';
import TextInput from '@/Shared/Forms/TextInput.vue';
import DefaultButton from '@/Shared/Buttons/DefaultButton';
import { Head, Link, useForm } from '@inertiajs/inertia-vue3';
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
    })).post(route('login'), {
        onFinish: () => {
            if (form.errors.user_identifier || form.errors.password) {
                form.reset('password');
            }
        },
    });
};
</script>

<template>
    <div class="min-h-screen flex flex-col justify-center items-center gap-y-4">
        <Notifications />

        <div class="flex flex-row items-stretch bg-white shadow-xlrounded-xl rounded-md shadow-lg">
            <div class="w-80 px-8 pb-8 pt-16 flex flex-col justify-between items-start">
                <!--<div class=""></div> -->
                <img class="h-auto" src="/images/slskey_logo_full_black.png" />
                <span class="text-sm italic">
                    {{ $t('landing.service') }} <a class="text-blue-800" href="https://slsp.ch">SLSP</a>.
                </span>
            </div>

            <div class="w-80 p-8 flex flex-col h-fit justify-between border-l">

                <div class="flex flex-col items-center justify-center">

                    <div class="flex w-full flex-col items-center xtext-gray-table mb-4">

                        <SwitchLoginButton href="/login/eduid">
                            {{ $t('landing.eduid') }}
                        </SwitchLoginButton>

                        <span class="text-sm italic text-[#4B5563]">
                            {{ $t('landing.clicktologin') }}
                        </span>


                    </div>

                    <!-- Divider element "OR" -->
                    <div class="flex w-full items-center py-4">
                        <div class="flex flex-col w-full items-center my-4">
                            <div class="flex-grow"></div>
                            <div class="border-t w-full mx-2"></div>
                            <div class="flex-grow"></div>
                        </div>
                        <div class="border uppercase p-1 border-gray-table rounded-sm text-sm">
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
                            <label class="block font-medium text-sm text-gray-700">
                                Username
                            </label>
                            <TextInput id="user_identifier" v-model="form.user_identifier" type="text"
                                class="mt-1 block w-full" required autofocus />
                        </div>

                        <div class="w-full">
                            <label class="block font-medium text-sm text-gray-700">
                                Password
                            </label>
                            <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full"
                                required autocomplete="current-password" />
                        </div>

                        <div class="w-full">
                            <DefaultButton @click="submit" class="mt-4 text-lg leading-6">
                                {{ $t('landing.login') }}
                            </DefaultButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
