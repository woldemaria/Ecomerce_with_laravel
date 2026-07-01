<template>
  <AuthenticatedLayout :breadcrumbs="[{ label: 'Inventory', href: route('inventory.index') }, { label: 'Adjust Stock' }]">
    <Head title="Adjust Inventory" />

    <div class="max-w-3xl mx-auto pb-12">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Adjust Inventory</h1>
        <p class="text-sm text-gray-500 mt-1">Update stock levels, record damages, or manage returns manually.</p>
      </div>

      <!-- Main Form -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <form @submit.prevent="submit" class="p-6 sm:p-8 space-y-8">
          
          <!-- Product Selection Section -->
          <div class="space-y-4">
            <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">1. Select Product</h2>
            
            <div class="relative">
              <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
              <select 
                v-model="form.product_id" 
                class="block w-full pl-3 pr-10 py-3 text-base border-gray-200 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-xl transition-colors bg-gray-50 hover:bg-white"
                @change="updateSelectedProduct"
              >
                <option value="" disabled>Select a product to adjust</option>
                <option v-for="product in products" :key="product.id" :value="product.id">
                  {{ product.name }} (SKU: {{ product.sku }}) - {{ product.quantity }} in stock
                </option>
              </select>
              <div v-if="form.errors.product_id" class="mt-1 text-sm text-red-600">{{ form.errors.product_id }}</div>
            </div>

            <!-- Selected Product Quick Stats -->
            <transition name="fade">
              <div v-if="activeProduct" class="mt-4 p-4 bg-blue-50/50 rounded-xl border border-blue-100 flex items-center justify-between">
                <div>
                  <p class="text-sm font-medium text-blue-900">{{ activeProduct.name }}</p>
                  <p class="text-xs text-blue-700 mt-0.5">Current Stock: <span class="font-bold">{{ activeProduct.quantity }}</span></p>
                </div>
                <div :class="['px-2.5 py-1 text-xs font-semibold rounded-full', stockStatusColor]">
                  {{ stockStatusText }}
                </div>
              </div>
            </transition>
          </div>

          <hr class="border-gray-100" />

          <!-- Adjustment Details Section -->
          <div class="space-y-6">
            <h2 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">2. Adjustment Details</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <!-- Reason -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                <select 
                  v-model="form.reason" 
                  class="block w-full pl-3 pr-10 py-3 text-base border-gray-200 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-xl transition-colors bg-gray-50 hover:bg-white"
                >
                  <option value="" disabled>Select reason</option>
                  <option value="restock">Restock (Received new inventory)</option>
                  <option value="sale">Manual Sale (Not via platform)</option>
                  <option value="damage">Damaged Goods</option>
                  <option value="return">Customer Return</option>
                  <option value="correction">Inventory Correction</option>
                  <option value="other">Other</option>
                </select>
                <div v-if="form.errors.reason" class="mt-1 text-sm text-red-600">{{ form.errors.reason }}</div>
              </div>

              <!-- Quantity Change -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity Change</label>
                <div class="flex items-center space-x-3">
                  <button type="button" @click="decrement" class="p-3 border border-gray-200 rounded-xl hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 transition-colors text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                  </button>
                  <div class="relative flex-1">
                    <input 
                      type="number" 
                      v-model="form.quantity_change" 
                      class="block w-full text-center py-3 border-gray-200 focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-xl transition-colors bg-gray-50 hover:bg-white font-mono text-lg font-bold"
                      placeholder="0"
                    />
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                      <span v-if="form.quantity_change > 0" class="text-green-500 text-sm font-bold">+</span>
                    </div>
                  </div>
                  <button type="button" @click="increment" class="p-3 border border-gray-200 rounded-xl hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 transition-colors text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                  </button>
                </div>
                <div v-if="form.errors.quantity_change" class="mt-1 text-sm text-red-600">{{ form.errors.quantity_change }}</div>
                
                <!-- Projected Stock -->
                <p v-if="activeProduct" class="mt-2 text-sm text-gray-500 text-center">
                  New stock level: <span class="font-bold text-gray-900">{{ projectedStock }}</span>
                </p>
              </div>
            </div>

            <!-- Notes -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
              <textarea 
                v-model="form.notes" 
                rows="3" 
                class="block w-full border-gray-200 focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-xl transition-colors bg-gray-50 hover:bg-white resize-none"
                placeholder="Provide additional context for this adjustment..."
              ></textarea>
              <div v-if="form.errors.notes" class="mt-1 text-sm text-red-600">{{ form.errors.notes }}</div>
            </div>
          </div>

          <!-- Actions -->
          <div class="pt-6 border-t border-gray-100 flex items-center justify-end gap-3">
            <Link :href="route('inventory.index')" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
              Cancel
            </Link>
            <button 
              type="submit" 
              :disabled="form.processing || !form.product_id || form.quantity_change === 0"
              class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-xl hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
            >
              <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Save Adjustment
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  products: {
    type: Array,
    required: true
  },
  selectedProduct: {
    type: Object,
    default: null
  }
})

const activeProduct = ref(props.selectedProduct || null)

const form = useForm({
  product_id: props.selectedProduct ? props.selectedProduct.id : '',
  quantity_change: 0,
  reason: '',
  notes: ''
})

// Auto-select reason based on quantity change (simple heuristic)
watch(() => form.quantity_change, (newVal) => {
  if (newVal > 0 && !form.reason) form.reason = 'restock'
  if (newVal < 0 && !form.reason) form.reason = 'damage'
})

function updateSelectedProduct() {
  activeProduct.value = props.products.find(p => p.id === form.product_id) || null
  form.quantity_change = 0 // Reset change when switching product
}

function increment() {
  form.quantity_change++
}

function decrement() {
  form.quantity_change--
}

const projectedStock = computed(() => {
  if (!activeProduct.value) return 0
  return Math.max(0, activeProduct.value.quantity + Number(form.quantity_change))
})

const stockStatusColor = computed(() => {
  if (!activeProduct.value) return ''
  const qty = activeProduct.value.quantity
  if (qty === 0) return 'bg-red-100 text-red-800'
  if (qty <= activeProduct.value.low_stock_threshold) return 'bg-orange-100 text-orange-800'
  return 'bg-green-100 text-green-800'
})

const stockStatusText = computed(() => {
  if (!activeProduct.value) return ''
  const qty = activeProduct.value.quantity
  if (qty === 0) return 'Out of Stock'
  if (qty <= activeProduct.value.low_stock_threshold) return 'Low Stock'
  return 'In Stock'
})

function submit() {
  form.post(route('inventory.adjust.store'), {
    preserveScroll: true,
    onSuccess: () => {
      // Form resets automatically or we redirect back via controller
    }
  })
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
