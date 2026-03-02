<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        avatar_url: string | null;
    };
}>();

const profileForm = useForm({
    name: props.user.name,
    email: props.user.email,
    avatar: null as File | null,
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updateProfile = () => {
    profileForm.post(route('user-profile.update'), {
        forceFormData: true,
        preserveScroll: true,
    });
};

const updatePassword = () => {
    passwordForm.post(route('user-profile.password'), {
        preserveScroll: true,
        onSuccess: () => passwordForm.reset(),
    });
};
</script>

<template>
    <Head title="My Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">My Profile</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Profile Information -->
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <section class="max-w-xl">
                        <header>
                            <div class="flex justify-between items-center">
                                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Profile Information</h2>
                                <span class="bg-gray-100 dark:bg-gray-900 text-gray-600 dark:text-gray-400 px-3 py-1 rounded-full text-sm font-bold border border-gray-200 dark:border-gray-700">
                                    User ID: {{ user.id }}
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Update your profile's information, email address, and avatar.</p>
                        </header>

                        <form @submit.prevent="updateProfile" class="mt-6 space-y-6">
                            <div v-if="user.avatar_url" class="flex items-center gap-4">
                                <img :src="user.avatar_url" alt="Avatar" class="w-20 h-20 rounded-full object-cover shadow" />
                            </div>

                            <div>
                                <InputLabel for="avatar" value="Profile Picture" />
                                <input
                                    id="avatar"
                                    type="file"
                                    class="mt-1 block w-full text-sm text-gray-900 dark:text-gray-100"
                                    @input="profileForm.avatar = ($event.target as HTMLInputElement).files?.[0] || null"
                                />
                                <InputError class="mt-2" :message="profileForm.errors.avatar" />
                            </div>

                            <div>
                                <InputLabel for="name" value="Name" />
                                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="profileForm.name" required />
                                <InputError class="mt-2" :message="profileForm.errors.name" />
                            </div>

                            <div>
                                <InputLabel for="email" value="Email" />
                                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="profileForm.email" required />
                                <InputError class="mt-2" :message="profileForm.errors.email" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="profileForm.processing">Save</PrimaryButton>
                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p v-if="profileForm.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">Saved.</p>
                                </Transition>
                            </div>
                        </form>
                    </section>
                </div>

                <!-- Update Password -->
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                    <section class="max-w-xl">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Update Password</h2>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Ensure your account is using a long, random password to stay secure.</p>
                        </header>

                        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
                            <div>
                                <InputLabel for="current_password" value="Current Password" />
                                <TextInput id="current_password" type="password" class="mt-1 block w-full" v-model="passwordForm.current_password" required />
                                <InputError :message="passwordForm.errors.current_password" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="new_password" value="New Password" />
                                <TextInput id="new_password" type="password" class="mt-1 block w-full" v-model="passwordForm.password" required />
                                <InputError :message="passwordForm.errors.password" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="password_confirmation" value="Confirm Password" />
                                <TextInput id="password_confirmation" type="password" class="mt-1 block w-full" v-model="passwordForm.password_confirmation" required />
                                <InputError :message="passwordForm.errors.password_confirmation" class="mt-2" />
                            </div>

                            <div class="flex items-center gap-4">
                                <PrimaryButton :disabled="passwordForm.processing">Save</PrimaryButton>
                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                    <p v-if="passwordForm.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">Saved.</p>
                                </Transition>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
