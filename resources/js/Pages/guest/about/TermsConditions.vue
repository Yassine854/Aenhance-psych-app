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
  <Head title="Terms and Conditions - AEnhance" />

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
        {{ t('termsConditions.banner.title') }}
      </h1>
      <div class="flex items-center gap-2 text-white text-sm">
        <a href="/" class="hover:underline">{{ t('termsConditions.banner.home') }}</a>
        <span>»</span>
        <span>{{ t('termsConditions.banner.about') }}</span>
        <span>»</span>
        <span>{{ t('termsConditions.banner.current') }}</span>
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
                {{ t('termsConditions.content.introTitle') }}
              </h2>
              <p class="text-gray-700 leading-relaxed mb-4">
                {{ t('termsConditions.content.introParagraph1') }}
              </p>
              <p class="text-gray-700 leading-relaxed">
                {{ t('termsConditions.content.introParagraph2') }}
              </p>
            </section>

            <!-- 1. Acceptance of Terms -->
            <section class="mb-8">
              <h2 class="text-xl md:text-2xl font-bold text-[#5997ac] mb-4 flex items-center gap-2">
                <span class="bg-[#5997ac] text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">1</span>
                {{ t('termsConditions.content.sections.acceptance.title') }}
              </h2>
              <p class="text-gray-700 leading-relaxed mb-3">
                {{ t('termsConditions.content.sections.acceptance.paragraph1') }}
              </p>
              <p class="text-gray-700 leading-relaxed">
                {{ t('termsConditions.content.sections.acceptance.paragraph2') }}
              </p>
            </section>

            <!-- 2. Service Description -->
            <section class="mb-8">
              <h2 class="text-xl md:text-2xl font-bold text-[#5997ac] mb-4 flex items-center gap-2">
                <span class="bg-[#5997ac] text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">2</span>
                {{ t('termsConditions.content.sections.serviceDescription.title') }}
              </h2>
              <p class="text-gray-700 leading-relaxed mb-3">
                {{ t('termsConditions.content.sections.serviceDescription.paragraph') }}
              </p>
              <ul class="space-y-2 ml-6">
                <li class="text-gray-700 flex items-start gap-2">
                  <span class="text-[#5997ac] mt-1">•</span>
                  <span>{{ t('termsConditions.content.sections.serviceDescription.items.0') }}</span>
                </li>
                <li class="text-gray-700 flex items-start gap-2">
                  <span class="text-[#5997ac] mt-1">•</span>
                  <span>{{ t('termsConditions.content.sections.serviceDescription.items.1') }}</span>
                </li>
                <li class="text-gray-700 flex items-start gap-2">
                  <span class="text-[#5997ac] mt-1">•</span>
                  <span>{{ t('termsConditions.content.sections.serviceDescription.items.2') }}</span>
                </li>
                <li class="text-gray-700 flex items-start gap-2">
                  <span class="text-[#5997ac] mt-1">•</span>
                  <span>{{ t('termsConditions.content.sections.serviceDescription.items.3') }}</span>
                </li>
              </ul>
            </section>

            <!-- 3. User Eligibility -->
            <section class="mb-8">
              <h2 class="text-xl md:text-2xl font-bold text-[#5997ac] mb-4 flex items-center gap-2">
                <span class="bg-[#5997ac] text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">3</span>
                {{ t('termsConditions.content.sections.eligibility.title') }}
              </h2>
              <p class="text-gray-700 leading-relaxed mb-3">
                {{ t('termsConditions.content.sections.eligibility.paragraph') }}
              </p>
              <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mt-4">
                <p class="text-sm text-yellow-800">
                  <strong>{{ t('termsConditions.content.sections.eligibility.note.title') }}:</strong> {{ t('termsConditions.content.sections.eligibility.note.text') }}
                </p>
              </div>
            </section>

            <!-- 4. Account Registration -->
            <section class="mb-8">
              <h2 class="text-xl md:text-2xl font-bold text-[#5997ac] mb-4 flex items-center gap-2">
                <span class="bg-[#5997ac] text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">4</span>
                {{ t('termsConditions.content.sections.registration.title') }}
              </h2>
              <p class="text-gray-700 leading-relaxed mb-3">
                {{ t('termsConditions.content.sections.registration.paragraph') }}
              </p>
              <ul class="space-y-2 ml-6">
                <li class="text-gray-700 flex items-start gap-2">
                  <span class="text-[#5997ac] mt-1">▸</span>
                  <span>{{ t('termsConditions.content.sections.registration.responsibilities.0') }}</span>
                </li>
                <li class="text-gray-700 flex items-start gap-2">
                  <span class="text-[#5997ac] mt-1">▸</span>
                  <span>{{ t('termsConditions.content.sections.registration.responsibilities.1') }}</span>
                </li>
                <li class="text-gray-700 flex items-start gap-2">
                  <span class="text-[#5997ac] mt-1">▸</span>
                  <span>{{ t('termsConditions.content.sections.registration.responsibilities.2') }}</span>
                </li>
                <li class="text-gray-700 flex items-start gap-2">
                  <span class="text-[#5997ac] mt-1">▸</span>
                  <span>{{ t('termsConditions.content.sections.registration.responsibilities.3') }}</span>
                </li>
              </ul>
            </section>

            <!-- 5. Therapeutic Relationship -->
            <section class="mb-8">
              <h2 class="text-xl md:text-2xl font-bold text-[#5997ac] mb-4 flex items-center gap-2">
                <span class="bg-[#5997ac] text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">5</span>
                {{ t('termsConditions.content.sections.therapeutic.title') }}
              </h2>
              <p class="text-gray-700 leading-relaxed mb-3">
                {{ t('termsConditions.content.sections.therapeutic.paragraph1') }}
              </p>
              <p class="text-gray-700 leading-relaxed">
                {{ t('termsConditions.content.sections.therapeutic.paragraph2') }}
              </p>
            </section>

            <!-- 6. Emergency Situations -->
            <section class="mb-8">
              <h2 class="text-xl md:text-2xl font-bold text-[#5997ac] mb-4 flex items-center gap-2">
                <span class="bg-[#5997ac] text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">6</span>
                {{ t('termsConditions.content.sections.emergency.title') }}
              </h2>
              <div class="bg-red-50 border-l-4 border-red-500 p-5 mb-4">
                <h4 class="font-bold text-red-800 mb-2">{{ t('termsConditions.content.sections.emergency.warning.title') }}</h4>
                <p class="text-red-700 leading-relaxed mb-3">
                  {{ t('termsConditions.content.sections.emergency.warning.text') }}
                </p>
                <p class="text-red-700 font-semibold">
                  {{ t('termsConditions.content.sections.emergency.warning.numbers') }}
                </p>
              </div>
              <p class="text-gray-700 leading-relaxed">
                {{ t('termsConditions.content.sections.emergency.paragraph') }}
              </p>
            </section>

            <!-- 7. Payment Terms -->
            <section class="mb-8">
              <h2 class="text-xl md:text-2xl font-bold text-[#5997ac] mb-4 flex items-center gap-2">
                <span class="bg-[#5997ac] text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">7</span>
                {{ t('termsConditions.content.sections.payment.title') }}
              </h2>
              <div class="space-y-4">
                <div>
                  <h4 class="font-semibold text-gray-800 mb-2">{{ t('termsConditions.content.sections.payment.fees.title') }}</h4>
                  <p class="text-gray-700 leading-relaxed">
                    {{ t('termsConditions.content.sections.payment.fees.text') }}
                  </p>
                </div>
                <div>
                  <h4 class="font-semibold text-gray-800 mb-2">{{ t('termsConditions.content.sections.payment.cancellation.title') }}</h4>
                  <p class="text-gray-700 leading-relaxed">
                    {{ t('termsConditions.content.sections.payment.cancellation.text') }}
                  </p>
                </div>
                <div>
                  <h4 class="font-semibold text-gray-800 mb-2">{{ t('termsConditions.content.sections.payment.refunds.title') }}</h4>
                  <p class="text-gray-700 leading-relaxed">
                    {{ t('termsConditions.content.sections.payment.refunds.text') }}
                  </p>
                </div>
              </div>
            </section>

            <!-- 8. Confidentiality -->
            <section class="mb-8">
              <h2 class="text-xl md:text-2xl font-bold text-[#5997ac] mb-4 flex items-center gap-2">
                <span class="bg-[#5997ac] text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">8</span>
                {{ t('termsConditions.content.sections.confidentiality.title') }}
              </h2>
              <p class="text-gray-700 leading-relaxed mb-3">
                {{ t('termsConditions.content.sections.confidentiality.paragraph1') }}
              </p>
              <p class="text-gray-700 leading-relaxed mb-3">
                {{ t('termsConditions.content.sections.confidentiality.paragraph2') }}
              </p>
              <ul class="space-y-2 ml-6">
                <li class="text-gray-700 flex items-start gap-2">
                  <span class="text-[#5997ac] mt-1">•</span>
                  <span>{{ t('termsConditions.content.sections.confidentiality.exceptions.0') }}</span>
                </li>
                <li class="text-gray-700 flex items-start gap-2">
                  <span class="text-[#5997ac] mt-1">•</span>
                  <span>{{ t('termsConditions.content.sections.confidentiality.exceptions.1') }}</span>
                </li>
                <li class="text-gray-700 flex items-start gap-2">
                  <span class="text-[#5997ac] mt-1">•</span>
                  <span>{{ t('termsConditions.content.sections.confidentiality.exceptions.2') }}</span>
                </li>
                <li class="text-gray-700 flex items-start gap-2">
                  <span class="text-[#5997ac] mt-1">•</span>
                  <span>{{ t('termsConditions.content.sections.confidentiality.exceptions.3') }}</span>
                </li>
              </ul>
            </section>

            <!-- 9. User Conduct -->
            <section class="mb-8">
              <h2 class="text-xl md:text-2xl font-bold text-[#5997ac] mb-4 flex items-center gap-2">
                <span class="bg-[#5997ac] text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">9</span>
                {{ t('termsConditions.content.sections.conduct.title') }}
              </h2>
              <p class="text-gray-700 leading-relaxed mb-3">
                {{ t('termsConditions.content.sections.conduct.paragraph') }}
              </p>
              <ul class="space-y-2 ml-6">
                <li class="text-gray-700 flex items-start gap-2">
                  <span class="text-red-500 mt-1">✗</span>
                  <span>{{ t('termsConditions.content.sections.conduct.prohibited.0') }}</span>
                </li>
                <li class="text-gray-700 flex items-start gap-2">
                  <span class="text-red-500 mt-1">✗</span>
                  <span>{{ t('termsConditions.content.sections.conduct.prohibited.1') }}</span>
                </li>
                <li class="text-gray-700 flex items-start gap-2">
                  <span class="text-red-500 mt-1">✗</span>
                  <span>{{ t('termsConditions.content.sections.conduct.prohibited.2') }}</span>
                </li>
                <li class="text-gray-700 flex items-start gap-2">
                  <span class="text-red-500 mt-1">✗</span>
                  <span>{{ t('termsConditions.content.sections.conduct.prohibited.3') }}</span>
                </li>
                <li class="text-gray-700 flex items-start gap-2">
                  <span class="text-red-500 mt-1">✗</span>
                  <span>{{ t('termsConditions.content.sections.conduct.prohibited.4') }}</span>
                </li>
              </ul>
            </section>

            <!-- 10. Limitation of Liability -->
            <section class="mb-8">
              <h2 class="text-xl md:text-2xl font-bold text-[#5997ac] mb-4 flex items-center gap-2">
                <span class="bg-[#5997ac] text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">10</span>
                {{ t('termsConditions.content.sections.liability.title') }}
              </h2>
              <p class="text-gray-700 leading-relaxed mb-3">
                {{ t('termsConditions.content.sections.liability.paragraph1') }}
              </p>
              <p class="text-gray-700 leading-relaxed">
                {{ t('termsConditions.content.sections.liability.paragraph2') }}
              </p>
            </section>

            <!-- 11. Termination -->
            <section class="mb-8">
              <h2 class="text-xl md:text-2xl font-bold text-[#5997ac] mb-4 flex items-center gap-2">
                <span class="bg-[#5997ac] text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">11</span>
                {{ t('termsConditions.content.sections.termination.title') }}
              </h2>
              <p class="text-gray-700 leading-relaxed mb-3">
                {{ t('termsConditions.content.sections.termination.paragraph1') }}
              </p>
              <p class="text-gray-700 leading-relaxed">
                {{ t('termsConditions.content.sections.termination.paragraph2') }}
              </p>
            </section>

            <!-- 12. Changes to Terms -->
            <section class="mb-8">
              <h2 class="text-xl md:text-2xl font-bold text-[#5997ac] mb-4 flex items-center gap-2">
                <span class="bg-[#5997ac] text-white w-8 h-8 rounded-full flex items-center justify-center text-sm">12</span>
                {{ t('termsConditions.content.sections.changes.title') }}
              </h2>
              <p class="text-gray-700 leading-relaxed">
                {{ t('termsConditions.content.sections.changes.paragraph') }}
              </p>
            </section>

            <!-- Contact Information -->
            <section class="bg-gradient-to-r from-[#5997ac]/10 to-[#af5166]/10 p-6 rounded-lg">
              <h2 class="text-xl font-bold text-[#5997ac] mb-3">
                {{ t('termsConditions.content.contact.title') }}
              </h2>
              <p class="text-gray-700 leading-relaxed mb-4">
                {{ t('termsConditions.content.contact.text') }}
              </p>
              <div class="space-y-2 text-sm text-gray-700">
                <p><strong>{{ t('termsConditions.content.contact.email') }}:</strong> contact@aenhance.tn</p>
              </div>
            </section>

          </div>
        </div>

        <!-- Sidebar (Right - 1 column) -->
        <div class="lg:col-span-1">
          
          <!-- About Section -->
          <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h3 class="text-xl font-bold text-[#5997ac] mb-4 pb-3 border-b-2 border-[#5997ac]">
              {{ t('termsConditions.sidebar.about.title') }}
            </h3>
            <ul class="space-y-2">
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('termsConditions.sidebar.about.items.telementalHealth') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('termsConditions.sidebar.about.items.whoWeAre') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('termsConditions.sidebar.about.items.ourCareTeam') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('termsConditions.sidebar.about.items.joinOurTeam') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-[#af5166] font-semibold py-2 transition-colors">
                  <span class="text-[#af5166]">›</span>
                  {{ t('termsConditions.sidebar.about.items.termsAndConditions') }}
                </a>
              </li>
              <li>
                <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-[#af5166] transition-colors py-2">
                  <span class="text-[#af5166]">›</span>
                  {{ t('termsConditions.sidebar.about.items.privacyProtection') }}
                </a>
              </li>
            </ul>
          </div>

          <!-- Important Notice -->
          <div class="bg-gradient-to-br from-[#5997ac] to-[#af5166] rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-start gap-3 mb-4">
              <svg class="w-8 h-8 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
              </svg>
              <div>
                <h3 class="text-lg font-bold mb-2">
                  {{ t('termsConditions.sidebar.notice.title') }}
                </h3>
                <p class="text-sm leading-relaxed">
                  {{ t('termsConditions.sidebar.notice.text') }}
                </p>
              </div>
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