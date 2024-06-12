 <template>
    <template v-if="typeof item !== 'undefined' && item != null && item != ''">
        <div>
            <p class="flex items-start max-w-content font-semibold">
                <span class="py-1 pr-2">
                    <slot />
                </span>
                {{ name }}:
            </p>
        </div>
        <p class="md:col-span-2 sm:col-span-2">{{ item }}</p>
    </template>
</template>

<script>

export default {
    props: {
        item: String,
        name: String
    }
}

</script>