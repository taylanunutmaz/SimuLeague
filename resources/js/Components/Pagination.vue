<script setup lang="ts">
import { PaginationMeta } from '@/types';
import { Link } from '@inertiajs/vue3';
const props = defineProps<{
    meta: PaginationMeta;
}>();
</script>

<template>
    <!-- eslint-disable vue/no-v-text-v-html-on-component -->
    <div
        v-if="props.meta.last_page > 1"
        class="mt-4 flex justify-center space-x-2"
    >
        <Link
            v-for="link in props.meta.links"
            :key="link.label"
            v-html="link.label || ''"
            :href="link.url || '#'"
            class="rounded px-4 py-2"
            :class="{
                'bg-blue-500 text-white hover:bg-blue-600': link.active,
                'bg-gray-200 text-gray-700 hover:bg-gray-300':
                    !link.active && link.url,
                'cursor-not-allowed text-gray-400': !link.url,
            }"
        ></Link>
    </div>
</template>
