<template>
  <AuthenticatedLayout :breadcrumbs="[{ label: 'Products', href: route('products.index') }, { label: product.name }]">
    <Head :title="product.name" />

    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
          <Link :href="route('products.index')" class="p-2 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
          </Link>
          <div>
            <h1 class="text-xl font-bold text-gray-900">{{ product.name }}</h1>
            <p class="text-sm text-gray-500 font-mono">{{ product.sku }}</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <Link :href="route('products.edit', product.id)" class="flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Edit Product
          </Link>
          <button @click="confirmDelete = true" class="flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            Delete
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-4">
          <!-- Summary Card -->
          <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-start justify-between mb-4">
              <h2 class="text-base font-semibold text-gray-900">Product Information</h2>
              <StatusBadge :status="product.status" />
            </div>
            <div class="space-y-4">
              <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Description</p>
                <p class="text-sm text-gray-700 leading-relaxed">{{ product.description || 'No description provided.' }}</p>
              </div>
              <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                <div>
                  <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Category</p>
                  <span class="px-2.5 py-1 bg-gray-100 text-gray-700 rounded-md text-sm">{{ product.category }}</span>
                </div>
                <div>
                  <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">SKU</p>
                  <span class="font-mono text-sm text-gray-900">{{ product.sku }}</span>
                </div>
                <div>
                  <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Added</p>
                  <p class="text-sm text-gray-700">{{ new Date(product.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
                </div>
                <div>
                  <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Last Updated</p>
                  <p class="text-sm text-gray-700">{{ new Date(product.updated_at).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Activity Timeline -->
          <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-5">
              <h2 class="text-base font-semibold text-gray-900">Inventory History</h2>
              <Link :href="route('inventory.adjust') + '?product_id=' + product.id" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                Adjust →
              </Link>
            </div>
            <div v-if="!recentAdjustments?.length" class="text-center py-8 text-gray-400">
              <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
              </svg>
              <p class="text-sm">No inventory adjustments yet</p>
            </div>
            <div v-else class="space-y-4">
              <div v-for="adj in recentAdjustments" :key="adj.id" class="flex gap-4">
                <div class="flex-shrink-0 flex flex-col items-center">
                  <div :class="['w-8 h-8 rounded-full flex items-center justify-center', adj.quantity_change > 0 ? 'bg-green-50' : 'bg-red-50']">
                    <svg class="w-4 h-4" :class="adj.quantity_change > 0 ? 'text-green-600' : 'text-red-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="adj.quantity_change > 0 ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3'"/>
                    </svg>
                  </div>
                  <div class="w-px flex-1 bg-gray-100 my-2"/>
                </div>
                <div class="pb-4">
                  <div class="flex items-center gap-2">
                    <span :class="['text-sm font-semibold', adj.quantity_change > 0 ? 'text-green-600' : 'text-red-600']">
                      {{ adj.quantity_change > 0 ? '+' : '' }}{{ adj.quantity_change }} units
                    </span>
                    <span class="text-xs text-gray-500 capitalize bg-gray-100 px-2 py-0.5 rounded-full">{{ adj.reason }}</span>
                  </div>
                  <p class="text-xs text-gray-500 mt-0.5">{{ adj.quantity_before }} → {{ adj.quantity_after }} · {{ adj.user_name }} · {{ adj.created_at }}</p>
                  <p v-if="adj.notes" class="text-xs text-gray-500 mt-1 italic">{{ adj.notes }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-4">
          <!-- Price Card -->
          <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wider">Pricing</h3>
            <div class="text-center">
              <p class="text-4xl font-bold text-gray-900">${{ Number(product.price).toFixed(2) }}</p>
              <p class="text-sm text-gray-400 mt-1">per unit</p>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-100">
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Inventory Value</span>
                <span class="font-semibold text-gray-900">${{ (product.price * product.quantity).toFixed(2) }}</span>
              </div>
            </div>
          </div>

          <!-- Inventory Card -->
          <div class="bg-white rounded-xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wider">Inventory</h3>
            <div class="text-center mb-4">
              <p :class="['text-4xl font-bold', product.quantity === 0 ? 'text-red-600' : product.quantity <= product.low_stock_threshold ? 'text-orange-600' : 'text-green-600']">
                {{ product.quantity }}
              </p>
              <p class="text-sm text-gray-400 mt-1">units in stock</p>
            </div>
            <div class="space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Stock Status</span>
                <StatusBadge :status="product.stock_status" />
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Low Stock Alert</span>
                <span class="font-medium text-gray-700">{{ product.low_stock_threshold }} units</span>
              </div>
            </div>
            <!-- Stock Level Bar -->
            <div class="mt-4">
              <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                <div
                  :class="['h-full rounded-full transition-all', product.quantity === 0 ? 'bg-red-500' : product.quantity <= product.low_stock_threshold ? 'bg-orange-500' : 'bg-green-500']"
                  :style="{ width: Math.min(100, (product.quantity / (product.low_stock_threshold * 3)) * 100) + '%' }"
                />
              </div>
            </div>
            <Link :href="route('inventory.adjust') + '?product_id=' + product.id" class="mt-4 flex items-center justify-center gap-2 w-full px-4 py-2 border border-blue-200 text-blue-600 rounded-lg text-sm font-medium hover:bg-blue-50 transition">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4"/></svg>
              Adjust Stock
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="confirmDelete" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl shadow-xl p-6 max-w-sm w-full">
        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <h3 class="text-lg font-bold text-gray-900 text-center mb-2">Delete Product?</h3>
        <p class="text-sm text-gray-500 text-center mb-6">This will permanently delete <strong>{{ product.name }}</strong> and all its inventory history. This action cannot be undone.</p>
        <div class="flex gap-3">
          <button @click="confirmDelete = false" class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Cancel</button>
          <Link :href="route('products.destroy', product.id)" method="delete" as="button" class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl text-sm font-semibold hover:bg-red-700 transition text-center">
            Delete
          </Link>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'

const props = defineProps({
  product: Object,
  recentAdjustments: Array,
})

const confirmDelete = ref(false)
</script>
