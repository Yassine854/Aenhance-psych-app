<template>
  <Head :title="t('psychologistAppointments.title')" />
  <Navbar :canLogin="canLogin" :canRegister="canRegister" :authUser="authUser || page.props?.auth?.user" />

  <main class="min-h-[calc(100vh-112px)] bg-[#f7f9fa] py-8 sm:py-12">
    <div class="app-page-shell space-y-6 px-4 sm:px-6 lg:px-8">
      <section class="relative overflow-hidden rounded-3xl border border-gray-200 bg-white p-5 shadow-sm sm:p-8">
        <div aria-hidden="true" class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[#af5166] to-[#5997ac]" />
        <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-start">
          <div>
            <p class="mb-2 text-xs font-bold uppercase tracking-widest text-[#5997ac]">{{ t('psychologistAppointments.workspace') }}</p>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">{{ t('psychologistAppointments.title') }}</h1>
            <p class="mt-2 max-w-xl text-sm leading-6 text-gray-500">{{ t('psychologistAppointments.overview') }}</p>
          </div>
          <Link :href="route('psychologist.availabilities')" class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-xl border border-[#5997ac]/25 bg-[#5997ac]/5 px-4 py-2.5 text-sm font-semibold text-[#477e90] transition hover:bg-[#5997ac]/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#5997ac]">
            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M8 2v4m8-4v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14H3V6a2 2 0 0 1 2-2Z" /><path d="m9 15 2 2 4-4" /></svg>
            {{ t('psychologistAppointments.manageAvailability') }}
          </Link>
        </div>
        <div class="mt-7 grid grid-cols-2 gap-3 lg:grid-cols-4">
          <button v-for="stat in summaryCards" :key="stat.key" type="button" @click="selectStatus(stat.filter)" :aria-pressed="statusFilter === stat.filter"
            class="flex min-w-0 items-center justify-between gap-2 rounded-2xl border p-4 text-start transition hover:-translate-y-0.5 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#5997ac]"
            :class="[stat.tone, statusFilter === stat.filter ? 'ring-2 ring-[#5997ac]/40' : '']">
            <div><p class="text-xs font-semibold">{{ stat.label }}</p><p class="mt-2 text-2xl font-bold tabular-nums">{{ stat.value }}</p></div>
            <span aria-hidden="true" class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white/70">
              <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path :d="stat.icon" stroke-linecap="round" stroke-linejoin="round" /></svg>
            </span>
          </button>
        </div>
      </section>

      <div v-if="flashMessage" role="status" class="flex items-start justify-between gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
        <span>{{ flashMessage }}</span><button type="button" @click="clearFlash" :aria-label="t('psychologistAppointments.dismiss')" class="font-bold">&#215;</button>
      </div>
      <div v-if="flashError" role="alert" class="flex items-start justify-between gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">
        <span>{{ flashError }}</span><button type="button" @click="clearError" :aria-label="t('psychologistAppointments.dismiss')" class="font-bold">&#215;</button>
      </div>

      <section :aria-label="t('psychologistAppointments.searchFilter')" class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
          <div class="flex min-w-0 flex-1 flex-col gap-2 sm:flex-row">
            <select v-model="searchField" :aria-label="t('psychologistAppointments.searchFilter')" class="min-h-11 rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-[#5997ac] focus:ring-[#5997ac] sm:w-36">
              <option value="patient">{{ t('psychologistAppointments.patient') }}</option><option value="date">{{ t('psychologistAppointments.date') }}</option>
            </select>
            <div class="relative flex-1">
              <svg aria-hidden="true" class="pointer-events-none absolute start-3 top-3.5 h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="10.5" cy="10.5" r="6.5" /><path d="m16 16 4 4" /></svg>
              <input v-if="searchField !== 'date'" v-model="searchQuery" type="search" :placeholder="searchPlaceholder" :aria-label="searchPlaceholder" class="min-h-11 w-full rounded-xl border-gray-200 bg-white ps-10 text-sm focus:border-[#5997ac] focus:ring-[#5997ac]" />
              <input v-else v-model="searchDate" type="date" :aria-label="t('psychologistAppointments.searchDate')" class="min-h-11 w-full rounded-xl border-gray-200 bg-white ps-10 text-sm focus:border-[#5997ac] focus:ring-[#5997ac]" />
            </div>
          </div>
          <button v-if="hasFilters" type="button" @click="resetFilters" class="min-h-11 rounded-xl px-3 text-sm font-semibold text-[#477e90] hover:bg-[#5997ac]/5">{{ t('psychologistAppointments.resetFilters') }}</button>
        </div>
        <div role="group" :aria-label="t('psychologistAppointments.status')" class="mt-4 flex flex-wrap gap-2 border-t border-gray-100 pt-4">
          <button v-for="filter in statusOptions" :key="filter.value" type="button" @click="selectStatus(filter.value)" :aria-pressed="statusFilter === filter.value"
            class="min-h-9 rounded-full px-3.5 py-1.5 text-xs font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#5997ac]"
            :class="statusFilter === filter.value ? 'bg-[#5997ac] text-white shadow-sm' : 'bg-gray-50 text-gray-600 hover:bg-gray-100'">{{ filter.label }}</button>
        </div>
      </section>

      <section :aria-label="t('psychologistAppointments.title')" class="space-y-4">
        <div class="flex items-center justify-between gap-3 px-1">
          <h2 class="text-base font-semibold text-gray-900">{{ t('psychologistAppointments.schedule') }} <span class="ms-1 rounded-full bg-gray-200/60 px-2 py-0.5 text-xs tabular-nums text-gray-600">{{ appointments.total || 0 }}</span></h2>
          <span class="text-xs text-gray-500">{{ t('psychologistAppointments.recentFirst') }}</span>
        </div>
        <TransitionGroup v-if="data.length" name="list" tag="div" class="space-y-4">
          <article v-for="a in sorted" :key="a.id" class="relative overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:border-[#5997ac]/30 hover:shadow-md">
            <div aria-hidden="true" class="absolute inset-y-0 start-0 w-1" :class="statusAccent(a.status)" />
            <div class="p-5 ps-6 sm:p-6 sm:ps-7">
              <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
                <div class="flex min-w-0 items-start gap-3">
                  <span aria-hidden="true" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-[#5997ac]/10 text-sm font-bold text-[#477e90]">{{ patientInitials(a) }}</span>
                  <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                      <h3 class="break-words text-base font-bold text-gray-900">{{ bookedForName(a) }}</h3>
                      <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold" :class="appointmentBadge(a.status)">{{ appointmentLabel(a.status) }}</span>
                    </div>
                    <div class="mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500">
                      <span>{{ bookedForBadge(a) }}</span><span aria-hidden="true">&middot;</span><span>{{ bookedForMeta(a) }}</span>
                    </div>
                    <p v-if="bookedForDetails(a)" class="mt-1.5 text-xs text-gray-500">{{ bookedForDetails(a) }}</p>
                  </div>
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-2 sm:justify-end">
                  <Link v-if="canJoinRoom(a)" :href="route('appointments.video_call.show', a.id)" class="appointment-primary" :title="t('psychologistAppointments.joinVideoCall')">
                    <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="6" width="12" height="12" rx="2" /><path d="m15 10 6-3v10l-6-3" /></svg>{{ t('psychologistAppointments.joinRoom') }}
                  </Link>
                  <button v-else-if="canStartCall(a)" type="button" @click="startCall(a)" :disabled="startingCallId === a.id" class="appointment-primary" :title="t('psychologistAppointments.startVideoCall')">{{ startingCallId === a.id ? t('psychologistAppointments.starting') : t('psychologistAppointments.startCall') }}</button>
                  <button v-if="normalizeStatus(a.status) === 'completed'" type="button" @click="onNotesClick(a, $event)" class="appointment-primary" :title="t('psychologistAppointments.editNotes')">{{ t('psychologistAppointments.notes') }}</button>
                  <button v-if="canCancelAppointment(a)" type="button" @click="cancelWithReason(a)" :disabled="savingId === a.id" class="appointment-secondary text-red-600 hover:border-red-200 hover:bg-red-50" :title="t('psychologistAppointments.cancelAppointment')">{{ t('psychologistAppointments.cancel') }}</button>
                  <button v-else-if="showCancelDisabled(a)" type="button" disabled class="appointment-secondary cursor-not-allowed text-gray-400" :title="t('psychologistAppointments.cancelDisabled')">{{ t('psychologistAppointments.cancel') }}</button>
                  <button v-if="currentUser && String(currentUser.role || '').toUpperCase() === 'PSYCHOLOGIST'" type="button" @click="openReportForPatient(a)" class="flex h-10 w-10 items-center justify-center rounded-xl text-gray-400 transition hover:bg-red-50 hover:text-red-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-red-200" :title="t('psychologistAppointments.reportPatient')" :aria-label="t('psychologistAppointments.reportPatient')">
                    <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M5 21V4m0 0c5-4 9 4 14 0v10c-5 4-9-4-14 0" /></svg>
                  </button>
                </div>
              </div>
              <div class="mt-5 grid gap-3 rounded-xl border border-gray-100 bg-gray-50/80 px-4 py-3 text-sm sm:grid-cols-[1.4fr_1fr_auto] sm:items-center">
                <div class="flex items-center gap-2.5 text-gray-700"><svg aria-hidden="true" class="h-4 w-4 shrink-0 text-[#5997ac]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="5" width="18" height="16" rx="2" /><path d="M7 2v6m10-6v6M3 11h18" /></svg><span class="font-medium">{{ formatDate(a.scheduled_start) }}</span></div>
                <div class="flex items-center gap-2.5 text-gray-700"><svg aria-hidden="true" class="h-4 w-4 shrink-0 text-[#5997ac]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></svg><span class="font-semibold tabular-nums">{{ formatTime(a.scheduled_start) }} &ndash; {{ formatTime(a.scheduled_end) }}</span></div>
                <span class="text-xs text-gray-500">{{ t('psychologistAppointments.videoSession') }}</span>
              </div>
              <p v-if="normalizeStatus(a.status) === 'cancelled' && a.cancellation_reason" class="mt-3 text-xs leading-5 text-gray-500"><span class="font-semibold">{{ t('psychologistAppointments.reason') }}:</span> {{ a.cancellation_reason }}</p>
            </div>
          </article>
        </TransitionGroup>
        <div v-else class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-14 text-center">
          <span aria-hidden="true" class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#5997ac]/10 text-[#5997ac]"><svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="5" width="18" height="16" rx="2" /><path d="M7 2v6m10-6v6M3 11h18m5 5h8" /></svg></span>
          <h3 class="mt-4 font-semibold text-gray-900">{{ t(hasFilters ? 'psychologistAppointments.noResults' : 'psychologistAppointments.noAppointments') }}</h3>
          <p class="mx-auto mt-2 max-w-sm text-sm leading-6 text-gray-500">{{ t(hasFilters ? 'psychologistAppointments.noResultsDesc' : 'psychologistAppointments.noAppointmentsDesc') }}</p>
          <button v-if="hasFilters" type="button" @click="resetFilters" class="appointment-primary mt-5">{{ t('psychologistAppointments.resetFilters') }}</button>
        </div>
        <div v-if="data.length" class="flex flex-col items-center justify-between gap-4 rounded-2xl border border-gray-200 bg-white px-5 py-4 sm:flex-row">
          <p class="text-xs text-gray-500">{{ t('psychologistAppointments.showing') }} <span class="font-semibold text-gray-700">{{ appointments.from || 0 }}&ndash;{{ appointments.to || 0 }}</span> {{ t('psychologistAppointments.of') }} {{ appointments.total || 0 }}</p>
          <nav v-if="appointments.last_page > 1" :aria-label="t('psychologistAppointments.pagination')" class="flex flex-wrap justify-center gap-1.5">
            <template v-for="(link, i) in appointments.links" :key="i">
              <Link v-if="link.url" :href="link.url" :class="linkClasses(link)" :style="link.active ? { backgroundColor: brandColor, borderColor: brandColor, color: '#fff' } : null" :aria-current="link.active ? 'page' : undefined" preserve-scroll><span v-safe-html="link.label" /></Link>
              <span v-else :class="linkClasses(link)" aria-disabled="true"><span v-safe-html="link.label" /></span>
            </template>
          </nav>
        </div>
        <p class="flex items-start gap-2 px-1 text-xs leading-5 text-gray-500"><svg aria-hidden="true" class="mt-0.5 h-4 w-4 shrink-0 text-[#5997ac]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9" /><path d="M12 11v6m0-10v1" /></svg>{{ t('psychologistAppointments.cancelDisabled') }}</p>
      </section>
    </div>
  </main>
  <Footer />
  <VideoCallSessionNotes ref="notesRef" />
  <ReportModal :show="showReportModal" :profile="reportProfile" :authUser="currentUser" reported-role="patient" @close="showReportModal=false" @sent="() => { showReportModal=false }" />
</template>


<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { useI18n } from 'vue-i18n'
import Navbar from '@/Components/Navbar.vue'
import Footer from '@/Components/Footer.vue'
import Swal from 'sweetalert2'
import VideoCallSessionNotes from '@/Components/VideoCall/VideoCallSessionNotes.vue'
import ReportModal from '@/Components/ReportModal.vue'

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
  appointments: Object,
  summary: { type: Object, default: () => ({}) },
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

const data = computed(() => props.appointments?.data || [])

const searchField = ref('patient')
const searchQuery = ref('')
const searchDate = ref('')
const statusFilter = ref('')
const hasFilters = computed(() => Boolean(statusFilter.value || searchQuery.value.trim() || searchDate.value))
const statusOptions = computed(() => [
  { value: '', label: t('psychologistAppointments.all') },
  ...['confirmed', 'pending', 'completed', 'cancelled', 'no_show'].map(value => ({
    value, label: appointmentLabel(value),
  })),
])
const summaryCards = computed(() => [
  { key: 'total', filter: '', label: t('psychologistAppointments.total'), tone: 'border-gray-200 bg-gray-50 text-gray-700', icon: 'M8 2v4m8-4v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14H3V6a2 2 0 0 1 2-2Z' },
  { key: 'confirmed', filter: 'confirmed', label: t('psychologistAppointments.confirmed'), tone: 'border-[#5997ac]/20 bg-[#5997ac]/5 text-[#477e90]', icon: 'M12 3v9l5 3M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0' },
  { key: 'pending', filter: 'pending', label: t('psychologistAppointments.pending'), tone: 'border-amber-200 bg-amber-50/70 text-amber-800', icon: 'M12 8v5m0 3v1M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0' },
  { key: 'completed', filter: 'completed', label: t('psychologistAppointments.completed'), tone: 'border-emerald-200 bg-emerald-50/70 text-emerald-800', icon: 'm8 12 3 3 5-6M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0' },
].map(card => ({ ...card, value: Number(props.summary[card.key] || 0) })))

function selectStatus(value) {
  if (statusFilter.value === value) return
  statusFilter.value = value
  applyServerFilters()
}

async function resetFilters() {
  if (searchDebounce) clearTimeout(searchDebounce)
  isHydratingFilters.value = true
  searchQuery.value = ''
  searchDate.value = ''
  searchField.value = 'patient'
  statusFilter.value = ''
  await nextTick()
  isHydratingFilters.value = false
  applyServerFilters()
}

function patientInitials(appointment) {
  return bookedForName(appointment).trim().split(/\s+/).slice(0, 2).map(part => part[0]).join('').toUpperCase() || '?'
}

function statusAccent(status) {
  return ({ confirmed: 'bg-[#5997ac]', pending: 'bg-amber-400', completed: 'bg-emerald-500', cancelled: 'bg-rose-400' })[normalizeStatus(status)] || 'bg-gray-300'
}
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
    status_filter: ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'].includes(filters?.status_filter) ? filters.status_filter : '',
  }
}

function hydrateFiltersFromProps() {
  const f = normalizeFilters(props.filters || {})
  isHydratingFilters.value = true
  searchField.value = f.search_field
  searchQuery.value = f.search_query
  searchDate.value = f.search_date
  statusFilter.value = f.status_filter
  nextTick(() => { isHydratingFilters.value = false })
}

function currentQueryParams() {
  const params = {
    search_field: searchField.value,
    search_query: searchField.value === 'date' ? '' : String(searchQuery.value || '').trim(),
    search_date: searchField.value === 'date' ? String(searchDate.value || '').trim() : '',
    status_filter: statusFilter.value,
  }

  return Object.fromEntries(
    Object.entries(params).filter(([_, value]) => value !== '' && value != null)
  )
}

function applyServerFilters({ resetPage = true } = {}) {
  if (isHydratingFilters.value) return
  if (searchDebounce) { clearTimeout(searchDebounce); searchDebounce = null }

  const params = currentQueryParams()
  if (resetPage) params.page = 1

  router.get(route('psychologist.appointments.index'), params, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
    only: ['appointments', 'filters', 'summary', 'status'],
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

const searchPlaceholder = computed(() => {
  switch (searchField.value) {
    case 'patient':
      return t('psychologistAppointments.searchPatientPlaceholder')
    default:
      return t('psychologistAppointments.searchPlaceholder')
  }
})

function bookedForName(a) {
  const beneficiary = a?.beneficiary || null
  if (String(a?.booking_for || '').toLowerCase() === 'other' && beneficiary) {
    return beneficiary.full_name || [beneficiary.first_name, beneficiary.last_name].filter(Boolean).join(' ') || t('psychologistAppointments.anotherPerson')
  }

  return a?.patient?.name || t('psychologistAppointments.patient')
}

function bookedForBadge(a) {
  return String(a?.booking_for || '').toLowerCase() === 'other' ? t('psychologistAppointments.anotherPerson') : t('psychologistAppointments.patientAccountOwner')
}

function bookedForMeta(a) {
  const beneficiary = a?.beneficiary || null
  if (String(a?.booking_for || '').toLowerCase() !== 'other' || !beneficiary) {
    return t('psychologistAppointments.accountOwner') + ': ' + (a?.patient?.name || 'patient')
  }

  const parts = [t('psychologistAppointments.bookedBy') + ' ' + (a?.patient?.name || 'patient')]
  if (beneficiary.relationship_to_patient) parts.push(beneficiary.relationship_to_patient)
  return parts.join(' • ')
}

function bookedForDetails(a) {
  const beneficiary = a?.beneficiary || null
  if (String(a?.booking_for || '').toLowerCase() !== 'other' || !beneficiary) {
    return ''
  }

  const parts = []
  const age = calculateAge(beneficiary.date_of_birth)
  if (age !== null) parts.push(t('psychologistAppointments.age') + ': ' + age)
  return parts.join(' • ')
}

function calculateAge(dateOfBirth) {
  if (!dateOfBirth) return null

  const birthYear = Number(String(dateOfBirth).slice(0, 4))
  if (!Number.isFinite(birthYear) || birthYear <= 0) return null

  const age = new Date().getFullYear() - birthYear
  return age >= 0 ? age : null
}

const filtered = computed(() => {
  return Array.isArray(data.value) ? [...data.value] : []
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

function getSortValue(a, key) {
  switch (key) {
    case 'id':
      return Number(a?.id || 0)
    case 'patient':
      return String(bookedForName(a) || a?.patient?.name || '').toLowerCase()
    case 'scheduled_start':
      try {
        return new Date(a?.scheduled_start || 0).getTime() || 0
      } catch {
        return 0
      }
    case 'status':
      return String(a?.status || '').toLowerCase()
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

const savingId = ref(null)
const startingCallId = ref(null)
const notesRef = ref(null)

// Report modal for reporting patients
const showReportModal = ref(false)
const reportProfile = ref(null)
const currentUser = computed(() => props.authUser || page.props?.auth?.user)

function openReportForPatient(a) {
  if (!a) return
  const candidate = a.patient_profile || a.patient || null
  if (candidate && candidate.id) {
    reportProfile.value = candidate
  } else {
    reportProfile.value = {
      id: a.patient_id || a.patient_profile_id || null,
      user: { name: a.patient?.name || a.patient_name || 'Patient' },
      first_name: a.patient?.first_name || (a.patient_name ? a.patient_name.split(' ')[0] : null) || null,
      last_name: a.patient?.last_name || '',
    }
  }
  showReportModal.value = true
}

function formatDate(value) {
  if (!value) return '—'
  try {
    const localeMap = { ar: 'ar', fr: 'fr', en: 'en' }
    const currentLocale = localeMap[locale.value] || 'en'
    return new Intl.DateTimeFormat(currentLocale, { year: 'numeric', month: 'short', day: '2-digit' }).format(new Date(value))
  } catch {
    return String(value)
  }
}

function formatTime(value) {
  if (!value) return '—'
  try {
    const localeMap = { ar: 'ar', fr: 'fr', en: 'en' }
    const currentLocale = localeMap[locale.value] || 'en'
    return new Intl.DateTimeFormat(currentLocale, { hour: '2-digit', minute: '2-digit' }).format(new Date(value))
  } catch {
    return '—'
  }
}

function normalizeStatus(value) {
  const status = String(value || '').trim().toLowerCase()
  return status === 'canceled' ? 'cancelled' : status
}

function appointmentLabel(status) {
  const s = normalizeStatus(status)
  if (!s) return '—'
  if (s === 'no_show') return t('psychologistAppointments.missed')
  if (s === 'pending') return t('psychologistAppointments.pending')
  if (s === 'confirmed') return t('psychologistAppointments.confirmed')
  if (s === 'completed') return t('psychologistAppointments.completed')
  if (s === 'cancelled') return t('psychologistAppointments.cancelled')
  return s.charAt(0).toUpperCase() + s.slice(1)
}

function appointmentBadge(status) {
  const s = normalizeStatus(status)
  if (s === 'pending') return 'bg-yellow-50 text-yellow-800 ring-1 ring-yellow-200'
  if (s === 'confirmed') return 'bg-blue-50 text-blue-700 ring-1 ring-blue-200'
  if (s === 'completed') return 'bg-green-50 text-green-700 ring-1 ring-green-200'
  if (s === 'cancelled') return 'bg-red-50 text-red-700 ring-1 ring-red-200'
  if (s === 'no_show') return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
  return 'bg-gray-100 text-gray-700 ring-1 ring-gray-200'
}

function canCancelAppointment(a) {
  const status = normalizeStatus(a?.status)
  if (!['pending', 'confirmed'].includes(status)) return false
  if (a?.can_cancel === true) return true
  return false
}

function canJoinRoom(a) {
  const status = normalizeStatus(a?.status)
  return status === 'confirmed' && !!a?.session_room_id
}

function canStartCall(a) {
  const status = normalizeStatus(a?.status)
  return status === 'confirmed' && !a?.session_room_id
}

function startCall(a) {
  if (!a?.id || !canStartCall(a)) return
  if (startingCallId.value) return

  startingCallId.value = a.id
  router.post(
    route('psychologist.appointments.video_call.start', a.id),
    {},
    {
      preserveScroll: true,
      onFinish: () => {
        startingCallId.value = null
      },
    }
  )
}

// Minimal fetch helper for JSON API calls (includes cookies)
async function fetchJson(url, options = {}) {
  const getMetaCsrfToken = () => {
    try { return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' } catch { return '' }
  }
  const getXsrfCookieToken = () => {
    try { const m = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]+)/); return m && m[1] ? decodeURIComponent(m[1]) : '' } catch { return '' }
  }

  const doFetch = async () => {
    const isWrite = Boolean(options.method && options.method !== 'GET')
    const headers = {
      'X-Requested-With': 'XMLHttpRequest',
      Accept: 'application/json',
      ...(options.headers || {}),
    }
    const meta = getMetaCsrfToken()
    const cookieToken = getXsrfCookieToken()
    if (isWrite && meta) headers['X-CSRF-TOKEN'] = meta
    if (isWrite && cookieToken) headers['X-XSRF-TOKEN'] = cookieToken
    if (isWrite && options.body && !headers['Content-Type']) headers['Content-Type'] = 'application/json'

    return fetch(url, { credentials: 'include', headers, ...options })
  }

  const parse = async (res) => {
    const text = await res.text()
    let data = null
    try { data = text ? JSON.parse(text) : null } catch { data = null }
    return { res, data }
  }

  let first = await doFetch()
  let { res, data } = await parse(first)

  if (res.status === 419) {
    try { await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' }) } catch {}
    try { await new Promise((r) => setTimeout(r, 60)) } catch {}
    const second = await doFetch()
    ;({ res, data } = await parse(second))
  }

  if (!res.ok) {
    const msg = data?.message || `Request failed (${res.status})`
    throw new Error(msg)
  }

  return data
}

// Open and edit (or create) session notes for an appointment
async function openEditNotes(a) {
  if (!a?.id) return
  try {
    const sessionResp = await fetchJson(`/appointments/${a.id}/session`, { method: 'GET' })
    const session = sessionResp?.session || null
    if (!session) {
      Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: t('psychologistAppointments.noSessionFound'), showConfirmButton: false, timer: 3000 })
      return
    }

    let note = null
    try {
      const data = await fetchJson(`/appointments/${a.id}/session-note`, { method: 'GET' })
      note = data?.note || null
    } catch (err) {
      note = null
    }

    const payload = await notesRef.value
      ? await notesRef.value.open({
          appointment_session_id: session.id,
          session_date: (note && note.session_date) || session.started_at,
          session_duration: (note && note.session_duration) || session.duration_minutes || 0,
          session_mode: (note && note.session_mode) || 'video_audio',
          risk_level: (note && note.risk_level) || 'none',
          subjective: (note && note.subjective) || '',
          objective: (note && note.objective) || '',
          assessment: (note && note.assessment) || '',
          intervention: (note && note.intervention) || '',
          plan: (note && note.plan) || '',
        })
      : null

    if (!payload) return

    if (note && note.id) {
      await fetchJson(`/appointment-session-notes/${note.id}`, {
        method: 'PATCH',
        body: JSON.stringify(payload),
      })
      Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: t('psychologistAppointments.notesUpdated'), showConfirmButton: false, timer: 3000 })
    } else {
      await fetchJson(`/appointment-session-notes`, {
        method: 'POST',
        body: JSON.stringify(payload),
      })
      Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: t('psychologistAppointments.notesCreated'), showConfirmButton: false, timer: 3000 })
    }
  } catch (e) {
    console.error('Failed opening/updating notes', e)
    const msg = e?.message || t('psychologistAppointments.notesError')
    Swal.fire({ toast: true, position: 'top-end', icon: 'error', title: msg, showConfirmButton: false, timer: 4000 })
  }
}

