<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { defineProps } from 'vue';

defineProps<{
    href: string;
}>();
</script>

<template>
    <Link
        :href="href"
        as="button"
        class="ml-2 rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-600"
    >
        <slot />
    </Link>
</template>
