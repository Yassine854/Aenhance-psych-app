<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import Navbar from '@/Components/Navbar.vue'
import Footer from '@/Components/Footer.vue'

const props = defineProps({
  canLogin: { type: Boolean },
  canRegister: { type: Boolean },
  authUser: { type: Object },
  notifications: { type: Object, required: true },
  stats: {
    type: Object,
    default: () => ({ total_count: 0, unread_count: 0 }),
  },
})

const markingAll = ref(false)

const linkClasses = (link) => {
  const base = 'inline-flex min-w-[36px] items-center justify-center rounded-md border px-3 py-1.5 text-sm transition'
  if (!link?.url) return `${base} cursor-not-allowed border-gray-200 text-gray-400`
  if (link.active) return `${base} border-[#5997ac] bg-[#5997ac] text-white`
  return `${base} border-gray-200 text-gray-700 hover:bg-gray-100`
}

const formatDate = (value) => {
  if (!value) return '—'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return '—'
  return date.toLocaleString()
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

<template>
  <Head title="My notifications" />

  <Navbar :canLogin="canLogin" :canRegister="canRegister" :authUser="authUser" />

  <div class="bg-gray-50 py-10 md:py-14">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
      <div class="rounded-2xl border border-gray-200 bg-white p-6 md:p-7 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">My notifications</h1>
            <p class="mt-2 text-gray-700">Follow updates related to your appointments and profile.</p>
          </div>

          <div class="flex items-center gap-2">
            <Link
              :href="route('psychologist.appointments.index')"
              class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-white border border-gray-200 text-gray-700 text-sm font-semibold hover:bg-gray-50 transition"
            >
              Go to appointments
            </Link>
            <button
              type="button"
              class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-[#5997ac] text-white text-sm font-semibold hover:opacity-90 transition disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="stats.unread_count === 0 || markingAll"
              @click="markAllAsRead"
            >
              {{ markingAll ? 'Processing...' : 'Mark all as read' }}
            </button>
          </div>
        </div>

        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
            <div class="text-xs font-semibold text-gray-500">Total notifications</div>
            <div class="mt-1 text-lg font-bold text-gray-900">{{ stats.total_count || 0 }}</div>
          </div>
          <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3">
            <div class="text-xs font-semibold text-green-700">Unread</div>
            <div class="mt-1 text-lg font-bold text-green-900">{{ stats.unread_count || 0 }}</div>
          </div>
        </div>
      </div>

      <div v-if="(notifications.data || []).length === 0" class="bg-white border border-gray-200 rounded-2xl shadow-sm p-8 text-gray-700">
        <div class="text-lg font-semibold text-gray-900">No notifications found</div>
        <div class="mt-1 text-sm text-gray-600">You will see new psychologist updates here.</div>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="notification in notifications.data || []"
          :key="notification.id"
          class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden"
        >
          <div class="p-5 md:p-6 flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div class="min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <div class="text-base md:text-lg font-semibold text-gray-900 truncate">{{ notification.title }}</div>
                <span
                  class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold"
                  :class="notification.is_read ? 'bg-gray-100 text-gray-700' : 'bg-green-100 text-green-700'"
                >
                  {{ notification.is_read ? 'Read' : 'Unread' }}
                </span>
              </div>

              <div class="mt-2 text-sm text-gray-700">{{ notification.message }}</div>

              <div class="mt-3 text-xs text-gray-500 flex items-center gap-2">
                <span>#{{ notification.index_no }}</span>
                <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                <span>{{ notification.type }}</span>
                <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                <span>{{ notification.time_ago }}</span>
              </div>
              <div class="mt-1 text-xs text-gray-400">{{ formatDate(notification.created_at) }}</div>
            </div>

            <div class="flex items-center gap-2">
              <button
                v-if="!notification.is_read"
                type="button"
                class="inline-flex items-center justify-center rounded-lg bg-[#5997ac] px-3 py-1.5 text-xs font-medium text-white hover:opacity-90"
                @click="markAsRead(notification.id)"
              >
                Mark read
              </button>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-between border border-gray-200 rounded-2xl bg-white px-4 py-3">
          <div class="text-sm text-gray-600">
            Showing {{ notifications.from || 0 }}-{{ notifications.to || 0 }} of {{ notifications.total || 0 }}
          </div>

          <div class="flex items-center gap-2">
            <Link
              v-for="(link, i) in notifications.links || []"
              :key="i"
              :href="link.url || '#'"
              :class="linkClasses(link)"
              preserve-scroll
            >
              <span v-html="link.label"></span>
            </Link>
          </div>
        </div>
      </div>
    </div>
  </div>

  <Footer />
</template>
