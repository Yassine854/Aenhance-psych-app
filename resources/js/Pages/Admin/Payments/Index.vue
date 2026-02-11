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

        <h1 class="text-2xl font-semibold text-gray-900">Payments</h1>
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
        <div class="ml-2">
          <button type="button" @click="filtersOpen = !filtersOpen" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border bg-white text-sm text-gray-700 hover:bg-gray-50">
            Filter
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 01.707 1.707L12 11.414V15a1 1 0 01-.553.894l-3 1.5A1 1 0 017 16.5V11.414L3.293 5.707A1 1 0 013 5z" clip-rule="evenodd"/></svg>
          </button>
        </div>
        </div>
      </div>
    </header>

    <div v-if="filtersOpen" class="bg-white rounded-lg shadow p-4">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <div class="text-sm font-medium text-gray-700 mb-2">Statuses</div>
          <div class="flex flex-col gap-2">
            <label v-for="s in statusOptions" :key="s.value" class="inline-flex items-center gap-2">
              <input type="checkbox" :value="s.value" v-model="activeStatuses" class="form-checkbox" />
              <span class="text-sm text-gray-700">{{ s.label }}</span>
            </label>
          </div>
        </div>

        <div>
          <div class="text-sm font-medium text-gray-700 mb-2">Created Between</div>
          <div class="flex gap-2">
            <input type="date" v-model="createdFrom" class="rounded-lg border-gray-300 px-3 py-2 text-sm w-1/2" />
            <input type="date" v-model="createdTo" class="rounded-lg border-gray-300 px-3 py-2 text-sm w-1/2" />
          </div>
        </div>
      </div>

      <div class="flex items-center justify-end gap-2 mt-4">
        <button type="button" @click="clearFilters" class="px-3 py-2 rounded-lg border bg-white text-sm text-gray-700 hover:bg-gray-50">Clear</button>
        <button type="button" @click="filtersOpen = false" class="px-3 py-2 rounded-lg border bg-white text-sm text-gray-700 hover:bg-gray-50">Close</button>
        <button type="button" @click="applyFilters" class="px-3 py-2 rounded-lg text-white text-sm hover:opacity-90" style="background-color: rgb(89 151 172 / var(--tw-bg-opacity, 1))">Apply</button>
      </div>
    </div>

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
                <button type="button" @click="toggleSort('amount')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                  Amount
                  <SortIcon :active="sortKey === 'amount'" :dir="sortDir" />
                </button>
              </th>
              <th class="px-4 py-3 text-left">
                <button type="button" @click="toggleSort('provider')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                  Provider
                  <SortIcon :active="sortKey === 'provider'" :dir="sortDir" />
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
              v-for="p in paginated"
              :key="p.id"
              class="hover:bg-gray-50"
            >
              <td class="px-4 py-3 text-sm text-gray-700">#{{ p.id }}</td>
              <td class="px-4 py-3">
                <div class="text-sm font-medium text-gray-900">{{ p.patient?.name || '—' }}</div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm font-medium text-gray-900">{{ p.psychologist?.name || '—' }}</div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm font-medium text-gray-900">{{ Number(p.amount).toFixed(2) }} {{ p.currency || 'TND' }}</div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm text-gray-700">{{ p.provider || '—' }}</div>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium" :class="paymentBadge(p.status)">
                    {{ paymentLabel(p.status) }}
                  </span>
                </div>
              </td>
              <td class="px-4 py-3 text-right">
                <div class="flex flex-col items-end gap-2">
                  <button
                    type="button"
                    title="View"
                    @click="openShow(p)"
                    class="inline-flex items-center justify-center h-9 w-9 rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                      <path d="M12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z" />
                      <path fill-rule="evenodd" d="M12 3c5.392 0 9.878 3.88 10.818 9-.94 5.12-5.426 9-10.818 9S2.122 17.12 1.182 12C2.122 6.88 6.608 3 12 3Zm0 15a6 6 0 0 0 6-6 6 6 0 0 0-12 0 6 6 0 0 0 6 6Z" clip-rule="evenodd" />
                    </svg>
                  </button>

                  <div class="inline-flex items-center gap-2">
                    <button
                      v-for="act in paymentActions(p)"
                      :key="act.value"
                      type="button"
                      @click="handlePaymentAction(p, act)"
                      :disabled="savingId === p.id"
                      class="inline-flex items-center justify-center h-8 px-2.5 rounded-lg border text-xs font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                      :class="act.classes"
                      :title="act.title"
                    >
                      <span>{{ act.label }}</span>
                    </button>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex items-center justify-between px-4 py-3 border-t border-gray-200">
        <div class="text-sm text-gray-600">
          <template v-if="isSearching">
            Showing
            <span v-if="totalFiltered === 0">0</span>
            <span v-else>{{ (clientPage - 1) * perPage + 1 }}-{{ Math.min(clientPage * perPage, totalFiltered) }}</span>
            of {{ totalFiltered }}
          </template>
          <template v-else>
            Showing {{ payments.from }}-{{ payments.to }} of {{ payments.total }}
          </template>
        </div>

        <div class="flex items-center gap-2">
          <template v-if="isSearching">
            <button
              type="button"
              class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-sm border bg-white text-gray-700 border-gray-200 hover:bg-gray-50 disabled:opacity-50"
              :disabled="clientPage <= 1"
              @click="clientPage = Math.max(1, clientPage - 1)"
            >
              Prev
            </button>

            <button
              v-for="p in totalPages"
              :key="p"
              type="button"
              class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-sm border"
              :class="p === clientPage ? 'text-white border' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'"
              :style="p === clientPage ? { backgroundColor: brandColor, borderColor: brandColor, color: '#fff' } : null"
              @click="clientPage = p"
            >
              {{ p }}
            </button>

            <button
              type="button"
              class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-sm border bg-white text-gray-700 border-gray-200 hover:bg-gray-50 disabled:opacity-50"
              :disabled="clientPage >= totalPages"
              @click="clientPage = Math.min(totalPages, clientPage + 1)"
            >
              Next
            </button>
          </template>

          <template v-else>
            <Link
              v-for="(link, i) in payments.links"
              :key="i"
              :href="link.url || '#'"
              :class="linkClasses(link)"
              :style="link.active ? { backgroundColor: brandColor, borderColor: brandColor, color: '#fff' } : null"
              preserve-scroll
            >
              <span v-html="link.label"></span>
            </Link>
          </template>
        </div>
      </div>
    </div>

    <Show :show="modal === 'show'" :payment="selected" @close="closeModal" />
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

