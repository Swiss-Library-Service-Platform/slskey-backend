<template>
    <div>
        <p class="flex items-center max-w-fit whitespace-nowrap font-semibold">
            <span class="py-1 pr-2">
                <OfficeBuildingIcon class=" h-4 w-4 text-color-one" />
            </span>
            {{ $t('institution_zone') }}:
        </p>
    </div>
    <div class="rounded-sm outline outline-1 outline-gray-100 bg-gray-50 max-w-fit md:col-span-2 sm:col-span-2">
        <div class="flex flex-col p-2">
            <p class="font-semibold">
                {{ item.institution_zone?.name }}
            </p>
            <p>
                {{ $t('iz_id') }}: {{ item.institution_zone?.iz_id }}
            </p>
            <p>
                {{ $t('iz_code') }}: {{ item.institution_zone?.iz_code }}
            </p>
            <p>
                {{ $t('iz_abbreviation') }}: {{ item.institution_zone?.iz_abbreviation }}
            </p>
        </div>
    </div>
</template>


<script>
import { OfficeBuildingIcon } from '@heroicons/vue/outline'

export default {
    components: { OfficeBuildingIcon },
    props: {
        item: Object,
        name: String
    }
}

</script>