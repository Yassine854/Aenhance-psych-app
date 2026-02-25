<template>
  <div class="space-y-6 p-6">
    <header class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Notifications</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          {{ stats.total_count }} total · {{ stats.unread_count }} unread
        </p>
      </div>

      <button
        type="button"
        class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
        :disabled="stats.unread_count === 0 || markingAll"
        @click="markAllAsRead"
      >
        {{ markingAll ? 'Processing...' : 'Mark all as read' }}
      </button>
    </header>

    <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-900">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
          <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">#</th>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Title</th>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Message</th>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Type</th>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Date</th>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
              <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Action</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-900">
            <tr v-for="notification in notifications.data || []" :key="notification.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/70">
              <td class="px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-200">
                #{{ notification.index_no }}
              </td>
              <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">
                {{ notification.title }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                {{ notification.message }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                {{ notification.type }}
              </td>
              <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">
                <div>{{ notification.time_ago }}</div>
                <div class="text-xs text-gray-400">{{ formatDate(notification.created_at) }}</div>
              </td>
              <td class="px-4 py-3 text-sm">
                <span
                  class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium"
                  :class="notification.is_read
                    ? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'
                    : 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300'"
                >
                  {{ notification.is_read ? 'Read' : 'Unread' }}
                </span>
              </td>
              <td class="px-4 py-3 text-right">
                <button
                  v-if="!notification.is_read"
                  type="button"
                  class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                  @click="markAsRead(notification.id)"
                >
                  Mark read
                </button>
              </td>
            </tr>

            <tr v-if="(notifications.data || []).length === 0">
              <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                No notifications found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex items-center justify-between border-t border-gray-200 px-4 py-3 dark:border-gray-800">
        <div class="text-sm text-gray-600 dark:text-gray-300">
          Showing {{ notifications.from || 0 }}-{{ notifications.to || 0 }} of {{ notifications.total || 0 }}
        </div>

        <div class="flex items-center gap-2">
          <Link
            v-for="(link, i) in notifications.links || []"
            :key="i"
            :href="link.url || '#'"
            :class="linkClasses(link)"
            :style="link.active ? { backgroundColor: brandColor, borderColor: brandColor, color: '#fff' } : null"
            preserve-scroll
          >
            <span v-html="link.label"></span>
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  notifications: { type: Object, required: true },
  stats: {
    type: Object,
    default: () => ({ total_count: 0, unread_count: 0 }),
  },
})

const markingAll = ref(false)
const brandColor = 'rgb(89 151 172 / var(--tw-bg-opacity, 1))'

const linkClasses = (link) => {
  const base = 'inline-flex min-w-[36px] items-center justify-center rounded-md border px-3 py-1.5 text-sm transition'
  if (!link?.url) return `${base} cursor-not-allowed border-gray-200 text-gray-400 dark:border-gray-700 dark:text-gray-600`
  if (link.active) return `${base} border-transparent text-white`
  return `${base} border-gray-200 text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700`
}

const formatDate = (value) => {
  if (!value) return '-'
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return '-'
  return d.toLocaleString()
}

const markAsRead = async (id) => {
  try {
    await window.axios.post(`/notifications/${id}/read`)
    router.reload({ only: ['notifications', 'stats'], preserveScroll: true })
  } catch (error) {
    console.error('Unable to mark notification as read', error)
  }
}

const markAllAsRead = async () => {
  if (markingAll.value || Number(props.stats?.unread_count || 0) === 0) return

  markingAll.value = true
  try {
    await window.axios.post('/notifications/read-all')
    router.reload({ only: ['notifications', 'stats'], preserveScroll: true })
  } catch (error) {
    console.error('Unable to mark all notifications as read', error)
  } finally {
    markingAll.value = false
  }
}
</script>
