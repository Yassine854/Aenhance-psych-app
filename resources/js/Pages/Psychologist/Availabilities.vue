<template>
  <div>
    <Head title="My Availabilities" />
    <Navbar :canLogin="routeHas('login')" :canRegister="routeHas('register')" :authUser="page.props.auth.user" />

    <div>
      <div class="bg-gradient-to-r from-[#af5166] to-[#5997ac]">
        <div class="mx-auto max-w-7xl px-4 py-8">
          <h1 class="text-2xl sm:text-3xl font-semibold text-white">My Availabilities</h1>
          <p class="mt-1 text-sm text-white/90">Manage your weekly availability slots — add, edit, and remove time ranges for each day.</p>
        </div>
      </div>

      <div class="mx-auto max-w-7xl p-6">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100">
        <div class="bg-gradient-to-r from-[#5997ac] to-[#7ba8b7] px-6 py-4 rounded-t-2xl">
          <h2 class="text-lg font-semibold text-white">My Availabilities</h2>
          <p class="text-sm text-white/80">View and edit your weekly availability slots</p>
        </div>
        <div class="p-6">
          <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
          <p v-if="availabilityRequiredError" class="text-sm text-red-600">{{ availabilityRequiredError }}</p>
          <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="d in daysOfWeek" :key="d.value" class="rounded-2xl bg-white border border-gray-100 shadow-sm p-4 flex flex-col">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <div class="text-sm font-semibold text-gray-900">{{ d.label }}</div>
                  <div class="text-xs text-gray-500 mt-1">{{ (availabilityByDay[d.value] || []).length }} slot(s)</div>
                </div>
                <button type="button" @click="addSlotForDay(d.value)" class="inline-flex items-center gap-2 px-3 py-1.5 text-sm bg-gradient-to-r from-[#5997ac] to-[#7ba8b7] text-white rounded-full shadow hover:opacity-95">
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                  Add
                </button>
              </div>

              <div class="mt-3 space-y-2">
                <template v-if="availabilityByDay[d.value].length">
                  <div v-for="(slot, idx) in availabilityByDay[d.value]" :key="idx" class="flex items-center justify-between gap-3 bg-gray-50 rounded-lg px-3 py-2">
                    <div class="flex items-center gap-3">
                      <div class="text-sm font-medium text-gray-800">{{ slot.start_time }} — {{ slot.end_time }}</div>
                      <div class="hidden sm:inline-flex items-center text-xs text-gray-500 bg-white/50 px-2 py-1 rounded">Day {{ d.value }}</div>
                    </div>
                    <div class="flex items-center gap-2">
                      <input type="time" v-model="slot.start_time" @change="onSlotChanged(d.value)" class="text-xs px-2 py-1 rounded-md border border-gray-200 bg-white" />
                      <input type="time" v-model="slot.end_time" @change="onSlotChanged(d.value)" class="text-xs px-2 py-1 rounded-md border border-gray-200 bg-white" />
                      <button type="button" @click="removeSlotForDay(d.value, idx)" class="text-sm text-red-600 hover:text-red-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                      </button>
                    </div>
                  </div>
                </template>
                <div v-else class="text-sm text-gray-500">No slots added.</div>
              </div>

              <p v-if="availabilityErrorsByDay[d.value]" class="mt-3 text-sm text-red-600">{{ availabilityErrorsByDay[d.value] }}</p>
            </div>
          </div>

          <div class="mt-6 flex items-center gap-3">
            <PrimaryButton
              :disabled="saving"
              @click="save"
              class="inline-flex items-center gap-2 px-6 py-2 bg-gradient-to-r from-[#af5166] to-[#c66b85] hover:from-[#8b4156] hover:to-[#a54f6a] text-white font-medium rounded-lg shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#af5166] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="saving" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
              </svg>
              <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
              {{ saving ? 'Saving Changes...' : 'Save Changes' }}
            </PrimaryButton>

            <transition name="fade">
              <div v-if="savedMessage" class="text-sm text-green-600">{{ savedMessage }}</div>
            </transition>
          </div>
        </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import Navbar from '@/Components/Navbar.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { Head, usePage } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import Swal from 'sweetalert2'

const page = usePage()
const props = defineProps({})

