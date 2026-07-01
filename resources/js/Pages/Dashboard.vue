<template>
  <AuthenticatedLayout :breadcrumbs="[{ label: 'Dashboard' }]">
    <Head title="Dashboard" />

    <!-- Welcome Banner -->
    <div class="mb-8 bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 text-white">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold">Welcome back, {{ $page.props.auth.user.name }}! 👋</h1>
          <p class="text-blue-100 mt-1">Here's what's happening with your store today.</p>
        </div>
        <Link :href="route('products.create')" class="hidden sm:flex items-center gap-2 bg-white text-blue-600 font-semibold px-5 py-2.5 rounded-xl hover:bg-blue-50 transition text-sm">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Add Product
        </Link>
      </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
      <StatCard :value="stats.total_products" label="Total Products" sub="All your product listings" icon-bg="bg-blue-50">
        <template #icon>
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
          </svg>
        </template>
      </StatCard>

      <StatCard :value="stats.total_inventory?.toLocaleString()" label="Total Inventory" sub="Units across all products" icon-bg="bg-green-50">
        <template #icon>
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
          </svg>
        </template>
      </StatCard>

      <StatCard :value="stats.active_listings" label="Active Listings" sub="Published and available" icon-bg="bg-emerald-50">
        <template #icon>
          <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </template>
      </StatCard>

      <StatCard :value="stats.low_stock_count" label="Low Stock Items" sub="Need restocking soon" icon-bg="bg-orange-50">
        <template #icon>
          <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
        </template>
      </StatCard>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
      <!-- Recent Activity -->
      <div class="xl:col-span-2 bg-white rounded-xl border border-gray-200">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
          <h2 class="text-base font-semibold text-gray-900">Recent Activity</h2>
          <Link :href="route('inventory.adjust')" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View all</Link>
        </div>
        <div class="divide-y divide-gray-50">
          <div v-if="!recentActivity?.length" class="flex flex-col items-center py-12 text-gray-400">
            <svg class="w-10 h-10 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
            </svg>
            <p class="text-sm">No recent activity yet</p>
          </div>
          <div v-for="activity in recentActivity" :key="activity.id" class="flex items-center gap-4 px-6 py-3.5 hover:bg-gray-50">
            <div :class="['w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0', activity.type === 'restock' ? 'bg-green-50' : 'bg-orange-50']">
              <svg class="w-4 h-4" :class="activity.type === 'restock' ? 'text-green-600' : 'text-orange-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="activity.type === 'restock' ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3'"/>
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-medium text-gray-900 truncate">{{ activity.product_name }}</p>
              <p class="text-xs text-gray-500 capitalize">{{ activity.reason }} · {{ activity.quantity_change > 0 ? '+' : '' }}{{ activity.quantity_change }} units</p>
            </div>
            <p class="text-xs text-gray-400 flex-shrink-0">{{ activity.created_at }}</p>
          </div>
        </div>
      </div>

      <!-- Right Column -->
      <div class="space-y-6">
        <div class="bg-white rounded-xl border border-gray-200 p-5">
          <h2 class="text-base font-semibold text-gray-900 mb-4">Quick Actions</h2>
          <div class="space-y-2">
            <Link :href="route('products.create')" class="flex items-center gap-3 w-full px-4 py-3 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
              Add New Product
            </Link>
            <Link :href="route('inventory.adjust')" class="flex items-center gap-3 w-full px-4 py-3 rounded-lg border border-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-50 transition">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4"/></svg>
              Adjust Inventory
            </Link>
            <Link :href="route('inventory.low-stock')" class="flex items-center gap-3 w-full px-4 py-3 rounded-lg border border-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-50 transition">
              <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
              View Low Stock
              <span v-if="stats.low_stock_count > 0" class="ml-auto bg-red-100 text-red-700 text-xs font-semibold px-2 py-0.5 rounded-full">{{ stats.low_stock_count }}</span>
            </Link>
            <Link :href="route('reports.performance')" class="flex items-center gap-3 w-full px-4 py-3 rounded-lg border border-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-50 transition">
              <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"/></svg>
              Performance Report
            </Link>
          </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200">
          <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Low Stock Alert</h2>
            <Link :href="route('inventory.low-stock')" class="text-xs text-blue-600 font-medium">View all</Link>
          </div>
          <div v-if="!lowStockProducts?.length" class="flex flex-col items-center py-8 text-gray-400">
            <svg class="w-8 h-8 mb-2 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-green-600 font-medium">All stock levels healthy!</p>
          </div>
          <div v-else class="divide-y divide-gray-50">
            <div v-for="product in lowStockProducts" :key="product.id" class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50">
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ product.name }}</p>
                <p class="text-xs text-gray-400">{{ product.sku }}</p>
              </div>
              <div class="text-right">
                <p class="text-sm font-bold text-orange-600">{{ product.quantity }}</p>
                <p class="text-xs text-gray-400">/ {{ product.low_stock_threshold }} min</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatCard from '@/Components/StatCard.vue'

defineProps({
  stats: Object,
  recentActivity: Array,
  lowStockProducts: Array,
})
</script>
