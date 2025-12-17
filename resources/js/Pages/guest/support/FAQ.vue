<script setup>
import { Head } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { ref, onMounted } from "vue";
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';

defineProps({
  canLogin: { type: Boolean },
  canRegister: { type: Boolean },
  authUser: { type: Object },
});

const { t, locale } = useI18n();
const openAccordion = ref(null);

// Toggle accordion
function toggleAccordion(index) {
  if (openAccordion.value === index) {
    openAccordion.value = null;
  } else {
    openAccordion.value = index;
  }
}

// Set language
function setLang(lang) {
  locale.value = lang;
  localStorage.setItem("locale", lang);
  
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

// FAQ items count (adjust based on your actual questions)
const faqCount = 7;
</script>

<template>
  <Head title="FAQ - AEnhance" />

  <Navbar 
    :canLogin="canLogin" 
    :canRegister="canRegister" 
    :authUser="authUser" 
  />

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
        {{ t('faq.banner.title') }}
      </h1>
      <div class="flex items-center gap-2 text-white text-sm">
        <a href="/" class="hover:underline">{{ t('faq.banner.home') }}</a>
        <span>»</span>
        <span>{{ t('faq.banner.support') }}</span>
        <span>»</span>
        <span>{{ t('faq.banner.current') }}</span>
      </div>
    </div>
  </div>

  <!-- Main Content Section -->
  <div class="bg-gray-50 py-12 md:py-16">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <!-- Main Content Area (Left - 3 columns) -->
        <div class="lg:col-span-3">
          <div class="bg-white rounded-lg shadow-sm p-6 md:p-8">
            
            <!-- FAQ Accordion -->
            <div class="space-y-4">
              
              <!-- FAQ Item 1 -->
              <div class="border-b border-gray-200">
                <button
                  @click="toggleAccordion(1)"
                  class="w-full flex items-start justify-between py-4 text-left hover:text-[#5997ac] transition-colors group"
                  :class="locale === 'ar' ? 'flex-row-reverse' : ''"
                >
                  <span class="text-[#5997ac] font-semibold text-base md:text-lg pr-4" :class="locale === 'ar' ? 'pr-0 pl-4' : ''">
                    {{ t('faq.questions.q1.question') }}
                  </span>
                  <svg 
                    class="w-5 h-5 text-[#af5166] transform transition-transform duration-300 flex-shrink-0"
                    :class="openAccordion === 1 ? 'rotate-180' : ''"
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </button>
                <div 
                  class="overflow-hidden transition-all duration-300"
                  :style="openAccordion === 1 ? 'max-height: 1000px; opacity: 1;' : 'max-height: 0; opacity: 0;'"
                >
                  <div class="pb-4 text-gray-700 text-sm md:text-base leading-relaxed space-y-3">
                    <p>{{ t('faq.questions.q1.answer.p1') }}</p>
                    <p>{{ t('faq.questions.q1.answer.p2') }}</p>
                    <p>{{ t('faq.questions.q1.answer.p3') }}</p>
                  </div>
                </div>
              </div>

              <!-- FAQ Item 2 -->
              <div class="border-b border-gray-200">
                <button
                  @click="toggleAccordion(2)"
                  class="w-full flex items-start justify-between py-4 text-left hover:text-[#5997ac] transition-colors group"
                  :class="locale === 'ar' ? 'flex-row-reverse' : ''"
                >
                  <span class="text-[#5997ac] font-semibold text-base md:text-lg pr-4" :class="locale === 'ar' ? 'pr-0 pl-4' : ''">
                    {{ t('faq.questions.q2.question') }}
                  </span>
                  <svg 
                    class="w-5 h-5 text-[#af5166] transform transition-transform duration-300 flex-shrink-0"
                    :class="openAccordion === 2 ? 'rotate-180' : ''"
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </button>
                <div 
                  class="overflow-hidden transition-all duration-300"
                  :style="openAccordion === 2 ? 'max-height: 1000px; opacity: 1;' : 'max-height: 0; opacity: 0;'"
                >
                  <div class="pb-4 text-gray-700 text-sm md:text-base leading-relaxed">
                    <p>{{ t('faq.questions.q2.answer') }}</p>
                  </div>
                </div>
              </div>

              <!-- FAQ Item 3 -->
              <div class="border-b border-gray-200">
                <button
                  @click="toggleAccordion(3)"
                  class="w-full flex items-start justify-between py-4 text-left hover:text-[#5997ac] transition-colors group"
                  :class="locale === 'ar' ? 'flex-row-reverse' : ''"
                >
                  <span class="text-[#5997ac] font-semibold text-base md:text-lg pr-4" :class="locale === 'ar' ? 'pr-0 pl-4' : ''">
                    {{ t('faq.questions.q3.question') }}
                  </span>
                  <svg 
                    class="w-5 h-5 text-[#af5166] transform transition-transform duration-300 flex-shrink-0"
                    :class="openAccordion === 3 ? 'rotate-180' : ''"
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </button>
                <div 
                  class="overflow-hidden transition-all duration-300"
                  :style="openAccordion === 3 ? 'max-height: 1000px; opacity: 1;' : 'max-height: 0; opacity: 0;'"
                >
                  <div class="pb-4 text-gray-700 text-sm md:text-base leading-relaxed">
                    <p>{{ t('faq.questions.q3.answer') }}</p>
                  </div>
                </div>
              </div>

              <!-- FAQ Item 4 -->
              <div class="border-b border-gray-200">
                <button
                  @click="toggleAccordion(4)"
                  class="w-full flex items-start justify-between py-4 text-left hover:text-[#5997ac] transition-colors group"
                  :class="locale === 'ar' ? 'flex-row-reverse' : ''"
                >
                  <span class="text-[#5997ac] font-semibold text-base md:text-lg pr-4" :class="locale === 'ar' ? 'pr-0 pl-4' : ''">
                    {{ t('faq.questions.q4.question') }}
                  </span>
                  <svg 
                    class="w-5 h-5 text-[#af5166] transform transition-transform duration-300 flex-shrink-0"
                    :class="openAccordion === 4 ? 'rotate-180' : ''"
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </button>
                <div 
                  class="overflow-hidden transition-all duration-300"
                  :style="openAccordion === 4 ? 'max-height: 1000px; opacity: 1;' : 'max-height: 0; opacity: 0;'"
                >
                  <div class="pb-4 text-gray-700 text-sm md:text-base leading-relaxed space-y-3">
                    <p class="font-semibold">{{ t('faq.questions.q4.answer.intro') }}</p>
                    <ol class="list-decimal list-inside space-y-2 ml-4">
                      <li>{{ t('faq.questions.q4.answer.step1') }}</li>
                      <li>{{ t('faq.questions.q4.answer.step2') }}</li>
                      <li>{{ t('faq.questions.q4.answer.step3') }}</li>
                      <li>{{ t('faq.questions.q4.answer.step4') }}</li>
                    </ol>
                  </div>
                </div>
              </div>

              <!-- FAQ Item 5 -->
              <div class="border-b border-gray-200">
                <button
                  @click="toggleAccordion(5)"
                  class="w-full flex items-start justify-between py-4 text-left hover:text-[#5997ac] transition-colors group"
                  :class="locale === 'ar' ? 'flex-row-reverse' : ''"
                >
                  <span class="text-[#5997ac] font-semibold text-base md:text-lg pr-4" :class="locale === 'ar' ? 'pr-0 pl-4' : ''">
                    {{ t('faq.questions.q5.question') }}
                  </span>
                  <svg 
                    class="w-5 h-5 text-[#af5166] transform transition-transform duration-300 flex-shrink-0"
                    :class="openAccordion === 5 ? 'rotate-180' : ''"
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </button>
                <div 
                  class="overflow-hidden transition-all duration-300"
                  :style="openAccordion === 5 ? 'max-height: 1000px; opacity: 1;' : 'max-height: 0; opacity: 0;'"
                >
                  <div class="pb-4 text-gray-700 text-sm md:text-base leading-relaxed">
                    <p>{{ t('faq.questions.q5.answer') }}</p>
                  </div>
                </div>
              </div>

              <!-- FAQ Item 6 -->
              <div class="border-b border-gray-200">
                <button
                  @click="toggleAccordion(6)"
                  class="w-full flex items-start justify-between py-4 text-left hover:text-[#5997ac] transition-colors group"
                  :class="locale === 'ar' ? 'flex-row-reverse' : ''"
                >
                  <span class="text-[#5997ac] font-semibold text-base md:text-lg pr-4" :class="locale === 'ar' ? 'pr-0 pl-4' : ''">
                    {{ t('faq.questions.q6.question') }}
                  </span>
                  <svg 
                    class="w-5 h-5 text-[#af5166] transform transition-transform duration-300 flex-shrink-0"
                    :class="openAccordion === 6 ? 'rotate-180' : ''"
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </button>
                <div 
                  class="overflow-hidden transition-all duration-300"
                  :style="openAccordion === 6 ? 'max-height: 1000px; opacity: 1;' : 'max-height: 0; opacity: 0;'"
                >
                  <div class="pb-4 text-gray-700 text-sm md:text-base leading-relaxed space-y-3">
                    <p class="font-semibold">{{ t('faq.questions.q6.answer.intro') }}</p>
                    <ol class="list-decimal list-inside space-y-2 ml-4">
                      <li>{{ t('faq.questions.q6.answer.step1') }}</li>
                      <li>{{ t('faq.questions.q6.answer.step2') }}</li>
                      <li>{{ t('faq.questions.q6.answer.step3') }}</li>
                      <li>{{ t('faq.questions.q6.answer.step4') }}</li>
                    </ol>
                  </div>
                </div>
              </div>

              <!-- FAQ Item 7 -->
              <div class="border-b border-gray-200">
                <button
                  @click="toggleAccordion(7)"
                  class="w-full flex items-start justify-between py-4 text-left hover:text-[#5997ac] transition-colors group"
                  :class="locale === 'ar' ? 'flex-row-reverse' : ''"
                >
                  <span class="text-[#5997ac] font-semibold text-base md:text-lg pr-4" :class="locale === 'ar' ? 'pr-0 pl-4' : ''">
                    {{ t('faq.questions.q7.question') }}
                  </span>
                  <svg 
                    class="w-5 h-5 text-[#af5166] transform transition-transform duration-300 flex-shrink-0"
                    :class="openAccordion === 7 ? 'rotate-180' : ''"
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                  >
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                  </svg>
                </button>
                <div 
                  class="overflow-hidden transition-all duration-300"
                  :style="openAccordion === 7 ? 'max-height: 1000px; opacity: 1;' : 'max-height: 0; opacity: 0;'"
                >
                  <div class="pb-4 text-gray-700 text-sm md:text-base leading-relaxed">
                    <p>{{ t('faq.questions.q7.answer') }}</p>
                  </div>
                </div>
              </div>

            </div>

          </div>
        </div>

        <!-- Sidebar (Right - 1 column) -->
        <div class="lg:col-span-1">
          
          <!-- Support Section -->
          <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h3 class="text-xl font-bold text-[#5997ac] mb-4 pb-3 border-b-2 border-[#5997ac]">
              {{ t('faq.sidebar.support.title') }}
            </h3>
            <ul class="space-y-2">
              <li>
                <a href="#" class="flex items-center gap-2 text-[#af5166] font-semibold py-2 transition-colors">
                  <span class="text-[#af5166]">›</span>
                  {{ t('faq.sidebar.support.items.faq') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('faq.sidebar.support.items.howItWorks') }}
                </a>
              </li>
            </ul>
          </div>

          <!-- Main Navigation Section -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-xl font-bold text-[#5997ac] mb-4 pb-3 border-b-2 border-[#5997ac]">
              {{ t('faq.sidebar.navigation.title') }}
            </h3>
            <ul class="space-y-2">
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('faq.sidebar.navigation.items.about') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('faq.sidebar.navigation.items.ourServices') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-[#af5166] font-semibold py-2 transition-colors">
                  <span class="text-[#af5166]">›</span>
                  {{ t('faq.sidebar.navigation.items.support') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('faq.sidebar.navigation.items.resources') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('faq.sidebar.navigation.items.blog') }}
                </a>
              </li>
            </ul>
          </div>

        </div>

      </div>
    </div>
  </div>

  <Footer />
</template>

<style scoped>
/* RTL Support */
[dir="rtl"] {
  text-align: right;
}

[dir="rtl"] .flex-row-reverse {
  flex-direction: row-reverse;
}

/* Smooth transitions */
.overflow-hidden {
  transition: max-height 0.3s ease-in-out, opacity 0.3s ease-in-out;
}
</style>