<template>
  <div class="p-6 space-y-6">
    <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Payouts</h1>
      </div>

      <div class="flex items-center gap-3 w-full md:w-auto">
        <div class="flex items-center gap-2 flex-1">
          <select
            v-model="searchField"
            class="h-10 w-40 md:w-48 shrink-0 rounded-lg border-gray-300 bg-white px-3 text-sm text-gray-700"
            aria-label="Search filter"
          >
            <option value="id">ID</option>
            
            <option value="psychologist">Psychologist</option>
            <option value="date">Date</option>
          </select>

          <div class="relative flex-1 md:w-80">
            <template v-if="searchField !== 'date'">
              <input
                v-model="searchQuery"
                type="text"
                :placeholder="searchPlaceholder"
                class="w-full rounded-lg border-gray-300 pl-10 pr-3 py-2"
              />

              <button
                v-if="searchQuery"
                type="button"
                @click="clearSearch"
                class="absolute right-2 top-1/2 -translate-y-1/2 inline-flex items-center justify-center h-7 w-7 rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                aria-label="Clear text"
                title="Clear"
              >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
            </template>

            <template v-else>
              <input
                v-model="searchDate"
                type="date"
                class="w-full rounded-lg border-gray-300 pl-10 pr-10 py-2"
                aria-label="Search date"
              />

              <button
                v-if="searchDate"
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
            </template>

            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
              <path
                fill-rule="evenodd"
                d="M12.9 14.32a8 8 0 111.414-1.414l4.387 4.387a1 1 0 01-1.414 1.414l-4.387-4.387zM14 8a6 6 0 11-12 0 6 6 0 0112 0z"
                clip-rule="evenodd"
              />
            </svg>
          </div>
        <div class="ml-2">
          <div class="flex items-center gap-2">
            <button type="button" @click="filtersOpen = !filtersOpen" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border bg-white text-sm text-gray-700 hover:bg-gray-50">
              Filter
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 01.707 1.707L12 11.414V15a1 1 0 01-.553.894l-3 1.5A1 1 0 017 16.5V11.414L3.293 5.707A1 1 0 013 5z" clip-rule="evenodd"/></svg>
            </button>
          </div>
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
              <input type="checkbox" :value="s.value" v-model="pendingActiveStatuses" class="form-checkbox" />
              <span class="text-sm text-gray-700">{{ s.label }}</span>
            </label>
          </div>
        </div>

        <div>
          <div class="text-sm font-medium text-gray-700 mb-2">Updated Between</div>
          <div class="flex gap-2">
            <input type="date" v-model="pendingCreatedFrom" class="rounded-lg border-gray-300 px-3 py-2 text-sm w-1/2" />
            <input type="date" v-model="pendingCreatedTo" class="rounded-lg border-gray-300 px-3 py-2 text-sm w-1/2" />
          </div>
        </div>
      </div>

      <div class="flex items-center justify-end gap-2 mt-4">
        <button type="button" @click="clearFilters" class="px-3 py-2 rounded-lg border bg-white text-sm text-gray-700 hover:bg-gray-50">Clear</button>
        <button type="button" @click="filtersOpen = false" class="px-3 py-2 rounded-lg border bg-white text-sm text-gray-700 hover:bg-gray-50">Close</button>
        <button type="button" @click="applyFilters" class="px-3 py-2 rounded-lg text-white text-sm hover:opacity-90" style="background-color: rgb(175 81 102 / var(--tw-bg-opacity, 1))">Apply</button>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
      <div v-if="selectedIds.length" class="flex items-center justify-end gap-3 px-4 py-3 border-b bg-white">
        <div class="text-sm text-gray-700">Selected: <span class="font-medium">{{ selectedIds.length }}</span></div>
        <select v-model="bulkStatus" class="h-10 w-44 md:w-25 rounded-lg border-gray-300 bg-white px-3 text-sm text-gray-700">
          <option value="">Change status</option>
          <option value="pending">Pending</option>
          <option value="on_hold">On Hold</option>
          <option value="paid">Paid</option>
          <option value="refund">Refund</option>
        </select>
        <button :disabled="!bulkStatus" @click="applyBulk" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-white border text-sm hover:bg-gray-50 disabled:opacity-50">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" viewBox="0 0 20 20" fill="currentColor"><path d="M5 10a1 1 0 112 0 1 1 0 01-2 0zM8 10a1 1 0 112 0 1 1 0 01-2 0zM11 10a1 1 0 112 0 1 1 0 01-2 0z"/></svg>
          <span class="text-sm text-gray-700">Apply</span>
        </button>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left">
                <input type="checkbox" v-model="selectAll" aria-label="Select all" />
              </th>
              <th class="px-4 py-3 text-left">
                <button type="button" @click="toggleSort('id')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                  ID
                  <SortIcon :active="sortKey === 'id'" :dir="sortDir" />
                </button>
              </th>
              <th class="px-4 py-3 text-left">
                <button type="button" @click="toggleSort('psychologist')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                  Psychologist
                  <SortIcon :active="sortKey === 'psychologist'" :dir="sortDir" />
                </button>
              </th>
              <th class="px-4 py-3 text-left">
                <button type="button" @click="toggleSort('net_amount')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                  Net
                  <SortIcon :active="sortKey === 'net_amount'" :dir="sortDir" />
                </button>
              </th>
              <th class="px-4 py-3 text-left">
                <button type="button" @click="toggleSort('updated_at')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                  Updated
                  <SortIcon :active="sortKey === 'updated_at'" :dir="sortDir" />
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
            <tr v-if="(sorted || []).length === 0">
              <td colspan="7" class="px-4 py-6 text-sm text-gray-500 text-center">No payouts found.</td>
            </tr>
            <tr
              v-for="p in sorted"
              :key="p.id"
              class="hover:bg-gray-50"
            >
              <td class="px-4 py-3"><input type="checkbox" :value="p.id" v-model="selectedIds" /></td>
              <td class="px-4 py-3 text-sm text-gray-700">#{{ p.id }}</td>
              <td class="px-4 py-3">
                <div class="text-sm font-medium text-gray-900">{{ p.psychologist?.name || '—' }}</div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm font-medium text-gray-900">{{ Number(p.net_amount).toFixed(2) }} {{ p.currency || 'TND' }}</div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm font-medium text-gray-900">{{ formatDate(p.updated_at) }}</div>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium" :class="payoutBadge(p.status)">
                    {{ payoutLabel(p.status) }}
                  </span>
                </div>
              </td>
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-2">
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

                  <div class="relative inline-block text-left" data-payout-actions>
                    <button
                      type="button"
                      @click="toggleActions(p.id)"
                      class="inline-flex items-center justify-center h-9 w-9 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50"
                      :aria-expanded="openActionsId === p.id"
                      :aria-haspopup="true"
                      aria-label="Open actions"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4z" />
                      </svg>
                    </button>

                    <div v-if="openActionsId === p.id" class="absolute right-0 mt-2 w-52 bg-white border rounded-lg shadow-lg z-50" role="menu" aria-orientation="vertical">
                      <div class="py-1">
                        <template v-for="act in payoutActions(p)" :key="'pay-'+act.value">
                          <button
                            type="button"
                            @click="onSelectAction(p, act)"
                            :class="['w-full flex items-center gap-2 px-3 py-2 text-sm hover:bg-gray-100', actionTextColor(act)]"
                            role="menuitem"
                            :disabled="savingId === p.id"
                          >
                            <svg :class="['h-4 w-4', actionTextColor(act)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="M12 5l7 7-7 7"></path></svg>
                            <span>{{ act.label }}</span>
                          </button>
                        </template>

                        <div v-if="payoutActions(p).length === 0" class="px-3 py-2 text-sm text-gray-500">No actions</div>
                      </div>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex items-center justify-between px-4 py-3 border-t border-gray-200">
          <div class="text-sm text-gray-600">
            Showing {{ payouts.from || 0 }}-{{ payouts.to || 0 }} of {{ payouts.total || 0 }}
          </div>

        <div class="flex items-center gap-2">
          <Link
            v-for="(link, i) in payouts.links"
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

    <Show :show="modal === 'show'" :payout="selected" @close="closeModal" />
  </div>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch, onMounted, onBeforeUnmount } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import SortIcon from '@/Components/SortIcon.vue'
