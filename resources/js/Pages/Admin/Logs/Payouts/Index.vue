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

        <h1 class="text-2xl font-semibold text-gray-900">Payout Logs</h1>
      </div>

      <div class="flex items-center gap-3 w-full md:w-auto">
        <div class="flex items-center gap-2 flex-1">
          <select
            v-model="searchField"
            class="h-10 w-40 md:w-48 shrink-0 rounded-lg border-gray-300 bg-white px-3 text-sm text-gray-700"
            aria-label="Search filter"
          >
            <option value="id">ID</option>
            <option value="status">Status</option>
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
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
          <div class="text-sm font-medium text-gray-700 mb-2">Actor Role</div>
          <select v-model="actorRole" class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm">
            <option value="">Any</option>
            <option value="admin">Admin</option>
            <option value="system">SYSTEM</option>
          </select>
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
                <button type="button" @click="toggleSort('action')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                  Action
                  <SortIcon :active="sortKey === 'action'" :dir="sortDir" />
                </button>
              </th>
              <th class="px-4 py-3 text-left">
                <button type="button" @click="toggleSort('actor_role')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                  Actor Role
                  <SortIcon :active="sortKey === 'actor_role'" :dir="sortDir" />
                </button>
              </th>
              <th class="px-4 py-3 text-left">
                <button type="button" @click="toggleSort('status')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                  Status
                  <SortIcon :active="sortKey === 'status'" :dir="sortDir" />
                </button>
              </th>
              <th class="px-4 py-3 text-left">
                <button type="button" @click="toggleSort('created_at')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                  Created
                  <SortIcon :active="sortKey === 'created_at'" :dir="sortDir" />
                </button>
              </th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>

          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="l in paginated" :key="l.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-sm text-gray-700">#{{ l.id }}</td>
              <td class="px-4 py-3">
                <div class="text-sm text-gray-700">{{ l.action }}</div>
                <div class="text-xs text-gray-500">{{ l.description || '—' }}</div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm font-medium text-gray-900">{{ l.actor_role || (l.actor_id ? 'User #' + l.actor_id : 'SYSTEM') }}</div>
              </td>
              <td class="px-4 py-3">
                <div>
                  <span :class="['inline-flex items-center px-2 py-0.5 rounded text-xs font-medium', statusBadgeClass(getStatus(l))]">
                    {{ getStatus(l) || '-' }}
                  </span>
                </div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm font-medium text-gray-900">{{ formatDate(l.created_at) }}</div>
                <div class="text-xs text-gray-500">{{ l.created_at ? new Date(l.created_at).toLocaleTimeString() : '' }}</div>
              </td>
              <td class="px-4 py-3 text-right">
                <div class="flex flex-col items-end gap-2">
                  <button type="button" title="View" @click="openShow(l)" class="inline-flex items-center justify-center h-9 w-9 rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                      <path d="M12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z" />
                      <path fill-rule="evenodd" d="M12 3c5.392 0 9.878 3.88 10.818 9-.94 5.12-5.426 9-10.818 9S2.122 17.12 1.182 12C2.122 6.88 6.608 3 12 3Zm0 15a6 6 0 0 0 6-6 6 6 0 0 0-12 0 6 6 0 0 0 6 6Z" clip-rule="evenodd" />
                    </svg>
                  </button>
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
            Showing {{ logs.from }}-{{ logs.to }} of {{ logs.total }}
          </template>
        </div>

        <div class="flex items-center gap-2">
          <template v-if="isSearching">
            <button type="button" class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-sm border bg-white text-gray-700 border-gray-200 hover:bg-gray-50 disabled:opacity-50" :disabled="clientPage <= 1" @click="clientPage = Math.max(1, clientPage - 1)">Prev</button>

            <button v-for="p in totalPages" :key="p" type="button" class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-sm border" :class="p === clientPage ? 'text-white border' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'" :style="p === clientPage ? { backgroundColor: brandColor, borderColor: brandColor, color: '#fff' } : null" @click="clientPage = p">{{ p }}</button>

            <button type="button" class="inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-sm border bg-white text-gray-700 border-gray-200 hover:bg-gray-50 disabled:opacity-50" :disabled="clientPage >= totalPages" @click="clientPage = Math.min(totalPages, clientPage + 1)">Next</button>
          </template>

          <template v-else>
            <Link v-for="(link, i) in logs.links" :key="i" :href="link.url || '#'" :class="linkClasses(link)" :style="link.active ? { backgroundColor: brandColor, borderColor: brandColor, color: '#fff' } : null" preserve-scroll>
              <span v-html="link.label"></span>
            </Link>
          </template>
        </div>
      </div>
    </div>

    <Show :show="modal === 'show'" :log="selected" @close="closeModal" />
  </div>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import SortIcon from '@/Pages/Admin/Psychologist/SortIcon.vue'
import Show from './Show.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({ logs: Object, status: { type: String, default: '' } })

const page = usePage()
const flashMessage = ref('')
const flashError = ref('')

let flashTimer = null
let errorTimer = null

