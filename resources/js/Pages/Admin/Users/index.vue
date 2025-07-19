/**
 * Bestandsnaam: Index.vue (Pages/Admin/Users)
 * Auteur: Fabio Vreede
 * Versie: v1.0.5
 * Datum: 2025-07-04
 * Tijd: 16:58:38
 * Doel: Dit component toont een overzicht van alle gebruikers in de admin interface met zoekfunctionaliteit,
 *       responsive design, en complete gebruikersbeheer functionaliteiten. Bevat modals voor create, edit
 *       en delete operaties, plus status toggle en admin bescherming.
 */

<script setup>
// Vue compositie API imports
import { ref, computed } from 'vue';

// Inertia.js imports voor navigatie en routing
import { Head, router } from '@inertiajs/vue3';

// Layout en component imports
import AdminLayout from '@/Layouts/Admin/AdminLayout.vue';
import CreateUser from './Create.vue';
import EditUser from './Edit.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

// Heroicons imports voor UI iconen
import { 
    UserMinusIcon, 
    UserPlusIcon, 
    PencilIcon, 
    TrashIcon, 
    MagnifyingGlassIcon 
} from '@heroicons/vue/24/outline';

// ========== PROPS DEFINITIE ==========

// Props van server - gebruikers data en configuratie
const props = defineProps({
    users: Object,                              // Gebruikers object met data array en meta informatie
    roles: Array,                              // Array van beschikbare rollen
    can: Object                                // Permissions object voor verschillende acties
});

// ========== MODAL STATE MANAGEMENT ==========

// Modal visibility state
const showCreateModal = ref(false);            // Create gebruiker modal
const showEditModal = ref(false);              // Edit gebruiker modal  
const showDeleteModal = ref(false);            // Delete bevestiging modal

// Geselecteerde gebruiker voor operaties
const selectedUser = ref(null);                // Huidige geselecteerde gebruiker voor edit/delete

// ========== ZOEK FUNCTIONALITEIT ==========

// Zoekterm reactive state
const searchQuery = ref('');                    // Huidige zoekterm van gebruiker

/**
 * Gefilterde gebruikers gebaseerd op zoekterm
 * Filtert op naam, email en rol (case-insensitive)
 * @returns {Array} Gefilterde array van gebruikers
 */
const filteredUsers = computed(() => {
    if (!searchQuery.value) return props.users.data;
    
    const query = searchQuery.value.toLowerCase();
    return props.users.data.filter(user => 
        user.name.toLowerCase().includes(query) ||
        user.email.toLowerCase().includes(query) ||
        (user.roles[0]?.name || '').toLowerCase().includes(query)
    );
});

// ========== MODAL EVENT HANDLERS ==========

/**
 * Open edit modal voor specifieke gebruiker
 * @param {Object} user - Gebruiker object om te bewerken
 */
const openEditModal = (user) => {
    selectedUser.value = user;
    showEditModal.value = true;
};

/**
 * Sluit edit modal en reset geselecteerde gebruiker
 */
const closeEditModal = () => {
    showEditModal.value = false;
    selectedUser.value = null;
};

/**
 * Open delete bevestiging modal voor specifieke gebruiker
 * @param {Object} user - Gebruiker object om te verwijderen
 */
const openDeleteModal = (user) => {
    selectedUser.value = user;
    showDeleteModal.value = true;
};

// ========== GEBRUIKER OPERATIES ==========

/**
 * Verwijder geselecteerde gebruiker na bevestiging
 * Stuurt DELETE request naar server en sluit modal bij succes
 */
const deleteUser = () => {
    router.delete(route('admin.users.destroy', selectedUser.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            selectedUser.value = null;
        },    
    });
};

/**
 * Toggle gebruiker status tussen active en blocked
 * @param {number} userId - ID van gebruiker om status te wijzigen
 */
const toggleUserStatus = (userId) => {
    router.patch(route('admin.users.toggle-status', userId));
};

// ========== UTILITY FUNCTIES ==========

/**
 * Controleert of gebruiker een specifieke rol heeft
 * @param {Object} user - Gebruiker object met roles array
 * @param {string} roleName - Naam van rol om te controleren  
 * @returns {boolean} True als gebruiker de rol heeft
 */
const hasRole = (user, roleName) => {
    return user.roles.some(role => role.name === roleName);
};
</script>

