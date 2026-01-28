<template>
  <Head title="Payouts" />

  <Navbar :canLogin="canLogin" :canRegister="canRegister" :authUser="authUser || page.props?.auth?.user" />

  <div class="min-h-[calc(100vh-112px)] bg-gray-50">
    <div class="bg-gradient-to-r from-[#af5166] to-[#5997ac]">
      <div class="mx-auto max-w-6xl px-4 py-8">
        <h1 class="text-2xl sm:text-3xl font-semibold text-white">Payouts</h1>
        <p class="mt-1 text-sm text-white/90">Manage your payouts. Click a row to see full details.</p>
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
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v4a1 1 0 00.293.707l2 2a1 1 0 001.414-1.414L11 10.586V7z" clip-rule="evenodd" />
                  </svg>
                  <div>
                    <div class="text-sm font-semibold text-green-800">{{ flashMessage }}</div>
                    <div class="text-xs text-green-700/80">&nbsp;</div>
                  </div>
                </div>
                <button type="button" @click="clearFlash" class="text-green-700/70 hover:text-green-800" aria-label="Dismiss">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 011.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </button>
              </div>
            </div>

            <div v-if="flashError" class="mb-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
              <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5 text-red-700 mt-0.5">
                    <path fill-rule="evenodd" d="M10.293 15.707a1 1 0 010-1.414L13.586 11 10.293 7.707a1 1 0 011.414-1.414L15 9.586l3.293-3.293a1 1 0 011.414 1.414L16.414 11l3.293 3.293a1 1 0 01-1.414 1.414L15 12.414l-3.293 3.293a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                  </svg>
                  <div>
                    <div class="text-sm font-semibold text-red-800">{{ flashError }}</div>
                    <div class="text-xs text-red-700/80">&nbsp;</div>
                  </div>
                </div>
                <button type="button" @click="clearError" class="text-red-700/70 hover:text-red-800" aria-label="Dismiss">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 011.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
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
              <option value="date">Appointment Date</option>
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
                    <th class="px-4 py-3 text-left">
                      <button type="button" @click="toggleSort('patient')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                        Patient
                        <SortIcon :active="sortKey === 'patient'" :dir="sortDir" />
                      </button>
                    </th>
                    <th class="px-4 py-3 text-left">
                      <button type="button" @click="toggleSort('scheduled_start')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                        Appointment Date
                        <SortIcon :active="sortKey === 'scheduled_start'" :dir="sortDir" />
                      </button>
                    </th>
                    <th class="px-4 py-3 text-left">
                      <button type="button" @click="toggleSort('status')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                        Status
                        <SortIcon :active="sortKey === 'status'" :dir="sortDir" />
                      </button>
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Net</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                  </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">
                  <tr
                    v-for="p in sorted"
                    :key="p.id"
                    class="hover:bg-gray-50"
                  >
                    <td class="px-4 py-3">
                      <div class="text-sm font-medium text-gray-900">{{ p.patient?.name || '—' }}</div>
                    </td>
                    <td class="px-4 py-3">
                      <div class="text-sm font-medium text-gray-900">{{ formatDate(p.appointment?.scheduled_start) }}</div>
                      <div class="text-xs text-gray-500">&nbsp;</div>
                    </td>
                    <td class="px-4 py-3">
                      <div class="flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium" :class="payoutBadge(p.status)">{{ payoutLabel(p.status) }}</span>
                      </div>
                    </td>
                    <td class="px-4 py-3 text-right">
                      <div class="text-sm font-medium text-gray-900">{{ p.net_amount }} {{ p.currency }}</div>
                    </td>
                    <td class="px-4 py-3 text-right">
                      <div class="inline-flex items-center justify-end gap-2">
                        <button @click="show(p)" title="View details" aria-label="View payout details" class="inline-flex items-center justify-center h-9 w-9 rounded-full bg-white text-indigo-600 shadow border border-gray-100 hover:scale-105 transition">
                          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                            <path d="M14 2v6h6" />
                            <path d="M8 11h8M8 15h8M8 19h5" />
                          </svg>
                        </button>
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
              <div class="mt-4 text-lg font-medium">No payouts yet</div>
              <div class="mt-1 text-sm">You don't have any payouts at the moment.</div>
            </div>
          </div>

          <div class="flex items-center justify-between px-4 py-3 border-t border-gray-200">
            <div class="text-sm text-gray-600">Showing {{ payouts.from }}-{{ payouts.to }} of {{ payouts.total }}</div>
            <div class="flex items-center gap-2">
              <Link v-for="(link, i) in payouts.links" :key="i" :href="link.url || '#'" :class="linkClasses(link)" preserve-scroll>
                <span v-html="link.label"></span>
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <PayoutDetails :payout="selected" @close="close" />
</template>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import Navbar from '@/Components/Navbar.vue'
import SortIcon from '@/Pages/Admin/Psychologist/SortIcon.vue'
import PayoutDetails from '@/Pages/Psychologist/Payouts/PayoutDetails.vue'

const props = defineProps({
  payouts: Object,
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

function clearFlash() { showFlash('') }
function clearError() { showError('') }

const data = computed(() => props.payouts?.data || [])

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

    return list.filter((p) => {
      const start = p?.appointment?.scheduled_start
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

  return list.filter((p) => {
    if (searchField.value === 'id') return String(p?.id ?? '').toLowerCase().includes(q)
    if (searchField.value === 'patient') return String(p?.patient?.name ?? '').toLowerCase().includes(q)
    return [String(p?.id ?? ''), String(p?.patient?.name ?? '')].join(' ').toLowerCase().includes(q)
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

function getSortValue(item, key) {
  switch (key) {
    case 'id':
      return Number(item?.id || 0)
    case 'patient':
      return String(item?.patient?.name || '').toLowerCase()
    case 'scheduled_start':
      try { return new Date(item?.appointment?.scheduled_start || 0).getTime() || 0 } catch { return 0 }
    case 'status':
      return String(item?.status || '').toLowerCase()
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

const selected = ref(null)

function show(p) { selected.value = p }
function close() { selected.value = null }

function formatDate(value) {
  if (!value) return '—'
  try { return new Intl.DateTimeFormat(undefined, { year: 'numeric', month: 'short', day: '2-digit' }).format(new Date(value)) } catch { return String(value) }
}

function payoutLabel(status) {
  const s = String(status || '').trim().toLowerCase()
  if (!s) return '—'
  return s.charAt(0).toUpperCase() + s.slice(1)
}

function payoutBadge(status) {
  const s = String(status || '').trim().toLowerCase()
  if (s === 'pending') return 'bg-yellow-50 text-yellow-800 ring-1 ring-yellow-200'
  if (s === 'paid') return 'bg-green-50 text-green-700 ring-1 ring-green-200'
  if (s === 'on_hold') return 'bg-red-50 text-red-700 ring-1 ring-red-200'
  if (s === 'refund') return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
  return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
}

function linkClasses(link) {
  const base = 'inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-sm border '
  if (link.active) return base + 'bg-indigo-600 text-white border-indigo-600'
  if (!link.url) return base + 'bg-gray-50 text-gray-400 border-gray-200 cursor-not-allowed'
  return base + 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'
}
</script>
