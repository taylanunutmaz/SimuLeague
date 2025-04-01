<script setup lang="ts">
import { Resource, Tournament } from '@/types';
import { computed } from 'vue';
import BaseTable from './BaseTable.vue';

const props = defineProps<{
    tournament: Resource<Tournament>;
    showAllWeeks?: boolean;
}>();

const weeks = computed(() =>
    props.showAllWeeks
        ? props.tournament.data.number_of_weeks
        : props.tournament.data.last_played_week,
);

const getMatchesOfWeek = (week: number) => {
    const matches = props.tournament.data.matches?.filter(
        (match) => match.week === week,
    );

    return (
        matches?.map((match) => [
            `${match.home_team?.name} <strong>${match.home_score ?? ''} - ${match.away_score ?? ''}</strong> ${match.away_team?.name}`,
        ]) || []
    );
};
</script>

<template>
    <div
        v-if="tournament.data.number_of_weeks"
        class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"
    >
        <BaseTable
            v-for="week in weeks"
            :key="week"
            :headers="['Week ' + week]"
            :rows="getMatchesOfWeek(week)"
        >
        </BaseTable>
    </div>
    <div v-else class="flex flex-col items-center justify-center">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            No fixtures available.
        </p>
    </div>
</template>
