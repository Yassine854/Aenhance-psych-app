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

// Intersection Observer for animations
const howItWorksSection = ref(null);
const instructionsSection = ref(null);
const howItWorksVisible = ref(false);
const instructionsVisible = ref(false);

onMounted(() => {
  const savedLang = localStorage.getItem("locale") || locale.value;
  setLang(savedLang);

  setTimeout(() => {
    const observerOptions = {
      threshold: 0.2,
      rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          if (entry.target === howItWorksSection.value) {
            howItWorksVisible.value = true;
          } else if (entry.target === instructionsSection.value) {
            instructionsVisible.value = true;
          }
        }
      });
    }, observerOptions);

    if (howItWorksSection.value) {
      observer.observe(howItWorksSection.value);
    }
    if (instructionsSection.value) {
      observer.observe(instructionsSection.value);
    }
  }, 100);
});
</script>

<template>
  <Head title="How It Works - AEnhance" />

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
        {{ t('howItWorks.banner.title') }}
      </h1>
      <div class="flex items-center gap-2 text-white text-sm">
        <a href="/" class="hover:underline">{{ t('howItWorks.banner.home') }}</a>
        <span>»</span>
        <span>{{ t('howItWorks.banner.support') }}</span>
        <span>»</span>
        <span>{{ t('howItWorks.banner.current') }}</span>
      </div>
    </div>
  </div>

  <!-- Main Content Section -->
  <div class="bg-gray-50 py-12 md:py-16">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <!-- Main Content Area (Left - 3 columns) -->
        <div class="lg:col-span-3">
          
          <!-- Getting Started Section -->
          <div ref="instructionsSection" class="bg-white rounded-lg shadow-sm p-6 md:p-8 mb-8">
            <h2 
              class="text-2xl md:text-3xl font-bold text-[#5997ac] mb-6 transition-all duration-1000"
              :class="instructionsVisible ? 'animate-slide-down opacity-100' : 'opacity-0 -translate-y-10'"
            >
              {{ t('howItWorks.gettingStarted.title') }}
            </h2>
            
            <div class="space-y-6">
              <!-- Step 1 -->
              <div 
                class="transition-all duration-1000 delay-100"
                :class="instructionsVisible ? 'animate-fade-in opacity-100' : 'opacity-0'"
              >
                <div class="flex items-start gap-3">
                  <span class="flex-shrink-0 w-6 h-6 bg-[#5997ac] text-white rounded-full flex items-center justify-center text-sm font-bold">1</span>
                  <div>
                    <h3 class="font-semibold text-gray-800 mb-1">{{ t('howItWorks.gettingStarted.step1.title') }}</h3>
                    <p class="text-gray-700 text-sm leading-relaxed">{{ t('howItWorks.gettingStarted.step1.description') }}</p>
                  </div>
                </div>
              </div>

              <!-- Step 2 -->
              <div 
                class="transition-all duration-1000 delay-200"
                :class="instructionsVisible ? 'animate-fade-in opacity-100' : 'opacity-0'"
              >
                <div class="flex items-start gap-3">
                  <span class="flex-shrink-0 w-6 h-6 bg-[#5997ac] text-white rounded-full flex items-center justify-center text-sm font-bold">2</span>
                  <div>
                    <h3 class="font-semibold text-gray-800 mb-1">{{ t('howItWorks.gettingStarted.step2.title') }}</h3>
                    <p class="text-gray-700 text-sm leading-relaxed">{{ t('howItWorks.gettingStarted.step2.description') }}</p>
                  </div>
                </div>
              </div>

              <!-- Step 3 -->
              <div 
                class="transition-all duration-1000 delay-300"
                :class="instructionsVisible ? 'animate-fade-in opacity-100' : 'opacity-0'"
              >
                <div class="flex items-start gap-3">
                  <span class="flex-shrink-0 w-6 h-6 bg-[#5997ac] text-white rounded-full flex items-center justify-center text-sm font-bold">3</span>
                  <div>
                    <h3 class="font-semibold text-gray-800 mb-1">{{ t('howItWorks.gettingStarted.step3.title') }}</h3>
                    <p class="text-gray-700 text-sm leading-relaxed">{{ t('howItWorks.gettingStarted.step3.description') }}</p>
                  </div>
                </div>
              </div>

              <!-- Step 4 -->
              <div 
                class="transition-all duration-1000 delay-400"
                :class="instructionsVisible ? 'animate-fade-in opacity-100' : 'opacity-0'"
              >
                <div class="flex items-start gap-3">
                  <span class="flex-shrink-0 w-6 h-6 bg-[#5997ac] text-white rounded-full flex items-center justify-center text-sm font-bold">4</span>
                  <div>
                    <h3 class="font-semibold text-gray-800 mb-1">{{ t('howItWorks.gettingStarted.step4.title') }}</h3>
                    <p class="text-gray-700 text-sm leading-relaxed">{{ t('howItWorks.gettingStarted.step4.description') }}</p>
                  </div>
                </div>
              </div>

              <!-- Step 5 -->
              <div 
                class="transition-all duration-1000 delay-500"
                :class="instructionsVisible ? 'animate-fade-in opacity-100' : 'opacity-0'"
              >
                <div class="flex items-start gap-3">
                  <span class="flex-shrink-0 w-6 h-6 bg-[#5997ac] text-white rounded-full flex items-center justify-center text-sm font-bold">5</span>
                  <div>
                    <h3 class="font-semibold text-gray-800 mb-1">{{ t('howItWorks.gettingStarted.step5.title') }}</h3>
                    <p class="text-gray-700 text-sm leading-relaxed">{{ t('howItWorks.gettingStarted.step5.description') }}</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Bottom Text -->
            <div 
              class="mt-8 p-4 bg-gray-50 rounded-lg border-l-4 border-[#5997ac] transition-all duration-1000 delay-600"
              :class="instructionsVisible ? 'animate-fade-in opacity-100' : 'opacity-0'"
            >
              <p class="text-gray-700 text-sm leading-relaxed">
                {{ t('howItWorks.gettingStarted.conclusion') }}
              </p>
            </div>
          </div>

          <!-- Visual Steps Section -->
          <section ref="howItWorksSection" class="relative bg-gradient-to-b from-white to-gray-50 rounded-lg shadow-sm overflow-hidden">
            <!-- Background Image -->
            <div class="absolute inset-0 opacity-90" style="background-image: url('/storage/home/question_marks.jpg'); background-size: cover; background-position: center; background-repeat: no-repeat;"></div>
            
            <div class="relative py-12 md:py-16 px-6 md:px-8">
              <!-- Title -->
              <h2 
                class="text-3xl md:text-4xl font-bold text-center text-[#5997ac] mb-12 md:mb-16 transition-all duration-1000"
                :class="howItWorksVisible ? 'animate-slide-down opacity-100' : 'opacity-0 -translate-y-10'"
              >
                {{ t('howItWorks.visualSteps.title') }}
              </h2>

              <!-- Steps Layout - 2 rows -->
              <div class="max-w-5xl mx-auto space-y-8">
                
                <!-- First Row - 2 Steps -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                  
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
                        {{ t('howItWorks.visualSteps.steps.createAccount.title') }}
                      </h3>
                      <p class="text-gray-600 text-xs md:text-sm leading-relaxed flex-grow">
                        {{ t('howItWorks.visualSteps.steps.createAccount.description') }}
                      </p>
                      <button class="mt-4 px-4 py-2 bg-[#5997ac] hover:bg-[#af5166] text-white text-xs rounded transition-colors duration-300">
                        {{ t('howItWorks.visualSteps.steps.createAccount.button') }}
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
                        {{ t('howItWorks.visualSteps.steps.exploreTherapists.title') }}
                      </h3>
                      <p class="text-gray-600 text-xs md:text-sm leading-relaxed flex-grow">
                        {{ t('howItWorks.visualSteps.steps.exploreTherapists.description') }}
                      </p>
                    </div>
                  </div>

                </div>

                <!-- Second Row - 2 Steps -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                  
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
                        {{ t('howItWorks.visualSteps.steps.scheduleSession.title') }}
                      </h3>
                      <p class="text-gray-600 text-xs md:text-sm leading-relaxed flex-grow">
                        {{ t('howItWorks.visualSteps.steps.scheduleSession.description') }}
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
                        {{ t('howItWorks.visualSteps.steps.connectVirtually.title') }}
                      </h3>
                      <p class="text-gray-600 text-xs md:text-sm leading-relaxed flex-grow">
                        {{ t('howItWorks.visualSteps.steps.connectVirtually.description') }}
                      </p>
                    </div>
                  </div>

                </div>

                <!-- Third Row - 1 Step (Centered) -->
                <div class="flex justify-center">
                  
                  <!-- Step 5: Start Journey -->
                  <div 
                    class="bg-white rounded-lg p-6 border-4 border-[#5997ac] hover:border-[#af5166] transition-all duration-300 shadow-sm hover:shadow-lg group w-full sm:w-1/2"
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
                        {{ t('howItWorks.visualSteps.steps.startJourney.title') }}
                      </h3>
                      <p class="text-gray-600 text-xs md:text-sm leading-relaxed flex-grow">
                        {{ t('howItWorks.visualSteps.steps.startJourney.description') }}
                      </p>
                    </div>
                  </div>

                </div>

              </div>
            </div>
          </section>

        </div>

        <!-- Sidebar (Right - 1 column) -->
        <div class="lg:col-span-1">
          
          <!-- Support Section -->
          <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h3 class="text-xl font-bold text-[#5997ac] mb-4 pb-3 border-b-2 border-[#5997ac]">
              {{ t('howItWorks.sidebar.support.title') }}
            </h3>
            <ul class="space-y-2">
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('howItWorks.sidebar.support.items.faq') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-[#af5166] font-semibold py-2 transition-colors">
                  <span class="text-[#af5166]">›</span>
                  {{ t('howItWorks.sidebar.support.items.howItWorks') }}
                </a>
              </li>
            </ul>
          </div>

          <!-- Main Navigation Section -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-xl font-bold text-[#5997ac] mb-4 pb-3 border-b-2 border-[#5997ac]">
              {{ t('howItWorks.sidebar.navigation.title') }}
            </h3>
            <ul class="space-y-2">
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('howItWorks.sidebar.navigation.items.about') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('howItWorks.sidebar.navigation.items.ourServices') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-[#af5166] font-semibold py-2 transition-colors">
                  <span class="text-[#af5166]">›</span>
                  {{ t('howItWorks.sidebar.navigation.items.support') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('howItWorks.sidebar.navigation.items.resources') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('howItWorks.sidebar.navigation.items.blog') }}
                </a>
              </li>
            </ul>
          </div>

          <!-- Launch Announcement Box -->
          <div class="bg-gradient-to-br from-[#5997ac] to-[#af5166] rounded-lg shadow-lg p-6 mt-6 text-white">
            <div class="text-center mb-4">
              <div class="w-32 h-32 mx-auto bg-white rounded-full flex items-center justify-center p-5">
                <img src="/storage/aenhance.svg" alt="AEnhance" class="w-full h-full object-contain" />
              </div>
            </div>
            <h3 class="text-lg font-bold text-center mb-3">
              {{ t('howItWorks.sidebar.launch.title') }}
            </h3>
            <p class="text-sm text-center leading-relaxed">
              {{ t('howItWorks.sidebar.launch.description') }}
            </p>
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

/* Animation Classes */
.animate-slide-down {
  animation: slide-down 0.8s ease-out forwards;
}

.animate-fade-in {
  animation: fade-in 0.8s ease-out forwards;
}

.animate-fade-in-up {
  animation: fade-in-up 0.8s ease-out forwards;
}

/* Delay Classes */
.delay-100 {
  animation-delay: 0.1s;
}

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
</style>