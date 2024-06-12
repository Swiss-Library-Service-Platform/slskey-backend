<script setup>
import Icon from '../Icon.vue';
import { v4 as uuid } from 'uuid'

defineProps({
    id: {
      type: String,
      default() {
        return `primary-button-${uuid()}`
      },
    },
    type: {
        type: String,
        default: 'button',
    },
    href: {
        type: String
    },
    icon: String,
});
</script>

<template>
    <Link :id=id :type="type" :as="type" :href="href" class="w-full">
      <img src="/images/eduidbutton.svg" alt="switch" class="" />
    </Link>
  
</template>
