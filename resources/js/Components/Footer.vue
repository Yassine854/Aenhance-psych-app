<template>
  <footer :class="['bg-[#e8e4dc] text-gray-800', isRTL ? 'rtl' : 'ltr']">
    <div class="max-w-7xl mx-auto px-8 py-12">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
        
        <!-- Logo and Description -->
        <div class="space-y-4">
          <div class="flex items-center gap-2">
            <img src="/storage/aenhance.svg" alt="AEnhance Logo" class="h-20 w-20 object-contain" />
          </div>
          <p class="text-sm leading-relaxed" :style="{ textAlign: isRTL ? 'right' : 'left' }">
            {{ $t('footer.companyDesc') }}
          </p>
        </div>

        <!-- About Section -->
        <div>
          <h3 class="text-[#5997ac] font-bold text-lg mb-4">
            {{ $t('footer.about') }}
          </h3>
          <ul class="space-y-2">
            <li>
              <a href="#" class="text-gray-700 hover:text-[#5997ac] transition-colors flex items-center gap-2 text-sm">
                <span :class="['text-[#C55A76]', isRTL ? 'rotate-180' : '']">›</span>
                {{ $t('footer.aboutMenu.telementalHealth') }}
              </a>
            </li>
            <li>
              <a href="#" class="text-gray-700 hover:text-[#5997ac] transition-colors flex items-center gap-2 text-sm">
                <span :class="['text-[#C55A76]', isRTL ? 'rotate-180' : '']">›</span>
                {{ $t('footer.aboutMenu.whoWeAre') }}
              </a>
            </li>
            <li>
              <a href="#" class="text-gray-700 hover:text-[#5997ac] transition-colors flex items-center gap-2 text-sm">
                <span :class="['text-[#C55A76]', isRTL ? 'rotate-180' : '']">›</span>
                {{ $t('footer.aboutMenu.ourCareTeam') }}
              </a>
            </li>
            <li>
              <a href="#" class="text-gray-700 hover:text-[#5997ac] transition-colors flex items-center gap-2 text-sm">
                <span :class="['text-[#C55A76]', isRTL ? 'rotate-180' : '']">›</span>
                {{ $t('footer.aboutMenu.joinOurTeam') }}
              </a>
            </li>
            <li>
              <a href="#" class="text-gray-700 hover:text-[#5997ac] transition-colors flex items-center gap-2 text-sm">
                <span :class="['text-[#C55A76]', isRTL ? 'rotate-180' : '']">›</span>
                {{ $t('footer.aboutMenu.termsConditions') }}
              </a>
            </li>
            <li>
              <a href="#" class="text-gray-700 hover:text-[#5997ac] transition-colors flex items-center gap-2 text-sm">
                <span :class="['text-[#C55A76]', isRTL ? 'rotate-180' : '']">›</span>
                {{ $t('footer.aboutMenu.privacyProtection') }}
              </a>
            </li>
          </ul>
        </div>

        <!-- Resources Section -->
        <div>
          <h3 class="text-[#5997ac] font-bold text-lg mb-4">
            {{ $t('footer.resources') }}
          </h3>
          <ul class="space-y-2">
            <li>
              <a href="#" class="text-gray-700 hover:text-[#5997ac] transition-colors flex items-center gap-2 text-sm">
                <span :class="['text-[#C55A76]', isRTL ? 'rotate-180' : '']">›</span>
                {{ $t('footer.resourcesMenu.anxietyDisorders') }}
              </a>
            </li>
            <li>
              <a href="#" class="text-gray-700 hover:text-[#5997ac] transition-colors flex items-center gap-2 text-sm">
                <span :class="['text-[#C55A76]', isRTL ? 'rotate-180' : '']">›</span>
                {{ $t('footer.resourcesMenu.moodDisorders') }}
              </a>
            </li>
            <li>
              <a href="#" class="text-gray-700 hover:text-[#5997ac] transition-colors flex items-center gap-2 text-sm">
                <span :class="['text-[#C55A76]', isRTL ? 'rotate-180' : '']">›</span>
                {{ $t('footer.resourcesMenu.sleepDisorders') }}
              </a>
            </li>
            <li>
              <a href="#" class="text-gray-700 hover:text-[#5997ac] transition-colors flex items-center gap-2 text-sm">
                <span :class="['text-[#C55A76]', isRTL ? 'rotate-180' : '']">›</span>
                {{ $t('footer.resourcesMenu.eatingDisorders') }}
              </a>
            </li>
            <li>
              <a href="#" class="text-gray-700 hover:text-[#5997ac] transition-colors flex items-center gap-2 text-sm">
                <span :class="['text-[#C55A76]', isRTL ? 'rotate-180' : '']">›</span>
                {{ $t('footer.resourcesMenu.adjustmentDisorders') }}
              </a>
            </li>
            <li>
              <a href="#" class="text-gray-700 hover:text-[#5997ac] transition-colors flex items-center gap-2 text-sm">
                <span :class="['text-[#C55A76]', isRTL ? 'rotate-180' : '']">›</span>
                {{ $t('footer.resourcesMenu.personalityDisorders') }}
              </a>
            </li>
          </ul>
        </div>

        <!-- Contact Section -->
        <div>
          <h3 class="text-[#5997ac] font-bold text-lg mb-4">
            {{ $t('footer.contactUs') }}
          </h3>
          <div class="space-y-3">
            <a 
              :href="`tel:${$t('footer.contact.phone')}`" 
              class="flex items-center gap-2 text-gray-700 hover:text-[#5997ac] transition-colors text-sm"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
              </svg>
              <span>{{ $t('footer.contact.phone') }}</span>
            </a>
            <a 
              :href="`mailto:${$t('footer.contact.email')}`" 
              class="flex items-center gap-2 text-gray-700 hover:text-[#5997ac] transition-colors text-sm"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
              </svg>
              <span>{{ $t('footer.contact.email') }}</span>
            </a>
          </div>

          <!-- Payment Methods -->
          <div class="mt-6">
            <h4 class="text-[#5997ac] font-bold text-base mb-3">
              {{ $t('footer.paymentMethods') }}
            </h4>
            <div class="payment-wrapper relative overflow-hidden group cursor-pointer inline-block">
              <img 
                src="/storage/home/sps2.png" 
                alt="Payment Methods"
                class="h-12 w-auto transition-all duration-500 group-hover:brightness-110"
              />
              <!-- Shine Effect Overlay -->
              <div class="shine-overlay absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 group-hover:opacity-30 pointer-events-none" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Bar (Like Nav Style) -->
    <div class="bg-[#af5166] text-white py-3">
      <div class="max-w-7xl mx-auto px-8">
        <div class="flex flex-col md:flex-row justify-between items-center gap-2 text-sm">
          <p>© 2025 AENHANCE</p>
          <p>{{ $t('footer.developedBy') }}: {{ $t('footer.companyName') }}</p>
        </div>
      </div>
    </div>
  </footer>
</template>

<script>
export default {
  name: 'Footer',
  computed: {
    isRTL() {
      return this.$i18n.locale === 'ar';
    }
  }
}
</script>

<style scoped>
@keyframes shine {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(100%);
  }
}

.payment-wrapper:hover .shine-overlay {
  animation: shine 0.8s ease-in-out;
}
</style>