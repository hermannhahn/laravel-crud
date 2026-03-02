<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps<{
    tasks: {
        data: Array<{
            id: number;
            title: string;
            description: string;
            status: string;
            status_label: string;
            due_date_formatted: string;
            company: { name: string };
            area: { id: number | null, name: string | null };
            professional: { id: number | null, name: string | null };
            can: { update: boolean; delete: boolean; respond: boolean };
        }>;
        links: Array<any>;
    };
    can: {
        create: boolean;
    };
}>();

const deleteTask = (id: number) => {
    if (confirm('Are you sure you want to delete this task?')) {
        router.delete(route('tasks.destroy', id));
    }
};

const getStatusClass = (status: string) => {
    return {
        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': status === 'pending',
        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': status === 'in_progress',
        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': status === 'completed',
    };
};
</script>

<template>
    <Head title="Tasks" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Tasks</h2>
                <Link v-if="can.create" :href="route('tasks.create')">
                    <PrimaryButton>New Task</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Flash Messages -->
                <div v-if="$page.props.flash.message" class="mb-4 p-4 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300 rounded-lg">
                    {{ $page.props.flash.message }}
                </div>
                <div v-if="$page.props.flash.error" class="mb-4 p-4 bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300 rounded-lg">
                    {{ $page.props.flash.error }}
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div v-if="tasks.data.length === 0" class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">No tasks found.</p>
                        </div>
                        
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Task / Details</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Responsible</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="task in tasks.data" :key="task.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150">
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ task.title }}</div>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ task.due_date_formatted || 'No deadline' }}</span>
                                                <span v-if="task.area.name" class="text-[10px] bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400 px-1.5 py-0.5 rounded border border-gray-200 dark:border-gray-700 font-bold uppercase">{{ task.area.name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="['px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusClass(task.status)]">
                                                {{ task.status_label }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <div v-if="$page.props.auth.user.user_type === 'professional'">
                                                <span class="block text-xs uppercase text-gray-400">Company</span>
                                                {{ task.company.name }}
                                            </div>
                                            <div v-else>
                                                <span class="block text-xs uppercase text-gray-400">Professional</span>
                                                {{ task.professional.name || 'Unassigned' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                            <button 
                                                v-if="$page.props.auth.user.user_type === 'professional' && !task.professional.id"
                                                @click="router.post(route('tasks.accept', task.id))"
                                                class="text-green-600 dark:text-green-400 hover:underline"
                                            >
                                                Accept
                                            </button>
                                            <Link :href="route('tasks.show', task.id)" class="text-blue-600 dark:text-blue-400 hover:underline">View</Link>
                                            <Link v-if="task.can.update" :href="route('tasks.edit', task.id)" class="text-indigo-600 dark:text-indigo-400 hover:underline">Edit</Link>
                                            <button v-if="task.can.delete" @click="deleteTask(task.id)" class="text-red-600 dark:text-red-400 hover:underline">Delete</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="tasks.links && tasks.links.length > 3" class="mt-6 flex justify-center">
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <Link 
                                    v-for="(link, index) in tasks.links" 
                                    :key="index"
                                    :href="link.url || '#'"
                                    v-html="link.label"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
                                    :class="{
                                        'bg-indigo-50 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-200 z-10': link.active,
                                        'cursor-default opacity-50': !link.url
                                    }"
                                />
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
