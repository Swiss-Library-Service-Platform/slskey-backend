<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">

        <div class="w-full flex flex-col sm:max-w-md mt-6 px-6 py-4 bg-white shadow-lg sm:rounded-md">
            <div class="self-center">
                <slot name="logo" />
            </div>

            <slot />
        </div>
    </div>
</template>