function showCancelDisabled(a) {
  const status = normalizeStatus(a?.status)
  if (!['pending', 'confirmed'].includes(status)) return false
  if (a?.can_cancel === false) return true
  return false
}

async function cancelWithReason(a) {
  if (!a || savingId.value === a.id) return

  const presetReasons = [
    { value: 'schedule_conflict', label: t('psychologistAppointments.reasons.scheduleConflict') },
    { value: 'personal_emergency', label: t('psychologistAppointments.reasons.personalEmergency') },
    { value: 'technical_issue', label: t('psychologistAppointments.reasons.technicalIssue') },
    { value: 'other', label: t('psychologistAppointments.reasons.other') },
  ]

  const result = await Swal.fire({
    title: t('psychologistAppointments.cancelConfirmTitle'),
    html: `
      <div style="text-align:left;max-width:520px;margin:0 auto">
        <div style="margin-bottom:8px">${t('psychologistAppointments.cancelConfirmAppointment')} #${a?.id ?? ''} ${t('psychologistAppointments.cancelConfirmFor')} ${a?.patient?.name || 'patient'}</div>
        <label for="swal-reason" style="display:block;margin:10px 0 6px;font-weight:600">${t('psychologistAppointments.cancelConfirmReason')}</label>
        <select id="swal-reason" class="swal2-input" style="margin:0 auto;width:100%;max-width:520px">
          ${presetReasons
            .map((r) => `<option value="${r.value}">${r.label}</option>`)
            .join('')}
        </select>

        <div id="swal-other-wrap" style="display:none">
          <label for="swal-other" style="display:block;margin:10px 0 6px;font-weight:600">${t('psychologistAppointments.cancelConfirmOtherReason')}</label>
          <textarea id="swal-other" class="swal2-textarea" style="margin:0 auto;width:100%;max-width:520px" maxlength="255" placeholder="${t('psychologistAppointments.cancelConfirmWriteReason')}"></textarea>
        </div>
      </div>
    `,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: t('psychologistAppointments.cancelConfirmYes'),
    cancelButtonText: t('psychologistAppointments.cancelConfirmKeep'),
    reverseButtons: true,
    focusCancel: true,
    confirmButtonColor: 'rgb(141,61,79)',
    didOpen: () => {
      const select = document.getElementById('swal-reason')
      const wrap = document.getElementById('swal-other-wrap')
      const other = document.getElementById('swal-other')
      const toggle = () => {
        const isOther = select?.value === 'other'
        if (wrap) wrap.style.display = isOther ? 'block' : 'none'
        if (!isOther && other) other.value = ''
      }
      if (select) select.addEventListener('change', toggle)
      toggle()
    },
    preConfirm: () => {
      const select = document.getElementById('swal-reason')
      const other = document.getElementById('swal-other')

      const selected = String(select?.value || '').trim()
      if (!selected) {
        Swal.showValidationMessage(t('psychologistAppointments.cancelConfirmSelectReason'))
        return false
      }

      if (selected === 'other') {
        const v = String(other?.value || '').trim()
        if (!v) {
          Swal.showValidationMessage(t('psychologistAppointments.cancelConfirmWriteReason'))
          return false
        }
        return v
      }

      const label = presetReasons.find((r) => r.value === selected)?.label
      return label || selected
    },
  })

  if (!result.isConfirmed) return

  savingId.value = a.id
  router.patch(
    route('psychologist.appointments.cancel', a.id),
    { cancellation_reason: String(result.value || '').trim() },
    {
      preserveScroll: true,
      onError: (errors) => {
        const msg = errors?.cancellation_reason || errors?.status
        if (msg) showError(msg)
      },
      onFinish: () => {
        savingId.value = null
      },
    }
  )
}