const props = defineProps({ payments: Object, status: { type: String, default: '' } })

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

const data = computed(() => props.payments?.data || [])

const searchField = ref('id')
const searchQuery = ref('')
const searchDate = ref('')

const perPage = ref((props.payments && props.payments.per_page) || 15)
const clientPage = ref(1)
const isSearching = computed(() => {
  const q = String(searchQuery.value || '').trim()
  const d = String(searchDate.value || '').trim()
  const hasStatus = (activeStatuses.value || []).length > 0
  const fromTo = String(createdFrom.value || '').trim() || String(createdTo.value || '').trim()
  return Boolean(q || d || hasStatus || fromTo)
})

watch([searchQuery, searchDate], () => {
  clientPage.value = 1
})

const modal = ref('')
const selected = ref(null)

const filtersOpen = ref(false)
const statusOptions = [
  { value: 'pending', label: 'Pending' },
  { value: 'paid', label: 'Paid' },
  { value: 'failed', label: 'Failed' },
  { value: 'refunded', label: 'Refunded' },
]
const activeStatuses = ref([])
const createdFrom = ref('')
const createdTo = ref('')

function applyFilters() {
  filtersOpen.value = false
}

function clearFilters() {
  activeStatuses.value = []
  createdFrom.value = ''
  createdTo.value = ''
}

function openShow(p) {
  selected.value = p
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
  let list = Array.isArray(data.value) ? [...data.value] : []

  if (searchField.value === 'date') {
    const d = String(searchDate.value || '').trim()
    if (d) {
      list = list.filter((a) => {
        const created = a?.created_at
        if (!created) return false
        try {
          const iso = new Date(created).toISOString().slice(0, 10)
          return iso === d
        } catch {
          return false
        }
      })
    }
  } else {
    const q = String(searchQuery.value || '').trim().toLowerCase()
    if (q) {
      list = list.filter((a) => {
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

        const hay = [String(a?.id ?? ''), String(a?.patient?.name ?? ''), String(a?.psychologist?.name ?? '')].join(' ').toLowerCase()
        return hay.includes(q)
      })
    }
  }

  if ((activeStatuses.value || []).length > 0) {
    list = list.filter((a) => {
      const s = String(a?.status || '').toLowerCase()
      return activeStatuses.value.includes(s)
    })
  }

  const fromVal = String(createdFrom.value || '').trim()
  const toVal = String(createdTo.value || '').trim()
  if (fromVal || toVal) {
    list = list.filter((a) => {
      const createdRaw = a?.created_at || null
      if (!createdRaw) return false
      const created = new Date(createdRaw)
      if (fromVal) {
        const from = new Date(fromVal)
        if (!Number.isNaN(from.getTime()) && created < from) return false
      }
      if (toVal) {
        const to = new Date(toVal)
        to.setHours(23, 59, 59, 999)
        if (!Number.isNaN(to.getTime()) && created > to) return false
      }
      return true
    })
  }

  return list
})

