<template>
    <div class="flex flex-row">
        <!--
        <LetterIcon class="mr-2" :letter="workflow == 'Webhook' ? 'A' : 'M'"
            :class="{ 'border-color-gray-disabled': disabled, 'border-black': !disabled }">
        </LetterIcon>
    -->
        <MaterialIcon class="mr-2" :icon="workflow == 'Webhook' ? 'auto_mode' : 'cursor_default_click'" :class="{
            'text-slsp': workflow !== 'Webhook',
            'text-deactivated': workflow == 'Webhook',
        }">
        </MaterialIcon>

        {{ slskeyGroupName }}
    </div>
</template>

<script>
import LetterIcon from './LetterIcon.vue';
import MaterialIcon from './MaterialIcon.vue';
export default {
    components: {
        LetterIcon,
        MaterialIcon
    },
    props: {
        slskeyGroupName: String,
        workflow: String,
        disabled: Boolean
    },
}
</script>