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

// Intersection Observer for animations
const missionSection = ref(null);
const visionSection = ref(null);
const valuesSection = ref(null);
const objectivesSection = ref(null);

const missionVisible = ref(false);
const visionVisible = ref(false);
const valuesVisible = ref(false);
const objectivesVisible = ref(false);

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

onMounted(() => {
  const savedLang = localStorage.getItem("locale") || locale.value;
  setLang(savedLang);

  // Setup Intersection Observer
  setTimeout(() => {
    const observerOptions = {
      threshold: 0.2,
      rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          if (entry.target === missionSection.value) {
            missionVisible.value = true;
          } else if (entry.target === visionSection.value) {
            visionVisible.value = true;
          } else if (entry.target === valuesSection.value) {
            valuesVisible.value = true;
          } else if (entry.target === objectivesSection.value) {
            objectivesVisible.value = true;
          }
        }
      });
    }, observerOptions);

    if (missionSection.value) observer.observe(missionSection.value);
    if (visionSection.value) observer.observe(visionSection.value);
    if (valuesSection.value) observer.observe(valuesSection.value);
    if (objectivesSection.value) observer.observe(objectivesSection.value);
  }, 100);
});
</script>

<template>
  <Head title="Who We Are - AEnhance" />

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
        {{ t('whoWeAre.banner.title') }}
      </h1>
      <div class="flex items-center gap-2 text-white text-sm">
        <a href="/" class="hover:underline">{{ t('whoWeAre.banner.home') }}</a>
        <span>»</span>
        <span>{{ t('whoWeAre.banner.about') }}</span>
        <span>»</span>
        <span>{{ t('whoWeAre.banner.current') }}</span>
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
            
            <!-- Introduction Section -->
            <section class="mb-8">
              <h2 class="text-2xl md:text-3xl font-bold text-[#5997ac] mb-4">
                {{ t('whoWeAre.introduction.title') }}
              </h2>
              <p class="text-gray-700 leading-relaxed mb-4">
                {{ t('whoWeAre.introduction.paragraph1') }}
              </p>
              <p class="text-gray-700 leading-relaxed">
                {{ t('whoWeAre.introduction.paragraph2') }}
              </p>
            </section>

            <!-- Mission Section -->
            <section ref="missionSection" class="mb-8">
              <h2 
                class="text-2xl md:text-3xl font-bold text-[#5997ac] mb-6 transition-all duration-1000"
                :class="missionVisible ? 'animate-slide-down opacity-100' : 'opacity-0 -translate-y-10'"
              >
                {{ t('whoWeAre.mission.title') }}
              </h2>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                <!-- Image -->
                <div 
                  class="flex justify-center transition-all duration-1000"
                  :class="missionVisible ? 'animate-slide-right opacity-100' : 'opacity-0 -translate-x-10'"
                >
                  <img 
                    src="/storage/home/Positive thinking-bro.png" 
                    alt="Mission" 
                    class="w-full max-w-sm h-auto"
                  />
                </div>

                <!-- Text Content -->
                <div>
                  <p 
                    class="text-gray-700 text-base leading-relaxed mb-4 transition-all duration-1000 delay-200"
                    :class="missionVisible ? 'animate-fade-in opacity-100' : 'opacity-0'"
                  >
                    {{ t('whoWeAre.mission.paragraph1') }}
                  </p>
                  <p 
                    class="text-gray-700 text-base leading-relaxed transition-all duration-1000 delay-300"
                    :class="missionVisible ? 'animate-fade-in opacity-100' : 'opacity-0'"
                  >
                    {{ t('whoWeAre.mission.paragraph2') }}
                  </p>
                </div>
              </div>
            </section>

            <!-- Vision Section -->
            <section ref="visionSection" class="mb-8">
              <h2 
                class="text-2xl md:text-3xl font-bold text-[#5997ac] mb-6 transition-all duration-1000"
                :class="visionVisible ? 'animate-slide-down opacity-100' : 'opacity-0 -translate-y-10'"
              >
                {{ t('whoWeAre.vision.title') }}
              </h2>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                <!-- Text Content -->
                <div>
                  <p 
                    class="text-gray-700 text-base leading-relaxed mb-4 transition-all duration-1000 delay-200"
                    :class="visionVisible ? 'animate-fade-in opacity-100' : 'opacity-0'"
                  >
                    {{ t('whoWeAre.vision.paragraph1') }}
                  </p>
                  <p 
                    class="text-gray-700 text-base leading-relaxed transition-all duration-1000 delay-300"
                    :class="visionVisible ? 'animate-fade-in opacity-100' : 'opacity-0'"
                  >
                    {{ t('whoWeAre.vision.paragraph2') }}
                  </p>
                </div>

                <!-- Image -->
                <div 
                  class="flex justify-center transition-all duration-1000 delay-400"
                  :class="visionVisible ? 'animate-slide-left opacity-100' : 'opacity-0 translate-x-10'"
                >
                  <img 
                    src="/storage/home/Personal goals-pana.png" 
                    alt="Vision" 
                    class="w-full max-w-sm h-auto"
                  />
                </div>
              </div>
            </section>

            <!-- Core Values Section -->
            <section ref="valuesSection" class="mb-8">
              <h2 
                class="text-2xl md:text-3xl font-bold text-[#5997ac] mb-6 transition-all duration-1000"
                :class="valuesVisible ? 'animate-slide-down opacity-100' : 'opacity-0 -translate-y-10'"
              >
                {{ t('whoWeAre.values.title') }}
              </h2>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Accessibility Value -->
                <div 
                  class="bg-gray-50 rounded-lg p-5 hover:shadow-md transition-all duration-300 border-l-4 border-[#5997ac]"
                  :class="valuesVisible ? 'animate-fade-in-up opacity-100' : 'opacity-0 translate-y-10'"
                  style="animation-delay: 0.1s"
                >
                  <div class="flex items-start gap-3 mb-2">
                    <div class="flex-shrink-0 w-10 h-10 bg-[#5997ac] rounded-full flex items-center justify-center">
                      <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-5.5-2.5l7.51-3.49L17.5 6.5 9.99 9.99 6.5 17.5zm5.5-6.6c.61 0 1.1.49 1.1 1.1s-.49 1.1-1.1 1.1-1.1-.49-1.1-1.1.49-1.1 1.1-1.1z"/>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-lg font-semibold text-gray-800 mb-2">
                        {{ t('whoWeAre.values.accessibility.title') }}
                      </h3>
                      <p class="text-gray-700 text-sm leading-relaxed">
                        {{ t('whoWeAre.values.accessibility.description') }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Quality Care Value -->
                <div 
                  class="bg-gray-50 rounded-lg p-5 hover:shadow-md transition-all duration-300 border-l-4 border-[#af5166]"
                  :class="valuesVisible ? 'animate-fade-in-up opacity-100' : 'opacity-0 translate-y-10'"
                  style="animation-delay: 0.2s"
                >
                  <div class="flex items-start gap-3 mb-2">
                    <div class="flex-shrink-0 w-10 h-10 bg-[#af5166] rounded-full flex items-center justify-center">
                      <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19.5 3.5L18 2l-1.5 1.5L15 2l-1.5 1.5L12 2l-1.5 1.5L9 2 7.5 3.5 6 2v14H3v3c0 1.66 1.34 3 3 3h12c1.66 0 3-1.34 3-3V2l-1.5 1.5zM15 20H6c-.55 0-1-.45-1-1v-1h10v2zm4-1c0 .55-.45 1-1 1s-1-.45-1-1v-3H8V5h11v14z"/>
                        <path d="M9 7h6v2H9zm0 3h6v2H9z"/>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-lg font-semibold text-gray-800 mb-2">
                        {{ t('whoWeAre.values.qualityCare.title') }}
                      </h3>
                      <p class="text-gray-700 text-sm leading-relaxed">
                        {{ t('whoWeAre.values.qualityCare.description') }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Privacy Value -->
                <div 
                  class="bg-gray-50 rounded-lg p-5 hover:shadow-md transition-all duration-300 border-l-4 border-[#5997ac]"
                  :class="valuesVisible ? 'animate-fade-in-up opacity-100' : 'opacity-0 translate-y-10'"
                  style="animation-delay: 0.3s"
                >
                  <div class="flex items-start gap-3 mb-2">
                    <div class="flex-shrink-0 w-10 h-10 bg-[#5997ac] rounded-full flex items-center justify-center">
                      <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM9 6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9V6zm9 14H6V10h12v10zm-6-3c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z"/>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-lg font-semibold text-gray-800 mb-2">
                        {{ t('whoWeAre.values.privacy.title') }}
                      </h3>
                      <p class="text-gray-700 text-sm leading-relaxed">
                        {{ t('whoWeAre.values.privacy.description') }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Empathy Value -->
                <div 
                  class="bg-gray-50 rounded-lg p-5 hover:shadow-md transition-all duration-300 border-l-4 border-[#af5166]"
                  :class="valuesVisible ? 'animate-fade-in-up opacity-100' : 'opacity-0 translate-y-10'"
                  style="animation-delay: 0.4s"
                >
                  <div class="flex items-start gap-3 mb-2">
                    <div class="flex-shrink-0 w-10 h-10 bg-[#af5166] rounded-full flex items-center justify-center">
                      <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-lg font-semibold text-gray-800 mb-2">
                        {{ t('whoWeAre.values.empathy.title') }}
                      </h3>
                      <p class="text-gray-700 text-sm leading-relaxed">
                        {{ t('whoWeAre.values.empathy.description') }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Innovation Value -->
                <div 
                  class="bg-gray-50 rounded-lg p-5 hover:shadow-md transition-all duration-300 border-l-4 border-[#5997ac]"
                  :class="valuesVisible ? 'animate-fade-in-up opacity-100' : 'opacity-0 translate-y-10'"
                  style="animation-delay: 0.5s"
                >
                  <div class="flex items-start gap-3 mb-2">
                    <div class="flex-shrink-0 w-10 h-10 bg-[#5997ac] rounded-full flex items-center justify-center">
                      <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M9 21c0 .5.4 1 1 1h4c.6 0 1-.5 1-1v-1H9v1zm3-19C8.1 2 5 5.1 5 9c0 2.4 1.2 4.5 3 5.7V17c0 .5.4 1 1 1h6c.6 0 1-.5 1-1v-2.3c1.8-1.3 3-3.4 3-5.7 0-3.9-3.1-7-7-7zm2.9 11.1l-.9.6V16h-4v-2.3l-.9-.6C7.8 12.2 7 10.6 7 9c0-2.8 2.2-5 5-5s5 2.2 5 5c0 1.6-.8 3.2-2.1 4.1z"/>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-lg font-semibold text-gray-800 mb-2">
                        {{ t('whoWeAre.values.innovation.title') }}
                      </h3>
                      <p class="text-gray-700 text-sm leading-relaxed">
                        {{ t('whoWeAre.values.innovation.description') }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Community Value -->
                <div 
                  class="bg-gray-50 rounded-lg p-5 hover:shadow-md transition-all duration-300 border-l-4 border-[#af5166]"
                  :class="valuesVisible ? 'animate-fade-in-up opacity-100' : 'opacity-0 translate-y-10'"
                  style="animation-delay: 0.6s"
                >
                  <div class="flex items-start gap-3 mb-2">
                    <div class="flex-shrink-0 w-10 h-10 bg-[#af5166] rounded-full flex items-center justify-center">
                      <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-lg font-semibold text-gray-800 mb-2">
                        {{ t('whoWeAre.values.community.title') }}
                      </h3>
                      <p class="text-gray-700 text-sm leading-relaxed">
                        {{ t('whoWeAre.values.community.description') }}
                      </p>
                    </div>
                  </div>
                </div>

              </div>
            </section>

            <!-- Objectives Section -->
            <section ref="objectivesSection">
              <h2 
                class="text-2xl md:text-3xl font-bold text-[#5997ac] mb-6 transition-all duration-1000"
                :class="objectivesVisible ? 'animate-slide-down opacity-100' : 'opacity-0 -translate-y-10'"
              >
                {{ t('whoWeAre.objectives.title') }}
              </h2>

              <div class="space-y-6">
                
                <div 
                  class="bg-gray-50 rounded-lg p-5 hover:shadow-md transition-all duration-300 border-l-4 border-[#5997ac]"
                  :class="objectivesVisible ? 'animate-fade-in-up opacity-100' : 'opacity-0 translate-y-10'"
                  style="animation-delay: 0.1s"
                >
                  <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 bg-[#5997ac] rounded-full flex items-center justify-center text-white font-bold text-lg">
                      1
                    </div>
                    <div>
                      <h3 class="text-lg font-bold text-[#5997ac] mb-2">
                        {{ t('whoWeAre.objectives.objective1.title') }}
                      </h3>
                      <p class="text-gray-700 leading-relaxed">
                        {{ t('whoWeAre.objectives.objective1.description') }}
                      </p>
                    </div>
                  </div>
                </div>

                <div 
                  class="bg-gray-50 rounded-lg p-5 hover:shadow-md transition-all duration-300 border-l-4 border-[#af5166]"
                  :class="objectivesVisible ? 'animate-fade-in-up opacity-100' : 'opacity-0 translate-y-10'"
                  style="animation-delay: 0.2s"
                >
                  <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 bg-[#af5166] rounded-full flex items-center justify-center text-white font-bold text-lg">
                      2
                    </div>
                    <div>
                      <h3 class="text-lg font-bold text-[#af5166] mb-2">
                        {{ t('whoWeAre.objectives.objective2.title') }}
                      </h3>
                      <p class="text-gray-700 leading-relaxed">
                        {{ t('whoWeAre.objectives.objective2.description') }}
                      </p>
                    </div>
                  </div>
                </div>

                <div 
                  class="bg-gray-50 rounded-lg p-5 hover:shadow-md transition-all duration-300 border-l-4 border-[#5997ac]"
                  :class="objectivesVisible ? 'animate-fade-in-up opacity-100' : 'opacity-0 translate-y-10'"
                  style="animation-delay: 0.3s"
                >
                  <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 bg-[#5997ac] rounded-full flex items-center justify-center text-white font-bold text-lg">
                      3
                    </div>
                    <div>
                      <h3 class="text-lg font-bold text-[#5997ac] mb-2">
                        {{ t('whoWeAre.objectives.objective3.title') }}
                      </h3>
                      <p class="text-gray-700 leading-relaxed">
                        {{ t('whoWeAre.objectives.objective3.description') }}
                      </p>
                    </div>
                  </div>
                </div>

                <div 
                  class="bg-gray-50 rounded-lg p-5 hover:shadow-md transition-all duration-300 border-l-4 border-[#af5166]"
                  :class="objectivesVisible ? 'animate-fade-in-up opacity-100' : 'opacity-0 translate-y-10'"
                  style="animation-delay: 0.4s"
                >
                  <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 bg-[#af5166] rounded-full flex items-center justify-center text-white font-bold text-lg">
                      4
                    </div>
                    <div>
                      <h3 class="text-lg font-bold text-[#af5166] mb-2">
                        {{ t('whoWeAre.objectives.objective4.title') }}
                      </h3>
                      <p class="text-gray-700 leading-relaxed">
                        {{ t('whoWeAre.objectives.objective4.description') }}
                      </p>
                    </div>
                  </div>
                </div>

                <div 
                  class="bg-gray-50 rounded-lg p-5 hover:shadow-md transition-all duration-300 border-l-4 border-[#5997ac]"
                  :class="objectivesVisible ? 'animate-fade-in-up opacity-100' : 'opacity-0 translate-y-10'"
                  style="animation-delay: 0.5s"
                >
                  <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 bg-[#5997ac] rounded-full flex items-center justify-center text-white font-bold text-lg">
                      5
                    </div>
                    <div>
                      <h3 class="text-lg font-bold text-[#5997ac] mb-2">
                        {{ t('whoWeAre.objectives.objective5.title') }}
                      </h3>
                      <p class="text-gray-700 leading-relaxed">
                        {{ t('whoWeAre.objectives.objective5.description') }}
                      </p>
                    </div>
                  </div>
                </div>

              </div>
            </section>

          </div>
        </div>

        <!-- Sidebar (Right - 1 column) -->
        <div class="lg:col-span-1">
          
          <!-- About Section -->
          <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h3 class="text-xl font-bold text-[#5997ac] mb-4 pb-3 border-b-2 border-[#5997ac]">
              {{ t('whoWeAre.sidebar.about.title') }}
            </h3>
            <ul class="space-y-2">
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('whoWeAre.sidebar.about.items.telementalHealth') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-[#af5166] font-semibold py-2 transition-colors">
                  <span class="text-[#af5166]">›</span>
                  {{ t('whoWeAre.sidebar.about.items.whoWeAre') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('whoWeAre.sidebar.about.items.ourCareTeam') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('whoWeAre.sidebar.about.items.joinOurTeam') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('whoWeAre.sidebar.about.items.termsAndConditions') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('whoWeAre.sidebar.about.items.privacyProtection') }}
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

.animate-fade-in-up {
  animation: fade-in-up 0.8s ease-out forwards;
}

.animate-pop-in {
  animation: pop-in 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
}

/* RTL Support */
[dir="rtl"] {
  text-align: right;
}

[dir="rtl"] .flex-row-reverse {
  flex-direction: row-reverse;
}
</style>