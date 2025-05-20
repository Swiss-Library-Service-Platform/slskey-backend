<script setup>
import { Head, useForm } from '@inertiajs/inertia-vue3';
import TextInput from '@/Shared/Forms/TextInput.vue';
import DefaultButton from '@/Shared/Buttons/DefaultButton.vue';

import { computed } from 'vue';
import { usePage } from '@inertiajs/inertia-vue3';

const errors = computed(() => usePage().props.value.errors);
const hasErrors = computed(() => Object.keys(errors.value).length > 0);

const props = defineProps({
    email: String,
    token: String,
});

const form = useForm({
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('login_setpassword'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>

    <Head title="Reset Password" />

    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">

        <div class="w-full flex flex-col sm:max-w-md mt-6 px-6 py-4 bg-white shadow-lg sm:rounded-md">
            <div class="self-center">
                <img src="/images/slskey_logo_small_black.png" class="h-12 w-auto mr-4" />
            </div>
            <div v-if="hasErrors">

                <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                    <li v-for="(error, key) in errors" :key="key">
                        {{ error }}
                    </li>
                </ul>
            </div>

            <div class="text-xl text-center my-4 ">
                Please change your password
            </div>

            <form @submit.prevent="submit">

                <div class="mt-4">
                    <label class="block font-medium text-sm text-gray-700">
                        Password
                    </label>

                    <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full" required
                        autocomplete="new-password" />
                </div>

                <div class="mt-4">
                    <label class="block font-medium text-sm text-gray-700">
                        Confirm Password
                    </label>
                    <TextInput id="password_confirmation" v-model="form.password_confirmation" type="password"
                        class="mt-1 block w-full" required autocomplete="new-password" />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <DefaultButton type="submit">
                        Save Password
                    </DefaultButton>
                </div>
            </form>
        </div>
    </div>

</template>
