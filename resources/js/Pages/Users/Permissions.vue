<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps<{
    user: {
        id: number;
        name: string;
    };
    permissions: {
        tasks: {
            can_view: boolean;
            can_create: boolean;
            can_update: boolean;
            can_delete: boolean;
            monthly_limit: number | string | null;
        };
    };
}>();

const form = useForm({
    module: 'tasks',
    can_view: props.permissions.tasks.can_view,
    can_create: props.permissions.tasks.can_create,
    can_update: props.permissions.tasks.can_update,
    can_delete: props.permissions.tasks.can_delete,
    monthly_limit: (props.permissions.tasks.monthly_limit ?? '') as any,
});

const submit = () => {
    form.put(route('users.permissions.update', props.user.id));
};
</script>

<template>
    <Head :title="'Manage Permissions - ' + user.name" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Manage Permissions: {{ user.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit" class="max-w-xl">
                            <h3 class="text-lg font-medium mb-4">Tasks Module</h3>
                            
                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <Checkbox id="can_view" v-model:checked="form.can_view" />
                                    <InputLabel for="can_view" value="Can View Tasks" class="ml-2" />
                                </div>
                                <div class="flex items-center">
                                    <Checkbox id="can_create" v-model:checked="form.can_create" />
                                    <InputLabel for="can_create" value="Can Create Tasks" class="ml-2" />
                                </div>
                                <div class="flex items-center">
                                    <Checkbox id="can_update" v-model:checked="form.can_update" />
                                    <InputLabel for="can_update" value="Can Update Tasks" class="ml-2" />
                                </div>
                                <div class="flex items-center">
                                    <Checkbox id="can_delete" v-model:checked="form.can_delete" />
                                    <InputLabel for="can_delete" value="Can Delete Tasks" class="ml-2" />
                                </div>

                                <div class="mt-4">
                                    <InputLabel for="monthly_limit" value="Monthly Creation Limit (Empty for Unlimited)" />
                                    <TextInput
                                        id="monthly_limit"
                                        type="number"
                                        class="mt-1 block w-full"
                                        v-model="form.monthly_limit"
                                        min="0"
                                    />
                                    <InputError :message="form.errors.monthly_limit" class="mt-2" />
                                </div>
                            </div>

                            <div class="mt-6 flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">Save Permissions</PrimaryButton>
                                <Link :href="route('users.index')" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">Cancel</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
