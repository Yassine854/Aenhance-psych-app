<script setup>
import { Head, Link } from "@inertiajs/vue3";
import { useI18n } from "vue-i18n";
import { onMounted } from "vue";
import Navbar from "@/Components/Navbar.vue";
import Footer from "@/Components/Footer.vue";

defineProps({
  canLogin: { type: Boolean },
  canRegister: { type: Boolean },
  authUser: { type: Object },
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
</script>

<template>
  <Head :title="`${t('services.banner.title')} - AEnhance`" />

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
        {{ t("services.banner.title") }}
      </h1>
      <div class="flex items-center gap-2 text-white text-sm">
        <a href="/" class="hover:underline">{{ t("services.banner.home") }}</a>
        <span>»</span>
        <span>{{ t("services.banner.current") }}</span>
      </div>
    </div>
  </div>

  <!-- Content -->
  <div class="bg-gray-50 py-12 md:py-16">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="max-w-4xl mx-auto text-center mb-10">
        <h2 class="text-2xl md:text-3xl font-bold text-[#5997ac]">
          {{ t("services.title") }}
        </h2>
        <p class="mt-3 text-gray-700 leading-relaxed">
          {{ t("services.subtitle") }}
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Consultation Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <div class="p-6">
            <div class="flex items-start justify-between gap-4">
              <div>
                <h3 class="text-xl font-semibold text-gray-900">{{ t("services.consultation.title") }}</h3>
                <p class="mt-2 text-gray-700 leading-relaxed">
                  {{ t("services.consultation.intro") }}
                </p>
              </div>
              <div class="flex-shrink-0 h-12 w-12 rounded-full bg-[#5997ac]/10 text-[#5997ac] flex items-center justify-center font-bold">
                C
              </div>
            </div>

            <div class="mt-6">
              <Link
                :href="route('services.consultation')"
                class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-[#5997ac] text-white text-sm font-medium hover:bg-[#467891] transition"
              >
                {{ t("services.consultation.cta") }}
              </Link>
            </div>
          </div>
        </div>

        <!-- Workshop Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <div class="p-6">
            <div class="flex items-start justify-between gap-4">
              <div>
                <h3 class="text-xl font-semibold text-gray-900">{{ t("services.workshop.title") }}</h3>
                <p class="mt-2 text-gray-700 leading-relaxed">
                  {{ t("services.workshop.intro") }}
                </p>
              </div>
              <div class="flex-shrink-0 h-12 w-12 rounded-full bg-[#af5166]/10 text-[#af5166] flex items-center justify-center font-bold">
                W
              </div>
            </div>

            <div class="mt-6">
              <div class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-gray-100 text-gray-600 text-sm font-medium cursor-not-allowed">
                {{ t("services.workshop.cta") }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="max-w-4xl mx-auto mt-10 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900">{{ t("services.note.title") }}</h3>
        <p class="mt-2 text-gray-700">{{ t("services.note.body") }}</p>
      </div>
    </div>
  </div>

  <Footer />
</template>
