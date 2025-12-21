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

    <div class="flex gap-2.5">
      <Link v-if="!authUser && canLogin" :href="route('login')"
        class="px-3.5 py-1.5 bg-[#5997ac] text-white text-[12px] rounded flex items-center gap-1.5 justify-center hover:bg-[#467891] transition">
        🔓 {{ t("login") }}
      </Link>
      <Link v-if="!authUser && canRegister" :href="route('register')"
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
          <li><a href="#" class="px-3 py-1 text-gray-700 font-medium hover:bg-gray-100 transition">{{ t('nav.services') }}</a></li>
          
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
          <li><a href="#" class="block px-3 py-2 text-gray-700 font-medium hover:bg-gray-100 transition">{{ t('nav.services') }}</a></li>
          
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
import { Link } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { ref, onMounted, computed } from "vue";

const props = defineProps({
  canLogin: { type: Boolean },
  canRegister: { type: Boolean },
  authUser: { type: Object },
});

const showDropdown = ref(false);
const showAboutDropdown = ref(false);
const showSupportDropdown = ref(false);
const showMobileMenu = ref(false);
const { t, locale } = useI18n();
const currentLang = ref("");

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