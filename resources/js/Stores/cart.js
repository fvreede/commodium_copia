// @/Stores/cart.js - Fixed version
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
        lastError: null,
        lastUpdated: null
    }),

    getters: {
        totalItems: (state) => state.totals.total_items || 0,
        
        subtotal: (state) => state.totals.subtotal || 0,

        total: (state) => state.totals.total || 0,

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
        
        itemsCount: (state) => state.items ? state.items.length : 0,

        // Helper to check if cart data is stale (older than 30 seconds)
        isStale: (state) => {
            if (!state.lastUpdated) return true;
            return (Date.now() - state.lastUpdated) > 30000;
        }
    },

    actions: {
        async loadCart(force = false) {
            // Don't reload if data is fresh unless forced
            if (!force && !this.isStale && this.items.length > 0) {
                return;
            }

            try {
                this.isLoading = true;
                this.lastError = null;
                
                const response = await axios.get('/cart');
                
                // Process items to ensure proper image URLs
                this.items = this.processCartItems(response.data.cartItems || []);
                this.totals = response.data.totals || {
                    subtotal: 0,
                    total: 0,
                    total_items: 0,
                    items_count: 0
                };
                
                this.lastUpdated = Date.now();
                
                console.log('Cart loaded successfully:', {
                    itemsCount: this.items.length,
                    totalItems: this.totals.total_items,
                    subtotal: this.totals.subtotal,
                    timestamp: new Date().toISOString()
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

        // Process cart items to ensure proper image URLs and data consistency
        processCartItems(items) {
            return items.map(item => ({
                ...item,
                image_path: this.getImageUrl(item.image_path),
                price: parseFloat(item.price) || 0,
                quantity: parseInt(item.quantity) || 0,
                stock_quantity: parseInt(item.stock_quantity) || 0,
                line_total: parseFloat(item.line_total) || (parseFloat(item.price) * parseInt(item.quantity))
            }));
        },

        // Get proper image URL with fallback handling
        getImageUrl(imagePath) {
            if (!imagePath) {
                return null; // Return null instead of placeholder to avoid 404s
            }

            // If it's already a full URL, return as is
            if (imagePath.startsWith('http') || imagePath.startsWith('/storage/') || imagePath.startsWith('/images/')) {
                return imagePath;
            }

            // Assume it's a storage path
            return `/storage/${imagePath}`;
        },

        async addToCart(product) {
            try {
                this.lastError = null;
                
                if (!product || !product.id) {
                    throw new Error('Invalid product data');
                }

                if (product.stock_quantity !== undefined && product.stock_quantity <= 0) {
                    return { 
                        success: false, 
                        message: 'Dit product is niet op voorraad' 
                    };
                }

                const response = await axios.post('/cart/add', {
                    product_id: parseInt(product.id),
                    quantity: 1
                });
                
                if (response.data.totals) {
                    this.totals = response.data.totals;
                }
                
                // Force reload to ensure consistency
                await this.loadCart(true);
                
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

                const response = await axios.delete(`/cart/${product.product_id}`);
                
                if (response.data.totals) {
                    this.totals = response.data.totals;
                }
                
                // Force reload to ensure consistency
                await this.loadCart(true);
                
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

                const newQuantity = parseInt(quantity);
                if (isNaN(newQuantity) || newQuantity < 0) {
                    throw new Error('Invalid quantity');
                }

                if (product.stock_quantity !== undefined && newQuantity > product.stock_quantity) {
                    return { 
                        success: false, 
                        message: `Maximaal ${product.stock_quantity} stuks beschikbaar` 
                    };
                }

                if (newQuantity === 0) {
                    return await this.removeFromCart(product);
                }

                const response = await axios.patch(`/cart/${product.product_id}`, {
                    quantity: newQuantity
                });
                
                if (response.data.totals) {
                    this.totals = response.data.totals;
                }
                
                // Force reload to ensure consistency
                await this.loadCart(true);
                
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
                
                const response = await axios.delete('/cart');
                
                this.items = [];
                this.totals = {
                    subtotal: 0,
                    total: 0,
                    total_items: 0,
                    items_count: 0
                };
                this.lastUpdated = Date.now();
                
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

        // Enhanced method to refresh cart data from server
        async refreshCart() {
            return await this.loadCart(true);
        },

        // Method to validate cart integrity (useful for checkout)
        async validateCart() {
            try {
                const response = await axios.get('/api/checkout/cart-data');
                
                if (response.data.cartItems) {
                    this.items = this.processCartItems(response.data.cartItems);
                    this.totals = response.data.totals;
                    this.lastUpdated = Date.now();
                }

                return {
                    success: true,
                    hasItems: response.data.hasItems,
                    items: this.items
                };
                
            } catch (error) {
                console.error('Error validating cart:', error);
                return {
                    success: false,
                    message: 'Kon winkelwagen niet valideren'
                };
            }
        },

        // Utility methods
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

        getProductQuantity(productId) {
            const item = this.items.find(item => 
                item.product_id === parseInt(productId) || item.id === parseInt(productId)
            );
            return item ? parseInt(item.quantity) : 0;
        },

        isProductInCart(productId) {
            return this.items.some(item => 
                item.product_id === parseInt(productId) || item.id === parseInt(productId)
            );
        },

        // Get cart summary for display
        getCartSummary() {
            return {
                itemCount: this.itemsCount,
                totalItems: this.totalItems,
                subtotal: this.subtotal,
                total: this.total,
                hasItems: this.hasItems,
                isLoading: this.isLoading,
                lastError: this.lastError
            };
        }
    }
})