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
</script>

<template>
  <Head title="Telemental Health - AEnhance" />

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
        {{ t('telementalHealth.banner.title') }}
      </h1>
      <div class="flex items-center gap-2 text-white text-sm">
        <a href="/" class="hover:underline">{{ t('telementalHealth.banner.home') }}</a>
        <span>»</span>
        <span>{{ t('telementalHealth.banner.about') }}</span>
        <span>»</span>
        <span>{{ t('telementalHealth.banner.current') }}</span>
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
            
            <!-- About Telemental Health -->
            <section class="mb-8">
              <h2 class="text-2xl md:text-3xl font-bold text-[#5997ac] mb-4">
                {{ t('telementalHealth.content.aboutTitle') }}
              </h2>
              <p class="text-gray-700 leading-relaxed mb-4">
                {{ t('telementalHealth.content.aboutParagraph') }}
              </p>
            </section>

            <!-- What Is Telemental Health -->
            <section class="mb-8">
              <h2 class="text-2xl md:text-3xl font-bold text-[#5997ac] mb-4">
                {{ t('telementalHealth.content.whatIsTitle') }}
              </h2>
              <p class="text-gray-700 leading-relaxed">
                {{ t('telementalHealth.content.whatIsParagraph') }}
              </p>
            </section>

            <!-- Why Choose Telemental Health -->
            <section class="mb-8">
              <h2 class="text-2xl md:text-3xl font-bold text-[#5997ac] mb-4">
                {{ t('telementalHealth.content.whyChooseTitle') }}
              </h2>
              
              <div class="space-y-4">
                <div>
                  <h3 class="text-lg font-semibold text-gray-800 mb-2">
                    1. {{ t('telementalHealth.content.reasons.accessibility.title') }}
                  </h3>
                  <p class="text-gray-700 leading-relaxed">
                    {{ t('telementalHealth.content.reasons.accessibility.description') }}
                  </p>
                </div>

                <div>
                  <h3 class="text-lg font-semibold text-gray-800 mb-2">
                    2. {{ t('telementalHealth.content.reasons.convenience.title') }}
                  </h3>
                  <p class="text-gray-700 leading-relaxed">
                    {{ t('telementalHealth.content.reasons.convenience.description') }}
                  </p>
                </div>

                <div>
                  <h3 class="text-lg font-semibold text-gray-800 mb-2">
                    3. {{ t('telementalHealth.content.reasons.privacy.title') }}
                  </h3>
                  <p class="text-gray-700 leading-relaxed">
                    {{ t('telementalHealth.content.reasons.privacy.description') }}
                  </p>
                </div>

                <div>
                  <h3 class="text-lg font-semibold text-gray-800 mb-2">
                    4. {{ t('telementalHealth.content.reasons.choice.title') }}
                  </h3>
                  <p class="text-gray-700 leading-relaxed">
                    {{ t('telementalHealth.content.reasons.choice.description') }}
                  </p>
                </div>

                <div>
                  <h3 class="text-lg font-semibold text-gray-800 mb-2">
                    5. {{ t('telementalHealth.content.reasons.effectiveness.title') }}
                  </h3>
                  <p class="text-gray-700 leading-relaxed">
                    {{ t('telementalHealth.content.reasons.effectiveness.description') }}
                  </p>
                </div>
              </div>
            </section>

            <!-- References -->
            <section class="border-t pt-6">
              <h3 class="text-sm font-semibold text-gray-600 mb-3">
                {{ t('telementalHealth.content.references.title') }}
              </h3>
              <ul class="text-xs text-gray-600 space-y-2 list-disc list-inside">
                <li>
                  <a href="https://pubmed.ncbi.nlm.nih.gov/26540584/" target="_blank" class="text-[#5997ac] hover:text-[#af5166] hover:underline transition-colors">
                    {{ t('telementalHealth.content.references.ref1') }}
                  </a>
                </li>
                <li>
                  <a href="https://pmc.ncbi.nlm.nih.gov/articles/PMC8606591/" target="_blank" class="text-[#5997ac] hover:text-[#af5166] hover:underline transition-colors">
                    {{ t('telementalHealth.content.references.ref2') }}
                  </a>
                </li>
                <li>
                  <a href="https://www.bmj.com/content/380/bmj-2022-072398" target="_blank" class="text-[#5997ac] hover:text-[#af5166] hover:underline transition-colors">
                    {{ t('telementalHealth.content.references.ref3') }}
                  </a>
                </li>
                <li>
                  <a href="https://digital.lib.washington.edu/researchworks/items/20ada002-383d-4199-876d-7a2d46582cf0" target="_blank" class="text-[#5997ac] hover:text-[#af5166] hover:underline transition-colors">
                    {{ t('telementalHealth.content.references.ref4') }}
                  </a>
                </li>
                <li>
                  <a href="https://pubmed.ncbi.nlm.nih.gov/36920229/" target="_blank" class="text-[#5997ac] hover:text-[#af5166] hover:underline transition-colors">
                    {{ t('telementalHealth.content.references.ref5') }}
                  </a>
                </li>
              </ul>
            </section>

          </div>
        </div>

        <!-- Sidebar (Right - 1 column) -->
        <div class="lg:col-span-1">
          
          <!-- About Section -->
          <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h3 class="text-xl font-bold text-[#5997ac] mb-4 pb-3 border-b-2 border-[#5997ac]">
              {{ t('telementalHealth.sidebar.about.title') }}
            </h3>
            <ul class="space-y-2">
              <li>
                <a href="#" class="flex items-center gap-2 text-[#af5166] font-semibold py-2 transition-colors">
                  <span class="text-[#af5166]">›</span>
                  {{ t('telementalHealth.sidebar.about.items.telementalHealth') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('telementalHealth.sidebar.about.items.whoWeAre') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('telementalHealth.sidebar.about.items.ourCareTeam') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('telementalHealth.sidebar.about.items.joinOurTeam') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('telementalHealth.sidebar.about.items.termsAndConditions') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('telementalHealth.sidebar.about.items.privacyProtection') }}
                </a>
              </li>
            </ul>
          </div>

          <!-- Main Navigation Section -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-xl font-bold text-[#5997ac] mb-4 pb-3 border-b-2 border-[#5997ac]">
              {{ t('telementalHealth.sidebar.navigation.title') }}
            </h3>
            <ul class="space-y-2">
              <li>
                <a href="#" class="flex items-center gap-2 text-[#af5166] font-semibold py-2 transition-colors">
                  <span class="text-[#af5166]">›</span>
                  {{ t('telementalHealth.sidebar.navigation.items.about') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('telementalHealth.sidebar.navigation.items.ourServices') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('telementalHealth.sidebar.navigation.items.support') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('telementalHealth.sidebar.navigation.items.resources') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('telementalHealth.sidebar.navigation.items.blog') }}
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
              {{ t('telementalHealth.sidebar.launch.title') }}
            </h3>
            <p class="text-sm text-center leading-relaxed">
              {{ t('telementalHealth.sidebar.launch.description') }}
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
</style>