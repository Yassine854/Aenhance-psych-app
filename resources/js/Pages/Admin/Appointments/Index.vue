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
        <p class="text-sm text-gray-600">Review all appointments and manage status changes with guidance.</p>
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
            <option value="psychologist">Psychologist</option>
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
                <button type="button" @click="toggleSort('psychologist')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                  Psychologist
                  <SortIcon :active="sortKey === 'psychologist'" :dir="sortDir" />
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
                  Appointment
                  <SortIcon :active="sortKey === 'status'" :dir="sortDir" />
                </button>
              </th>
              <th class="px-4 py-3 text-left">
                <button type="button" @click="toggleSort('payment_status')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                  Payment
                  <SortIcon :active="sortKey === 'payment_status'" :dir="sortDir" />
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
              :class="String(a.status || '').toLowerCase() === 'cancelled' ? 'bg-red-50/30' : ''"
            >
              <td class="px-4 py-3 text-sm text-gray-700">#{{ a.id }}</td>
              <td class="px-4 py-3">
                <div class="text-sm font-medium text-gray-900">{{ a.patient?.name || '—' }}</div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm font-medium text-gray-900">{{ a.psychologist?.name || '—' }}</div>
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
                  <span
                    v-if="a.price != null && !['cancelled', 'no_show'].includes(String(a.status || '').toLowerCase())"
                    class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-gray-100 text-gray-800"
                  >
                    {{ Number(a.price).toFixed(2) }} {{ a.currency || 'TND' }}
                  </span>
                </div>
                <div v-if="String(a.status || '').toLowerCase() === 'cancelled'" class="mt-1 text-xs text-gray-500">
                  Cancelled by: {{ a.canceled_by || '—' }}<span v-if="a.canceled_by_user_id"> (user #{{ a.canceled_by_user_id }})</span>
                </div>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2 flex-wrap">
                  <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium" :class="paymentBadge(a.payment?.status)">
                    {{ paymentLabel(a.payment?.status) }}
                  </span>
                  <span v-if="a.payment?.provider" class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-gray-100 text-gray-800">
                    {{ a.payment.provider }}
                  </span>
                </div>
              </td>
              <td class="px-4 py-3 text-right">
                <div class="flex flex-col items-end gap-2">
                  <button
                    type="button"
                    title="View"
                    @click="openShow(a)"
                    class="inline-flex items-center justify-center h-9 w-9 rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                      <path d="M12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z" />
                      <path fill-rule="evenodd" d="M12 3c5.392 0 9.878 3.88 10.818 9-.94 5.12-5.426 9-10.818 9S2.122 17.12 1.182 12C2.122 6.88 6.608 3 12 3Zm0 15a6 6 0 0 0 6-6 6 6 0 0 0-12 0 6 6 0 0 0 6 6Z" clip-rule="evenodd" />
                    </svg>
                  </button>

                  <div v-if="appointmentActions(a).length" class="inline-flex flex-wrap items-center justify-end gap-2">
                    <button
                      v-for="act in appointmentActions(a)"
                      :key="act.value"
                      type="button"
                      @click="applyUpdate(a, { appointment_status: act.value })"
                      :disabled="savingId === a.id"
                      class="inline-flex items-center justify-center h-8 px-2.5 rounded-lg border text-xs font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                      :class="act.classes"
                      :title="act.title"
                    >
                      <span>{{ act.label }}</span>
                    </button>
                  </div>

                  <div v-if="paymentActions(a).length" class="inline-flex flex-wrap items-center justify-end gap-2">
                    <button
                      v-for="act in paymentActions(a)"
                      :key="act.value"
                      type="button"
                      @click="applyUpdate(a, { payment_status: act.value, appointment_status: act.appointment_status || null })"
                      :disabled="savingId === a.id"
                      class="inline-flex items-center justify-center h-8 px-2.5 rounded-lg border text-xs font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                      :class="act.classes"
                      :title="act.title"
                    >
                      <span>{{ act.label }}</span>
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

    <Show :show="modal === 'show'" :appointment="selected" @close="closeModal" />
  </div>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import SortIcon from '@/Pages/Admin/Psychologist/SortIcon.vue'
import Swal from 'sweetalert2'
import Show from './Show.vue'

defineOptions({ layout: AdminLayout })

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

// initialize from first render
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

// IMPORTANT: derive rows directly from Inertia props so the table refreshes immediately
// after an update (redirect back) without needing any manual syncing.
const data = computed(() => props.appointments?.data || [])

const searchField = ref('id')
const searchQuery = ref('')
const searchDate = ref('')

const modal = ref('')
const selected = ref(null)

function openShow(a) {
  selected.value = a
  modal.value = 'show'
}

function closeModal() {
  modal.value = ''
  selected.value = null
}

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
    case 'psychologist':
      return 'Search by psychologist name...'
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
      const hay = String(a?.patient?.name ?? '').toLowerCase()
      return hay.includes(q)
    }

    if (searchField.value === 'psychologist') {
      const hay = String(a?.psychologist?.name ?? '').toLowerCase()
      return hay.includes(q)
    }

    const hay = [
      String(a?.id ?? ''),
      String(a?.patient?.name ?? ''),
      String(a?.psychologist?.name ?? ''),
    ]
      .join(' ')
      .toLowerCase()
    return hay.includes(q)
  })
})

