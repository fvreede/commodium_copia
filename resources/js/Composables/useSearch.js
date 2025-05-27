// resources/js/Composables/useSearch.js

import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

export function useSearch() {
    const searchQuery = ref('')
    const suggestions = ref([])
    const showSuggestions = ref(false)
    const searchTimeout = ref(null)
    const isLoading = ref(false)
    const recentSearches = ref(JSON.parse(localStorage.getItem('recentSearches') || '[]'))
    const searchCache = new Map()

    // Computed property for filtered suggestions
    const filteredSuggestions = computed(() => {
        return suggestions.value.slice(0, 6) // Limit to 6 suggestions for better UX
    })

    const performSearch = (query = null) => {
        const searchTerm = query || searchQuery.value
        if (searchTerm && searchTerm.trim()) {
            const trimmedQuery = searchTerm.trim()
            
            // Add to recent searches
            addToRecentSearches(trimmedQuery)
            
            // Navigate to search page
            router.get('/search', { q: trimmedQuery })
            
            // Clean up
            showSuggestions.value = false
            searchQuery.value = ''
        }
    }

    const fetchSuggestions = async (query) => {
        if (!query || query.length < 2) {
            suggestions.value = []
            showSuggestions.value = false
            return
        }

        // Check cache first
        const cacheKey = query.toLowerCase()
        if (searchCache.has(cacheKey)) {
            suggestions.value = searchCache.get(cacheKey)
            showSuggestions.value = suggestions.value.length > 0
            return
        }

        isLoading.value = true
        
        try {
            const response = await axios.get('/search/suggestions', {
                params: { 
                    q: query,
                    limit: 8 // Request more from backend, filter on frontend
                },
                timeout: 5000 // 5 second timeout
            })
            
            const results = response.data || []
            
            // Cache the results for 5 minutes
            searchCache.set(cacheKey, results)
            setTimeout(() => searchCache.delete(cacheKey), 5 * 60 * 1000)
            
            suggestions.value = results
            showSuggestions.value = results.length > 0
        } catch (error) {
            console.error('Error fetching suggestions:', error)
            suggestions.value = []
            showSuggestions.value = false
            
            // Show recent searches as fallback if available
            if (recentSearches.value.length > 0) {
                const filteredRecent = recentSearches.value
                    .filter(search => search.toLowerCase().includes(query.toLowerCase()))
                    .slice(0, 3)
                    .map(search => ({
                        id: `recent-${search}`,
                        name: search,
                        isRecentSearch: true
                    }))
                
                if (filteredRecent.length > 0) {
                    suggestions.value = filteredRecent
                    showSuggestions.value = true
                }
            }
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
        } else if (query && query.length === 1) {
            // Show recent searches for single character
            if (recentSearches.value.length > 0) {
                const recentSuggestions = recentSearches.value
                    .slice(0, 3)
                    .map(search => ({
                        id: `recent-${search}`,
                        name: search,
                        isRecentSearch: true
                    }))
                
                suggestions.value = recentSuggestions
                showSuggestions.value = true
            }
        } else {
            suggestions.value = []
            showSuggestions.value = false
        }
    }

    const selectSuggestion = (item) => {
        if (item.isRecentSearch) {
            // Handle recent search selection
            performSearch(item.name)
        } else {
            // Handle product selection
            router.get(`/product/${item.id}`)
            showSuggestions.value = false
            searchQuery.value = ''
        }
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

    const addToRecentSearches = (query) => {
        try {
            // Remove if already exists
            const filtered = recentSearches.value.filter(search => 
                search.toLowerCase() !== query.toLowerCase()
            )
            
            // Add to beginning
            filtered.unshift(query)
            
            // Keep only last 10 searches
            recentSearches.value = filtered.slice(0, 10)
            
            // Save to localStorage
            localStorage.setItem('recentSearches', JSON.stringify(recentSearches.value))
        } catch (error) {
            console.error('Error saving recent searches:', error)
        }
    }

    const clearRecentSearches = () => {
        recentSearches.value = []
        try {
            localStorage.removeItem('recentSearches')
        } catch (error) {
            console.error('Error clearing recent searches:', error)
        }
    }

    const removeRecentSearch = (searchToRemove) => {
        recentSearches.value = recentSearches.value.filter(search => search !== searchToRemove)
        try {
            localStorage.setItem('recentSearches', JSON.stringify(recentSearches.value))
        } catch (error) {
            console.error('Error updating recent searches:', error)
        }
    }

    // Preload popular/trending products when component mounts
    const preloadPopularSuggestions = async () => {
        try {
            const response = await axios.get('/search/popular', { timeout: 3000 })
            const popular = response.data || []
            
            // Cache popular items
            searchCache.set('__popular__', popular)
        } catch (error) {
            console.error('Error preloading popular suggestions:', error)
        }
    }

    // Get popular suggestions when no query
    const getPopularSuggestions = () => {
        const popular = searchCache.get('__popular__') || []
        suggestions.value = popular.slice(0, 4)
        showSuggestions.value = popular.length > 0
    }

    // Focus management
    const focusSearch = () => {
        showSuggestions.value = true
        
        // If no query, show recent searches or popular items
        if (!searchQuery.value) {
            if (recentSearches.value.length > 0) {
                const recentSuggestions = recentSearches.value
                    .slice(0, 4)
                    .map(search => ({
                        id: `recent-${search}`,
                        name: search,
                        isRecentSearch: true
                    }))
                
                suggestions.value = recentSuggestions
            } else {
                getPopularSuggestions()
            }
        }
    }

    // Cleanup function
    const cleanup = () => {
        clearTimeout(searchTimeout.value)
        searchCache.clear()
    }

    return {
        // Reactive state
        searchQuery,
        suggestions: filteredSuggestions,
        showSuggestions,
        isLoading,
        recentSearches,
        
        // Methods
        performSearch,
        fetchSuggestions,
        handleSearchInput,
        selectSuggestion,
        hideSuggestions,
        clearSearch,
        focusSearch,
        
        // Recent searches management
        addToRecentSearches,
        clearRecentSearches,
        removeRecentSearch,
        
        // Popular suggestions
        preloadPopularSuggestions,
        getPopularSuggestions,
        
        // Cleanup
        cleanup
    }
}