<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import Swal from 'sweetalert2'
import { useI18n } from 'vue-i18n'
import Navbar from '@/Components/Navbar.vue'
import Footer from '@/Components/Footer.vue'
import { resolveStorageUrl } from '@/utils/storage'

const props = defineProps({
  canLogin: { type: Boolean },
  canRegister: { type: Boolean },
  authUser: { type: Object },
  patientProfile: { type: Object, default: null },
  previousBeneficiaries: { type: Array, default: () => [] },
  status: { type: String, default: '' },
  psychologistProfile: { type: Object, required: true },
  days: { type: Array, default: () => [] },
  sessionMinutes: { type: Number, default: 60 },
})

const { t, locale } = useI18n()
const page = usePage()

function showFlashToast(flash) {
  const statusKey = flash?.status_key || flash?.statusKey || null
  const errorKey = flash?.error_key || flash?.errorKey || null
  const key = errorKey || statusKey || null
  const icon = errorKey ? 'error' : 'success'
  const raw = flash?.error || flash?.status || props.status || page.props?.status || null
  const text = key ? t(key) : raw

  if (!text) return

  Swal.fire({
    position: 'top-end',
    icon,
    title: text,
    showConfirmButton: false,
    timer: 3000,
    toast: true,
    timerProgressBar: true,
    showCloseButton: true,
  })
}

watch(
  () => page.props?.flash,
  (flash) => {
    showFlashToast(flash)
  },
  { immediate: true }
)

function languageLabel(lang) {
  const v = String(lang || '').toLowerCase();
  if (locale.value === 'fr') {
    if (v === 'english') return t('appointment.languages.english');
    if (v === 'french') return t('appointment.languages.french');
    if (v === 'arabic') return t('appointment.languages.arabic');
  }
  if (locale.value === 'ar') {
    if (v === 'english') return t('appointment.languages.english');
    if (v === 'french') return t('appointment.languages.french');
    if (v === 'arabic') return t('appointment.languages.arabic');
  }
  if (v === 'english') return 'English';
  if (v === 'french') return 'French';
  if (v === 'arabic') return 'Arabic';
  return String(lang || '').trim();
}

function languagesFor(profile) {
  const langs = Array.isArray(profile?.languages) ? profile.languages : [];
  return langs.map(languageLabel).filter(Boolean);
}

function fullName(p) {
  const first = (p?.first_name || '').trim()
  const last = (p?.last_name || '').trim()
  return `${first} ${last}`.trim() || p?.user?.name || t('appointment.psychologist')
}

function patientDisplayName() {
  const first = (props.patientProfile?.first_name || '').trim()
  const last = (props.patientProfile?.last_name || '').trim()
  return `${first} ${last}`.trim() || props.authUser?.name || t('appointment.you')
}

function bookingForLabel(value) {
  return value === 'other' ? t('appointment.anotherPerson') : t('appointment.myself')
}

function beneficiarySignature(beneficiary) {
  return [
    String(beneficiary?.first_name || '').trim().toLowerCase(),
    String(beneficiary?.last_name || '').trim().toLowerCase(),
    String(beneficiary?.date_of_birth || '').trim(),
    String(beneficiary?.gender || '').trim().toLowerCase(),
    String(beneficiary?.relationship_to_patient || '').trim().toLowerCase(),
  ].join('|')
}

function beneficiaryNameSignature(beneficiary) {
  return [
    String(beneficiary?.first_name || '').trim().toLowerCase(),
    String(beneficiary?.last_name || '').trim().toLowerCase(),
  ].join('|')
}

function avatarUrl(p) {
  return resolveStorageUrl(p?.profile_image_url) || null
}

function initials(p) {
  const source = fullName(p)
  const parts = source.split(/\s+/).filter(Boolean)
  return parts
    .slice(0, 2)
    .map((x) => (x[0] || '').toUpperCase())
    .join('') || 'P'
}

function formatPrice(value) {
  if (value === null || value === undefined || value === '') return '—'
  const num = Number(value)
  if (Number.isNaN(num)) return String(value)
  return `${num.toFixed(2)} TND`
}

function isValidIsoDate(s) {
  return typeof s === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(s)
}

