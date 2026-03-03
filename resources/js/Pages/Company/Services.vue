<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps<{
    services: Array<{
        id: number;
        title: string;
        description: string;
        price: number;
        area: { id: number; name: string };
        company: { id: number; name: string };
    }>;
    areas: Array<{
        id: number;
        name: string;
    }>;
    companies: Array<{
        id: number;
        name: string;
    }>;
    filters: {
        company_id?: string;
    };
}>();

const form = useForm({
    title: '',
    description: '',
    price: '',
    task_area_id: '',
    company_id: props.filters.company_id || '',
});

const submit = () => {
    form.post(route('services.store'), {
        onSuccess: () => {
            form.reset('title', 'description', 'price', 'task_area_id');
        },
    });
};

const deleteService = (id: number) => {
    if (confirm('Are you sure you want to delete this service?')) {
        router.delete(route('services.destroy', id));
    }
};

const onFilterChange = () => {
    router.get(route('services.index'), { company_id: form.company_id }, {
        preserveState: true,
        replace: true,
    });
};
</script>

<template>
    <Head title="Services" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Manage Services</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Create Service -->
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Create New Service</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Offer a new predefined service to your clients.</p>
                        </header>

                        <form @submit.prevent="submit" class="mt-6 space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Admin: Company Selection -->
                                <div v-if="$page.props.auth.user.role === 'admin'">
                                    <InputLabel for="company_id" value="Company" />
                                    <select
                                        id="company_id"
                                        v-model="form.company_id"
                                        @change="onFilterChange"
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
                                        placeholder="e.g., Website Maintenance, Logo Design"
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
                                        placeholder="Describe what this service includes..."
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
                                        placeholder="Amount to be paid..."
                                    />
                                    <InputError :message="form.errors.price" class="mt-2" />
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">Create Service</PrimaryButton>
                            </div>
                        </form>
                    </section>
                </div>

                <!-- Services List -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium">Existing Services</h3>
                            <div v-if="$page.props.auth.user.role === 'admin' && !form.company_id" class="text-sm text-yellow-600 font-bold">
                                Showing all services from all companies
                            </div>
                        </div>

                        <div v-if="services.length === 0" class="text-gray-500 py-8 text-center border-2 border-dashed rounded-lg">
                            No services found. Start by creating one above.
                        </div>

                        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="service in services" :key="service.id" class="p-6 bg-gray-50 dark:bg-gray-900 rounded-xl border dark:border-gray-700 flex flex-col h-full">
                                <div class="flex justify-between items-start mb-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200 uppercase">
                                        {{ service.area.name }}
                                    </span>
                                    <button @click="deleteService(service.id)" class="text-gray-400 hover:text-red-600 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="Wait, I am using text for now..."></path></svg>
                                        <span class="font-bold">✕</span>
                                    </button>
                                </div>
                                
                                <h4 class="text-xl font-bold mb-2">{{ service.title }}</h4>
                                <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 flex-grow">{{ service.description }}</p>
                                
                                <div class="mt-auto pt-4 border-t dark:border-gray-800 flex justify-between items-center">
                                    <div class="text-2xl font-black text-indigo-600 dark:text-indigo-400">
                                        ${{ Number(service.price).toLocaleString() }}
                                    </div>
                                    <div v-if="$page.props.auth.user.role === 'admin'" class="text-[10px] text-gray-500 uppercase font-bold text-right">
                                        By: {{ service.company.name }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
