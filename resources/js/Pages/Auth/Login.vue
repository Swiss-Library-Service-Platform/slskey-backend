<script setup>
import { Head, Link, useForm } from '@inertiajs/inertia-vue3';
import JetAuthenticationCard from '@/Jetstream/AuthenticationCard.vue';
import JetButton from '@/Jetstream/Button.vue';
import JetInput from '@/Jetstream/Input.vue';
import JetCheckbox from '@/Jetstream/Checkbox.vue';
import JetLabel from '@/Jetstream/Label.vue';
import JetValidationErrors from '@/Jetstream/ValidationErrors.vue';

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
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <!-- 

        Note: LandingLoginIndex.vue is used instead of Login.vue

    <Head title="Log in" />

    <JetAuthenticationCard>
        <template #logo>
            <img src="/images/slskey_logo_small_black.png" class="h-16 w-auto mr-4 my-4" />
        </template>
        <JetValidationErrors class="mb-4" />

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">

            <div>
                <JetLabel for="user_identifier" value="Username" />
                <JetInput id="user_identifier" v-model="form.user_identifier" type="text" class="mt-1 block w-full" required
                    autofocus />
            </div>

            <div class="mt-4">
                <JetLabel for="password" value="Password" />
                <JetInput id="password" v-model="form.password" type="password" class="mt-1 block w-full" required
                    autocomplete="current-password" />
            </div>

            <div class="block mt-4">
                <label class="flex items-center">
                    <JetCheckbox v-model:checked="form.remember" name="remember" />
                    <span class="ml-2 text-sm text-gray-600">Remember me</span>
                </label>
            </div>

            <div class="flex items-center justify-between mt-8">
                <Link v-if="canResetPassword" :href="route('password.request')"
                    class="underline text-sm text-gray-600 hover:text-gray-900">
                Forgot your password?
                </Link>

                <a href="/login"
                    class="inline-flex items-center font-bold px-4 py-2 bg-color-switch border border-transparent rounded-md text-xs text-white hover:bg-opacity-70 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                    {{ $t('login.back') }} 
                </a>
                <JetButton class="ml-4 bg-color-switch font-bold" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    {{ $t('login.login') }} 
                </JetButton>
            </div>
        </form>
    </JetAuthenticationCard>
-->
</template>
