<template>
  <TransitionRoot as="template" :show="isOpen">
    <Dialog as="div" class="relative z-[200]" @close="$emit('close')">
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
          <div class="pointer-events-none fixed top-16 bottom-0 right-0 flex max-w-full pl-10">
            <TransitionChild 
              as="template" 
              enter="transform transition ease-in-out duration-500 sm:duration-700" 
              enter-from="translate-x-full" 
              enter-to="translate-x-0" 
              leave="transform transition ease-in-out duration-500 sm:duration-700" 
              leave-from="translate-x-0" 
              leave-to="translate-x-full"
            >
              <DialogPanel class="pointer-events-auto w-screen max-w-md">
                <div class="flex h-full flex-col bg-white shadow-xl">
                  <!-- Header -->
                  <div class="flex-shrink-0 border-b border-gray-200 px-4 py-6 sm:px-6">
                    <div class="flex items-center justify-between">
                      <DialogTitle class="text-xl font-semibold text-gray-900">
                        Winkelwagen
                        <span v-if="cartStore.totalItems > 0" class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                          {{ cartStore.totalItems }} {{ cartStore.totalItems === 1 ? 'item' : 'items' }}
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

                    <!-- Sort controls - Only show if there are items -->
                    <div v-if="cartStore.sortedItems.length > 0" class="mt-4 flex items-center justify-between">
                      <span class="text-sm font-medium text-gray-700">Sorteer op:</span>
                      <div class="flex items-center space-x-2">
                        <select 
                          v-model="cartStore.sortBy"
                          class="text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                          @change="cartStore.setSorting($event.target.value)"
                        >
                          <option value="name">Naam</option>
                          <option value="price">Prijs</option>
                          <option value="quantity">Aantal</option>
                        </select>
                        <button 
                          @click="cartStore.toggleSortDirection()"
                          class="p-1.5 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded transition-colors"
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
                      <button 
                        type="button" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-600 bg-indigo-50 hover:bg-indigo-100 transition-colors"
                        @click="$emit('close')"
                      >
                        Begin met winkelen
                        <ArrowRightIcon class="ml-2 h-4 w-4" />
                      </button>
                    </div>

                    <!-- Cart items -->
                    <div v-else class="px-4 py-6 sm:px-6">
                      <ul role="list" class="divide-y divide-gray-200">
                        <li 
                          v-for="item in cartStore.sortedItems" 
                          :key="item.id" 
                          class="flex py-6 group hover:bg-gray-50 transition-colors rounded-lg -mx-2 px-2"
                        >
                          <!-- Product image -->
                          <div class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                            <img 
                              :src="getImageUrl(item.image_path)" 
                              :alt="item.name" 
                              class="h-full w-full object-cover object-center transition-transform group-hover:scale-105" 
                              @error="handleImageError"
                            />
                          </div>

                          <div class="ml-4 flex flex-1 flex-col justify-between">
                            <!-- Product info -->
                            <div class="flex justify-between">
                              <div class="pr-2">
                                <h3 class="text-sm font-medium text-gray-900 line-clamp-2">
                                  {{ item.name }}
                                </h3>
                                <p class="mt-1 text-sm font-semibold text-indigo-600">
                                  €{{ formatPrice(item.price) }}
                                </p>
                                <p v-if="item.stock_quantity <= 5 && item.stock_quantity > 0" 
                                   class="mt-1 text-xs text-orange-600 font-medium">
                                  Nog {{ item.stock_quantity }} op voorraad
                                </p>
                                <p v-if="item.stock_quantity === 0" 
                                   class="mt-1 text-xs text-red-600 font-medium">
                                  Niet op voorraad
                                </p>
                              </div>
                              
                              <!-- Item subtotal -->
                              <div class="text-right flex-shrink-0">
                                <p class="text-sm font-semibold text-gray-900">
                                  €{{ formatPrice(item.price * item.quantity) }}
                                </p>
                              </div>
                            </div>

                            <!-- Quantity controls and remove button -->
                            <div class="flex items-center justify-between mt-3">
                              <div class="flex items-center">
                                <span class="text-xs text-gray-500 mr-2">Aantal:</span>
                                <div class="flex items-center border border-gray-300 rounded-md">
                                  <button 
                                    @click="decrementQuantity(item)"
                                    :disabled="updatingItems.has(item.product_id)"
                                    class="flex items-center justify-center w-8 h-8 text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed rounded-l-md transition-colors"
                                    :title="item.quantity === 1 ? 'Verwijder item' : 'Verminder aantal'"
                                  >
                                    <MinusIcon class="h-3 w-3" />
                                  </button>
                                  
                                  <span class="flex items-center justify-center w-10 h-8 text-sm font-medium bg-gray-50 border-x border-gray-300">
                                    {{ updatingItems.has(item.product_id) ? '...' : item.quantity }}
                                  </span>
                                  
                                  <button 
                                    @click="incrementQuantity(item)"
                                    :disabled="updatingItems.has(item.product_id) || item.quantity >= item.stock_quantity"
                                    class="flex items-center justify-center w-8 h-8 text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed rounded-r-md transition-colors"
                                    :title="item.quantity >= item.stock_quantity ? 'Maximum voorraad bereikt' : 'Verhoog aantal'"
                                  >
                                    <PlusIcon class="h-3 w-3" />
                                  </button>
                                </div>
                              </div>

                              <button 
                                type="button" 
                                @click="removeItem(item)"
                                :disabled="updatingItems.has(item.product_id)"
                                class="flex items-center justify-center w-8 h-8 text-red-600 hover:text-red-500 hover:bg-red-50 disabled:opacity-50 disabled:cursor-not-allowed rounded-md transition-colors"
                                title="Verwijder uit winkelwagen"
                              >
                                <TrashIcon class="h-4 w-4" />
                              </button>
                            </div>
                          </div>
                        </li>
                      </ul>
                    </div>
                  </div>

                  <!-- Footer with totals and checkout -->
                  <div v-if="cartStore.sortedItems.length > 0" class="flex-shrink-0 border-t border-gray-200 px-4 py-6 sm:px-6 bg-gray-50">
                    <!-- Totals -->
                    <div class="space-y-2 mb-6">
                      <div class="flex justify-between text-sm text-gray-600">
                        <span>Subtotaal ({{ cartStore.totalItems }} items)</span>
                        <span>€{{ formatPrice(cartStore.subtotal) }}</span>
                      </div>
                      <div class="flex justify-between text-lg font-semibold text-gray-900 pt-2 border-t border-gray-200">
                        <span>Totaal</span>
                        <span>€{{ formatPrice(cartStore.total) }}</span>
                      </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="space-y-3">
                      <button 
                        type="button" 
                        :disabled="cartStore.sortedItems.length === 0 || hasOutOfStockItems"
                        class="w-full rounded-md border border-transparent bg-indigo-600 px-6 py-3 text-base font-medium text-white shadow-sm hover:bg-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
                      >
                        <span v-if="hasOutOfStockItems">Controleer voorraad</span>
                        <span v-else>Bestelling afronden</span>
                      </button>
                      
                      <button 
                        type="button" 
                        class="w-full text-center text-sm font-medium text-indigo-600 hover:text-indigo-500 transition-colors"
                        @click="$emit('close')"
                      >
                        Verder winkelen
                        <ArrowRightIcon class="ml-1 h-4 w-4 inline" />
                      </button>
                    </div>

                    <!-- Clear cart option -->
                    <div v-if="cartStore.sortedItems.length > 0" class="mt-4 pt-4 border-t border-gray-200">
                      <button 
                        type="button" 
                        @click="clearCart"
                        :disabled="clearingCart"
                        class="text-xs text-gray-500 hover:text-red-600 disabled:opacity-50 transition-colors"
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
import { ref, computed } from 'vue';
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

// Props
defineProps({
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
  if (!imagePath) return '/images/placeholder.jpg';
  return `/storage/${imagePath}`;
};

const handleImageError = (event) => {
  event.target.src = '/images/placeholder.jpg';
};

const incrementQuantity = async (item) => {
  if (item.quantity >= item.stock_quantity) return;
  
  updatingItems.value.add(item.product_id);
  try {
    const result = await cartStore.incrementQuantity(item);
    if (!result.success) {
      console.error('Failed to increment quantity:', result.message);
      // Could show a toast notification here
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
    const result = await cartStore.removeFromCart(item);
    if (!result.success) {
      console.error('Failed to remove item:', result.message);
      // Could show a toast notification here
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
      // Could show a toast notification here
    }
  } finally {
    clearingCart.value = false;
  }
};
</script>