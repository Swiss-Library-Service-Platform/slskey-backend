<template>
  <a :disabled="loading" v-if="href" :href="href" class="flex items-center px-3
              py-2
              bg-color-one
              rounded-sm
              font-bold 
              text-sm
              text-white
              hover:text-color-one
              hover:bg-color-one-1
              focus:outline-none
              disabled:text-white
              disabled:opacity-40 ">
    <div v-if="loading" class="btn-spinner mr-2" />
    <slot />
  </a>
  <button :id="id" :disabled="loading" v-else class="flex items-center px-3
              py-2
              bg-color-one
              rounded-sm
              font-bold 
              text-sm
              text-white
              hover:text-color-one
              hover:bg-color-one-1
              focus:outline-none
              disabled:text-white
              disabled:opacity-40 ">
    <div v-if="loading" class="btn-spinner mr-2" />
    <slot />
  </button>
</template>

<script>
import { v4 as uuid } from 'uuid'

export default {
  props: {
    id: {
      type: String,
      default() {
        return `loading-button-${uuid()}`
      },
    },
    loading: Boolean,
    href: {
      type: String
    },
  },
}
</script>
