<script setup>
import { ref, onMounted } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import FlashModal from '@/Components/FlashModal.vue';
import { ExclamationTriangleIcon, EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline';

const showFlashModal = ref(false);
const flashMessage = ref('');

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
            flashMessage.value = 'Password updated successfully.';
            showFlashModal.value = true;
        },
    });
};

const closeFlashModal = () => {
    showFlashModal.value = false;
};

// Toggle password visibility methods
const currentPasswordVisible = ref(false);
const newPasswordVisible = ref(false);
const confirmPasswordVisible = ref(false);

const toggleCurrentPasswordVisibility = () => {
    currentPasswordVisible.value = !currentPasswordVisible.value;
}

const toggleNewPasswordVisibility = () => {
    newPasswordVisible.value = !newPasswordVisible.value;
}

const toggleConfirmPasswordVisibility = () => {
    confirmPasswordVisible.value = !confirmPasswordVisible.value;
}
</script>

<template>
    <AdminLayout>
        <Head title="Admin Settings" />
        <div class="py-12">
            
            <!-- Flash Message -->
            <FlashModal
                :show="showFlashModal"
                :message="flashMessage"
                :type="'success'"
                :duration="4000"
                @close="closeFlashModal"
            />

            <!-- Password Update Form -->
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
                            <div class="relative">
                                <InputLabel for="current_password" value="Current Password" />
                                <TextInput
                                    id="current_password"
                                    v-model="passwordForm.current_password"
                                    :type="currentPasswordVisible? 'text' : 'password'"
                                    class="mt-1 block w-full"
                                    autocomplete="current-password"
                                />
                                <button
                                    type="button"
                                    @click="toggleCurrentPasswordVisibility"
                                    class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 mt-6"
                                >
                                    <EyeIcon v-if="!currentPasswordVisible" class="h-5 w-5"/>
                                    <EyeSlashIcon v-else class="h-5 w-5"/>
                                </button>
                                <InputError :message="passwordForm.errors.current_password" class="mt-2" />
                            </div>

                            <div class="relative">
                                <InputLabel for="password" value="New Password" />
                                <TextInput
                                    id="password"
                                    v-model="passwordForm.password"
                                    :type="newPasswordVisible? 'text' : 'password'"
                                    class="mt-1 block w-full"
                                    autocomplete="new-password"
                                />
                                <button
                                    type="button"
                                    @click="toggleNewPasswordVisibility"
                                    class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 mt-6"
                                >
                                    <EyeIcon v-if="!newPasswordVisible" class="h-5 w-5"/>
                                    <EyeSlashIcon v-else class="h-5 w-5"/>
                                </button>
                                <InputError :message="passwordForm.errors.password" class="mt-2" />
                            </div>

                            <div class="relative">
                                <InputLabel for="password_confirmation" value="Confirm Password" />
                                <TextInput
                                    id="password_confirmation"
                                    v-model="passwordForm.password_confirmation"
                                    :type="confirmPasswordVisible? 'text' : 'password'"
                                    class="mt-1 block w-full"
                                    autocomplete="new-password"
                                />
                                <button
                                    type="button"
                                    @click="toggleConfirmPasswordVisibility"
                                    class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 mt-6"
                                >
                                    <EyeIcon v-if="!confirmPasswordVisible" class="h-5 w-5"/>
                                    <EyeSlashIcon v-else class="h-5 w-5"/>
                                </button>
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