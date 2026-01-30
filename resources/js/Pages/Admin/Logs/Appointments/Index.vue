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
            <button type="button" @click="clearFlash" class="text-green-700/70 hover:text-green-800" aria-label="Dismiss">✕</button>
          </div>
        </div>
        <h1 class="text-2xl font-semibold text-gray-900">Appointment Logs</h1>
        <p class="text-sm text-gray-600">Manage audit entries related to appointments.</p>
      </div>

      <div class="flex items-center gap-3 w-full md:w-auto">
        <div class="flex items-center gap-2 flex-1">
          <select
            v-model="searchField"
            class="h-10 w-36 md:w-44 shrink-0 rounded-lg border-gray-300 bg-white px-3 text-sm text-gray-700"
            aria-label="Search filter"
          >
            <option value="id">ID</option>
            <option value="actor">Actor</option>
            <option value="status">Status</option>
          </select>

          <div class="relative flex-1">
            <input v-model="searchQuery" type="text" :placeholder="searchPlaceholder" class="w-full rounded-lg border-gray-300 pl-10 pr-3 py-2"/>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.9 14.32a8 8 0 111.414-1.414l4.387 4.387a1 1 0 01-1.414 1.414l-4.387-4.387zM14 8a6 6 0 11-12 0 6 6 0 0112 0z" clip-rule="evenodd"/></svg>
          </div>
        </div>
        <div class="ml-2">
          <button type="button" @click="filtersOpen = !filtersOpen" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border bg-white text-sm text-gray-700 hover:bg-gray-50">
            Filter
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 01.707 1.707L12 11.414V15a1 1 0 01-.553.894l-3 1.5A1 1 0 017 16.5V11.414L3.293 5.707A1 1 0 013 5z" clip-rule="evenodd"/></svg>
          </button>
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
            <option value="patient">Patient</option>
            <option value="psychologist">Psychologist</option>
            <option value="admin">Admin</option>
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
        <button
          type="button"
          @click="applyFilters"
          class="px-3 py-2 rounded-lg text-white text-sm hover:opacity-90"
          style="background-color: rgb(175 81 102 / var(--tw-bg-opacity, 1))"
        >
          Apply
        </button>
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
                <button type="button" @click="toggleSort('actor_username')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                  Actor
                  <SortIcon :active="sortKey === 'actor_username'" :dir="sortDir" />
                </button>
              </th>
              <th class="px-4 py-3 text-left">
                <button type="button" @click="toggleSort('status')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                  Status
                  <SortIcon :active="sortKey === 'status'" :dir="sortDir" />
                </button>
              </th>
              <th class="px-4 py-3 text-left">
                <button type="button" @click="toggleSort('created')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                  Created
                  <SortIcon :active="sortKey === 'created'" :dir="sortDir" />
                </button>
              </th>
              <th class="px-4 py-3 text-right">
                <div class="inline-flex items-center gap-1 justify-end text-xs font-medium text-gray-500 uppercase tracking-wider">
                  <span>Actions</span>
                </div>
              </th>
            </tr>
          </thead>

          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="log in sortedLogs" :key="log.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-sm text-gray-700">#{{ log.id }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ log.action }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ log.actor_role || '-' }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ getActorName(log) }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">
                <span v-if="getStatus(log)" :class="['inline-flex items-center px-2 py-0.5 rounded text-xs font-medium', statusBadgeClass(getStatus(log))]">
                  {{ getStatus(log) }}
                </span>
                <span v-else class="text-gray-500">-</span>
              </td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ formatDate(log.created_at) }}</td>
              <td class="px-4 py-3 text-right">
                <div class="inline-flex items-center gap-2">
                  <button type="button" title="View" @click="openShow(log)" class="inline-flex items-center justify-center h-9 w-9 rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50">
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
        <div class="text-sm text-gray-600">Showing {{ logs.from }}-{{ logs.to }} of {{ logs.total }}</div>
        <div class="flex items-center gap-2">
          <Link v-for="(link, i) in logs.links" :key="i" :href="link.url || '#'" :class="linkClasses(link)" :style="linkStyle(link)" preserve-scroll>
            <span v-html="link.label"></span>
          </Link>
        </div>
      </div>
    </div>

    <ShowModal :show="modal === 'show'" :log="selected" @close="closeModal" />
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ShowModal from './Show.vue'
import SortIcon from '@/Components/SortIcon.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({ logs: Object })
const page = usePage()

const logsData = ref(props.logs?.data ? [...props.logs.data] : [])
watch(
  () => props.logs?.data,
  (next) => {
    logsData.value = next ? [...next] : []
  }
)

