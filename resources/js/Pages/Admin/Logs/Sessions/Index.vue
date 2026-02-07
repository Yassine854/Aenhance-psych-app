<template>
  <div class="p-6 space-y-6">
    <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Session Logs</h1>
        <p class="text-sm text-gray-600">Manage audit entries related to appointment sessions.</p>
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

    <ShowModal :show="modal === 'show'" :log="selected" :session="selectedSession" @close="closeModal" />
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
const selectedSession = ref(null)

const sortKey = ref('id')
const sortDir = ref('desc')

const filteredLogs = computed(() => {
  const q = String(searchQuery.value || '').trim().toLowerCase()

  const list = (logsData.value || []).filter(l => {
    // only show session status actions (created -> active, updated -> completed)
    const actionRaw = String(l?.action || '').toLowerCase()
    if (!(actionRaw === 'updated_session_status' || actionRaw === 'created_session_status')) return false

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

    if ((activeStatuses.value || []).length > 0) {
      const s = String(getStatus(l) || '').toLowerCase()
      if (!activeStatuses.value.includes(s)) return false
    }

    if (actorRole.value) {
      const role = String(l.actor_role || '').toLowerCase()
      if (!role.includes(String(actorRole.value).toLowerCase())) return false
    }

    if (createdFrom.value) {
      const from = new Date(createdFrom.value)
      const created = new Date(l.created_at)
      if (Number.isNaN(from.getTime()) === false && created < from) return false
    }
    if (createdTo.value) {
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

const filtersOpen = ref(false)
const statusOptions = [
  { value: 'active', label: 'Active' },
  { value: 'completed', label: 'Completed' },
]
const activeStatuses = ref([])
const actorRole = ref('')
const createdFrom = ref('')
const createdTo = ref('')

function applyFilters() { filtersOpen.value = false }
function clearFilters() { activeStatuses.value = []; actorRole.value = ''; createdFrom.value = ''; createdTo.value = '' }
function openShow(log) {
  selected.value = log
  selectedSession.value = null
  try {
    const ap = log?.appointment || null
    if (ap) {
      const p = ap.patient || null
      const ph = ap.psychologist || null
      selectedSession.value = {
        appointment_id: ap.id ?? null,
        room_id: null,
        started_at: null,
        ended_at: null,
        patient_joined_at: null,
        psychologist_joined_at: null,
        patient_left_at: null,
        psychologist_left_at: null,
        duration_minutes: null,
        status: null,
        patient: p ? {
          id: p.id ?? null,
          name: p.name ?? null,
          email: p.email ?? null,
          profile: p.patient_profile ?? p.patientProfile ?? null,
        } : null,
        psychologist: ph ? {
          id: ph.id ?? null,
          name: ph.name ?? null,
          email: ph.email ?? null,
          profile: ph.psychologist_profile ?? ph.psychologistProfile ?? null,
        } : null,
      }
    }
  } catch (e) {
    selectedSession.value = null
  }
  modal.value = 'show'
}

function closeModal() { modal.value = null; selected.value = null; selectedSession.value = null }

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
  const role = String(log.actor_role || '').toLowerCase()
  if (role.includes('admin')) return 'Admin'
  if (role.includes('system')) return 'SYSTEM'

  // try to use related appointment/session data when present
  const sess = log.appointment_session || log.session || log.appointmentSession || null
  const appt = log.appointment || null
  const target = sess || appt || null
  if (!target) return log.actor_id ? String(log.actor_id) : '-'

  if (role.includes('patient')) {
    const u = target.patient || null
    if (!u) return '-'
    if (u.name) return u.name
    const profile = u.patient_profile || u.patientProfile || null
    if (profile && (profile.first_name || profile.last_name)) return ((profile.first_name || '') + (profile.last_name ? ` ${profile.last_name}` : '')).trim()
    return u.email || '-'
  }
  if (role.includes('psychologist')) {
    const u = target.psychologist || null
    if (!u) return '-'
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
  // action-driven mapping
  if (action === 'created_session_status') return 'active'
  if (action === 'updated_session_status') return 'completed'

  const desc = String(log.description || '').toLowerCase()
  // explicit status mentions (prefer these)
  const explicit = desc.match(/status\s+([a-z_\-]+)/) || desc.match(/to\s+([a-z_\-]+)/)
  if (explicit && explicit[1]) {
    const t = explicit[1].replace(/-/g, '_').toLowerCase()
    if (t === 'active') return 'active'
    if (t === 'completed' || t === 'complete') return 'completed'
    return null
  }

  if (/\b(active|in[- ]?room)\b/.test(desc)) return 'active'
  if (/\b(completed|complete)\b/.test(desc)) return 'completed'

  return null
}

function statusBadgeClass(status) {
  if (!status) return 'bg-gray-50 text-gray-700'
  const s = String(status).toLowerCase()
  switch (s) {
    case 'active':
      return 'bg-blue-100 text-blue-800'
    case 'completed':
      return 'bg-green-100 text-green-800'
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
