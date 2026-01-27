<template>
  <Head title="Appointments" />

  <Navbar :canLogin="canLogin" :canRegister="canRegister" :authUser="authUser || page.props?.auth?.user" />

  <div class="min-h-[calc(100vh-112px)] bg-gray-50">
    <div class="bg-gradient-to-r from-[#af5166] to-[#5997ac]">
      <div class="mx-auto max-w-6xl px-4 py-8">
        <h1 class="text-2xl sm:text-3xl font-semibold text-white">Appointments</h1>
        <p class="mt-1 text-sm text-white/90">Manage your appointments. You can only cancel 24h+ before start time.</p>
      </div>
    </div>

    <div class="mx-auto max-w-6xl px-4 py-8">
      <div class="p-6 space-y-6">
        <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
          <div>
            <div v-if="flashMessage" class="mb-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3">
              <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5 text-green-700 mt-0.5">
                    <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.61-1.814a.75.75 0 0 0-1.22-.872l-3.236 4.53-1.784-1.784a.75.75 0 1 0-1.06 1.06l2.4 2.4a.75.75 0 0 0 1.14-.094l3.76-5.24Z" clip-rule="evenodd" />
                  </svg>
                  <div>
                    <div class="text-sm font-medium text-green-800">Success</div>
                    <div class="text-sm text-green-800">{{ flashMessage }}</div>
                  </div>
                </div>
                <button type="button" @click="clearFlash" class="text-green-700/70 hover:text-green-800" aria-label="Dismiss">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </button>
              </div>
            </div>

            <div v-if="flashError" class="mb-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
              <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5 text-red-700 mt-0.5">
                    <path fill-rule="evenodd" d="M12 2.25c5.385 0 9.75 4.365 9.75 9.75S17.385 21.75 12 21.75 2.25 17.385 2.25 12 6.615 2.25 12 2.25Zm.75 6a.75.75 0 0 0-1.5 0V12a.75.75 0 0 0 1.5 0V8.25ZM12 15.75a.75.75 0 1 0 0 1.5.75.75 0 0 0 0-1.5Z" clip-rule="evenodd" />
                  </svg>
                  <div>
                    <div class="text-sm font-medium text-red-800">Not allowed</div>
                    <div class="text-sm text-red-800">{{ flashError }}</div>
                  </div>
                </div>
                <button type="button" @click="clearError" class="text-red-700/70 hover:text-red-800" aria-label="Dismiss">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </header>

        <div class="flex items-center gap-3 w-full md:w-auto">
          <div class="flex items-center gap-2 flex-1">
          <select
            v-model="searchField"
            class="h-10 w-40 md:w-48 shrink-0 rounded-lg border-gray-300 bg-white px-3 text-sm text-gray-700"
            aria-label="Search filter"
          >
            <option value="patient">Patient</option>
            <option value="date">Date</option>
          </select>

            <div class="relative flex-1 md:w-80">
              <input
                v-if="searchField !== 'date'"
                v-model="searchQuery"
                type="text"
                :placeholder="searchPlaceholder"
                class="w-full rounded-lg border-gray-300 pl-10 pr-3 py-2"
              />

              <input
                v-else
                v-model="searchDate"
                type="date"
                class="w-full rounded-lg border-gray-300 pl-10 pr-10 py-2"
                aria-label="Search date"
              />

              <button
                v-if="searchField === 'date' && searchDate"
                type="button"
                @click="searchDate = ''"
                class="absolute right-2 top-1/2 -translate-y-1/2 inline-flex items-center justify-center h-7 w-7 rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                aria-label="Clear date"
                title="Clear"
              >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>

              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                <path
                  fill-rule="evenodd"
                  d="M12.9 14.32a8 8 0 111.414-1.414l4.387 4.387a1 1 0 01-1.414 1.414l-4.387-4.387zM14 8a6 6 0 11-12 0 6 6 0 0112 0z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow overflow-hidden">
          <div class="overflow-x-auto">
            <template v-if="data.length">
              <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <!-- ID column removed intentionally -->
                  <th class="px-4 py-3 text-left">
                    <button type="button" @click="toggleSort('patient')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                      Patient
                      <SortIcon :active="sortKey === 'patient'" :dir="sortDir" />
                    </button>
                  </th>
                  <th class="px-4 py-3 text-left">
                    <button type="button" @click="toggleSort('scheduled_start')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                      Date
                      <SortIcon :active="sortKey === 'scheduled_start'" :dir="sortDir" />
                    </button>
                  </th>
                  <th class="px-4 py-3 text-left">
                    <button type="button" @click="toggleSort('status')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                      Status
                      <SortIcon :active="sortKey === 'status'" :dir="sortDir" />
                    </button>
                  </th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>

              <tbody class="bg-white divide-y divide-gray-200">
                <tr
                  v-for="a in sorted"
                  :key="a.id"
                  class="hover:bg-gray-50"
                  :class="normalizeStatus(a.status) === 'cancelled' ? 'bg-red-50/30' : ''"
                >
                  <!-- ID cell removed -->
                  <td class="px-4 py-3">
                    <div class="text-sm font-medium text-gray-900">{{ a.patient?.name || '—' }}</div>
                  </td>
                  <td class="px-4 py-3">
                    <div class="text-sm font-medium text-gray-900">{{ formatDate(a.scheduled_start) }}</div>
                    <div class="text-xs text-gray-500">{{ formatTime(a.scheduled_start) }} – {{ formatTime(a.scheduled_end) }}</div>
                  </td>
                  <td class="px-4 py-3">
                    <div class="flex items-center gap-2 flex-wrap">
                      <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium" :class="appointmentBadge(a.status)">
                        {{ appointmentLabel(a.status) }}
                      </span>
                    </div>
                    <div v-if="normalizeStatus(a.status) === 'cancelled'" class="mt-1 text-xs text-gray-500">
                      Cancelled by: {{ a.canceled_by || '—' }}<span v-if="a.canceled_by_user_id"> (user #{{ a.canceled_by_user_id }})</span>
                    </div>
                    <div v-if="normalizeStatus(a.status) === 'cancelled' && a.cancellation_reason" class="mt-1 text-xs text-gray-500">
                      Reason: {{ a.cancellation_reason }}
                    </div>
                  </td>
                  <td class="px-4 py-3 text-right">
                    <div class="flex flex-col items-end gap-2">
                      <div class="inline-flex flex-wrap items-center justify-end gap-2">
                        <Link
                          v-if="canJoinRoom(a)"
                          :href="route('appointments.video_call.show', a.id)"
                          class="inline-flex items-center justify-center h-8 px-2.5 rounded-lg border text-xs font-medium border-gray-200 bg-white text-gray-700 hover:bg-gray-50"
                          title="Join video call"
                        >
                          Join room
                        </Link>

                        <button
                          v-else-if="canStartCall(a)"
                          type="button"
                          @click="startCall(a)"
                          :disabled="startingCallId === a.id"
                          class="inline-flex items-center justify-center h-8 px-2.5 rounded-lg border text-xs font-medium disabled:opacity-50 disabled:cursor-not-allowed border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100"
                          title="Start video call"
                        >
                          {{ startingCallId === a.id ? 'Starting…' : 'Start call' }}
                        </button>

                        <button
                          v-if="canCancelAppointment(a)"
                          type="button"
                          @click="cancelWithReason(a)"
                          :disabled="savingId === a.id"
                          class="inline-flex items-center justify-center h-8 px-2.5 rounded-lg border text-xs font-medium disabled:opacity-50 disabled:cursor-not-allowed border-red-200 bg-red-50 text-red-700 hover:bg-red-100"
                          title="Cancel appointment"
                        >
                          Cancel
                        </button>

                        <button
                          v-else-if="showCancelDisabled(a)"
                          type="button"
                          disabled
                          class="inline-flex items-center justify-center h-8 px-2.5 rounded-lg border text-xs font-medium opacity-60 cursor-not-allowed border-gray-200 bg-gray-50 text-gray-600"
                          title="You can only cancel at least 24 hours before start"
                        >
                          Cancel
                        </button>
                        <button
                          v-if="normalizeStatus(a.status) === 'completed'"
                          type="button"
                          @click="onNotesClick(a, $event)"
                          class="notes-btn inline-flex items-center gap-2 h-9 px-3 rounded-lg text-xs font-medium border shadow-sm transition duration-150 hover:shadow-md"
                          style="border-color: rgb(89 151 172 / var(--tw-bg-opacity, 1)); color: rgb(89 151 172 / var(--tw-bg-opacity, 1));"
                          title="Edit session notes"
                          >
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" :style="{ color: 'inherit' }">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5l3 3L12 15l-4 1 1-4 9.5-9.5z" />
                          </svg>
                          Notes
                        </button>
                        <!-- Psychologist report patient button -->
                        <button
                          v-if="currentUser && String(currentUser.role || '').toUpperCase() === 'PSYCHOLOGIST'"
                          @click.prevent="openReportForPatient(a)"
                          class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-white text-red-600 shadow border border-gray-100 hover:scale-105 transition ml-2"
                          title="Report patient"
                          aria-label="Report patient"
                        >
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" aria-hidden="true">
                            <path class="fill-current" d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                            <rect x="10.5" y="7" width="3" height="7" rx="0.6" class="fill-white stroke-red-600" stroke-width="0.9" />
                            <circle class="fill-white stroke-red-600" cx="12" cy="16.5" r="1.4" stroke-width="0.9" />
                          </svg>
                        </button>
                      </div>

                      <div class="text-xs text-gray-400">
                        {{ guidanceText(a) }}
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
                </table>
              </template>

              <div v-else class="p-8 text-center text-gray-500">
                <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <div class="mt-4 text-lg font-medium">No appointments yet</div>
                <div class="mt-1 text-sm">You don't have any appointments scheduled at the moment.</div>
              </div>
          </div>

          <div class="flex items-center justify-between px-4 py-3 border-t border-gray-200">
            <div class="text-sm text-gray-600">Showing {{ appointments.from }}-{{ appointments.to }} of {{ appointments.total }}</div>
            <div class="flex items-center gap-2">
              <Link v-for="(link, i) in appointments.links" :key="i" :href="link.url || '#'" :class="linkClasses(link)" preserve-scroll>
                <span v-html="link.label"></span>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <VideoCallSessionNotes ref="notesRef" />
  <ReportModal :show="showReportModal" :profile="reportProfile" :authUser="currentUser" reported-role="patient" @close="showReportModal=false" @sent="() => { showReportModal=false }" />
</template>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import Navbar from '@/Components/Navbar.vue'
import SortIcon from '@/Pages/Admin/Psychologist/SortIcon.vue'
import Swal from 'sweetalert2'
import VideoCallSessionNotes from '@/Components/VideoCall/VideoCallSessionNotes.vue'
import ReportModal from '@/Components/ReportModal.vue'

const props = defineProps({
  appointments: Object,
  status: { type: String, default: '' },
  canLogin: Boolean,
  canRegister: Boolean,
  authUser: Object,
})

const page = usePage()
const flashMessage = ref('')
const flashError = ref('')

let flashTimer = null
let errorTimer = null

function showFlash(message) {
  flashMessage.value = message || ''
  if (flashTimer) {
    clearTimeout(flashTimer)
    flashTimer = null
  }
  if (!flashMessage.value) return

  flashTimer = setTimeout(() => {
    flashMessage.value = ''
    flashTimer = null
  }, 5000)
}

function showError(message) {
  flashError.value = message || ''
  if (errorTimer) {
    clearTimeout(errorTimer)
    errorTimer = null
  }
  if (!flashError.value) return

  errorTimer = setTimeout(() => {
    flashError.value = ''
    errorTimer = null
  }, 5000)
}

showFlash(props.status || page.props?.flash?.status || '')
showError(page.props?.flash?.error || '')

watch(
  () => props.status,
  (next) => {
    if (next) showFlash(next)
  }
)

watch(
  () => page.props?.flash?.status,
  (next) => {
    if (next) showFlash(next)
  }
)

watch(
  () => page.props?.flash?.error,
  (next) => {
    if (next) showError(next)
  }
)

function clearFlash() {
  showFlash('')
}

function clearError() {
  showError('')
}

const data = computed(() => props.appointments?.data || [])

const searchField = ref('patient')
const searchQuery = ref('')
const searchDate = ref('')

watch(searchField, () => {
  searchQuery.value = ''
  searchDate.value = ''
})

const searchPlaceholder = computed(() => {
  switch (searchField.value) {
    case 'id':
      return 'Search by ID...'
    case 'patient':
      return 'Search by patient name...'
    default:
      return 'Search...'
  }
})

const filtered = computed(() => {
  const list = data.value || []

  if (searchField.value === 'date') {
    const d = String(searchDate.value || '').trim()
    if (!d) return list

    return list.filter((a) => {
      const start = a?.scheduled_start
      if (!start) return false
      try {
        const iso = new Date(start).toISOString().slice(0, 10)
        return iso === d
      } catch {
        return false
      }
    })
  }

  const q = searchQuery.value.trim().toLowerCase()
  if (!q) return list

  return list.filter((a) => {
    if (searchField.value === 'id') {
      return String(a?.id ?? '').toLowerCase().includes(q)
    }

    if (searchField.value === 'patient') {
      return String(a?.patient?.name ?? '').toLowerCase().includes(q)
    }

    return [String(a?.id ?? ''), String(a?.patient?.name ?? '')].join(' ').toLowerCase().includes(q)
  })
})

const sortKey = ref('scheduled_start')
const sortDir = ref('desc')

function toggleSort(key) {
  if (sortKey.value === key) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
    return
  }
  sortKey.value = key
  sortDir.value = 'asc'
}

