<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { ref, onMounted, computed, onUnmounted } from "vue";
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/components/Footer.vue';



defineProps({
  canLogin: { type: Boolean },
  canRegister: { type: Boolean },
  authUser: { type: Object },
});

const showDropdown = ref(false);
const showAboutDropdown = ref(false);
const showMobileMenu = ref(false);
const { t, locale } = useI18n();
const currentLang = ref("");
const autoPlayInterval = ref(null);

// Carousel state
const currentSlide = ref(0);

// Carousel slides data
const slides = [
  {
    image: '/storage/home/aenhance-accessibility-13.jpg',
    titleKey: 'carousel.slide1.title',
    descriptionKey: 'carousel.slide1.description',
    buttonKey: 'carousel.slide1.button'
  },
  {
    image: '/storage/home/aenhance-privacy-31.jpg',
    titleKey: 'carousel.slide2.title',
    descriptionKey: 'carousel.slide2.description',
    buttonKey: 'carousel.slide2.button'
  },
  {
    image: '/storage/home/aenhance-quality-19.jpg',
    titleKey: 'carousel.slide3.title',
    descriptionKey: 'carousel.slide3.description',
    buttonKey: 'carousel.slide3.button'
  }
];

// Carousel functions
function nextSlide() {
  currentSlide.value = (currentSlide.value + 1) % slides.length;
}

function prevSlide() {
  currentSlide.value = (currentSlide.value - 1 + slides.length) % slides.length;
}

function goToSlide(index) {
  currentSlide.value = index;
  resetAutoPlay(); // Add this line
}

// Auto-play functions
function startAutoPlay() {
  autoPlayInterval.value = setInterval(() => {
    nextSlide();
  }, 5000); // Change to 3000 for 3 seconds, 7000 for 7 seconds, etc.
}

function stopAutoPlay() {
  if (autoPlayInterval.value) {
    clearInterval(autoPlayInterval.value);
    autoPlayInterval.value = null;
  }
}

function resetAutoPlay() {
  stopAutoPlay();
  startAutoPlay();
}

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
  startAutoPlay();
});

onUnmounted(() => {
  stopAutoPlay();
});

// About dropdown items reactive to language
const aboutItems = computed(() => [
  t("nav.aboutItems.0"),
  t("nav.aboutItems.1"),
  t("nav.aboutItems.2"),
  t("nav.aboutItems.3"),
  t("nav.aboutItems.4"),
  t("nav.aboutItems.5")
])

// Intersection Observer for animations
const telehealthSection = ref(null);
const valuesSection = ref(null);
const howItWorksSection = ref(null);
const supportSection = ref(null);
const joinTeamSection = ref(null);
const telehealthVisible = ref(false);
const valuesVisible = ref(false);
const howItWorksVisible = ref(false);
const supportVisible = ref(false);
const joinTeamVisible = ref(false);

onMounted(() => {
  const savedLang = localStorage.getItem("locale") || locale.value;
  setLang(savedLang);

  // Setup Intersection Observer with a slight delay to ensure DOM is ready
  setTimeout(() => {
    const observerOptions = {
      threshold: 0.2,
      rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          if (entry.target === telehealthSection.value) {
            telehealthVisible.value = true;
          } else if (entry.target === valuesSection.value) {
            valuesVisible.value = true;
          } else if (entry.target === howItWorksSection.value) {
            howItWorksVisible.value = true;
          } else if (entry.target === supportSection.value) {
            supportVisible.value = true;
          } else if (entry.target === joinTeamSection.value) {
            joinTeamVisible.value = true;
          }
        }
      });
    }, observerOptions);

    if (telehealthSection.value) {
      observer.observe(telehealthSection.value);
    }
    if (valuesSection.value) {
      observer.observe(valuesSection.value);
    }
    if (howItWorksSection.value) {
      observer.observe(howItWorksSection.value);
    }
    if (supportSection.value) {
      observer.observe(supportSection.value);
    }
    if (joinTeamSection.value) {
      observer.observe(joinTeamSection.value);
    }
  }, 100);
});
</script>

<template>
  <Head title="Telemental Health - AEfhance" />

    <Navbar 
    :canLogin="canLogin" 
    :canRegister="canRegister" 
    :authUser="authUser" 
  />

  <!-- Hero Carousel Section -->
  <div 
  class="relative w-full h-[350px] sm:h-[400px] md:h-[450px] lg:h-[480px] overflow-hidden bg-white"
  @mouseenter="stopAutoPlay"
  @mouseleave="startAutoPlay"
