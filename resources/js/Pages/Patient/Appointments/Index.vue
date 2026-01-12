<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch, nextTick } from 'vue'
import Swal from 'sweetalert2'
import Navbar from '@/Components/Navbar.vue'
import Footer from '@/Components/Footer.vue'

const props = defineProps({
  canLogin: { type: Boolean },
  canRegister: { type: Boolean },
  authUser: { type: Object },
  status: { type: String, default: '' },
  appointments: { type: Array, default: () => [] },
})

const page = usePage()

const flashStatus = computed(() => props.status || page.props?.flash?.status || '')
const confirmingId = ref(null)
const cancelingId = ref(null)

// Pagination: server already returns recent-first; paginate when more than 5
const pageSize = ref(5)
const currentPage = ref(1)
const appointmentsTop = ref(null)

const appointmentsList = computed(() => props.appointments || [])

const totalPages = computed(() => Math.max(1, Math.ceil(appointmentsList.value.length / pageSize.value)))

const paginatedAppointments = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return appointmentsList.value.slice(start, start + pageSize.value)
})

watch(() => props.appointments, () => {
  currentPage.value = 1
})

// Scroll to top of appointments when the page changes
watch(currentPage, async () => {
  await nextTick()
  try {
    const el = appointmentsTop.value
    if (el && typeof el.scrollIntoView === 'function') {
      el.scrollIntoView({ behavior: 'smooth', block: 'start' })
    } else {
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
  } catch {
    // ignore
  }
})

const pagesToShow = computed(() => {
  const total = totalPages.value
  const current = currentPage.value
  const out = []
  if (total <= 7) {
    for (let i = 1; i <= total; i++) out.push(i)
    return out
  }
  out.push(1)
  const left = Math.max(2, current - 1)
  const right = Math.min(total - 1, current + 1)
  if (left > 2) out.push('left-ellipsis')
  for (let i = left; i <= right; i++) out.push(i)
  if (right < total - 1) out.push('right-ellipsis')
  out.push(total)
  return out
})

const toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 2200,
  timerProgressBar: true,
})

function formatDateTime(value) {
  if (!value) return ''
  try {
    const dt = new Date(value)
    return new Intl.DateTimeFormat(undefined, {
      weekday: 'short',
      year: 'numeric',
      month: 'short',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
    }).format(dt)
  } catch {
    return String(value)
  }
}

function formatDate(value) {
  if (!value) return ''
  try {
    const dt = new Date(value)
    return new Intl.DateTimeFormat(undefined, {
      weekday: 'long',
      year: 'numeric',
      month: 'short',
      day: '2-digit',
    }).format(dt)
  } catch {
    return String(value)
  }
}

function formatTime(value) {
  if (!value) return ''
  try {
    const dt = new Date(value)
    return new Intl.DateTimeFormat(undefined, {
      hour: '2-digit',
      minute: '2-digit',
    }).format(dt)
  } catch {
    return ''
  }
}

function durationMinutes(startValue, endValue) {
  try {
    const start = new Date(startValue)
    const end = new Date(endValue)
    const diff = Math.round((end.getTime() - start.getTime()) / 60000)
    return Number.isFinite(diff) && diff > 0 ? diff : null
  } catch {
    return null
  }
}

function formatDuration(startValue, endValue) {
  const mins = durationMinutes(startValue, endValue)
  if (!mins) return ''
  if (mins < 60) return `${mins} min`
  const h = Math.floor(mins / 60)
  const m = mins % 60
  return m ? `${h}h ${m}m` : `${h}h`
}

function statusAccentClass(status) {
  const s = String(status || '').toLowerCase()
  if (s === 'confirmed') return 'bg-blue-500'
  if (s === 'pending') return 'bg-yellow-400'
  if (s === 'completed') return 'bg-green-500'
  if (s === 'cancelled') return 'bg-red-500'
  if (s === 'no_show') return 'bg-red-500'
  return 'bg-gray-400'
}

function isCancelled(a) {
  return String(a?.status || '').toLowerCase() === 'cancelled'
}

function isUpcoming(a) {
  try {
    const start = new Date(a?.scheduled_start)
    return start.getTime() > Date.now() && ['pending', 'confirmed'].includes(String(a?.status || '').toLowerCase())
  } catch {
    return false
  }
}