const sortKey = ref('created_at')
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
    case 'psychologist':
      return String(a?.psychologist?.name || '').toLowerCase()
    case 'amount':
      return Number(a?.amount || 0)
    case 'provider':
      return String(a?.provider || '').toLowerCase()
    case 'status':
      return String(a?.status || '').toLowerCase()
    case 'created_at':
      try { return new Date(a?.created_at || 0).getTime() || 0 } catch { return 0 }
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

const totalFiltered = computed(() => (filtered.value || []).length)
const totalPages = computed(() => Math.max(1, Math.ceil(totalFiltered.value / perPage.value)))

const paginated = computed(() => {
  if (!isSearching.value) return sorted.value
  const start = (clientPage.value - 1) * perPage.value
  return sorted.value.slice(start, start + perPage.value)
})

const brandColor = 'rgb(89 151 172 / var(--tw-bg-opacity, 1))'

const savingId = ref(null)

function formatDate(value) {
  if (!value) return '—'
  try {
    return new Intl.DateTimeFormat(undefined, { year: 'numeric', month: 'short', day: '2-digit' }).format(new Date(value))
  } catch {
    return String(value)
  }
}

function paymentLabel(status) {
  const s = String(status || '').toLowerCase()
  if (!s) return '—'
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

function linkClasses(link) {
  const base = 'inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-sm border '
  if (link.active) return base + 'bg-indigo-600 text-white border-indigo-600'
  if (!link.url) return base + 'bg-gray-50 text-gray-400 border-gray-200 cursor-not-allowed'
  return base + 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'
}

function labelForStatus(value) {
  const v = String(value || '').toLowerCase()
  if (!v) return ''
  if (v === 'paid') return 'Mark paid'
  if (v === 'failed') return 'Mark failed'
  if (v === 'refunded') return 'Refund'
  return v
}

function statusTitleText(p, payload) {
  const payFrom = String(p?.status || '').toLowerCase() || '—'
  const payTo = payload?.status ? String(payload.status).toLowerCase() : null
  const parts = []
  if (payTo) parts.push(`Payment: ${payFrom} → ${payTo}`)
  const name = p?.patient?.name || ''
  const context = name ? `for ${name}` : ''
  const title = 'Apply change?'
  const text = `Payment #${p?.id ?? ''} ${context}\n${parts.join('\n')}`.trim()
  return { title, text }
}

function isDestructive(payload) {
  const payTo = String(payload?.status || '').toLowerCase()
  return payTo === 'failed' || payTo === 'refunded'
}

async function applyUpdate(p, payload) {
  if (!p || savingId.value === p.id) return

  const { title, text } = statusTitleText(p, payload)
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

  savingId.value = p.id
  router.patch(route('admin.payments.update', p.id), payload, {
    preserveScroll: true,
    onError: (errors) => {
      const msg = errors?.status || errors?.refund_reason
      if (msg) showError(msg)
    },
    onFinish: () => {
      savingId.value = null
    },
  })
}

function paymentActions(p) {
  const pay = String(p?.status || '').toLowerCase()
  if (pay === 'pending') {
    return [
      { value: 'paid', label: 'Mark Paid', title: 'Mark as Paid', classes: 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100' },
      { value: 'failed', label: 'Fail', title: 'Mark as Failed', classes: 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100' },
    ]
  }

  if (pay === 'paid') {
    return [
      { value: 'refunded', label: 'Refund', title: 'Refund payment', classes: 'border-yellow-200 bg-yellow-50 text-yellow-700 hover:bg-yellow-100' },
    ]
  }

  return []
}

async function handlePaymentAction(p, act) {
  if (!p || !act) return

  if (String(act.value) === 'refunded') {
    const { value: reason, isConfirmed } = await Swal.fire({
      title: 'Refund reason (optional)',
      input: 'text',
      inputPlaceholder: 'Reason for refund',
      showCancelButton: true,
      confirmButtonText: 'Refund',
      confirmButtonColor: 'rgb(89,151,172)',
    })

    if (!isConfirmed) return
    await applyUpdate(p, { status: act.value, refund_reason: reason })
    return
  }

  await applyUpdate(p, { status: act.value })
}

</script>

<style scoped></style>