const searchQuery = ref('')
const searchField = ref('id')
const modal = ref(null)
const selected = ref(null)
const flashMessage = ref('')

const sortKey = ref('id')
const sortDir = ref('desc')

// client-side search (mirrors Patient index)
const filteredLogs = computed(() => {
  const q = String(searchQuery.value || '').trim().toLowerCase()

  const list = (logsData.value || []).filter(l => {
    // primary search field filtering
    let matchesSearch = true
    if (q) {
      switch (searchField.value) {
        case 'id':
          matchesSearch = String(l?.id ?? '').toLowerCase().includes(q)
          break
        case 'actor':
          matchesSearch = String(getActorName(l) || '').toLowerCase().includes(q)
          break
        case 'status':
          matchesSearch = String(getStatus(l) || '').toLowerCase().includes(q)
          break
        default:
          matchesSearch = false
      }
    }
    if (!matchesSearch) return false

    // status multi-select filter
    if ((activeStatuses.value || []).length > 0) {
      const s = String(getStatus(l) || '').toLowerCase()
      if (!activeStatuses.value.includes(s)) return false
    }

    // actor role filter
    if (actorRole.value) {
      const role = String(l.actor_role || '').toLowerCase()
      if (!role.includes(String(actorRole.value).toLowerCase())) return false
    }

    // created at interval filter
    if (createdFrom.value) {
      const from = new Date(createdFrom.value)
      const created = new Date(l.created_at)
      if (Number.isNaN(from.getTime()) === false && created < from) return false
    }
    if (createdTo.value) {
      // include entire day for `createdTo`
      const to = new Date(createdTo.value)
      to.setHours(23,59,59,999)
      const created = new Date(l.created_at)
      if (Number.isNaN(to.getTime()) === false && created > to) return false
    }

    return true
  })

  return list
})

const searchPlaceholder = computed(() => {
  switch (searchField.value) {
    case 'id':
      return 'Search by ID...'
    case 'actor':
      return 'Search by actor...'
    case 'status':
      return 'Search by status...'
    default:
      return 'Search...'
  }
})

// no redirect: live client-side filtering handled by `filteredLogs`

// filter panel state + options
const filtersOpen = ref(false)
const statusOptions = [
  { value: 'pending', label: 'Pending' },
  { value: 'confirmed', label: 'Confirmed' },
  { value: 'completed', label: 'Completed' },
  { value: 'cancelled', label: 'Cancelled' },
  { value: 'no_show', label: 'No Show' },
]
const activeStatuses = ref([])
const actorRole = ref('')
const createdFrom = ref('')
const createdTo = ref('')

function applyFilters() {
  // filters are reactive; toggling panel closed is a UX aid
  filtersOpen.value = false
}

function clearFilters() {
  activeStatuses.value = []
  actorRole.value = ''
  createdFrom.value = ''
  createdTo.value = ''
}

function openShow(log) {
  selected.value = log
  modal.value = 'show'
}

function closeModal() {
  modal.value = null
  selected.value = null
}

function clearFlash() { flashMessage.value = '' }

function formatDate(value) {
  if (!value) return '-'
  try {
    const d = new Date(value)
    if (Number.isNaN(d.getTime())) return String(value)
    return d.toLocaleString()
  } catch {
    return String(value)
  }
}

function toggleSort(key) {
  if (sortKey.value === key) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
    return
  }
  sortKey.value = key
  sortDir.value = 'asc'
}

function getActorName(log) {
  if (!log) return '-'
  const appt = log.appointment || null
  if (!appt) return '-'
  // actor_role may be e.g. 'PATIENT' or 'PSYCHOLOGIST' or 'ADMIN'|'SYSTEM'
  const role = String(log.actor_role || '').toLowerCase()
  if (role.includes('patient')) {
    const u = appt.patient || null
    if (!u) return '-'
    // prefer patient profile name, then user.name, then email
    // prefer username over profile full name per request
    if (u.name) return u.name
    const profile = u.patient_profile || u.patientProfile || null
    if (profile && (profile.first_name || profile.last_name)) return ((profile.first_name || '') + (profile.last_name ? ` ${profile.last_name}` : '')).trim()
    return u.email || '-'
  }
  if (role.includes('psychologist')) {
    const u = appt.psychologist || null
    if (!u) return '-'
    // prefer psychologist profile name, then user.name, then email
    // prefer username over profile full name per request
    if (u.name) return u.name
    const profile = u.psychologist_profile || u.psychologistProfile || null
    if (profile && (profile.first_name || profile.last_name)) return ((profile.first_name || '') + (profile.last_name ? ` ${profile.last_name}` : '')).trim()
    return u.email || '-'
  }
  return log.actor_id ? String(log.actor_id) : '-'
}

