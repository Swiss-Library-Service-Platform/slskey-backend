 <template>
    <template v-if="typeof item !== 'undefined' && item != null">
        <div>
            <p class="flex items-start max-w-content font-semibold">
                <span class=" py-1 pr-2">
                    <slot />
                </span>
                {{ name }}:
            </p>
        </div>
        <a class="md:col-span-2 sm:col-span-2 underline" :href="checkUrl(href)">{{ item }}</a>
    </template>
</template>

<script>

export default {
    props: {
        href: String,
        item: String,
        name: String
    },
    methods: {
        checkUrl(url) {
            if (url.startsWith('https://') || url.startsWith('http://')) {
                return url;
            } else {
                return '//' + url
            }
        }
    }
}

</script>