// Sorting (client-side, applies after search)
const sortKey = ref('scheduled_start')
const sortDir = ref('desc') // 'asc' | 'desc'

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
    case 'psychologist':
      return String(a?.psychologist?.name || '').toLowerCase()
    case 'scheduled_start':
      try {
        return new Date(a?.scheduled_start || 0).getTime() || 0
      } catch {
        return 0
      }
    case 'status':
      return String(a?.status || '').toLowerCase()
    case 'payment_status':
      return String(a?.payment?.status || '').toLowerCase()
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

function appointmentLabel(status) {
  const s = String(status || '').toLowerCase()
  if (!s) return '—'
  if (s === 'no_show') return 'missed'
  return s.charAt(0).toUpperCase() + s.slice(1)
}

function appointmentBadge(status) {
  const s = String(status || '').toLowerCase()
  if (s === 'pending') return 'bg-yellow-50 text-yellow-800 ring-1 ring-yellow-200'
  if (s === 'confirmed') return 'bg-blue-50 text-blue-700 ring-1 ring-blue-200'
  if (s === 'completed') return 'bg-green-50 text-green-700 ring-1 ring-green-200'
  if (s === 'cancelled') return 'bg-red-50 text-red-700 ring-1 ring-red-200'
  if (s === 'no_show') return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
  return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
}

function paymentLabel(status) {
  const s = String(status || '').toLowerCase()
  if (!s) return 'No payment'
  if (s === 'paid') return 'Paid'
  if (s === 'pending') return 'Pending'
  if (s === 'failed') return 'Failed'
  if (s === 'refunded') return 'Refunded'
  return s
}

function paymentBadge(status) {
  const s = String(status || '').toLowerCase()
  if (!s) return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
  if (s === 'paid') return 'bg-green-50 text-green-700 ring-1 ring-green-200'
  if (s === 'pending') return 'bg-yellow-50 text-yellow-800 ring-1 ring-yellow-200'
  if (s === 'failed') return 'bg-red-50 text-red-700 ring-1 ring-red-200'
  if (s === 'refunded') return 'bg-blue-50 text-blue-700 ring-1 ring-blue-200'
  return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
}

function normalizeStatus(value) {
  return String(value || '').trim().toLowerCase()
}

function effectiveAppointmentStatus(a) {
  return normalizeStatus(a?.status)
}

function currentPaymentStatus(a) {
  return normalizeStatus(a?.payment?.status)
}

function appointmentOptions(a) {
  const s = normalizeStatus(a?.status)
  if (s === 'pending') return [{ value: 'confirmed', label: 'Confirmed' }, { value: 'cancelled', label: 'Cancelled' }]
  if (s === 'confirmed') return [{ value: 'completed', label: 'Completed' }, { value: 'no_show', label: 'No-show' }, { value: 'cancelled', label: 'Cancelled' }]
  return []
}

function paymentOptions(a) {
  const appt = effectiveAppointmentStatus(a)
  const pay = currentPaymentStatus(a)

  // Terminal/strict modes
  if (appt === 'cancelled') {
    if (pay === 'paid') return [{ value: 'refunded', label: 'Refunded' }]
    return []
  }

  if (appt === 'pending') {
    return [
      { value: 'pending', label: 'Pending' },
      { value: 'paid', label: 'Paid' },
      { value: 'failed', label: 'Failed' },
    ]
  }

  if (appt === 'confirmed' || appt === 'completed' || appt === 'no_show') {
    // keep it logical: these states should be paid (admin can mark paid if missing)
    if (pay === 'paid' || !pay) return [{ value: 'paid', label: 'Paid' }]
    return [{ value: 'paid', label: 'Paid' }]
  }

  return []
}

function guidanceText(a) {
  const s = normalizeStatus(a?.status)
  const pay = currentPaymentStatus(a)

  if (s === 'pending') return 'Next: confirm or cancel. Payment can be paid/failed.'
  if (s === 'confirmed') return 'Next: complete, missed, or cancel. Payment should be paid.'
  if (s === 'completed') return 'Completed is final.'
  if (s === 'no_show') return pay === 'paid' ? 'Missed is final. You may refund.' : 'Missed is final.'
  if (s === 'cancelled') return pay === 'paid' ? 'Cancelled is final. You may refund.' : 'Cancelled is final.'
  return '—'
}