function statusBadgeClass(status) {
  const s = String(status || '').toLowerCase()
  if (s === 'confirmed') return 'bg-blue-50 text-blue-700 ring-1 ring-blue-200'
  if (s === 'pending') return 'bg-yellow-50 text-yellow-800 ring-1 ring-yellow-200'
  if (s === 'completed') return 'bg-green-50 text-green-700 ring-1 ring-green-200'
  if (s === 'cancelled') return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
  if (s === 'no_show') return 'bg-red-50 text-red-700 ring-1 ring-red-200'
  return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
}

function statusLabel(status) {
  const s = String(status || '').toLowerCase()
  if (s === 'no_show') return 'Appointment missed'
  return s ? s.charAt(0).toUpperCase() + s.slice(1) : '—'
}

function missedByLabel(value) {
  if (!value) return ''
  const v = String(value).toLowerCase()
  if (v === 'patient') return 'Patient'
  if (v === 'psychologist') return 'Psychologist'
  return String(value)
}


function canPay(a) {
  return String(a?.status || '').toLowerCase() === 'pending'
}

function payAndConfirm(a) {
  if (!a?.id || !canPay(a)) return
  confirmingId.value = a.id

  router.patch(
    route('appointments.update', a.id),
    { status: 'confirmed' },
    {
      preserveScroll: true,
      onFinish: () => {
        confirmingId.value = null
      },
    }
  )
}

function canCancel(a) {
  return String(a?.status || '').toLowerCase() === 'pending'
}

function canJoinCall(a) {
  const s = String(a?.status || '').toLowerCase()
  const sessionStatus = String(a?.session_status || '').toLowerCase()
  if (s !== 'confirmed' || !a?.session_room_id || sessionStatus !== 'active') return false
  // Only allow join if within 15 minutes before scheduled_start or after.
  try {
    const start = new Date(a.scheduled_start)
    const now = new Date()
    // Allow join if now >= (start - 15min)
    return now.getTime() >= (start.getTime() - 15 * 60 * 1000)
  } catch {
    return false
  }
}

async function cancelAppointment(a) {
  if (!a?.id || !canCancel(a)) return

  const res = await Swal.fire({
    title: 'Cancel this appointment?',
    text: 'This action cannot be undone.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, cancel it',
    cancelButtonText: 'Keep it',
    reverseButtons: true,
    focusCancel: true,
  })

  if (!res.isConfirmed) return

  cancelingId.value = a.id
  router.delete(route('appointments.destroy', a.id), {
    preserveScroll: true,
    onSuccess: () => {
      toast.fire({ icon: 'success', title: 'Appointment cancelled' })
    },
    onError: () => {
      toast.fire({ icon: 'error', title: 'Could not cancel appointment' })
    },
    onFinish: () => {
      cancelingId.value = null
    },
  })
}
</script>

