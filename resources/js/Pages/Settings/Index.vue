<template>
  <AuthenticatedLayout :breadcrumbs="[{ label: 'Settings' }]">
    <Head title="Store Settings" />

    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Store Settings</h1>
      <p class="text-gray-500 mt-1">Manage your store preferences and integrations.</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm mb-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Store Details</h2>
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Store Name</label>
          <input type="text" disabled class="w-full max-w-md border-gray-300 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed" :value="user.name + '\'s Store'" />
          <p class="text-xs text-gray-400 mt-1">To change your store name, please contact support.</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Contact Email</label>
          <input type="email" disabled class="w-full max-w-md border-gray-300 rounded-lg bg-gray-50 text-gray-500 cursor-not-allowed" :value="user.email" />
          <p class="text-xs text-gray-400 mt-1">Use the profile page to update your login email.</p>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm mb-6">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Inventory Preferences</h2>
      
      <div class="space-y-4 max-w-md">
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-sm font-medium text-gray-900">Low Stock Alerts</h3>
            <p class="text-xs text-gray-500">Receive emails when products fall below threshold.</p>
          </div>
          <button type="button" class="bg-blue-600 relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2" role="switch" aria-checked="true">
            <span aria-hidden="true" class="translate-x-5 pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
          </button>
        </div>
        
        <div class="flex items-center justify-between">
          <div>
            <h3 class="text-sm font-medium text-gray-900">Auto-hide Out of Stock</h3>
            <p class="text-xs text-gray-500">Automatically hide products from store when quantity is 0.</p>
          </div>
          <button type="button" class="bg-gray-200 relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2" role="switch" aria-checked="false">
            <span aria-hidden="true" class="translate-x-0 pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineProps({
  user: Object,
})
</script>
