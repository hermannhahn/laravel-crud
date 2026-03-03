<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps<{
    content: string;
}>();

// Helper to format the content: split by double newlines for paragraphs
// and handle single newlines as line breaks.
const formattedParagraphs = props.content.split(/\n\n+/);
</script>

<template>
    <Head title="Help & Support" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Help & Support</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700">
                    <!-- Decorative Top Bar -->
                    <div class="h-2 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>
                    
                    <div class="p-8 md:p-12">
                        <div class="flex items-center mb-8">
                            <div class="p-3 bg-indigo-100 dark:bg-indigo-900/50 rounded-xl mr-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white">System Information</h3>
                                <p class="text-gray-500 dark:text-gray-400">Resources and contact details for the platform.</p>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div v-for="(paragraph, index) in formattedParagraphs" :key="index" 
                                 class="bg-gray-50 dark:bg-gray-700/30 p-6 rounded-2xl border border-gray-100 dark:border-gray-700">
                                <p class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                                    {{ paragraph }}
                                </p>
                            </div>

                            <div v-if="!content" class="text-center py-12">
                                <p class="text-gray-500 dark:text-gray-400 italic">No help information available at the moment.</p>
                            </div>
                        </div>

                        <div class="mt-12 pt-8 border-t border-gray-100 dark:border-gray-700 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                            <div class="text-sm text-gray-500 dark:text-gray-400 italic">
                                Need more help? Our support team is here for you.
                            </div>
                            <div class="flex gap-4">
                                <Link :href="route('dashboard')">
                                    <PrimaryButton class="!bg-gray-800 dark:!bg-gray-200 !text-white dark:!text-gray-800">
                                        Back to Dashboard
                                    </PrimaryButton>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
