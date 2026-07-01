<template>
  <AuthenticatedLayout :breadcrumbs="[{ label: 'Inventory', href: route('inventory.index') }, { label: 'Low Stock Alerts' }]">
    <Head title="Low Stock Alerts" />

    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Low Stock Alerts</h1>
      <p class="text-sm text-gray-500 mt-1">Review items that are running low and need to be restocked.</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-8">
      <div class="bg-white rounded-xl border border-red-200 p-5 flex items-center gap-4">
        <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div>
          <p class="text-2xl font-bold text-gray-900">{{ stats.out_of_stock }}</p>
          <p class="text-sm text-gray-500">Out of Stock</p>
        </div>
      </div>
      <div class="bg-white rounded-xl border border-orange-200 p-5 flex items-center gap-4">
        <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <div>
          <p class="text-2xl font-bold text-gray-900">{{ stats.critical }}</p>
          <p class="text-sm text-gray-500">Critical (&le; 5 units)</p>
        </div>
      </div>
      <div class="bg-white rounded-xl border border-yellow-200 p-5 flex items-center gap-4">
        <div class="w-12 h-12 bg-yellow-50 rounded-xl flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        </div>
        <div>
          <p class="text-2xl font-bold text-gray-900">{{ stats.low }}</p>
          <p class="text-sm text-gray-500">Total Low Stock</p>
        </div>
      </div>
      <div class="bg-white rounded-xl border border-blue-200 p-5 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
          <p class="text-2xl font-bold text-gray-900">${{ Number(stats.total_value_at_risk).toLocaleString() }}</p>
          <p class="text-sm text-gray-500">Value at Risk</p>
        </div>
      </div>
    </div>

    <!-- Out of Stock List -->
    <div v-if="outOfStockProducts.length" class="bg-white rounded-xl border border-red-200 overflow-hidden mb-8 shadow-sm">
      <div class="px-6 py-4 bg-red-50 border-b border-red-100 flex items-center justify-between">
        <h2 class="font-bold text-red-900">Out of Stock Needs Restock</h2>
        <span class="px-2.5 py-1 bg-red-100 text-red-800 text-xs font-bold rounded-full">{{ outOfStockProducts.length }} Items</span>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-500 border-b border-gray-100">
            <tr>
              <th class="text-left px-6 py-3 font-semibold uppercase tracking-wider text-xs">Product</th>
              <th class="text-left px-6 py-3 font-semibold uppercase tracking-wider text-xs">SKU</th>
              <th class="text-right px-6 py-3 font-semibold uppercase tracking-wider text-xs">Threshold</th>
              <th class="px-6 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="product in outOfStockProducts" :key="product.id" class="hover:bg-red-50/50 transition-colors">
              <td class="px-6 py-3 font-medium text-gray-900">{{ product.name }}</td>
              <td class="px-6 py-3 font-mono text-xs text-gray-500">{{ product.sku }}</td>
              <td class="px-6 py-3 text-right text-gray-500">{{ product.low_stock_threshold }} units</td>
              <td class="px-6 py-3 text-right">
                <Link :href="route('inventory.adjust') + '?product_id=' + product.id" class="text-blue-600 hover:text-blue-800 font-semibold text-xs">Restock &rarr;</Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Low Stock List -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
      <div class="px-6 py-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
        <h2 class="font-bold text-gray-900">Low Stock Items</h2>
        <span class="px-2.5 py-1 bg-orange-100 text-orange-800 text-xs font-bold rounded-full">{{ lowStockProducts.length }} Items</span>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-white text-gray-500 border-b border-gray-100">
            <tr>
              <th class="text-left px-6 py-3 font-semibold uppercase tracking-wider text-xs">Product</th>
              <th class="text-left px-6 py-3 font-semibold uppercase tracking-wider text-xs">SKU</th>
              <th class="text-center px-6 py-3 font-semibold uppercase tracking-wider text-xs">Current Stock</th>
              <th class="text-left px-6 py-3 font-semibold uppercase tracking-wider text-xs">Health Bar</th>
              <th class="px-6 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 bg-white">
            <tr v-if="!lowStockProducts.length">
              <td colspan="5" class="text-center py-12 text-gray-400">All stock levels are healthy! 🎉</td>
            </tr>
            <tr v-for="product in lowStockProducts" :key="product.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4 font-medium text-gray-900">{{ product.name }}</td>
              <td class="px-6 py-4 font-mono text-xs text-gray-500">{{ product.sku }}</td>
              <td class="px-6 py-4 text-center">
                <span :class="['font-bold text-lg', product.quantity <= 5 ? 'text-red-600' : 'text-orange-500']">{{ product.quantity }}</span>
                <span class="text-xs text-gray-400 ml-1">/ {{ product.low_stock_threshold }}</span>
              </td>
              <td class="px-6 py-4 w-48">
                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                  <div 
                    :class="['h-full rounded-full transition-all duration-500', product.quantity <= 5 ? 'bg-red-500' : 'bg-orange-400']"
                    :style="{ width: Math.min(100, (product.quantity / product.low_stock_threshold) * 100) + '%' }"
                  ></div>
                </div>
              </td>
              <td class="px-6 py-4 text-right">
                <Link :href="route('inventory.adjust') + '?product_id=' + product.id" class="text-blue-600 hover:text-blue-800 font-semibold text-xs">Restock &rarr;</Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineProps({
  lowStockProducts: {
    type: Array,
    required: true
  },
  outOfStockProducts: {
    type: Array,
    required: true
  },
  stats: {
    type: Object,
    required: true
  }
})
</script>
