<!-- resources/js/layout/ThemeProvider.vue -->
<template>
  <slot />
</template>

<script>
import { ref, onMounted } from 'vue'

const theme = ref('light')

export function useTheme() {
  return {
    theme,
    toggleTheme: () => {
      theme.value = theme.value === 'dark' ? 'light' : 'dark'
      document.documentElement.classList.toggle('dark', theme.value === 'dark')
      localStorage.setItem('theme', theme.value)
    },
  }
}

export default {
  name: 'ThemeProvider',
  setup() {
    onMounted(() => {
      const saved = localStorage.getItem('theme')
      if (saved) theme.value = saved
      else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        theme.value = 'dark'
      }
      document.documentElement.classList.toggle('dark', theme.value === 'dark')
    })
  },
}
</script>