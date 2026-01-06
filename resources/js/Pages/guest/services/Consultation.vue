<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { computed, onMounted } from "vue";
import Navbar from "@/Components/Navbar.vue";
import Footer from "@/Components/Footer.vue";

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
      <div class="flex items-start justify-between gap-4 mb-8">
        <div>
          <h2 class="text-2xl md:text-3xl font-bold text-[#5997ac]">
            {{ t("services.consultation.title") }}
          </h2>
          <p class="mt-2 text-gray-700 max-w-3xl">
            {{ t("services.consultation.subtitle") }}
          </p>
        </div>

        <Link
          :href="route('services.index')"
          class="hidden sm:inline-flex items-center justify-center px-4 py-2 rounded-md bg-white border border-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-100 transition"
        >
          {{ t("services.consultation.back") }}
        </Link>
      </div>

      <div v-if="!profiles.length" class="bg-white border border-gray-200 rounded-lg shadow-sm p-6 text-gray-700">
        {{ t("services.consultation.empty") }}
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="profile in profiles"
          :key="profile.id"
          class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden"
        >
          <div class="p-6">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0">
                <div class="h-14 w-14 rounded-full overflow-hidden ring-2 ring-[#5997ac]/20 bg-gray-100 flex items-center justify-center">
                  <img
                    v-if="avatarUrl(profile)"
                    :src="avatarUrl(profile)"
                    alt="Profile"
                    class="h-full w-full object-cover"
                  />
                  <span v-else class="text-sm font-semibold text-gray-700">{{ initials(profile) }}</span>
                </div>
              </div>

              <div class="min-w-0 flex-1">
                <div class="flex items-start justify-between gap-2">
                  <h3 class="text-base font-semibold text-gray-900 truncate">
                    {{ fullName(profile) }}
                  </h3>
                  <span
                    v-if="profile.is_approved"
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-[#5997ac]/10 text-[#5997ac]"
                  >
                    {{ t("services.consultation.verified") }}
                  </span>
                </div>

                <p class="mt-1 text-sm text-gray-700">
                  <span class="font-medium">{{ t("services.consultation.specialization") }}:</span>
                  <span>{{ (profile.specialisations || []).map(s => s.name).join(', ') || t("services.consultation.na") }}</span>
                </p>

                <p class="mt-1 text-sm text-gray-700" v-if="profile.city || profile.country">
                  <span class="font-medium">{{ t("services.consultation.location") }}:</span>
                  <span>{{ [profile.city, profile.country].filter(Boolean).join(", ") }}</span>
                </p>

                <p class="mt-1 text-sm text-gray-700" v-if="profile.gender">
                  <span class="font-medium">{{ t("services.consultation.gender") }}:</span>
                  <span>{{ profile.gender }}</span>
                </p>
              </div>
            </div>

            <div class="mt-4">
              <p class="text-sm text-gray-700 leading-relaxed line-clamp-4">
                {{ profile.bio || t("services.consultation.noBio") }}
              </p>
            </div>

            <div class="mt-4 flex items-center justify-between gap-3">
              <div class="text-sm">
                <span class="text-gray-500">{{ t("services.consultation.price") }}</span>
                <div class="font-semibold text-gray-900">{{ formatPrice(profile.price_per_session) }}</div>
              </div>

              <div class="text-right">
                <div class="text-xs text-gray-500">{{ t("services.consultation.availability") }}</div>
                <div v-if="availabilityLines(profile).length" class="text-xs text-gray-700 space-y-0.5">
                  <div v-for="line in availabilityLines(profile)" :key="line">{{ line }}</div>
                </div>
                <div v-else class="text-xs text-gray-600">{{ t("services.consultation.availabilityUnknown") }}</div>
              </div>
            </div>
          </div>

          <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            <div class="flex items-center justify-between gap-3">
              <span class="text-xs text-gray-600 truncate">
                {{ profile.user?.email || "" }}
              </span>
              <Link
                :href="route('services.index')"
                class="inline-flex items-center justify-center px-3 py-1.5 rounded-md bg-white border border-gray-200 text-gray-700 text-xs font-medium hover:bg-gray-100 transition"
              >
                {{ t("services.consultation.learnMore") }}
              </Link>
            </div>
          </div>
        </div>
      </div>

      <div class="sm:hidden mt-8">
        <Link
          :href="route('services.index')"
          class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-white border border-gray-200 text-gray-700 text-sm font-medium hover:bg-gray-100 transition"
        >
          {{ t("services.consultation.back") }}
        </Link>
      </div>
    </div>
  </div>

  <Footer />
</template>
