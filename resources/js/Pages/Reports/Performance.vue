<template>
  <AuthenticatedLayout :breadcrumbs="[{ label: 'Reports' }, { label: 'Performance' }]">
    <Head title="Performance Report" />

    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Performance Report</h1>
      <p class="text-gray-500 mt-1">Overview of your store's inventory and product performance.</p>
    </div>

    <!-- KPIs -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
      <StatCard :value="stats.total_products" label="Total Products" sub="Registered products" icon-bg="bg-blue-50">
        <template #icon><svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></template>
      </StatCard>
      
      <StatCard :value="stats.active_products" label="Active Products" sub="Currently available" icon-bg="bg-green-50">
        <template #icon><svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></template>
      </StatCard>

      <StatCard :value="`$${stats.inventory_value.toLocaleString()}`" label="Total Value" sub="Current stock valuation" icon-bg="bg-purple-50">
        <template #icon><svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></template>
      </StatCard>

      <StatCard :value="stats.low_stock_count" label="Low Stock Products" sub="Needing attention" icon-bg="bg-orange-50">
        <template #icon><svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></template>
      </StatCard>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
      <!-- Category Distribution -->
      <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Category Distribution</h2>
        <div class="h-64">
          <Bar :data="categoryChartData" :options="chartOptions" v-if="categoryDistribution.length" />
          <div v-else class="h-full flex items-center justify-center text-gray-400">No data available</div>
        </div>
      </div>

      <!-- Monthly Trend -->
      <div class="bg-white rounded-xl border border-gray-200 p-6">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Inventory Movement (6 Months)</h2>
        <div class="h-64">
          <Line :data="trendChartData" :options="chartOptions" v-if="monthlyTrend.length" />
          <div v-else class="h-full flex items-center justify-center text-gray-400">No data available</div>
        </div>
      </div>
    </div>

    <!-- Top Products Table -->
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-base font-semibold text-gray-900">Top Products by Quantity</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
          <thead class="bg-gray-50 text-gray-500">
            <tr>
              <th class="px-6 py-3 font-medium">Product</th>
              <th class="px-6 py-3 font-medium">SKU</th>
              <th class="px-6 py-3 font-medium">Status</th>
              <th class="px-6 py-3 font-medium text-right">Price</th>
              <th class="px-6 py-3 font-medium text-right">Quantity</th>
              <th class="px-6 py-3 font-medium text-right">Total Value</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="product in topProducts" :key="product.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 font-medium text-gray-900">{{ product.name }}</td>
              <td class="px-6 py-4 text-gray-500">{{ product.sku }}</td>
              <td class="px-6 py-4">
                <span :class="{
                  'bg-green-100 text-green-700': product.status === 'active',
                  'bg-gray-100 text-gray-700': product.status === 'draft',
                  'bg-red-100 text-red-700': product.status === 'inactive'
                }" class="px-2.5 py-1 text-xs font-semibold rounded-full capitalize">
                  {{ product.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-right text-gray-900">${{ product.price }}</td>
              <td class="px-6 py-4 text-right font-medium text-gray-900">{{ product.quantity }}</td>
              <td class="px-6 py-4 text-right text-gray-900">${{ product.value.toLocaleString() }}</td>
            </tr>
            <tr v-if="!topProducts.length">
              <td colspan="6" class="px-6 py-8 text-center text-gray-400">No products found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import StatCard from '@/Components/StatCard.vue'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement
} from 'chart.js'
import { Bar, Line } from 'vue-chartjs'

ChartJS.register(CategoryScale, LinearScale, BarElement, PointElement, LineElement, Title, Tooltip, Legend)

const props = defineProps({
  stats: Object,
  categoryDistribution: Array,
  statusDistribution: Array,
  topProducts: Array,
  monthlyTrend: Array,
})

const categoryChartData = computed(() => ({
  labels: props.categoryDistribution.map(c => c.category),
  datasets: [{
    label: 'Products Count',
    backgroundColor: '#3b82f6',
    borderRadius: 4,
    data: props.categoryDistribution.map(c => c.count)
  }]
}))

const trendChartData = computed(() => ({
  labels: props.monthlyTrend.map(t => t.month),
  datasets: [{
    label: 'Net Change',
    backgroundColor: '#10b981',
    borderColor: '#10b981',
    data: props.monthlyTrend.map(t => t.net_change),
    tension: 0.3
  }]
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    y: { beginAtZero: true, grid: { borderDash: [2, 4], color: '#f3f4f6' } },
    x: { grid: { display: false } }
  }
}
</script>
