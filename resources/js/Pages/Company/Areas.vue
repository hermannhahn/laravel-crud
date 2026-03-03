<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

defineProps<{
    areas: Array<{
        id: number;
        name: string;
        company?: { id: number; name: string };
    }>;
    companies: Array<{
        id: number;
        name: string;
    }>;
}>();

const form = useForm({
    name: '',
    company_id: '',
});

const submit = () => {
    form.post(route('areas.store'), {
        onSuccess: () => form.reset('name'),
    });
};

const deleteArea = (id: number) => {
    if (confirm('Are you sure? This might affect tasks and professional assignments in this area.')) {
        router.delete(route('areas.destroy', id));
    }
};
</script>

<template>
    <Head title="Task Areas" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Manage Task Areas</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Create Area -->
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Create New Area</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Define a professional area or specialty for tasks.</p>
                        </header>

                        <form @submit.prevent="submit" class="mt-6 space-y-6">
                            <!-- Admin only: Company Selection -->
                            <div v-if="$page.props.auth.user.role === 'admin'" class="max-w-xl">
                                <InputLabel for="company_id" value="Target Company (Admin only)" />
                                <select
                                    id="company_id"
                                    v-model="form.company_id"
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

                            <div class="flex items-end gap-4 max-w-xl">
                                <div class="flex-1">
                                    <InputLabel for="name" value="Area Name" />
                                    <TextInput
                                        id="name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.name"
                                        required
                                        placeholder="Enter area name..."
                                    />
                                    <InputError :message="form.errors.name" class="mt-2" />
                                </div>
                                <PrimaryButton :disabled="form.processing">Create</PrimaryButton>
                            </div>
                        </form>
                    </section>
                </div>

                <!-- Areas List -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium mb-4">Existing Areas</h3>
                        <div v-if="areas.length === 0" class="text-gray-500 py-4">No areas defined yet.</div>
                        <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            <div v-for="area in areas" :key="area.id" class="flex flex-col p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border dark:border-gray-700">
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">{{ area.name }}</span>
                                    <button @click="deleteArea(area.id)" class="text-red-600 hover:text-red-900 text-sm font-bold">✕</button>
                                </div>
                                <div v-if="area.company" class="mt-2 text-[10px] text-gray-400 uppercase font-bold">
                                    Company: {{ area.company.name }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
