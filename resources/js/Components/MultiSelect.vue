<script setup lang="ts">
const model = defineModel<number[]>({ required: true });

defineProps<{
    options: { id: number; name: string }[];
    id: string;
}>();
</script>

<template>
    <div>
        <select
            :id="id"
            v-model="model"
            multiple
            class="mt-1 block w-full rounded-md border-gray-300 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
        >
            <option
                v-for="option in options"
                :key="option.id"
                :value="option.id"
                class="dark:bg-gray-700 dark:text-gray-100"
            >
                {{ option.name }}
            </option>
        </select>
    </div>
</template>
