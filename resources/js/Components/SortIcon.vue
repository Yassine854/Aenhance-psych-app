<template>
  <span class="inline-flex items-center">
    <svg
      v-if="!active"
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 20 20"
      fill="currentColor"
      class="h-4 w-4 text-gray-300 group-hover:text-gray-400"
    >
      <path
        fill-rule="evenodd"
        d="M10 3a.75.75 0 0 1 .53.22l3.5 3.5a.75.75 0 1 1-1.06 1.06L10 4.81 7.03 7.78a.75.75 0 1 1-1.06-1.06l3.5-3.5A.75.75 0 0 1 10 3Zm0 14a.75.75 0 0 1-.53-.22l-3.5-3.5a.75.75 0 1 1 1.06-1.06L10 15.19l2.97-2.97a.75.75 0 1 1 1.06 1.06l-3.5 3.5A.75.75 0 0 1 10 17Z"
        clip-rule="evenodd"
      />
    </svg>

    <svg
      v-else
      xmlns="http://www.w3.org/2000/svg"
      viewBox="0 0 20 20"
      fill="currentColor"
      class="h-4 w-4 text-gray-700"
    >
      <path
        v-if="dir === 'asc'"
        fill-rule="evenodd"
        d="M10 3a.75.75 0 0 1 .53.22l3.5 3.5a.75.75 0 1 1-1.06 1.06L10 4.81 7.03 7.78a.75.75 0 1 1-1.06-1.06l3.5-3.5A.75.75 0 0 1 10 3Z"
        clip-rule="evenodd"
      />
      <path
        v-else
        fill-rule="evenodd"
        d="M10 17a.75.75 0 0 1-.53-.22l-3.5-3.5a.75.75 0 1 1 1.06-1.06L10 15.19l2.97-2.97a.75.75 0 1 1 1.06 1.06l-3.5 3.5A.75.75 0 0 1 10 17Z"
        clip-rule="evenodd"
      />
    </svg>
  </span>
</template>

<script setup>
defineProps({
  active: Boolean,
  dir: {
    type: String,
    default: 'asc',
  },
})
</script>
