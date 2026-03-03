<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps<{
    areas: Array<{ id: number; name: string }>;
    companies: Array<{ id: number; name: string }>;
    selectedCompanyId?: number | string;
}>();

const form = useForm({
    title: '',
    description: '',
    price: '',
    task_area_id: '',
    company_id: props.selectedCompanyId || '',
});

const onCompanyChange = () => {
    router.reload({
        data: { company_id: form.company_id },
        only: ['areas'],
    });
};

const submit = () => {
    form.post(route('services.store'));
};
</script>

<template>
    <Head title="Add Service" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Add New Service</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100 max-w-2xl">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Admin: Company Selection -->
                                <div v-if="$page.props.auth.user.role === 'admin'">
                                    <InputLabel for="company_id" value="Company" />
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

                                <!-- Area Selection -->
                                <div>
                                    <InputLabel for="task_area_id" value="Area" />
                                    <select
                                        id="task_area_id"
                                        v-model="form.task_area_id"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        required
                                        :disabled="!form.company_id && $page.props.auth.user.role === 'admin'"
                                    >
                                        <option value="" disabled>Select area...</option>
                                        <option v-for="area in areas" :key="area.id" :value="area.id">
                                            {{ area.name }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.task_area_id" class="mt-2" />
                                </div>

                                <!-- Title -->
                                <div class="md:col-span-2">
                                    <InputLabel for="title" value="Service Title" />
                                    <TextInput
                                        id="title"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.title"
                                        required
                                        placeholder="e.g., Website Maintenance"
                                    />
                                    <InputError :message="form.errors.title" class="mt-2" />
                                </div>

                                <!-- Description -->
                                <div class="md:col-span-2">
                                    <InputLabel for="description" value="Description" />
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                        rows="3"
                                    ></textarea>
                                    <InputError :message="form.errors.description" class="mt-2" />
                                </div>

                                <!-- Payout -->
                                <div>
                                    <InputLabel for="price" value="Professional Payout (USD)" />
                                    <TextInput
                                        id="price"
                                        type="number"
                                        step="0.01"
                                        class="mt-1 block w-full"
                                        v-model="form.price"
                                        required
                                    />
                                    <InputError :message="form.errors.price" class="mt-2" />
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">Save Service</PrimaryButton>
                                <Link :href="route('services.index')" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 underline">
                                    Cancel
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
