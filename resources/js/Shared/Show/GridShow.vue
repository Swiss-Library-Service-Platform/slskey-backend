<template>
    <slot name="header" />
    <div :class="$attrs.class" class="grid md:grid-cols-3 sm:grid-cols-3 gap-4">
        <slot name="default" />
    </div>
</template>