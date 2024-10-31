<template>
    <AppLayout title="Import" :breadCrumbs="[{ name: $t('admin_users.title') }]">

        <div class="my-8 w-full flex flex-col p-6 gap-y-4 bg-white shadow-md rounded-md">

            <div class="w-full flex flex-row justify-between pb-4 border-b">
                <div class="flex flex-row justify-start text-xl">
                    <div class="flex flex-col justify-between mr-4">
                        <p id="progress-text">Total Users:</p>
                    </div>
                    <div class="flex flex-col font-bold font-bold">
                        <p id="progress-text">{{ this.processedRows }} / {{ this.importRows.length }} </p>
                    </div>
                </div>

                <div>
                    <checkbox-classic-input class="w-full" v-model="this.testRun" label="Test run" />
                    <checkbox-classic-input class="w-full" v-model="this.withoutExternalApis" label="Retrieve and save Alma Infos" />
                    <checkbox-classic-input class="w-full" v-model="this.checkIsActive" label="(Migration) Only import if user already activated" />
                    <checkbox-classic-input class="w-full" v-model="this.setHistoryActivationDate" label="(Migration) Set historic activation date" />
                    <DefaultButton v-if="!importStarted" @click="startImport"
                        class="w-fit">
                        Start Import
                    </DefaultButton>
                    <DefaultButton v-if="importStarted" @click="stopImport"
                        class="text-color-blocked w-fit">
                        Stop Import
                    </DefaultButton>
                </div>

            </div>


            <!-- Progress Bar -->
            <div class="relative">
                <progress id="progress-bar w-full mt-5" :max="this.importRows.length" :value="processedRows"></progress>
                <p class="absolute left-1/2 text-black font-bold top-2" id="progress-text">{{ ((processedRows /
                    this.importRows.length) * 100).toFixed(2) }}%</p>
            </div>

            <!-- Results -->
            <div v-if="processedRows != 0" class="flex flex-col">

                <div class="w-full flex flex-row justify-between pt-8 border-t">
                    <div class="text-2xl">
                        Results
                    </div>
                </div>

                <div class="flex flex-row justify-between pt-4">
                    <!-- Success and Error Count -->
                    <div class="flex flex-row justify-start">
                        <div class="flex flex-col justify-between mr-4">
                            <p v-if="this.checkIsActive" id="progress-text">Active:</p>
                            <p id="progress-text">Success:</p>
                            <p id="progress-text">Error:</p>
                            <br>
                        </div>
                        <div class="flex flex-col font-bold font-bold">
                            <p v-if="this.checkIsActive" id="progress-text">{{ this.activeRows }}</p>
                            <p id="progress-text">{{ this.successRows }}</p>
                            <p id="progress-text">{{ this.errorRows }}</p>
                            <br>
                        </div>
                    </div>

                    <!-- Show only Errors Button -->
                    <DefaultButton v-if="!this.showErrors" @click="setShowErrors(true)" class="w-fit border py-2">
                        Show Only Errors
                    </DefaultButton>
                    <DefaultButton v-if="this.showErrors" @click="setShowErrors(false)" class="w-fit border py-2">
                        Show All Rows
                    </DefaultButton>
                </div>

                <!-- Result Table -->
                <table class="table-auto min-w-full divide-y divide-gray-table rounded-md">
                    <thead class="">
                        <tr>
                            <th class="py-4 pr-6 text-left whitespace-nowrap"> {{ $t('slskey_groups.slskey_code') }}
                            </th>
                            <th class="py-4 px-6 text-left whitespace-nowrap"> {{ $t('slskey_user.primary_id') }} </th>
                            <th class="py-4 px-6 text-left whitespace-nowrap"> Switch Status </th>
                            <th class="py-4 px-6 text-left whitespace-nowrap"> Custom Verification </th>
                            <th class="py-4 px-6 text-left whitespace-nowrap"> Success </th>
                            <th class="py-4 pl-6 text-left whitespace-nowrap"> Error </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-table">
                        <template v-if="doneRows.length > 0">
                            <template v-for="doneRow in doneRows" :key="'user' + doneRow.id">
                                <tr v-if="!showErrors || !doneRow.success"
                                    class="hover:bg-gray-100 focus-within:bg-gray-100">
                                    <td class="pr-6 py-4 align-top">
                                        {{ doneRow.slskey_code }}
                                    </td>
                                    <td class="px-6 py-4 align-top">
                                        {{ doneRow.primary_id }}
                                    </td>
                                    <td class="px-6 py-4 align-top">
                                        <Icon class="h-6 w-6"
                                            :class="doneRow.isActive == null ? 'text-color-deactivated' : (doneRow.isActive ? 'text-color-active' : 'text-color-blocked')"
                                            :icon="doneRow.isActive == null ? 'question-mark' : (doneRow.isActive ? 'check-circle' : 'x')" />
                                    </td>
                                    <td class="px-6 py-4 align-top">
                                        <Icon class="h-6 w-6"
                                            :class="doneRow.isVerified == null ? 'text-color-deactivated' : (doneRow.isVerified ? 'text-color-active' : 'text-color-blocked')"
                                            :icon="doneRow.isVerified == null ? 'question-mark' : (doneRow.isVerified ? 'check-circle' : 'x')" />
                                    </td>
                                    <td class="px-6 py-4 align-top">
                                        <Icon class="h-6 w-6"
                                            :class="doneRow.success ? 'text-color-active' : 'text-color-blocked'"
                                            :icon="doneRow.success ? 'check-circle' : 'x'" />
                                    </td>
                                    <td class="pl-6 py-4 align-top">
                                        {{ doneRow.message }}
                                    </td>
                                </tr>
                            </template>

                        </template>
                        <template v-else>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">No results yet.</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
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
import Echo from 'laravel-echo';
import Icon from '@/Shared/Icon.vue';
import CheckboxClassicInput from '@/Shared/Forms/CheckboxClassicInput.vue'

