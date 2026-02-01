<template>
  <!-- Language Bar -->
  <div class="flex justify-between items-center px-3 py-2 bg-[#af5166] text-white shadow-md text-[12px] relative">
    <div class="relative">
      <button @click="showDropdown = !showDropdown" class="flex items-center gap-1.5 text-[13px]">
        🌐 {{ currentLang }}
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
      </button>
      <div v-if="showDropdown" class="absolute top-full left-0 mt-1 bg-white dark:bg-gray-800 text-black dark:text-white rounded shadow-md w-max z-50">
        <button v-for="lang in languages" :key="lang.code" @click="setLang(lang.code)"
          class="block w-full text-left px-3 py-1 hover:bg-gray-200 transition text-[13px]">
          {{ lang.label }}
        </button>
      </div>
    </div>

    <div class="flex gap-2.5 items-center">
      <!-- Appointments icon (shows pending appointments count for patient) -->
      <div v-if="isPatient" class="relative">
        <Link
          :href="route('patient.appointments')"
          class="inline-flex items-center justify-center p-2 bg-white/10 text-white rounded-full border border-white/20 hover:bg-white/20 transition mr-2"
          aria-label="Appointments"
          title="Appointments"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="1.5" />
            <path d="M16 2v4M8 2v4" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M3 10h18" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M9 14l2 2 4-4" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
          </svg>
        </Link>
        <span
          v-if="patientCartLocal > 0"
          class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-semibold leading-none text-white bg-red-500 rounded-full shadow-md transform transition-all duration-150"
          :class="{ 'scale-110 ring-4 ring-red-300/40': _pulse }"
          role="status"
          aria-live="polite"
          aria-atomic="true"
        >
          {{ patientCartLocal }}
        </span>
      </div>

      <!-- Patient menu (shown when a patient is logged in) -->
      <div v-if="isPatient" class="relative">
        <button
          type="button"
          @click="showPatientMenu = !showPatientMenu"
          class="flex items-center gap-2 px-3.5 py-1.5 bg-white/10 text-white text-[12px] rounded-full border border-white/20 hover:bg-white/20 transition"
        >
          <span class="inline-flex h-7 w-7 items-center justify-center rounded-full overflow-hidden ring-2 ring-white/25 bg-white/10">
            <img
              v-if="patientAvatarUrl"
              :src="patientAvatarUrl"
              alt="Profile"
              class="h-full w-full object-cover"
            />
            <span v-else class="text-[11px] font-semibold tracking-wide">{{ patientInitials }}</span>
          </span>
          <span class="max-w-[180px] truncate">{{ patientDisplayName }}</span>
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>

        <div
          v-if="showPatientMenu"
          class="absolute right-0 top-full mt-1 bg-white dark:bg-gray-800 text-black dark:text-white rounded shadow-md w-56 z-50 overflow-hidden"
        >
          <!-- Profile Header -->
          <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 dark:bg-gray-700">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full overflow-hidden ring-2 ring-gray-200 dark:ring-gray-600">
                <img
                  v-if="patientAvatarUrl"
                  :src="patientAvatarUrl"
                  alt="Profile"
                  class="w-full h-full object-cover"
                />
                <div v-else class="w-full h-full bg-[#f6aec2] flex items-center justify-center text-white font-semibold">
                  {{ patientInitials }}
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                  {{ patientDisplayName }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                  Patient
                </div>
              </div>
            </div>
          </div>

          <Link
            :href="route('patient.profile')"
            class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-[13px]"
            @click="showPatientMenu = false"
          >
            Profile info
          </Link>
          <Link
            :href="route('patient.account')"
            class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-[13px]"
            @click="showPatientMenu = false"
          >
            Account info
          </Link>
          <Link
            :href="route('patient.appointments')"
            class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-[13px]"
            @click="showPatientMenu = false"
          >
            Appointments
          </Link>
          <Link
            :href="route('patient.appointments')"
            class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-[13px]"
            @click="showPatientMenu = false"
          >
            History
          </Link>
          <div class="border-t border-gray-200"></div>
          <button
            type="button"
            class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-[13px]"
            @click.prevent="handlePatientLogout"
          >
            Log out
          </button>
        </div>
      </div>
      <!-- Psychologist menu (shown when a psychologist is logged in) -->
      <div v-if="isPsychologist" class="relative">
        <button
          type="button"
          @click="showPsychologistMenu = !showPsychologistMenu"
          class="flex items-center gap-2 px-3.5 py-1.5 bg-white/10 text-white text-[12px] rounded-full border border-white/20 hover:bg-white/20 transition"
        >
          <span class="inline-flex h-7 w-7 items-center justify-center rounded-full overflow-hidden ring-2 ring-white/25 bg-white/10">
            <img
              v-if="psychologistAvatarUrl"
              :src="psychologistAvatarUrl"
              alt="Profile"
              class="h-full w-full object-cover"
            />
            <span v-else class="text-[11px] font-semibold tracking-wide">{{ psychologistInitials }}</span>
          </span>
          <span class="max-w-[180px] truncate">{{ psychologistDisplayName }}</span>
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </button>

        <div
          v-if="showPsychologistMenu"
          class="absolute right-0 top-full mt-1 bg-white dark:bg-gray-800 text-black dark:text-white rounded shadow-md w-56 z-50 overflow-hidden"
        >
          <!-- Profile Header -->
          <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 dark:bg-gray-700">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full overflow-hidden ring-2 ring-gray-200 dark:ring-gray-600">
                <img
                  v-if="psychologistAvatarUrl"
                  :src="psychologistAvatarUrl"
                  alt="Profile"
                  class="w-full h-full object-cover"
                />
                <div v-else class="w-full h-full bg-[#5997ac] flex items-center justify-center text-white font-semibold">
                  {{ psychologistInitials }}
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                  {{ psychologistDisplayName }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                  Psychologist
                </div>
              </div>
            </div>
          </div>

          <Link
            :href="route('psychologist.profile.self')"
            class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-[13px]"
            @click="showPsychologistMenu = false"
          >
            Edit Profile
          </Link>

          <Link
            :href="route('psychologist.account')"
            class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-[13px]"
            @click="showPsychologistMenu = false"
          >
            Account Settings
          </Link>

          <Link
            :href="route('psychologist.availabilities')"
            class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-[13px]"
            @click="showPsychologistMenu = false"
          >
            Manage Availability
          </Link>

          <Link
            :href="route('psychologist.appointments.index')"
            class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-[13px]"
            @click="showPsychologistMenu = false"
          >
            Appointments
          </Link>

          <Link
            :href="route('psychologist.patients.index')"
            class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-[13px]"
            @click="showPsychologistMenu = false"
          >
            Patients
          </Link>

          <Link
            :href="route('psychologist.payouts.index')"
            class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-[13px]"
            @click="showPsychologistMenu = false"
          >
            Payouts
          </Link>
          <div class="border-t border-gray-200"></div>
          <button
            type="button"
            class="block w-full text-left px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 transition text-[13px]"
            @click.prevent="handlePsychologistLogout"
          >
            Log out
          </button>
        </div>
      </div>

      <!-- Guest auth buttons -->
      <Link v-if="!resolvedAuthUser && canLogin" :href="route('login')"
        class="px-3.5 py-1.5 bg-[#5997ac] text-white text-[12px] rounded flex items-center gap-1.5 justify-center hover:bg-[#467891] transition">
        🔓 {{ t("login") }}
      </Link>
      <Link v-if="!resolvedAuthUser && canRegister" :href="route('register')"
        class="px-3.5 py-1.5 bg-[#f6aec2] text-white text-[12px] rounded flex items-center gap-1.5 justify-center hover:bg-[#e190b0] transition">
        👤 {{ t("register") }}
      </Link>
    </div>
  </div>

  <!-- Navigation Bar -->
  <nav class="bg-white shadow-sm">
    <div class="mx-auto px-4">
      <div class="flex items-center justify-between py-3">
        <!-- Logo (Extreme Left) -->
        <div class="flex-shrink-0">
          <Link :href="route('home')" class="cursor-pointer">
            <img src="/storage/aenhance.svg" alt="Logo" class="h-14 w-auto object-contain hover:opacity-80 transition-opacity" />
          </Link>
        </div>

        <!-- Desktop Navigation -->
        <ul class="hidden lg:flex gap-8 items-center flex-1 justify-center">
          <!-- About -->
          <li class="relative">
            <button @click="showAboutDropdown = !showAboutDropdown"
              class="px-3 py-1 text-gray-700 font-medium flex items-center gap-1 relative group hover:bg-gray-100 transition">
              {{ t('nav.about') }}
              <svg class="w-3 h-3 mt-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                      d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 111.02 1.1l-4 3.625a.75.75 0 01-1.02 0l-4-3.625a.75.75 0 01.02-1.06z"
                      clip-rule="evenodd"/>
              </svg>
            </button>
            <ul v-if="showAboutDropdown" class="absolute top-full left-0 mt-1 bg-white dark:bg-gray-800 text-black dark:text-white rounded shadow-md w-52 z-50">
              <li v-for="item in aboutItems" :key="item">
                <Link 
                  :href="item.href" 
                  class="block px-4 py-2 hover:bg-gray-100 text-sm transition"
                  @click="showAboutDropdown = false"
                >
                  {{ item.label }}
                </Link>
              </li>
            </ul>
          </li>

          <!-- Other links -->
          <li>
            <Link
              :href="route('services.index')"
              class="px-3 py-1 text-gray-700 font-medium hover:bg-gray-100 transition"
            >
              {{ t('nav.services') }}
            </Link>
          </li>
          
          <!-- Support Dropdown -->
          <li class="relative">
            <button @click="showSupportDropdown = !showSupportDropdown"
              class="px-3 py-1 text-gray-700 font-medium flex items-center gap-1 relative group hover:bg-gray-100 transition">
              {{ t('nav.support') }}
              <svg class="w-3 h-3 mt-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                      d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 111.02 1.1l-4 3.625a.75.75 0 01-1.02 0l-4-3.625a.75.75 0 01.02-1.06z"
                      clip-rule="evenodd"/>
              </svg>
            </button>
            <ul v-if="showSupportDropdown" class="absolute top-full left-0 mt-1 bg-white dark:bg-gray-800 text-black dark:text-white rounded shadow-md w-52 z-50">
              <li v-for="item in supportItems" :key="item.label">
                <Link 
                  :href="item.href" 
                  class="block px-4 py-2 hover:bg-gray-100 text-sm transition"
                  @click="showSupportDropdown = false"
                >
                  {{ item.label }}
                </Link>
              </li>
            </ul>
          </li>
          
          <li><a href="#" class="px-3 py-1 text-gray-700 font-medium hover:bg-gray-100 transition">{{ t('nav.resources') }}</a></li>
          <li><a href="#" class="px-3 py-1 text-gray-700 font-medium hover:bg-gray-100 transition">{{ t('nav.blog') }}</a></li>
        </ul>

        <!-- Hamburger (mobile) -->
        <button @click="showMobileMenu = !showMobileMenu" class="lg:hidden p-2 rounded-md border border-gray-300">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
      </div>

      <!-- Mobile Menu -->
      <div v-if="showMobileMenu" class="lg:hidden border-t border-gray-200 py-3">
        <ul class="space-y-2">
          <!-- About -->
          <li>
            <button @click="showAboutDropdown = !showAboutDropdown"
              class="w-full text-left px-3 py-2 text-gray-700 font-medium flex items-center justify-between hover:bg-gray-100 transition">
              {{ t('nav.about') }}
              <svg class="w-4 h-4" :class="{ 'rotate-180': showAboutDropdown }" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                      d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 111.02 1.1l-4 3.625a.75.75 0 01-1.02 0l-4-3.625a.75.75 0 01.02-1.06z"
                      clip-rule="evenodd"/>
              </svg>
            </button>
            <ul v-if="showAboutDropdown" class="pl-6 mt-2 space-y-1">
              <li v-for="item in aboutItems" :key="item">
                <Link 
                  :href="item.href" 
                  class="block px-3 py-2 text-sm text-gray-600 hover:bg-gray-100 transition"
                  @click="showAboutDropdown = false; showMobileMenu = false"
                >
                  {{ item.label }}
                </Link>
              </li>
            </ul>
          </li>

          <!-- Other links -->
          <li>
            <Link
              :href="route('services.index')"
              class="block px-3 py-2 text-gray-700 font-medium hover:bg-gray-100 transition"
              @click="showMobileMenu = false"
            >
              {{ t('nav.services') }}
            </Link>
          </li>
          
          <!-- Support Dropdown (Mobile) -->
          <li>
            <button @click="showSupportDropdown = !showSupportDropdown"
              class="w-full text-left px-3 py-2 text-gray-700 font-medium flex items-center justify-between hover:bg-gray-100 transition">
              {{ t('nav.support') }}
              <svg class="w-4 h-4" :class="{ 'rotate-180': showSupportDropdown }" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                      d="M5.23 7.21a.75.75 0 011.06.02L10 10.585l3.71-3.356a.75.75 0 111.02 1.1l-4 3.625a.75.75 0 01-1.02 0l-4-3.625a.75.75 0 01.02-1.06z"
                      clip-rule="evenodd"/>
              </svg>
            </button>
            <ul v-if="showSupportDropdown" class="pl-6 mt-2 space-y-1">
              <li v-for="item in supportItems" :key="item.label">
                <Link 
                  :href="item.href" 
                  class="block px-3 py-2 text-sm text-gray-600 hover:bg-gray-100 transition"
                  @click="showSupportDropdown = false; showMobileMenu = false"
                >
                  {{ item.label }}
                </Link>
              </li>
            </ul>
          </li>
          
          <li><a href="#" class="block px-3 py-2 text-gray-700 font-medium hover:bg-gray-100 transition">{{ t('nav.resources') }}</a></li>
          <li><a href="#" class="block px-3 py-2 text-gray-700 font-medium hover:bg-gray-100 transition">{{ t('nav.blog') }}</a></li>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { Link, usePage } from "@inertiajs/vue3";
import { Inertia } from '@inertiajs/inertia'
import { useI18n } from "vue-i18n";
import { ref, onMounted, computed, watch, onBeforeUnmount } from "vue";

const props = defineProps({
  canLogin: { type: Boolean },
  canRegister: { type: Boolean },
  authUser: { type: Object },
});

const page = usePage()

const resolvedAuthUser = computed(() => {
  const fromPage = page.props?.auth?.user || null
  const fromProp = props.authUser || null
  if (!fromPage) return fromProp
  if (!fromProp) return fromPage

  return {
    ...fromPage,
    ...fromProp,
    profile_image_url: fromProp.profile_image_url ?? fromPage.profile_image_url ?? null,
  }
})

const showDropdown = ref(false);
const showAboutDropdown = ref(false);
const showSupportDropdown = ref(false);
const showMobileMenu = ref(false);
const showPatientMenu = ref(false)
const showPsychologistMenu = ref(false)
const { t, locale } = useI18n();
const currentLang = ref("");

const isPatient = computed(() => {
  const role = (resolvedAuthUser.value?.role ?? '').toString().trim().toUpperCase()
  return !!resolvedAuthUser.value && role === 'PATIENT'
})

const patientDisplayName = computed(() => {
  return resolvedAuthUser.value?.name || resolvedAuthUser.value?.email || 'Account'
})

const patientAvatarUrl = computed(() => {
  const url = resolvedAuthUser.value?.profile_image_url
  if (!url) return null
  if (typeof url !== 'string') return null
  if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('/')) return url
  return `/${url}`
})

const patientInitials = computed(() => {
  const source = (resolvedAuthUser.value?.name || resolvedAuthUser.value?.email || 'A').trim()
  const parts = source.split(/\s+/).filter(Boolean)
  const initials = parts.slice(0, 2).map((p) => p[0]).join('')
  return (initials || 'A').toUpperCase()
})

const isPsychologist = computed(() => {
  const role = (resolvedAuthUser.value?.role ?? '').toString().trim().toUpperCase()
  return !!resolvedAuthUser.value && role === 'PSYCHOLOGIST'
})

// Pending appointments count (cart) - read from common props with fallbacks
const patientCartCount = computed(() => {
  const p = page.props || {}
  // possible server-side keys: pendingAppointmentsCount, cart.appointments, auth.user.pending_appointments_count
  const fromPending = Number(p.pendingAppointmentsCount || p.pending_appointments_count || 0)
  if (fromPending && !Number.isNaN(fromPending)) return fromPending
  const fromCart = (p.cart && Array.isArray(p.cart.appointments)) ? p.cart.appointments.length : 0
  if (fromCart) return fromCart
  const fromUser = Number(p?.auth?.user?.pending_appointments_count || 0)
  if (fromUser && !Number.isNaN(fromUser)) return fromUser
  // Fallback: scan props for any arrays of appointment-like objects and count those with pending status
  function countPendingInArray(arr) {
    if (!Array.isArray(arr)) return 0
    return arr.reduce((acc, item) => {
      if (!item || typeof item !== 'object') return acc
      const status = (item.status || item.state || item.status_name || '').toString().toLowerCase()
      if (status === 'pending' || status === 'awaiting' || status === 'scheduled') return acc + 1
      return acc
    }, 0)
  }

  let scanned = 0
  for (const key in p) {
    if (!Object.prototype.hasOwnProperty.call(p, key)) continue
    const val = p[key]
    if (Array.isArray(val)) {
      scanned += countPendingInArray(val)
    } else if (val && typeof val === 'object') {
      // look for nested arrays inside objects
      for (const k2 in val) {
        if (Array.isArray(val[k2])) scanned += countPendingInArray(val[k2])
      }
    }
  }

  if (scanned > 0) return scanned
  return 0
})

// Local badge that can be updated instantly when appointments are added/removed.
// Initialized from the computed `patientCartCount` and kept in sync.
const patientCartLocal = ref(Number(patientCartCount.value || 0))
const _pulse = ref(false)

// When server-provided prop changes (e.g., on full page visit), sync local value.
watch(patientCartCount, (v) => {
  const newVal = Number(v || 0)
  patientCartLocal.value = newVal
})

function handleAppointmentAdded(evt) {
  // optional: payload may include count
  const increment = Number(evt?.detail?.count ?? 1)
  patientCartLocal.value = Number(patientCartLocal.value || 0) + increment
  // trigger a quick pulse animation
  _pulse.value = true
  setTimeout(() => (_pulse.value = false), 700)
}

function handleAppointmentRemoved(evt) {
  const decrement = Number(evt?.detail?.count ?? 1)
  patientCartLocal.value = Math.max(0, Number(patientCartLocal.value || 0) - decrement)
}

onMounted(() => {
  window.addEventListener('appointment:added', handleAppointmentAdded)
  window.addEventListener('appointment:removed', handleAppointmentRemoved)
})

onBeforeUnmount(() => {
  window.removeEventListener('appointment:added', handleAppointmentAdded)
  window.removeEventListener('appointment:removed', handleAppointmentRemoved)
})

const psychologistDisplayName = computed(() => {
  return resolvedAuthUser.value?.name || resolvedAuthUser.value?.email || 'Account'
})

const psychologistAvatarUrl = computed(() => {
  const url = resolvedAuthUser.value?.profile_image_url
  if (!url) return null
  if (typeof url !== 'string') return null
  if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('/')) return url
  return `/${url}`
})

