import { defineStore } from "pinia";
import { router } from '@inertiajs/vue3';

export const useUserStore = defineStore('users', {
    state: () => ({
        users: [],
        searchQuery: '',
        isLoading: false,
    }),

    getters: {
        filteredUsers: (state) => state.users,
        hasSearchResults: (state) => state.users.length > 0,
    },

    actions: {
        async performSearch() {
            if (!this.searchQuery) {
                this.users = [];
                return;
            }

            this.isLoading = true;

            try {
                // TODO: create this route to Laravel backend
                const response = await fetch(route('admin.api.users.search', { query: this.searchQuery }));
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                const data = await response.json();
                this.users = data;
            } catch (error) {
                console.error('Search failed:', error);
                this.users = [];
            } finally {
                this.isLoading = false;
            }
        },

        clearSearch() {
            this.searchQuery = '';
            this.users = [];
        }
    }
});