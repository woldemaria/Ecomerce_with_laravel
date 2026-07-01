<template>
  <AuthenticatedLayout :breadcrumbs="[{ label: 'Products', href: route('products.index') }, { label: 'Bulk Manage' }]">
    <Head title="Bulk Manage Products" />

    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
      <h2 class="text-xl font-bold text-gray-900 mb-4">Bulk Actions</h2>
      <p class="text-gray-500 mb-6 text-sm">Select multiple products from your inventory and apply actions to them simultaneously.</p>
      
      <div class="flex items-center gap-4 text-gray-400 p-12 justify-center border-2 border-dashed border-gray-200 rounded-xl bg-gray-50">
        <div class="text-center">
          <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
          </svg>
          <p class="font-medium text-gray-900">Bulk management is under construction.</p>
          <p class="text-sm mt-1">Check back later for updates to this feature.</p>
          <div class="mt-4">
             <Link :href="route('products.index')" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">
               &larr; Return to Products
             </Link>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
</script>