<template>
    <AdminLayout>
        <!-- Page Title voor Browser Tab -->
        <Head title="User Management" />

        <div class="py-4 sm:py-6 lg:py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                    <div class="p-4 sm:p-6">
                        
                        <!-- Header Sectie met Zoeken en Add User Knop -->
                        <div class="flex flex-col space-y-4 mb-6">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                                <!-- Page Titel -->
                                <h2 class="text-xl sm:text-2xl font-semibold text-gray-800">Users</h2>
                                <!-- Add New User Knop -->
                                <PrimaryButton 
                                    @click="showCreateModal = true" 
                                    class="w-full sm:w-auto justify-center py-3 sm:py-2 text-sm"
                                >
                                    <UserPlusIcon class="h-4 w-4 mr-2" />
                                    Add New User
                                </PrimaryButton>
                            </div>
                            
                            <!-- Zoekbalk Sectie -->
                            <div class="relative w-full">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Search users..."
                                    class="w-full pr-10 pl-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-slate-400 focus:border-transparent text-base"
                                />
                                <!-- Zoek Icoon -->
                                <MagnifyingGlassIcon class="absolute right-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                            </div>
                        </div>

                        <!-- Mobiele Card Weergave (zichtbaar op kleine schermen) -->
                        <div class="block lg:hidden space-y-4">
                            <!-- Gebruiker Card -->
                            <div 
                                v-for="user in filteredUsers" 
                                :key="user.id"
                                class="bg-gray-50 rounded-lg p-4 border border-gray-200"
                            >
                                <div class="flex flex-col space-y-3">
                                    <!-- Gebruiker Informatie -->
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-gray-900 text-lg">{{ user.name }}</h3>
                                        <p class="text-gray-600 text-sm break-all">{{ user.email }}</p>
                                        <div class="flex items-center justify-between mt-2">
                                            <!-- Rol Weergave -->
                                            <span class="text-sm text-gray-500">{{ user.roles[0]?.name || 'No role' }}</span>
                                            <!-- Status Badge -->
                                            <span :class="{
                                                'px-3 py-1 text-xs font-semibold rounded-full': true,
                                                'bg-green-100 text-green-800': user.status === 'active',
                                                'bg-red-100 text-red-800': user.status === 'blocked',
                                            }">
                                                {{ user.status }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Actie Knoppen (alleen voor non-admin gebruikers) -->
                                    <div v-if="!hasRole(user, 'admin')" class="flex space-x-2 pt-2 border-t border-gray-200">
                                        <!-- Edit Knop -->
                                        <button
                                            v-if="can.edit"
                                            @click="openEditModal(user)"
                                            class="flex-1 flex items-center justify-center px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors text-sm font-medium"
                                        >
                                            <PencilIcon class="h-4 w-4 mr-1" />
                                            Edit
                                        </button>
                                        <!-- Block/Unblock Knop -->
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
                                        <!-- Delete Knop -->
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
                            
                            <!-- Lege Staat voor Mobiel -->
                            <div v-if="filteredUsers.length === 0" class="text-center py-8">
                                <p class="text-gray-500">No users found</p>
                            </div>
                        </div>

                        <!-- Desktop Tabel Weergave (verborgen op kleine schermen) -->
                        <div class="hidden lg:block">
                            <div class="overflow-x-auto">
                                <!-- Gebruikers Tabel -->
                                <table class="min-w-full divide-y divide-gray-200">
                                    <!-- Tabel Header -->
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <!-- Tabel Body -->
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <!-- Gebruiker Rij -->
                                        <tr v-for="user in filteredUsers" :key="user.id" class="hover:bg-gray-50">
                                            <!-- Naam Kolom -->
                                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ user.name }}</td>
                                            <!-- Email Kolom -->
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ user.email }}</td>
                                            <!-- Rol Kolom -->
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ user.roles[0]?.name || 'No role' }}</td>
                                            <!-- Status Kolom -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span :class="{
                                                    'px-2 py-1 text-xs font-semibold rounded-full': true,
                                                    'bg-green-100 text-green-800': user.status === 'active',
                                                    'bg-red-100 text-red-800': user.status === 'blocked',
                                                }">
                                                    {{ user.status }}
                                                </span>
                                            </td>
                                            <!-- Acties Kolom -->
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex space-x-3">
                                                    <!-- Admin Bescherming - Geen acties voor admin gebruikers -->
                                                    <template v-if="!hasRole(user, 'admin')">
                                                        <!-- Edit Link -->
                                                        <button
                                                            v-if="can.edit"
                                                            @click="openEditModal(user)"
                                                            class="text-blue-600 hover:text-blue-900 transition-colors"
                                                            title="Edit user"
                                                        >
                                                            <PencilIcon class="h-5 w-5" />
                                                        </button>
                                                        <!-- Block/Unblock Knop -->
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
                                                        <!-- Delete Knop -->
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
                            
                            <!-- Lege Staat voor Desktop -->
                            <div v-if="filteredUsers.length === 0" class="text-center py-12">
                                <p class="text-gray-500 text-lg">No users found</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Components -->
        
        <!-- Create User Modal -->
        <CreateUser
            :show="showCreateModal"
            :roles="roles"
            @close="showCreateModal = false"
        />

        <!-- Edit User Modal -->
        <EditUser
            :show="showEditModal"
            :user="selectedUser"
            :roles="roles"
            @close="closeEditModal"
        />

        <!-- Delete Bevestiging Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false" max-width="sm">
            <div class="p-4 sm:p-6">
                <!-- Delete Icoon -->
                <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-red-100 rounded-full">
                    <TrashIcon class="w-6 h-6 text-red-600" />
                </div>
                <!-- Modal Titel -->
                <h2 class="text-lg font-medium text-gray-900 text-center mb-2">Delete User</h2>
                <!-- Bevestiging Bericht -->
                <p class="text-sm text-gray-600 text-center mb-6">
                    Are you sure you want to delete <strong>{{ selectedUser?.name }}</strong>? This action cannot be undone.
                </p>
                <!-- Modal Actie Knoppen -->
                <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">
                    <!-- Cancel Knop -->
                    <button
                        type="button"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                        @click="showDeleteModal = false"
                    >
                        Cancel
                    </button>
                    <!-- Delete Bevestiging Knop -->
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