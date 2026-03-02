<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps<{
    team: Array<{
        id: number;
        name: string;
        email: string;
        assigned_tasks_count: number;
        pivot: {
            task_area_id: number | null;
            can_view_tasks: boolean;
            can_respond_tasks: boolean;
            can_edit_tasks: boolean;
        };
    }>;
    availableProfessionals: Array<{
        id: number;
        name: string;
        email: string;
    }>;
    areas: Array<{
        id: number;
        name: string;
    }>;
}>();

const addForm = useForm({
    professional_id: '',
    task_area_id: '',
});

const addProfessional = () => {
    addForm.post(route('team.add'), {
        onSuccess: () => addForm.reset(),
    });
};

const updatePermissions = (user: any) => {
    router.patch(route('team.update-permissions', user.id), {
        task_area_id: user.pivot.task_area_id,
        can_view_tasks: user.pivot.can_view_tasks,
        can_respond_tasks: user.pivot.can_respond_tasks,
        can_edit_tasks: user.pivot.can_edit_tasks,
    }, { preserveScroll: true });
};

const removeProfessional = (id: number) => {
    if (confirm('Remove this professional from your team?')) {
        router.delete(route('team.remove', id));
    }
};
</script>

<template>
    <Head title="My Team" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Team Management</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Add Professional -->
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <section class="max-w-xl">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Add Professional</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Add a registered professional to your company's team.</p>
                        </header>

                        <form @submit.prevent="addProfessional" class="mt-6 flex flex-wrap items-end gap-4">
                            <div class="flex-1 min-w-[200px]">
                                <InputLabel for="professional" value="Select Professional" />
                                <select
                                    id="professional"
                                    v-column="addForm.professional_id"
                                    v-model="addForm.professional_id"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                >
                                    <option value="" disabled>Choose a professional...</option>
                                    <option v-for="pro in availableProfessionals" :key="pro.id" :value="pro.id">
                                        {{ pro.name }} ({{ pro.email }})
                                    </option>
                                </select>
                            </div>
                            <div class="flex-1 min-w-[200px]">
                                <InputLabel for="task_area_id" value="Assign Area" />
                                <select
                                    id="task_area_id"
                                    v-model="addForm.task_area_id"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="" disabled>Choose area...</option>
                                    <option v-for="area in areas" :key="area.id" :value="area.id">
                                        {{ area.name }}
                                    </option>
                                </select>
                            </div>
                            <PrimaryButton :disabled="addForm.processing || !addForm.professional_id || !addForm.task_area_id">Add</PrimaryButton>
                        </form>
                    </section>
                </div>

                <!-- Team List -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium mb-4">Current Team</h3>
                        <div v-if="team.length === 0" class="text-gray-500 py-4">No professionals in your team yet.</div>
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                                        <th class="px-4 py-3">Professional</th>
                                        <th class="px-4 py-3 text-center">Area</th>
                                        <th class="px-4 py-3 text-center">Tasks</th>
                                        <th class="px-4 py-3 text-center">View</th>
                                        <th class="px-4 py-3 text-center">Respond</th>
                                        <th class="px-4 py-3 text-center">Edit</th>
                                        <th class="px-4 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="member in team" :key="member.id">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium">{{ member.name }}</div>
                                            <div class="text-xs text-gray-500">{{ member.email }}</div>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <select 
                                                v-model="member.pivot.task_area_id" 
                                                @change="updatePermissions(member)"
                                                class="text-xs border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"
                                            >
                                                <option v-for="area in areas" :key="area.id" :value="area.id">
                                                    {{ area.name }}
                                                </option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-4 text-center text-sm font-bold">{{ member.assigned_tasks_count }}</td>
                                        <td class="px-4 py-4 text-center">
                                            <Checkbox v-model:checked="member.pivot.can_view_tasks" @change="updatePermissions(member)" />
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <Checkbox v-model:checked="member.pivot.can_respond_tasks" @change="updatePermissions(member)" />
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <Checkbox v-model:checked="member.pivot.can_edit_tasks" @change="updatePermissions(member)" />
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <button @click="removeProfessional(member.id)" class="text-red-600 hover:text-red-900 text-sm">Remove</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
