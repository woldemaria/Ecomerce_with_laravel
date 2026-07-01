<template>
  <div class="flex flex-col sm:flex-row gap-2 items-center justify-between py-4 border-t border-gray-100">
    <p class="text-sm text-gray-500">
      Showing {{ from }} to {{ to }} of {{ total }} results
    </p>
    <div class="flex items-center gap-1">
      <Link
        v-if="prevPageUrl"
        :href="prevPageUrl"
        preserve-scroll
        class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-700 hover:bg-gray-50 transition"
      >
        ← Prev
      </Link>
      <span v-else class="px-3 py-1.5 rounded-lg border border-gray-100 text-sm text-gray-300 cursor-not-allowed">← Prev</span>

      <template v-for="link in links" :key="link.label">
        <Link
          v-if="link.url && !link.label.includes('Previous') && !link.label.includes('Next')"
          :href="link.url"
          preserve-scroll
          :class="[
            'px-3 py-1.5 rounded-lg border text-sm transition',
            link.active
              ? 'bg-blue-600 border-blue-600 text-white font-semibold'
              : 'border-gray-200 text-gray-700 hover:bg-gray-50'
          ]"
          v-html="link.label"
        />
      </template>

      <Link
        v-if="nextPageUrl"
        :href="nextPageUrl"
        preserve-scroll
        class="px-3 py-1.5 rounded-lg border border-gray-200 text-sm text-gray-700 hover:bg-gray-50 transition"
      >
        Next →
      </Link>
      <span v-else class="px-3 py-1.5 rounded-lg border border-gray-100 text-sm text-gray-300 cursor-not-allowed">Next →</span>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  from: Number,
  to: Number,
  total: Number,
  prevPageUrl: String,
  nextPageUrl: String,
  links: Array,
})
</script>
