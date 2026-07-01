<template>
  <AuthenticatedLayout :breadcrumbs="[{ label: 'Products', href: route('products.index') }, { label: 'Add Product' }]">
    <Head title="Add Product" />

    <div class="max-w-3xl mx-auto">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-xl font-bold text-gray-900">Add New Product</h1>
          <p class="text-sm text-gray-500 mt-0.5">Fill in the details below to create a new product listing</p>
        </div>
        <Link :href="route('products.index')" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
          Back
        </Link>
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
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Product Name <span class="text-red-500">*</span></label>
              <input
                v-model="form.name"
                type="text"
                placeholder="e.g. Premium Wireless Headphones"
                class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 transition"
                :class="errors.name ? 'border-red-400 bg-red-50' : 'border-gray-200'"
              />
              <p v-if="errors.name" class="mt-1 text-xs text-red-600">{{ errors.name }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
              <textarea
                v-model="form.description"
                rows="4"
                placeholder="Describe your product in detail..."
                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 resize-none transition"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Category <span class="text-red-500">*</span></label>
              <div class="flex gap-2">
                <select
                  v-if="!customCategory"
                  v-model="form.category"
                  class="flex-1 px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                  :class="errors.category ? 'border-red-400 bg-red-50' : 'border-gray-200'"
                >
                  <option value="">Select a category</option>
                  <option v-for="cat in predefinedCategories" :key="cat" :value="cat">{{ cat }}</option>
                </select>
                <input
                  v-else
                  v-model="form.category"
                  type="text"
                  placeholder="Enter custom category"
                  class="flex-1 px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                />
                <button type="button" @click="customCategory = !customCategory" class="px-3 py-2 border border-gray-200 rounded-lg text-xs text-gray-600 hover:bg-gray-50 transition">
                  {{ customCategory ? 'Pick from list' : '+ Custom' }}
                </button>
              </div>
              <p v-if="errors.category" class="mt-1 text-xs text-red-600">{{ errors.category }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Product Image</label>
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
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Price (USD) <span class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute left-3 top-2.5 text-gray-400 text-sm">$</span>
                <input
                  v-model="form.price"
                  type="number"
                  step="0.01"
                  min="0"
                  placeholder="0.00"
                  class="w-full pl-7 pr-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                  :class="errors.price ? 'border-red-400 bg-red-50' : 'border-gray-200'"
                />
              </div>
              <p v-if="errors.price" class="mt-1 text-xs text-red-600">{{ errors.price }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">SKU <span class="text-red-500">*</span></label>
              <div class="flex gap-2">
                <input
                  v-model="form.sku"
                  type="text"
                  placeholder="e.g. WH-1000XM5"
                  class="flex-1 px-4 py-2.5 border rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                  :class="errors.sku ? 'border-red-400 bg-red-50' : 'border-gray-200'"
                />
                <button type="button" @click="generateSku" class="px-3 py-2 border border-gray-200 rounded-lg text-xs text-gray-600 hover:bg-gray-50 transition">
                  Generate
                </button>
              </div>
              <p v-if="errors.sku" class="mt-1 text-xs text-red-600">{{ errors.sku }}</p>
            </div>
          </div>
        </div>

        <!-- Inventory -->
        <div class="bg-white rounded-xl border border-gray-200 p-6 mb-6">
          <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-5 flex items-center gap-2">
            <span class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold">3</span>
            Inventory Settings
          </h2>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Initial Quantity <span class="text-red-500">*</span></label>
              <input
                v-model="form.quantity"
                type="number"
                min="0"
                placeholder="0"
                class="w-full px-4 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                :class="errors.quantity ? 'border-red-400 bg-red-50' : 'border-gray-200'"
              />
              <p v-if="errors.quantity" class="mt-1 text-xs text-red-600">{{ errors.quantity }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">Low Stock Threshold</label>
              <input
                v-model="form.low_stock_threshold"
                type="number"
                min="0"
                placeholder="10"
                class="w-full px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20"
              />
              <p class="mt-1 text-xs text-gray-400">You'll be alerted when stock drops below this</p>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-between">
          <Link :href="route('products.index')" class="px-5 py-2.5 border border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition">
            Cancel
          </Link>
          <div class="flex items-center gap-3">
            <button
              type="button"
              @click="saveDraft"
              :disabled="processing"
              class="px-5 py-2.5 border border-gray-300 bg-white rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition disabled:opacity-50"
            >
              Save as Draft
            </button>
            <button
              type="submit"
              :disabled="processing"
              class="px-6 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition disabled:opacity-50 flex items-center gap-2"
            >
              <svg v-if="processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
              </svg>
              Publish Product
            </button>
          </div>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const customCategory = ref(false)
const processing = ref(false)

const predefinedCategories = [
  'Electronics', 'Clothing', 'Home & Garden', 'Sports & Outdoors',
  'Books', 'Toys & Games', 'Health & Beauty', 'Automotive', 'Food & Grocery', 'Other'
]

const form = useForm({
  name: '',
  description: '',
  category: '',
  price: '',
  sku: '',
  quantity: 0,
  low_stock_threshold: 10,
  status: 'active',
  image: null,
})

const errors = ref({})

function generateSku() {
  const prefix = form.name ? form.name.slice(0, 3).toUpperCase() : 'SKU'
  const rand = Math.random().toString(36).substr(2, 6).toUpperCase()
  form.sku = `${prefix}-${rand}`
}

function submit() {
  form.status = 'active'
  form.post(route('products.store'), {
    onError: (e) => { errors.value = e },
    onStart: () => { processing.value = true },
    onFinish: () => { processing.value = false },
  })
}

function saveDraft() {
  form.status = 'draft'
  form.post(route('products.store'), {
    onError: (e) => { errors.value = e },
    onStart: () => { processing.value = true },
    onFinish: () => { processing.value = false },
  })
}
</script>
