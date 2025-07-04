<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue';
import CreateUser from './Create.vue';
import EditUser from './Edit.vue';
import { UserMinusIcon, UserPlusIcon, PencilIcon, TrashIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
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
const searchQuery = ref('');

const filteredUsers = computed(() => {
    if (!searchQuery.value) return props.users.data;
    
    const query = searchQuery.value.toLowerCase();
    return props.users.data.filter(user => 
        user.name.toLowerCase().includes(query) ||
        user.email.toLowerCase().includes(query) ||
        (user.roles[0]?.name || '').toLowerCase().includes(query)
    );
});

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
                        <!-- Header with Search and Add User Button -->
                        <div class="flex flex-col space-y-4 mb-6">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                                <h2 class="text-xl sm:text-2xl font-semibold text-gray-800">Users</h2>
                                <PrimaryButton 
                                    @click="showCreateModal = true" 
                                    class="w-full sm:w-auto justify-center py-3 sm:py-2 text-sm"
                                >
                                    <UserPlusIcon class="h-4 w-4 mr-2" />
                                    Add New User
                                </PrimaryButton>
                            </div>
                            
                            <!-- Search Bar -->
                            <div class="relative w-full">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Search users..."
                                    class="w-full pr-10 pl-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:border-transparent text-base"
                                />
                                <MagnifyingGlassIcon class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                            </div>
                        </div>

                        <!-- Mobile Card View (visible on small screens) -->
                        <div class="block lg:hidden space-y-4">
                            <div 
                                v-for="user in filteredUsers" 
                                :key="user.id"
                                class="bg-gray-50 rounded-lg p-4 border border-gray-200"
                            >
                                <div class="flex flex-col space-y-3">
                                    <!-- User Info -->
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900 text-lg">{{ user.name }}</h3>
                                        <p class="text-gray-600 text-sm break-all">{{ user.email }}</p>
                                        <div class="flex items-center justify-between mt-2">
                                            <span class="text-sm text-gray-500">{{ user.roles[0]?.name || 'No role' }}</span>
                                            <span :class="{
                                                'px-3 py-1 text-xs font-semibold rounded-full': true,
                                                'bg-green-100 text-green-800': user.status === 'active',
                                                'bg-red-100 text-red-800': user.status === 'blocked',
                                            }">
                                                {{ user.status }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Actions -->
                                    <div v-if="!hasRole(user, 'admin')" class="flex space-x-2 pt-2 border-t border-gray-200">
                                        <button
                                            v-if="can.edit"
                                            @click="openEditModal(user)"
                                            class="flex-1 flex items-center justify-center px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors text-sm font-medium"
                                        >
                                            <PencilIcon class="h-4 w-4 mr-1" />
                                            Edit
                                        </button>
                                        <button
                                            v-if="can.block"
                                            @click="toggleUserStatus(user.id)"
                                            :class="{
                                                'flex-1 flex items-center justify-center px-4 py-2 rounded-lg transition-colors text-sm font-medium': true,
                                                'bg-red-50 text-red-700 hover:bg-red-100': user.status === 'active',
                                                'bg-green-50 text-green-700 hover:bg-green-100': user.status === 'blocked'
                                            }"
                                        >
                                            <UserMinusIcon v-if="user.status === 'active'" class="h-4 w-4 mr-1" />
                                            <UserPlusIcon v-else class="h-4 w-4 mr-1" />
                                            {{ user.status === 'active' ? 'Block' : 'Unblock' }}
                                        </button>
                                        <button
                                            v-if="can.delete"
                                            @click="openDeleteModal(user)"
                                            class="flex items-center justify-center px-3 py-2 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors"
                                        >
                                            <TrashIcon class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Empty State for Mobile -->
                            <div v-if="filteredUsers.length === 0" class="text-center py-8">
                                <p class="text-gray-500">No users found</p>
                            </div>
                        </div>

                        <!-- Desktop Table View (hidden on small screens) -->
                        <div class="hidden lg:block">
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
                                        <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ user.name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ user.email }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ user.roles[0]?.name || 'No role' }}</td>
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
                                                            class="text-blue-600 hover:text-blue-900 transition-colors"
                                                            title="Edit user"
                                                        >
                                                            <PencilIcon class="h-5 w-5" />
                                                        </button>
                                                        <button
                                                            v-if="can.block"
                                                            @click="toggleUserStatus(user.id)"
                                                            :class="{
                                                                'transition-colors': true,
                                                                'hover:text-red-900': user.status === 'active',
                                                                'text-red-600': user.status === 'active',
                                                                'hover:text-green-900': user.status === 'blocked',
                                                                'text-green-600': user.status === 'blocked'
                                                            }"
                                                            :title="user.status === 'active' ? 'Block user' : 'Unblock user'"
                                                        >
                                                            <UserMinusIcon v-if="user.status === 'active'" class="h-5 w-5" />
                                                            <UserPlusIcon v-else class="h-5 w-5" />
                                                        </button>
                                                        <button
                                                            v-if="can.delete"
                                                            @click="openDeleteModal(user)"
                                                            class="text-red-600 hover:text-red-900 transition-colors"
                                                            title="Delete user"
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
                            
                            <!-- Empty State for Desktop -->
                            <div v-if="filteredUsers.length === 0" class="text-center py-12">
                                <p class="text-gray-500 text-lg">No users found</p>
                            </div>
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

        <!-- Enhanced Delete Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false" max-width="sm">
            <div class="p-4 sm:p-6">
                <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-red-100 rounded-full">
                    <TrashIcon class="w-6 h-6 text-red-600" />
                </div>
                <h2 class="text-lg font-medium text-gray-900 text-center mb-2">Delete User</h2>
                <p class="text-sm text-gray-600 text-center mb-6">
                    Are you sure you want to delete <strong>{{ selectedUser?.name }}</strong>? This action cannot be undone.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                    <button
                        type="button"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                        @click="showDeleteModal = false"
                    >
                        Cancel
                    </button>
                    <button 
                        type="button"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        @click="deleteUser"
                    >
                        Delete User
                    </button>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>