<template>
  <div v-if="show && specialisation" class="fixed inset-0 z-[1000] flex items-center justify-center">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[1000]" @click="$emit('close')"></div>

    <div class="relative w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden z-[1001]">
      <div class="bg-gradient-to-r from-[rgb(141,61,79)] to-[rgb(89,151,172)] p-6">
        <div class="flex items-center justify-between">
          <div class="text-white">
            <div class="text-xl font-semibold">Specialisation</div>
            <div class="text-sm opacity-90">#{{ specialisation.id }}</div>
          </div>
          <button @click="$emit('close')" class="text-white/90 hover:text-white text-2xl leading-none">✕</button>
        </div>
      </div>

      <div class="bg-white p-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div class="sm:col-span-1 text-sm text-gray-500">Name</div>
          <div class="sm:col-span-2 text-sm font-medium text-gray-900">{{ specialisation.name }}</div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
          <button type="button" @click="$emit('close')" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  show: Boolean,
  specialisation: Object,
})

defineEmits(['close'])
</script>
