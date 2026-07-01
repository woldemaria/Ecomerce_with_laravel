<template>
  <div class="bg-white rounded-xl border border-gray-200 p-6 hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between mb-4">
      <div :class="`w-12 h-12 rounded-xl flex items-center justify-center ${iconBg}`">
        <slot name="icon" />
      </div>
      <span v-if="trend !== undefined" :class="[
        'flex items-center gap-1 text-xs font-semibold px-2 py-1 rounded-full',
        trend >= 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'
      ]">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            :d="trend >= 0 ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3'"
          />
        </svg>
        {{ Math.abs(trend) }}%
      </span>
    </div>
    <p class="text-2xl font-bold text-gray-900">{{ value }}</p>
    <p class="text-sm text-gray-500 mt-1">{{ label }}</p>
    <p v-if="sub" class="text-xs text-gray-400 mt-0.5">{{ sub }}</p>
  </div>
</template>

<script setup>
defineProps({
  value: [String, Number],
  label: String,
  sub: String,
  iconBg: { type: String, default: 'bg-blue-50' },
  trend: Number,
})
</script>
