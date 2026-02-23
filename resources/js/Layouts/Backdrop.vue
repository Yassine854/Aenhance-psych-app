<template>
  <div
    v-if="isMobileOpen"
    class="fixed inset-0 bg-gray-900/50 z-40 lg:hidden pointer-events-auto"
    @click="closeMobileSidebar"
  ></div>
</template>

<script setup lang="ts">
import { useSidebar } from '@/composables/useSidebar'
const { closeMobileSidebar, isMobileOpen } = useSidebar()
</script>
