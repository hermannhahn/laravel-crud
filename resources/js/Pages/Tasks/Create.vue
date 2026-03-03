<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps<{
    professionals: Array<{
        id: number;
        name: string;
    }>;
    areas: Array<{
        id: number;
        name: string;
    }>;
    services: Array<{
        id: number;
        title: string;
        task_area_id: number;
        price: number;
    }>;
    companies: Array<{
        id: number;
        name: string;
    }>;
}>();

const form = useForm({
    title: '',
    description: '',
    status: 'pending',
    due_date: '',
    professional_id: '',
    task_area_id: '' as number | '',
    service_id: '' as number | '',
    company_id: '',
});

const filteredServices = computed(() => {
    if (!form.task_area_id) return [];
    return props.services.filter(s => s.task_area_id === Number(form.task_area_id));
});

const onCompanyChange = () => {
    if (form.company_id) {
        router.reload({
            data: { company_id: form.company_id },
            only: ['areas', 'professionals', 'services'],
        });
    }
};

const onServiceChange = () => {
    const service = props.services.find(s => s.id === Number(form.service_id));
    if (service) {
        if (!form.title) form.title = service.title;
    }
};

const submit = () => {
    form.post(route('tasks.store'), {
        onFinish: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Create Task" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Create Task</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100 max-w-xl">
                        <form @submit.prevent="submit">
                            <!-- Admin only: Company Selection -->
                            <div v-if="$page.props.auth.user.role === 'admin'" class="mb-6 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-indigo-100 dark:border-indigo-900">
                                <InputLabel for="company_id" value="Owner Company (Admin only)" />
                                <select
                                    id="company_id"
                                    v-model="form.company_id"
                                    @change="onCompanyChange"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="" disabled>Select company...</option>
                                    <option v-for="company in companies" :key="company.id" :value="company.id">
                                        {{ company.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.company_id" class="mt-2" />
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <InputLabel for="task_area_id" value="Task Area" />
                                    <select
                                        id="task_area_id"
                                        v-model="form.task_area_id"
                                        @change="form.service_id = ''"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        required
                                    >
                                        <option value="" disabled>Select area...</option>
                                        <option v-for="area in areas" :key="area.id" :value="area.id">
                                            {{ area.name }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.task_area_id" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="service_id" value="Service" />
                                    <select
                                        id="service_id"
                                        v-model="form.service_id"
                                        @change="onServiceChange"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        :disabled="!form.task_area_id"
                                        required
                                    >
                                        <option value="" disabled>Select service...</option>
                                        <option v-for="service in filteredServices" :key="service.id" :value="service.id">
                                            {{ service.title }} (Payout: ${{ service.price }})
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.service_id" class="mt-2" />
                                </div>
                            </div>

                            <div class="mt-4">
                                <InputLabel for="description" value="Description" />
                                <textarea
                                    id="description"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    v-model="form.description"
                                    rows="4"
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.description" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="professional_id" value="Assign Professional" />
                                <select
                                    id="professional_id"
                                    v-model="form.professional_id"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                >
                                    <option value="">No professional assigned</option>
                                    <option v-for="pro in professionals" :key="pro.id" :value="pro.id">
                                        {{ pro.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.professional_id" class="mt-2" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="status" value="Status" />
                                <select
                                    id="status"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    v-model="form.status"
                                    required
                                >
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.status" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="due_date" value="Due Date" />
                                <TextInput
                                    id="due_date"
                                    type="date"
                                    class="mt-1 block w-full"
                                    v-model="form.due_date"
                                />
                                <InputError class="mt-2" :message="form.errors.due_date" />
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <Link :href="route('tasks.index')" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 underline mr-4">
                                    Cancel
                                </Link>
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Create Task
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
