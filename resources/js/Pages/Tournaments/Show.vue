<script setup lang="ts">
import ActionLink from '@/Components/ActionLink.vue';
import BaseTable from '@/Components/BaseTable.vue';
import InfoWithActions from '@/Components/InfoWithActions.vue';
import AuthenticatedLayout from '@/Layouts/MainLayout.vue';
import { Resource, Tournament } from '@/types';
import { Head } from '@inertiajs/vue3';

defineProps<{
    tournament: Resource<Tournament>;
}>();
</script>

<template>
    <Head title="Tournaments" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                {{ tournament.data.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <InfoWithActions>
                    <template #actions>
                        <ActionLink
                            :href="
                                route('tournaments.fixtures', {
                                    tournament: tournament.data.id,
                                })
                            "
                        >
                            Go To Fixtures
                        </ActionLink>
                    </template>
                </InfoWithActions>

                <BaseTable
                    :headers="['Team Name']"
                    :rows="
                        tournament.data.teams?.map((team) => [team.name]) || []
                    "
                >
                </BaseTable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