function parseIsoDate(s) {
  if (!isValidIsoDate(s)) return null
  const [y, m, d] = s.split('-').map(Number)
  const dt = new Date(y, (m || 1) - 1, d || 1)
  return Number.isNaN(dt.getTime()) ? null : dt
}

function formatIsoDate(dt) {
  const y = dt.getFullYear()
  const m = String(dt.getMonth() + 1).padStart(2, '0')
  const d = String(dt.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

const maxBeneficiaryDateOfBirth = computed(() => {
  const dt = new Date()
  dt.setFullYear(dt.getFullYear() - 1)
  return formatIsoDate(dt)
})

function monthKey(dt) {
  const y = dt.getFullYear()
  const m = String(dt.getMonth() + 1).padStart(2, '0')
  return `${y}-${m}`
}

function startOfMonth(dt) {
  return new Date(dt.getFullYear(), dt.getMonth(), 1)
}

const allDays = computed(() => (props.days || []).filter((d) => typeof d?.date === 'string'))
const availableDays = computed(() => allDays.value.filter((d) => Array.isArray(d.slots) && d.slots.length))
const availableDateSet = computed(() => new Set(availableDays.value.map((d) => d.date)))
const initialDate = computed(() => availableDays.value?.[0]?.date || '')

const selectedDate = ref(initialDate.value)
const selectedSlot = ref(null)

const selectedDay = computed(() => (props.days || []).find((d) => d.date === selectedDate.value) || null)

watch(
  () => initialDate.value,
  (v) => {
    if (!selectedDate.value && v) {
      selectedDate.value = v
    }
  }
)

// Calendar
const weekdayHeaders = computed(() => [
  t('appointment.calendar.sun'),
  t('appointment.calendar.mon'),
  t('appointment.calendar.tue'),
  t('appointment.calendar.wed'),
  t('appointment.calendar.thu'),
  t('appointment.calendar.fri'),
  t('appointment.calendar.sat')
])

const monthOptions = computed(() => {
  const keys = []
  const seen = new Set()

  for (const d of allDays.value) {
    const dt = parseIsoDate(d.date)
    if (!dt) continue
    const key = monthKey(dt)
    if (seen.has(key)) continue
    seen.add(key)
    keys.push({ key, date: startOfMonth(dt) })
  }

  keys.sort((a, b) => a.date.getTime() - b.date.getTime())
  return keys
})

const currentMonthKey = ref(monthOptions.value?.[0]?.key || '')

const currentMonthIndex = computed(() => monthOptions.value.findIndex((m) => m.key === currentMonthKey.value))

function setMonthByKey(key) {
  if (!key || typeof key !== 'string') return
  if (monthOptions.value.some((m) => m.key === key)) {
    currentMonthKey.value = key
  }
}

function goPrevMonth() {
  const idx = currentMonthIndex.value
  if (idx > 0) {
    currentMonthKey.value = monthOptions.value[idx - 1].key
  }
}

function goNextMonth() {
  const idx = currentMonthIndex.value
  if (idx >= 0 && idx < monthOptions.value.length - 1) {
    currentMonthKey.value = monthOptions.value[idx + 1].key
  }
}

watch(
  () => monthOptions.value.map((m) => m.key).join('|'),
  () => {
    if (!currentMonthKey.value && monthOptions.value.length) {
      currentMonthKey.value = monthOptions.value[0].key
      return
    }
    if (currentMonthKey.value && monthOptions.value.length && !monthOptions.value.some((m) => m.key === currentMonthKey.value)) {
      currentMonthKey.value = monthOptions.value[0].key
    }
  }
)

watch(
  () => selectedDate.value,
  (date) => {
    const dt = parseIsoDate(date)
    if (!dt) return
    const key = monthKey(dt)
    setMonthByKey(key)
  },
  { immediate: true }
)

const currentMonthDate = computed(() => {
  const match = monthOptions.value.find((m) => m.key === currentMonthKey.value)
  if (match) return match.date
  const fallback = availableDays.value?.[0]?.date
  return parseIsoDate(fallback) ? startOfMonth(parseIsoDate(fallback)) : new Date()
})

const currentMonthLabel = computed(() => {
  try {
    return new Intl.DateTimeFormat(undefined, { month: 'long', year: 'numeric' }).format(currentMonthDate.value)
  } catch {
    return currentMonthDate.value.toLocaleString(undefined, { month: 'long', year: 'numeric' })
  }
})

const calendarCells = computed(() => {
  const first = startOfMonth(currentMonthDate.value)
  const firstDow = first.getDay()
  const start = new Date(first)
  start.setDate(first.getDate() - firstDow)

  const cells = []
  for (let i = 0; i < 42; i++) {
    const dt = new Date(start)
    dt.setDate(start.getDate() + i)
    const iso = formatIsoDate(dt)
    const inMonth = dt.getMonth() === first.getMonth()
    const available = availableDateSet.value.has(iso)
    const selected = iso === selectedDate.value
    cells.push({ iso, dt, inMonth, available, selected })
  }
  return cells
})

const form = useForm({
  psychologist_id: props.psychologistProfile?.user_id || '',
  scheduled_start: '',
  booking_for: 'self',
  beneficiary_first_name: '',
  beneficiary_last_name: '',
  beneficiary_date_of_birth: '',
  beneficiary_gender: '',
  beneficiary_relationship: '',
})

const previousBeneficiaries = computed(() => {
  const source = Array.isArray(props.previousBeneficiaries) ? props.previousBeneficiaries : []
  const seen = new Set()

  return source.filter((beneficiary) => {
    const signature = beneficiaryNameSignature(beneficiary)
    if (seen.has(signature)) return false
    seen.add(signature)
    return true
  })
})

const selectedBeneficiarySignature = computed(() => {
  if (form.booking_for !== 'other') return ''

  return beneficiarySignature({
    first_name: form.beneficiary_first_name,
    last_name: form.beneficiary_last_name,
    date_of_birth: form.beneficiary_date_of_birth,
    gender: form.beneficiary_gender,
    relationship_to_patient: form.beneficiary_relationship,
  })
})

watch(
  () => form.booking_for,
  (value) => {
    if (value !== 'other') {
      form.beneficiary_first_name = ''
      form.beneficiary_last_name = ''
      form.beneficiary_date_of_birth = ''
      form.beneficiary_gender = ''
      form.beneficiary_relationship = ''
    }
  }
)

function selectedBeneficiaryName() {
  if (form.booking_for !== 'other') return patientDisplayName()
  return `${String(form.beneficiary_first_name || '').trim()} ${String(form.beneficiary_last_name || '').trim()}`.trim() || t('appointment.anotherPerson')
}

function applyPreviousBeneficiary(beneficiary) {
  if (!beneficiary) return

  form.booking_for = 'other'
  form.beneficiary_first_name = String(beneficiary.first_name || '')
  form.beneficiary_last_name = String(beneficiary.last_name || '')
  form.beneficiary_date_of_birth = String(beneficiary.date_of_birth || '')
  form.beneficiary_gender = String(beneficiary.gender || '')
  form.beneficiary_relationship = String(beneficiary.relationship_to_patient || '')
}

function pickDate(date) {
  if (!availableDateSet.value.has(date)) return
  selectedDate.value = date
  selectedSlot.value = null
  form.scheduled_start = ''
}

function pickSlot(slot) {
  selectedSlot.value = slot
  form.scheduled_start = slot?.start_iso || ''
}

const beneficiaryIsComplete = computed(() => {
  if (form.booking_for !== 'other') return true

  return [
    form.beneficiary_first_name,
    form.beneficiary_last_name,
    form.beneficiary_date_of_birth,
    form.beneficiary_relationship,
  ].every((value) => String(value || '').trim() !== '')
})

const canSubmit = computed(() => {
  return !!form.psychologist_id && !!form.scheduled_start && beneficiaryIsComplete.value && !form.processing
})

function submit() {
  if (!canSubmit.value) return
  form.post(route('appointments.store'), {
    preserveScroll: true,
    onError: () => {
      Swal.fire({
        position: 'top-end',
        icon: 'error',
        title: t('appointment.booking_failed'),
        showConfirmButton: false,
        timer: 3000,
        toast: true,
        timerProgressBar: true,
        showCloseButton: true,
      })
    },
    onSuccess: () => {
      try {
        fetch(route('appointments.pendingCount'))
          .then((r) => r.json())
          .then((json) => {
            try { localStorage.setItem('pendingAppointmentsCount', String(Number(json.count || 0))) } catch (e) {}
            try { window.dispatchEvent(new CustomEvent('appointment:count-updated', { detail: { count: Number(json.count || 0) } })) } catch (e) {}
          })
          .catch(() => {})
      } catch (e) {}
    }
  })
}
</script>

<template>
  <Head :title="t('appointment.title')" />

  <Navbar :canLogin="canLogin" :canRegister="canRegister" :authUser="authUser" />

  <div class="bg-gray-50 py-10 md:py-14">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-start justify-between gap-4 mb-8">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900">{{ t('appointment.title') }}</h1>
          <p class="mt-2 text-gray-700 max-w-3xl">
            {{ t('appointment.subtitle') }} <span class="font-semibold">{{ sessionMinutes }} {{ t('appointment.minutes') }}</span>.
          </p>
        </div>

        <Link
          :href="route('services.consultation')"
          class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-white border border-gray-200 text-gray-700 text-sm font-semibold hover:bg-gray-50 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300"
        >
          {{ t('appointment.backToPsychologists') }}
        </Link>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Psychologist card -->
        <div class="lg:col-span-1">
          <div class="group bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden flex flex-col h-full">
            <div class="relative h-64 bg-gray-100 overflow-hidden">
              <div class="absolute top-3 left-3 flex items-center gap-2">
                <span v-if="psychologistProfile.is_approved" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-white/95 text-[#5997ac] ring-1 ring-[#5997ac]/25 backdrop-blur">{{ t('appointment.verified') }}</span>
              </div>

              <img
                v-if="avatarUrl(psychologistProfile)"
                :src="avatarUrl(psychologistProfile)"
                alt="Profile"
                class="h-full w-full object-cover bg-gray-100"
              />
              <div v-else class="h-full w-full flex items-center justify-center bg-gradient-to-br from-[#5997ac]/15 to-[#e8b4b8]/15">
                <span class="text-3xl font-semibold text-gray-700">{{ initials(psychologistProfile) }}</span>
              </div>

              <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-black/60 to-transparent"></div>

              <div class="absolute bottom-3 left-4 right-4">
                <h3 class="text-lg font-semibold text-white leading-snug line-clamp-2">{{ fullName(psychologistProfile) }}</h3>
              </div>
              <div :class="locale === 'ar' ? 'absolute bottom-3 left-2 -translate-x-1' : 'absolute bottom-3 right-2 translate-x-1'">
                <div class="inline-flex items-baseline gap-2 px-3 py-1 rounded-full bg-white/95 text-right shadow-lg">
                  <span class="text-sm font-semibold text-[#5997ac]">{{ formatPrice(psychologistProfile.price_per_session).replace(' TND','') }}</span>
                  <span class="text-xs text-gray-500">TND</span>
                </div>
              </div>
            </div>

            <div class="p-5 flex-1 flex flex-col">
              <div v-if="(psychologistProfile.specialisations || []).length" class="flex flex-wrap gap-2">
                <span
                  v-for="s in (psychologistProfile.specialisations || [])"
                  :key="s.name"
                  class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-[#af5166]/10 text-[#af5166]"
                >
                  {{ s.name }}
                </span>
              </div>

              <div v-if="(psychologistProfile.expertises || []).length" class="mt-2 flex flex-wrap gap-2">
                <span
                  v-for="e in (psychologistProfile.expertises || [])"
                  :key="e.name"
                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-[rgba(175,81,102,0.06)] text-[#af5166]"
                >
                  {{ e.name }}
                </span>
              </div>

              <div v-if="languagesFor(psychologistProfile).length" class="mt-2 flex flex-wrap gap-2">
                <span
                  v-for="label in languagesFor(psychologistProfile)"
                  :key="label"
                  class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-[#5997ac]/10 text-[#5997ac]"
                >
                  {{ label }}
                </span>
              </div>

              <p class="mt-3 text-sm text-gray-700 leading-relaxed line-clamp-3">
                {{ psychologistProfile.bio || t('appointment.noBio') }}
              </p>
            </div>
          </div>
        </div>

        <!-- Slot picker -->
        <div class="lg:col-span-2">
          <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
            <div class="p-5 border-b border-gray-100">
              <div class="flex items-center justify-between gap-3">
                <div class="text-sm font-semibold text-gray-900">{{ t('appointment.pickDate') }}</div>

                <div class="flex items-center gap-2" v-if="monthOptions.length">
                  <button
                    type="button"
                    class="h-9 w-9 inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    @click="goPrevMonth"
                    :disabled="currentMonthIndex <= 0"
                    :aria-label="t('appointment.prevMonth')"
                  >
                    ‹
                  </button>

                  <div class="min-w-[12rem] text-center text-sm font-semibold text-gray-900">
                    {{ currentMonthLabel }}
                  </div>

                  <button
                    type="button"
                    class="h-9 w-9 inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    @click="goNextMonth"
                    :disabled="currentMonthIndex >= monthOptions.length - 1"
                    :aria-label="t('appointment.nextMonth')"
                  >
                    ›
                  </button>
                </div>
              </div>

              <div v-if="!availableDays.length" class="mt-4 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                {{ t('appointment.noSlots') }}
              </div>

              <div v-else class="mt-4">
                <div class="grid grid-cols-7 gap-2 text-xs font-semibold text-gray-500">
                  <div v-for="h in weekdayHeaders" :key="h" class="text-center">{{ h }}</div>
                </div>

                <Transition name="cal-fade" mode="out-in">
                  <div :key="currentMonthKey" class="mt-2 grid grid-cols-7 gap-2">
                    <button
                      v-for="cell in calendarCells"
                      :key="cell.iso"
                      type="button"
                      @click="pickDate(cell.iso)"
                      class="h-10 rounded-xl border text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#5997ac]/40"
                      :class="
                        !cell.inMonth
                          ? 'border-transparent bg-transparent text-transparent cursor-default'
                          : cell.selected
                            ? 'bg-[#5997ac] border-[#5997ac] text-white'
                            : cell.available
                              ? 'bg-white border-gray-200 text-gray-900 hover:bg-gray-50'
                              : 'bg-gray-50 border-gray-200 text-gray-400 cursor-not-allowed'
                      "
                      :disabled="!cell.inMonth || !cell.available"
                    >
                      {{ cell.inMonth ? cell.dt.getDate() : '' }}
                    </button>
                  </div>
                </Transition>

                <div class="mt-3 text-xs text-gray-500">
                  {{ t('appointment.highlightedDays') }}
                </div>
              </div>
            </div>

            <div class="p-5">
              <Transition name="panel-fade" mode="out-in">
                <div :key="selectedDate">
                  <div class="flex items-center justify-between gap-3">
                    <div class="text-sm font-semibold text-gray-900">{{ t('appointment.pickTime') }}</div>
                    <div class="text-xs text-gray-500" v-if="selectedDay">{{ (selectedDay.slots || []).length }} {{ t('appointment.available') }}</div>
                  </div>

                  <div v-if="!selectedDay || !(selectedDay.slots || []).length" class="mt-4 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                    {{ t('appointment.noTimes') }}
                  </div>

                  <div v-else class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    <button
                      v-for="slot in selectedDay.slots"
                      :key="slot.start_iso"
                      type="button"
                      @click="pickSlot(slot)"
                      class="px-3 py-2 rounded-xl border text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#af5166]/30"
                      :class="
                        selectedSlot && selectedSlot.start_iso === slot.start_iso
                          ? 'bg-[#af5166] border-[#af5166] text-white'
                          : 'bg-white border-gray-200 text-gray-900 hover:bg-gray-50'
                      "
                    >
                      {{ slot.start_time }} – {{ slot.end_time }}
                    </button>
                  </div>
                </div>
              </Transition>

              <div class="mt-6 rounded-2xl border border-gray-200 bg-gray-50 p-4 sm:p-5">
                <div>
                  <div class="text-sm font-semibold text-gray-900">{{ t('appointment.whomFor') }}</div>
                  <div class="mt-1 text-xs text-gray-500">{{ t('appointment.whomForDesc') }}</div>
                </div>

                <div class="mt-4 grid gap-3 md:grid-cols-2">
                  <button
                    type="button"
                    @click="form.booking_for = 'self'"
                    class="rounded-2xl border px-4 py-4 text-left transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#5997ac]/30"
                    :class="form.booking_for === 'self' ? 'border-[#5997ac] bg-white ring-1 ring-[#5997ac]/20' : 'border-gray-200 bg-white hover:border-gray-300'"
                  >
                    <div class="flex items-start justify-between gap-3">
                      <div>
                        <div class="text-sm font-semibold text-gray-900">{{ t('appointment.myself') }}</div>
                        <div class="mt-1 text-sm text-gray-600">{{ t('appointment.myselfDesc') }}</div>
                      </div>
                      <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border" :class="form.booking_for === 'self' ? 'border-[#5997ac] bg-[#5997ac] text-white' : 'border-gray-300 bg-white text-transparent'">•</span>
                    </div>
                    <div class="mt-3 text-xs text-gray-500">{{ patientDisplayName() }}</div>
                  </button>

                  <button
                    type="button"
                    @click="form.booking_for = 'other'"
                    class="rounded-2xl border px-4 py-4 text-left transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#5997ac]/30"
                    :class="form.booking_for === 'other' ? 'border-[#5997ac] bg-white ring-1 ring-[#5997ac]/20' : 'border-gray-200 bg-white hover:border-gray-300'"
                  >
                    <div class="flex items-start justify-between gap-3">
                      <div>
                        <div class="text-sm font-semibold text-gray-900">{{ t('appointment.anotherPerson') }}</div>
                        <div class="mt-1 text-sm text-gray-600">{{ t('appointment.anotherPersonDesc') }}</div>
                      </div>
                      <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border" :class="form.booking_for === 'other' ? 'border-[#5997ac] bg-[#5997ac] text-white' : 'border-gray-300 bg-white text-transparent'">•</span>
                    </div>
                  </button>
                </div>

                <div v-if="form.errors.booking_for" class="mt-3 text-sm text-red-600">{{ form.errors.booking_for }}</div>

                <div v-if="form.booking_for === 'other'" class="mt-5 space-y-5">
                  <div v-if="previousBeneficiaries.length" class="rounded-2xl border border-[#5997ac]/15 bg-white p-4">
                    <div class="text-sm font-semibold text-gray-900">{{ t('appointment.previousPeople') }}</div>
                    <div class="mt-1 text-xs text-gray-500">{{ t('appointment.previousPeopleDesc') }}</div>

                    <div class="mt-4 grid grid-cols-1 gap-3 lg:grid-cols-2">
                      <button
                        v-for="beneficiary in previousBeneficiaries"
                        :key="beneficiarySignature(beneficiary)"
                        type="button"
                        @click="applyPreviousBeneficiary(beneficiary)"
                        class="rounded-2xl border px-4 py-3 text-left transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#5997ac]/30"
                        :class="selectedBeneficiarySignature === beneficiarySignature(beneficiary) ? 'border-[#5997ac] bg-[#5997ac]/5 ring-1 ring-[#5997ac]/20' : 'border-gray-200 bg-white hover:border-gray-300'"
                      >
                        <div class="text-sm font-semibold text-gray-900">{{ beneficiary.full_name || t('appointment.anotherPerson') }}</div>
                        <div class="mt-1 text-xs text-gray-500">
                          {{ beneficiary.relationship_to_patient || t('appointment.relationshipNotProvided') }}
                          <span v-if="beneficiary.date_of_birth"> · {{ beneficiary.date_of_birth }}</span>
                        </div>
                      </button>
                    </div>
                  </div>

                  <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">{{ t('appointment.firstName') }}</label>
                      <input v-model="form.beneficiary_first_name" type="text" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#5997ac] focus:ring-[#5997ac]" :placeholder="t('appointment.firstNamePlaceholder')" />
                      <div v-if="form.errors.beneficiary_first_name" class="mt-1 text-sm text-red-600">{{ form.errors.beneficiary_first_name }}</div>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700">{{ t('appointment.lastName') }}</label>
                      <input v-model="form.beneficiary_last_name" type="text" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#5997ac] focus:ring-[#5997ac]" :placeholder="t('appointment.lastNamePlaceholder')" />
                      <div v-if="form.errors.beneficiary_last_name" class="mt-1 text-sm text-red-600">{{ form.errors.beneficiary_last_name }}</div>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700">{{ t('appointment.dateOfBirth') }}</label>
                      <input v-model="form.beneficiary_date_of_birth" type="date" :max="maxBeneficiaryDateOfBirth" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#5997ac] focus:ring-[#5997ac]" />
                      <div v-if="form.errors.beneficiary_date_of_birth" class="mt-1 text-sm text-red-600">{{ form.errors.beneficiary_date_of_birth }}</div>
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700">{{ t('appointment.relationship') }}</label>
                      <input v-model="form.beneficiary_relationship" type="text" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#5997ac] focus:ring-[#5997ac]" :placeholder="t('appointment.relationshipPlaceholder')" />
                      <div v-if="form.errors.beneficiary_relationship" class="mt-1 text-sm text-red-600">{{ form.errors.beneficiary_relationship }}</div>
                    </div>

                    <div class="md:col-span-2">
                      <label class="block text-sm font-medium text-gray-700">{{ t('appointment.gender') }}</label>
                      <select v-model="form.beneficiary_gender" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-[#5997ac] focus:ring-[#5997ac]">
                        <option value="">{{ t('appointment.preferNotSay') }}</option>
                        <option value="female">{{ t('appointment.female') }}</option>
                        <option value="male">{{ t('appointment.male') }}</option>
                        <option value="other">{{ t('appointment.other') }}</option>
                      </select>
                      <div v-if="form.errors.beneficiary_gender" class="mt-1 text-sm text-red-600">{{ form.errors.beneficiary_gender }}</div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-6 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
                <div class="text-sm text-gray-700">
                  <div class="font-medium text-gray-900">{{ t('appointment.selected') }}</div>
                  <div v-if="selectedDay && selectedSlot" class="text-gray-700">
                    {{ selectedDay.date }} · {{ selectedSlot.start_time }} – {{ selectedSlot.end_time }}
                  </div>
                  <div v-else class="text-gray-500">{{ t('appointment.chooseDateTime') }}</div>

                  <div class="mt-2 text-gray-700">
                    {{ t('appointment.for') }}: <span class="font-medium text-gray-900">{{ selectedBeneficiaryName() }}</span>
                    <span class="text-gray-500">({{ bookingForLabel(form.booking_for) }})</span>
                  </div>

                  <div v-if="form.errors.scheduled_start" class="mt-2 text-sm text-red-600">{{ form.errors.scheduled_start }}</div>
                </div>

                <button
                  type="button"
                  @click="submit"
                  :disabled="!canSubmit"
                  class="inline-flex items-center justify-center px-5 py-2.5 rounded-md text-sm font-semibold transition"
                  :class="canSubmit ? 'bg-[#5997ac] text-white hover:opacity-90' : 'bg-gray-200 text-gray-500 cursor-not-allowed'"
                >
                  {{ form.processing ? t('appointment.booking') : t('appointment.bookAppointment') }}
                </button>
              </div>

              <div class="mt-4 text-xs text-gray-500">
                {{ t('appointment.pendingNote') }} <span class="font-medium">{{ t('appointment.pending') }}</span>.
              </div>
            </div>
          </div>

          <div class="sm:hidden mt-6">
            <Link
              :href="route('services.consultation')"
              class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-white border border-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-100 transition"
            >
              {{ t('appointment.backToPsychologists') }}
            </Link>
          </div>
        </div>
      </div>
    </div>
  </div>

  <Footer />
</template>

<style scoped>
[dir="rtl"] {
  text-align: right;
}

.cal-fade-enter-active,
.cal-fade-leave-active,
.panel-fade-enter-active,
.panel-fade-leave-active {
  transition: opacity 160ms ease, transform 160ms ease;
}

.cal-fade-enter-from,
.panel-fade-enter-from {
  opacity: 0;
  transform: translateY(6px);
}

.cal-fade-leave-to,
.panel-fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>