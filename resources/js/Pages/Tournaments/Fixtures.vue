<script setup lang="ts">
import ActionButton from '@/Components/ActionButton.vue';
import ActionLink from '@/Components/ActionLink.vue';
import FixturesTable from '@/Components/FixturesTable.vue';
import InfoWithActions from '@/Components/InfoWithActions.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { Resource, Tournament } from '@/types';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps<{
    tournament: Resource<Tournament>;
}>();

const generateFixtures = () => {
    router.post(
        route('tournaments.fixtures.generate', {
            tournament: props.tournament.data.id,
        }),
        {},
        {
            onSuccess: () => {
                router.reload();
            },
            onError: (error) => {
                console.error('Error generating fixtures:', error);
            },
        },
    );
};
</script>

<template>
    <Head title="Tournaments" />

    <MainLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                {{ tournament.data.name }} - Fixtures
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <InfoWithActions>
                    <template #actions>
                        <ActionButton
                            v-if="!tournament.data.number_of_weeks"
                            @click="generateFixtures"
                            class="rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-600"
                        >
                            Generate Fixtures
                        </ActionButton>
                        <ActionLink
                            v-else
                            :href="
                                route('tournaments.simulation.index', {
                                    tournament: props.tournament.data.id,
                                })
                            "
                        >
                            Go to Simulation
                        </ActionLink>
                    </template>
                </InfoWithActions>


                <FixturesTable
                    :tournament="tournament"
                    :show-all-weeks="true"
                />
            </div>
        </div>
    </MainLayout>
</template>
