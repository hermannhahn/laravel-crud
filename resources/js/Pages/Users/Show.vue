<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        role: string;
        is_active: boolean;
        avatar_url: string | null;
        created_at: string;
        tasks_count: number;
    };
}>();
</script>

<template>
    <Head :title="'User Profile - ' + user.name" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                User Details: {{ user.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center gap-6 mb-8">
                            <div v-if="user.avatar_url">
                                <img :src="user.avatar_url" alt="Avatar" class="w-32 h-32 rounded-full object-cover shadow-lg border-4 border-white dark:border-gray-700" />
                            </div>
                            <div v-else class="w-32 h-32 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-4xl font-bold text-gray-400">
                                {{ user.name.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold">{{ user.name }}</h3>
                                <p class="text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                                <div class="mt-2 flex gap-2">
                                    <span :class="[
                                        'px-2 py-1 rounded text-xs font-semibold uppercase',
                                        user.role === 'admin' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'
                                    ]">
                                        {{ user.role }}
                                    </span>
                                    <span :class="[
                                        'px-2 py-1 rounded text-xs font-semibold uppercase',
                                        user.is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
                                    ]">
                                        {{ user.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="border dark:border-gray-700 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase mb-2">Account Information</h4>
                                <div class="space-y-3">
                                    <div>
                                        <span class="block text-xs text-gray-400">Member Since</span>
                                        <span class="text-lg">{{ user.created_at }}</span>
                                    </div>
                                    <div>
                                        <span class="block text-xs text-gray-400">Total Tasks</span>
                                        <span class="text-lg font-semibold">{{ user.tasks_count }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="border dark:border-gray-700 rounded-lg p-4">
                                <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase mb-2">Quick Actions</h4>
                                <div class="flex flex-wrap gap-3">
                                    <Link :href="route('users.permissions.edit', user.id)" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                                        Manage Permissions
                                    </Link>
                                    <Link :href="route('users.index')" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:ring ring-blue-200 disabled:opacity-25 transition ease-in-out duration-150">
                                        Back to List
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
