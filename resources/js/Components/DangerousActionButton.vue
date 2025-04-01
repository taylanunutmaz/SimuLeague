<script setup lang="ts">
const emit = defineEmits<{
    (e: 'click'): void;
}>();

const confirmAction = () => {
    if (confirm('Are you sure you want to reset the data?')) {
        emit('click');
    }
};
</script>

<template>
    <button
        class="ml-2 rounded bg-red-500 px-4 py-2 text-white hover:bg-red-600"
        @click="confirmAction"
        @keydown.enter.prevent="confirmAction"
    >
        <slot />
    </button>
</template>
