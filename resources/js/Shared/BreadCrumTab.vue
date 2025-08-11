<template>
    <div class="flex justify-between w-full">
        <h2 class="font-semibold text-white leading-tight text-2xl justify-self-start flex flex-row items-center">
            <UsersIcon class="h-5 w-5 mr-2"></UsersIcon>
            {{ this.title }}
        </h2>
        <div class="flex">
            <div class="font-semibold text-white leading-tight text-xl justify-self-start bg-one-2 rounded px-2 py-1">
                <template v-if="link_1">
                    <Link class="hover:text-indigo-400 flex flex-row items-center"
                        :class="{ 'text-one': route().current(link_1), 'text-one-1': !route().current(link_1) }"
                        :href="link_1">
                    <UserCircleIcon class="h-5 w-5 mr-2"></UserCircleIcon>

                    {{ name_1 }}
                    </Link>
                </template>
            </div>
            <div
                class="font-semibold text-white leading-tight text-xl justify-self-start bg-one-2 rounded px-2 py-1 ml-5">
                <template v-if="link_2">

                    <Link class="hover:text-indigo-400 flex flex-row items-center"
                        :class="{ 'text-one': route().current(link_2), 'text-one-1': !route().current(link_2) }"
                        :href="link_2">
                    <KeyIcon class="h-5 w-5 mr-2"></KeyIcon>
                    {{ name_2 }}

                    </Link>
                </template>
            </div>
        </div>


    </div>
</template>

<script setuo>
import { KeyIcon, UserCircleIcon, UsersIcon } from '@heroicons/vue/solid';


export default {
    props: {
        title: String,
        link_1: String,
        name_1: String,
        link_2: String,
        name_2: String
    },
    components: { UserCircleIcon, KeyIcon, UsersIcon }
}


</script>