<script setup lang="ts">
import ActionLink from '@/Components/ActionLink.vue';
import BaseTable from '@/Components/BaseTable.vue';
import DangerousActionButton from '@/Components/DangerousActionButton.vue';
import InfoWithActions from '@/Components/InfoWithActions.vue';
import Pagination from '@/Components/Pagination.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { PaginatedResource, Tournament } from '@/types';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps<{
    tournaments: PaginatedResource<Tournament>;
}>();

const deleteTournament = (id: number) => {
    router.delete(`/tournaments/${id}`, {
        onSuccess: () => {
            router.reload();
        },
    });
};
</script>

<template>
    <Head title="Tournaments" />

    <MainLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Tournaments
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <InfoWithActions>
                    <template #actions>
                        <ActionLink :href="route('tournaments.create')">
                            Create Tournament
                        </ActionLink>
                    </template>
                </InfoWithActions>

                <BaseTable
                    :headers="['ID', 'Name']"
                    :rows="
                        tournaments.data.map((tournament) => ({
                            id: tournament.id,
                            name: tournament.name,
                        }))
                    "
                >
                    <template #actions="{ row }">
                        <ActionLink :href="route('tournaments.show', row.id)">
                            View
                        </ActionLink>
                        <DangerousActionButton
                            @click="deleteTournament(row.id)"
                        >
                            Delete
                        </DangerousActionButton>
                    </template>
                </BaseTable>

                <Pagination :meta="props.tournaments.meta" />
            </div>
        </div>
    </MainLayout>
</template>