import Swal from 'sweetalert2'
import Show from './Show.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({ payouts: Object, status: { type: String, default: '' }, filters: { type: Object, default: () => ({}) } })

const page = usePage()
const toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3500,
  timerProgressBar: true,
})

function showFlash(message) {
  if (!message) return
  toast.fire({ icon: 'success', title: message })
}

function showError(message) {
  if (!message) return
  toast.fire({ icon: 'error', title: message })
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

const data = computed(() => props.payouts?.data || [])

const searchField = ref('id')
const searchQuery = ref('')
const searchDate = ref('')

const isHydratingFilters = ref(false)
let searchDebounce = null

function normalizeFilters(filters = {}) {
  const validField = ['id', 'psychologist', 'date'].includes(String(filters?.search_field || '').toLowerCase())
    ? String(filters.search_field).toLowerCase()
    : 'id'

  return {
    search_field: validField,
    search_query: String(filters?.search_query || ''),
    search_date: String(filters?.search_date || ''),
    statuses: Array.isArray(filters?.statuses) ? filters.statuses : [],
    created_from: String(filters?.created_from || ''),
    created_to: String(filters?.created_to || ''),
  }
}

function hydrateFiltersFromProps() {
  const f = normalizeFilters(props.filters || {})
  isHydratingFilters.value = true
  searchField.value = f.search_field
  searchQuery.value = f.search_query
  searchDate.value = f.search_date
  activeStatuses.value = f.statuses
  createdFrom.value = f.created_from
  createdTo.value = f.created_to
  pendingActiveStatuses.value = Array.isArray(f.statuses) ? [...f.statuses] : []
  pendingCreatedFrom.value = f.created_from || ''
  pendingCreatedTo.value = f.created_to || ''
  isHydratingFilters.value = false
}

function currentQueryParams() {
  const params = {
    search_field: searchField.value,
    search_query: searchField.value === 'date' ? '' : String(searchQuery.value || '').trim(),
    search_date: searchField.value === 'date' ? String(searchDate.value || '').trim() : '',
    statuses: [...activeStatuses.value],
    created_from: String(createdFrom.value || '').trim(),
    created_to: String(createdTo.value || '').trim(),
  }

  return Object.fromEntries(
    Object.entries(params).filter(([_, value]) => {
      if (Array.isArray(value)) return value.length > 0
      return value !== '' && value != null
    })
  )
}

function applyServerFilters({ resetPage = true } = {}) {
  if (isHydratingFilters.value) return

  const params = currentQueryParams()
  if (resetPage) params.page = 1

  router.get(route('admin.payouts.index'), params, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
    only: ['payouts', 'filters', 'status'],
  })
}

