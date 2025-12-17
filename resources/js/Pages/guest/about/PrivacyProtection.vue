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
  <Head title="Privacy Protection - AEnhance" />

  <Navbar 
    :canLogin="canLogin" 
    :canRegister="canRegister" 
    :authUser="authUser" 
  />

  <!-- Banner Section -->
  <div class="relative w-full h-[180px] overflow-hidden">
    <div
      class="absolute inset-0 bg-cover bg-center"
      style="background-image: url('/storage/banners/banner2.jpg')"
    >
      <div class="absolute inset-0 bg-gradient-to-r from-[#5997ac]/80 to-[#e8b4b8]/80"></div>
    </div>
    
    <div class="relative h-full flex flex-col justify-center container mx-auto px-4 sm:px-6 lg:px-8">
      <h1 class="text-3xl md:text-4xl font-bold text-white mb-3">
        {{ t('privacyProtection.banner.title') }}
      </h1>
      <div class="flex items-center gap-2 text-white text-sm">
        <a href="/" class="hover:underline">{{ t('privacyProtection.banner.home') }}</a>
        <span>»</span>
        <span>{{ t('privacyProtection.banner.about') }}</span>
        <span>»</span>
        <span>{{ t('privacyProtection.banner.current') }}</span>
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
            
            <!-- Introduction -->
            <section class="mb-8">
              <h2 class="text-2xl md:text-3xl font-bold text-[#5997ac] mb-4">
                {{ t('privacyProtection.content.introTitle') }}
              </h2>
              <p class="text-gray-700 leading-relaxed mb-4">
                {{ t('privacyProtection.content.introParagraph1') }}
              </p>
              <p class="text-gray-700 leading-relaxed">
                {{ t('privacyProtection.content.introParagraph2') }}
              </p>
            </section>

            <!-- Our Commitment to Privacy -->
            <section class="mb-8">
              <h2 class="text-2xl md:text-3xl font-bold text-[#5997ac] mb-4">
                {{ t('privacyProtection.content.commitmentTitle') }}
              </h2>
              
              <div class="space-y-6">
                <!-- End-to-End Encryption -->
                <div class="bg-gray-50 p-5 rounded-lg">
                  <div class="flex items-start gap-3 mb-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-[#5997ac] rounded-full flex items-center justify-center">
                      <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-lg font-semibold text-gray-800 mb-2">
                        {{ t('privacyProtection.content.commitment.encryption.title') }}
                      </h3>
                      <p class="text-gray-700 leading-relaxed">
                        {{ t('privacyProtection.content.commitment.encryption.description') }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- HIPAA Compliance -->
                <div class="bg-gray-50 p-5 rounded-lg">
                  <div class="flex items-start gap-3 mb-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-[#5997ac] rounded-full flex items-center justify-center">
                      <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-lg font-semibold text-gray-800 mb-2">
                        {{ t('privacyProtection.content.commitment.compliance.title') }}
                      </h3>
                      <p class="text-gray-700 leading-relaxed">
                        {{ t('privacyProtection.content.commitment.compliance.description') }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Secure Data Storage -->
                <div class="bg-gray-50 p-5 rounded-lg">
                  <div class="flex items-start gap-3 mb-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-[#5997ac] rounded-full flex items-center justify-center">
                      <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-lg font-semibold text-gray-800 mb-2">
                        {{ t('privacyProtection.content.commitment.storage.title') }}
                      </h3>
                      <p class="text-gray-700 leading-relaxed">
                        {{ t('privacyProtection.content.commitment.storage.description') }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- No Third-Party Sharing -->
                <div class="bg-gray-50 p-5 rounded-lg">
                  <div class="flex items-start gap-3 mb-3">
                    <div class="flex-shrink-0 w-10 h-10 bg-[#5997ac] rounded-full flex items-center justify-center">
                      <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-lg font-semibold text-gray-800 mb-2">
                        {{ t('privacyProtection.content.commitment.noSharing.title') }}
                      </h3>
                      <p class="text-gray-700 leading-relaxed">
                        {{ t('privacyProtection.content.commitment.noSharing.description') }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <!-- Your Rights -->
            <section class="mb-8">
              <h2 class="text-2xl md:text-3xl font-bold text-[#5997ac] mb-4">
                {{ t('privacyProtection.content.rightsTitle') }}
              </h2>
              
              <div class="space-y-4">
                <div class="flex items-start gap-3">
                  <span class="text-[#af5166] font-bold text-lg">•</span>
                  <div>
                    <h4 class="font-semibold text-gray-800">
                      {{ t('privacyProtection.content.rights.access.title') }}
                    </h4>
                    <p class="text-gray-700">
                      {{ t('privacyProtection.content.rights.access.description') }}
                    </p>
                  </div>
                </div>

                <div class="flex items-start gap-3">
                  <span class="text-[#af5166] font-bold text-lg">•</span>
                  <div>
                    <h4 class="font-semibold text-gray-800">
                      {{ t('privacyProtection.content.rights.correction.title') }}
                    </h4>
                    <p class="text-gray-700">
                      {{ t('privacyProtection.content.rights.correction.description') }}
                    </p>
                  </div>
                </div>

                <div class="flex items-start gap-3">
                  <span class="text-[#af5166] font-bold text-lg">•</span>
                  <div>
                    <h4 class="font-semibold text-gray-800">
                      {{ t('privacyProtection.content.rights.deletion.title') }}
                    </h4>
                    <p class="text-gray-700">
                      {{ t('privacyProtection.content.rights.deletion.description') }}
                    </p>
                  </div>
                </div>

                <div class="flex items-start gap-3">
                  <span class="text-[#af5166] font-bold text-lg">•</span>
                  <div>
                    <h4 class="font-semibold text-gray-800">
                      {{ t('privacyProtection.content.rights.export.title') }}
                    </h4>
                    <p class="text-gray-700">
                      {{ t('privacyProtection.content.rights.export.description') }}
                    </p>
                  </div>
                </div>
              </div>
            </section>

            <!-- Information We Collect -->
            <section class="mb-8">
              <h2 class="text-2xl md:text-3xl font-bold text-[#5997ac] mb-4">
                {{ t('privacyProtection.content.collectionTitle') }}
              </h2>
              <p class="text-gray-700 leading-relaxed mb-4">
                {{ t('privacyProtection.content.collectionDescription') }}
              </p>
              
              <ul class="space-y-2 ml-6">
                <li class="text-gray-700 flex items-start gap-2">
                  <span class="text-[#5997ac] mt-1">▸</span>
                  <span>{{ t('privacyProtection.content.collectionItems.0') }}</span>
                </li>
                <li class="text-gray-700 flex items-start gap-2">
                  <span class="text-[#5997ac] mt-1">▸</span>
                  <span>{{ t('privacyProtection.content.collectionItems.1') }}</span>
                </li>
                <li class="text-gray-700 flex items-start gap-2">
                  <span class="text-[#5997ac] mt-1">▸</span>
                  <span>{{ t('privacyProtection.content.collectionItems.2') }}</span>
                </li>
                <li class="text-gray-700 flex items-start gap-2">
                  <span class="text-[#5997ac] mt-1">▸</span>
                  <span>{{ t('privacyProtection.content.collectionItems.3') }}</span>
                </li>
              </ul>
            </section>

            <!-- Security Measures -->
            <section class="mb-8">
              <h2 class="text-2xl md:text-3xl font-bold text-[#5997ac] mb-4">
                {{ t('privacyProtection.content.securityTitle') }}
              </h2>
              <p class="text-gray-700 leading-relaxed mb-4">
                {{ t('privacyProtection.content.securityDescription') }}
              </p>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border-l-4 border-[#5997ac] pl-4 py-2">
                  <h4 class="font-semibold text-gray-800 mb-1">
                    {{ t('privacyProtection.content.securityMeasures.0.title') }}
                  </h4>
                  <p class="text-sm text-gray-600">
                    {{ t('privacyProtection.content.securityMeasures.0.description') }}
                  </p>
                </div>
                <div class="border-l-4 border-[#5997ac] pl-4 py-2">
                  <h4 class="font-semibold text-gray-800 mb-1">
                    {{ t('privacyProtection.content.securityMeasures.1.title') }}
                  </h4>
                  <p class="text-sm text-gray-600">
                    {{ t('privacyProtection.content.securityMeasures.1.description') }}
                  </p>
                </div>
                <div class="border-l-4 border-[#5997ac] pl-4 py-2">
                  <h4 class="font-semibold text-gray-800 mb-1">
                    {{ t('privacyProtection.content.securityMeasures.2.title') }}
                  </h4>
                  <p class="text-sm text-gray-600">
                    {{ t('privacyProtection.content.securityMeasures.2.description') }}
                  </p>
                </div>
                <div class="border-l-4 border-[#5997ac] pl-4 py-2">
                  <h4 class="font-semibold text-gray-800 mb-1">
                    {{ t('privacyProtection.content.securityMeasures.3.title') }}
                  </h4>
                  <p class="text-sm text-gray-600">
                    {{ t('privacyProtection.content.securityMeasures.3.description') }}
                  </p>
                </div>
              </div>
            </section>

            <!-- Contact Us -->
            <section class="bg-gradient-to-r from-[#5997ac]/10 to-[#af5166]/10 p-6 rounded-lg">
              <h2 class="text-xl font-bold text-[#5997ac] mb-3">
                {{ t('privacyProtection.content.contactTitle') }}
              </h2>
              <p class="text-gray-700 leading-relaxed mb-4">
                {{ t('privacyProtection.content.contactDescription') }}
              </p>
              <button class="px-6 py-2.5 bg-[#af5166] hover:bg-[#8d3d4f] text-white text-sm rounded-lg transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                {{ t('privacyProtection.content.contactButton') }}
              </button>
            </section>

          </div>
        </div>

        <!-- Sidebar (Right - 1 column) -->
        <div class="lg:col-span-1">
          
          <!-- About Section -->
          <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h3 class="text-xl font-bold text-[#5997ac] mb-4 pb-3 border-b-2 border-[#5997ac]">
              {{ t('privacyProtection.sidebar.about.title') }}
            </h3>
            <ul class="space-y-2">
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('privacyProtection.sidebar.about.items.telementalHealth') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('privacyProtection.sidebar.about.items.whoWeAre') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('privacyProtection.sidebar.about.items.ourCareTeam') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('privacyProtection.sidebar.about.items.joinOurTeam') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('privacyProtection.sidebar.about.items.termsAndConditions') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-[#af5166] font-semibold py-2 transition-colors">
                  <span class="text-[#af5166]">›</span>
                  {{ t('privacyProtection.sidebar.about.items.privacyProtection') }}
                </a>
              </li>
            </ul>
          </div>

          <!-- Quick Facts -->
          <div class="bg-gradient-to-br from-[#5997ac] to-[#af5166] rounded-lg shadow-lg p-6 text-white mb-6">
            <h3 class="text-lg font-bold mb-4 text-center">
              {{ t('privacyProtection.sidebar.quickFacts.title') }}
            </h3>
            <ul class="space-y-3 text-sm">
              <li class="flex items-start gap-2">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ t('privacyProtection.sidebar.quickFacts.items.0') }}</span>
              </li>
              <li class="flex items-start gap-2">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ t('privacyProtection.sidebar.quickFacts.items.1') }}</span>
              </li>
              <li class="flex items-start gap-2">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ t('privacyProtection.sidebar.quickFacts.items.2') }}</span>
              </li>
              <li class="flex items-start gap-2">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ t('privacyProtection.sidebar.quickFacts.items.3') }}</span>
              </li>
            </ul>
          </div>

          <!-- Trust Badge -->
          <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="text-center">
              <div class="w-24 h-24 mx-auto bg-gradient-to-br from-[#5997ac] to-[#af5166] rounded-full flex items-center justify-center mb-4">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
              </div>
              <h4 class="font-bold text-[#5997ac] mb-2">
                {{ t('privacyProtection.sidebar.trust.title') }}
              </h4>
              <p class="text-sm text-gray-600">
                {{ t('privacyProtection.sidebar.trust.description') }}
              </p>
            </div>
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