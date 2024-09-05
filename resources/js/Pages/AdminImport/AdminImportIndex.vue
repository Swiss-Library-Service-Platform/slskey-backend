
<template>
    <AppLayout title="Import" :breadCrumbs="[{ name: $t('admin_users.title') }]">
        <div class="my-8 w-fit flex flex-col p-6 gap-y-4 bg-white shadow-md rounded-md">

            <div class="text-2xl">
                Import CSV File
            </div>

            <p>
                This page allows you to import a csv file into the database.
                <br>
                It must be a <span class="underline"> semilcolon-seperated</span> csv file <span class="underline">without header row</span> containing the following columns:
                <br>
                <br>
                A: <b>slskey code</b> - The slskey code for the activation
                <br>
                B: <b>primary id</b> - The primary id of the activation
                <br>
                <br>
                <span class="italic"> And optionally for migration from old PURA database: </span>
                <br>
                C: <b>activation date</b> - The activation date of the activation
                <br>
                D: <b>expiration date</b> - The expiration date of the activation
                <br>
                E: <b>remarks</b> - The remarks of the activation
                <br>
                F: <b>member educational institution</b> - The member educational institution of the activation (1 or 0)
            </p>

            <div class="flex flex-col gap-y-4 border-t py-5">
                Select the File to Import

                <input type="file" class="form-control" accept=".csv" @change="onChange" />
            </div>
        </div>
    </AppLayout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import SelectFilter from '@/Shared/Filters/SelectFilter.vue';
import DefaultButton from '@/Shared/Buttons/DefaultButton.vue';
import { Inertia } from '@inertiajs/inertia';
import TextInput from '../../Shared/Forms/TextInput.vue';

export default {
    components: {
        AppLayout,
        DefaultButton,
        SelectFilter,
        Inertia,
        TextInput
    },
    props: {
    },
    data() {
        return {
            inputCSV: null,
        }
    },
    methods: {
        onChange(event) {
            this.inputCSV = event.target.files[0];
            Inertia.post("/admin/import/preview", { csv_file: this.inputCSV }, {
            });
        }
    },
}
</script>