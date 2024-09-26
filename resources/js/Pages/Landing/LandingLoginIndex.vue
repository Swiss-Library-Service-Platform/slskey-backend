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
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    form.transform(data => ({
        ...data,
        _token: csrfToken,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="min-h-screen flex flex-col justify-center items-center gap-y-4 bg-gray-background">
        <!-- <FlashMessages /> because we are not inside Applayout here and use notifications -->
        <Notifications />

        <div class="flex flex-row items-stretch bg-white shadow-xlrounded-xl rounded-lg shadow-lg">
            <div class="w-80 px-8 py-8 flex flex-col justify-around items-start"> 
                <!--<div class=""></div> -->
                <JetApplicationLogo class="h-auto px-8" />
                <span class="text-sm italic px-8">
                   {{ $t('landing.service') }} <a class="text-blue-800" href="https://slsp.ch">SLSP</a>.
                </span>
            </div>

            <div class="w-80 p-8 py-16 h-auto flex flex-col h-fit justify-between border-l">

                <div class="flex flex-col items-center justify-center">

                    <form class="w-full" @submit.prevent="submit" @keydown.enter="submit">

                        <div class="w-full mb-4">
                            <JetLabel for="user_identifier" :value="$t('landing.username')" />
                            <JetInput id="user_identifier" v-model="form.user_identifier" type="text"
                                class="mt-1 block w-full" required autofocus />
                        </div>

                        <div class="w-full">
                            <JetLabel for="password" :value="$t('landing.password')" />
                            <JetInput id="password" v-model="form.password" type="password" class="mt-1 block w-full"
                                required autocomplete="current-password" />
                        </div>
                        <JetValidationErrors class="mb-6" />

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
