<script setup lang="ts">
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <MainLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800"
                >
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-semibold">
                            Welcome to the Dashboard
                        </h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            This is your dashboard where you can manage your
                            tournaments.
                        </p>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            Use the navigation links to access different
                            sections of the application.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
