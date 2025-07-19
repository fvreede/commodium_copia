/**
 * Bestandsnaam: useSearch.js
 * Auteur: Fabio Vreede
 * Versie: v1.0.2
 * Datum: 2025-05-27
 * Tijd: 09:58:20
 * Doel: Geavanceerde zoek composable voor Vue 3 e-commerce applicatie met real-time suggesties, caching, recent searches management en populaire zoektermen. Bevat debounced input handling, localStorage integratie, error handling en performance optimalisaties voor optimale zoekervaring en conversie.
 */

// resources/js/Composables/useSearch.js

import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

/**
 * HOOFDZOEK COMPOSABLE FUNCTIE
 * Vue 3 Composition API hook voor complete zoekfunctionaliteit
 * 
 * @returns {Object} Reactive state en methoden voor zoek implementatie
 */
export function useSearch() {
    /**
     * REACTIVE STATE DEFINITIE
     * Kern reactive variabelen voor zoek interface en data management
     */
    const searchQuery = ref('')                    // Huidige zoek query input
    const suggestions = ref([])                    // Array van zoek suggesties van API
    const showSuggestions = ref(false)             // Boolean voor suggesties dropdown zichtbaarheid
    const searchTimeout = ref(null)                // Timeout referentie voor debounced input
    const isLoading = ref(false)                   // Loading state voor API calls
    const recentSearches = ref(JSON.parse(localStorage.getItem('recentSearches') || '[]'))  // Recent searches uit localStorage
    const searchCache = new Map()                  // In-memory cache voor zoekresultaten performance

    /**
     * COMPUTED EIGENSCHAPPEN
     * Reactive berekeningen voor UI optimalisatie
     */
    
    // Gefilterde suggesties gelimiteerd tot 6 items voor betere UX
    const filteredSuggestions = computed(() => {
        return suggestions.value.slice(0, 6) // Limiteer tot 6 suggesties voor betere UX
    })

    /**
     * HOOFDZOEK FUNCTIONALITEIT
     * Voert zoekactie uit en navigeert naar zoekresultaten pagina
     * 
     * @param {string|null} query - Optionele zoek query, gebruikt searchQuery.value als null
     */
    const performSearch = (query = null) => {
        const searchTerm = query || searchQuery.value
        if (searchTerm && searchTerm.trim()) {
            const trimmedQuery = searchTerm.trim()
            
            // Voeg toe aan recent searches voor toekomstige suggesties
            addToRecentSearches(trimmedQuery)
            
            // Navigeer naar zoekresultaten pagina via Inertia.js
            router.get('/search', { q: trimmedQuery })
            
            // Cleanup UI state na succesvolle navigatie
            showSuggestions.value = false
            searchQuery.value = ''
        }
    }

    /**
     * SUGGESTIES OPHALEN VAN API
     * Haalt zoek suggesties op met caching en error handling
     * 
     * @param {string} query - Zoek query voor suggesties API call
     */
    const fetchSuggestions = async (query) => {
        // Validatie: minimum 2 karakters vereist voor API call
        if (!query || query.length < 2) {
            suggestions.value = []
            showSuggestions.value = false
            return
        }

        // Controleer cache eerst voor performance optimalisatie
        const cacheKey = query.toLowerCase()
        if (searchCache.has(cacheKey)) {
            suggestions.value = searchCache.get(cacheKey)
            showSuggestions.value = suggestions.value.length > 0
            return
        }

        isLoading.value = true
        
        try {
            // API call naar Laravel backend voor zoek suggesties
            const response = await axios.get('/search/suggestions', {
                params: { 
                    q: query,
                    limit: 8 // Vraag meer van backend, filter op frontend
                },
                timeout: 5000 // 5 seconden timeout voor responsiviteit
            })
            
            const results = response.data || []
            
            // Cache resultaten voor 5 minuten voor performance
            searchCache.set(cacheKey, results)
            setTimeout(() => searchCache.delete(cacheKey), 5 * 60 * 1000)
            
            // Update reactive state met nieuwe suggesties
            suggestions.value = results
            showSuggestions.value = results.length > 0
        } catch (error) {
            console.error('Error fetching suggestions:', error)
            suggestions.value = []
            showSuggestions.value = false
            
            // Fallback: toon recent searches als backup bij API falen
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

    /**
     * DEBOUNCED INPUT HANDLER
     * Behandelt gebruiker input met debouncing voor performance optimalisatie
     * 
     * @param {string} query - Nieuwe input waarde van zoek veld
     */
    const handleSearchInput = (query) => {
        // Clear vorige timeout om debouncing te implementeren
        clearTimeout(searchTimeout.value)
        searchQuery.value = query
        
        if (query && query.length >= 2) {
            // Debounce API calls met 300ms delay voor performance
            searchTimeout.value = setTimeout(() => {
                fetchSuggestions(query)
            }, 300) // Debounce voor 300ms
        } else if (query && query.length === 1) {
            // Toon recent searches voor single karakter als hint
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
            // Clear suggesties bij lege query
            suggestions.value = []
            showSuggestions.value = false
        }
    }

    /**
     * SUGGESTIE SELECTIE HANDLER
     * Behandelt klik op suggestie item (product of recent search)
     * 
     * @param {Object} item - Geselecteerde suggestie item met type informatie
     */
    const selectSuggestion = (item) => {
        if (item.isRecentSearch) {
            // Recent search selectie - voer zoekactie uit
            performSearch(item.name)
        } else {
            // Product selectie - navigeer direct naar product pagina
            router.get(`/product/${item.id}`)
            showSuggestions.value = false
            searchQuery.value = ''
        }
    }

    /**
     * SUGGESTIES VERBERGEN
     * Verbergt suggesties dropdown met kleine delay voor click events
     */
    const hideSuggestions = () => {
        setTimeout(() => {
            showSuggestions.value = false
        }, 200) // Kleine delay om click events te laten afvuren
    }

    /**
     * ZOEK STATE OPSCHONEN
     * Reset alle zoek gerelateerde state naar default waarden
     */
    const clearSearch = () => {
        searchQuery.value = ''
        suggestions.value = []
        showSuggestions.value = false
        clearTimeout(searchTimeout.value)
    }

    /**
     * RECENT SEARCHES MANAGEMENT
     * Voegt nieuwe zoek query toe aan recent searches lijst
     * 
     * @param {string} query - Zoek query om toe te voegen aan recent searches
     */
    const addToRecentSearches = (query) => {
        try {
            // Verwijder als al bestaat om duplicaten te voorkomen
            const filtered = recentSearches.value.filter(search => 
                search.toLowerCase() !== query.toLowerCase()
            )
            
            // Voeg toe aan begin van lijst (meest recent eerst)
            filtered.unshift(query)
            
            // Behoud alleen laatste 10 searches voor storage efficiency
            recentSearches.value = filtered.slice(0, 10)
            
            // Persisteer naar localStorage voor sessie continuïteit
            localStorage.setItem('recentSearches', JSON.stringify(recentSearches.value))
        } catch (error) {
            console.error('Error saving recent searches:', error)
        }
    }

    /**
     * RECENT SEARCHES OPSCHONEN
     * Wist alle recent searches uit memory en localStorage
     */
    const clearRecentSearches = () => {
        recentSearches.value = []
        try {
            localStorage.removeItem('recentSearches')
        } catch (error) {
            console.error('Error clearing recent searches:', error)
        }
    }

    /**
     * INDIVIDUELE RECENT SEARCH VERWIJDEREN
     * Verwijdert specifieke search uit recent searches lijst
     * 
     * @param {string} searchToRemove - Zoek query om te verwijderen
     */
    const removeRecentSearch = (searchToRemove) => {
        recentSearches.value = recentSearches.value.filter(search => search !== searchToRemove)
        try {
            localStorage.setItem('recentSearches', JSON.stringify(recentSearches.value))
        } catch (error) {
            console.error('Error updating recent searches:', error)
        }
    }

    /**
     * POPULAIRE SUGGESTIES PRELOADING
     * Laadt populaire/trending producten voor betere initial experience
     */
    const preloadPopularSuggestions = async () => {
        try {
            const response = await axios.get('/search/popular', { timeout: 3000 })
            const popular = response.data || []
            
            // Cache populaire items voor snelle toegang
            searchCache.set('__popular__', popular)
        } catch (error) {
            console.error('Error preloading popular suggestions:', error)
        }
    }

    /**
     * POPULAIRE SUGGESTIES OPHALEN
     * Toont populaire suggesties wanneer geen query actief
     */
    const getPopularSuggestions = () => {
        const popular = searchCache.get('__popular__') || []
        suggestions.value = popular.slice(0, 4)
        showSuggestions.value = popular.length > 0
    }

    /**
     * FOCUS MANAGEMENT
     * Behandelt focus events op zoek input voor betere UX
     */
    const focusSearch = () => {
        showSuggestions.value = true
        
        // Als geen query, toon recent searches of populaire items
        if (!searchQuery.value) {
            if (recentSearches.value.length > 0) {
                // Toon recent searches als beschikbaar
                const recentSuggestions = recentSearches.value
                    .slice(0, 4)
                    .map(search => ({
                        id: `recent-${search}`,
                        name: search,
                        isRecentSearch: true
                    }))
                
                suggestions.value = recentSuggestions
            } else {
                // Fallback naar populaire suggesties
                getPopularSuggestions()
            }
        }
    }

    /**
     * CLEANUP FUNCTIE
     * Ruimt timers en cache op bij component unmount
     */
    const cleanup = () => {
        clearTimeout(searchTimeout.value)
        searchCache.clear()
    }

    /**
     * COMPOSABLE RETURN OBJECT
     * Exporteert alle reactive state en methoden voor gebruik in components
     */
    return {
        // Reactive State Export
        searchQuery,                    // Huidige zoek input waarde
        suggestions: filteredSuggestions, // Gefilterde suggesties voor UI
        showSuggestions,               // Suggesties dropdown zichtbaarheid
        isLoading,                     // Loading state voor feedback
        recentSearches,                // Recent searches array
        
        // Hoofdzoek Methoden
        performSearch,                 // Voert zoekactie uit
        fetchSuggestions,             // Haalt suggesties op van API
        handleSearchInput,            // Debounced input handler
        selectSuggestion,             // Suggestie selectie handler
        hideSuggestions,              // Verbergt suggesties dropdown
        clearSearch,                  // Reset zoek state
        focusSearch,                  // Focus event handler
        
        // Recent Searches Management
        addToRecentSearches,          // Voegt toe aan recent searches
        clearRecentSearches,          // Wist alle recent searches
        removeRecentSearch,           // Verwijdert specifieke recent search
        
        // Populaire Suggesties
        preloadPopularSuggestions,    // Preload populaire items
        getPopularSuggestions,        // Toont populaire suggesties
        
        // Lifecycle Management
        cleanup                       // Cleanup functie voor unmount
    }
}