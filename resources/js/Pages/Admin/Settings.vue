<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { ref } from 'vue';

const props = defineProps<{
    settings: {
        app_name: string;
        app_logo: string | null;
        help_content: string;
    }
}>();

const form = useForm({
    app_name: props.settings.app_name,
    app_logo: null as File | null,
    help_content: props.settings.help_content,
});

const logoPreview = ref(props.settings.app_logo);

const handleLogoChange = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        form.app_logo = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            logoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    // We use POST with _method=POST because we are uploading a file
    // and SettingController@update handles POST.
    form.post(route('settings.update'), {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="System Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">System Settings</h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- App Name -->
                            <div>
                                <InputLabel for="app_name" value="Application Name" />
                                <TextInput
                                    id="app_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.app_name"
                                    required
                                />
                                <InputError :message="form.errors.app_name" class="mt-2" />
                            </div>

                            <!-- Logo -->
                            <div>
                                <InputLabel for="app_logo" value="Application Logo" />
                                <div class="mt-2 flex items-center space-x-4">
                                    <div v-if="logoPreview" class="h-16 w-16 bg-gray-100 dark:bg-gray-700 rounded p-2 flex items-center justify-center">
                                        <img :src="logoPreview" alt="Logo Preview" class="max-h-full max-w-full object-contain" />
                                    </div>
                                    <input
                                        id="app_logo"
                                        type="file"
                                        @change="handleLogoChange"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-300"
                                        accept="image/*"
                                    />
                                </div>
                                <InputError :message="form.errors.app_logo" class="mt-2" />
                            </div>

                            <!-- Help Content -->
                            <div>
                                <InputLabel for="help_content" value="Help & Support Information" />
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">This information will be visible to all users on the Help page.</p>
                                <textarea
                                    id="help_content"
                                    v-model="form.help_content"
                                    rows="10"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                    placeholder="Enter contact info, support links, or instructions..."
                                ></textarea>
                                <InputError :message="form.errors.help_content" class="mt-2" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="form.processing">Save Settings</PrimaryButton>
                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">Saved.</p>
                                </Transition>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
