// @/Stores/cart.js - Enhanced version with order integration
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
        lastUpdated: null,
        pendingUpdates: new Set(), // Track pending API calls
        updateQueue: new Map(), // Queue voor pending updates
        lastUpdateTime: new Map(), // Track laatste update tijd per product
        // NEW: Order integration state
        orderProcessing: false,
        orderSuccess: false,
        lastOrderId: null,
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
        },

        // NEW: Check if ready for checkout
        isReadyForCheckout: (state) => {
            return state.hasItems && 
                   !state.isLoading && 
                   !state.orderProcessing &&
                   state.items.every(item => item.stock_quantity >= item.quantity);
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

        // OPTIMISTIC UPDATE with anti-spam protection
        async updateQuantityOptimistic(product, newQuantity) {
            try {
                this.lastError = null;
                
                if (!product || !product.product_id) {
                    throw new Error('Invalid product data for update');
                }

                const quantity = parseInt(newQuantity);
                if (isNaN(quantity) || quantity < 0) {
                    throw new Error('Invalid quantity');
                }

                // Check stock limits
                if (product.stock_quantity !== undefined && quantity > product.stock_quantity) {
                    return { 
                        success: false, 
                        message: `Maximaal ${product.stock_quantity} stuks beschikbaar` 
                    };
                }

                // Remove item if quantity is 0
                if (quantity === 0) {
                    return await this.removeFromCartOptimistic(product);
                }

                const productId = product.product_id;
                const now = Date.now();
                
                // DEBOUNCE: Prevent spam clicking
                const lastUpdate = this.lastUpdateTime.get(productId) || 0;
                if (now - lastUpdate < 200) { // 200ms debounce
                    console.log('Debouncing rapid clicks for product:', productId);
                    
                    // Queue the update instead of ignoring it
                    this.updateQueue.set(productId, {
                        product,
                        quantity,
                        timestamp: now
                    });
                    
                    // Process queue after delay
                    setTimeout(() => this.processQueuedUpdate(productId), 250);
                    
                    return { success: true, message: 'Update in behandeling...' };
                }
                
                this.lastUpdateTime.set(productId, now);
                
                // Check if there's already a pending update for this product
                if (this.pendingUpdates.has(productId)) {
                    console.log('Update already pending for product:', productId);
                    
                    // Update the queued request
                    this.updateQueue.set(productId, {
                        product,
                        quantity,
                        timestamp: now
                    });
                    
                    return { success: true, message: 'Update wordt verwerkt...' };
                }

                return await this.executeQuantityUpdate(product, quantity);
                
            } catch (error) {
                console.error('Error in updateQuantityOptimistic:', error);
                this.lastError = error.response?.data?.message || 'Failed to update quantity';
                return { success: false, message: 'Kon aantal niet bijwerken' };
            }
        },

        // Process queued updates
        async processQueuedUpdate(productId) {
            const queuedUpdate = this.updateQueue.get(productId);
            if (!queuedUpdate) return;
            
            // Remove from queue
            this.updateQueue.delete(productId);
            
            // Only process if not currently updating
            if (!this.pendingUpdates.has(productId)) {
                await this.executeQuantityUpdate(queuedUpdate.product, queuedUpdate.quantity);
            }
        },

        // Actual update execution
        async executeQuantityUpdate(product, quantity) {
            const productId = product.product_id;
            
            try {
                // Mark as pending
                this.pendingUpdates.add(productId);

                // 1. Update UI immediately (optimistic)
                const itemIndex = this.items.findIndex(item => item.product_id === productId);
                if (itemIndex !== -1) {
                    const oldQuantity = this.items[itemIndex].quantity;
                    const priceDiff = (quantity - oldQuantity) * this.items[itemIndex].price;
                    
                    // Update item
                    this.items[itemIndex].quantity = quantity;
                    this.items[itemIndex].line_total = quantity * this.items[itemIndex].price;
                    
                    // Update totals
                    this.totals.total_items = this.totals.total_items - oldQuantity + quantity;
                    this.totals.subtotal = Math.max(0, (this.totals.subtotal || 0) + priceDiff);
                    this.totals.total = Math.max(0, (this.totals.total || 0) + priceDiff);
                }

                // 2. Sync with server
                const response = await axios.patch(`/cart/${productId}`, {
                    quantity: quantity
                });
                
                // Update with server totals (more accurate)
                if (response.data.totals) {
                    this.totals = response.data.totals;
                }
                
                // Optional: Refresh cart data occasionally for integrity
                if (Math.random() < 0.05) { // 5% chance
                    setTimeout(() => this.loadCart(true), 1000);
                }
                
                return { 
                    success: true, 
                    message: response.data.message || 'Aantal bijgewerkt' 
                };
                
            } catch (error) {
                console.error('API error during update:', error);
                
                // Revert optimistic changes
                await this.loadCart(true);
                
                throw error;
                
            } finally {
                this.pendingUpdates.delete(productId);
                this.lastUpdateTime.set(productId, Date.now());
            }
        },

        async removeFromCartOptimistic(product) {
            try {
                this.lastError = null;
                
                if (!product || !product.product_id) {
                    throw new Error('Invalid product data for removal');
                }

                const productId = product.product_id;
                this.pendingUpdates.add(productId);

                // 1. FIRST: Remove from UI optimistically
                const itemIndex = this.items.findIndex(item => item.product_id === productId);
                if (itemIndex !== -1) {
                    const removedItem = this.items[itemIndex];
                    
                    // Remove item from array
                    this.items.splice(itemIndex, 1);
                    
                    // Update totals
                    this.totals.total_items -= removedItem.quantity;
                    this.totals.subtotal -= (removedItem.price * removedItem.quantity);
                    this.totals.total -= (removedItem.price * removedItem.quantity);
                    this.totals.items_count = this.items.length;
                }

                // 2. THEN: Sync with server
                try {
                    const response = await axios.delete(`/cart/${productId}`);
                    
                    if (response.data.totals) {
                        this.totals = response.data.totals;
                    }
                    
                    return { 
                        success: true, 
                        message: response.data.message || 'Product verwijderd uit winkelwagen' 
                    };
                    
                } catch (apiError) {
                    console.error('API error, reverting removal:', apiError);
                    
                    // Revert by reloading cart
                    await this.loadCart(true);
                    throw apiError;
                }
                
            } catch (error) {
                console.error('Error removing from cart:', error);
                this.lastError = error.response?.data?.message || 'Failed to remove item from cart';
                
                const message = error.response?.data?.message || 'Kon product niet verwijderen uit winkelwagen';
                return { success: false, message };
                
            } finally {
                this.pendingUpdates.delete(product.product_id);
            }
        },

        // Improved increment with anti-spam
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

            // Prevent rapid fire clicks
            const productId = product.product_id;
            const now = Date.now();
            const lastClick = this.lastUpdateTime.get(`click_${productId}`) || 0;
            
            if (now - lastClick < 100) { // 100ms anti-spam
                console.log('Too fast clicking detected, ignoring');
                return { success: true, message: 'Te snel geklikt, even wachten...' };
            }
            
            this.lastUpdateTime.set(`click_${productId}`, now);
            
            return await this.updateQuantityOptimistic(product, currentQuantity + 1);
        },

        async decrementQuantity(product) {
            if (!product) {
                return { success: false, message: 'Invalid product' };
            }

            const currentQuantity = parseInt(product.quantity) || 0;
            
            // Prevent rapid fire clicks
            const productId = product.product_id;
            const now = Date.now();
            const lastClick = this.lastUpdateTime.get(`click_${productId}`) || 0;
            
            if (now - lastClick < 100) { // 100ms anti-spam
                console.log('Too fast clicking detected, ignoring');
                return { success: true, message: 'Te snel geklikt, even wachten...' };
            }
            
            this.lastUpdateTime.set(`click_${productId}`, now);
            
            if (currentQuantity <= 1) {
                return await this.removeFromCartOptimistic(product);
            }
            
            return await this.updateQuantityOptimistic(product, currentQuantity - 1);
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
                
                // Only refresh cart for add operations to ensure consistency
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

        // NEW: Order-related methods
        async prepareForCheckout() {
            try {
                // Validate cart before checkout
                await this.validateCart();
                
                if (!this.hasItems) {
                    throw new Error('Winkelwagen is leeg');
                }

                // Check stock for all items
                const stockIssues = this.items.filter(item => 
                    item.stock_quantity !== undefined && 
                    item.quantity > item.stock_quantity
                );

                if (stockIssues.length > 0) {
                    throw new Error('Sommige producten zijn niet meer voldoende op voorraad');
                }

                return { success: true };
                
            } catch (error) {
                this.lastError = error.message;
                return { success: false, message: error.message };
            }
        },

        async markOrderProcessing(orderId = null) {
            this.orderProcessing = true;
            this.lastOrderId = orderId;
            this.orderSuccess = false;
        },

        async markOrderSuccess(orderId) {
            this.orderProcessing = false;
            this.orderSuccess = true;
            this.lastOrderId = orderId;
            
            // Clear cart after successful order
            await this.clearCartAfterOrder();
        },

        async markOrderFailed() {
            this.orderProcessing = false;
            this.orderSuccess = false;
            // Don't clear cart on failure so user can retry
        },

        async clearCartAfterOrder() {
            try {
                // Clear local state immediately
                this.items = [];
                this.totals = {
                    subtotal: 0,
                    total: 0,
                    total_items: 0,
                    items_count: 0
                };
                
                // Clear any pending updates
                this.pendingUpdates.clear();
                this.updateQueue.clear();
                this.lastUpdateTime.clear();
                
                this.lastUpdated = Date.now();
                
                console.log('Cart cleared after successful order');
                
                return { success: true };
                
            } catch (error) {
                console.error('Error clearing cart after order:', error);
                return { success: false, message: 'Fout bij leegmaken winkelwagen' };
            }
        },

        resetOrderState() {
            this.orderProcessing = false;
            this.orderSuccess = false;
            this.lastOrderId = null;
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
                lastError: this.lastError,
                orderProcessing: this.orderProcessing,
                orderSuccess: this.orderSuccess,
                isReadyForCheckout: this.isReadyForCheckout
            };
        }
    }
})