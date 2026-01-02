<template>
  <span class="inline-flex flex-col leading-none select-none">
    <svg
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 20 20"
      fill="currentColor"
      class="h-3 w-3 -mb-0.5 transition-all duration-200 ease-out"
      :class="upClass"
      aria-hidden="true"
    >
      <path
        fill-rule="evenodd"
        d="M10 3a1 1 0 0 1 .832.445l4 6A1 1 0 0 1 14 11H6a1 1 0 0 1-.832-1.555l4-6A1 1 0 0 1 10 3Z"
        clip-rule="evenodd"
      />
    </svg>
    <svg
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 20 20"
      fill="currentColor"
      class="h-3 w-3 transition-all duration-200 ease-out"
      :class="downClass"
      aria-hidden="true"
    >
      <path
        fill-rule="evenodd"
        d="M10 17a1 1 0 0 1-.832-.445l-4-6A1 1 0 0 1 6 9h8a1 1 0 0 1 .832 1.555l-4 6A1 1 0 0 1 10 17Z"
        clip-rule="evenodd"
      />
    </svg>
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  active: { type: Boolean, default: false },
  dir: { type: String, default: 'asc' }, // 'asc' | 'desc'
})

const upClass = computed(() => {
  if (!props.active) {
    return 'text-gray-400/70 opacity-80 group-hover:opacity-100 group-hover:text-gray-500'
  }
  if (props.dir === 'asc') {
    return 'text-gray-700 opacity-100 -translate-y-[0.5px] drop-shadow-sm'
  }
  return 'text-gray-300 opacity-60'
})

const downClass = computed(() => {
  if (!props.active) {
    return 'text-gray-400/70 opacity-80 group-hover:opacity-100 group-hover:text-gray-500'
  }
  if (props.dir === 'desc') {
    return 'text-gray-700 opacity-100 translate-y-[0.5px] drop-shadow-sm'
  }
  return 'text-gray-300 opacity-60'
})
</script>
