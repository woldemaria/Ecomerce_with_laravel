<template>
  <AuthenticatedLayout :breadcrumbs="[{ label: 'Products' }]">
    <Head title="Products" />

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
      <div>
        <h1 class="text-xl font-bold text-gray-900">Products</h1>
        <p class="text-sm text-gray-500 mt-0.5">Manage your product catalog</p>
      </div>
      <div class="flex items-center gap-2">
        <a :href="route('products.export')" class="flex items-center gap-2 px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
          </svg>
          Export CSV
        </a>
        <Link :href="route('products.create')" class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Add Product
        </Link>
      </div>
    </div>

    <!-- Filters Bar -->
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
      <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
          <input
            v-model="localFilters.search"
            type="text"
            placeholder="Search by name or SKU..."
            class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400"
            @keyup.enter="applyFilters"
          />
          <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>
        <select v-model="localFilters.category" @change="applyFilters" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20">
          <option value="all">All Categories</option>
          <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
        </select>
        <select v-model="localFilters.status" @change="applyFilters" class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20">
          <option value="all">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
          <option value="draft">Draft</option>
        </select>
        <button @click="clearFilters" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
          Clear
        </button>
      </div>
    </div>

    <!-- Bulk Actions Bar (visible when rows selected) -->
    <div v-if="selectedIds.length > 0" class="flex items-center gap-3 bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 mb-4">
      <span class="text-sm font-medium text-blue-800">{{ selectedIds.length }} selected</span>
      <div class="flex items-center gap-2 ml-auto">
        <button @click="bulkAction('activate')" class="px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700 transition">Activate</button>
        <button @click="bulkAction('deactivate')" class="px-3 py-1.5 bg-gray-600 text-white text-xs font-medium rounded-lg hover:bg-gray-700 transition">Deactivate</button>
        <button @click="bulkAction('delete')" class="px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700 transition">Delete</button>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="w-10 px-4 py-3">
                <input type="checkbox" :checked="allSelected" @change="toggleAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"/>
              </th>
              <th @click="sort('name')" class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 select-none">
                <div class="flex items-center gap-1">Product Name <SortIcon field="name" :current="localFilters.sort_field" :dir="localFilters.sort_dir"/></div>
              </th>
              <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">SKU</th>
              <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
              <th @click="sort('price')" class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 select-none">
                <div class="flex items-center gap-1">Price <SortIcon field="price" :current="localFilters.sort_field" :dir="localFilters.sort_dir"/></div>
              </th>
              <th @click="sort('quantity')" class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider cursor-pointer hover:text-gray-700 select-none">
                <div class="flex items-center gap-1">Qty <SortIcon field="quantity" :current="localFilters.sort_field" :dir="localFilters.sort_dir"/></div>
              </th>
              <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
              <th class="w-24 px-4 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="!products.data?.length">
              <td colspan="8" class="text-center py-16 text-gray-400">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <p class="text-sm font-medium text-gray-500">No products found</p>
                <Link :href="route('products.create')" class="mt-3 inline-flex text-sm text-blue-600 font-medium">Add your first product</Link>
              </td>
            </tr>
            <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50 transition">
              <td class="px-4 py-3">
                <input type="checkbox" :value="product.id" v-model="selectedIds" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"/>
              </td>
              <td class="px-4 py-3">
                <div class="font-medium text-gray-900">{{ product.name }}</div>
                <div class="text-xs text-gray-400 mt-0.5">Added {{ new Date(product.created_at).toLocaleDateString() }}</div>
              </td>
              <td class="px-4 py-3 text-gray-600 font-mono text-xs">{{ product.sku }}</td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 bg-gray-100 text-gray-600 rounded-md text-xs">{{ product.category }}</span>
              </td>
              <td class="px-4 py-3 font-semibold text-gray-900">${{ Number(product.price).toFixed(2) }}</td>
              <td class="px-4 py-3">
                <span :class="[
                  'font-semibold',
                  product.quantity === 0 ? 'text-red-600' : product.quantity <= product.low_stock_threshold ? 'text-orange-600' : 'text-gray-900'
                ]">{{ product.quantity }}</span>
              </td>
              <td class="px-4 py-3">
                <StatusBadge :status="product.status" />
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-1">
                  <Link :href="route('products.show', product.id)" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                  </Link>
                  <Link :href="route('products.edit', product.id)" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 hover:text-blue-600 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </Link>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="px-4 border-t border-gray-100">
        <Pagination
          :from="products.from"
          :to="products.to"
          :total="products.total"
          :prev-page-url="products.prev_page_url"
          :next-page-url="products.next_page_url"
          :links="products.links"
        />
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import Pagination from '@/Components/Pagination.vue'

const SortIcon = {
  props: ['field', 'current', 'dir'],
  template: `<svg class="w-3 h-3" :class="field === current ? 'text-blue-600' : 'text-gray-300'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="field === current && dir === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7'"/>
  </svg>`
}

const props = defineProps({
  products: Object,
  categories: Array,
  filters: Object,
})

const selectedIds = ref([])
const localFilters = ref({
  search: props.filters?.search || '',
  category: props.filters?.category || 'all',
  status: props.filters?.status || 'all',
  sort_field: props.filters?.sort_field || 'created_at',
  sort_dir: props.filters?.sort_dir || 'desc',
})

const allSelected = computed(() =>
  props.products.data?.length > 0 &&
  props.products.data.every(p => selectedIds.value.includes(p.id))
)

function toggleAll() {
  if (allSelected.value) {
    selectedIds.value = []
  } else {
    selectedIds.value = props.products.data.map(p => p.id)
  }
}

function applyFilters() {
  router.get(route('products.index'), {
    search: localFilters.value.search || undefined,
    category: localFilters.value.category !== 'all' ? localFilters.value.category : undefined,
    status: localFilters.value.status !== 'all' ? localFilters.value.status : undefined,
    sort_field: localFilters.value.sort_field,
    sort_dir: localFilters.value.sort_dir,
  }, { preserveState: true, replace: true })
}

function clearFilters() {
  localFilters.value = { search: '', category: 'all', status: 'all', sort_field: 'created_at', sort_dir: 'desc' }
  applyFilters()
}

function sort(field) {
  if (localFilters.value.sort_field === field) {
    localFilters.value.sort_dir = localFilters.value.sort_dir === 'asc' ? 'desc' : 'asc'
  } else {
    localFilters.value.sort_field = field
    localFilters.value.sort_dir = 'asc'
  }
  applyFilters()
}

function bulkAction(action) {
  if (!selectedIds.value.length) return
  if (action === 'delete' && !confirm(`Delete ${selectedIds.value.length} products?`)) return
  router.post(route('products.bulk'), {
    product_ids: selectedIds.value,
    action,
  }, {
    onSuccess: () => { selectedIds.value = [] }
  })
}
</script>