function getSortValue(a, key) {
  switch (key) {
    case 'id':
      return Number(a?.id || 0)
    case 'patient':
      return String(a?.patient?.name || '').toLowerCase()
    case 'scheduled_start':
      try {
        return new Date(a?.scheduled_start || 0).getTime() || 0
      } catch {
        return 0
      }
    case 'status':
      return String(a?.status || '').toLowerCase()
    default:
      return ''
  }
}

const sorted = computed(() => {
  const list = filtered.value || []
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
    .map((x) => x.item)
})

const savingId = ref(null)
const startingCallId = ref(null)
const notesRef = ref(null)

// Report modal for reporting patients
const showReportModal = ref(false)
const reportProfile = ref(null)
const currentUser = computed(() => props.authUser || page.props?.auth?.user)

function openReportForPatient(a) {
  if (!a) return
  const candidate = a.patient_profile || a.patient || null
  if (candidate && candidate.id) {
    reportProfile.value = candidate
  } else {
    reportProfile.value = {
      id: a.patient_id || a.patient_profile_id || null,
      user: { name: a.patient?.name || a.patient_name || 'Patient' },
      first_name: a.patient?.first_name || (a.patient_name ? a.patient_name.split(' ')[0] : null) || null,
      last_name: a.patient?.last_name || '',
    }
  }
  showReportModal.value = true
}