function appointmentActions(a) {
  const s = normalizeStatus(a?.status)
  if (s === 'pending') {
    return [
      { value: 'confirmed', label: 'Confirm', title: 'Confirm appointment', classes: 'border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100' },
      { value: 'cancelled', label: 'Cancel', title: 'Cancel appointment', classes: 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100' },
    ]
  }

  if (s === 'confirmed') {
    return [
      { value: 'completed', label: 'Complete', title: 'Mark as completed', classes: 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100' },
      { value: 'no_show', label: 'Missed', title: 'Mark as missed (no-show)', classes: 'border-gray-200 bg-gray-100 text-gray-800 hover:bg-gray-200' },
      { value: 'cancelled', label: 'Cancel', title: 'Cancel appointment', classes: 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100' },
    ]
  }

  return []
}

function paymentActions(a) {
  const appt = normalizeStatus(a?.status)
  const pay = currentPaymentStatus(a)

  if (appt === 'cancelled' || appt === 'no_show') {
    if (pay === 'paid') {
      return [
        { value: 'refunded', label: 'Refund', title: 'Refund the last paid payment', classes: 'border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100' },
      ]
    }
    return []
  }

  // For pending/confirmed/completed/no_show: only show meaningful actions.
  const actions = []

  if (pay !== 'paid') {
    actions.push({
      value: 'paid',
      label: 'Mark paid',
      title: appt === 'pending' ? 'Mark paid (will confirm if pending)' : 'Mark paid',
      classes: 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100',
      appointment_status: appt === 'pending' ? 'confirmed' : null,
    })
  }

  if (appt === 'pending' && pay !== 'failed') {
    actions.push({
      value: 'failed',
      label: 'Mark failed',
      title: 'Mark payment as failed',
      classes: 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100',
      appointment_status: null,
    })
  }

  return actions
}

function linkClasses(link) {
  const base = 'inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-sm border '
  if (link.active) return base + 'bg-indigo-600 text-white border-indigo-600'
  if (!link.url) return base + 'bg-gray-50 text-gray-400 border-gray-200 cursor-not-allowed'
  return base + 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'
}

function labelForStatus(type, value) {
  const v = normalizeStatus(value)
  if (!v) return ''

  if (type === 'appointment') {
    if (v === 'confirmed') return 'Confirm'
    if (v === 'completed') return 'Complete'
    if (v === 'no_show') return 'Mark missed'
    if (v === 'cancelled') return 'Cancel'
    return v
  }

  if (type === 'payment') {
    if (v === 'paid') return 'Mark paid'
    if (v === 'failed') return 'Mark failed'
    if (v === 'refunded') return 'Refund'
    return v
  }

  return v
}

function statusTitleText(a, payload) {
  const apptFrom = String(a?.status || '').toLowerCase() || '—'
  const payFrom = String(a?.payment?.status || '').toLowerCase() || 'none'

  const apptTo = payload?.appointment_status ? String(payload.appointment_status).toLowerCase() : null
  const payTo = payload?.payment_status ? String(payload.payment_status).toLowerCase() : null

  const parts = []
  if (apptTo) parts.push(`Appointment: ${apptFrom} → ${apptTo}`)
  if (payTo) parts.push(`Payment: ${payFrom} → ${payTo}`)

  const name = a?.patient?.name || ''
  const context = name ? `for ${name}` : ''

  const title = 'Apply change?'
  const text = `Appointment #${a?.id ?? ''} ${context}\n${parts.join('\n')}`.trim()
  return { title, text }
}

function isDestructive(payload) {
  const apptTo = normalizeStatus(payload?.appointment_status)
  const payTo = normalizeStatus(payload?.payment_status)
  return apptTo === 'cancelled' || apptTo === 'no_show' || payTo === 'failed' || payTo === 'refunded'
}

async function applyUpdate(a, payload) {
  if (!a || savingId.value === a.id) return

  const { title, text } = statusTitleText(a, payload)
  const destructive = isDestructive(payload)

  const result = await Swal.fire({
    title,
    text,
    icon: destructive ? 'warning' : 'question',
    showCancelButton: true,
    confirmButtonText: destructive ? 'Yes, apply' : 'Yes, apply',
    cancelButtonText: 'Cancel',
    reverseButtons: true,
    focusCancel: true,
    confirmButtonColor: destructive ? 'rgb(141,61,79)' : 'rgb(89,151,172)',
  })

  if (!result.isConfirmed) return

  savingId.value = a.id
  router.patch(route('admin.appointments.update', a.id), payload, {
    preserveScroll: true,
    onError: (errors) => {
      const msg = errors?.appointment_status || errors?.payment_status || errors?.cancellation_reason
      if (msg) showError(msg)
    },
    onFinish: () => {
      savingId.value = null
    },
  })
}
</script>
