<script setup>
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { UserPlusIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    show: Boolean,
    roles: Array
});

const emit = defineEmits(['close']);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: ''
});

const submit = () => {
    form.post(route('admin.users.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
};

const closeModal = () => {
    form.reset();
    form.clearErrors();
    emit('close');
};
</script>

<template>
    <Modal :show="show" @close="closeModal" max-width="md">
        <div class="relative">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 sm:p-6 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-lg">
                        <UserPlusIcon class="w-5 h-5 text-blue-600" />
                    </div>
                    <h2 class="text-lg sm:text-xl font-semibold text-gray-900">Create New User</h2>
                </div>
                <button 
                    @click="closeModal"
                    class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                >
                    <XMarkIcon class="w-5 h-5" />
                </button>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="p-4 sm:p-6">
                <div class="space-y-4 sm:space-y-5">
                    <!-- Name Field -->
                    <div>
                        <InputLabel for="name" value="Full Name" class="text-sm font-medium text-gray-700" />
                        <TextInput 
                            id="name" 
                            v-model="form.name" 
                            type="text" 
                            class="mt-2 block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            placeholder="Enter full name"
                            required 
                            :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.name }"
                        />
                        <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <!-- Email Field -->
                    <div>
                        <InputLabel for="email" value="Email Address" class="text-sm font-medium text-gray-700" />
                        <TextInput 
                            id="email" 
                            v-model="form.email" 
                            type="email" 
                            class="mt-2 block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            placeholder="Enter email address"
                            required 
                            :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.email }"
                        />
                        <div v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <InputLabel for="password" value="Password" class="text-sm font-medium text-gray-700" />
                        <TextInput 
                            id="password" 
                            v-model="form.password" 
                            type="password" 
                            class="mt-2 block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            placeholder="Enter password"
                            required 
                            :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.password }"
                        />
                        <div v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                            {{ form.errors.password }}
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Minimum 8 characters required</p>
                    </div>

                    <!-- Confirm Password Field -->
                    <div>
                        <InputLabel for="password_confirmation" value="Confirm Password" class="text-sm font-medium text-gray-700" />
                        <TextInput 
                            id="password_confirmation" 
                            v-model="form.password_confirmation" 
                            type="password" 
                            class="mt-2 block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            placeholder="Confirm password"
                            required 
                            :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.password_confirmation }"
                        />
                        <div v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-600">
                            {{ form.errors.password_confirmation }}
                        </div>
                    </div>

                    <!-- Role Field -->
                    <div>
                        <InputLabel for="role" value="User Role" class="text-sm font-medium text-gray-700" />
                        <select 
                            id="role" 
                            v-model="form.role" 
                            class="mt-2 block w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white"
                            required 
                            :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.role }"
                        >
                            <option value="">Select a role</option>
                            <option v-for="role in roles" :key="role.name" :value="role.name">
                                {{ role.name }}
                            </option>
                        </select>
                        <div v-if="form.errors.role" class="mt-1 text-sm text-red-600">
                            {{ form.errors.role }}
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="mt-8 pt-4 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <PrimaryButton 
                            type="submit"
                            :disabled="form.processing"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 text-sm font-medium transition-colors"
                        >
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <UserPlusIcon v-else class="w-4 h-4 mr-2" />
                            {{ form.processing ? 'Creating...' : 'Create User' }}
                        </PrimaryButton>
                        <SecondaryButton
                            type="button"
                            @click="closeModal"
                            class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                            :disabled="form.processing"
                        >
                            Cancel
                        </SecondaryButton>
                    </div>
                </div>
            </form>
        </div>
    </Modal>
</template>