function getStatus(log) {
  if (!log) return null
  const action = String(log.action || '').toLowerCase()
  if (action.includes('created')) return 'pending'
  if (action.includes('confirm')) return 'confirmed'
  if (action.includes('complete') || action.includes('completed')) return 'completed'
  if (action.includes('cancel')) return 'cancelled'
  if (action.includes('no_show') || action.includes('no-show') || action.includes('no show')) return 'no_show'

  const desc = String(log.description || '').toLowerCase()
  if (/\b(no[_\- ]?show)\b/.test(desc)) return 'no_show'
  if (/\b(cancel|cancelled|canceled)\b/.test(desc)) return 'cancelled'
  if (/\b(created|pending)\b/.test(desc)) return 'pending'
  if (/\b(confirm|confirmed)\b/.test(desc)) return 'confirmed'
  if (/\b(complete|completed)\b/.test(desc)) return 'completed'

  const toMatch = desc.match(/to\s+([a-z_\-]+)/) || desc.match(/status\s+([a-z_\-]+)/)
  if (toMatch && toMatch[1]) {
    const t = toMatch[1].replace(/-/g, '_').toLowerCase()
    const map = {
      pending: 'pending',
      created: 'pending',
      confirm: 'confirmed',
      confirmed: 'confirmed',
      complete: 'completed',
      completed: 'completed',
      cancel: 'cancelled',
      canceled: 'cancelled',
      cancelled: 'cancelled',
      'no_show': 'no_show',
      'no-show': 'no_show',
      'no show': 'no_show',
      noshow: 'no_show',
    }
    return map[t] ?? null
  }

  return null
}

function statusBadgeClass(status) {
  if (!status) return 'bg-gray-50 text-gray-700'
  const s = String(status).toLowerCase()
  switch (s) {
    case 'pending':
      return 'bg-yellow-100 text-yellow-800'
    case 'confirmed':
      return 'bg-blue-100 text-blue-800'
    case 'completed':
      return 'bg-green-100 text-green-800'
    case 'cancelled':
    case 'canceled':
      return 'bg-red-100 text-red-800'
    case 'no_show':
      return 'bg-gray-100 text-gray-800'
    default:
      return 'bg-gray-50 text-gray-700'
  }
}

const sortedLogs = computed(() => {
  const list = Array.isArray(filteredLogs?.value) ? [...filteredLogs.value] : []
  const key = sortKey.value
  const dir = sortDir.value

  list.sort((a, b) => {
    let av = ''
    let bv = ''
    switch (key) {
      case 'id':
        av = Number(a.id || 0)
        bv = Number(b.id || 0)
        break
      case 'action':
        av = String(a.action || '').toLowerCase()
        bv = String(b.action || '').toLowerCase()
        break
      case 'created':
        av = new Date(a.created_at || 0).getTime()
        bv = new Date(b.created_at || 0).getTime()
        break
      case 'actor_role':
        av = String(a.actor_role || '').toLowerCase()
        bv = String(b.actor_role || '').toLowerCase()
        break
      case 'status':
        av = String(getStatus(a) || '').toLowerCase()
        bv = String(getStatus(b) || '').toLowerCase()
        break
      case 'actor_username':
        av = String(getActorName(a) || '').toLowerCase()
        bv = String(getActorName(b) || '').toLowerCase()
        break
      case 'target':
        av = String((a.target_type ? a.target_type + ' #' : '') + (a.target_id ?? '')).toLowerCase()
        bv = String((b.target_type ? b.target_type + ' #' : '') + (b.target_id ?? '')).toLowerCase()
        break
      default:
        av = String(a[key] ?? '').toLowerCase()
        bv = String(b[key] ?? '').toLowerCase()
    }

    if (av < bv) return dir === 'asc' ? -1 : 1
    if (av > bv) return dir === 'asc' ? 1 : -1
    return 0
  })

  return list
})

function linkClasses(link) {
  const base = 'px-3 py-1.5 rounded border text-sm'
  if (link.active) return `${base} bg-indigo-600 border-indigo-600 text-white`
  if (!link.url) return `${base} bg-gray-50 border-gray-200 text-gray-400 cursor-not-allowed`
  return `${base} bg-white border-gray-200 text-gray-700 hover:bg-gray-50`
}

function linkStyle(link) {
  if (link && link.active) {
    const c = 'rgb(89 151 172 / var(--tw-bg-opacity, 1))'
    return { backgroundColor: c, borderColor: c, color: '#ffffff' }
  }
  return {}
}
</script>
