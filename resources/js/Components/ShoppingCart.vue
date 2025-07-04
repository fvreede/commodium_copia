<!-- Mobile-Friendly ShoppingCart.vue -->
<template>
  <TransitionRoot as="template" :show="isOpen">
    <Dialog as="div" class="relative z-[1100] sm:z-[200]" @close="$emit('close')">
      <TransitionChild 
        as="template" 
        enter="ease-in-out duration-500" 
        enter-from="opacity-0" 
        enter-to="opacity-100" 
        leave="ease-in-out duration-500" 
        leave-from="opacity-100" 
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
          <!-- Mobile: Full screen, Desktop: Right side panel -->
          <div class="pointer-events-none fixed inset-0 sm:top-16 sm:bottom-0 sm:right-0 flex justify-end sm:max-w-full sm:pl-10">
            <TransitionChild 
              as="template" 
              enter="transform transition ease-in-out duration-500 sm:duration-700" 
              enter-from="translate-x-full" 
              enter-to="translate-x-0" 
              leave="transform transition ease-in-out duration-500 sm:duration-700" 
              leave-from="translate-x-0" 
              leave-to="translate-x-full"
            >
              <DialogPanel class="pointer-events-auto w-screen sm:max-w-md">
                <div class="flex h-full flex-col bg-white shadow-xl">
                  <!-- Header - Mobile optimized -->
                  <div class="flex-shrink-0 border-b border-gray-200 px-4 py-4 sm:px-6 sm:py-6">
                    <div class="flex items-center justify-between">
                      <DialogTitle class="text-lg sm:text-xl font-semibold text-gray-900">
                        Winkelwagen
                        <span v-if="cartStore.totalItems > 0" class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                          {{ cartStore.totalItems }}
                        </span>
                      </DialogTitle>

                      <button 
                        type="button" 
                        class="relative -m-2 p-2 text-gray-400 hover:text-gray-500 transition-colors" 
                        @click="$emit('close')"
                      >
                        <span class="absolute -inset-0.5" />
                        <span class="sr-only">Sluit winkelwagen</span>
                        <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                      </button>
                    </div>

                    <!-- Sort controls -->
                    <div v-if="cartStore.sortedItems.length > 0" class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-2 sm:space-y-0">
                      <span class="text-sm font-medium text-gray-700">Sorteer op:</span>
                      <div class="flex items-center space-x-2">
                        <select 
                          v-model="cartStore.sortBy"
                          class="flex-1 sm:flex-none text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                          @change="cartStore.setSorting($event.target.value)"
                        >
                          <option value="name">Naam</option>
                          <option value="price">Prijs</option>
                          <option value="quantity">Aantal</option>
                        </select>
                        <button 
                          @click="cartStore.toggleSortDirection()"
                          class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded transition-colors"
                          :title="sortDirectionLabel"
                        >
                          <ArrowUpIcon v-if="cartStore.sortDirection === 'asc'" class="h-4 w-4" />
                          <ArrowDownIcon v-else class="h-4 w-4" />
                        </button>
                      </div>
                    </div>
                  </div>

                  <!-- Content -->
                  <div class="flex-1 overflow-y-auto">
                    <!-- Loading state -->
                    <div v-if="cartStore.isLoading" class="flex items-center justify-center py-12">
                      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                    </div>

                    <!-- Empty cart message -->
                    <div v-else-if="cartStore.sortedItems.length === 0" class="flex flex-col items-center justify-center py-16 px-4">
                      <ShoppingCartIcon class="h-16 w-16 text-gray-300 mb-4" />
                      <h3 class="text-lg font-medium text-gray-900 mb-2">Uw winkelwagen is leeg</h3>
                      <p class="text-sm text-gray-500 text-center mb-6">
                        Voeg producten toe aan uw winkelwagen om verder te gaan met bestellen.
                      </p>
                      <PrimaryButton 
                        class="px-4 py-2 text-sm font-medium"
                        @click="$emit('close')"
                      >
                        Begin met winkelen
                        <ArrowRightIcon class="ml-2 h-4 w-4" />
                      </PrimaryButton>
                    </div>

                    <!-- Cart items - Mobile optimized layout -->
                    <div v-else class="px-4 py-4 sm:px-6 sm:py-6">
                      <ul role="list" ref="cartList" class="space-y-4 sm:divide-y sm:divide-gray-200 sm:space-y-0">
                        <li 
                          v-for="item in cartStore.sortedItems" 
                          :key="item.id" 
                          class="bg-white border border-gray-200 rounded-xl p-4 sm:border-0 sm:rounded-none sm:p-0 sm:py-6 hover:bg-gray-50 transition-all duration-200 shadow-sm hover:shadow-md sm:shadow-none"
                        >
                          <!-- Mobile-first layout: Image left, content right -->
                          <div class="flex gap-4">
                            <!-- Product image - Fixed width for consistency -->
                            <div class="flex-shrink-0">
                              <div class="h-20 w-20 sm:h-24 sm:w-24 overflow-hidden rounded-lg border border-gray-200 bg-gray-50 relative group">
                                <img 
                                  v-if="getImageUrl(item.image_path) && !hasImageError(item.product_id)"
                                  :src="getImageUrl(item.image_path)" 
                                  :alt="item.name" 
                                  class="h-full w-full object-cover object-center transition-transform duration-200 group-hover:scale-105" 
                                  @error="() => handleImageError(item.product_id)"
                                />
                                <!-- Fallback when no image or image error -->
                                <div 
                                  v-if="!getImageUrl(item.image_path) || hasImageError(item.product_id)" 
                                  class="h-full w-full flex items-center justify-center bg-gray-100"
                                >
                                  <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                  </svg>
                                </div>
                                
                                <!-- Stock indicator overlay -->
                                <div v-if="item.stock_quantity === 0" class="absolute inset-0 bg-red-500 bg-opacity-20 flex items-center justify-center rounded-lg">
                                  <span class="text-xs font-semibold text-red-700 bg-white px-2 py-1 rounded-full shadow-sm">
                                    Uitverkocht
                                  </span>
                                </div>
                              </div>
                            </div>

                            <!-- Product information - Takes remaining space -->
                            <div class="flex-1 min-w-0 flex flex-col justify-between">
                              <!-- Top section: Name and prices -->
                              <div class="space-y-1">
                                <!-- Product name -->
                                <h3 class="text-sm font-medium text-gray-900 line-clamp-2 leading-tight">
                                  {{ item.name }}
                                </h3>
                                
                                <!-- Price information - Mobile optimized -->
                                <div class="flex items-center justify-between">
                                  <div class="flex items-center gap-2 text-sm">
                                    <span class="text-gray-600">€{{ formatPrice(item.price) }}</span>
                                    <span class="text-gray-400">×</span>
                                    <span class="font-medium text-gray-900">{{ item.quantity }}</span>
                                  </div>
                                  <div class="text-right">
                                    <div class="text-base font-semibold text-gray-900">
                                      €{{ formatPrice(item.price * item.quantity) }}
                                    </div>
                                  </div>
                                </div>
                                
                                <!-- Stock warnings -->
                                <div v-if="item.stock_quantity <= 5 && item.stock_quantity > 0" class="flex items-center gap-1">
                                  <div class="h-2 w-2 bg-orange-400 rounded-full animate-pulse"></div>
                                  <span class="text-xs text-orange-600 font-medium">
                                    Nog {{ item.stock_quantity }} op voorraad
                                  </span>
                                </div>
                              </div>

                              <!-- Bottom section: Controls -->
                              <div class="flex items-center justify-between mt-3 pt-2 border-t border-gray-100">
                                <!-- Quantity controls - Compact design -->
                                <div class="flex items-center bg-gray-50 rounded-lg border border-gray-200 overflow-hidden">
                                  <button 
                                    @click="decrementQuantity(item)"
                                    :disabled="updatingItems.has(item.product_id)"
                                    class="flex items-center justify-center w-9 h-9 text-gray-600 hover:bg-gray-200 active:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed transition-colors touch-manipulation"
                                    :title="item.quantity === 1 ? 'Verwijder item' : 'Verminder aantal'"
                                  >
                                    <MinusIcon class="h-4 w-4" />
                                  </button>
                                  
                                  <div class="flex items-center justify-center min-w-[2.5rem] h-9 px-2 text-sm font-medium text-gray-900 bg-white border-x border-gray-200">
                                    <span v-if="updatingItems.has(item.product_id)" class="animate-pulse">•••</span>
                                    <span v-else>{{ item.quantity }}</span>
                                  </div>
                                  
                                  <button 
                                    @click="incrementQuantity(item)"
                                    :disabled="updatingItems.has(item.product_id) || item.quantity >= item.stock_quantity"
                                    class="flex items-center justify-center w-9 h-9 text-gray-600 hover:bg-gray-200 active:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed transition-colors touch-manipulation"
                                    :title="item.quantity >= item.stock_quantity ? 'Maximum voorraad bereikt' : 'Verhoog aantal'"
                                  >
                                    <PlusIcon class="h-4 w-4" />
                                  </button>
                                </div>

                                <!-- Remove button - Prominent but not overwhelming -->
                                <button 
                                  type="button" 
                                  @click="removeItem(item)"
                                  :disabled="updatingItems.has(item.product_id)"
                                  class="flex items-center justify-center w-9 h-9 text-red-500 hover:text-red-600 hover:bg-red-50 active:bg-red-100 disabled:opacity-50 disabled:cursor-not-allowed rounded-lg transition-colors touch-manipulation ml-3"
                                  title="Verwijder uit winkelwagen"
                                >
                                  <TrashIcon class="h-5 w-5" />
                                </button>
                              </div>
                            </div>
                          </div>
                        </li>
                      </ul>
                    </div>
                  </div>

                  <!-- Footer with totals and checkout -->
                  <div v-if="cartStore.sortedItems.length > 0" class="flex-shrink-0 border-t border-gray-200 px-4 py-4 sm:px-6 sm:py-6 bg-gray-50">
                    <!-- Totals -->
                    <div class="space-y-2 mb-6">
                      <div class="flex justify-between text-base sm:text-sm text-gray-600">
                        <span>Subtotaal ({{ cartStore.totalItems }} items)</span>
                        <span>€{{ formatPrice(cartStore.subtotal) }}</span>
                      </div>
                      <div class="flex justify-between text-xl sm:text-lg font-semibold text-gray-900 pt-2 border-t border-gray-200">
                        <span>Totaal</span>
                        <span>€{{ formatPrice(cartStore.total) }}</span>
                      </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="space-y-4">
                      <PrimaryButton 
                        :disabled="cartStore.sortedItems.length === 0 || hasOutOfStockItems"
                        class="w-full justify-center px-6 py-4 sm:py-3 text-lg sm:text-base font-medium disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
                        @click="goToCheckout"
                      >
                        <span v-if="hasOutOfStockItems">Controleer voorraad</span>
                        <span v-else>Bestelling afronden</span>
                      </PrimaryButton>
                      
                      <SecondaryButton 
                        class="w-full justify-center text-base sm:text-sm font-medium py-2"
                        @click="$emit('close')"
                      >
                        Verder winkelen
                        <ArrowRightIcon class="ml-1 h-4 w-4 inline" />
                      </SecondaryButton>
                    </div>

                    <!-- Clear cart option -->
                    <div v-if="cartStore.sortedItems.length > 0" class="mt-4 pt-4 border-t border-gray-200 text-center">
                      <button 
                        type="button" 
                        @click="clearCart"
                        :disabled="clearingCart"
                        class="text-sm text-gray-500 hover:text-red-600 disabled:opacity-50 transition-colors"
                      >
                        {{ clearingCart ? 'Winkelwagen legen...' : 'Winkelwagen legen' }}
                      </button>
                    </div>
                  </div>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import PrimaryButton from './PrimaryButton.vue';
