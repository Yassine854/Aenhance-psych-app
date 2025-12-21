<!-- resources/js/layout/ThemeProvider.vue -->
<template>
  <slot />
</template>

<script>
import { ref, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

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
    const { props } = usePage()

    onMounted(() => {
      const saved = localStorage.getItem('theme')
      const isAdmin = props.value?.auth?.user?.role === 'admin'

      if (isAdmin) {
        // Force light theme for admin users and persist it
        theme.value = 'light'
        localStorage.setItem('theme', 'light')
      } else if (saved) {
        theme.value = saved
      } else {
        // Default to light theme when there is no saved preference
        theme.value = 'light'
      }

      document.documentElement.classList.toggle('dark', theme.value === 'dark')
    })
  },
}
</script>