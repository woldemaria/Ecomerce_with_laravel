<template>
  <AuthenticatedLayout :breadcrumbs="[{ label: 'Products', href: route('products.index') }, { label: product.name, href: route('products.show', product.id) }, { label: 'Edit' }]">
    <Head :title="`Edit — ${product.name}`" />

    <div class="max-w-3xl mx-auto">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-xl font-bold text-gray-900">Edit Product</h1>
          <p class="text-sm text-gray-500 mt-0.5">Update the product details and save changes</p>
        </div>
        <Link :href="route('products.show', product.id)" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
          Back to Product
        </Link>
      </div>

      <!-- Unsaved Changes Warning -->
      <div v-if="hasChanges" class="flex items-center gap-3 bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-3 mb-4">
        <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <p class="text-sm text-yellow-800 font-medium">You have unsaved changes</p>
      </div>

      <form @submit.prevent="submit">
        <!-- Basic Information -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4">
          <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-5 flex items-center gap-2">
            <span class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold">1</span>
            Basic Information
          </h2>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Product Name <span class="text-red-500">*</span>
                <span v-if="changed.name" class="ml-2 text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full font-normal">Changed</span>
              </label>
              <input v-model="form.name" type="text" class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20" :class="errors.name ? 'border-red-400' : changed.name ? 'border-blue-400' : 'border-gray-200'"/>
              <p v-if="errors.name" class="mt-1 text-xs text-red-600">{{ errors.name }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Description
                <span v-if="changed.description" class="ml-2 text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full font-normal">Changed</span>
              </label>
              <textarea v-model="form.description" rows="4" class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 resize-none" :class="changed.description ? 'border-blue-400' : 'border-gray-200'"/>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Category <span class="text-red-500">*</span>
                <span v-if="changed.category" class="ml-2 text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full font-normal">Changed</span>
              </label>
              <select v-model="form.category" class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20" :class="changed.category ? 'border-blue-400' : 'border-gray-200'">
                <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
              </select>
              </select>
              <p v-if="errors.category" class="mt-1 text-xs text-red-600">{{ errors.category }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Product Image
                <span v-if="changed.image" class="ml-2 text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full font-normal">Changed</span>
              </label>
              <div v-if="product.image && !changed.image" class="mb-2">
                <img :src="`/storage/${product.image}`" class="w-32 h-32 object-cover rounded-lg border border-gray-200" alt="Product Image" />
              </div>
              <input
                type="file"
                accept="image/*"
                @change="e => form.image = e.target.files[0]"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20"
              />
              <p v-if="errors.image" class="mt-1 text-xs text-red-600">{{ errors.image }}</p>
            </div>
          </div>
        </div>

        <!-- Pricing & SKU -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4">
          <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-5 flex items-center gap-2">
            <span class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold">2</span>
            Pricing & Identification
          </h2>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Price (USD) <span class="text-red-500">*</span>
                <span v-if="changed.price" class="ml-2 text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full font-normal">Changed</span>
              </label>
              <div class="relative">
                <span class="absolute left-3 top-2.5 text-gray-400 text-sm">$</span>
                <input v-model="form.price" type="number" step="0.01" min="0" class="w-full pl-7 pr-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20" :class="changed.price ? 'border-blue-400' : 'border-gray-200'"/>
              </div>
              <p v-if="errors.price" class="mt-1 text-xs text-red-600">{{ errors.price }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">SKU <span class="text-red-500">*</span>
                <span v-if="changed.sku" class="ml-2 text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full font-normal">Changed</span>
              </label>
              <input v-model="form.sku" type="text" class="w-full px-4 py-2.5 border rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500/20" :class="changed.sku ? 'border-blue-400' : 'border-gray-200'"/>
              <p v-if="errors.sku" class="mt-1 text-xs text-red-600">{{ errors.sku }}</p>
            </div>
          </div>
        </div>

        <!-- Inventory -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-4">
          <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-5 flex items-center gap-2">
            <span class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold">3</span>
            Inventory & Status
          </h2>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Quantity <span class="text-red-500">*</span>
                <span v-if="changed.quantity" class="ml-2 text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full font-normal">Changed</span>
              </label>
              <input v-model="form.quantity" type="number" min="0" class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20" :class="changed.quantity ? 'border-blue-400' : 'border-gray-200'"/>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Low Stock Threshold</label>
              <input v-model="form.low_stock_threshold" type="number" min="0" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20"/>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
              <select v-model="form.status" class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                <option value="active">Active</option>
                <option value="draft">Draft</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between">
          <Link :href="route('products.show', product.id)" class="px-5 py-2.5 border border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition">Cancel</Link>
          <button type="submit" :disabled="form.processing || !hasChanges" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition disabled:opacity-50 flex items-center gap-2">
            <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            Save Changes
          </button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({ product: Object })

const categories = [
  'Electronics', 'Clothing', 'Home & Garden', 'Sports & Outdoors',
  'Books', 'Toys & Games', 'Health & Beauty', 'Automotive', 'Food & Grocery', 'Other'
]

const form = useForm({
  name: props.product.name,
  description: props.product.description || '',
  category: props.product.category,
  price: props.product.price,
  sku: props.product.sku,
  quantity: props.product.quantity,
  low_stock_threshold: props.product.low_stock_threshold,
  status: props.product.status,
  image: null,
  _method: 'put',
})

const errors = computed(() => form.errors)

const changed = computed(() => ({
  name: form.name !== props.product.name,
  description: form.description !== (props.product.description || ''),
  category: form.category !== props.product.category,
  price: String(form.price) !== String(props.product.price),
  sku: form.sku !== props.product.sku,
  quantity: Number(form.quantity) !== props.product.quantity,
  image: form.image !== null,
}))

const hasChanges = computed(() => Object.values(changed.value).some(Boolean))

function submit() {
  form.post(route('products.update', props.product.id))
}
</script>
