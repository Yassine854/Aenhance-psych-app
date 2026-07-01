<template>
  <Head :title="t('psychologistPatients.title')" />

  <Navbar :canLogin="canLogin" :canRegister="canRegister" :authUser="authUser || page.props?.auth?.user" />

  <div class="min-h-[calc(100vh-112px)] bg-gray-50">
    <div class="bg-gradient-to-r from-[#af5166] to-[#5997ac]">
      <div class="mx-auto max-w-6xl px-4 py-8">
        <h1 class="text-2xl sm:text-3xl font-semibold text-white">{{ t('psychologistPatients.title') }}</h1>
        <p class="mt-1 text-sm text-white/90">{{ t('psychologistPatients.subtitle') }}</p>
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
                    <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.61-1.814a.75.75 0 0 0-1.22-.872l-3.236 4.53-1.784-1.784a.75.75 0 1 0-1.06 1.06l2.4 2.4a.75.75 0 0 0 1.14-.094l3.76-5.24Z" clip-rule="evenodd" />
                  </svg>
                  <div>
                    <div class="text-sm font-medium text-green-800">{{ t('psychologistPatients.success') }}</div>
                    <div class="text-sm text-green-800">{{ flashMessage }}</div>
                  </div>
                </div>
                <button type="button" @click="clearFlash" class="text-green-700/70 hover:text-green-800" :aria-label="t('psychologistPatients.dismiss')">
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
                    <div class="text-sm font-medium text-red-800">{{ t('psychologistPatients.error') }}</div>
                    <div class="text-sm text-red-800">{{ flashError }}</div>
                  </div>
                </div>
                <button type="button" @click="clearError" class="text-red-700/70 hover:text-red-800" :aria-label="t('psychologistPatients.dismiss')">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
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
              :aria-label="t('psychologistPatients.searchFilter')"
            >
              <option value="patient">{{ t('psychologistPatients.patient') }}</option>
              <option value="date">{{ t('psychologistPatients.lastSessionDate') }}</option>
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
                  :aria-label="t('psychologistPatients.clearText')"
                  :title="t('psychologistPatients.clear')"
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
                  :aria-label="t('psychologistPatients.searchDate')"
                />

                <button
                  v-if="searchDate"
                  type="button"
                  @click="searchDate = ''"
                  class="absolute right-2 top-1/2 -translate-y-1/2 inline-flex items-center justify-center h-7 w-7 rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700"
                  :aria-label="t('psychologistPatients.clearDate')"
                  :title="t('psychologistPatients.clear')"
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
                        {{ t('psychologistPatients.patient') }}
                        <SortIcon :active="sortKey === 'patient'" :dir="sortDir" />
                      </button>
                    </th>
                    <th class="px-4 py-3 text-left">
                      <button type="button" @click="toggleSort('last_session')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                        {{ t('psychologistPatients.lastSession') }}
                        <SortIcon :active="sortKey === 'last_session'" :dir="sortDir" />
                      </button>
                    </th>
                    <th class="px-4 py-3 text-left">
                      <button type="button" @click="toggleSort('duration')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                        {{ t('psychologistPatients.duration') }}
                        <SortIcon :active="sortKey === 'duration'" :dir="sortDir" />
                      </button>
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ t('psychologistPatients.actions') }}</th>
                  </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">
                  <tr
                    v-for="p in sorted"
                    :key="p.id"
                    class="hover:bg-gray-50"
                  >
                    <td class="px-4 py-3">
                      <div class="text-sm font-medium text-gray-900">{{ p.name }}</div>
                    </td>
                    <td class="px-4 py-3">
                      <div class="text-sm text-gray-900">{{ formatDate(latestSessionDate(p)) }}</div>
                    </td>
                    <td class="px-4 py-3">
                      <div class="text-sm text-gray-900">{{ latestSessionDuration(p) ? (latestSessionDuration(p) + ' ' + t('psychologistPatients.mins')) : '—' }}</div>
                    </td>
                    <td class="px-4 py-3 text-right">
                      <button
                        @click="openNotes(p)"
                        class="notes-btn inline-flex items-center gap-2 h-9 px-3 rounded-lg text-xs font-medium border shadow-sm transition duration-150 hover:shadow-md"
                        style="border-color: rgb(89 151 172 / var(--tw-bg-opacity, 1)); color: rgb(89 151 172 / var(--tw-bg-opacity, 1));"
                        :title="t('psychologistPatients.notes')"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" :style="{ color: 'inherit' }">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5l3 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                        {{ t('psychologistPatients.notes') }}
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </template>

            <div v-else class="p-8 text-center text-gray-500">
              <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <div class="mt-4 text-lg font-medium">{{ t('psychologistPatients.noPatients') }}</div>
              <div class="mt-1 text-sm">{{ t('psychologistPatients.noPatientsDesc') }}</div>
            </div>
          </div>

          <div class="flex items-center justify-between px-4 py-3 border-t border-gray-200">
            <div class="text-sm text-gray-600">{{ t('psychologistPatients.showing') }} {{ patients.from || 0 }}-{{ patients.to || 0 }} {{ t('psychologistPatients.of') }} {{ patients.total || 0 }}</div>
            <div class="flex items-center gap-2">
              <Link
                v-for="(link, i) in patients.links"
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

        <NotesBook v-if="showNotes" :patient="selectedPatient" @close="closeNotes" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ref, computed, watch, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import Navbar from '@/Components/Navbar.vue'
