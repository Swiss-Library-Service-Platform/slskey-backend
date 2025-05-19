<!-- create a label with popper popup for additional info -->

<template>
    <div class="flex items
    -center">
        <button ref="btnRef" v-on:mouseenter="toggleTooltip()" v-on:mouseleave="toggleTooltip()" class="ml-1">
            <QuestionMarkCircleIcon class="h-5 w-5 text-gray-400" />
        </button>
        <div ref="tooltipRef" v-bind:class="{ 'hidden': !tooltipShow, 'block': tooltipShow }"
            class="bg-white border border-gray-200 p-2 rounded-sm shadow-md ml-3 block z-50 font-normal leading-normal text-sm max-w-xs text-left no-underline break-words rounded-md">
            <div class="text-black p-3 whitespace-pre-line">
                <slot></slot>
                <br>
            </div>
        </div>
    </div>
</template>

<script>
import { createPopper } from '@popperjs/core';
import { QuestionMarkCircleIcon } from '@heroicons/vue/outline';

export default {
    components: {
        QuestionMarkCircleIcon
    },
    data() {
        return {
            tooltipShow: false,
        }
    },
    props: {
        id: {
            type: String,
            default: 'label-with-popup'
        },
    },
    methods: {
        toggleTooltip() {
            this.tooltipShow = !this.tooltipShow;
            if (this.tooltipShow) {
                createPopper(this.$refs.btnRef, this.$refs.tooltipRef, {
                    placement: 'right',
                    modifiers: [
                        {
                            name: 'offset',
                            options: {
                                offset: [0, 8],
                            },
                        },
                    ],
                });
            }
        }
    }
}
</script>
