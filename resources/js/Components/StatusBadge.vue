<template>
  <span :class="classes">{{ label }}</span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: String,
})

const config = {
  active:        { bg: 'bg-green-50',  text: 'text-green-700',  label: 'Active' },
  inactive:      { bg: 'bg-gray-100',  text: 'text-gray-600',   label: 'Inactive' },
  draft:         { bg: 'bg-yellow-50', text: 'text-yellow-700', label: 'Draft' },
  in_stock:      { bg: 'bg-green-50',  text: 'text-green-700',  label: 'In Stock' },
  low_stock:     { bg: 'bg-orange-50', text: 'text-orange-700', label: 'Low Stock' },
  out_of_stock:  { bg: 'bg-red-50',    text: 'text-red-700',    label: 'Out of Stock' },
}

const current = computed(() => config[props.status] || { bg: 'bg-gray-100', text: 'text-gray-600', label: props.status })

const classes = computed(() =>
  `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold ${current.value.bg} ${current.value.text}`
)

const label = computed(() => current.value.label)
</script>
