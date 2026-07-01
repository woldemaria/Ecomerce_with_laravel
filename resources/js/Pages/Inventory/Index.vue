<template>
  <AuthenticatedLayout :breadcrumbs="[{ label: 'Inventory' }]">
    <Head title="Inventory Management" />

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-xl font-bold text-gray-900">Inventory Management</h1>
        <p class="text-sm text-gray-500 mt-0.5">Monitor and manage your stock levels</p>
      </div>
      <Link :href="route('inventory.adjust')" class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4"/></svg>
        Adjust Inventory
      </Link>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
      <div class="bg-white rounded-xl border border-green-200 p-5 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
          <p class="text-2xl font-bold text-gray-900">{{ summary.in_stock }}</p>
          <p class="text-sm text-gray-500">In Stock</p>
        </div>
      </div>
      <div class="bg-white rounded-xl border border-orange-200 p-5 flex items-center gap-4">
        <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div>
          <p class="text-2xl font-bold text-gray-900">{{ summary.low_stock }}</p>
          <p class="text-sm text-gray-500">Low Stock</p>
        </div>
      </div>
      <div class="bg-white rounded-xl border border-red-200 p-5 flex items-center gap-4">
        <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </div>
        <div>
          <p class="text-2xl font-bold text-gray-900">{{ summary.out_of_stock }}</p>
          <p class="text-sm text-gray-500">Out of Stock</p>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
      <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
          <input v-model="search" type="text" placeholder="Search products..." class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20" @keyup.enter="applyFilters"/>
          <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <select v-model="stockFilter" @change="applyFilters" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20">
          <option value="">All Stock Levels</option>
          <option value="in_stock">In Stock</option>
          <option value="low_stock">Low Stock</option>
          <option value="out_of_stock">Out of Stock</option>
        </select>
        <button @click="clearFilters" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-500 hover:bg-gray-50">Clear</button>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Product</th>
              <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">SKU</th>
              <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Current Qty</th>
              <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock Status</th>
              <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Stock Level</th>
              <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Last Updated</th>
              <th class="px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="!products.data?.length">
              <td colspan="7" class="text-center py-12 text-gray-400 text-sm">No products found</td>
            </tr>
            <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50">
              <td class="px-4 py-3">
                <p class="font-medium text-gray-900">{{ product.name }}</p>
                <p class="text-xs text-gray-400">{{ product.category }}</p>
              </td>
              <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ product.sku }}</td>
              <td class="px-4 py-3">
                <span :class="['text-base font-bold', product.quantity === 0 ? 'text-red-600' : product.quantity <= product.low_stock_threshold ? 'text-orange-600' : 'text-gray-900']">
                  {{ product.quantity }}
                </span>
                <span class="text-xs text-gray-400 ml-1">units</span>
              </td>
              <td class="px-4 py-3">
                <StatusBadge :status="product.stock_status_label" />
              </td>
              <td class="px-4 py-3 w-40">
                <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                  <div
                    :class="['h-full rounded-full', product.quantity === 0 ? 'bg-red-500' : product.quantity <= product.low_stock_threshold ? 'bg-orange-500' : 'bg-green-500']"
                    :style="{ width: Math.min(100, (product.quantity / Math.max(product.low_stock_threshold * 3, 1)) * 100) + '%' }"
                  />
                </div>
                <p class="text-xs text-gray-400 mt-1">min: {{ product.low_stock_threshold }}</p>
              </td>
              <td class="px-4 py-3 text-xs text-gray-500">{{ new Date(product.updated_at).toLocaleDateString() }}</td>
              <td class="px-4 py-3">
                <Link :href="route('inventory.adjust') + '?product_id=' + product.id" class="px-3 py-1.5 text-xs font-medium text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50 transition">
                  Adjust
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="px-4 border-t border-gray-100">
        <Pagination :from="products.from" :to="products.to" :total="products.total" :prev-page-url="products.prev_page_url" :next-page-url="products.next_page_url" :links="products.links"/>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
  products: Object,
  summary: Object,
  filters: Object,
})

const search = ref(props.filters?.search || '')
const stockFilter = ref(props.filters?.stock_status || '')

function applyFilters() {
  router.get(route('inventory.index'), {
    search: search.value || undefined,
    stock_status: stockFilter.value || undefined,
  }, { preserveState: true, replace: true })
}

function clearFilters() {
  search.value = ''
  stockFilter.value = ''
  applyFilters()
}
</script>