function formatDate(value) {
  if (!value) return '—'
  try {
    return new Intl.DateTimeFormat(undefined, { year: 'numeric', month: 'short', day: '2-digit' }).format(new Date(value))
  } catch {
    return String(value)
  }
}

function formatTime(value) {
  if (!value) return '—'
  try {
    return new Intl.DateTimeFormat(undefined, { hour: '2-digit', minute: '2-digit' }).format(new Date(value))
  } catch {
    return '—'
  }
}

function normalizeStatus(value) {
  return String(value || '').trim().toLowerCase()
}

function appointmentLabel(status) {
  const s = normalizeStatus(status)
  if (!s) return '—'
  if (s === 'no_show') return 'missed'
  return s.charAt(0).toUpperCase() + s.slice(1)
}

function appointmentBadge(status) {
  const s = normalizeStatus(status)
  if (s === 'pending') return 'bg-yellow-50 text-yellow-800 ring-1 ring-yellow-200'
  if (s === 'confirmed') return 'bg-blue-50 text-blue-700 ring-1 ring-blue-200'
  if (s === 'completed') return 'bg-green-50 text-green-700 ring-1 ring-green-200'
  if (s === 'cancelled') return 'bg-red-50 text-red-700 ring-1 ring-red-200'
  if (s === 'no_show') return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
  return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
}

