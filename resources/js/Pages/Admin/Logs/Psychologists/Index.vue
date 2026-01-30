<template>
  <div class="p-6 space-y-6">
    <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Psychologist Logs</h1>
        <p class="text-sm text-gray-600">Manage audit entries related to psychologist profiles.</p>
      </div>
      
      <div class="flex items-center gap-3 w-full md:w-auto">
        <div class="flex items-center gap-2 flex-1">
          <select v-model="searchField" class="h-10 w-36 md:w-44 shrink-0 rounded-lg border-gray-300 bg-white px-3 text-sm text-gray-700">
            <option value="id">ID</option>
            <option value="actor">Target</option>
            <option value="action">Action</option>
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
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <div class="text-sm font-medium text-gray-700 mb-2">Actor Role</div>
          <select v-model="actorRole" class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm">
            <option value="">Any</option>
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
                <button type="button" @click="toggleSort('target')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                  Target
                  <SortIcon :active="sortKey === 'target'" :dir="sortDir" />
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
              <td class="px-4 py-3 text-sm text-gray-700">{{ getTargetLabel(log) }}</td>
              <td class="px-4 py-3 text-sm text-gray-700">{{ formatDate(log.created_at) }}</td>
              <td class="px-4 py-3 text-right">
                <div class="inline-flex items-center gap-2">
                  <button type="button" title="View" @click="openShow(log)" class="inline-flex items-center justify-center h-9 w-9 rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z" /><path fill-rule="evenodd" d="M12 3c5.392 0 9.878 3.88 10.818 9-.94 5.12-5.426 9-10.818 9S2.122 17.12 1.182 12C2.122 6.88 6.608 3 12 3Zm0 15a6 6 0 0 0 6-6 6 6 0 0 0-12 0 6 6 0 0 0 6 6Z" clip-rule="evenodd" /></svg>
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
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import ShowModal from './Show.vue'
import SortIcon from '@/Components/SortIcon.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({ logs: Object })

const logsData = ref(props.logs?.data ? [...props.logs.data] : [])
watch(() => props.logs?.data, (next) => { logsData.value = next ? [...next] : [] })

const searchQuery = ref('')
const searchField = ref('id')
const modal = ref(null)
const selected = ref(null)

const sortKey = ref('id')
const sortDir = ref('desc')

const searchPlaceholder = computed(() => {
  switch (searchField.value) {
    case 'id': return 'Search by ID...'
    case 'actor': return 'Search by target...'
    case 'action': return 'Search by action...'
    default: return 'Search...'
  }
})

// filter panel state
const filtersOpen = ref(false)
const actorRole = ref('')
const createdFrom = ref('')
const createdTo = ref('')

function applyFilters() {
  // filters are reactive; closing the panel is a UX affordance
  filtersOpen.value = false
}

function clearFilters() {
  actorRole.value = ''
  createdFrom.value = ''
  createdTo.value = ''
}

const filteredLogs = computed(() => {
  const q = String(searchQuery.value || '').trim().toLowerCase()
  return (logsData.value || []).filter(l => {
    if (q) {
      switch (searchField.value) {
        case 'id': if (!String(l?.id ?? '').toLowerCase().includes(q)) return false; break
        case 'actor': if (!String(getActorName(l) || '').toLowerCase().includes(q)) return false; break
        case 'action': if (!String(l.action || '').toLowerCase().includes(q)) return false; break
      }
    }

    // actor role filter
    if (actorRole.value) {
      const role = String(l.actor_role || '').toLowerCase()
      if (!role.includes(String(actorRole.value).toLowerCase())) return false
    }

    // created between filters
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
})

const sortedLogs = computed(() => {
  const list = Array.isArray(filteredLogs?.value) ? [...filteredLogs.value] : []
  const key = sortKey.value
  const dir = sortDir.value
  list.sort((a,b) => {
    let av = ''
    let bv = ''
    switch (key) {
      case 'id': av = Number(a.id||0); bv = Number(b.id||0); break
      case 'action': av = String(a.action||'').toLowerCase(); bv = String(b.action||'').toLowerCase(); break
      case 'actor_role': av = String(a.actor_role||'').toLowerCase(); bv = String(b.actor_role||'').toLowerCase(); break
      case 'actor': av = String(getActorName(a)||'').toLowerCase(); bv = String(getActorName(b)||'').toLowerCase(); break
      case 'target':
        av = String((a.target_type ? a.target_type + ' #' : '') + (a.target_id ?? '')).toLowerCase()
        bv = String((b.target_type ? b.target_type + ' #' : '') + (b.target_id ?? '')).toLowerCase()
        break
      case 'created': av = new Date(a.created_at||0).getTime(); bv = new Date(b.created_at||0).getTime(); break
      default: av = String(a[key]??'').toLowerCase(); bv = String(b[key]??'').toLowerCase();
    }
    if (av < bv) return dir === 'asc' ? -1 : 1
    if (av > bv) return dir === 'asc' ? 1 : -1
    return 0
  })
  return list
})

function toggleSort(key) { if (sortKey.value === key) { sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'; return } sortKey.value = key; sortDir.value = 'asc' }

function getActorName(log) {
  if (!log) return '-'
  // If actor_user attached by controller (admin or other user), prefer that
  if (log.actor_user && log.actor_user.name) return log.actor_user.name
  if (log.actor_user && log.actor_user.username) return log.actor_user.username
  // Prefer nested psychologist -> user -> name when controller attached it
  const psych = log.psychologist || log.psychologist_profile || log.psychologistProfile || null
  if (psych) {
    if (psych.user && psych.user.name) return psych.user.name
    if (psych.name) return psych.name
    if (psych.user && psych.user.username) return psych.user.username
  }

  if (log.actor_username) return log.actor_username
  if (log.actor_name) return log.actor_name
  return log.actor_id ? String(log.actor_id) : '-'
}

function getTargetLabel(log) {
  if (!log) return '-'
  // If the log targets a psychologist profile, prefer attached psychologist name
  if (log.target_type === 'PsychologistProfile') {
    const p = log.psychologist || null
    if (p) {
      if (p.user && p.user.name) return p.user.name
      if (p.name) return p.name
    }
    return `${log.target_type} #${log.target_id}`
  }
  // Generic fallback
  return `${log.target_type || 'Target'} #${log.target_id ?? ''}`
}

function formatDate(value) { if (!value) return '-'; try { const d = new Date(value); if (Number.isNaN(d.getTime())) return String(value); return d.toLocaleString() } catch { return String(value) } }

function openShow(log) { selected.value = log; modal.value = 'show' }
function closeModal() { modal.value = null; selected.value = null }

function linkClasses(link) {
  const base = 'px-3 py-1.5 rounded border text-sm'
  if (link.active) return `${base} bg-indigo-600 border-indigo-600 text-white`
  if (!link.url) return `${base} bg-gray-50 border-gray-200 text-gray-400 cursor-not-allowed`
  return `${base} bg-white border-gray-200 text-gray-700 hover:bg-gray-50`
}

function linkStyle(link) { if (link && link.active) { const c = 'rgb(89 151 172 / var(--tw-bg-opacity, 1))'; return { backgroundColor: c, borderColor: c, color: '#ffffff' } } return {} }
</script>