import SecondaryButton from './SecondaryButton.vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { 
  XMarkIcon, 
  TrashIcon, 
  PlusIcon, 
  MinusIcon, 
  ShoppingCartIcon, 
  ArrowUpIcon, 
  ArrowDownIcon,
  ArrowRightIcon
} from '@heroicons/vue/24/outline';
import { useCartStore } from '@/Stores/cart';
import { router } from '@inertiajs/vue3';

// Props
const props = defineProps({
  isOpen: {
    type: Boolean,
    required: true
  }
});

// Emits
defineEmits(['close']);

// Initialize the cart store
const cartStore = useCartStore();

// Local state
const updatingItems = ref(new Set());
const clearingCart = ref(false);
const imageErrors = ref(new Set()); // Track image errors

// Load cart when component mounts or when opened
onMounted(async () => {
  await cartStore.loadCart();
});

// Watch for when cart opens to refresh data
watch(() => props.isOpen, async (isOpen) => {
  if (isOpen) {
    await cartStore.loadCart(true); // Force refresh when opening
    imageErrors.value.clear(); // Clear image errors when reopening
  }
});

// Computed properties
const sortDirectionLabel = computed(() => {
  return cartStore.sortDirection === 'asc' ? 'Oplopend sorteren' : 'Aflopend sorteren';
});