function canCancelAppointment(a) {
  const status = normalizeStatus(a?.status)
  if (!['pending', 'confirmed'].includes(status)) return false
  if (a?.can_cancel === true) return true
  return false
}

function canJoinRoom(a) {
  const status = normalizeStatus(a?.status)
  return status === 'confirmed' && !!a?.session_room_id
}

function canStartCall(a) {
  const status = normalizeStatus(a?.status)
  return status === 'confirmed' && !a?.session_room_id
}

function startCall(a) {
  if (!a?.id || !canStartCall(a)) return
  if (startingCallId.value) return

  startingCallId.value = a.id
  router.post(
    route('psychologist.appointments.video_call.start', a.id),
    {},
    {
      preserveScroll: true,
      onFinish: () => {
        startingCallId.value = null
      },
    }
  )
}

// Minimal fetch helper for JSON API calls (includes cookies)
async function fetchJson(url, options = {}) {
  const getMetaCsrfToken = () => {
    try { return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' } catch { return '' }
  }
  const getXsrfCookieToken = () => {
    try { const m = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]+)/); return m && m[1] ? decodeURIComponent(m[1]) : '' } catch { return '' }
  }

  const doFetch = async () => {
    const isWrite = Boolean(options.method && options.method !== 'GET')
    const headers = {
      'X-Requested-With': 'XMLHttpRequest',
      Accept: 'application/json',
      ...(options.headers || {}),
    }
    const meta = getMetaCsrfToken()
    const cookieToken = getXsrfCookieToken()
    if (isWrite && meta) headers['X-CSRF-TOKEN'] = meta
    if (isWrite && cookieToken) headers['X-XSRF-TOKEN'] = cookieToken
    // Ensure JSON bodies are correctly parsed by Laravel
    if (isWrite && options.body && !headers['Content-Type']) headers['Content-Type'] = 'application/json'

    return fetch(url, { credentials: 'include', headers, ...options })
  }

  const parse = async (res) => {
    const text = await res.text()
    let data = null
    try { data = text ? JSON.parse(text) : null } catch { data = null }
    return { res, data }
  }

  let first = await doFetch()
  let { res, data } = await parse(first)

  if (res.status === 419) {
    try { await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' }) } catch {}
    try { await new Promise((r) => setTimeout(r, 60)) } catch {}
    const second = await doFetch()
    ;({ res, data } = await parse(second))
  }

  if (!res.ok) {
    const msg = data?.message || `Request failed (${res.status})`
    throw new Error(msg)
  }

  return data
}

