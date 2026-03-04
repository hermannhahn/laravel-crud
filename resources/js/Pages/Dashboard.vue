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
        monthly_earnings?: number;
        total_projected_earnings?: number;
        monthly_spent?: number;
        total_committed?: number;
        total_projected_revenue?: number;
        monthly_revenue?: number;
        commission_rate?: number;
    };
}>();

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};

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
            label: page.props.taskLabelPlural + ' Created',
            backgroundColor: '#6366f1',
            data: props.chartData.map(d => d.created || 0)
        },
        {
            label: page.props.taskLabelPlural + ' Completed',
            backgroundColor: '#10b981',
            data: props.chartData.map(d => d.completed)
        }
    ]
}));

const professionalChartData = computed(() => ({
    labels: props.chartData.map(d => d.date),
    datasets: [
        {
            label: page.props.taskLabelPlural + ' Completed',
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
                <div v-if="stats" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Pending Tasks (Shared) -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border-l-4 border-yellow-500">
                        <div class="text-sm text-gray-500 dark:text-gray-400 uppercase font-bold">Total Pending {{ $page.props.taskLabelPlural }}</div>
                        <div class="text-3xl font-bold dark:text-white">{{ stats.pending }}</div>
                    </div>

                    <!-- Completed Tasks (Shared) -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border-l-4 border-green-500">
                        <div class="text-sm text-gray-500 dark:text-gray-400 uppercase font-bold">{{ $page.props.taskLabelPlural }} Completed (This Month)</div>
                        <div class="text-3xl font-bold dark:text-white">{{ stats.completed }}</div>
                    </div>

                    <!-- Admin Revenue (Admin only) -->
                    <template v-if="user.role === 'admin'">
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border-l-4 border-orange-500">
                            <div class="text-sm text-gray-500 dark:text-gray-400 uppercase font-bold">Projected Revenue (Total Pending)</div>
                            <div class="text-3xl font-bold dark:text-white text-orange-600">{{ formatCurrency(stats.total_projected_revenue || 0) }}</div>
                            <div class="text-xs text-gray-400 mt-1">From all in-progress {{ $page.props.taskLabelPlural }}</div>
                        </div>

                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border-l-4 border-purple-500">
                            <div class="text-sm text-gray-500 dark:text-gray-400 uppercase font-bold">Monthly Platform Revenue</div>
                            <div class="text-3xl font-bold dark:text-white text-purple-600">{{ formatCurrency(stats.monthly_revenue || 0) }}</div>
                            <div class="text-xs text-gray-400 mt-1">Rate: {{ stats.commission_rate }}% (This Month)</div>
                        </div>
                    </template>

                    <!-- Financial Stats (Company only) -->
                    <template v-if="user.user_type === 'company' && user.role !== 'admin'">
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border-l-4 border-blue-500">
                            <div class="text-sm text-gray-500 dark:text-gray-400 uppercase font-bold">Spent This Month</div>
                            <div class="text-3xl font-bold dark:text-white text-blue-600">{{ formatCurrency(stats.monthly_spent || 0) }}</div>
                        </div>

                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border-l-4 border-indigo-500">
                            <div class="text-sm text-gray-500 dark:text-gray-400 uppercase font-bold">Total Committed (Pending)</div>
                            <div class="text-3xl font-bold dark:text-white text-indigo-600">{{ formatCurrency(stats.total_committed || 0) }}</div>
                            <div class="text-xs text-gray-400 mt-1">All in-progress {{ $page.props.taskLabelPlural }}</div>
                        </div>
                    </template>

                    <!-- Financial Stats (Professional only) -->
                    <template v-if="user.user_type === 'professional'">
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border-l-4 border-indigo-500">
                            <div class="text-sm text-gray-500 dark:text-gray-400 uppercase font-bold">Earnings This Month</div>
                            <div class="text-3xl font-bold dark:text-white text-indigo-600">{{ formatCurrency(stats.monthly_earnings || 0) }}</div>
                        </div>

                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border-l-4 border-orange-500">
                            <div class="text-sm text-gray-500 dark:text-gray-400 uppercase font-bold">Total Projected Earnings</div>
                            <div class="text-3xl font-bold dark:text-white text-orange-600">{{ formatCurrency(stats.total_projected_earnings || 0) }}</div>
                            <div class="text-xs text-gray-400 mt-1">All in-progress {{ $page.props.taskLabelPlural }}</div>
                        </div>
                    </template>
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
