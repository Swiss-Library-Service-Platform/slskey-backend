<template>
    <div :class="$attrs.class" class="flex flex-col justify-center text-sm">
        <label class="block font-medium text-sm text-gray-700">
            {{ label }}
        </label>
        <div class="flex h-10 place-items-center justify-center">
            <div class="p-0.5 flex shrink-0 rounded-full bg-white border-1 border-transparent" :class="{
                'bg-color-one ': selected,
                'bg-gray-100': selected === null,
                'bg-color-one-1': selected === false
            }">
                <div class="pointer-events-none select-none absolute rounded-full bg-white shadow-lg w-6 h-6 
                    transform transition duration-200 ease-in-out focus:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-opacity-75"
                    :class="{
                        'translate-x-0': selected === false,
                        'translate-x-6 bg-gray-500': selected === null,
                        'translate-x-12': selected,
                    }">
                </div>
                <button class="w-6 h-6 " @click="clicked(false)" />
                <button class="w-6 h-6 " @click="clicked(null)" />
                <button class="w-6 h-6 " @click="clicked(true)" />
            </div>
        </div>
    </div>
</template>

<script>

export default {
    props: {
        modelValue: String,
        label: String,
    },
    data() {
        return {
            selected: this.checkValue(this.modelValue)
        }
    },
    emits: ['update:modelValue'],
    watch: {
        modelValue() {
            if (!_.isNil(this.modelValue)) {
                this.selected = this.modelValue === 'true' ? true : false
            } else {
                this.selected = null;
            }
        }
    },
    methods: {
        checkValue(value) {
            if (_.isNil(value)) {
                return null;
            } else {
                return value === 'true' ? true : false
            }
        },
        clicked(selected) {
            this.selected = selected;
            if (_.isNil(selected)) {
                this.$emit('update:modelValue', null);
            } else {
                this.$emit('update:modelValue', selected === true ? 'true' : 'false');
            }
        }
    }
}
</script>
