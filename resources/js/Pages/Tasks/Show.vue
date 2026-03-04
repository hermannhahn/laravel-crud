<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps<{
    task: {
        id: number;
        title: string;
        description: string;
        status: string;
        status_label: string;
        company: { name: string };
        profession: { id: number | null, name: string | null };
        service: { id: number | null, title: string | null, payout: number | null };
        professional: { id: number | null, name: string | null };
        responses: Array<{
            id: number;
            message: string;
            created_at: string;
            user: { name: string; avatar_url?: string };
        }>;
        can: { update: boolean; delete: boolean; respond: boolean };
    };
}>();

const form = useForm({
    message: '',
});

const submitResponse = () => {
    form.post(route('tasks.respond', props.task.id), {
        onSuccess: () => form.reset(),
    });
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
    <Head :title="$page.props.taskLabelSingular + ': ' + (task.service.title || task.title)" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $page.props.taskLabelSingular }} Details
                </h2>
                <div class="space-x-2">
                    <!-- Professional Actions -->
                    <button 
                        v-if="$page.props.auth.user.user_type === 'professional' && !task.professional.id"
                        @click="router.post(route('tasks.accept', task.id))"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                        >
                        Accept {{ $page.props.taskLabelSingular }}
                        </button>
                    <button 
                        v-if="$page.props.auth.user.id === task.professional.id"
                        @click="router.post(route('tasks.release', task.id))"
                        class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700 active:bg-yellow-900 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Release {{ $page.props.taskLabelSingular }}
                    </button>

                    <button 
                        v-if="$page.props.auth.user.user_type === 'company' && task.professional.id"
                        @click="router.post(route('tasks.unassign', task.id))"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                    >
                        Unassign Professional from {{ $page.props.taskLabelSingular }}
                    </button>

                    <Link v-if="task.can.update" :href="route('tasks.edit', task.id)">
                        <PrimaryButton>Edit {{ $page.props.taskLabelSingular }}</PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex flex-wrap justify-between items-start gap-4 mb-6">
                            <div class="flex-1 min-w-[300px]">
                                <h3 class="text-2xl font-bold mb-2">{{ task.service.title || task.title }}</h3>
                                
                                <div class="flex flex-wrap items-center gap-3 mb-6">
                                    <span v-if="task.profession.name" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200 uppercase">
                                        {{ task.profession.name }}
                                    </span>
                                    <span v-if="task.service.payout" class="text-xl font-black text-indigo-600 dark:text-indigo-400">
                                        Payout: ${{ Number(task.service.payout).toLocaleString() }}
                                    </span>
                                </div>

                                <p class="text-gray-600 dark:text-gray-400 whitespace-pre-wrap">{{ task.description }}</p>
                            </div>
                            
                            <div class="w-full md:w-64 space-y-4">
                                <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                                    <span class="block text-xs font-semibold text-gray-500 uppercase mb-1">Status</span>
                                    <span :class="['px-2.5 py-0.5 rounded-full text-sm font-medium', getStatusClass(task.status)]">
                                        {{ task.status_label }}
                                    </span>
                                </div>
                                
                                <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                                    <span class="block text-xs font-semibold text-gray-500 uppercase mb-1">Involved</span>
                                    <div class="text-sm mb-2">
                                        <span class="text-gray-400 font-bold uppercase text-[10px]">Company:</span>
                                        <div class="font-medium">{{ task.company.name }}</div>
                                    </div>
                                    <div class="text-sm">
                                        <span class="text-gray-400 font-bold uppercase text-[10px]">Professional:</span>
                                        <div class="font-medium">{{ task.professional.name || 'Unassigned' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-8 border-gray-200 dark:border-gray-700" />

                        <!-- Responses Section -->
                        <div class="space-y-6">
                            <h4 class="text-lg font-bold">Responses & Updates</h4>
                            
                            <div v-if="task.responses.length === 0" class="text-gray-500 italic">No responses yet.</div>
                            
                            <div v-for="response in task.responses" :key="response.id" class="flex gap-4 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg">
                                <div class="flex-1">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="font-bold text-sm">{{ response.user.name }}</span>
                                        <span class="text-xs text-gray-500">{{ response.created_at }}</span>
                                    </div>
                                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ response.message }}</p>
                                </div>
                            </div>

                            <!-- Add Response Form -->
                            <form v-if="task.can.respond" @submit.prevent="submitResponse" class="mt-8">
                                <InputLabel for="message" value="Post a response" />
                                <textarea
                                    id="message"
                                    v-model="form.message"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    rows="3"
                                    placeholder="Write your update here..."
                                    required
                                ></textarea>
                                <InputError :message="form.errors.message" class="mt-2" />
                                <PrimaryButton class="mt-4" :disabled="form.processing">Send Response</PrimaryButton>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
