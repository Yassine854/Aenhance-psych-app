<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { computed, onMounted, ref, onBeforeUnmount, nextTick, reactive } from "vue";
import Navbar from "@/Components/Navbar.vue";
import Footer from "@/Components/Footer.vue";
import ReportModal from '@/Components/ReportModal.vue'

const props = defineProps({
  canLogin: { type: Boolean },
  canRegister: { type: Boolean },
  authUser: { type: Object },
  profiles: { type: Array, default: () => [] },
});

const { t, locale } = useI18n();

function setLang(lang) {
  locale.value = lang;
  localStorage.setItem("locale", lang);

  if (lang === "ar") {
    document.documentElement.setAttribute("dir", "rtl");
    document.documentElement.setAttribute("lang", "ar");
  } else {
    document.documentElement.setAttribute("dir", "ltr");
    document.documentElement.setAttribute("lang", lang);
  }
}

onMounted(() => {
  const savedLang = localStorage.getItem("locale") || locale.value;
  setLang(savedLang);
});

const dayNames = computed(() => {
  if (locale.value === "fr") {
    return ["Dimanche", "Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi"]; 
  }
  if (locale.value === "ar") {
    return ["الأحد", "الإثنين", "الثلاثاء", "الأربعاء", "الخميس", "الجمعة", "السبت"]; 
  }
  return ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"]; 
});

function fullName(profile) {
  const first = (profile?.first_name || "").trim();
  const last = (profile?.last_name || "").trim();
  const combined = `${first} ${last}`.trim();
  return combined || profile?.user?.name || profile?.user?.email || t("services.consultation.unknownName");
}

function avatarUrl(profile) {
  const url = profile?.profile_image_url;
  if (!url || typeof url !== "string") return null;
  if (url.startsWith("http://") || url.startsWith("https://") || url.startsWith("/")) return url;
  return `/${url}`;
}

function initials(profile) {
  const source = fullName(profile);
  const parts = source.split(/\s+/).filter(Boolean);
  const letters = parts.slice(0, 2).map((p) => (p[0] || "").toUpperCase()).join("");
  return letters || "A";
}

function formatTime(timeStr) {
  if (!timeStr || typeof timeStr !== "string") return "";
  return timeStr.slice(0, 5);
}

function formatPrice(value) {
  if (value === null || value === undefined || value === "") return t("services.consultation.priceUnknown");
  const num = Number(value);
  if (Number.isNaN(num)) return String(value);
  return `${num.toFixed(2)} TND`;
}

function availabilityLines(profile) {
  const items = Array.isArray(profile?.availabilities) ? profile.availabilities : [];
  if (!items.length) return [];

  return items.slice(0, 3).map((slot) => {
    const day = dayNames.value[slot.day_of_week] ?? "";
    const start = formatTime(slot.start_time);
    const end = formatTime(slot.end_time);
    return `${day} · ${start}–${end}`;
  });
}

function availabilityDays(profile) {
  const items = Array.isArray(profile?.availabilities) ? profile.availabilities : [];
  const seen = new Set();
  const days = [];
  for (const slot of items) {
    const idx = Number(slot.day_of_week);
    if (Number.isNaN(idx)) continue;
    const name = dayNames.value[idx] ?? null;
    if (name && !seen.has(name)) {
      seen.add(name);
      days.push(name);
    }
  }
  return days;
}

