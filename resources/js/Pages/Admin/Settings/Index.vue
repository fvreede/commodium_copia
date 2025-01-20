<script setup>
import { ref, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    passwordForm.post(route('admin.settings.password'), {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();

            // Set flash message when password update is successful
            if ($page.props.flash.success) {
                showFlashMessage.value = true;
                flashMessage.value = $page.props.flash.success;

                // Hide message after 5 seconds
                setTimeout(() => {
                    showFlashMessage.value = false;
                }, 5000);
            }
        },
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Admin Settings" />
        <div class="py-12">
            <!-- Timed Flash Message -->
            <Transition
                enter-active-class="transition-duration-300 ease-out"
                enter-from-class="transform -translate-y-2 opacity-0"
                enter-to-class="transform translate-y-0 opacity-100"
                leave-active-class="transition duration-300 ease-in"
                leave-from-class="transform translate-y-0 opacity-100"
                leave-to-class="transform -translate-y-2 opacity-0"
            >
                <div 
                    v-if="showFlashMessage"
                    class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-4">
                    <div 
                        class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded relative flex justify-between items-center" 
                        role="alert"
                    >
                        <span>{{ flashMessage }}</span>
                        <button 
                            @click="showFlashMessage = false" 
                            class="text-green-700 hover:text-green-900"
                        >
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path 
                                    fill-rule="evenodd" 
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" 
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </button>
                    </div>
                </div>
            </Transition>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Password Update Section -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <section class="max-w-xl">
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">Update Password</h2>
                            <p class="mt-1 text-sm text-gray-600">
                                Ensure your account is using a long, random password to stay secure.
                            </p>
                        </header>

                        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
                            <div>
                                <InputLabel for="current_password" value="Current Password" />
                                <TextInput
                                    id="current_password"
                                    v-model="passwordForm.current_password"
                                    type="password"
                                    class="mt-1 block w-full"
                                    autocomplete="current-password"
                                />
                                <InputError :message="passwordForm.errors.current_password" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="password" value="New Password" />
                                <TextInput
                                    id="password"
                                    v-model="passwordForm.password"
                                    type="password"
                                    class="mt-1 block w-full"
                                    autocomplete="new-password"
                                />
                                <InputError :message="passwordForm.errors.password" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="password_confirmation" value="Confirm Password" />
                                <TextInput
                                    id="password_confirmation"
                                    v-model="passwordForm.password_confirmation"
                                    type="password"
                                    class="mt-1 block w-full"
                                    autocomplete="new-password"
                                />
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
                                    <p v-if="passwordForm.recentlySuccessful" class="text-sm text-green-600">Saved.</p>
                                </Transition>
                            </div>
                        </form>
                    </section>
                </div>

                <!-- Security Notice -->
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        <section class="space-y-6">
                            <header>
                                <div class="flex items-center space-x-2">
                                    <ExclamationTriangleIcon class="h-5 w-5 text-yellow-500" />
                                    <h2 class="text-lg font-medium text-gray-900">Security Notice</h2>
                                </div>
                            </header>

                            <div class="text-sm text-gray-600">
                                <p>As an administrator, your account has elevated privileges. Please ensure you:</p>
                                <ul class="list-disc list-inside mt-2 space-y-1">
                                    <li>Use a strong, unique password</li>
                                    <li>Enable two-factor authentication if available</li>
                                    <li>Never share your login credentials</li>
                                    <li>Log out when accessing from shared devices</li>
                                </ul>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>