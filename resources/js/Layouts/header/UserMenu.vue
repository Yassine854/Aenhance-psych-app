<template>
  <div class="relative z-[70]" ref="dropdownRef" @click.stop>
    <button
      type="button"
      class="flex items-center text-gray-700 dark:text-gray-400"
      @click.stop.prevent="toggleDropdown"
    >
      <span class="mr-3 overflow-hidden rounded-full h-11 w-11">
        <img :src="avatarSrc" :alt="user.name" />
      </span>

      <span class="block mr-1 font-medium text-theme-sm">{{ user.name }}</span>

      <ChevronDownIcon :class="{ 'rotate-180': dropdownOpen }" />
    </button>

    <!-- Dropdown Start -->
    <div
      v-if="dropdownOpen"
      class="absolute right-0 z-[80] mt-[17px] flex w-[260px] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg pointer-events-auto dark:border-gray-800 dark:bg-gray-800"
    >
      <div>
        <span class="block font-medium text-gray-700 text-theme-sm dark:text-gray-400">
          {{ user.name }}
        </span>
        <span class="mt-0.5 block text-theme-xs text-gray-500 dark:text-gray-400">
          {{ user.email }}
        </span>
      </div>

      <ul class="flex flex-col gap-1 pt-4 pb-3 border-b border-gray-200 dark:border-gray-800">
        <li v-for="item in visibleMenuItems" :key="item.text">
          <Link
            :href="item.href"
            class="flex items-center gap-3 px-3 py-2 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300"
          >
            <component
              :is="item.icon"
              class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300"
            />
            {{ item.text }}
          </Link>
        </li>
      </ul>

      <button
        @click.prevent="signOut"
        class="flex items-center gap-3 px-3 py-2 mt-3 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300 w-full text-left"
      >
        <LogoutIcon
          class="text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300"
        />
        Sign out
      </button>
    </div>
    <!-- Dropdown End -->
  </div>
</template>

<script setup>
import { UserCircleIcon, ChevronDownIcon, LogoutIcon, SettingsIcon, InfoCircleIcon } from '@/icons'
import { Link, router, usePage } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted, computed } from 'vue'

const dropdownOpen = ref(false)
const dropdownRef = ref(null)
const page = usePage()
const user = computed(() => page.props?.auth?.user ?? page.props?.value?.auth?.user ?? {})
const userRole = computed(() => String(user.value?.role || '').toUpperCase().trim())

// Try multiple prop paths to find a psychologist's profile image URL.
// Fallbacks ensure we don't break for other roles or missing data.
const profileImageUrl = computed(() => {
  const p = page.props?.value ?? page.props
  return (
    // Common patterns for passing profile data via Inertia props
    p?.psychologist?.profile?.profile_image_url ||
    p?.profile?.profile_image_url ||
    user.value?.profile_image_url ||
    null
  )
})

const avatarSrc = computed(() => {
  if (userRole.value === 'ADMIN') return '/storage/aenhance.svg'
  if (userRole.value === 'PSYCHOLOGIST' && profileImageUrl.value) return profileImageUrl.value
  return '/images/user/owner.jpg'
})

const menuItems = computed(() => {
  if (userRole.value === 'PSYCHOLOGIST') {
    return [
      { href: '/psychologist/profile/edit', icon: UserCircleIcon, text: 'Edit profile', disabled: false },
      { href: '/psychologist/account', icon: SettingsIcon, text: 'Account settings', disabled: false },
      { href: '/support', icon: InfoCircleIcon, text: 'Support', disabled: false },
    ]
  }

  if (userRole.value === 'PATIENT') {
    return [
      { href: '/patient/profile', icon: UserCircleIcon, text: 'Edit profile', disabled: false },
      { href: '/patient/account', icon: SettingsIcon, text: 'Account settings', disabled: false },
      { href: '/support', icon: InfoCircleIcon, text: 'Support', disabled: false },
    ]
  }

  if (userRole.value === 'ADMIN') {
    return [
      { href: '/profile', icon: SettingsIcon, text: 'Account settings', disabled: false },
      { href: '/notifications', icon: InfoCircleIcon, text: 'Notifications', disabled: false },
    ]
  }

  return [
    { href: '/dashboard', icon: SettingsIcon, text: 'Dashboard', disabled: false },
    { href: '/support', icon: InfoCircleIcon, text: 'Support', disabled: false },
  ]
})

const visibleMenuItems = computed(() => menuItems.value.filter(i => !i.disabled));

const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value
}

const closeDropdown = () => {
  dropdownOpen.value = false
}

const signOut = () => {
  router.post('/logout')
  closeDropdown()
}

const handleClickOutside = (event) => {
  const root = dropdownRef.value
  if (!root) return
  const path = typeof event.composedPath === 'function' ? event.composedPath() : []
  const clickedInside = path.length ? path.includes(root) : root.contains(event.target)

  if (!clickedInside) {
    closeDropdown()
  }
}

onMounted(() => {
  document.addEventListener('mousedown', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('mousedown', handleClickOutside)
})
</script>