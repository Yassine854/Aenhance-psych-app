<template>
  <div class="relative" ref="dropdownRef">
    <button
      class="relative flex items-center justify-center text-gray-500 transition-colors bg-white border border-gray-200 rounded-full hover:text-dark-900 h-11 w-11 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white"
      @click="toggleDropdown"
    >
      <span
        :class="{ hidden: !notifying, flex: notifying }"
        class="absolute right-0 top-0.5 z-1 h-2 w-2 rounded-full bg-orange-400"
      >
        <span
          class="absolute inline-flex w-full h-full bg-orange-400 rounded-full opacity-75 -z-1 animate-ping"
        ></span>
      </span>
      <svg
        class="fill-current"
        width="20"
        height="20"
        viewBox="0 0 20 20"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          fill-rule="evenodd"
          clip-rule="evenodd"
          d="M10.75 2.29248C10.75 1.87827 10.4143 1.54248 10 1.54248C9.58583 1.54248 9.25004 1.87827 9.25004 2.29248V2.83613C6.08266 3.20733 3.62504 5.9004 3.62504 9.16748V14.4591H3.33337C2.91916 14.4591 2.58337 14.7949 2.58337 15.2091C2.58337 15.6234 2.91916 15.9591 3.33337 15.9591H4.37504H15.625H16.6667C17.0809 15.9591 17.4167 15.6234 17.4167 15.2091C17.4167 14.7949 17.0809 14.4591 16.6667 14.4591H16.375V9.16748C16.375 5.9004 13.9174 3.20733 10.75 2.83613V2.29248ZM14.875 14.4591V9.16748C14.875 6.47509 12.6924 4.29248 10 4.29248C7.30765 4.29248 5.12504 6.47509 5.12504 9.16748V14.4591H14.875ZM8.00004 17.7085C8.00004 18.1228 8.33583 18.4585 8.75004 18.4585H11.25C11.6643 18.4585 12 18.1228 12 17.7085C12 17.2943 11.6643 16.9585 11.25 16.9585H8.75004C8.33583 16.9585 8.00004 17.2943 8.00004 17.7085Z"
          fill=""
        />
      </svg>
    </button>

    <!-- Dropdown Start -->
    <div
      v-if="dropdownOpen"
      class="absolute -right-[240px] mt-[17px] flex h-[480px] w-[350px] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-lg dark:border-gray-800 dark:bg-gray-800 sm:w-[361px] lg:right-0"
    >
      <div
        class="flex items-center justify-between pb-3 mb-3 border-b border-gray-100 dark:border-gray-800"
      >
        <h5 class="text-lg font-semibold text-gray-800 dark:text-white/90">Notifications</h5>

        <button @click="closeDropdown" class="text-gray-500 dark:text-gray-400">
          <svg
            class="fill-current"
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              fill-rule="evenodd"
              clip-rule="evenodd"
              d="M6.21967 7.28131C5.92678 6.98841 5.92678 6.51354 6.21967 6.22065C6.51256 5.92775 6.98744 5.92775 7.28033 6.22065L11.999 10.9393L16.7176 6.22078C17.0105 5.92789 17.4854 5.92788 17.7782 6.22078C18.0711 6.51367 18.0711 6.98855 17.7782 7.28144L13.0597 12L17.7782 16.7186C18.0711 17.0115 18.0711 17.4863 17.7782 17.7792C17.4854 18.0721 17.0105 18.0721 16.7176 17.7792L11.999 13.0607L7.28033 17.7794C6.98744 18.0722 6.51256 18.0722 6.21967 17.7794C5.92678 17.4865 5.92678 17.0116 6.21967 16.7187L10.9384 12L6.21967 7.28131Z"
              fill=""
            />
          </svg>
        </button>
      </div>

      <div v-if="canUseNotifications" class="mb-2 flex items-center justify-between px-1">
        <p class="text-xs text-gray-500 dark:text-gray-400">
          {{ unreadCount }} unread
        </p>
        <button
          type="button"
          class="text-xs font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400"
          @click="markAllAsRead"
          :disabled="unreadCount === 0"
        >
          Mark all as read
        </button>
      </div>

      <ul class="flex flex-col h-auto overflow-y-auto custom-scrollbar">
        <li
          v-for="notification in notifications"
          :key="notification.id"
          @click="handleItemClick(notification, $event)"
        >
          <a
            class="flex gap-3 rounded-lg border-b border-gray-100 p-3 px-4.5 py-3 hover:bg-gray-100 dark:border-gray-800 dark:hover:bg-white/5"
            :class="notification.is_read ? 'opacity-80' : ''"
            href="#"
          >
            <span class="relative block w-full h-10 rounded-full z-1 max-w-10">
              <span class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-sm font-semibold text-gray-700 dark:bg-gray-700 dark:text-gray-100">
                #{{ notification.index_no }}
              </span>
              <span
                :class="notification.is_read ? 'bg-gray-300' : 'bg-success-500'"
                class="absolute bottom-0 right-0 z-10 h-2.5 w-full max-w-2.5 rounded-full border-[1.5px] border-white dark:border-gray-900"
              ></span>
            </span>

            <span class="block">
              <span class="mb-1.5 block text-theme-sm text-gray-500 dark:text-gray-400">
                <span class="font-medium text-gray-800 dark:text-white/90">
                  {{ notification.title }}
                </span>
                <span class="mt-1 block text-gray-500 dark:text-gray-400">
                  {{ notification.message }}
                </span>
              </span>

              <span class="flex items-center gap-2 text-gray-500 text-theme-xs dark:text-gray-400">
                <span>{{ notification.type }}</span>
                <span class="w-1 h-1 bg-gray-400 rounded-full"></span>
                <span>{{ notification.time_ago }}</span>
              </span>
            </span>
          </a>
        </li>

        <li v-if="canUseNotifications && notifications.length === 0" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
          No notifications yet.
        </li>

        <li v-if="!canUseNotifications" class="px-3 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
          Notifications are available for admin and psychologist accounts.
        </li>
      </ul>

      <button
        @click="handleViewAllClick"
        class="mt-3 flex w-full items-center justify-center rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700"
      >
        View all notifications
      </button>
    </div>
    <!-- Dropdown End -->
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

