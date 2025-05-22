// @/Stores/cart.js
import { defineStore } from 'pinia'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

export const useCartStore = defineStore('cart', {
    state: () => ({
        items: [],
        sortBy: 'name',
        sortDirection: 'asc',
        isLoading: false
    }),

    getters: {
        totalItems: (state) => state.items.reduce((total, item) => total + item.quantity, 0),
        
        subtotal: (state) => state.items.reduce((total, item) => total + (item.price * item.quantity), 0),

        total: (state) => {
            const subtotal = state.items.reduce((total, item) => total + (item.price * item.quantity), 0)
            const deliveryCost = 0
            return subtotal + deliveryCost
        },

        sortedItems: (state) => {
            return [...state.items].sort((a, b) => {
                let compareA = a[state.sortBy]
                let compareB = b[state.sortBy]
                
                if (state.sortBy === 'price') {
                    compareA = compareA * a.quantity
                    compareB = compareB * b.quantity
                }

                if (compareA < compareB) return state.sortDirection === 'asc' ? -1 : 1
                if (compareA > compareB) return state.sortDirection === 'asc' ? 1 : -1
                return 0
            })
        }
    },

    actions: {
        async loadCart() {
            try {
                this.isLoading = true
                const response = await axios.get(route('cart.index'))
                this.items = response.data.cartItems || []
            } catch (error) {
                console.error('Fout bij laden winkelwagen:', error)
            } finally {
                this.isLoading = false
            }
        },

        async addToCart(product) {
            try {
                const response = await axios.post(route('cart.add'), {
                    product_id: product.id,
                    quantity: 1,
                    price: product.price,
                    name: product.name,
                    image_path: product.image_path
                })
                await this.loadCart() // Herlaad de winkelwagen na toevoegen
            } catch (error) {
                console.error('Fout bij toevoegen aan winkelwagen:', error)
            }
        },

        async removeFromCart(product) {
            try {
                await axios.delete(route('cart.remove', product.id))
                await this.loadCart() // Herlaad de winkelwagen na verwijderen
            } catch (error) {
                console.error('Fout bij verwijderen uit winkelwagen:', error)
            }
        },

        async updateQuantity(product, quantity) {
            try {
                await axios.patch(route('cart.update', product.id), {
                    quantity: quantity
                })
                await this.loadCart() // Herlaad de winkelwagen na update
            } catch (error) {
                console.error('Fout bij bijwerken hoeveelheid:', error)
            }
        },

        async clearCart() {
            try {
                await axios.delete(route('cart.clear'))
                this.items = []
            } catch (error) {
                console.error('Fout bij legen winkelwagen:', error)
            }
        },

        setSorting(sortBy) {
            if (this.sortBy === sortBy) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc'
            } else {
                this.sortBy = sortBy
                this.sortDirection = 'asc'
            }
        }
    }
})