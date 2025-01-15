<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue';
import CreateUser from './Create.vue';
import EditUser from './Edit.vue';
import { UserIcon, UserMinusIcon, UserPlusIcon, PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    users: Object,
    roles: Array,
    can: Object
});

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedUser = ref(null);

const openEditModal = (user) => {
    selectedUser.value = user;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    selectedUser.value = null;
};

const openDeleteModal = (user) => {
    selectedUser.value = user;
    showDeleteModal.value = true;
};

const deleteUser = () => {
    router.delete(route('admin.users.destroy', selectedUser.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            selectedUser.value = null;
        },    
    });
};

const toggleUserStatus = (userId) => {
    router.patch(route('admin.users.toggle-status', userId));
};

const hasRole = (user, roleName) => {
    return user.roles.some(role => role.name === roleName);
};
</script>

<template>
    <AdminLayout>
        <Head title="User Management" />

        <div class="py-4 sm:py-6 lg:py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-4 sm:p-6">
                        <!-- Header with Add User Button -->
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
                            <h2 class="text-xl sm:text-2xl font-semibold text-gray-800">Users</h2>
                            <PrimaryButton @click="showCreateModal = true" class="w-full sm:w-auto justify-center">
                                Add New User
                            </PrimaryButton>
                        </div>

                        <!-- Table View -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="user in users.data" :key="user.id">
                                        <td class="px-6 py-4 whitespace-nowrap">{{ user.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ user.email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ user.roles[0]?.name || 'No role' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="{
                                                'px-2 py-1 text-xs font-semibold rounded-full': true,
                                                'bg-green-100 text-green-800': user.status === 'active',
                                                'bg-red-100 text-red-800': user.status === 'blocked',
                                            }">
                                                {{ user.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex space-x-3">
                                                <template v-if="!hasRole(user, 'admin')">
                                                    <button
                                                        v-if="can.edit"
                                                        @click="openEditModal(user)"
                                                        class="text-blue-600 hover:text-blue-900"
                                                    >
                                                        <PencilIcon class="h-5 w-5" />
                                                    </button>
                                                    <button
                                                        v-if="can.block"
                                                        @click="toggleUserStatus(user.id)"
                                                        :class="{
                                                            'hover:text-red-900': user.status === 'active',
                                                            'text-red-600': user.status === 'active',
                                                            'hover:text-green-900': user.status === 'blocked',
                                                            'text-green-600': user.status === 'blocked'
                                                        }"
                                                    >
                                                        <UserMinusIcon v-if="user.status === 'active'" class="h-5 w-5" />
                                                        <UserPlusIcon v-else class="h-5 w-5" />
                                                    </button>
                                                    <button
                                                        v-if="can.delete"
                                                        @click="openDeleteModal(user)"
                                                        class="text-red-600 hover:text-red-900"
                                                    >
                                                        <TrashIcon class="h-5 w-5" />
                                                    </button>
                                                </template>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <CreateUser
            :show="showCreateModal"
            :roles="roles"
            @close="showCreateModal = false"
        />

        <EditUser
            :show="showEditModal"
            :user="selectedUser"
            :roles="roles"
            @close="closeEditModal"
        />

        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-4 sm:p-6">
                <h2 class="text-lg font-medium text-gray-900">Delete User</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Are you sure you want to delete this user? This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end space-x-3">
                    <button
                        type="button"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                        @click="showDeleteModal = false"
                    >
                        Cancel
                    </button>
                    <button 
                        type="button"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        @click="deleteUser"
                    >
                        Delete User
                    </button>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>