function clearSearch() {
  if (searchDebounce) {
    clearTimeout(searchDebounce)
    searchDebounce = null
  }
  searchQuery.value = ''
  applyServerFilters({ resetPage: true })
}



const modal = ref('')
const selected = ref(null)
const openActionsId = ref(null)

const filtersOpen = ref(false)
const statusOptions = [
  { value: 'pending', label: 'Pending' },
  { value: 'paid', label: 'Paid' },
  { value: 'on_hold', label: 'On Hold' },
  { value: 'refund', label: 'Refund' },
]
const activeStatuses = ref([])
const createdFrom = ref('')
const createdTo = ref('')
const pendingActiveStatuses = ref([])
const pendingCreatedFrom = ref('')
const pendingCreatedTo = ref('')

hydrateFiltersFromProps()

watch(searchField, (next) => {
  if (isHydratingFilters.value) return

  if (next === 'date') {
    searchQuery.value = ''
  } else {
    searchDate.value = ''
  }
  applyServerFilters({ resetPage: true })
})

watch(searchQuery, () => {
  if (isHydratingFilters.value || searchField.value === 'date') return
  if (searchDebounce) clearTimeout(searchDebounce)
  searchDebounce = setTimeout(() => {
    applyServerFilters({ resetPage: true })
    searchDebounce = null
  }, 300)
})

