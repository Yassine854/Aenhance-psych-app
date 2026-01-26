<template>
  <Head title="Patients" />

  <Navbar :canLogin="canLogin" :canRegister="canRegister" :authUser="authUser || page.props?.auth?.user" />

  <div class="min-h-[calc(100vh-112px)] bg-gray-50">
    <div class="bg-gradient-to-r from-[#af5166] to-[#5997ac]">
      <div class="mx-auto max-w-6xl px-4 py-8">
        <h1 class="text-2xl sm:text-3xl font-semibold text-white">Patients</h1>
        <p class="mt-1 text-sm text-white/90">Patients who had sessions with you. Click a row to view session notes.</p>
      </div>
    </div>

    <div class="mx-auto max-w-6xl px-4 py-8">
      <div class="p-6 space-y-6">
        <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
          <div>
            <div class="text-sm text-gray-600">Manage patient session notes and history.</div>
          </div>

          <div class="flex items-center gap-3 w-full md:w-auto">
            <select v-model="searchField" class="h-10 w-40 md:w-48 shrink-0 rounded-lg border-gray-300 bg-white px-3 text-sm text-gray-700">
              <option value="patient">Patient</option>
              <option value="date">Last Session Date</option>
            </select>

            <div class="relative flex-1 md:w-80">
              <input v-if="searchField !== 'date'" v-model="searchQuery" type="text" :placeholder="searchPlaceholder"
                class="w-full rounded-lg border-gray-300 pl-10 pr-3 py-2" />

              <input v-else v-model="searchDate" type="date" class="w-full rounded-lg border-gray-300 pl-10 pr-10 py-2" />

              <button @click="clearDate" v-if="searchField === 'date' && searchDate" type="button"
                      class="absolute right-2 top-1/2 -translate-y-1/2 inline-flex items-center justify-center h-7 w-7 rounded-md text-gray-500 hover:bg-gray-100">
                ✕
              </button>

              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M12.9 14.32a8 8 0 111.414-1.414l4.387 4.387a1 1 0 01-1.414 1.414l-4.387-4.387zM14 8a6 6 0 11-12 0 6 6 0 0112 0z" clip-rule="evenodd" />
              </svg>
            </div>

            <!-- search is reactive as you type; no button needed -->
          </div>
        </header>

        <div class="bg-white rounded-lg shadow overflow-hidden">
          <div class="overflow-x-auto">
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
                    <button type="button" @click="toggleSort('last_session')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                      Last Session
                      <SortIcon :active="sortKey === 'last_session'" :dir="sortDir" />
                    </button>
                  </th>
                  <th class="px-4 py-3 text-left">
                    <button type="button" @click="toggleSort('duration')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                      Duration
                      <SortIcon :active="sortKey === 'duration'" :dir="sortDir" />
                    </button>
                  </th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>

              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="p in sorted" :key="p.id" class="hover:bg-gray-50">
                  <td class="px-4 py-3">
                    <div class="text-sm font-medium text-gray-900">{{ p.name }}</div>
                  </td>
                  <td class="px-4 py-3">
                    <div class="text-sm text-gray-900">{{ formatDate(latestSessionDate(p)) }}</div>
                  </td>
                  <td class="px-4 py-3">
                    <div class="text-sm text-gray-900">{{ latestSessionDuration(p) ? (latestSessionDuration(p) + ' mins') : '—' }}</div>
                  </td>
                  <td class="px-4 py-3 text-right">
                    <button @click="openNotes(p)" class="notes-btn inline-flex items-center gap-2 h-9 px-3 rounded-lg text-xs font-medium border shadow-sm transition duration-150 hover:shadow-md" style="border-color: rgb(89 151 172 / var(--tw-bg-opacity, 1)); color: rgb(89 151 172 / var(--tw-bg-opacity, 1));" title="Notes">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" :style="{ color: 'inherit' }">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5l3 3L12 15l-4 1 1-4 9.5-9.5z" />
                      </svg>
                      Notes
                    </button>
                  </td>
                </tr>

                <tr v-if="patientsList.length === 0">
                  <td class="p-8 text-center text-gray-500" colspan="4">
                    <div class="mx-auto h-10 w-10 text-gray-300">📘</div>
                    <div class="mt-4 text-lg font-medium">No patients found</div>
                    <div class="mt-1 text-sm">Try a different search or remove filters.</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="flex items-center justify-between px-4 py-3 border-t border-gray-200">
            <div class="text-sm text-gray-600">Showing {{ patientsList.length }} patients</div>
            <div class="flex items-center gap-2">
              <!-- Placeholder for pagination links if needed in future -->
            </div>
          </div>
        </div>

        <NotesBook v-if="showNotes" :patient="selectedPatient" @close="closeNotes" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, usePage } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import Navbar from '@/Components/Navbar.vue'