// Open and edit (or create) session notes for an appointment
async function openEditNotes(a) {
  if (!a?.id) return
  try {
    // Always fetch the latest session for the appointment
    const sessionResp = await fetchJson(`/appointments/${a.id}/session`, { method: 'GET' })
    const session = sessionResp?.session || null
    if (!session) {
      Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: 'No session found for this appointment.', showConfirmButton: false, timer: 3000 })
      return
    }

    // Try to fetch an existing note; if not found proceed to create
    let note = null
    try {
      const data = await fetchJson(`/appointments/${a.id}/session-note`, { method: 'GET' })
      note = data?.note || null
    } catch (err) {
      note = null
    }

    const payload = await notesRef.value
      ? await notesRef.value.open({
          appointment_session_id: session.id,
          session_date: (note && note.session_date) || session.started_at,
          session_duration: (note && note.session_duration) || session.duration_minutes || 0,
          session_mode: (note && note.session_mode) || 'video_audio',
          risk_level: (note && note.risk_level) || 'none',
          subjective: (note && note.subjective) || '',
          objective: (note && note.objective) || '',
          assessment: (note && note.assessment) || '',
          intervention: (note && note.intervention) || '',
          plan: (note && note.plan) || '',
        })
      : null

    if (!payload) return

    // If a note exists, PATCH; otherwise create with POST
    if (note && note.id) {
      await fetchJson(`/appointment-session-notes/${note.id}`, {
        method: 'PATCH',
        body: JSON.stringify(payload),
      })
      Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Session notes updated', showConfirmButton: false, timer: 3000 })
    } else {
      await fetchJson(`/appointment-session-notes`, {
        method: 'POST',
        body: JSON.stringify(payload),
      })
      Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Session notes created', showConfirmButton: false, timer: 3000 })
    }
  } catch (e) {
    console.error('Failed opening/updating notes', e)
    const msg = e?.message || 'Failed to load or save session note'
    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: msg, showConfirmButton: false, timer: 4000 })
  }
}

function showCancelDisabled(a) {
  const status = normalizeStatus(a?.status)
  if (!['pending', 'confirmed'].includes(status)) return false
  if (a?.can_cancel === false) return true
  return false
}

function guidanceText(a) {
  const s = normalizeStatus(a?.status)
  if (s === 'pending' || s === 'confirmed') {
    return a?.can_cancel ? 'You can cancel (24h+ before start).' : 'Cancellation allowed only 24h+ before start.'
  }
  if (s === 'completed') return 'Completed is final.'
  if (s === 'no_show') return 'Missed is final.'
  if (s === 'cancelled') return 'Cancelled is final.'
  return '—'
}

