<template>
  <ThemeProvider>
    <div class="min-h-screen xl:flex">
      <Backdrop />
      <app-sidebar />
      <div
        class="min-w-0 flex-1 transition-all duration-300 ease-in-out"
        :class="[isExpanded || isHovered ? 'lg:ml-[290px]' : 'lg:ml-[90px]']"
      >
        <app-header />
        <div class="app-page-shell p-4 md:p-6">
          <slot></slot>
        </div>
      </div>
    </div>
  </ThemeProvider>
</template>

<script setup>
import AppSidebar from './AppSidebar.vue'
import AppHeader from './AppHeader.vue'
import { useSidebarProvider } from '@/composables/useSidebar'
import Backdrop from './Backdrop.vue'
import ThemeProvider from './ThemeProvider.vue'

const { isExpanded, isHovered } = useSidebarProvider()
</script>