import NotesBook from './NotesBook.vue'
import SortIcon from '@/Pages/Admin/Psychologist/SortIcon.vue'

const props = defineProps({ patients: Array, search: String, canLogin: Boolean, canRegister: Boolean, authUser: Object })
const page = usePage()

const patientsList = ref(props.patients || [])
const searchQuery = ref(props.search || '')
const searchDate = ref('')
const searchField = ref('patient')

const selectedPatient = ref(null)
const showNotes = ref(false)

const sortKey = ref('last_session')
const sortDir = ref('desc')


const filtered = computed(() => {
  const list = patientsList.value || []
  if (searchField.value === 'date') {
    const d = String(searchDate.value || '').trim()
    if (!d) return list
    return list.filter((p) => {
      if (!p.sessions || !p.sessions.length) return false
      return p.sessions.some((s) => {
        try {
          const iso = new Date(s.started_at).toISOString().slice(0, 10)
          return iso === d
        } catch {
          return false
        }
      })
    })
  }

  const q = String(searchQuery.value || '').trim().toLowerCase()
  if (!q) return list

  return list.filter((p) => String(p.name || '').toLowerCase().includes(q))
})

function clearDate() { searchDate.value = '' }

function latestSessionDate(p) {
  if (!p.sessions || p.sessions.length === 0) return null
  return p.sessions[0].started_at || null
}

function latestSessionDuration(p) {
  if (!p.sessions || p.sessions.length === 0) return null
  return p.sessions[0].duration ?? null
}

function formatDate(v) {
  if (!v) return '—'
  try { return new Intl.DateTimeFormat(undefined, { year: 'numeric', month: 'short', day: '2-digit' }).format(new Date(v)) } catch { return String(v) }
}

function toggleSort(key) {
  if (sortKey.value === key) sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  else { sortKey.value = key; sortDir.value = 'asc' }
}

const sorted = computed(() => {
  const list = filtered.value || []
  const key = sortKey.value
  const dir = sortDir.value === 'asc' ? 1 : -1

  return [...list].sort((a, b) => {
    if (key === 'patient') {
      return String(a.name || '').localeCompare(String(b.name || '')) * dir
    }
    if (key === 'last_session') {
      const ta = latestSessionDate(a) ? new Date(latestSessionDate(a)).getTime() : 0
      const tb = latestSessionDate(b) ? new Date(latestSessionDate(b)).getTime() : 0
      return (ta - tb) * dir
    }
    if (key === 'duration') {
      const da = Number(latestSessionDuration(a) || 0)
      const db = Number(latestSessionDuration(b) || 0)
      return (da - db) * dir
    }
    return 0
  })
})

function openNotes(p) { selectedPatient.value = p; showNotes.value = true }
function closeNotes() { selectedPatient.value = null; showNotes.value = false }

const searchPlaceholder = computed(() => (searchField.value === 'date' ? 'Search by session date...' : 'Search by patient name...'))

// keep reactive to server props changes
watch(() => props.patients, (next) => { patientsList.value = next || [] })

</script>

<style scoped>
.notes-btn { will-change: background-color, color, border-color; }
.notes-btn:hover { background-color: rgb(89 151 172 / var(--tw-bg-opacity, 1)); border-color: rgb(89 151 172 / var(--tw-bg-opacity, 1)); color: white !important; }
.notes-btn:hover svg { color: white !important; }
.btn-click-anim { animation: btnClick 240ms cubic-bezier(.2,.9,.2,1); }
@keyframes btnClick {
  0% { transform: scale(1); box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
  30% { transform: scale(0.97); box-shadow: 0 6px 18px rgba(0,0,0,0.08); }
  100% { transform: scale(1); box-shadow: 0 4px 10px rgba(0,0,0,0.06); }
}
</style>
