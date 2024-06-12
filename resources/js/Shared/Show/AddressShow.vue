<template>
    <div>
        <p class="flex items-center max-w-fit whitespace-nowrap font-semibold">
            <span class="py-1 pr-2">
                <LocationMarkerIcon class=" h-4 w-4 text-color-one" />
            </span>
            {{ $t('location') }}:
        </p>
    </div>
    <div class="rounded-sm outline outline-1 outline-gray-100 bg-gray-50 max-w-fit 2 sm:col-span-2">
        <div class="flex flex-col p-2">
            <p class="font-semibold">
                {{ item.address_line_1 }}
            </p>
            <p>
                {{ item.address_line_2 }}
            </p>
            <p>
                {{ item.address_line_3 }}
            </p>
            <p>
                {{ item.address_line_4 }}
            </p>
            <p>
                {{ item.street }}
            </p>
            <p>
                <span v-if="item.post_office_box">
                    {{ item.post_office_box }}
                </span>
            </p>
            <p>
                <span v-if="item.postal_code">
                    {{ item.postal_code }}
                </span>
                {{ item.city }}
            </p>
        </div>
    </div>
</template>


<script>
import { LocationMarkerIcon } from '@heroicons/vue/outline'

export default {
    components: { LocationMarkerIcon },
    props: {
        item: Object,
        name: String
    }
}

</script>