const hasOutOfStockItems = computed(() => {
  return cartStore.sortedItems.some(item => item.stock_quantity === 0 || item.quantity > item.stock_quantity);
});

// Methods
const formatPrice = (price) => {
  return Number(price).toFixed(2);
};

const getImageUrl = (imagePath) => {
  if (!imagePath) return null;
  
  // If it's already a full URL, return as is
  if (imagePath.startsWith('http') || imagePath.startsWith('/storage/') || imagePath.startsWith('/images/')) {
    return imagePath;
  }
  
  // Assume it's a storage path
  return `/storage/${imagePath}`;
};

const handleImageError = (productId) => {
  // Add product ID to the set of items with image errors
  imageErrors.value.add(productId);
};

const hasImageError = (productId) => {
  return imageErrors.value.has(productId);
};

const goToCheckout = () => {
  router.visit('/checkout');
};

const incrementQuantity = async (item) => {
  if (item.quantity >= item.stock_quantity) return;
  
  updatingItems.value.add(item.product_id);
  try {
    const result = await cartStore.incrementQuantity(item);
    if (!result.success) {
      console.error('Failed to increment quantity:', result.message);
    }
  } finally {
    updatingItems.value.delete(item.product_id);
  }
};

const decrementQuantity = async (item) => {
  updatingItems.value.add(item.product_id);
  try {
    const result = await cartStore.decrementQuantity(item);
    if (!result.success) {
      console.error('Failed to decrement quantity:', result.message);
      // Could show a toast notification here
    }
  } finally {
    updatingItems.value.delete(item.product_id);
  }
};

const removeItem = async (item) => {
  updatingItems.value.add(item.product_id);
  try {
    const result = await cartStore.removeFromCartOptimistic(item);
    if (!result.success) {
      console.error('Failed to remove item:', result.message);
    }
  } finally {
    updatingItems.value.delete(item.product_id);
  }
};

const clearCart = async () => {
  if (!confirm('Weet u zeker dat u de gehele winkelwagen wilt legen?')) {
    return;
  }
  
  clearingCart.value = true;
  try {
    const result = await cartStore.clearCart();
    if (!result.success) {
      console.error('Failed to clear cart:', result.message);
    }
  } finally {
    clearingCart.value = false;
  }
};

const cartList = ref(null);
let lastScrollTop = 0;

watch(() => cartStore.sortedItems.map(i => i.quantity), async () => {
  if (cartList.value) {
    lastScrollTop = cartList.value.scrollTop;
  }

  await nextTick();

  if (cartList.value) {
    cartList.value.scrollTop = lastScrollTop;
  }
});
</script>

<style scoped>
/* Line clamp utility for product names */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2; /* Standard property for compatibility */
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>