<template>
  <Head title="My appointments" />

  <Navbar :canLogin="canLogin" :canRegister="canRegister" :authUser="authUser" />

  <div class="bg-gray-50 py-10 md:py-14">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-8 rounded-2xl border border-gray-200 bg-white p-6 md:p-7 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">My appointments</h1>
            <p class="mt-2 text-gray-700 max-w-3xl">
              View your sessions, pay for pending bookings, and manage cancellations.
            </p>
          </div>

          <Link
            :href="route('services.consultation')"
            class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-white border border-gray-200 text-gray-700 text-sm font-semibold hover:bg-gray-50 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300"
          >
            Book a new one
          </Link>
        </div>

        <div class="mt-5 grid grid-cols-1 sm:grid-cols-4 gap-3">
          <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3">
            <div class="text-xs font-semibold text-gray-500">Total</div>
            <div class="mt-1 text-lg font-bold text-gray-900">{{ appointments.length }}</div>
          </div>
          <div class="rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-3">
            <div class="text-xs font-semibold text-yellow-700">Pending</div>
            <div class="mt-1 text-lg font-bold text-yellow-900">
              {{ appointments.filter((a) => String(a?.status || '').toLowerCase() === 'pending').length }}
            </div>
          </div>
          <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3">
            <div class="text-xs font-semibold text-blue-700">Confirmed</div>
            <div class="mt-1 text-lg font-bold text-blue-900">
              {{ appointments.filter((a) => String(a?.status || '').toLowerCase() === 'confirmed').length }}
            </div>
          </div>
          <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3">
            <div class="text-xs font-semibold text-green-700">Completed</div>
            <div class="mt-1 text-lg font-bold text-green-900">
              {{ appointments.filter((a) => String(a?.status || '').toLowerCase() === 'completed').length }}
            </div>
          </div>
        </div>
      </div>

      <div v-if="flashStatus" class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
        {{ flashStatus }}
      </div>

      <div v-if="!appointments.length" class="bg-white border border-gray-200 rounded-2xl shadow-sm p-8 text-gray-700">
        <div class="text-lg font-semibold text-gray-900">No appointments yet</div>
        <div class="mt-1 text-sm text-gray-600">When you book, your sessions will appear here.</div>
        <div class="mt-6">
          <Link
            :href="route('services.consultation')"
            class="inline-flex items-center justify-center px-5 py-2.5 rounded-lg bg-[#5997ac] text-white text-sm font-semibold hover:opacity-90 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#5997ac]/30"
          >
            Find a psychologist
          </Link>
        </div>
      </div>

      <div v-else class="space-y-4" ref="appointmentsTop">
        <TransitionGroup name="list" tag="div" class="space-y-4">
          <div
            v-for="a in paginatedAppointments"
            :key="a.id"
            class="group relative bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden transition hover:shadow-md"
            :class="[
              isUpcoming(a) ? 'ring-1 ring-gray-200' : '',
              isCancelled(a) ? 'border-red-200 ring-1 ring-red-200' : '',
            ]"
          >
            <div class="absolute left-0 top-0 h-full w-1.5" :class="statusAccentClass(a.status)" />
            <div class="p-5 md:p-6">
              <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                <div class="min-w-0">
                  <div class="flex items-center gap-2 flex-wrap">
                    <div class="text-lg font-semibold text-gray-900 truncate">
                      {{ a.psychologist_name || 'Psychologist' }}
                      
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold" :class="statusBadgeClass(a.status)">
                      {{ statusLabel(a.status) }}
                    </span>
                    <span v-if="a.missed_by" class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold text-red-700 bg-red-50 ring-1 ring-red-100">Missed by: {{ missedByLabel(a.missed_by) }}</span>
                    <span
                      v-if="isUpcoming(a)"
                      class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-white text-gray-700 ring-1 ring-gray-200"
                    >
                      Upcoming
                    </span>
                  </div>

                </div>

                <div class="flex items-center gap-2 md:justify-end">
                  <Link
                    v-if="String(a.status).toLowerCase() === 'confirmed' && String(a.session_status).toLowerCase() === 'active'"
                    :href="canJoinCall(a) ? route('appointments.video_call.show', a.id) : undefined"
                    class="inline-flex items-center gap-2 justify-center px-5 py-2.5 rounded-xl border-0 text-base font-bold shadow-md transition focus:outline-none focus:ring-2 focus:ring-offset-2"
                    :class="[
                      canJoinCall(a)
                        ? 'bg-gradient-to-r from-[#af5166] to-[#5997ac] text-white hover:from-[#af5166]/90 hover:to-[#5997ac]/90 focus:ring-[#af5166] scale-105 hover:scale-110 active:scale-100 cursor-pointer'
                        : 'bg-gray-200 text-gray-400 cursor-not-allowed pointer-events-none'
                    ]"
                    style="box-shadow: 0 4px 18px rgba(89,151,172,0.13);"
                    :aria-disabled="!canJoinCall(a)"
                    tabindex="0"
                  >
                    <svg v-if="canJoinCall(a)" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M15 10l4.553-2.276A1 1 0 0 1 21 8.618v6.764a1 1 0 0 1-1.447.894L15 14M4 6h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z"/></svg>
                    Join call
                  </Link>

                  <button
                    v-if="canPay(a)"
                    type="button"
                    @click="payAndConfirm(a)"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-[#af5166] text-white text-sm font-semibold hover:opacity-90 transition disabled:opacity-60 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#af5166]/30"
                    :disabled="confirmingId === a.id"
                  >
                    {{ confirmingId === a.id ? 'Processing…' : 'Pay & confirm' }}
                  </button>

                  <button
                    v-if="canCancel(a)"
                    type="button"
                    @click="cancelAppointment(a)"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-white border border-red-200 text-red-700 text-sm font-semibold hover:bg-red-50 transition disabled:opacity-60 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-200"
                    :disabled="cancelingId === a.id"
                  >
                    {{ cancelingId === a.id ? 'Cancelling…' : 'Cancel' }}
                  </button>
                </div>
              </div>

              <div class="mt-2 text-sm text-gray-700">
                <div class="mt-3 rounded-2xl border border-gray-200 bg-gray-50 -ml-1 -mr-3 md:-ml-2 md:-mr-4 px-4 md:px-5 py-4">
                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                      <div class="text-xs font-semibold text-gray-500">Date</div>
                      <div class="mt-1 text-sm font-semibold text-gray-900">{{ formatDate(a.scheduled_start) || '—' }}</div>
                      <div class="mt-0.5 text-xs text-gray-500">{{ formatDateTime(a.scheduled_start) ? 'Starts ' + formatTime(a.scheduled_start) : '' }}</div>
                    </div>

                    <div>
                      <div class="text-xs font-semibold text-gray-500">Time</div>
                      <div class="mt-1 inline-flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-sm font-semibold text-gray-900 ring-1 ring-gray-200">{{ formatTime(a.scheduled_start) || '—' }} – {{ formatTime(a.scheduled_end) || '—' }}</span>
                            <!-- duration badge removed per design -->
                      </div>
                      <div class="mt-1 text-xs text-gray-500">Local time</div>
                    </div>

                    <div>
                      <div class="text-xs font-semibold text-gray-500">Price</div>
                      <div class="mt-1">
                        <span v-if="a.price != null" class="inline-flex items-center rounded-full bg-white px-3 py-1 text-sm font-bold text-gray-900 ring-1 ring-gray-200">{{ Number(a.price).toFixed(2) }} {{ a.currency || 'TND' }}</span>
                        <span v-else class="text-sm font-semibold text-gray-900">—</span>
                      </div>
                      <div v-if="a.reference" class="mt-2 text-xs text-gray-500">Ref:
                        <span class="inline-flex items-center rounded-md bg-white px-2 py-0.5 font-mono text-[11px] text-gray-700 ring-1 ring-gray-200">{{ a.reference }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </TransitionGroup>
        <!-- Pagination controls -->
        <div v-if="appointmentsList.length > pageSize" class="mt-6 flex items-center justify-between">
          <div class="text-sm text-gray-600">Showing {{ (currentPage - 1) * pageSize + 1 }} - {{ Math.min(currentPage * pageSize, appointmentsList.length) }} of {{ appointmentsList.length }}</div>
          <div class="inline-flex items-center gap-2">
            <button
              @click="currentPage = Math.max(1, currentPage - 1)"
              :disabled="currentPage === 1"
              class="px-3 py-1 rounded-full bg-white border shadow-sm text-sm disabled:opacity-50"
              aria-label="Previous page"
            >
              ‹
            </button>

            <div class="inline-flex items-center gap-2">
              <template v-for="item in pagesToShow" :key="String(item)">
                <button
                  v-if="typeof item === 'number'"
                  @click="currentPage = item"
                  :aria-current="currentPage === item ? 'page' : null"
                  :class="[
                    'px-3 py-1 rounded-full text-sm shadow-sm border transition-all',
                    currentPage === item ? 'text-white' : 'text-gray-700 bg-white'
                  ]"
                  :style="currentPage === item ? { backgroundColor: 'rgb(89 151 172 / var(--tw-bg-opacity, 1))', boxShadow: '0 6px 18px rgba(89,151,172,0.18)', transform: 'translateY(-1px)' } : {}"
                >
                  {{ item }}
                </button>

                <span v-else class="text-gray-400 px-2">…</span>
              </template>
            </div>

            <button
              @click="currentPage = Math.min(totalPages, currentPage + 1)"
              :disabled="currentPage === totalPages"
              class="px-3 py-1 rounded-full bg-white border shadow-sm text-sm disabled:opacity-50"
              aria-label="Next page"
            >
              ›
            </button>
          </div>
        </div>
      </div>

      <div class="sm:hidden mt-8">
        <Link
          :href="route('services.consultation')"
          class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-white border border-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-100 transition"
        >
          Book a new one
        </Link>
      </div>
    </div>
  </div>

  <Footer />
</template>

<style scoped>
.list-enter-active,
.list-leave-active {
  transition: opacity 180ms ease, transform 180ms ease;
}

.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateY(8px);
}
</style>
