// @/Stores/cart.js
import { defineStore } from 'pinia'
import axios from 'axios'

export const useCartStore = defineStore('cart', {
    state: () => ({
        items: [],
        totals: {
            subtotal: 0,
            total: 0,
            total_items: 0,
            items_count: 0
        },
        sortBy: 'name',
        sortDirection: 'asc',
        isLoading: false
    }),

    getters: {
        totalItems: (state) => state.totals.total_items,
        
        subtotal: (state) => state.totals.subtotal,

        total: (state) => state.totals.total,

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
                this.totals = response.data.totals || {
                    subtotal: 0,
                    total: 0,
                    total_items: 0,
                    items_count: 0
                }
            } catch (error) {
                console.error('Error loading cart:', error)
                this.items = []
                this.totals = {
                    subtotal: 0,
                    total: 0,
                    total_items: 0,
                    items_count: 0
                }
            } finally {
                this.isLoading = false
            }
        },

        async addToCart(product) {
            try {
                const response = await axios.post(route('cart.add'), {
                    product_id: product.id,
                    quantity: 1
                })
                
                // Update totals from response
                this.totals = response.data.totals
                
                // Reload cart to get updated items
                await this.loadCart()
                
                return { success: true, message: response.data.message }
            } catch (error) {
                console.error('Error adding to cart:', error)
                const message = error.response?.data?.message || 'Failed to add item to cart'
                return { success: false, message }
            }
        },

        async removeFromCart(product) {
            try {
                const response = await axios.delete(route('cart.remove', product.product_id))
                this.totals = response.data.totals
                await this.loadCart()
                return { success: true, message: response.data.message }
            } catch (error) {
                console.error('Error removing from cart:', error)
                const message = error.response?.data?.message || 'Failed to remove item from cart'
                return { success: false, message }
            }
        },

        async updateQuantity(product, quantity) {
            try {
                const response = await axios.patch(route('cart.update', product.product_id), {
                    quantity: quantity
                })
                this.totals = response.data.totals
                await this.loadCart()
                return { success: true, message: response.data.message }
            } catch (error) {
                console.error('Error updating quantity:', error)
                const message = error.response?.data?.message || 'Failed to update quantity'
                return { success: false, message }
            }
        },

        async incrementQuantity(product) {
            return await this.updateQuantity(product, product.quantity + 1)
        },

        async decrementQuantity(product) {
            if (product.quantity <= 1) {
                return await this.removeFromCart(product)
            }
            return await this.updateQuantity(product, product.quantity - 1)
        },

        async clearCart() {
            try {
                const response = await axios.delete(route('cart.clear'))
                this.items = []
                this.totals = {
                    subtotal: 0,
                    total: 0,
                    total_items: 0,
                    items_count: 0
                }
                return { success: true, message: response.data.message }
            } catch (error) {
                console.error('Error clearing cart:', error)
                return { success: false, message: 'Failed to clear cart' }
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