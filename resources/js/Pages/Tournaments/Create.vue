<script setup lang="ts">
import ActionButton from '@/Components/ActionButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import MultiSelect from '@/Components/MultiSelect.vue';
import TextInput from '@/Components/TextInput.vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import { Resource, Team } from '@/types';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
    teams: Resource<Team[]>;
}>();

const form = useForm({
    name: '',
    team_ids: [] as number[],
});

const submit = () => {
    form.post('/tournaments', {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <MainLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                Create Tournament
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div
                    class="bg-white p-6 shadow sm:rounded-lg dark:bg-gray-800 dark:text-gray-100"
                >
                    <form @submit.prevent="submit">
                        <div>
                            <InputLabel
                                for="name"
                                value="Tournament Name"
                                class="text-gray-700 dark:text-gray-300"
                            />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                placeholder="Enter tournament name"
                                class="mt-1 block w-full bg-white dark:bg-gray-700 dark:text-gray-100 dark:placeholder-gray-400"
                            />
                            <InputError
                                v-if="form.errors.name"
                                :message="form.errors.name"
                                class="text-red-500 dark:text-red-400"
                            />
                        </div>

                        <div class="mt-4">
                            <InputLabel
                                for="teams"
                                value="Select Teams"
                                class="text-gray-700 dark:text-gray-300"
                            />
                            <MultiSelect
                                id="teams"
                                :options="props.teams.data"
                                v-model="form.team_ids"
                                class="bg-white dark:bg-gray-700 dark:text-gray-100"
                            />
                            <InputError
                                v-if="form.errors.team_ids"
                                :message="form.errors.team_ids"
                                class="text-red-500 dark:text-red-400"
                            />
                        </div>

                        <div class="mt-6 flex justify-end">
                            <ActionButton
                                type="submit"
                                class="rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700"
                            >
                                Create Tournament
                            </ActionButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
