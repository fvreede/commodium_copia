import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

export function useSearch() {
    const searchQuery = ref('')
    const suggestions = ref([])
    const showSuggestions = ref(false)
    const searchTimeout = ref(null)
    const isLoading = ref(false)

    const performSearch = (query = null) => {
        const searchTerm = query || searchQuery.value
        if (searchTerm && searchTerm.trim()) {
            router.get('/search', { q: searchTerm.trim() })
            showSuggestions.value = false
        }
    }

    const fetchSuggestions = async (query) => {
        if (!query || query.length < 2) {
            suggestions.value = []
            showSuggestions.value = false
            return
        }

        isLoading.value = true
        
        try {
            const response = await axios.get('/search/suggestions', {
                params: { q: query }
            })
            suggestions.value = response.data
            showSuggestions.value = suggestions.value.length > 0
        } catch (error) {
            console.error('Error fetching suggestions:', error)
            suggestions.value = []
            showSuggestions.value = false
        } finally {
            isLoading.value = false
        }
    }

    const handleSearchInput = (query) => {
        clearTimeout(searchTimeout.value)
        searchQuery.value = query
        
        if (query && query.length >= 2) {
            searchTimeout.value = setTimeout(() => {
                fetchSuggestions(query)
            }, 300) // Debounce for 300ms
        } else {
            suggestions.value = []
            showSuggestions.value = false
        }
    }

    const selectSuggestion = (product) => {
        router.get(`/product/${product.id}`)
        showSuggestions.value = false
        searchQuery.value = ''
    }

    const hideSuggestions = () => {
        setTimeout(() => {
            showSuggestions.value = false
        }, 200) // Small delay to allow click events to fire
    }

    const clearSearch = () => {
        searchQuery.value = ''
        suggestions.value = []
        showSuggestions.value = false
        clearTimeout(searchTimeout.value)
    }

    return {
        searchQuery,
        suggestions,
        showSuggestions,
        isLoading,
        performSearch,
        fetchSuggestions,
        handleSearchInput,
        selectSuggestion,
        hideSuggestions,
        clearSearch
    }
}