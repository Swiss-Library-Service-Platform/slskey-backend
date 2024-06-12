 <template>
    <template v-if="typeof item !== 'undefined' && item != null">
        <p class="flex items-start max-w-content font-semibold">
            <span class="py-1 pr-2">
                <slot />
            </span>
            {{ name }}:
        </p>
        <Link :href="href" class="md:col-span-2 sm:col-span-2">
        <p class="p-2 bg-gray-50 transition duration-300 hover:bg-color-one-2 max-w-fit rounded">
            {{ item }}
        </p>
        </Link>
    </template>
</template>

<script>

export default {
    props: {
        href: String,
        item: String,
        name: String
    }
}

</script>