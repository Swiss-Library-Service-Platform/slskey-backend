
<template>
    <AppLayout :title="$t('activation.title')" :breadCrumbs="[{ name: $t('activation.title') }]">


        <div class="w-fit my-8 justify-center align-center flex py-8 bg-white gap-5 rounded-sm gap-y-4 px-8 shadow-md">
            <div class="w-96 flex flex-col gap-y-8">

                <div>
                    <div class="text-2xl">
                        {{ $t('activation.activation_start') }}
                    </div>
                    <div class="text-lg italic">
                        {{ $t('activation.activation_start_info') }}
                    </div>
                </div>

                <TextInput @enter="activate" v-model="inputIdentifier" :placeholder="$t('activation.identifier')"></TextInput>
                <DefaultButton :disabled="!inputIdentifier || loading" @click.prevent="activate" icon="search" :loading="loading">
                    {{ $t('activation.search') }}
                </DefaultButton>

            </div>
        </div>

    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import TextInput from '../../Shared/Forms/TextInput.vue';
import DefaultButton from '../../Shared/Buttons/DefaultButton.vue';
import { Inertia } from '@inertiajs/inertia';

// register globally
export default {
    components: { AppLayout, TextInput, DefaultButton, Inertia },
    props: {
        filters: Object,
        results: Object,
        total: Number,
    },
    data() {
        return {
            inputIdentifier: "",
            loading: false
        }
    },
    methods: {
        activate: function () {
            this.loading = true;
            Inertia.get("/activation/" + this.inputIdentifier,
                {
                    origin: 'ACTIVATION_START'
                }
            );
        }
    }
}
</script>


