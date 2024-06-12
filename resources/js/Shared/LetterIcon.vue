<template>
    <div :class="$attrs.class">
        <div class="flex items-center justify-center h-18px w-18px rounded-full border border-inherit text-xs mt-3px">
            {{ letter }}
        </div>
    </div>
</template>

<script>

export default {
    components: {

    },
    props: {
        letter: {
            type: String,
            required: true
        }
    }
}
</script>