const psychologistInitials = computed(() => {
  const source = (resolvedAuthUser.value?.name || resolvedAuthUser.value?.email || 'A').trim()
  const parts = source.split(/\s+/).filter(Boolean)
  const initials = parts.slice(0, 2).map((p) => p[0]).join('')
  return (initials || 'A').toUpperCase()
})

// Languages list
const languages = [
  { code: "en", label: "🇬🇧 English" },
  { code: "fr", label: "🇫🇷 Français" },
  { code: "ar", label: "🇸🇦 العربية" },
];

// Set language
function setLang(lang) {
  locale.value = lang;
  currentLang.value = languages.find((l) => l.code === lang).label;
  localStorage.setItem("locale", lang);
  showDropdown.value = false;
  showPatientMenu.value = false
  showPsychologistMenu.value = false
  
  // Set document direction based on language
  if (lang === 'ar') {
    document.documentElement.setAttribute('dir', 'rtl');
    document.documentElement.setAttribute('lang', 'ar');
  } else {
    document.documentElement.setAttribute('dir', 'ltr');
    document.documentElement.setAttribute('lang', lang);
  }
}

// Load saved language
onMounted(() => {
  const savedLang = localStorage.getItem("locale") || locale.value;
  setLang(savedLang);
});

// About dropdown items reactive to language
const aboutItems = computed(() => [
  { label: t("nav.aboutItems.0"), href: route('telemental-health') },
  { label: t("nav.aboutItems.1"), href: route('who-we-are') },
  { label: t("nav.aboutItems.2"), href: "#" },
  { label: t("nav.aboutItems.3"), href: "#" },
  { label: t("nav.aboutItems.4"), href: route('terms-conditions') },
  { label: t("nav.aboutItems.5"), href: route('privacy-protection') }
]);

// Support dropdown items reactive to language
const supportItems = computed(() => [
  { label: t("nav.supportItems.0"), href: route('faq') },
  { label: t("nav.supportItems.1"), href: route('how-it-works') }
]);

async function handlePatientLogout(e) {
  try {
    await Inertia.post(route('logout'))
  } finally {
    // hide menu after request starts/completes
    showPatientMenu.value = false
  }
}

async function handlePsychologistLogout(e) {
  try {
    await Inertia.post(route('logout'))
  } finally {
    showPsychologistMenu.value = false
  }
}
</script>

<style scoped>
nav ul li a {
  position: relative;
}
nav ul li a::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 2px;
  background-color: #5997ac;
  transform: scaleX(0);
  transform-origin: left;
  transition: transform 0.3s;
}
nav ul li:hover > a::before {
  transform: scaleX(1);
}

/* RTL Support for Arabic */
[dir="rtl"] nav ul li a::before {
  transform-origin: right;
}
</style>