function languageLabel(lang) {
  const v = String(lang || '').toLowerCase();
  if (locale.value === 'fr') {
    if (v === 'english') return 'Anglais';
    if (v === 'french') return 'Français';
    if (v === 'arabic') return 'Arabe';
  }
  if (locale.value === 'ar') {
    if (v === 'english') return 'الإنجليزية';
    if (v === 'french') return 'الفرنسية';
    if (v === 'arabic') return 'العربية';
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

function specialisationsFor(profile) {
  const items = Array.isArray(profile?.specialisations) ? profile.specialisations : [];
  return items
    .map((s) => (s?.name || '').trim())
    .filter(Boolean);
}

function expertisesFor(profile) {
  const items = Array.isArray(profile?.expertises) ? profile.expertises : [];
  return items
    .map((e) => (e?.name || '').trim())
    .filter(Boolean);
}

function selectHref(profile) {
  const bookUrl = route('appointments.book', profile?.id);

  const role = String(props.authUser?.role || '').toUpperCase();
  if (props.authUser && role === 'PATIENT') {
    return bookUrl;
  }
  if (props.authUser) {
    return route('dashboard');
  }

  const target = props.canLogin ? route('login') : route('register');
  const params = new URLSearchParams();
  params.set('redirect', bookUrl);
  return `${target}?${params.toString()}`;
}

// Report modal state
const showReportModal = ref(false)
const reportProfile = ref(null)
const pressed = ref(false)
// Select button press state (tracks which card is pressed)
const selectPressed = ref(null)

// Filter state
const filterOpen = ref(false)
const filters = reactive({
  specialisations: new Set(),
  expertises: new Set(),
  languages: new Set(),
  days: new Set(),
  priceMin: null,
  priceMax: null,
})

function toggleFilter() {
  filterOpen.value = !filterOpen.value
}

function resetFilters() {
  filters.specialisations = new Set()
  filters.expertises = new Set()
  filters.languages = new Set()
  filters.days = new Set()
  filters.priceMin = null
  filters.priceMax = null
}

function toggleSet(setRef, value) {
  if (!setRef || typeof setRef.has !== 'function') return
  if (setRef.has(value)) setRef.delete(value)
  else setRef.add(value)
}

const allSpecialisations = computed(() => {
  const s = new Set()
  for (const p of props.profiles || []) {
    for (const v of specialisationsFor(p)) s.add(v)
  }
  return Array.from(s).sort()
})

const allExpertises = computed(() => {
  const s = new Set()
  for (const p of props.profiles || []) {
    for (const v of expertisesFor(p)) s.add(v)
  }
  return Array.from(s).sort()
})

const allLanguages = computed(() => {
  const s = new Set()
  for (const p of props.profiles || []) {
    for (const v of languagesFor(p)) s.add(v)
  }
  return Array.from(s).sort()
})

const allDays = computed(() => {
  const s = new Set()
  for (const p of props.profiles || []) {
    for (const d of availabilityDays(p)) s.add(d)
  }
  return Array.from(s)
})

const priceBounds = computed(() => {
  let min = Infinity, max = -Infinity
  for (const p of props.profiles || []) {
    const v = Number(p?.price_per_session)
    if (!Number.isFinite(v)) continue
    min = Math.min(min, v)
    max = Math.max(max, v)
  }
  if (!Number.isFinite(min)) min = 0
  if (!Number.isFinite(max)) max = 0
  return { min, max }
})

// initialize price filters if not set
onMounted(() => {
  if (filters.priceMin == null) filters.priceMin = priceBounds.value.min
  if (filters.priceMax == null) filters.priceMax = priceBounds.value.max
})

const drawerSideClass = computed(() => (locale.value === 'ar' ? 'right-0 translate-x-full' : 'left-0 -translate-x-full'))
const drawerRounded = computed(() => (locale.value === 'ar' ? 'rounded-l-lg' : 'rounded-r-lg'))

const filteredProfiles = computed(() => {
  const list = props.profiles || []
  return list.filter((p) => {
    // specialisations
    if (filters.specialisations.size) {
      const vals = specialisationsFor(p)
      const ok = Array.from(filters.specialisations).every((f) => vals.includes(f))
      if (!ok) return false
    }
    // expertises
    if (filters.expertises.size) {
      const vals = expertisesFor(p)
      const ok = Array.from(filters.expertises).every((f) => vals.includes(f))
      if (!ok) return false
    }
    // languages
    if (filters.languages.size) {
      const vals = languagesFor(p)
      const ok = Array.from(filters.languages).every((f) => vals.includes(f))
      if (!ok) return false
    }
    // days
    if (filters.days.size) {
      const vals = availabilityDays(p)
      const ok = Array.from(filters.days).every((f) => vals.includes(f))
      if (!ok) return false
    }
    // price
    const price = Number(p?.price_per_session)
    if (Number.isFinite(price)) {
      if (filters.priceMin != null && price < Number(filters.priceMin)) return false
      if (filters.priceMax != null && price > Number(filters.priceMax)) return false
    }
    return true
  })
})

function onSelectDown(id) {
  selectPressed.value = id
}

function onSelectUp() {
  selectPressed.value = null
}

function openReport(profile) {
  reportProfile.value = profile
  showReportModal.value = true
}

function onReportSent(data) {
  // optionally handle after-send actions
}

// Carousel / swipe state and handlers
const carousel = ref(null)
const isDragging = ref(false)
const showLeft = ref(false)
const showRight = ref(false)
const rtlScroll = ref(null)
const rtlFactor = ref(1)
let dragStartX = 0
let dragStartScroll = 0

function isRtlDocument() {
  return document?.documentElement?.getAttribute('dir') === 'rtl'
}

function detectRtlScrollEdges() {
  if (typeof document === 'undefined') return null

  const outer = document.createElement('div')
  outer.style.width = '100px'
  outer.style.height = '100px'
  outer.style.overflow = 'scroll'
  outer.style.direction = 'rtl'
  outer.style.visibility = 'hidden'
  outer.style.position = 'absolute'
  outer.style.top = '-9999px'

  const inner = document.createElement('div')
  inner.style.width = '200px'
  inner.style.height = '100px'
  outer.appendChild(inner)
  document.body.appendChild(outer)

  const max = Math.max(0, outer.scrollWidth - outer.clientWidth)
  const rightEdge = Number(outer.scrollLeft || 0)

  outer.scrollLeft = -999999
  const minVal = Number(outer.scrollLeft || 0)

  outer.scrollLeft = 999999
  const maxVal = Number(outer.scrollLeft || 0)

  document.body.removeChild(outer)

  // The opposite edge is the extreme farthest from the initial (right edge) value.
  const leftEdge = Math.abs(minVal - rightEdge) > Math.abs(maxVal - rightEdge) ? minVal : maxVal
  const span = Math.abs(leftEdge - rightEdge) || max
  const factor = Math.sign(rightEdge - leftEdge) || 1

  return { leftEdge, rightEdge, span, factor }
}

function getPhysicalEdgeDistances(el) {
  if (!el) return null
  const max = Math.max(0, (el.scrollWidth || 0) - (el.clientWidth || 0))
  const sl = Number(el.scrollLeft || 0)
  if (!isRtlDocument()) {
    const distLeft = Math.min(Math.max(sl, 0), max)
    return { max, distLeft, distRight: max - distLeft }
  }

  const edges = rtlScroll.value
  if (!edges) return null

  const span = Math.max(0, edges.span || max)
  const distLeft = Math.min(Math.max(Math.abs(sl - edges.leftEdge), 0), span)
  const distRight = Math.min(Math.max(Math.abs(sl - edges.rightEdge), 0), span)
  return { max: span, distLeft, distRight }
}

function scrollToLeftEdge() {
  const el = carousel.value
  if (!el) return

  if (!isRtlDocument()) {
    el.scrollLeft = 0
    return
  }

  if (rtlScroll.value) {
    el.scrollLeft = rtlScroll.value.leftEdge
  }
}

function onPointerDown(e) {
  isDragging.value = true
  dragStartX = e.type && String(e.type).startsWith('touch') ? (e.touches?.[0]?.pageX || 0) : (e.pageX || e.clientX || 0)
  dragStartScroll = carousel.value?.scrollLeft || 0
  if (carousel.value) carousel.value.classList.add('cursor-grabbing')
  // update arrow visibility when user begins dragging
  updateArrows()
}

function onPointerMove(e) {
  if (!isDragging.value || !carousel.value) return
  const x = e.type && String(e.type).startsWith('touch') ? (e.touches?.[0]?.pageX || 0) : (e.pageX || e.clientX || 0)
  const walk = x - dragStartX
  carousel.value.scrollLeft = dragStartScroll - walk
}

function onPointerUp() {
  isDragging.value = false
  if (carousel.value) carousel.value.classList.remove('cursor-grabbing')
  // ensure arrows update after drag completes
  setTimeout(updateArrows, 50)
}

function scrollByAmount(amount) {
  if (!carousel.value) return
  const el = carousel.value
  let left = amount

  if (isRtlDocument()) {
    // Translate “physical” movement (right = +, left = -) to actual scrollLeft.
    left = amount * (rtlFactor.value || 1)
  }

  el.scrollBy({ left, behavior: 'smooth' })
}

function scrollNext() {
  if (!carousel.value) return
  const w = carousel.value.clientWidth || 600
  scrollByAmount(Math.round(w * 0.75))
  setTimeout(updateArrows, 300)
}

function scrollPrev() {
  if (!carousel.value) return
  const w = carousel.value.clientWidth || 600
  scrollByAmount(-Math.round(w * 0.75))
  setTimeout(updateArrows, 300)
}

function updateArrows() {
  if (!carousel.value) return
  const edges = getPhysicalEdgeDistances(carousel.value)
  if (!edges) return

  const threshold = 24
  const atLeftEdge = edges.distLeft <= threshold
  const atRightEdge = edges.distRight <= threshold

  // Left arrow should only show if we can scroll further left.
  showLeft.value = !atLeftEdge
  // Right arrow should only show if we can scroll further right.
  showRight.value = !atRightEdge
}

function onKeydown(e) {
  if (e.key === 'ArrowRight') scrollNext()
  if (e.key === 'ArrowLeft') scrollPrev()
}

onMounted(async () => {
  rtlScroll.value = isRtlDocument() ? detectRtlScrollEdges() : null
  rtlFactor.value = rtlScroll.value?.factor || 1
  window.addEventListener('keydown', onKeydown)
  await nextTick()
  // Ensure we always start at the physical extreme left so the left arrow is hidden by default.
  scrollToLeftEdge()
  updateArrows()
  requestAnimationFrame(updateArrows)
  setTimeout(updateArrows, 60)
  if (carousel.value) carousel.value.addEventListener('scroll', updateArrows, { passive: true })
  window.addEventListener('resize', updateArrows)
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', onKeydown)
  if (carousel.value) carousel.value.removeEventListener('scroll', updateArrows)
  window.removeEventListener('resize', updateArrows)
})
</script>

<template>
  <Head :title="`${t('services.consultation.banner.title')} - AEnhance`" />

  <Navbar :canLogin="canLogin" :canRegister="canRegister" :authUser="authUser" />

  <!-- Banner Section -->
  <div class="relative w-full h-[180px] overflow-hidden">
    <div
      class="absolute inset-0 bg-cover bg-center"
      style="background-image: url('/storage/banners/banner1.jpg')"
    >
      <div class="absolute inset-0 bg-gradient-to-r from-[#5997ac]/80 to-[#e8b4b8]/80"></div>
    </div>

    <div class="relative h-full flex flex-col justify-center container mx-auto px-4 sm:px-6 lg:px-8">
      <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">
        {{ t("services.consultation.banner.title") }}
      </h1>
      <div class="flex items-center gap-2 text-white text-sm">
        <Link :href="route('home')" class="hover:underline">{{ t("services.consultation.banner.home") }}</Link>
        <span>»</span>
        <Link :href="route('services.index')" class="hover:underline">{{ t("services.consultation.banner.services") }}</Link>
        <span>»</span>
        <span>{{ t("services.consultation.banner.current") }}</span>
      </div>
    </div>
  </div>

  <!-- Content -->
  <div class="bg-gray-50 py-12 md:py-16">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="mb-8 flex items-start gap-4 w-full" :class="locale.value === 'ar' ? 'flex-row-reverse' : ''">
        <div class="flex-1 flex justify-start">
          <!-- Filter button (left for LTR, right for RTL) -->
          <button
            @click="toggleFilter"
            type="button"
            class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-white border border-gray-200 text-gray-700 text-sm font-semibold hover:bg-gray-50 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#5997ac]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 3H2l8 9v5l4 4v-9z" />
            </svg>
            {{ t('services.consultation.filter') || 'Filter' }}
          </button>
        </div>

        <div class="flex-1 flex justify-end">
          <Link
            :href="route('services.index')"
            class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-white border border-gray-200 text-gray-700 text-sm font-semibold hover:bg-gray-50 transition focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300"
          >
            {{ t("services.consultation.back") }}
          </Link>
        </div>
      </div>

      <div v-if="!profiles.length" class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 text-gray-700">
        {{ t("services.consultation.empty") }}
      </div>

      <div v-else class="relative">
        <!-- Left Arrow -->
        <button
          v-show="showLeft"
          @click.prevent="scrollPrev"
          aria-label="Previous"
          class="absolute left-2 top-1/2 -translate-y-1/2 z-30 w-10 h-10 rounded-full shadow-lg bg-white/95 text-[#5997ac] flex items-center justify-center hover:bg-[#5997ac] hover:text-white transition"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 18l-6-6 6-6" />
          </svg>
        </button>

        <!-- Right Arrow -->
        <button
          v-show="showRight"
          @click.prevent="scrollNext"
          aria-label="Next"
          class="absolute right-2 top-1/2 -translate-y-1/2 z-30 w-10 h-10 rounded-full shadow-lg bg-white/95 text-[#5997ac] flex items-center justify-center hover:bg-[#5997ac] hover:text-white transition"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 6l6 6-6 6" />
          </svg>
        </button>

        <!-- Horizontal scroll/carousel -->
        <div
          ref="carousel"
          tabindex="0"
          class="overflow-x-auto overflow-y-hidden whitespace-nowrap scroll-smooth snap-x snap-mandatory flex gap-6 py-4 px-4"
          style="-ms-overflow-style: none; scrollbar-width: none;"
          @mousedown="onPointerDown"
          @mousemove="onPointerMove"
          @mouseup="onPointerUp"
          @mouseleave="onPointerUp"
          @touchstart.passive="onPointerDown"
          @touchmove.passive="onPointerMove"
          @touchend.passive="onPointerUp"
        >
          <div
            v-for="profile in filteredProfiles"
            :key="profile.id"
            class="snap-start shrink-0 w-[320px] group bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden flex flex-col h-[560px]"
          >
            <div class="relative h-64 bg-gray-100 overflow-hidden">
              <button
                v-if="props.authUser && String(props.authUser.role || '').toUpperCase() === 'PATIENT'"
                @click.prevent="openReport(profile)"
                @mousedown.prevent="pressed = true"
                @mouseup="pressed = false"
                @mouseleave="pressed = false"
                @touchstart.prevent="pressed = true"
                @touchend="pressed = false"
                :class="[
                  'absolute top-1 right-1 w-6 h-6 flex items-center justify-center bg-white/95 rounded-full shadow text-red-600 z-20 transition-transform duration-150 ease-out focus:outline-none group',
                  'hover:scale-110 hover:-translate-y-0.5 active:scale-95 hover:bg-red-600 hover:text-white',
                  pressed ? 'scale-95 -rotate-6' : ''
                ]"
                title="Report psychologist"
              >
                <div class="relative flex items-center justify-center w-4 h-4">
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 group" viewBox="0 0 24 24" aria-hidden="true">
                    <path class="fill-current" d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    <rect x="10.5" y="7" width="3" height="7" rx="0.6" class="fill-white stroke-red-600 group-hover:fill-red-600 group-hover:stroke-white" stroke-width="0.9" />
                    <circle class="fill-white stroke-red-600 group-hover:fill-red-600 group-hover:stroke-white" cx="12" cy="16.5" r="1.4" stroke-width="0.9" />
                  </svg>
                </div>
              </button>

              <img
                v-if="avatarUrl(profile)"
                :src="avatarUrl(profile)"
                alt="Profile"
                class="h-full w-full object-cover bg-gray-100"
              />
              <div
                v-else
                class="h-full w-full flex items-center justify-center bg-gradient-to-br from-[#5997ac]/15 to-[#e8b4b8]/15"
              >
                <span class="text-3xl font-semibold text-gray-700">{{ initials(profile) }}</span>
              </div>

              <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-black/60 to-transparent"></div>

                <div class="absolute top-3 left-3 flex flex-wrap items-center gap-2">
                <span
                  v-if="profile.is_approved"
                  class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-white/95 text-[#5997ac] ring-1 ring-[#5997ac]/25 backdrop-blur"
                >
                  {{ t("services.consultation.verified") }}
                </span>
              </div>

              <div class="absolute bottom-3 left-4 right-4">
                <h3 class="text-lg font-semibold text-white leading-snug line-clamp-2">
                  {{ fullName(profile) }}
                </h3>
              </div>

              <!-- price badge at bottom-right of image -->
              <div class="absolute bottom-3 right-2 translate-x-1">
                <div class="inline-flex items-baseline gap-2 px-3 py-1 rounded-full bg-white/95 text-right shadow-lg">
                  <span class="text-sm font-semibold text-[#5997ac]">{{ formatPrice(profile.price_per_session).replace(' TND','') }}</span>
                  <span class="text-xs text-gray-500">TND</span>
                </div>
              </div>
            </div>

            <div class="p-5 flex-1 flex flex-col">
              <div v-if="specialisationsFor(profile).length" class="flex flex-wrap gap-2">
                <span
                  v-for="label in specialisationsFor(profile)"
                  :key="`spec-${profile.id}-${label}`"
                  class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-[#af5166]/10 text-[#af5166]"
                >
                  {{ label }}
                </span>
              </div>
              <div v-if="expertisesFor(profile).length" class="mt-2 flex flex-wrap gap-2">
                <span
                  v-for="label in expertisesFor(profile)"
                  :key="`exp-${profile.id}-${label}`"
                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-[rgba(175,81,102,0.06)] text-[#af5166]"
                >
                  {{ label }}
                </span>
              </div>
              <div v-else class="text-xs text-gray-500">
                {{ t("services.consultation.na") }}
              </div>

              <div v-if="languagesFor(profile).length" class="mt-3 flex flex-wrap gap-2">
                <span
                  v-for="label in languagesFor(profile)"
                  :key="`lang-body-${profile.id}-${label}`"
                  class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-[#5997ac]/10 text-[#5997ac]"
                >
                  {{ label }}
                </span>
              </div>

              <p class="mt-3 text-sm text-gray-700 leading-relaxed line-clamp-3">
                {{ profile.bio || t("services.consultation.noBio") }}
              </p>

              <!-- availability day chips in card body (above price/availability) -->
              <div v-if="availabilityDays(profile).length" class="mt-3 flex flex-wrap gap-2">
                <span
                  v-for="day in availabilityDays(profile)"
                  :key="`day-body-${profile.id}-${day}`"
                  class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 ring-1 ring-gray-200"
                >
                  {{ day }}
                </span>
              </div>

              <Link
                :href="selectHref(profile)"
                @mousedown.prevent="onSelectDown(profile.id)"
                @mouseup="onSelectUp"
                @mouseleave="onSelectUp"
                @touchstart.passive="onSelectDown(profile.id)"
                @touchend="onSelectUp"
                :class="[
                  'mt-auto inline-flex w-full items-center justify-center px-4 py-2 rounded-md bg-[#5997ac] text-white text-sm font-semibold transition-transform duration-150 ease-out',
                  selectPressed === profile.id ? 'scale-95 translate-y-0.5 shadow-sm' : 'hover:scale-105 hover:-translate-y-0.5 hover:shadow-lg'
                ]"
              >
                {{ t("services.consultation.select") }}
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <Footer />
  <ReportModal :show="showReportModal" :profile="reportProfile" :authUser="authUser" @close="showReportModal=false" @sent="onReportSent" />

  <!-- Filter slide-over -->
  <div v-if="filterOpen" class="fixed inset-0 z-30">
    <div @click="filterOpen=false" class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity"></div>

    <aside :class="['fixed top-0 h-full w-80 bg-white shadow-xl z-40 transform transition-transform p-0 overflow-hidden', locale.value === 'ar' ? 'left-0' : 'right-0', drawerRounded]">
      <div class="p-4 flex items-center justify-between border-b bg-white">
        <div class="flex items-center gap-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-[#5997ac]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 3H2l8 9v5l4 4v-9z" />
          </svg>
          <h3 class="text-sm font-semibold">{{ t('services.consultation.filtersTitle') || 'Filters' }}</h3>
        </div>
        <button @click="filterOpen=false" class="text-gray-500 hover:text-gray-700">✕</button>
      </div>

      <div class="p-4 overflow-y-auto h-full bg-white">
        <!-- Specialisations -->
        <div class="mb-4">
          <div class="flex items-center gap-2 mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#af5166]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 6L9 17l-5-5" />
            </svg>
            <div class="text-xs font-medium">{{ t('services.consultation.specialisations') || 'Specialisations' }}</div>
          </div>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="s in allSpecialisations"
              :key="s"
              @click.prevent="toggleSet(filters.specialisations, s)"
              :class="[filters.specialisations.has(s) ? 'bg-[#af5166]/10 text-[#af5166] ring-1 ring-[#af5166]/25' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50', 'px-3 py-1 rounded-full text-xs transition transform hover:scale-105']"
            >
              {{ s }}
            </button>
          </div>
        </div>

        <!-- Expertises -->
        <div class="mb-4">
          <div class="flex items-center gap-2 mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-[#5997ac]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2l2 7h7l-5.5 4 2 7L12 16l-5.5 4 2-7L3 9h7z" />
            </svg>
            <div class="text-xs font-medium">{{ t('services.consultation.expertises') || 'Expertises' }}</div>
          </div>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="e in allExpertises"
              :key="e"
              @click.prevent="toggleSet(filters.expertises, e)"
              :class="[filters.expertises.has(e) ? 'bg-[rgba(175,81,102,0.06)] text-[#af5166] ring-1 ring-[#af5166]/25' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50', 'px-3 py-1 rounded-full text-xs transition transform hover:scale-105']"
            >
              {{ e }}
            </button>
          </div>
        </div>

        <!-- Languages -->
        <div class="mb-4">
          <div class="flex items-center gap-2 mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2a10 10 0 100 20 10 10 0 000-20z" />
            </svg>
            <div class="text-xs font-medium">{{ t('services.consultation.languages') || 'Languages' }}</div>
          </div>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="l in allLanguages"
              :key="l"
              @click.prevent="toggleSet(filters.languages, l)"
              :class="[filters.languages.has(l) ? 'bg-[#5997ac]/10 text-[#5997ac] ring-1 ring-[#5997ac]/25' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50', 'px-3 py-1 rounded-full text-xs transition transform hover:scale-105']"
            >
              {{ l }}
            </button>
          </div>
        </div>

        <!-- Days -->
        <div class="mb-4">
          <div class="flex items-center gap-2 mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="4" width="18" height="18" rx="2" />
            </svg>
            <div class="text-xs font-medium">{{ t('services.consultation.days') || 'Days' }}</div>
          </div>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="d in allDays"
              :key="d"
              @click.prevent="toggleSet(filters.days, d)"
              :class="[filters.days.has(d) ? 'bg-gray-100 text-gray-700 ring-1 ring-gray-200' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50', 'px-3 py-1 rounded-full text-xs transition transform hover:scale-105']"
            >
              {{ d }}
            </button>
          </div>
        </div>

        <!-- Price -->
        <div class="mb-4">
          <div class="flex items-center gap-2 mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-yellow-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 1v22" />
              <path d="M17 5H7a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2z" />
            </svg>
            <div class="text-xs font-medium">{{ t('services.consultation.price') || 'Price (TND)' }}</div>
          </div>

          <div class="flex items-center gap-2 mb-2">
            <input type="number" class="w-1/2 px-2 py-1 border rounded" v-model.number="filters.priceMin" :min="priceBounds.min" :max="priceBounds.max" />
            <input type="number" class="w-1/2 px-2 py-1 border rounded" v-model.number="filters.priceMax" :min="priceBounds.min" :max="priceBounds.max" />
          </div>
          <div>
            <input type="range" :min="priceBounds.min" :max="priceBounds.max" v-model.number="filters.priceMax" class="w-full" />
          </div>
        </div>

        <div class="flex items-center gap-2 mt-4">
          <button @click.prevent="filterOpen=false" class="flex-1 px-4 py-2 bg-[#5997ac] text-white rounded-lg font-semibold">{{ t('services.consultation.apply') || 'Apply' }}</button>
          <button @click.prevent="resetFilters" class="px-4 py-2 bg-white border border-gray-200 rounded-lg">{{ t('services.consultation.clear') || 'Clear' }}</button>
        </div>
      </div>
    </aside>
  </div>
</template>
