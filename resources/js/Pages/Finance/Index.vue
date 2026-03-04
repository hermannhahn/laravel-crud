<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps<{
    tasks: {
        data: any[];
        links: any[];
    };
    filters: {
        search?: string;
        date_from?: string;
        date_to?: string;
    };
    stats: {
        total_amount: number;
        count: number;
    };
}>();

const user = usePage().props.auth.user;
const search = ref(props.filters.search || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');

const filter = debounce(() => {
    router.get(route('finance.index'), {
        search: search.value,
        date_from: dateFrom.value,
        date_to: dateTo.value,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300);

watch([search, dateFrom, dateTo], filter);

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
};
</script>

<template>
    <Head title="Finance" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Finance</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase">
                            {{ user.role === 'admin' ? 'Total Volume' : (user.user_type === 'company' ? 'Total Spent' : 'Total Earned') }}
                        </div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                            {{ formatCurrency(stats.total_amount) }}
                        </div>
                    </div>
                    <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 rounded-lg shadow-sm">
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase">
                            Completed Tasks
                        </div>
                        <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-white">
                            {{ stats.count }}
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="p-6 mb-6 bg-white dark:bg-gray-800 rounded-lg shadow-sm">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <InputLabel for="search" value="Search" />
                            <TextInput
                                id="search"
                                v-model="search"
                                type="text"
                                class="block w-full mt-1"
                                placeholder="Task, Company or Professional..."
                            />
                        </div>
                        <div>
                            <InputLabel for="date_from" value="From" />
                            <TextInput
                                id="date_from"
                                v-model="dateFrom"
                                type="date"
                                class="block w-full mt-1"
                            />
                        </div>
                        <div>
                            <InputLabel for="date_to" value="To" />
                            <TextInput
                                id="date_to"
                                v-model="dateTo"
                                type="date"
                                class="block w-full mt-1"
                            />
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-gray-700">
                                    <th class="px-6 py-4 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                    <th class="px-6 py-4 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">{{ $page.props.taskLabelSingular }}</th>
                                    <th v-if="user.user_type !== 'company'" class="px-6 py-4 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Company</th>
                                    <th v-if="user.user_type !== 'professional'" class="px-6 py-4 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Professional</th>
                                    <th class="px-6 py-4 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="task in tasks.data" :key="task.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        {{ formatDate(task.completed_at) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                        {{ task.title }}
                                    </td>
                                    <td v-if="user.user_type !== 'company'" class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        {{ task.company?.name }}
                                    </td>
                                    <td v-if="user.user_type !== 'professional'" class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        {{ task.professional?.name || 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-gray-100 text-right">
                                        {{ formatCurrency(task.payout) }}
                                    </td>
                                </tr>
                                <tr v-if="tasks.data.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                        No financial records found for this period.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="tasks.links.length > 3" class="mt-6 flex justify-center">
                    <template v-for="(link, k) in tasks.links" :key="k">
                        <div v-if="link.url === null" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 dark:text-gray-600 border dark:border-gray-700 rounded" v-html="link.label" />
                        <Link v-else :href="link.url" 
                            class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border dark:border-gray-700 rounded hover:bg-white dark:hover:bg-gray-700 focus:border-indigo-500 focus:text-indigo-500 dark:text-gray-300" 
                            :class="{ 'bg-blue-700 text-white dark:bg-blue-600': link.active }" 
                            v-html="link.label" 
                        />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
