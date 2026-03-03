<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Checkbox from '@/Components/Checkbox.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps<{
    professionals: Array<{
        id: number;
        name: string;
        email: string;
        assigned_tasks_count: number;
        company_areas: Array<{ id: number; name: string }>;
        pivot: {
            company_id?: number;
            can_view_tasks: boolean;
            can_respond_tasks: boolean;
            can_edit_tasks: boolean;
        };
    }>;
    areas: Array<{
        id: number;
        name: string;
    }>;
    companies: Array<{
        id: number;
        name: string;
    }>;
}>();

const addForm = useForm({
    professional_id: '',
    task_area_ids: [] as number[],
    company_id: '',
});

const onCompanyFilterChange = (compId: any) => {
    router.reload({
        data: { company_id: compId },
        only: ['professionals', 'areas'],
    });
};

const addProfessional = () => {
    addForm.post(route('professionals.add'), {
        onSuccess: () => addForm.reset('professional_id'),
    });
};

const updatePermissions = (pro: any) => {
    // Collect area IDs from the reactive object
    const areaIds = pro.company_areas.map((a: any) => a.id);
    
    // For admins, we need to pass the company_id being managed
    const companyId = usePage().props.auth.user.role === 'admin' 
        ? (pro.pivot.company_id || addForm.company_id) 
        : null;

    router.patch(route('professionals.update-permissions', pro.id), {
        company_id: companyId,
        task_area_ids: areaIds,
        can_view_tasks: pro.pivot.can_view_tasks,
        can_respond_tasks: pro.pivot.can_respond_tasks,
        can_edit_tasks: pro.pivot.can_edit_tasks,
    }, { preserveScroll: true });
};

const toggleArea = (pro: any, areaId: number) => {
    const index = pro.company_areas.findIndex((a: any) => a.id === areaId);
    if (index > -1) {
        pro.company_areas.splice(index, 1);
    } else {
        const area = props.areas.find(a => a.id === areaId);
        if (area) pro.company_areas.push(area);
    }
    updatePermissions(pro);
};

const removeProfessional = (pro: any) => {
    if (confirm('Deauthorize this professional?')) {
        const companyId = usePage().props.auth.user.role === 'admin' 
            ? (pro.pivot.company_id || addForm.company_id) 
            : null;

        router.delete(route('professionals.remove', pro.id), {
            data: { company_id: companyId }
        });
    }
};
</script>

<template>
    <Head title="Authorized Professionals" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Manage Authorized Professionals</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Admin only: Select Company to Manage -->
                <div v-if="$page.props.auth.user.role === 'admin'" class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg border-2 border-indigo-500">
                    <div class="max-w-xl">
                        <InputLabel for="filter_company_id" value="Select Company to Manage" />
                        <select
                            id="filter_company_id"
                            v-model="addForm.company_id"
                            @change="onCompanyFilterChange(addForm.company_id)"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                        >
                            <option value="" disabled>Choose a company...</option>
                            <option v-for="company in companies" :key="company.id" :value="company.id">
                                {{ company.name }}
                            </option>
                        </select>
                        <p class="mt-2 text-xs text-gray-500">Select a company first to see its authorized professionals and available areas.</p>
                    </div>
                </div>

                <!-- Add Professional -->
                <div v-if="$page.props.auth.user.user_type === 'company' || addForm.company_id" class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Authorize New Professional</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Add a professional by ID and select their authorized areas.</p>
                        </header>

                        <form @submit.prevent="addProfessional" class="mt-6 space-y-6">
                            <div class="max-w-xl">
                                <InputLabel for="professional_id" value="Professional's Unique ID" />
                                <TextInput
                                    id="professional_id"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="addForm.professional_id"
                                    required
                                    placeholder="Enter professional's ID..."
                                />
                                <InputError :message="addForm.errors.professional_id" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel value="Authorized Areas" />
                                <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <label v-for="area in areas" :key="area.id" class="flex items-center">
                                        <input 
                                            type="checkbox" 
                                            :value="area.id" 
                                            v-model="addForm.task_area_ids"
                                            class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
                                        />
                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ area.name }}</span>
                                    </label>
                                </div>
                                <InputError :message="addForm.errors.task_area_ids" class="mt-2" />
                            </div>

                            <PrimaryButton :disabled="addForm.processing">Authorize</PrimaryButton>
                        </form>
                    </section>
                </div>

                <!-- Professionals List -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="text-lg font-medium mb-4">Authorized Professionals</h3>
                        <div v-if="professionals.length === 0" class="text-gray-500 py-4">No professionals authorized yet.</div>
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                                        <th class="px-4 py-3">Professional</th>
                                        <th class="px-4 py-3">Authorized Areas</th>
                                        <th class="px-4 py-3 text-center">Open Tasks</th>
                                        <th class="px-4 py-3 text-center">View</th>
                                        <th class="px-4 py-3 text-center">Respond</th>
                                        <th class="px-4 py-3 text-center">Edit</th>
                                        <th class="px-4 py-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="pro in professionals" :key="pro.id">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium">{{ pro.name }}</div>
                                            <div class="text-[10px] text-gray-400 uppercase font-bold">ID: {{ pro.id }}</div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex flex-wrap gap-2">
                                                <button 
                                                    v-for="area in areas" 
                                                    :key="area.id"
                                                    @click="toggleArea(pro, area.id)"
                                                    :class="[
                                                        'px-2 py-0.5 rounded text-[10px] font-bold uppercase transition',
                                                        pro.company_areas.some(a => a.id === area.id)
                                                            ? 'bg-indigo-100 text-indigo-700 border border-indigo-200 dark:bg-indigo-900 dark:text-indigo-300'
                                                            : 'bg-gray-100 text-gray-400 border border-gray-200 dark:bg-gray-900 dark:text-gray-600 opacity-50'
                                                    ]"
                                                >
                                                    {{ area.name }}
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 text-center text-sm font-bold">{{ pro.assigned_tasks_count }}</td>
                                        <td class="px-4 py-4 text-center">
                                            <Checkbox v-model:checked="pro.pivot.can_view_tasks" @change="updatePermissions(pro)" />
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <Checkbox v-model:checked="pro.pivot.can_respond_tasks" @change="updatePermissions(pro)" />
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <Checkbox v-model:checked="pro.pivot.can_edit_tasks" @change="updatePermissions(pro)" />
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <button @click="removeProfessional(pro)" class="text-red-600 hover:text-red-900 text-sm">Deauthorize</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