function showFlash(message) {
  flashMessage.value = message || ''
  if (flashTimer) { clearTimeout(flashTimer); flashTimer = null }
  if (!flashMessage.value) return
  flashTimer = setTimeout(() => { flashMessage.value = ''; flashTimer = null }, 5000)
}

function showError(message) {
  flashError.value = message || ''
  if (errorTimer) { clearTimeout(errorTimer); errorTimer = null }
  if (!flashError.value) return
  errorTimer = setTimeout(() => { flashError.value = ''; errorTimer = null }, 5000)
}

showFlash(props.status || page.props?.flash?.status || '')
showError(page.props?.flash?.error || '')

watch(() => props.status, (next) => { if (next) showFlash(next) })
watch(() => page.props?.flash?.status, (next) => { if (next) showFlash(next) })
watch(() => page.props?.flash?.error, (next) => { if (next) showError(next) })

function clearFlash() { showFlash('') }
function clearError() { showError('') }

const data = computed(() => props.logs?.data || [])

const searchField = ref('id')
const searchQuery = ref('')
const searchDate = ref('')

const perPage = ref((props.logs && props.logs.per_page) || 15)
const clientPage = ref(1)

watch([searchQuery, searchDate], () => { clientPage.value = 1 })

const modal = ref('')
const selected = ref(null)

const filtersOpen = ref(false)
const statusOptions = [
  { value: 'pending', label: 'Pending' },
  { value: 'paid', label: 'Paid' },
  { value: 'on_hold', label: 'On Hold' },
  { value: 'refunded', label: 'Refunded' },
]
const activeStatuses = ref([])

const actorRole = ref('')

const createdFrom = ref('')
const createdTo = ref('')

function applyFilters() { filtersOpen.value = false }
function clearFilters() { activeStatuses.value = []; actorRole.value = ''; createdFrom.value = ''; createdTo.value = '' }

// computed after actor role vars are declared
const isSearching = computed(() => {
  const q = String(searchQuery.value || '').trim()
  const d = String(searchDate.value || '').trim()
  const hasStatus = (activeStatuses.value || []).length > 0
  const hasActorRole = Boolean(actorRole.value)
  const fromTo = String(createdFrom.value || '').trim() || String(createdTo.value || '').trim()
  return Boolean(q || d || hasStatus || hasActorRole || fromTo)
})

function openShow(l) { selected.value = l; modal.value = 'show' }
function closeModal() { modal.value = ''; selected.value = null }

watch(searchField, () => { searchQuery.value = ''; searchDate.value = '' })

const searchPlaceholder = computed(() => {
  switch (searchField.value) {
    case 'id': return 'Search by log ID...'
    case 'status': return 'Search by status...'
    case 'payout': return 'Search by payout ID...'
    case 'psychologist': return 'Search by psychologist name...'
    default: return 'Search...'
  }
})

const filtered = computed(() => {
  let list = Array.isArray(data.value) ? [...data.value] : []

  if (searchField.value === 'date') {
    const d = String(searchDate.value || '').trim()
    if (d) {
      list = list.filter((a) => { try { return new Date(a.created_at).toISOString().slice(0,10) === d } catch { return false } })
    }
  } else {
    const q = String(searchQuery.value || '').trim().toLowerCase()
    if (q) {
      list = list.filter((a) => {
        if (searchField.value === 'id') return String(a?.id ?? '').toLowerCase().includes(q)
        if (searchField.value === 'status') {
          const s = String(getStatus(a) || '').toLowerCase()
          return s.includes(q)
        }
        if (searchField.value === 'payout') return String(a?.target_id ?? '').toLowerCase().includes(q)
        if (searchField.value === 'psychologist') return String(a?.psychologist?.name ?? a?.meta?.psychologist_name ?? '').toLowerCase().includes(q)
        const hay = [String(a?.id ?? ''), String(a?.target_id ?? ''), String(a?.psychologist?.name ?? ''), String(a?.description ?? '')].join(' ').toLowerCase()
        return hay.includes(q)
      })
    }
  }

  if ((activeStatuses.value || []).length > 0) {
    list = list.filter((a) => { const s = String(getStatus(a) || '').toLowerCase(); return activeStatuses.value.includes(s) })
  }

  if (actorRole.value) {
    list = list.filter((a) => { const r = String(getActorRole(a) || '').toLowerCase(); return r.includes(String(actorRole.value).toLowerCase()) })
  }

  const fromVal = String(createdFrom.value || '').trim()
  const toVal = String(createdTo.value || '').trim()
  if (fromVal || toVal) {
    list = list.filter((a) => {
      const createdRaw = a?.created_at || null
      if (!createdRaw) return false
      const created = new Date(createdRaw)
      if (fromVal) { const from = new Date(fromVal); if (!Number.isNaN(from.getTime()) && created < from) return false }
      if (toVal) { const to = new Date(toVal); to.setHours(23,59,59,999); if (!Number.isNaN(to.getTime()) && created > to) return false }
      return true
    })
  }

  return list
})

const sortKey = ref('created_at')
const sortDir = ref('desc')