const daysOfWeek = [
  { value: 0, label: 'Sunday' },
  { value: 1, label: 'Monday' },
  { value: 2, label: 'Tuesday' },
  { value: 3, label: 'Wednesday' },
  { value: 4, label: 'Thursday' },
  { value: 5, label: 'Friday' },
  { value: 6, label: 'Saturday' },
]

const emptyWeeklyAvailability = () => ({ 0: [], 1: [], 2: [], 3: [], 4: [], 5: [], 6: [] })

const availabilityByDay = ref(emptyWeeklyAvailability())
const availabilityErrorsByDay = ref({ 0: '', 1: '', 2: '', 3: '', 4: '', 5: '', 6: '' })
const availabilityRequiredError = ref('')
const saving = ref(false)
const error = ref('')
const savedMessage = ref('')

function normalizeTime(t) {
  if (!t) return ''
  const s = String(t)
  const m = s.match(/^(\d{2}:\d{2})/)
  return m ? m[1] : ''
}

function timeToMinutes(t) {
  if (!t || typeof t !== 'string') return null
  const m = t.match(/^(\d{2}):(\d{2})$/)
  if (!m) return null
  const hh = Number(m[1])
  const mm = Number(m[2])
  if (Number.isNaN(hh) || Number.isNaN(mm)) return null
  return hh * 60 + mm
}

function sortSlots(day) {
  availabilityByDay.value[day].sort((a, b) => {
    const am = timeToMinutes(a.start_time) ?? 0
    const bm = timeToMinutes(b.start_time) ?? 0
    return am - bm
  })
}

function validateDaySlots(day) {
  availabilityErrorsByDay.value[day] = ''
  const slots = availabilityByDay.value[day]
  if (!slots.length) return true

  const normalized = slots.map((s) => ({ start: timeToMinutes(s.start_time), end: timeToMinutes(s.end_time) }))

  for (const s of normalized) {
    if (s.start === null || s.end === null) {
      availabilityErrorsByDay.value[day] = 'Please set start and end time for all slots.'
      return false
    }
    if (s.end <= s.start) {
      availabilityErrorsByDay.value[day] = 'End time must be after start time.'
      return false
    }
  }

  const sorted = [...normalized].sort((a, b) => a.start - b.start)
  for (let i = 1; i < sorted.length; i++) {
    const prev = sorted[i - 1]
    const cur = sorted[i]
    if (cur.start < prev.end) {
      availabilityErrorsByDay.value[day] = 'Slots overlap. Please adjust times.'
      return false
    }
  }

  return true
}

function onSlotChanged(day) {
  availabilityRequiredError.value = ''
  sortSlots(day)
  validateDaySlots(day)
}

function addSlotForDay(day) {
  availabilityErrorsByDay.value[day] = ''
  availabilityRequiredError.value = ''
  availabilityByDay.value[day].push({ start_time: '09:00', end_time: '12:00' })
  onSlotChanged(day)
}

function removeSlotForDay(day, index) {
  availabilityByDay.value[day].splice(index, 1)
  onSlotChanged(day)
}

const flattenedAvailabilities = computed(() => {
  const out = []
  for (const d of daysOfWeek) {
    for (const slot of availabilityByDay.value[d.value] || []) {
      out.push({ day_of_week: d.value, start_time: slot.start_time, end_time: slot.end_time })
    }
  }
  return out
})

function parsedAvailabilitiesByDay(availabilities) {
  const byDay = emptyWeeklyAvailability()
  for (const a of availabilities || []) {
    const day = Number(a?.day_of_week)
    if (Number.isNaN(day) || day < 0 || day > 6) continue
    byDay[day].push({ start_time: normalizeTime(a?.start_time), end_time: normalizeTime(a?.end_time) })
  }
  for (const d of daysOfWeek) {
    byDay[d.value].sort((a, b) => {
      const am = timeToMinutes(a.start_time) ?? 0
      const bm = timeToMinutes(b.start_time) ?? 0
      return am - bm
    })
  }
  return byDay
}

watch(() => page.props.profile, (p) => {
  availabilityByDay.value = parsedAvailabilitiesByDay(p?.availabilities || [])
}, { immediate: true })