watch(searchDate, () => {
  if (isHydratingFilters.value || searchField.value !== 'date') return
  applyServerFilters({ resetPage: true })
})

function applyFilters() {
  activeStatuses.value = Array.isArray(pendingActiveStatuses.value) ? [...pendingActiveStatuses.value] : []
  createdFrom.value = String(pendingCreatedFrom.value || '')
  createdTo.value = String(pendingCreatedTo.value || '')
  applyServerFilters({ resetPage: true })
  filtersOpen.value = false
}

function clearFilters() {
  pendingActiveStatuses.value = []
  pendingCreatedFrom.value = ''
  pendingCreatedTo.value = ''
}

function openShow(p) { selected.value = p; modal.value = 'show' }
function closeModal() { modal.value = ''; selected.value = null }

function toggleActions(id) {
  openActionsId.value = openActionsId.value === id ? null : id
}

function onSelectAction(p, act) {
  openActionsId.value = null
  if (!act) return
  handlePayoutAction(p, act)
}

function handleDocumentClick(event) {
  const target = event?.target
  if (target instanceof Element && target.closest('[data-payout-actions]')) return
  openActionsId.value = null
}

onMounted(() => {
  document.addEventListener('click', handleDocumentClick)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleDocumentClick)
})

// selection / bulk
const selectedIds = ref([])
const selectAll = ref(false)
const bulkStatus = ref('')

watch(selectAll, (v) => {
  if (v) selectedIds.value = (data.value || []).map(x => x.id)
  else selectedIds.value = []
})

const searchPlaceholder = computed(() => {
  switch (searchField.value) {
    case 'id': return 'Search by ID...'
    case 'psychologist': return 'Search by psychologist name...'
    default: return 'Search...'
  }
})

const filtered = computed(() => (Array.isArray(data.value) ? [...data.value] : []))

const sortKey = ref('id')
const sortDir = ref('desc')