const dropdownOpen = ref(false)
const notifying = ref(false)
const dropdownRef = ref(null)
const notifications = ref([])
const unreadCount = ref(0)
const latestId = ref(0)
const pollTimerRef = ref(null)

const page = usePage()
const canUseNotifications = computed(() => {
  const role = String(page?.props?.auth?.user?.role || '').toUpperCase().trim()
  return role === 'ADMIN' || role === 'PSYCHOLOGIST'
})

const applyFeed = (payload) => {
  notifications.value = Array.isArray(payload?.notifications) ? payload.notifications : []
  unreadCount.value = Number(payload?.unread_count || 0)
  latestId.value = Number(payload?.latest_id || 0)
  notifying.value = unreadCount.value > 0
}

const fetchFeed = async () => {
  if (!canUseNotifications.value) return

  try {
    const { data } = await window.axios.get('/notifications/feed')
    applyFeed(data)
  } catch (error) {
    console.error('Failed to fetch notifications feed', error)
  }
}

const fetchLite = async () => {
  if (!canUseNotifications.value) return

  try {
    const { data } = await window.axios.get('/notifications/feed?lite=1')
    const unread = Number(data?.unread_count || 0)
    const latest = Number(data?.latest_id || 0)
    const hasChanges = latest > latestId.value

    unreadCount.value = unread
    notifying.value = unread > 0

    if (hasChanges || dropdownOpen.value) {
      await fetchFeed()
    }
  } catch (error) {
    console.error('Failed to fetch lightweight notifications feed', error)
  }
}

const startPolling = () => {
  stopPolling()
  if (!canUseNotifications.value) return

  pollTimerRef.value = setInterval(() => {
    if (document.visibilityState !== 'visible') return
    fetchLite()
  }, 10000)
}

const stopPolling = () => {
  if (!pollTimerRef.value) return
  clearInterval(pollTimerRef.value)
  pollTimerRef.value = null
}

const toggleDropdown = () => {
  dropdownOpen.value = !dropdownOpen.value
  if (dropdownOpen.value) {
    notifying.value = unreadCount.value > 0
    fetchFeed()
  }
}

const closeDropdown = () => {
  dropdownOpen.value = false
}

const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    closeDropdown()
  }
}

const handleItemClick = async (notification, event) => {
  event.preventDefault()

  if (canUseNotifications.value && notification && !notification.is_read) {
    try {
      const { data } = await window.axios.post(`/notifications/${notification.id}/read`)
      applyFeed(data)
    } catch (error) {
      console.error('Failed to mark notification as read', error)
    }
  }

  closeDropdown()
  router.visit(notification?.action_url || '/notifications')
}

const handleViewAllClick = (event) => {
  event.preventDefault()
  closeDropdown()
  router.visit('/notifications')
}

const markAllAsRead = async () => {
  if (!canUseNotifications.value || unreadCount.value === 0) return

  try {
    const { data } = await window.axios.post('/notifications/read-all')
    applyFeed(data)
  } catch (error) {
    console.error('Failed to mark all notifications as read', error)
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  fetchFeed().then(() => startPolling())
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  stopPolling()
})
</script>
