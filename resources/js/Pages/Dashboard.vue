<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Bar } from 'vue-chartjs';
import { 
    Chart as ChartJS, 
    Title, 
    Tooltip, 
    Legend, 
    BarElement, 
    CategoryScale, 
    LinearScale 
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps<{
    chartData: Array<{
        date: string;
        created?: number;
        completed: number;
        pending_accepted?: number;
    }>;
    stats?: {
        pending: number;
        completed: number;
    };
}>();

const page = usePage();
const user = computed(() => page.props.auth.user);

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                stepSize: 1
            }
        }
    }
};

const companyChartData = computed(() => ({
    labels: props.chartData.map(d => d.date),
    datasets: [
        {
            label: 'Tasks Created',
            backgroundColor: '#6366f1',
            data: props.chartData.map(d => d.created || 0)
        },
        {
            label: 'Tasks Completed',
            backgroundColor: '#10b981',
            data: props.chartData.map(d => d.completed)
        }
    ]
}));

const professionalChartData = computed(() => ({
    labels: props.chartData.map(d => d.date),
    datasets: [
        {
            label: 'Tasks Completed',
            backgroundColor: '#10b981',
            data: props.chartData.map(d => d.completed)
        },
        {
            label: 'Pending (Accepted)',
            backgroundColor: '#f59e0b',
            data: props.chartData.map(d => d.pending_accepted || 0)
        }
    ]
}));
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Stats Text Cards -->
                <div v-if="stats" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Pending Tasks (Shared) -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border-l-4 border-yellow-500">
                        <div class="text-sm text-gray-500 dark:text-gray-400 uppercase font-bold">Total Pending Tasks</div>
                        <div class="text-3xl font-bold dark:text-white">{{ stats.pending }}</div>
                    </div>

                    <!-- Completed Tasks (Shared Label for simplicity) -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border-l-4 border-green-500">
                        <div class="text-sm text-gray-500 dark:text-gray-400 uppercase font-bold">Total Completed Tasks</div>
                        <div class="text-3xl font-bold dark:text-white">{{ stats.completed }}</div>
                    </div>
                </div>

                <!-- Chart Section -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium mb-6">
                            Performance (Last 5 Days)
                            <span v-if="user.role === 'admin'" class="text-xs text-gray-400 ml-2 uppercase">(Global Data)</span>
                        </h3>
                        
                        <div class="h-[300px]">
                            <Bar 
                                v-if="user.user_type === 'company' || user.role === 'admin'"
                                :data="companyChartData"
                                :options="chartOptions"
                            />
                            <Bar 
                                v-if="user.user_type === 'professional'"
                                :data="professionalChartData"
                                :options="chartOptions"
                            />
                        </div>
                    </div>
                </div>

                <!-- Admin Info -->
                <div v-if="user.role === 'admin'" class="bg-indigo-50 dark:bg-indigo-900 p-6 rounded-lg">
                    <p class="text-indigo-700 dark:text-indigo-200">You are logged in as a <strong>System Administrator</strong>. You have full visibility across all companies and professionals.</p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
