<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

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
</script>

<template>
    <Modal :show="show" @close="$emit('close')">
        <form @submit.prevent="submit" class="p-6">
            <h2 class="text-lg font-medium text-gray-900">Create New User</h2>

            <div class="mt-6">
                <InputLabel for="name" value="Name" />
                <TextInput 
                    id="name" 
                    v-model="form.name" 
                    type="text" 
                    class="mt-1 block w-full" 
                    required 
                />
            </div>

            <div class="mt-6">
                <InputLabel for="email" value="Email" />
                <TextInput 
                    id="email" 
                    v-model="form.email" 
                    type="email" 
                    class="mt-1 block w-full" 
                    required 
                />
            </div>

            <div class="mt-6">
                <InputLabel for="password" value="Password" />
                <TextInput 
                    id="password" 
                    v-model="form.password" 
                    type="password" 
                    class="mt-1 block w-full" 
                    required 
                />
            </div>

            <div class="mt-6">
                <InputLabel for="password_confirmation" value="Confirm Password" />
                <TextInput 
                    id="password_confirmation" 
                    v-model="form.password_confirmation" 
                    type="password" 
                    class="mt-1 block w-full" 
                    required 
                />
            </div>

            <div class="mt-6">
                <InputLabel for="role" value="Role" />
                <select 
                    id="role" 
                    v-model="form.role" 
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    required 
                >
                    <option value="">Select Role</option>
                    <option v-for="role in roles" :key="role.name" :value="role.name">
                        {{ role.name }}
                    </option>
                </select>
            </div>

            <div class="mt-6 flex justify-end">
                <PrimaryButton :disabled="form.processing">
                    Create User
                </PrimaryButton>
            </div>
        </form>
    </Modal>
</template>