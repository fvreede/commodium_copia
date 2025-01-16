<script setup>
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { watch } from 'vue';

const props = defineProps({
    show: Boolean,
    user: {
        type: Object,
        default: null
    },
    roles: {
        type: Array,
        required: true
    },
});

const emit = defineEmits(['close']);

// Initialize form with empty values
const form = useForm({
    name: '',
    email: '',
    role: '',
});

// Watch for changes in props.user and update form data
watch(() => props.user, (newUser) => {
    if (newUser) {
        if (hasRole(newUser, 'admin')) {
            emit('close');
            return;
        }
        form.name = newUser.name || '';
        form.email = newUser.email || '';
        form.role = newUser.roles && newUser.roles.length > 0 ? newUser.roles[0].name : '';
    } else {
        form.reset();
    }
}, { immediate: true });

const submit = () => {
    if (!props.user) return;
    
    form.patch(route('admin.users.update', props.user.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
};

const hasRole = (user, roleName) => {
    return user.roles.some(role => role.name === roleName);
};
</script>

<template>
    <Modal :show="show" @close="$emit('close')" max-width="md">
        <form @submit.prevent="submit" class="p-6">
            <h2 class="text-lg font-medium text-gray-900">Edit User</h2>
            
            <div v-if="form.errors.length" class="mb-4 p-4 bg-red-50 text-red-600 rounded">
                <p v-for="error in form.errors" :key="error">{{ error }}</p>
            </div>

            <div class="mt-6">
                <InputLabel for="name" value="Name" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    :disabled="!props.user"
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
                    :disabled="!props.user"
                />
            </div>

            <div class="mt-6">
                <InputLabel for="role" value="Role" />
                <select
                    id="role"
                    v-model="form.role"
                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    required
                    :disabled="!props.user || props.user.email === 'admin@cc.nl'"
                >
                    <option value="">Select a role</option>
                    <option 
                        v-for="role in roles" 
                        :key="role.name" 
                        :value="role.name"
                    >
                        {{ role.name }}
                    </option>
                </select>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button
                    type="button"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                    @click="$emit('close')"
                >
                    Cancel
                </button>
                <PrimaryButton 
                    :disabled="form.processing || !props.user"
                >
                    Update User
                </PrimaryButton>
            </div>
        </form>
    </Modal>
</template>