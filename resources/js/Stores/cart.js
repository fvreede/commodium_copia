/**
 * Bestandsnaam: cart.js)
 * Auteur: Fabio Vreede
 * Versie: v2.0.8
 * Datum: 2025-07-01
 * Tijd: 01:25:04
 * Doel: Geavanceerde Pinia store voor winkelwagen management met optimistic updates, anti-spam beveiliging,
 *       order integratie, en real-time synchronisatie. Bevat debouncing voor rapid clicks, queue systeem
 *       voor pending updates, stock validatie, image URL processing, sorting functionaliteit, en complete
 *       checkout flow integratie. Enhanced versie met robuuste error handling en performance optimalisaties
 *       voor seamless e-commerce user experience.
 */

// Pinia store definition import
import { defineStore } from 'pinia'
// Axios voor API communicatie
import axios from 'axios'

export const useCartStore = defineStore('cart', {
    // ========== STATE DEFINITIE ==========
    state: () => ({
        // Cart items en totalen
        items: [],                            // Array van cart items met product informatie
        totals: {                             // Cart totalen object
            subtotal: 0,                      // Subtotaal voor belasting
            total: 0,                         // Eindtotaal inclusief kosten
            total_items: 0,                   // Totaal aantal items (quantity sum)
            items_count: 0                    // Aantal unieke items in cart
        },

        // Sorting en filtering state
        sortBy: 'name',                       // Sorteer veld: 'name', 'price', 'quantity'
        sortDirection: 'asc',                 // Sorteer richting: 'asc' of 'desc'

        // Loading en error states
        isLoading: false,                     // Global loading indicator voor cart operaties
        lastError: null,                      // Laatste error message voor user feedback
        lastUpdated: null,                    // Timestamp van laatste cart update

        // Anti-spam en performance optimization
        pendingUpdates: new Set(),            // Track pending API calls per product ID
        updateQueue: new Map(),               // Queue voor pending updates tijdens debouncing
        lastUpdateTime: new Map(),            // Track laatste update tijd per product voor debouncing

        // Order integratie state
        orderProcessing: false,               // Indicator dat order wordt verwerkt
        orderSuccess: false,                  // Indicator voor succesvolle order placement
        lastOrderId: null,                    // ID van laatste geplaatste order
    }),

    // ========== GETTERS ==========
    getters: {
        /**
         * Totaal aantal items in cart (sum van alle quantities)
         * @param {Object} state - Store state
         * @returns {number} Totaal aantal items
         */
        totalItems: (state) => state.totals.total_items || 0,

        /**
         * Cart subtotaal bedrag
         * @param {Object} state - Store state
         * @returns {number} Subtotaal in euros
         */
        subtotal: (state) => state.totals.subtotal || 0,

        /**
         * Cart totaal bedrag inclusief kosten
         * @param {Object} state - Store state
         * @returns {number} Totaal bedrag in euros
         */
        total: (state) => state.totals.total || 0,

        /**
         * Gesorteerde cart items gebaseerd op sortBy en sortDirection
         * @param {Object} state - Store state
         * @returns {Array} Gesorteerde array van cart items
         */
        sortedItems: (state) => {
            if (!state.items || state.items.length === 0) return [];
            
            return [...state.items].sort((a, b) => {
                let compareA, compareB;
                
                // Bepaal vergelijking waarden gebaseerd op sort criteria
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

                // Pas sort direction toe
                if (compareA < compareB) return state.sortDirection === 'asc' ? -1 : 1;
                if (compareA > compareB) return state.sortDirection === 'asc' ? 1 : -1;
                return 0;
            });
        },

        /**
         * Controleert of cart items bevat
         * @param {Object} state - Store state
         * @returns {boolean} True als cart niet leeg is
         */
        hasItems: (state) => state.items && state.items.length > 0,

        /**
         * Aantal unieke items in cart
         * @param {Object} state - Store state
         * @returns {number} Aantal verschillende producten
         */
        itemsCount: (state) => state.items ? state.items.length : 0,

        /**
         * Controleert of cart data stale is (ouder dan 30 seconden)
         * Gebruikt voor performance optimization om onnodige API calls te voorkomen
         * @param {Object} state - Store state
         * @returns {boolean} True als data refresh nodig is
         */
        isStale: (state) => {
            if (!state.lastUpdated) return true;
            return (Date.now() - state.lastUpdated) > 30000;
        },

        /**
         * Controleert of cart klaar is voor checkout proces
         * Valideert items, loading states, en stock availability
         * @param {Object} state - Store state
         * @returns {boolean} True als checkout mogelijk is
         */
        isReadyForCheckout: (state) => {
            return state.hasItems &&
                   !state.isLoading &&
                   !state.orderProcessing &&
                   state.items.every(item => item.stock_quantity >= item.quantity);
        }
    },

    // ========== ACTIONS ==========
    actions: {
        // ========== CART LOADING EN SYNCHRONISATIE ==========

        /**
         * Laadt cart data van server met caching optimisatie
         * @param {boolean} force - Force refresh ook als data nog fresh is
         */
        async loadCart(force = false) {
            // Voorkom onnodige reloads als data nog fresh is
            if (!force && !this.isStale && this.items.length > 0) {
                return;
            }

            try {
                this.isLoading = true;
                this.lastError = null;

                const response = await axios.get('/cart');

                // Process items voor consistent data format en image URLs
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
                
                // Reset naar lege state bij error
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

        /**
         * Verwerkt cart items voor consistente data format
         * Zorgt voor proper image URLs en data type conversies
         * @param {Array} items - Raw cart items van server
         * @returns {Array} Processed cart items
         */
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

        /**
         * Genereert proper image URL met fallback handling
         * @param {string} imagePath - Raw image path van database
         * @returns {string|null} Volledige image URL of null
         */
        getImageUrl(imagePath) {
            if (!imagePath) {
                return null; // Return null in plaats van placeholder om 404s te voorkomen
            }

            // Als het al een volledige URL is, return as-is
            if (imagePath.startsWith('http') || imagePath.startsWith('/storage/') || imagePath.startsWith('/images/')) {
                return imagePath;
            }

            // Assume het is een storage path
            return `/storage/${imagePath}`;
        },

        // ========== OPTIMISTIC UPDATES MET ANTI-SPAM ==========

        /**
         * Optimistic quantity update met debouncing en anti-spam beveiliging
         * @param {Object} product - Product object met product_id
         * @param {number} newQuantity - Nieuwe quantity waarde
         * @returns {Object} Success/error response object
         */
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

                // Remove item als quantity 0 is
                if (quantity === 0) {
                    return await this.removeFromCartOptimistic(product);
                }

                const productId = product.product_id;
                const now = Date.now();

                // DEBOUNCE: Voorkom spam clicking
                const lastUpdate = this.lastUpdateTime.get(productId) || 0;
                if (now - lastUpdate < 200) { // 200ms debounce
                    console.log('Debouncing rapid clicks for product:', productId);
                    
                    // Queue de update in plaats van negeren
                    this.updateQueue.set(productId, {
                        product,
                        quantity,
                        timestamp: now
                    });

                    // Process queue na delay
                    setTimeout(() => this.processQueuedUpdate(productId), 250);
                    return { success: true, message: 'Update in behandeling...' };
                }

                this.lastUpdateTime.set(productId, now);

                // Check of er al een pending update is voor dit product
                if (this.pendingUpdates.has(productId)) {
                    console.log('Update already pending for product:', productId);
                    
                    // Update de queued request
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

        /**
         * Verwerkt queued updates na debounce periode
         * @param {number} productId - Product ID om te updaten
         */
        async processQueuedUpdate(productId) {
            const queuedUpdate = this.updateQueue.get(productId);
            if (!queuedUpdate) return;

            // Remove van queue
            this.updateQueue.delete(productId);

            // Alleen process als niet currently updating
            if (!this.pendingUpdates.has(productId)) {
                await this.executeQuantityUpdate(queuedUpdate.product, queuedUpdate.quantity);
            }
        },

        /**
         * Voert daadwerkelijke quantity update uit (optimistic + API sync)
         * @param {Object} product - Product object
         * @param {number} quantity - Nieuwe quantity
         * @returns {Object} Success/error response
         */
        async executeQuantityUpdate(product, quantity) {
            const productId = product.product_id;

            try {
                // Mark als pending
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

                // 2. Sync met server
                const response = await axios.patch(`/cart/${productId}`, {
                    quantity: quantity
                });

                // Update met server totals (meer accuraat)
                if (response.data.totals) {
                    this.totals = response.data.totals;
                }

                // Optional: Refresh cart data af en toe voor integrity
                if (Math.random() < 0.05) { // 5% kans
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

        // ========== ITEM REMOVAL MET OPTIMISTIC UPDATES ==========

        /**
         * Verwijdert item uit cart met optimistic update
         * @param {Object} product - Product object om te verwijderen
         * @returns {Object} Success/error response
         */
        async removeFromCartOptimistic(product) {
            try {
                this.lastError = null;

                if (!product || !product.product_id) {
                    throw new Error('Invalid product data for removal');
                }

                const productId = product.product_id;
                this.pendingUpdates.add(productId);

                // 1. EERST: Remove van UI optimistically
                const itemIndex = this.items.findIndex(item => item.product_id === productId);
                if (itemIndex !== -1) {
                    const removedItem = this.items[itemIndex];

                    // Remove item van array
                    this.items.splice(itemIndex, 1);

                    // Update totals
                    this.totals.total_items -= removedItem.quantity;
                    this.totals.subtotal -= (removedItem.price * removedItem.quantity);
                    this.totals.total -= (removedItem.price * removedItem.quantity);
                    this.totals.items_count = this.items.length;
                }

                // 2. DAN: Sync met server
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
                    
                    // Revert door cart te herladen
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

        // ========== QUANTITY INCREMENT/DECREMENT MET ANTI-SPAM ==========

        /**
         * Verhoogt quantity met 1 (met anti-spam beveiliging)
         * @param {Object} product - Product object om te verhogen
         * @returns {Object} Success/error response
         */
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

            // Voorkom rapid fire clicks
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

        /**
         * Verlaagt quantity met 1 (met anti-spam beveiliging)
         * @param {Object} product - Product object om te verlagen
         * @returns {Object} Success/error response
         */
        async decrementQuantity(product) {
            if (!product) {
                return { success: false, message: 'Invalid product' };
            }

            const currentQuantity = parseInt(product.quantity) || 0;

            // Voorkom rapid fire clicks
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

        // ========== PRODUCT TOEVOEGEN ==========

        /**
         * Voegt nieuw product toe aan cart
         * @param {Object} product - Product object om toe te voegen
         * @returns {Object} Success/error response
         */
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

                const requestedQuantity = parseInt(product.quantity) || 1;

                const response = await axios.post('/cart/add', {
                    product_id: parseInt(product.id),
                    quantity: requestedQuantity
                });

                if (response.data.totals) {
                    this.totals = response.data.totals;
                }

                // Alleen refresh cart voor add operations om consistency te waarborgen
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

        // ========== CART CLEARING ==========

        /**
         * Leegt volledige winkelwagen
         * @returns {Object} Success/error response
         */
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

        // ========== ORDER INTEGRATIE METHODEN ==========

        /**
         * Bereidt cart voor op checkout proces met validatie
         * @returns {Object} Success/error response
         */
        async prepareForCheckout() {
            try {
                // Valideer cart voor checkout
                await this.validateCart();

                if (!this.hasItems) {
                    throw new Error('Winkelwagen is leeg');
                }

                // Check stock voor alle items
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

        /**
         * Markeert order als in verwerking
         * @param {number|null} orderId - Optional order ID
         */
        async markOrderProcessing(orderId = null) {
            this.orderProcessing = true;
            this.lastOrderId = orderId;
            this.orderSuccess = false;
        },

        /**
         * Markeert order als succesvol voltooid
         * @param {number} orderId - ID van succesvolle order
         */
        async markOrderSuccess(orderId) {
            this.orderProcessing = false;
            this.orderSuccess = true;
            this.lastOrderId = orderId;

            // Clear cart na succesvolle order
            await this.clearCartAfterOrder();
        },

        /**
         * Markeert order als gefaald
         */
        async markOrderFailed() {
            this.orderProcessing = false;
            this.orderSuccess = false;
            // Geen cart clearing bij failure zodat user kan retry
        },

        /**
         * Leegt cart na succesvolle order placement
         * @returns {Object} Success/error response
         */
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

                // Clear alle pending updates
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

        /**
         * Reset order state naar default
         */
        resetOrderState() {
            this.orderProcessing = false;
            this.orderSuccess = false;
            this.lastOrderId = null;
        },

        // ========== SORTING FUNCTIONALITEIT ==========

        /**
         * Stelt sort criteria in met direction toggle
         * @param {string} sortBy - Sort veld: 'name', 'price', 'quantity'
         */
        setSorting(sortBy) {
            if (!['name', 'price', 'quantity'].includes(sortBy)) {
                console.warn('Invalid sort option:', sortBy);
                return;
            }

            if (this.sortBy === sortBy) {
                // Toggle direction als zelfde veld
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                // Nieuwe sort veld, start met asc
                this.sortBy = sortBy;
                this.sortDirection = 'asc';
            }
        },

        /**
         * Toggle sort direction
         */
        toggleSortDirection() {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        },

        // ========== REFRESH EN VALIDATIE ==========

        /**
         * Enhanced methode om cart data te refreshen van server
         * @returns {Promise} Promise van loadCart operatie
         */
        async refreshCart() {
            return await this.loadCart(true);
        },

        /**
         * Valideert cart integrity (nuttig voor checkout)
         * @returns {Object} Validatie resultaat met cart data
         */
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

        // ========== UTILITY METHODEN ==========

        /**
         * Controleert of product toegevoegd kan worden aan cart
         * @param {Object} product - Product object om te controleren
         * @param {number} requestedQuantity - Gewenste quantity (default: 1)
         * @returns {Object} Object met canAdd boolean en reason
         */
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

        /**
         * Haalt quantity op voor specifiek product ID
         * @param {number} productId - Product ID om te zoeken
         * @returns {number} Quantity in cart of 0
         */
        getProductQuantity(productId) {
            const item = this.items.find(item =>
                item.product_id === parseInt(productId) || item.id === parseInt(productId)
            );
            return item ? parseInt(item.quantity) : 0;
        },

        /**
         * Controleert of product in cart aanwezig is
         * @param {number} productId - Product ID om te controleren
         * @returns {boolean} True als product in cart zit
         */
        isProductInCart(productId) {
            return this.items.some(item =>
                item.product_id === parseInt(productId) || item.id === parseInt(productId)
            );
        },

        /**
         * Haalt cart summary op voor display doeleinden
         * @returns {Object} Cart summary object met alle relevante data
         */
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