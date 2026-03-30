<template>
  <div class="space-y-6 p-6">
    <header class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Notifications</h1>
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

    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left">
                  <button type="button" @click="toggleSort('id')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                    ID
                    <SortIcon :active="sortKey === 'id'" :dir="sortDir" />
                  </button>
              </th>
              <th class="px-4 py-3 text-left">
                  <button type="button" @click="toggleSort('title')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                    Title
                    <SortIcon :active="sortKey === 'title'" :dir="sortDir" />
                  </button>
              </th>
              <th class="px-4 py-3 text-left">
                  <button type="button" @click="toggleSort('message')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                    Message
                    <SortIcon :active="sortKey === 'message'" :dir="sortDir" />
                  </button>
              </th>
              <th class="px-4 py-3 text-left">
                  <button type="button" @click="toggleSort('type')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                    Type
                    <SortIcon :active="sortKey === 'type'" :dir="sortDir" />
                  </button>
              </th>
              <th class="px-4 py-3 text-left">
                  <button type="button" @click="toggleSort('created_at')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                    Date
                    <SortIcon :active="sortKey === 'created_at'" :dir="sortDir" />
                  </button>
              </th>
              <th class="px-4 py-3 text-left">
                  <button type="button" @click="toggleSort('status')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                    Status
                    <SortIcon :active="sortKey === 'status'" :dir="sortDir" />
                  </button>
              </th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
            </tr>
          </thead>

          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-if="(notifications.data || []).length === 0">
              <td colspan="7" class="px-4 py-6 text-sm text-gray-500 text-center">No notifications found.</td>
            </tr>

            <tr
              v-for="n in (sortedNotifications || [])"
              :key="n.id"
              class="hover:bg-gray-50"
            >
              <td class="px-4 py-3 text-sm text-gray-700">#{{ n.index_no }}</td>
              <td class="px-4 py-3">
                <div class="text-sm font-medium text-gray-900">{{ n.title || '—' }}</div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm text-gray-700">{{ n.message || '—' }}</div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm text-gray-600">{{ n.type || '—' }}</div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm font-medium text-gray-900">{{ formatDate(n.created_at) }}</div>
                <div class="text-xs text-gray-500">{{ n.time_ago || '' }}</div>
              </td>
              <td class="px-4 py-3">
                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium" :class="n.is_read ? 'bg-gray-100 text-gray-700' : 'bg-green-100 text-green-700'">
                  {{ n.is_read ? 'Read' : 'Unread' }}
                </span>
              </td>
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-2">
                  <button
                    v-if="!n.is_read"
                    type="button"
                    class="inline-flex items-center justify-center h-9 px-3 rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50"
                    @click="markAsRead(n.id)"
                  >
                    Mark read
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex items-center justify-between px-4 py-3 border-t border-gray-200">
        <div class="text-sm text-gray-600">
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
import { ref, computed } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import SortIcon from '@/Components/SortIcon.vue'

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

// Sorting (client-side)
// Default to ID descending
const sortKey = ref('id')
const sortDir = ref('desc')

function toggleSort(key) {
  if (sortKey.value === key) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
    return
  }
  sortKey.value = key
  sortDir.value = 'asc'
}

function getSortValue(item, key) {
  if (!item) return ''
  switch (key) {
    case 'id':
      return Number(item?.id || item?.index_no || 0)
    case 'title':
      return String(item?.title || '').toLowerCase()
    case 'message':
      return String(item?.message || '').toLowerCase()
    case 'type':
      return String(item?.type || '').toLowerCase()
    case 'created_at':
      try { return new Date(item?.created_at || 0).getTime() || 0 } catch { return 0 }
    case 'status':
      return item?.is_read ? 'read' : 'unread'
    default:
      return ''
  }
}

const sortedNotifications = computed(() => {
  const list = Array.isArray(props.notifications?.data) ? [...props.notifications.data] : []
  const key = sortKey.value
  const dir = sortDir.value
  const multiplier = dir === 'asc' ? 1 : -1

  return list
    .map((item, idx) => ({ item, idx }))
    .sort((a, b) => {
      const av = getSortValue(a.item, key)
      const bv = getSortValue(b.item, key)

      if (typeof av === 'number' && typeof bv === 'number') {
        const diff = av - bv
        return diff !== 0 ? diff * multiplier : a.idx - b.idx
      }

      const diff = String(av).localeCompare(String(bv))
      return diff !== 0 ? diff * multiplier : a.idx - b.idx
    })
    .map(x => x.item)
})

const linkClasses = (link) => {
  const base = 'inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-sm border '
  if (link.active) return base + 'bg-indigo-600 text-white border-indigo-600'
  if (!link.url) return base + 'bg-gray-50 text-gray-400 border-gray-200 cursor-not-allowed'
  return base + 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'
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