function linkClasses(link) {
  const base = 'inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-sm border '
  if (link.active) return base + 'bg-indigo-600 text-white border-indigo-600'
  if (!link.url) return base + 'bg-gray-50 text-gray-400 border-gray-200 cursor-not-allowed'
  return base + 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'
}

const brandColor = 'rgb(89 151 172 / var(--tw-bg-opacity, 1))'

onBeforeUnmount(() => {
  clearTimeout(searchDebounce)
  clearTimeout(flashTimer)
  clearTimeout(errorTimer)
})

// Notes button click animation: briefly add a class then open modal
function onNotesClick(a, ev) {
  try {
    const btn = ev?.currentTarget || ev?.target
    if (btn && btn.classList) {
      btn.classList.remove('btn-click-anim')
      void btn.offsetWidth
      btn.classList.add('btn-click-anim')
      const cleanup = () => {
        btn.classList.remove('btn-click-anim')
        btn.removeEventListener('animationend', cleanup)
      }
      btn.addEventListener('animationend', cleanup)
    }
  } catch (err) {
    // ignore animation errors
  }

  openEditNotes(a)
}
</script>

<style scoped>
.appointment-primary {
  @apply inline-flex min-h-10 items-center justify-center gap-2 rounded-xl bg-[#5997ac] px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-[#477e90] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#5997ac] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50;
}
.appointment-secondary {
  @apply inline-flex min-h-10 items-center justify-center rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-semibold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#5997ac] disabled:opacity-60;
}
.list-enter-active, .list-leave-active { transition: opacity 180ms ease, transform 180ms ease; }
.list-enter-from, .list-leave-to { opacity: 0; transform: translateY(6px); }
@media (prefers-reduced-motion: reduce) { .list-enter-active, .list-leave-active { transition: none; } }
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
