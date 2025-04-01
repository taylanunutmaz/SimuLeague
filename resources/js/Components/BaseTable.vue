<script setup lang="ts">
defineProps<{
    headers: string[];
    rows: Array<Record<string, any>>;
}>();
</script>

<template>
    <table
        class="min-w-full border bg-white dark:border-gray-700 dark:bg-gray-800"
    >
        <thead>
            <tr>
                <th
                    v-for="header in headers"
                    :key="header"
                    class="border px-4 py-2 dark:border-gray-700 dark:text-gray-300"
                >
                    {{ header }}
                </th>
                <th
                    v-if="$slots.actions"
                    class="border px-4 py-2 dark:border-gray-700 dark:text-gray-300"
                >
                    Actions
                </th>
            </tr>
        </thead>
        <tbody>
            <template v-if="rows.length === 0">
                <tr>
                    <td
                        :colspan="headers.length + ($slots.actions ? 1 : 0)"
                        class="border px-4 py-2 text-center dark:border-gray-700 dark:text-gray-300"
                    >
                        No data available
                    </td>
                </tr>
            </template>
            <template v-else>
                <tr v-for="row in rows" :key="row.id">
                    <td
                        v-for="(value, key) in row"
                        :key="key"
                        v-html="value"
                        class="border px-4 py-2 dark:border-gray-700 dark:text-gray-300"
                    ></td>
                    <td
                        v-if="$slots.actions"
                        class="border px-4 py-2 dark:border-gray-700"
                    >
                        <slot name="actions" :row="row" />
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
</template>