import NotesBook from './NotesBook.vue'
import SortIcon from '@/Components/SortIcon.vue'

const { t, locale } = useI18n()

function setLang(lang) {
  locale.value = lang
  localStorage.setItem('locale', lang)
  if (lang === 'ar') {
    document.documentElement.setAttribute('dir', 'rtl')
    document.documentElement.setAttribute('lang', 'ar')
    return
  }
  document.documentElement.setAttribute('dir', 'ltr')
  document.documentElement.setAttribute('lang', lang)
}

onMounted(() => {
  const savedLang = localStorage.getItem('locale') || locale.value
  setLang(savedLang)
})

const props = defineProps({
  patients: Object,
  status: { type: String, default: '' },
  filters: { type: Object, default: () => ({}) },
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

function clearFlash() {
  showFlash('')
}

function clearError() {
  showError('')
}

const data = computed(() => props.patients?.data || [])

const searchQuery = ref('')
const searchDate = ref('')
const searchField = ref('patient')
const isHydratingFilters = ref(false)
let searchDebounce = null

function normalizeFilters(filters = {}) {
  const validField = ['patient', 'date'].includes(String(filters?.search_field || '').toLowerCase())
    ? String(filters.search_field).toLowerCase()
    : 'patient'

  return {
    search_field: validField,
    search_query: String(filters?.search_query || ''),
    search_date: String(filters?.search_date || ''),
  }
}

function hydrateFiltersFromProps() {
  const f = normalizeFilters(props.filters || {})
  isHydratingFilters.value = true
  searchField.value = f.search_field
  searchQuery.value = f.search_query
  searchDate.value = f.search_date
  isHydratingFilters.value = false
}

function currentQueryParams() {
  const params = {
    search_field: searchField.value,
    search_query: searchField.value === 'date' ? '' : String(searchQuery.value || '').trim(),
    search_date: searchField.value === 'date' ? String(searchDate.value || '').trim() : '',
  }

  return Object.fromEntries(
    Object.entries(params).filter(([_, value]) => value !== '' && value != null)
  )
}

function applyServerFilters({ resetPage = true } = {}) {
  if (isHydratingFilters.value) return

  const params = currentQueryParams()
  if (resetPage) params.page = 1

  router.get(route('psychologist.patients.index'), params, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
    only: ['patients', 'filters', 'status'],
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

hydrateFiltersFromProps()

watch(
  () => props.filters,
  () => {
    hydrateFiltersFromProps()
  }
)

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

const selectedPatient = ref(null)
const showNotes = ref(false)

const sortKey = ref('last_session')
const sortDir = ref('desc')

const filtered = computed(() => (Array.isArray(data.value) ? [...data.value] : []))

function latestSessionDate(p) {
  if (p?.last_session_started_at) return p.last_session_started_at
  if (!p.sessions || p.sessions.length === 0) return null
  return p.sessions[0].started_at || null
}

function latestSessionDuration(p) {
  if (p?.last_session_duration != null) return p.last_session_duration
  if (!p.sessions || p.sessions.length === 0) return null
  return p.sessions[0].duration ?? null
}

function formatDate(v) {
  if (!v) return '—'
  try {
    const localeMap = { ar: 'ar', fr: 'fr', en: 'en' }
    const currentLocale = localeMap[locale.value] || 'en'
    return new Intl.DateTimeFormat(currentLocale, { year: 'numeric', month: 'short', day: '2-digit' }).format(new Date(v))
  } catch { return String(v) }
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

const searchPlaceholder = computed(() => 
  searchField.value === 'date' 
    ? t('psychologistPatients.searchBySessionDate') 
    : t('psychologistPatients.searchByPatientName')
)

function linkClasses(link) {
  const base = 'inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-sm border '
  if (link.active) return base + 'bg-indigo-600 text-white border-indigo-600'
  if (!link.url) return base + 'bg-gray-50 text-gray-400 border-gray-200 cursor-not-allowed'
  return base + 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'
}

const brandColor = 'rgb(89 151 172 / var(--tw-bg-opacity, 1))'
</script>

<style scoped>
[dir="rtl"] {
  text-align: right;
}

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