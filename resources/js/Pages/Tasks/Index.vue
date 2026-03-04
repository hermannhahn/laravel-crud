<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref, watch, computed } from 'vue';

const props = defineProps<{
    tasks: {
        data: Array<{
            id: number;
            title: string;
            description: string;
            status: string;
            status_label: string;
            due_date_formatted: string;
            company: { name: string };
            profession: { id: number | null, name: string | null };
            service: { id: number | null, title: string | null, payout: number | null };
            professional: { id: number | null, name: string | null };
            can: { update: boolean; delete: boolean; respond: boolean };
        }>;
        links: Array<any>;
    };
    filters: {
        search?: string;
        status?: string;
        profession_id?: string;
        sort?: string;
    };
    availableProfessions: Array<{ id: number; name: string }>;
    can: {
        create: boolean;
    };
}>();

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const profession_id = ref(props.filters.profession_id || '');
const sort = ref(props.filters.sort || 'latest');

const updateFilters = () => {
    router.get(route('tasks.index'), {
        search: search.value,
        status: status.value,
        profession_id: profession_id.value,
        sort: sort.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

watch([search, status, profession_id, sort], () => {
    updateFilters();
});

const resetFilters = () => {
    search.value = '';
    status.value = '';
    profession_id.value = '';
    sort.value = 'latest';
};

const getStatusClass = (status: string) => {
    return {
        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300': status === 'pending',
        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300': status === 'in_progress',
        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300': status === 'completed',
    };
};

const deleteTask = (id: number) => {
    if (confirm('Are you sure you want to delete this task?')) {
        router.delete(route('tasks.destroy', id));
    }
};
</script>

<template>
    <Head :title="$page.props.taskLabelPlural" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $page.props.taskLabelPlural }}</h2>
                <Link v-if="can.create" :href="route('tasks.create')">
                    <PrimaryButton>New {{ $page.props.taskLabelSingular }}</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Filters -->
                <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow-sm space-y-4">
                    <div class="flex flex-wrap items-center gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <TextInput 
                                v-model="search" 
                                type="text" 
                                :placeholder="'Search ' + $page.props.taskLabelPlural.toLowerCase() + ', professionals, companies...'" 
                                class="w-full text-sm"
                            />
                        </div>
                        
                        <select v-model="status" class="text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>

                        <select v-model="profession_id" class="text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                            <option value="">All Professions</option>
                            <option v-for="prof in availableProfessions" :key="prof.id" :value="prof.id">
                                {{ prof.name }}
                            </option>
                        </select>

                        <select v-model="sort" class="text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                            <option value="latest">Latest</option>
                            <option value="oldest">Oldest</option>
                            <option value="due_date">Due Date</option>
                        </select>

                        <button @click="resetFilters" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                            Reset
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div v-if="tasks.data.length === 0" class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">No {{ $page.props.taskLabelPlural.toLowerCase() }} found matching your criteria.</p>
                        </div>
                        
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $page.props.taskLabelSingular }} / Details</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Company</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr 
                                        v-for="task in tasks.data" 
                                        :key="task.id" 
                                        @click="router.get(route('tasks.show', task.id))"
                                        class="hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150 cursor-pointer"
                                    >
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ task.service.title || task.title }}
                                            </div>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span v-if="task.profession.name" class="text-[10px] bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400 px-1.5 py-0.5 rounded border border-gray-200 dark:border-gray-700 font-bold uppercase">{{ task.profession.name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="['px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusClass(task.status)]">
                                                {{ task.status_label }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            <div v-if="$page.props.auth.user.user_type === 'professional'">
                                                {{ task.company.name }}
                                            </div>
                                            <div v-else>
                                                {{ task.professional.name || 'Unassigned' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                <button 
                                                    v-if="$page.props.auth.user.user_type === 'professional' && task.status === 'pending' && (!task.professional.id || task.professional.id === $page.props.auth.user.id)"
                                                    @click.stop="router.post(route('tasks.accept', task.id))"
                                                    class="inline-flex items-center justify-center p-1.5 text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/30 rounded-full transition-colors"
                                                    :title="'Accept ' + $page.props.taskLabelSingular"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                </button>
                                                
                                                <Link 
                                                    v-if="task.can.update" 
                                                    :href="route('tasks.edit', task.id)" 
                                                    @click.stop 
                                                    class="inline-flex items-center justify-center p-1.5 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-full transition-colors"
                                                    :title="'Edit ' + $page.props.taskLabelSingular"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                </Link>

                                                <button 
                                                    v-if="task.can.delete" 
                                                    @click.stop="deleteTask(task.id)" 
                                                    class="inline-flex items-center justify-center p-1.5 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-full transition-colors"
                                                    :title="'Delete ' + $page.props.taskLabelSingular"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </div>
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