function toggleSort(key) { if (sortKey.value === key) { sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'; return } sortKey.value = key; sortDir.value = 'desc' }

function getSortValue(a, key) {
  switch (key) {
    case 'id': return Number(a?.id || 0)
    case 'updated_at': try { return new Date(a?.updated_at || 0).getTime() || 0 } catch { return 0 }
    case 'psychologist': return String(a?.psychologist?.name || '').toLowerCase()
    case 'net_amount': return Number(a?.net_amount || 0)
    case 'status': return String(a?.status || '').toLowerCase()
    case 'created_at': try { return new Date(a?.created_at || 0).getTime() || 0 } catch { return 0 }
    default: return ''
  }
}

const sorted = computed(() => {
  const list = filtered.value || []
  const key = sortKey.value
  const dir = sortDir.value
  const multiplier = dir === 'asc' ? 1 : -1

  return list
    .map((item, idx) => ({ item, idx }))
    .sort((a,b) => {
      const av = getSortValue(a.item, key)
      const bv = getSortValue(b.item, key)
      if (typeof av === 'number' && typeof bv === 'number') { const diff = av - bv; return diff !== 0 ? diff * multiplier : a.idx - b.idx }
      const diff = String(av).localeCompare(String(bv))
      return diff !== 0 ? diff * multiplier : a.idx - b.idx
    })
    .map(x => x.item)
})

const brandColor = 'rgb(89 151 172 / var(--tw-bg-opacity, 1))'

const savingId = ref(null)
const savingBulk = ref(false)

function formatDate(value) { if (!value) return '—'; try { return new Intl.DateTimeFormat(undefined, { year: 'numeric', month: 'short', day: '2-digit' }).format(new Date(value)) } catch { return String(value) } }

function payoutLabel(status) {
  const s = String(status || '').toLowerCase()
  if (!s) return '—'
  if (s === 'paid') return 'Paid'
  if (s === 'pending') return 'Pending'
  if (s === 'on_hold') return 'On Hold'
  if (s === 'refund') return 'Refund'
  return s
}

function payoutBadge(status) {
  const s = String(status || '').toLowerCase()
  if (!s) return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
  if (s === 'paid') return 'bg-green-50 text-green-700 ring-1 ring-green-200'
  if (s === 'pending') return 'bg-yellow-50 text-yellow-800 ring-1 ring-yellow-200'
  if (s === 'on_hold') return 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-200'
  if (s === 'refund') return 'bg-red-50 text-red-700 ring-1 ring-red-200'
  return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
}

function linkClasses(link) {
  const base = 'inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-sm border '
  if (link.active) return base + 'bg-indigo-600 text-white border-indigo-600'
  if (!link.url) return base + 'bg-gray-50 text-gray-400 border-gray-200 cursor-not-allowed'
  return base + 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'
}

function payoutActions(p) {
  const all = [
    { value: 'paid', label: 'Mark Paid', classes: 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100' },
    { value: 'on_hold', label: 'Put On Hold', classes: 'border-indigo-200 bg-indigo-50 text-indigo-700 hover:bg-indigo-100' },
    { value: 'pending', label: 'Mark Pending', classes: 'border-yellow-200 bg-yellow-50 text-yellow-800 hover:bg-yellow-100' },
    { value: 'refund', label: 'Refund', classes: 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100' },
  ]
  const st = String(p?.status || '').toLowerCase()
  return all.filter(a => a.value !== st)
}

function actionTextColor(act) {
  const v = String(act?.value || '').toLowerCase()
  if (v === 'paid') return 'text-green-700'
  if (v === 'pending') return 'text-yellow-800'
  if (v === 'on_hold') return 'text-indigo-700'
  if (v === 'refund') return 'text-red-700'
  return 'text-gray-700'
}

function statusTitleText(p, payload) {
  const from = String(p?.status || '').toLowerCase() || '—'
  const to = payload?.status ? String(payload.status).toLowerCase() : null
  const parts = []
  if (to) parts.push(`Payout: ${from} → ${to}`)
  const name = p?.psychologist?.name || ''
  const context = name ? `for ${name}` : ''
  const title = 'Apply change?'
  const text = `Payout #${p?.id ?? ''} ${context}\n${parts.join('\n')}`.trim()
  return { title, text }
}

function isDestructive(payload) { const to = String(payload?.status || '').toLowerCase(); return to === 'failed' }

async function applyUpdate(p, payload) {
  if (!p || savingId.value === p.id) return
  const { title, text } = statusTitleText(p, payload)
  const destructive = isDestructive(payload)
  const result = await Swal.fire({ title, text, icon: destructive ? 'warning' : 'question', showCancelButton: true, confirmButtonText: 'Yes, apply', cancelButtonText: 'Cancel', reverseButtons: true, focusCancel: true, confirmButtonColor: destructive ? 'rgb(141,61,79)' : 'rgb(89,151,172)', })
  if (!result.isConfirmed) return
  savingId.value = p.id
  router.patch(route('admin.payouts.update', p.id), payload, {
    preserveScroll: true,
    onError: (errors) => { const msg = errors?.status; if (msg) showError(msg) },
    onFinish: () => { savingId.value = null },
  })
}

async function handlePayoutAction(p, act) {
  if (!p || !act) return
  await applyUpdate(p, { status: act.value })
}

async function applyBulk() {
  if (!selectedIds.value || selectedIds.value.length === 0 || !bulkStatus.value) return
  const ids = selectedIds.value
  const title = 'Apply change?'
  const text = `Change status for ${ids.length} payout(s) to ${payoutLabel(bulkStatus.value)}?`
  const destructive = String(bulkStatus.value || '').toLowerCase() === 'refund'

  const result = await Swal.fire({
    title,
    text,
    icon: destructive ? 'warning' : 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, apply',
    cancelButtonText: 'Cancel',
    reverseButtons: true,
    focusCancel: true,
    confirmButtonColor: destructive ? 'rgb(141,61,79)' : brandColor,
  })

  if (!result.isConfirmed) return

  savingBulk.value = true
  await router.patch(route('admin.payouts.bulk_update'), { ids, status: bulkStatus.value }, {
    preserveScroll: true,
    onError: (errors) => { const msg = errors?.status; if (msg) showError(msg) },
    onFinish: () => { savingBulk.value = false; bulkStatus.value = ''; selectedIds.value = [] },
  })
}

</script>

<style scoped></style>
