<template>
  <AuthenticatedLayout :breadcrumbs="[{ label: 'Reports' }, { label: 'Advanced Search' }]">
    <Head title="Advanced Search" />

    <div class="mb-8 flex justify-between items-end">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Advanced Search</h1>
        <p class="text-gray-500 mt-1">Search and filter your entire product catalog.</p>
      </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
      <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
          <input v-model="form.search" type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="Name, desc..." />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
          <input v-model="form.sku" type="text" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" placeholder="SKU" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
          <select v-model="form.category" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
            <option value="">All Categories</option>
            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
          <select v-model="form.status" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500">
            <option value="">All Statuses</option>
            <option value="active">Active</option>
            <option value="draft">Draft</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Min Price</label>
          <input v-model="form.min_price" type="number" step="0.01" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Max Price</label>
          <input v-model="form.max_price" type="number" step="0.01" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Min Quantity</label>
          <input v-model="form.min_qty" type="number" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Max Quantity</label>
          <input v-model="form.max_qty" type="number" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" />
        </div>

        <div class="lg:col-span-4 flex justify-end gap-3 mt-2">
          <button type="button" @click="reset" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Reset</button>
          <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Search</button>
        </div>
      </form>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
          <thead class="bg-gray-50 text-gray-500 border-b border-gray-200">
            <tr>
              <th class="px-6 py-4 font-medium">Product</th>
              <th class="px-6 py-4 font-medium">SKU</th>
              <th class="px-6 py-4 font-medium">Category</th>
              <th class="px-6 py-4 font-medium text-right">Price</th>
              <th class="px-6 py-4 font-medium text-right">Quantity</th>
              <th class="px-6 py-4 font-medium">Status</th>
              <th class="px-6 py-4"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 font-medium text-gray-900">{{ product.name }}</td>
              <td class="px-6 py-4 text-gray-500">{{ product.sku }}</td>
              <td class="px-6 py-4 text-gray-500">{{ product.category }}</td>
              <td class="px-6 py-4 text-right text-gray-900">${{ product.price }}</td>
              <td class="px-6 py-4 text-right font-medium text-gray-900">{{ product.quantity }}</td>
              <td class="px-6 py-4">
                <span :class="{
                  'bg-green-100 text-green-700': product.status === 'active',
                  'bg-gray-100 text-gray-700': product.status === 'draft',
                  'bg-red-100 text-red-700': product.status === 'inactive'
                }" class="px-2.5 py-1 text-xs font-semibold rounded-full capitalize">
                  {{ product.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-right text-sm">
                <Link :href="route('products.show', product.id)" class="text-blue-600 hover:text-blue-900 font-medium">View</Link>
              </td>
            </tr>
            <tr v-if="!products.data.length">
              <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                No products found matching your search criteria.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-if="products.data.length" class="px-6 py-4 border-t border-gray-100">
        <Pagination :links="products.links" />
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
  products: Object,
  categories: Array,
  filters: Object,
})

const form = useForm({
  search: props.filters.search || '',
  category: props.filters.category || '',
  status: props.filters.status || '',
  sku: props.filters.sku || '',
  min_price: props.filters.min_price || '',
  max_price: props.filters.max_price || '',
  min_qty: props.filters.min_qty || '',
  max_qty: props.filters.max_qty || '',
})

const submit = () => {
  form.get(route('reports.search'), {
    preserveState: true,
    preserveScroll: true,
  })
}

const reset = () => {
  form.reset()
  submit()
}
</script>
