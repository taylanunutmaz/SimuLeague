<script setup lang="ts">
import ActionButton from '@/Components/ActionButton.vue';
import BaseTable from '@/Components/BaseTable.vue';
import DangerousActionButton from '@/Components/DangerousActionButton.vue';
import FixturesTable from '@/Components/FixturesTable.vue';
import InfoWithActions from '@/Components/InfoWithActions.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import {
    PredictedChampionshipRates,
    Resource,
    Standing,
    Tournament,
} from '@/types';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps<{
    tournament: Resource<Tournament>;
    standings: Resource<Standing[]>;
    predicted_championship_rates: Array<PredictedChampionshipRates>;
}>();

const startSimulation = () => {
    router.post(
        route('tournaments.simulation.start', {
            tournament: props.tournament.data.id,
        }),
    );
};

const playAllWeeks = () => {
    router.post(
        route('tournaments.simulation.play-all-weeks', {
            tournament: props.tournament.data.id,
        }),
    );
};

const playNextWeek = () => {
    router.post(
        route('tournaments.simulation.play-next-week', {
            tournament: props.tournament.data.id,
        }),
    );
};

const resetData = () => {
    router.post(
        route('tournaments.simulation.reset', {
            tournament: props.tournament.data.id,
        }),
    );
};
</script>

<template>
    <Head title="Tournaments" />

    <MainLayout>
        <template #header>
            <h1
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                {{ tournament.data.name }} - Simulation
            </h1>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                <InfoWithActions>
                    <template #info>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Tournament Status:
                            <strong>{{ tournament.data.status }}</strong>
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Weeks:
                            <strong>
                                {{ tournament.data.last_played_week }} /
                                {{ tournament.data.number_of_weeks }}
                            </strong>
                        </p>
                    </template>
                    <template #actions>
                        <ActionButton
                            v-if="tournament.data.status === 'not_started'"
                            @click="startSimulation"
                        >
                            Start Simulation
                        </ActionButton>
                        <ActionButton
                            v-if="tournament.data.status === 'in_progress'"
                            @click="playAllWeeks"
                        >
                            Play All Weeks
                        </ActionButton>
                        <ActionButton
                            v-if="tournament.data.status === 'in_progress'"
                            @click="playNextWeek"
                        >
                            Play Next Week
                        </ActionButton>
                        <DangerousActionButton
                            v-if="tournament.data.status !== 'not_started'"
                            @click="resetData"
                        >
                            Reset Data
                        </DangerousActionButton>
                    </template>
                </InfoWithActions>

                <BaseTable
                    :headers="[
                        'Position',
                        'Team',
                        'Played',
                        'Won',
                        'Drawn',
                        'Lost',
                        'GD',
                        'Points',
                    ]"
                    :rows="
                        standings.data.map((standing, i) => [
                            i + 1,
                            standing.team?.name,
                            standing.played,
                            standing.won,
                            standing.drawn,
                            standing.lost,
                            standing.goal_difference,
                            standing.points,
                        ])
                    "
                >
                </BaseTable>

                <BaseTable
                    v-if="predicted_championship_rates.length > 0"
                    :headers="['Team', 'Predicted Championship Rate']"
                    :rows="
                        predicted_championship_rates.map((rate) => [
                            rate.team.name,
                            rate.championship_probability,
                        ])
                    "
                >
                </BaseTable>

                <FixturesTable :tournament="tournament" />
            </div>
        </div>
    </MainLayout>
</template>