>
    <!-- Slides -->
    <div
      v-for="(slide, index) in slides"
      :key="index"
      class="absolute inset-0 transition-opacity duration-700"
      :class="currentSlide === index ? 'opacity-100' : 'opacity-0'"
    >
      <!-- Background Image with improved responsiveness -->
      <div
        class="absolute inset-0 bg-cover bg-center bg-no-repeat"
        :style="{ 
          backgroundImage: `url(${slide.image})`,
          backgroundSize: 'cover',
          backgroundPosition: 'center center'
        }"
      >
        <!-- Dark overlay for better text readability -->
        <div class="absolute inset-0 bg-black bg-opacity-40"></div>
      </div>

      <!-- Content -->
      <div class="relative h-full flex items-center hero-carousel-content">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
          <div class="max-w-xl lg:max-w-2xl text-white" :class="locale === 'ar' ? 'text-right' : ''">
            <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold mb-2 md:mb-3 drop-shadow-lg">{{ t(slide.titleKey) }}</h1>
            <p class="text-xs sm:text-sm md:text-base mb-3 md:mb-4 leading-relaxed drop-shadow-md">{{ t(slide.descriptionKey) }}</p>
            <button class="px-3 py-2 md:px-5 md:py-2.5 bg-[#af5166] hover:bg-[#8d3d4f] text-white text-xs md:text-sm rounded-lg transition flex items-center gap-2 shadow-lg" :class="locale === 'ar' ? 'flex-row-reverse' : ''">
              <svg class="w-3 h-3 md:w-4 md:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
              </svg>
              {{ t(slide.buttonKey) }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Left Arrow -->
    <button
      @click="prevSlide"
      class="absolute left-2 sm:left-4 md:left-6 top-1/2 -translate-y-1/2 z-10 text-white opacity-70 hover:opacity-100 transition-opacity"
    >
      <svg class="w-7 h-7 sm:w-8 sm:h-8 md:w-10 md:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
      </svg>
    </button>

    <!-- Right Arrow -->
    <button
      @click="nextSlide"
      class="absolute right-2 sm:right-4 md:right-6 top-1/2 -translate-y-1/2 z-10 text-white opacity-70 hover:opacity-100 transition-opacity"
    >
      <svg class="w-7 h-7 sm:w-8 sm:h-8 md:w-10 md:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
      </svg>
    </button>

    <!-- Indicators -->
    <div class="absolute bottom-3 md:bottom-4 left-1/2 -translate-x-1/2 flex gap-2 md:gap-3 z-10">
      <button
        v-for="(slide, index) in slides"
        :key="index"
        @click="goToSlide(index)"
        class="h-1 rounded-full transition-all duration-300"
        :class="currentSlide === index ? 'w-10 md:w-12 bg-white' : 'w-6 md:w-8 bg-white bg-opacity-50'"
      ></button>
    </div>
  </div>

  <!-- Mental Telehealth Section -->
  <section ref="telehealthSection" class="bg-gray-50 py-12 md:py-16 lg:py-20">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
        
        <!-- Mental Telehealth Content -->
        <div class="flex flex-col items-center">
          <h2 
            class="text-3xl md:text-4xl font-bold text-[#5997ac] mb-6 transition-all duration-1000"
            :class="telehealthVisible ? 'animate-slide-down opacity-100' : 'opacity-0 -translate-y-10'"
          >
            {{ t('sections.mentalTelehealth.title') }}
          </h2>
          <div class="max-w-xl text-left">
            <p 
              class="text-gray-700 text-sm md:text-base leading-relaxed mb-4 transition-all duration-1000 delay-200"
              :class="telehealthVisible ? 'animate-fade-in opacity-100' : 'opacity-0'"
            >
              {{ t('sections.mentalTelehealth.paragraph1') }}
            </p>
            <p 
              class="text-gray-700 text-sm md:text-base leading-relaxed mb-6 transition-all duration-1000 delay-300"
              :class="telehealthVisible ? 'animate-fade-in opacity-100' : 'opacity-0'"
            >
              {{ t('sections.mentalTelehealth.paragraph2') }}
            </p>
            <div class="flex justify-center">
              <button 
                class="px-5 py-2.5 bg-[#af5166] hover:bg-[#8d3d4f] text-white text-sm rounded-lg transition-all duration-1000 delay-500 flex items-center gap-2 hover:scale-105 hover:shadow-lg"
                :class="telehealthVisible ? 'animate-pop-in opacity-100' : 'opacity-0 scale-75'"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                {{ t('sections.mentalTelehealth.button') }}
              </button>
            </div>
          </div>
        </div>

        <!-- Mental Telehealth Image -->
        <div 
          class="flex justify-center transition-all duration-1000 delay-400"
          :class="telehealthVisible ? 'animate-slide-left opacity-100' : 'opacity-0 translate-x-10'"
        >
          <img src="/storage/home/aenhance-mental-telehealth-15.png" alt="Mental Telehealth" class="w-48 h-48 sm:w-52 sm:h-52 md:w-56 md:h-56 lg:w-64 lg:h-64 object-contain hover:scale-105 transition-transform duration-500" />
        </div>

      </div>
    </div>
  </section>

  <!-- AEnhance Values Section -->
  <section ref="valuesSection" class="bg-white py-12 md:py-16 lg:py-20">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
        
        <!-- AEnhance Logo Image -->
        <div 
          class="flex justify-center order-2 lg:order-1 transition-all duration-1000 delay-400"
          :class="valuesVisible ? 'animate-slide-right opacity-100' : 'opacity-0 -translate-x-10'"
        >
          <img src="/storage/aenhance.svg" alt="AEnhance Logo" class="w-48 h-48 sm:w-52 sm:h-52 md:w-56 md:h-56 lg:w-64 lg:h-64 object-contain hover:scale-105 transition-transform duration-500" />
        </div>

        <!-- AEnhance Values Content -->
        <div class="flex flex-col items-center order-1 lg:order-2">
          <h2 
            class="text-3xl md:text-4xl font-bold text-[#5997ac] mb-6 transition-all duration-1000"
            :class="valuesVisible ? 'animate-slide-down opacity-100' : 'opacity-0 -translate-y-10'"
          >
            {{ t('sections.aenhanceValues.title') }}
          </h2>
          <div class="max-w-xl space-y-4 text-left">
            <div 
              class="transition-all duration-1000 delay-200"
              :class="valuesVisible ? 'animate-fade-in opacity-100' : 'opacity-0'"
            >
              <p class="text-gray-700 text-sm md:text-base leading-relaxed">
                <strong class="text-gray-800 font-bold">{{ t('sections.aenhanceValues.accessibility.title') }}:</strong> {{ t('sections.aenhanceValues.accessibility.description') }}
              </p>
            </div>
            <div 
              class="transition-all duration-1000 delay-300"
              :class="valuesVisible ? 'animate-fade-in opacity-100' : 'opacity-0'"
            >
              <p class="text-gray-700 text-sm md:text-base leading-relaxed">
                <strong class="text-gray-800 font-bold">{{ t('sections.aenhanceValues.qualityCare.title') }}:</strong> {{ t('sections.aenhanceValues.qualityCare.description') }}
              </p>
            </div>
            <div 
              class="transition-all duration-1000 delay-400"
              :class="valuesVisible ? 'animate-fade-in opacity-100' : 'opacity-0'"
            >
              <p class="text-gray-700 text-sm md:text-base leading-relaxed">
                <strong class="text-gray-800 font-bold">{{ t('sections.aenhanceValues.privacy.title') }}:</strong> {{ t('sections.aenhanceValues.privacy.description') }}
              </p>
            </div>
            <div class="flex justify-center pt-2">
              <button 
                class="px-5 py-2.5 bg-[#af5166] hover:bg-[#8d3d4f] text-white text-sm rounded-lg transition-all duration-1000 delay-600 flex items-center gap-2 hover:scale-105 hover:shadow-lg"
                :class="valuesVisible ? 'animate-pop-in opacity-100' : 'opacity-0 scale-75'"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                {{ t('sections.aenhanceValues.button') }}
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- How Does It Work Section -->
   <section ref="howItWorksSection" class="relative bg-gradient-to-b from-gray-50 to-white py-12 md:py-16 lg:py-20 overflow-hidden">
    <!-- Background Image -->
    <div class="absolute inset-0 opacity-90" style="background-image: url('/storage/home/question_marks.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;"></div>
    
    <!-- Content Overlay -->
    
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Title -->
      <h2 
        class="text-3xl md:text-4xl font-bold text-center text-[#5997ac] mb-12 md:mb-16 transition-all duration-1000"
        :class="howItWorksVisible ? 'animate-slide-down opacity-100' : 'opacity-0 -translate-y-10'"
      >
        {{ t('sections.howItWorks.title') }}
      </h2>

      <!-- Steps Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 max-w-7xl mx-auto">
        
        <!-- Step 1: Create Account -->
        <div 
          class="bg-white rounded-lg p-6 border-4 border-[#5997ac] hover:border-[#af5166] transition-all duration-300 shadow-sm hover:shadow-lg group"
          :class="howItWorksVisible ? 'animate-fade-in-up opacity-100' : 'opacity-0 translate-y-10'"
          style="animation-delay: 0.1s"
        >
          <div class="flex flex-col items-center text-center h-full">
            <div class="text-[#5997ac] group-hover:text-[#af5166] transition-colors duration-300 mb-4 icon-rotate">
              <svg class="w-16 h-16 md:w-20 md:h-20" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                <path d="M18 8h2v2h-2zm0 4h2v2h-2z"/>
              </svg>
            </div>
            <h3 class="text-[#5997ac] font-bold text-base md:text-lg mb-3">
              {{ t('sections.howItWorks.steps.createAccount.title') }}
            </h3>
            <p class="text-gray-600 text-xs md:text-sm leading-relaxed flex-grow">
              {{ t('sections.howItWorks.steps.createAccount.description') }}
            </p>
            <button class="mt-4 px-4 py-2 bg-[#5997ac] hover:bg-[#af5166] text-white text-xs rounded transition-colors duration-300">
              {{ t('sections.howItWorks.steps.createAccount.button') }}
            </button>
          </div>
        </div>

        <!-- Step 2: Explore Therapists -->
        <div 
          class="bg-white rounded-lg p-6 border-4 border-[#5997ac] hover:border-[#af5166] transition-all duration-300 shadow-sm hover:shadow-lg group"
          :class="howItWorksVisible ? 'animate-fade-in-up opacity-100' : 'opacity-0 translate-y-10'"
          style="animation-delay: 0.2s"
        >
          <div class="flex flex-col items-center text-center h-full">
            <div class="text-[#5997ac] group-hover:text-[#af5166] transition-colors duration-300 mb-4 icon-rotate">
              <svg class="w-16 h-16 md:w-20 md:h-20" fill="currentColor" viewBox="0 0 24 24">
                <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                <rect x="2" y="2" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-dasharray="4 2" rx="2"/>
              </svg>
            </div>
            <h3 class="text-[#5997ac] font-bold text-base md:text-lg mb-3">
              {{ t('sections.howItWorks.steps.exploreTherapists.title') }}
            </h3>
            <p class="text-gray-600 text-xs md:text-sm leading-relaxed flex-grow">
              {{ t('sections.howItWorks.steps.exploreTherapists.description') }}
            </p>
          </div>
        </div>

        <!-- Step 3: Schedule Session -->
        <div 
          class="bg-white rounded-lg p-6 border-4 border-[#5997ac] hover:border-[#af5166] transition-all duration-300 shadow-sm hover:shadow-lg group"
          :class="howItWorksVisible ? 'animate-fade-in-up opacity-100' : 'opacity-0 translate-y-10'"
          style="animation-delay: 0.3s"
        >
          <div class="flex flex-col items-center text-center h-full">
            <div class="text-[#5997ac] group-hover:text-[#af5166] transition-colors duration-300 mb-4 icon-rotate">
              <svg class="w-16 h-16 md:w-20 md:h-20" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V10h14v10zM9 14H7v-2h2v2zm4 0h-2v-2h2v2zm4 0h-2v-2h2v2zm-8 4H7v-2h2v2zm4 0h-2v-2h2v2zm4 0h-2v-2h2v2z"/>
              </svg>
            </div>
            <h3 class="text-[#5997ac] font-bold text-base md:text-lg mb-3">
              {{ t('sections.howItWorks.steps.scheduleSession.title') }}
            </h3>
            <p class="text-gray-600 text-xs md:text-sm leading-relaxed flex-grow">
              {{ t('sections.howItWorks.steps.scheduleSession.description') }}
            </p>
          </div>
        </div>

        <!-- Step 4: Connect Virtually -->
        <div 
          class="bg-white rounded-lg p-6 border-4 border-[#5997ac] hover:border-[#af5166] transition-all duration-300 shadow-sm hover:shadow-lg group"
          :class="howItWorksVisible ? 'animate-fade-in-up opacity-100' : 'opacity-0 translate-y-10'"
          style="animation-delay: 0.4s"
        >
          <div class="flex flex-col items-center text-center h-full">
            <div class="text-[#5997ac] group-hover:text-[#af5166] transition-colors duration-300 mb-4 icon-rotate">
              <svg class="w-16 h-16 md:w-20 md:h-20" fill="currentColor" viewBox="0 0 24 24">
                <path d="M20 18c1.1 0 1.99-.9 1.99-2L22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2H0v2h24v-2h-4zM4 6h16v10H4V6z"/>
                <path d="M12 9c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-4 0c0 2.21 1.79 4 4 4s4-1.79 4-4h-2c0 1.1-.9 2-2 2s-2-.9-2-2H8z" opacity="0.5"/>
                <path d="M1 1l3 3m17-3l-3 3" stroke="currentColor" stroke-width="1.5"/>
              </svg>
            </div>
            <h3 class="text-[#5997ac] font-bold text-base md:text-lg mb-3">
              {{ t('sections.howItWorks.steps.connectVirtually.title') }}
            </h3>
            <p class="text-gray-600 text-xs md:text-sm leading-relaxed flex-grow">
              {{ t('sections.howItWorks.steps.connectVirtually.description') }}
            </p>
          </div>
        </div>

        <!-- Step 5: Start Journey -->
        <div 
          class="bg-white rounded-lg p-6 border-4 border-[#5997ac] hover:border-[#af5166] transition-all duration-300 shadow-sm hover:shadow-lg group"
          :class="howItWorksVisible ? 'animate-fade-in-up opacity-100' : 'opacity-0 translate-y-10'"
          style="animation-delay: 0.5s"
        >
          <div class="flex flex-col items-center text-center h-full">
            <div class="text-[#5997ac] group-hover:text-[#af5166] transition-colors duration-300 mb-4 icon-rotate">
              <svg class="w-16 h-16 md:w-20 md:h-20" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2L4.5 20.29l.71.71L12 18l6.79 3 .71-.71z"/>
                <path d="M12 2v16" stroke="white" stroke-width="1.5"/>
              </svg>
            </div>
            <h3 class="text-[#5997ac] font-bold text-base md:text-lg mb-3">
              {{ t('sections.howItWorks.steps.startJourney.title') }}
            </h3>
            <p class="text-gray-600 text-xs md:text-sm leading-relaxed flex-grow">
              {{ t('sections.howItWorks.steps.startJourney.description') }}
            </p>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- You Need Support Section -->
  <section ref="supportSection" class="bg-gray-50 py-12 md:py-16 lg:py-20">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
        
        <!-- You Need Support Content -->
        <div class="flex flex-col items-center">
          <h2 
            class="text-3xl md:text-4xl font-bold text-[#5997ac] mb-6 transition-all duration-1000"
            :class="supportVisible ? 'animate-slide-down opacity-100' : 'opacity-0 -translate-y-10'"
          >
            {{ t('sections.youNeedSupport.title') }}
          </h2>
          <div class="max-w-xl text-left">
            <p 
              class="text-gray-700 text-sm md:text-base leading-relaxed mb-4 transition-all duration-1000 delay-200"
              :class="supportVisible ? 'animate-fade-in opacity-100' : 'opacity-0'"
            >
              {{ t('sections.youNeedSupport.paragraph1') }}
            </p>
            <p 
              class="text-gray-700 text-sm md:text-base leading-relaxed mb-6 transition-all duration-1000 delay-300"
              :class="supportVisible ? 'animate-fade-in opacity-100' : 'opacity-0'"
            >
              {{ t('sections.youNeedSupport.paragraph2') }}
            </p>
            <div class="flex justify-center">
              <button 
                class="px-5 py-2.5 bg-[#af5166] hover:bg-[#8d3d4f] text-white text-sm rounded-lg transition-all duration-1000 delay-500 flex items-center gap-2 hover:scale-105 hover:shadow-lg"
                :class="supportVisible ? 'animate-pop-in opacity-100' : 'opacity-0 scale-75'"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                {{ t('sections.youNeedSupport.button') }}
              </button>
            </div>
          </div>
        </div>

        <!-- Patient Image -->
        <div 
          class="flex justify-center transition-all duration-1000 delay-400"
          :class="supportVisible ? 'animate-slide-left opacity-100' : 'opacity-0 translate-x-10'"
        >
          <img src="/storage/home/aenhance-patient-88.png" alt="You Need Support" class="w-48 h-48 sm:w-52 sm:h-52 md:w-56 md:h-56 lg:w-64 lg:h-64 object-contain hover:scale-105 transition-transform duration-500" />
        </div>

      </div>
    </div>
  </section>

  <!-- Join Our Team Section -->
  <section ref="joinTeamSection" class="bg-white py-12 md:py-16 lg:py-20">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center">
        
        <!-- Psychologist Image -->
        <div 
          class="flex justify-center order-2 lg:order-1 transition-all duration-1000 delay-400"
          :class="joinTeamVisible ? 'animate-slide-right opacity-100' : 'opacity-0 -translate-x-10'"
        >
          <img src="/storage/home/aenhance-psychologues-85.png" alt="Join Our Team" class="w-48 h-48 sm:w-52 sm:h-52 md:w-56 md:h-56 lg:w-64 lg:h-64 object-contain hover:scale-105 transition-transform duration-500" />
        </div>

        <!-- Join Our Team Content -->
        <div class="flex flex-col items-center order-1 lg:order-2">
          <h2 
            class="text-3xl md:text-4xl font-bold text-[#5997ac] mb-6 transition-all duration-1000"
            :class="joinTeamVisible ? 'animate-slide-down opacity-100' : 'opacity-0 -translate-y-10'"
          >
            {{ t('sections.joinOurTeam.title') }}
          </h2>
          <div class="max-w-xl text-left">
            <p 
              class="text-gray-700 text-sm md:text-base leading-relaxed mb-4 transition-all duration-1000 delay-200"
              :class="joinTeamVisible ? 'animate-fade-in opacity-100' : 'opacity-0'"
            >
              {{ t('sections.joinOurTeam.paragraph1') }}
            </p>
            <p 
              class="text-gray-700 text-sm md:text-base leading-relaxed mb-6 transition-all duration-1000 delay-300"
              :class="joinTeamVisible ? 'animate-fade-in opacity-100' : 'opacity-0'"
            >
              {{ t('sections.joinOurTeam.paragraph2') }}
            </p>
            <div class="flex justify-center">
              <button 
                class="px-5 py-2.5 bg-[#af5166] hover:bg-[#8d3d4f] text-white text-sm rounded-lg transition-all duration-1000 delay-500 flex items-center gap-2 hover:scale-105 hover:shadow-lg"
                :class="joinTeamVisible ? 'animate-pop-in opacity-100' : 'opacity-0 scale-75'"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                {{ t('sections.joinOurTeam.button') }}
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>
  <Footer />
</template>

<style>
.gradient-header {
  background: linear-gradient(90deg, #7ba7bc 0%, #e8b4b8 100%);
}
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

/* Keep carousel content on the left even in RTL */
[dir="rtl"] .hero-carousel-content {
  direction: ltr;
}

/* Ensure proper Arabic text rendering */
[lang="ar"] {
  font-family: 'Segoe UI', 'Arial', sans-serif;
}

[dir="rtl"] [lang="ar"] {
  text-align: right;
}

/* Animation Keyframes */
@keyframes slide-down {
  from {
    opacity: 0;
    transform: translateY(-30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slide-left {
  from {
    opacity: 0;
    transform: translateX(30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes slide-right {
  from {
    opacity: 0;
    transform: translateX(-30px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes fade-in {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes pop-in {
  0% {
    opacity: 0;
    transform: scale(0.5);
  }
  60% {
    transform: scale(1.1);
  }
  100% {
    opacity: 1;
    transform: scale(1);
  }
}

/* Animation Classes */
.animate-slide-down {
  animation: slide-down 0.8s ease-out forwards;
}

.animate-slide-left {
  animation: slide-left 0.8s ease-out forwards;
}

.animate-slide-right {
  animation: slide-right 0.8s ease-out forwards;
}

.animate-fade-in {
  animation: fade-in 0.8s ease-out forwards;
}

.animate-pop-in {
  animation: pop-in 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
}

/* Delay Classes */
.delay-200 {
  animation-delay: 0.2s;
}

.delay-300 {
  animation-delay: 0.3s;
}

.delay-400 {
  animation-delay: 0.4s;
}

.delay-500 {
  animation-delay: 0.5s;
}

.delay-600 {
  animation-delay: 0.6s;
}

/* Icon Rotation on Hover */
.icon-rotate svg {
  transition: transform 0.5s ease-in-out;
}

.group:hover .icon-rotate svg {
  transform: rotate(360deg);
}

/* Fade in Up Animation */
@keyframes fade-in-up {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in-up {
  animation: fade-in-up 0.8s ease-out forwards;
}
</style>