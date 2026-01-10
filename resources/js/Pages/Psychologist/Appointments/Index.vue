<template>
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

        <h1 class="text-2xl font-semibold text-gray-900">Appointments</h1>
        <p class="text-sm text-gray-600">Manage your appointments. You can only cancel 24h+ before start time.</p>
      </div>

      <div class="flex items-center gap-3 w-full md:w-auto">
        <div class="flex items-center gap-2 flex-1">
          <select
            v-model="searchField"
            class="h-10 w-40 md:w-48 shrink-0 rounded-lg border-gray-300 bg-white px-3 text-sm text-gray-700"
            aria-label="Search filter"
          >
            <option value="id">ID</option>
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
              <td class="px-4 py-3 text-sm text-gray-700">#{{ a.id }}</td>
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
                  </div>

                  <div class="text-xs text-gray-400">
                    {{ guidanceText(a) }}
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
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
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import UserLayout from '@/Layouts/UserLayout.vue'
import SortIcon from '@/Pages/Admin/Psychologist/SortIcon.vue'
import Swal from 'sweetalert2'

defineOptions({ layout: UserLayout })

const props = defineProps({
  appointments: Object,
  status: { type: String, default: '' },
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

const searchField = ref('id')
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
  return status === 'confirmed' && !!a?.session_started_at
}

function canStartCall(a) {
  const status = normalizeStatus(a?.status)
  return status === 'confirmed' && !a?.session_started_at
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
</script>