function toggleSort(key) { if (sortKey.value === key) { sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'; return } sortKey.value = key; sortDir.value = 'asc' }

function getSortValue(a, key) {
  switch (key) {
    case 'id': return Number(a?.id || 0)
    case 'action': return String(a?.action || '').toLowerCase()
    case 'actor_role': return String(a?.actor_role || '').toLowerCase()
    case 'status': return String(getStatus(a) || '').toLowerCase()
    case 'payout': return Number(a?.target_id || 0)
    case 'psychologist': return String(a?.psychologist?.name || a?.meta?.psychologist_name || '').toLowerCase()
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

const totalFiltered = computed(() => (filtered.value || []).length)
const totalPages = computed(() => Math.max(1, Math.ceil(totalFiltered.value / perPage.value)))

const paginated = computed(() => { if (!isSearching.value) return sorted.value; const start = (clientPage.value - 1) * perPage.value; return sorted.value.slice(start, start + perPage.value) })

const brandColor = 'rgb(89 151 172 / var(--tw-bg-opacity, 1))'

function formatDate(value) { if (!value) return '—'; try { return new Intl.DateTimeFormat(undefined, { year: 'numeric', month: 'short', day: '2-digit' }).format(new Date(value)) } catch { return String(value) } }

function linkClasses(link) { const base = 'inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-sm border '; if (link.active) return base + 'bg-indigo-600 text-white border-indigo-600'; if (!link.url) return base + 'bg-gray-50 text-gray-400 border-gray-200 cursor-not-allowed'; return base + 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }

const savingId = ref(null)

function openShowLog(l) { openShow(l) }

function applySearch() { router.get(route('admin.logs.payouts.index'), { q: searchQuery.value, page: 1 }, { preserveState: true, replace: true }) }

function goto(pageNum) { if (isSearching.value) { clientPage.value = pageNum } else { router.get(route('admin.logs.payouts.index'), { page: pageNum }, { preserveState: true, replace: true }) } }

function statusBadgeClass(status) {
  if (!status) return 'bg-gray-50 text-gray-700'
  const s = String(status).toLowerCase()
  switch (s) {
    case 'paid': return 'bg-green-100 text-green-800'
    case 'pending': return 'bg-yellow-100 text-yellow-800'
    case 'on_hold': return 'bg-indigo-100 text-indigo-800'
    case 'refunded': return 'bg-red-100 text-red-800'
    case 'confirmed': return 'bg-blue-100 text-blue-800'
    case 'completed': return 'bg-green-100 text-green-800'
    case 'cancelled': return 'bg-red-100 text-red-800'
    case 'no_show': return 'bg-gray-100 text-gray-800'
    default: return 'bg-gray-50 text-gray-700'
  }
}

function normalizeStatusToken(token) {
  const t = String(token || '').trim().toLowerCase()
  const map = {
    'pending': 'pending', 'created': 'pending',
    'confirm': 'confirmed', 'confirmed': 'confirmed',
    'complete': 'completed', 'completed': 'completed',
    'cancel': 'cancelled', 'canceled': 'cancelled', 'cancelled': 'cancelled',
    'no_show': 'no_show', 'no-show': 'no_show', 'no show': 'no_show', 'noshow': 'no_show',
    'refund': 'refunded', 'refunded': 'refunded', 'refunds': 'refunded',
    'paid': 'paid', 'paid_at': 'paid',
    'on_hold': 'on_hold', 'on-hold': 'on_hold', 'on hold': 'on_hold'
  }
  return map[t] || (map[t.replace('-', '_')] || null)
}

function getStatus(l) {
  if (!l) return null
  if (l.status) {
    const norm = normalizeStatusToken(l.status)
    return norm || String(l.status)
  }
  const action = String(l.action || '').toLowerCase()
  if (action.includes('created')) return 'pending'
  if (action.includes('confirm')) return 'confirmed'
  if (action.includes('complete') || action.includes('completed')) return 'completed'
  if (action.includes('cancel')) return 'cancelled'
  if (action.includes('no_show') || action.includes('no-show') || action.includes('no show')) return 'no_show'
  if (action.includes('refund') || action.includes('refunded')) return 'refunded'

  const desc = String(l.description || '').toLowerCase()
  if (/\b(no[_\- ]?show)\b/.test(desc)) return 'no_show'
  if (/\b(cancel|cancelled|canceled)\b/.test(desc)) return 'cancelled'
  if (/\b(created|pending)\b/.test(desc)) return 'pending'
  if (/\b(confirm|confirmed)\b/.test(desc)) return 'confirmed'
  if (/\b(complete|completed)\b/.test(desc)) return 'completed'
  if (/\b(refund|refunded|refunds)\b/.test(desc)) return 'refunded'

  let m = desc.match(/to\s+([a-z_\-]+)/)
  if (m && m[1]) return normalizeStatusToken(m[1])
  m = desc.match(/status\s+([a-z_\-]+)/)
  if (m && m[1]) return normalizeStatusToken(m[1])
  return null
}

function getActorRole(l) {
  if (!l) return null
  if (l.actor_role) return String(l.actor_role).toLowerCase()
  // treat absence of actor_role as system
  return l.actor_id ? String(l.actor_role || '').toLowerCase() || 'system' : 'system'
}

</script>

<style scoped></style>