async function cancelWithReason(a) {
  if (!a || savingId.value === a.id) return

  const presetReasons = [
    { value: 'schedule_conflict', label: 'Schedule conflict' },
    { value: 'personal_emergency', label: 'Personal emergency' },
    { value: 'technical_issue', label: 'Technical issue' },
    { value: 'other', label: 'Other' },
  ]

  const result = await Swal.fire({
    title: 'Cancel appointment?',
    html: `
      <div style="text-align:left;max-width:520px;margin:0 auto">
        <div style="margin-bottom:8px">Appointment #${a?.id ?? ''} for patient ${a?.patient?.name || 'patient'}</div>
        <label for="swal-reason" style="display:block;margin:10px 0 6px;font-weight:600">Cancellation reason</label>
        <select id="swal-reason" class="swal2-input" style="margin:0 auto;width:100%;max-width:520px">
          ${presetReasons
            .map((r) => `<option value="${r.value}">${r.label}</option>`)
            .join('')}
        </select>

        <div id="swal-other-wrap" style="display:none">
          <label for="swal-other" style="display:block;margin:10px 0 6px;font-weight:600">Other reason</label>
          <textarea id="swal-other" class="swal2-textarea" style="margin:0 auto;width:100%;max-width:520px" maxlength="255" placeholder="Write the reason..."></textarea>
        </div>
      </div>
    `,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, cancel',
    cancelButtonText: 'Keep',
    reverseButtons: true,
    focusCancel: true,
    confirmButtonColor: 'rgb(141,61,79)',
    didOpen: () => {
      const select = document.getElementById('swal-reason')
      const wrap = document.getElementById('swal-other-wrap')
      const other = document.getElementById('swal-other')
      const toggle = () => {
        const isOther = select?.value === 'other'
        if (wrap) wrap.style.display = isOther ? 'block' : 'none'
        if (!isOther && other) other.value = ''
      }
      if (select) select.addEventListener('change', toggle)
      toggle()
    },
    preConfirm: () => {
      const select = document.getElementById('swal-reason')
      const other = document.getElementById('swal-other')

      const selected = String(select?.value || '').trim()
      if (!selected) {
        Swal.showValidationMessage('Please select a cancellation reason.')
        return false
      }

      if (selected === 'other') {
        const v = String(other?.value || '').trim()
        if (!v) {
          Swal.showValidationMessage('Please write the cancellation reason.')
          return false
        }
        return v
      }

      const label = presetReasons.find((r) => r.value === selected)?.label
      return label || selected
    },
  })

  if (!result.isConfirmed) return

  savingId.value = a.id
  router.patch(
    route('psychologist.appointments.cancel', a.id),
    { cancellation_reason: String(result.value || '').trim() },
    {
      preserveScroll: true,
      onError: (errors) => {
        const msg = errors?.cancellation_reason || errors?.status
        if (msg) showError(msg)
      },
      onFinish: () => {
        savingId.value = null
      },
    }
  )
}

function linkClasses(link) {
  const base = 'inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-sm border '
  if (link.active) return base + 'bg-indigo-600 text-white border-indigo-600'
  if (!link.url) return base + 'bg-gray-50 text-gray-400 border-gray-200 cursor-not-allowed'
  return base + 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'
}

// Notes button click animation: briefly add a class then open modal
function onNotesClick(a, ev) {
  try {
    const btn = ev?.currentTarget || ev?.target
    if (btn && btn.classList) {
      btn.classList.remove('btn-click-anim')
      // force reflow to restart animation
      // eslint-disable-next-line no-unused-expressions
      void btn.offsetWidth
      btn.classList.add('btn-click-anim')
      const cleanup = () => {
        btn.classList.remove('btn-click-anim')
        btn.removeEventListener('animationend', cleanup)
      }
      btn.addEventListener('animationend', cleanup)
    }
  } catch (err) {
    // ignore animation errors
  }

  // still perform the original action
  openEditNotes(a)
}
</script>

<style scoped>
.notes-btn { will-change: background-color, color, border-color; }
.notes-btn:hover { background-color: rgb(89 151 172 / var(--tw-bg-opacity, 1)); border-color: rgb(89 151 172 / var(--tw-bg-opacity, 1)); color: white !important; }
.notes-btn:hover svg { color: white !important; }
.btn-click-anim { animation: btnClick 240ms cubic-bezier(.2,.9,.2,1); }
@keyframes btnClick {
  0% { transform: scale(1); box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
  30% { transform: scale(0.97); box-shadow: 0 6px 18px rgba(0,0,0,0.08); }
  100% { transform: scale(1); box-shadow: 0 4px 10px rgba(0,0,0,0.06); }
}
</style>
