<template>
  <div class="p-6 space-y-6">
    <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div>
        <div v-if="flashMessage" class="mb-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3">
          <div class="flex items-start justify-between gap-3">
            <div class="text-sm text-green-800">{{ flashMessage }}</div>
            <button @click="clearFlash" class="text-green-700/80">✕</button>
          </div>
        </div>

        <div v-if="flashError" class="mb-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3">
          <div class="flex items-start justify-between gap-3">
            <div class="text-sm text-red-800">{{ flashError }}</div>
            <button @click="clearError" class="text-red-700/80">✕</button>
          </div>
        </div>

        <h1 class="text-2xl font-semibold text-gray-900">Reports</h1>
      </div>

      <div class="flex items-center gap-3 w-full md:w-auto">
        <div class="flex items-center gap-2 flex-1">
          <select
            v-model="searchField"
            class="h-10 w-40 md:w-48 shrink-0 rounded-lg border-gray-300 bg-white px-3 text-sm text-gray-700"
            aria-label="Search filter"
          >
            <option value="id">ID</option>
            <option value="reporter">Reporter</option>
            <option value="reported">Reported</option>
            <option value="status">Status</option>
          </select>

          <div class="relative flex-1 md:w-80">
            <input
              v-model="searchQuery"
              type="text"
              :placeholder="searchPlaceholder"
              class="w-full rounded-lg border-gray-300 pl-10 pr-3 py-2"
            />

            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M9 3a6 6 0 104.472 10.03l2.249 2.249a1 1 0 001.415-1.415l-2.249-2.249A6 6 0 009 3zm-4 6a4 4 0 118 0 4 4 0 01-8 0z" clip-rule="evenodd" />
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
                <button type="button" @click="toggleSort('reporter')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                  Reporter
                  <SortIcon :active="sortKey === 'reporter'" :dir="sortDir" />
                </button>
              </th>
              <th class="px-4 py-3 text-left">
                <button type="button" @click="toggleSort('reported')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                  Reported
                  <SortIcon :active="sortKey === 'reported'" :dir="sortDir" />
                </button>
              </th>
              <th class="px-4 py-3 text-left">
                <button type="button" @click="toggleSort('reason')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                  Reason
                  <SortIcon :active="sortKey === 'reason'" :dir="sortDir" />
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
            <tr v-for="r in paginated" :key="r.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-sm text-gray-700">#{{ r.id }}</td>
              <td class="px-4 py-3">
                <div class="text-sm font-medium text-gray-900">{{ r.reporter?.name || r.reporter?.type || '—' }}</div>
              </td>
              <td class="px-4 py-3">
                <div class="text-sm font-medium text-gray-900">{{ r.reported?.name || r.reported?.type || '—' }}</div>
              </td>
              <td class="px-4 py-3 text-sm text-gray-700 truncate max-w-xs">{{ r.reason }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">
                <div class="flex items-center gap-2">
                  <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium" :class="reportBadge(r)">
                    {{ reportLabel(r) }}
                  </span>
                </div>
              </td>
              <td class="px-4 py-3 text-sm text-right">
                <div class="flex items-center justify-end gap-2">
                  <button type="button" title="View" @click="openShow(r)" class="inline-flex items-center justify-center h-9 w-9 rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                      <path d="M12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z" />
                      <path fill-rule="evenodd" d="M12 3c5.392 0 9.878 3.88 10.818 9-.94 5.12-5.426 9-10.818 9S2.122 17.12 1.182 12C2.122 6.88 6.608 3 12 3Zm0 15a6 6 0 0 0 6-6 6 6 0 0 0-12 0 6 6 0 0 0 6 6Z" clip-rule="evenodd" />
                    </svg>
                  </button>

                  <button v-if="!r.is_resolved" @click="resolveReport(r)" :disabled="savingId===r.id" class="inline-flex items-center justify-center h-9 px-3 rounded-lg border border-yellow-200 bg-yellow-50 text-yellow-800 hover:bg-yellow-100">
                    Resolve
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="(paginated || []).length === 0">
              <td class="px-4 py-6 text-center text-sm text-gray-500" colspan="6">No reports</td>
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
            Showing {{ reports.from }}-{{ reports.to }} of {{ reports.total }}
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
              v-for="link in reports.links || []"
              :key="String(link.label)"
              :href="link.url || '#'"
              :class="linkClasses(link)"
              preserve-scroll
              v-html="link.label"
              :style="link.active ? { backgroundColor: brandColor, borderColor: brandColor, color: '#fff' } : null"
            />
          </template>
        </div>
      </div>
    </div>

    <Show :show="modal === 'show'" :report="selected" @close="closeModal" />
  </div>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import SortIcon from '@/Pages/Admin/Psychologist/SortIcon.vue'
import Swal from 'sweetalert2'
import Show from './Show.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({ reports: Object, status: { type: String, default: '' } })
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

const reports = computed(() => props.reports || {})
const data = computed(() => props.reports?.data || [])
const searchField = ref('id')
const searchQuery = ref('')
const perPage = ref((props.reports && props.reports.per_page) || 15)
const clientPage = ref(1)

const isSearching = computed(() => {
  const q = String(searchQuery.value || '').trim()
  return Boolean(q)
})

watch([searchQuery, searchField], () => {
  clientPage.value = 1
})

const modal = ref('')
const selected = ref(null)

const filtered = computed(() => {
  let list = Array.isArray(data.value) ? [...data.value] : []

  const q = String(searchQuery.value || '').trim().toLowerCase()
  if (!q) return list

  return list.filter((r) => {
    if (searchField.value === 'id') {
      return String(r?.id ?? '').toLowerCase().includes(q)
    }

    if (searchField.value === 'reporter') {
      const hay = [String(r?.reporter?.name ?? ''), String(r?.reporter?.type ?? '')].join(' ').toLowerCase()
      return hay.includes(q)
    }

    if (searchField.value === 'reported') {
      const hay = [String(r?.reported?.name ?? ''), String(r?.reported?.type ?? '')].join(' ').toLowerCase()
      return hay.includes(q)
    }

    if (searchField.value === 'status') {
      const statusText = r?.is_resolved ? 'resolved' : 'open'
      const hay = [statusText, String(r?.is_resolved ? 1 : 0)].join(' ').toLowerCase()
      return hay.includes(q)
    }

    const hay = [String(r?.id ?? ''), String(r?.reason ?? ''), String(r?.reporter?.name ?? ''), String(r?.reported?.name ?? ''), r?.is_resolved ? 'resolved' : 'open'].join(' ').toLowerCase()
    return hay.includes(q)
  })
})

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

function getSortValue(r, key) {
  switch (key) {
    case 'id':
      return Number(r?.id || 0)
    case 'reporter':
      return String(r?.reporter?.name || r?.reporter?.type || '').toLowerCase()
    case 'reported':
      return String(r?.reported?.name || r?.reported?.type || '').toLowerCase()
    case 'reason':
      return String(r?.reason || '').toLowerCase()
    case 'status':
      return r?.is_resolved ? 1 : 0
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

const totalFiltered = computed(() => (sorted.value || []).length)
const totalPages = computed(() => Math.max(1, Math.ceil(totalFiltered.value / perPage.value)))

const paginated = computed(() => {
  if (!isSearching.value) return sorted.value
  const start = (clientPage.value - 1) * perPage.value
  return sorted.value.slice(start, start + perPage.value)
})

const brandColor = 'rgb(89 151 172 / var(--tw-bg-opacity, 1))'

const savingId = ref(null)

function openShow(r) { selected.value = r; modal.value = 'show' }
function closeModal() { modal.value = ''; selected.value = null }

watch(searchField, () => {
  searchQuery.value = ''
})

const searchPlaceholder = computed(() => {
  switch (searchField.value) {
    case 'id':
      return 'Search by ID...'
    case 'reporter':
      return 'Search by reporter...'
    case 'reported':
      return 'Search by reported...'
    case 'status':
      return 'Search by status (open/resolved)...'
    default:
      return 'Search...'
  }
})

function linkClasses(link) {
  const base = 'inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-sm border '
  if (link.active) return base + 'bg-indigo-600 text-white border-indigo-600'
  if (!link.url) return base + 'bg-gray-50 text-gray-400 border-gray-200 cursor-not-allowed'
  return base + 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'
}

function reportLabel(r) {
  return r?.is_resolved ? 'Resolved' : 'Open'
}

function reportBadge(r) {
  return r?.is_resolved
    ? 'bg-green-50 text-green-700 ring-1 ring-green-200'
    : 'bg-yellow-50 text-yellow-800 ring-1 ring-yellow-200'
}

async function resolveReport(r) {
  if (!r || savingId.value === r.id) return
  const result = await Swal.fire({ title: 'Resolve report?', text: `Mark report #${r.id} as resolved?`, icon: 'question', showCancelButton: true, confirmButtonText: 'Yes, resolve', cancelButtonText: 'Cancel', confirmButtonColor: 'rgb(89,151,172)' })
  if (!result.isConfirmed) return
  savingId.value = r.id
  await router.patch(route('admin.reports.update', r.id), { is_resolved: true }, {
    preserveScroll: true,
    onError: (errors) => { const msg = errors?.message || errors?.error || errors?.status; if (msg) showError(msg) },
    onFinish: () => { savingId.value = null }
  })
}

watch(() => props.reports, () => { clientPage.value = 1 })
</script>

<style scoped></style>
