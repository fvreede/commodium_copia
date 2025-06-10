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
        isLoading: false,
        lastError: null
    }),

    getters: {
        totalItems: (state) => state.totals.total_items,
        
        subtotal: (state) => state.totals.subtotal,

        total: (state) => state.totals.total,

        sortedItems: (state) => {
            if (!state.items || state.items.length === 0) return [];
            
            return [...state.items].sort((a, b) => {
                let compareA, compareB;
                
                switch (state.sortBy) {
                    case 'name':
                        compareA = a.name?.toLowerCase() || '';
                        compareB = b.name?.toLowerCase() || '';
                        break;
                    case 'price':
                        // Sort by individual price, not total
                        compareA = parseFloat(a.price) || 0;
                        compareB = parseFloat(b.price) || 0;
                        break;
                    case 'quantity':
                        compareA = parseInt(a.quantity) || 0;
                        compareB = parseInt(b.quantity) || 0;
                        break;
                    default:
                        compareA = a.name?.toLowerCase() || '';
                        compareB = b.name?.toLowerCase() || '';
                }

                if (compareA < compareB) return state.sortDirection === 'asc' ? -1 : 1;
                if (compareA > compareB) return state.sortDirection === 'asc' ? 1 : -1;
                return 0;
            });
        },

        hasItems: (state) => state.items && state.items.length > 0,
        
        itemsCount: (state) => state.items ? state.items.length : 0
    },

    actions: {
        async loadCart() {
            try {
                this.isLoading = true;
                this.lastError = null;
                
                const response = await axios.get(route('cart.index'));
                
                this.items = response.data.cartItems || [];
                this.totals = response.data.totals || {
                    subtotal: 0,
                    total: 0,
                    total_items: 0,
                    items_count: 0
                };
                
                console.log('Cart loaded successfully:', {
                    itemsCount: this.items.length,
                    totalItems: this.totals.total_items
                });
                
            } catch (error) {
                console.error('Error loading cart:', error);
                this.lastError = error.response?.data?.message || 'Failed to load cart';
                
                // Reset to empty state on error
                this.items = [];
                this.totals = {
                    subtotal: 0,
                    total: 0,
                    total_items: 0,
                    items_count: 0
                };
            } finally {
                this.isLoading = false;
            }
        },

        async addToCart(product) {
            try {
                this.lastError = null;
                
                // Validate product data
                if (!product || !product.id) {
                    throw new Error('Invalid product data');
                }

                // Check if product has stock
                if (product.stock_quantity !== undefined && product.stock_quantity <= 0) {
                    return { 
                        success: false, 
                        message: 'Dit product is niet op voorraad' 
                    };
                }

                const response = await axios.post(route('cart.add'), {
                    product_id: parseInt(product.id),
                    quantity: 1
                });
                
                // Update totals from response
                if (response.data.totals) {
                    this.totals = response.data.totals;
                }
                
                // Reload cart to get updated items
                await this.loadCart();
                
                return { 
                    success: true, 
                    message: response.data.message || 'Product toegevoegd aan winkelwagen' 
                };
                
            } catch (error) {
                console.error('Error adding to cart:', error);
                this.lastError = error.response?.data?.message || 'Failed to add item to cart';
                
                const message = error.response?.data?.message || error.message || 'Kon product niet toevoegen aan winkelwagen';
                return { success: false, message };
            }
        },

        async removeFromCart(product) {
            try {
                this.lastError = null;
                
                if (!product || !product.product_id) {
                    throw new Error('Invalid product data for removal');
                }

                const response = await axios.delete(route('cart.remove', product.product_id));
                
                if (response.data.totals) {
                    this.totals = response.data.totals;
                }
                
                await this.loadCart();
                
                return { 
                    success: true, 
                    message: response.data.message || 'Product verwijderd uit winkelwagen' 
                };
                
            } catch (error) {
                console.error('Error removing from cart:', error);
                this.lastError = error.response?.data?.message || 'Failed to remove item from cart';
                
                const message = error.response?.data?.message || 'Kon product niet verwijderen uit winkelwagen';
                return { success: false, message };
            }
        },

        async updateQuantity(product, quantity) {
            try {
                this.lastError = null;
                
                if (!product || !product.product_id) {
                    throw new Error('Invalid product data for update');
                }

                // Validate quantity
                const newQuantity = parseInt(quantity);
                if (isNaN(newQuantity) || newQuantity < 0) {
                    throw new Error('Invalid quantity');
                }

                // Check stock limits
                if (product.stock_quantity !== undefined && newQuantity > product.stock_quantity) {
                    return { 
                        success: false, 
                        message: `Maximaal ${product.stock_quantity} stuks beschikbaar` 
                    };
                }

                // If quantity is 0, remove the item
                if (newQuantity === 0) {
                    return await this.removeFromCart(product);
                }

                const response = await axios.patch(route('cart.update', product.product_id), {
                    quantity: newQuantity
                });
                
                if (response.data.totals) {
                    this.totals = response.data.totals;
                }
                
                await this.loadCart();
                
                return { 
                    success: true, 
                    message: response.data.message || 'Aantal bijgewerkt' 
                };
                
            } catch (error) {
                console.error('Error updating quantity:', error);
                this.lastError = error.response?.data?.message || 'Failed to update quantity';
                
                const message = error.response?.data?.message || 'Kon aantal niet bijwerken';
                return { success: false, message };
            }
        },

        async incrementQuantity(product) {
            if (!product) {
                return { success: false, message: 'Invalid product' };
            }

            // Check stock before incrementing
            const currentQuantity = parseInt(product.quantity) || 0;
            const stockQuantity = parseInt(product.stock_quantity) || 999;
            
            if (currentQuantity >= stockQuantity) {
                return { 
                    success: false, 
                    message: `Maximaal ${stockQuantity} stuks beschikbaar` 
                };
            }

            return await this.updateQuantity(product, currentQuantity + 1);
        },

        async decrementQuantity(product) {
            if (!product) {
                return { success: false, message: 'Invalid product' };
            }

            const currentQuantity = parseInt(product.quantity) || 0;
            
            if (currentQuantity <= 1) {
                return await this.removeFromCart(product);
            }
            
            return await this.updateQuantity(product, currentQuantity - 1);
        },

        async clearCart() {
            try {
                this.lastError = null;
                
                const response = await axios.delete(route('cart.clear'));
                
                this.items = [];
                this.totals = {
                    subtotal: 0,
                    total: 0,
                    total_items: 0,
                    items_count: 0
                };
                
                return { 
                    success: true, 
                    message: response.data.message || 'Winkelwagen geleegd' 
                };
                
            } catch (error) {
                console.error('Error clearing cart:', error);
                this.lastError = error.response?.data?.message || 'Failed to clear cart';
                
                return { 
                    success: false, 
                    message: 'Kon winkelwagen niet legen' 
                };
            }
        },

        setSorting(sortBy) {
            if (!['name', 'price', 'quantity'].includes(sortBy)) {
                console.warn('Invalid sort option:', sortBy);
                return;
            }

            if (this.sortBy === sortBy) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortBy = sortBy;
                this.sortDirection = 'asc';
            }
        },

        toggleSortDirection() {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        },

        // Utility method to check if product can be added to cart
        canAddProduct(product, requestedQuantity = 1) {
            if (!product) return { canAdd: false, reason: 'Invalid product' };
            
            if (product.stock_quantity !== undefined && product.stock_quantity <= 0) {
                return { canAdd: false, reason: 'Out of stock' };
            }
            
            if (product.stock_quantity !== undefined && requestedQuantity > product.stock_quantity) {
                return { canAdd: false, reason: `Only ${product.stock_quantity} available` };
            }

            return { canAdd: true };
        },

        // Method to get current quantity of a product in cart
        getProductQuantity(productId) {
            const item = this.items.find(item => 
                item.product_id === parseInt(productId) || item.id === parseInt(productId)
            );
            return item ? parseInt(item.quantity) : 0;
        },

        // Method to check if product is in cart
        isProductInCart(productId) {
            return this.items.some(item => 
                item.product_id === parseInt(productId) || item.id === parseInt(productId)
            );
        }
    }
})