export default {
    components: {
        AppLayout,
        DefaultButton,
        SelectFilter,
        Inertia,
        TextInput,
        Echo,
        Icon,
        CheckboxClassicInput
    },
    props: {
        givenRows: Array,
    },
    data() {
        return {
            processedRows: 0,
            importStarted: false,
            showErrors: false,
            successRows: 0,
            errorRows: 0,
            activeRows: 0,
            importRows: this.givenRows,
            doneRows: [],
            checkIsActive: 0,
            withoutExternalApis: 0,
            setHistoryActivationDate: 0,
            testRun: 1
        }
    },
    methods: {
        startImport() {
            // confirm first
            if (!this.testRun && !confirm("Are you sure you want to start the productive import?")) {
                return;
            }

            this.processedRows = 0;
            this.successRows = 0;
            this.errorRows = 0;
            this.activeRows = 0;
            this.doneRows = [];

            axios.post("/admin/import/store", {
                importRows: this.importRows,
                testRun: this.testRun,
                withoutExternalApis: this.withoutExternalApis,
                checkIsActive: this.checkIsActive,
                setHistoryActivationDate: this.setHistoryActivationDate
            }).then(() => {
                this.importStarted = true;
            }).catch(error => {
                alert(error);
            });
        },
        stopImport() {
            this.importStarted = false;
            axios.post("/admin/import/stop").then(() => {
            }).catch(error => {
                alert(error);
            });
        },
        listenForEvents() {
            // Setup Pusher Channel
            var pusher = new Pusher('0d52d133626dc1765aa7', {
                cluster: 'eu',
            });
            // Listen for Import progress
            var channel = pusher.subscribe('import-progress');
            channel.bind('import-progress-row', (event) => {
                this.successRows = event.success ? this.successRows + 1 : this.successRows;
                this.errorRows = event.success ? this.errorRows : this.errorRows + 1;
                this.activeRows = event.isActive ? this.activeRows + 1 : this.activeRows;
                
                this.processedRows++;
                this.updateRow(event.currentRow, event);

                if (this.processedRows == this.importRows.length) {
                    this.importStarted = false;
                }
            });
        },
        updateRow(index, event) {
            // let $row = this.importRows[index];
            let doneRow = {};
            doneRow.primary_id = event.primary_id;
            doneRow.slskey_code = event.slskey_code;
            doneRow.success = event.success;
            doneRow.message = event.message;
            doneRow.isActive = event.isActive;
            doneRow.isVerified = event.isVerified;
            // push the doneRow to doneRows at first index
            this.doneRows.unshift(doneRow);
        },
        setShowErrors(show) {
            this.showErrors = show;
        }
    },
    mounted() {
        this.listenForEvents();
    },
}
</script>

<style>
progress {
    border: 0;
    height: 40px;
    border-radius: 20px;
    width: 100%;
    background: none;
    position: relative;
}

progress::-webkit-progress-bar {
    border: 0;
    height: 40px;
    border-radius: 20px;
    width: 100%;
    background-color: #dadada;
}

progress::-webkit-progress-value {
    border: 0;
    height: 40px;
    border-radius: 20px;
    background: #89cf97;
}

progress::-moz-progress-bar {
    border: 0;
    height: 40px;
    border-radius: 20px;
    width: 100%;
    background-color: #dadada;
}
</style>