async function ensureCsrfToken() {
  const m1 = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  if (m1) return { token: decodeURIComponent(m1[1]), type: 'cookie' }

  const tokenEl = document.querySelector('meta[name="csrf-token"]')
  const metaToken = tokenEl?.getAttribute('content') || ''
  if (metaToken) return { token: metaToken, type: 'meta' }

  try {
    await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' })
  } catch {}

  const m2 = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  return m2 ? { token: decodeURIComponent(m2[1]), type: 'cookie' } : { token: '', type: 'none' }
}

async function save() {
  error.value = ''
  // clear previous per-day errors
  for (const k of Object.keys(availabilityErrorsByDay.value)) availabilityErrorsByDay.value[k] = ''
  // ensure validation
  let ok = true
  for (const d of daysOfWeek) {
    if ((availabilityByDay.value[d.value] || []).length) {
      if (!validateDaySlots(d.value)) ok = false
    }
  }
  if (!ok) return
  if (!flattenedAvailabilities.value.length) {
    availabilityRequiredError.value = 'Please add at least one availability slot.'
    return
  }

  saving.value = true
  try {
    const csrf = await ensureCsrfToken()
    const payload = { availabilities: JSON.stringify(flattenedAvailabilities.value) }
    const headers = { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    if (csrf.type === 'meta' && csrf.token) headers['X-CSRF-TOKEN'] = csrf.token
    else if (csrf.type === 'cookie' && csrf.token) headers['X-XSRF-TOKEN'] = csrf.token

    const res = await fetch(route('psychologist.availabilities.update'), {
      method: 'PATCH',
      headers,
      body: JSON.stringify(payload),
      credentials: 'include',
    })

    if (!res.ok) {
      // try parse json for validation errors
      let j = null
      try { j = await res.json() } catch (e) { /* ignore */ }
      if (res.status === 422 && j && j.errors) {
        // reset per-day errors
        for (const k of Object.keys(availabilityErrorsByDay.value)) availabilityErrorsByDay.value[k] = ''

        const msgs = []
        for (const key of Object.keys(j.errors)) {
          const m = j.errors[key]
          const msg = Array.isArray(m) ? m[0] : m
          msgs.push(msg)

          const match = key.match(/^availabilities\.(\d+)\.(start_time|end_time|day_of_week)$/)
          if (match) {
            const idx = Number(match[1])
            const flat = flattenedAvailabilities.value[idx]
            if (flat) {
              const day = Number(flat.day_of_week)
              availabilityErrorsByDay.value[day] = availabilityErrorsByDay.value[day] ? availabilityErrorsByDay.value[day] + ' ' + msg : msg
            }
          }
          if (key === 'availabilities') {
            availabilityRequiredError.value = Array.isArray(j.errors.availabilities) ? j.errors.availabilities[0] : j.errors.availabilities
          }
        }
        const combined = msgs.join(' ')
        error.value = combined || 'Validation failed'
        Swal.fire({ position: 'top-end', icon: 'error', title: 'Save failed', text: error.value, toast: true, timer: 4000, showConfirmButton: false, timerProgressBar: true })
        return
      }

      // fallback: read text
      const txt = await res.text().catch(() => '')
      try { const parsed = JSON.parse(txt); error.value = parsed.message || 'Failed to save' } catch { error.value = txt || 'Failed to save' }
      Swal.fire({ position: 'top-end', icon: 'error', title: 'Save failed', text: error.value, toast: true, timer: 4000, showConfirmButton: false, timerProgressBar: true })
      return
    }

    // success
    const data = await res.json().catch(() => null)
    if (data && data.success) {
      // reflect saved data
      availabilityByDay.value = parsedAvailabilitiesByDay(data.availabilities || [])
      savedMessage.value = 'Saved'
      Swal.fire({ position: 'top-end', icon: 'success', title: 'Availabilities saved', toast: true, timer: 2500, showConfirmButton: false, timerProgressBar: true })
      setTimeout(() => { savedMessage.value = '' }, 2500)
    }
  } catch (e) {
    error.value = e?.message || 'Error saving availabilities'
  } finally {
    saving.value = false
  }
}

async function reloadFromServer() {
  // reload profile via Inertia visit to same URL (forces props refresh)
  await fetch(window.location.href, { credentials: 'include' })
  availabilityByDay.value = parsedAvailabilitiesByDay(page.props.profile?.availabilities || [])
}

function routeHas(name) {
  try { return !!route(name) } catch { return false }
}
</script>

<style scoped>
</style>
