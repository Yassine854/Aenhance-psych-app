<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed, onMounted, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import Navbar from '@/Components/Navbar.vue'
import Footer from '@/Components/Footer.vue'
import { resolveStorageUrl } from '@/utils/storage'

const props = defineProps({
  ressources: { type: Object, default: () => ({ data: [], links: [] }) },
  canLogin: { type: Boolean },
  canRegister: { type: Boolean },
  authUser: { type: Object },
})

const { t, locale } = useI18n()

function setLang(lang) {
  locale.value = lang
  localStorage.setItem('locale', lang)

  if (lang === 'ar') {
    document.documentElement.setAttribute('dir', 'rtl')
    document.documentElement.setAttribute('lang', 'ar')
    return
  }

  document.documentElement.setAttribute('dir', 'ltr')
  document.documentElement.setAttribute('lang', lang)
}

onMounted(() => {
  const savedLang = localStorage.getItem('locale') || locale.value
  setLang(savedLang)
})

const visibleRessources = computed(() => props.ressources?.data || [])
const paginationLinks = computed(() => props.ressources?.links || [])
const selectedRessourceId = ref(null)

watch(
  visibleRessources,
  (items) => {
    const firstId = items[0]?.id ?? null
    const hasCurrent = items.some((item) => item.id === selectedRessourceId.value)

    if (!hasCurrent) {
      selectedRessourceId.value = firstId
    }
  },
  { immediate: true },
)

const selectedRessource = computed(() => {
  const items = visibleRessources.value
  return items.find((item) => item.id === selectedRessourceId.value) || items[0] || null
})

const remainingRessources = computed(() => {
  if (!selectedRessource.value) return []
  return visibleRessources.value.filter((item) => item.id !== selectedRessource.value.id)
})

const currentPdfUrl = computed(() => {
  const pdf = selectedRessource.value?.pdf
  if (!pdf) return ''
  return `${resolveStorageUrl(pdf)}#toolbar=1&navpanes=0&view=FitH`
})

function formatDate(value) {
  if (!value) return t('ressources.unscheduled')

  const localeMap = {
    'ar': 'ar',
    'fr': 'fr',
    'en': 'en'
  }
  
  const currentLocale = localeMap[locale.value] || 'en'
  
  return new Intl.DateTimeFormat(currentLocale, {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  }).format(new Date(value))
}

function selectRessource(ressourceId) {
  selectedRessourceId.value = ressourceId
}
</script>

<template>
  <Head :title="`${t('ressources.banner.title')} - AEnhance`" />

  <Navbar
    :canLogin="canLogin"
    :canRegister="canRegister"
    :authUser="authUser"
  />

  <!-- Banner -->
  <div class="relative h-[180px] w-full overflow-hidden">
    <div
      class="absolute inset-0 bg-cover bg-center"
      style="background-image: url('/storage/banners/banner1.jpg')"
    >
      <div class="absolute inset-0 bg-gradient-to-r from-[#5997ac]/85 to-[#e8b4b8]/80"></div>
    </div>

    <div class="relative container mx-auto flex h-full flex-col justify-center px-4 sm:px-6 lg:px-8">
      <h1 class="mb-3 text-3xl font-bold text-white md:text-4xl">{{ t('ressources.banner.title') }}</h1>
      <div class="flex items-center gap-2 text-sm text-white">
        <Link href="/" class="hover:underline">{{ t('ressources.banner.home') }}</Link>
        <span>»</span>
        <span>{{ t('ressources.banner.current') }}</span>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="bg-gray-50 py-12 md:py-16">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 gap-8 lg:grid-cols-4">
        <!-- Main Column -->
        <div class="space-y-8 lg:col-span-3">
          <!-- Selected Resource -->
          <section v-if="selectedRessource" class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-100">
            <div class="border-b border-gray-100 px-6 py-5 md:px-8">
              <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">
                <span class="rounded-full bg-[#5997ac]/10 px-3 py-1 text-[#5997ac]">
                  {{ t('ressources.selected') }}
                </span>
                <span>{{ formatDate(selectedRessource.published_at) }}</span>
              </div>
              <h2 class="mt-4 text-3xl font-bold text-gray-900 md:text-4xl">{{ selectedRessource.title }}</h2>
              <div
                v-if="selectedRessource.description"
                class="ressource-content mt-4 max-w-3xl text-base text-gray-600 md:text-lg"
                v-html="selectedRessource.description"
              ></div>
            </div>

            <div class="p-6 md:p-8">
              <div class="flex flex-wrap items-center gap-4 border-b border-gray-100 pb-5 text-sm text-gray-500">
                <span class="font-medium text-gray-700">
                  {{ t('ressources.by') }} {{ selectedRessource.author?.name || t('ressources.team') }}
                </span>
                <a
                  v-if="selectedRessource.pdf"
                  :href="resolveStorageUrl(selectedRessource.pdf)"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="inline-flex items-center gap-2 rounded-full border border-[#5997ac]/20 bg-[#5997ac]/5 px-4 py-2 font-medium text-[#5997ac] transition hover:bg-[#5997ac]/10"
                >
                  {{ t('ressources.openNewTab') }}
                </a>
                <a
                  v-if="selectedRessource.pdf"
                  :href="resolveStorageUrl(selectedRessource.pdf)"
                  download
                  class="inline-flex items-center gap-2 rounded-full border border-[#af5166]/20 bg-[#af5166]/5 px-4 py-2 font-medium text-[#af5166] transition hover:bg-[#af5166]/10"
                >
                  {{ t('ressources.downloadPdf') }}
                </a>
              </div>

              <div v-if="selectedRessource.pdf" class="mt-8 overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-inner">
                <iframe
                  :src="currentPdfUrl"
                  :title="selectedRessource.title"
                  class="h-[820px] w-full bg-white"
                ></iframe>
              </div>

              <div v-else class="mt-8 rounded-3xl border border-dashed border-gray-300 bg-gray-50 p-10 text-center text-gray-500">
                {{ t('ressources.noPdf') }}
              </div>
            </div>
          </section>

          <!-- All Resources List -->
          <section class="space-y-6">
            <div>
              <h2 class="text-2xl font-bold text-[#5997ac] md:text-3xl">
                {{ t('ressources.allResources') }}
              </h2>
            </div>

            <!-- Empty State -->
            <div v-if="!visibleRessources.length" class="rounded-lg bg-white p-10 text-center shadow-sm">
              <h3 class="text-xl font-semibold text-gray-900">
                {{ t('ressources.noResources') }}
              </h3>
              <p class="mt-3 text-gray-600">
                {{ t('ressources.noResourcesDesc') }}
              </p>
            </div>

            <!-- Resource Articles -->
            <article
              v-for="ressource in remainingRessources"
              :key="ressource.id"
              class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-100"
            >
              <div class="p-6 md:p-8">
                <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">
                  <span>{{ formatDate(ressource.published_at) }}</span>
                  <span class="rounded-full bg-[#af5166]/10 px-3 py-1 text-[#af5166]">
                    {{ t('ressources.pdfResource') }}
                  </span>
                </div>

                <h3 class="mt-4 text-2xl font-bold text-gray-900">{{ ressource.title }}</h3>
                <p class="mt-3 text-sm text-gray-500">
                  {{ t('ressources.by') }} {{ ressource.author?.name || t('ressources.team') }}
                </p>
                <div
                  v-if="ressource.description"
                  class="ressource-content mt-4 border-l-4 border-[#5997ac] pl-4 text-base text-gray-600"
                  v-html="ressource.description"
                ></div>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                  <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-2xl bg-[#5997ac] px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-[#467891]"
                    @click="selectRessource(ressource.id)"
                  >
                    {{ t('ressources.preview') }}
                  </button>
                  <a
                    v-if="ressource.pdf"
                    :href="resolveStorageUrl(ressource.pdf)"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center justify-center rounded-2xl border border-gray-200 px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                  >
                    {{ t('ressources.openPdf') }}
                  </a>
                </div>
              </div>
            </article>

            <!-- Pagination -->
            <div v-if="paginationLinks.length > 3" class="flex flex-wrap items-center justify-between gap-4 rounded-lg bg-white px-6 py-4 shadow-sm">
              <div class="text-sm text-gray-600">
                {{ t('ressources.showing') }} {{ ressources.from || 0 }}-{{ ressources.to || 0 }} 
                {{ t('ressources.of') }} {{ ressources.total || 0 }} {{ t('ressources.resources') }}
              </div>
              <div class="flex flex-wrap items-center gap-2">
                <Link
                  v-for="(link, index) in paginationLinks"
                  :key="index"
                  :href="link.url || '#'"
                  preserve-scroll
                  :class="[
                    'inline-flex min-h-10 min-w-10 items-center justify-center rounded-xl border px-3 py-2 text-sm font-medium transition',
                    link.active
                      ? 'border-[#5997ac] bg-[#5997ac] text-white'
                      : link.url
                        ? 'border-gray-200 bg-white text-gray-700 hover:border-[#5997ac] hover:text-[#5997ac]'
                        : 'cursor-not-allowed border-gray-100 bg-gray-50 text-gray-300',
                  ]"
                >
                  <span v-html="link.label"></span>
                </Link>
              </div>
            </div>
          </section>
        </div>

        <!-- Sidebar -->
        <aside class="space-y-6 lg:col-span-1">
          <!-- Resource Overview -->
          <div class="rounded-lg bg-white p-6 shadow-sm">
            <h3 class="mb-4 border-b-2 border-[#5997ac] pb-3 text-xl font-bold text-[#5997ac]">
              {{ t('ressources.sidebar.overview') }}
            </h3>
            <div class="space-y-4 text-sm text-gray-700">
              <div class="rounded-2xl bg-gray-50 p-4">
                <div class="text-xs uppercase tracking-[0.18em] text-gray-500">
                  {{ t('ressources.sidebar.current') }}
                </div>
                <div class="mt-2 font-semibold text-gray-900">
                  {{ selectedRessource?.title || t('ressources.noResource') }}
                </div>
                <div v-if="selectedRessource" class="mt-2 text-gray-500">
                  {{ formatDate(selectedRessource.published_at) }}
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Resources -->
          <div class="rounded-lg bg-white p-6 shadow-sm">
            <h3 class="mb-4 border-b-2 border-[#5997ac] pb-3 text-xl font-bold text-[#5997ac]">
              {{ t('ressources.sidebar.recent') }}
            </h3>
            <ul class="space-y-4">
              <li 
                v-for="ressource in visibleRessources" 
                :key="ressource.id" 
                class="border-b border-gray-100 pb-4 last:border-b-0 last:pb-0"
              >
                <button 
                  type="button" 
                  class="block w-full text-left transition hover:opacity-80" 
                  @click="selectRessource(ressource.id)"
                >
                  <div class="text-sm font-semibold text-gray-900">
                    {{ ressource.title }}
                  </div>
                  <div class="mt-1 text-xs uppercase tracking-[0.18em] text-gray-500">
                    {{ formatDate(ressource.published_at) }}
                  </div>
                </button>
              </li>
            </ul>
          </div>
        </aside>
      </div>
    </div>
  </div>

  <Footer />
</template>

<style scoped>
[dir="rtl"] {
  text-align: right;
}

.ressource-content {
  color: rgb(31 41 55);
  font-size: 0.98rem;
  line-height: 1.95;
}

.ressource-content :deep(p) {
  margin: 0;
}

.ressource-content :deep(p + p) {
  margin-top: 1rem;
}

.ressource-content :deep(h2) {
  color: rgb(17 24 39);
  font-size: 1.7rem;
  font-weight: 700;
  line-height: 2.15rem;
  margin: 1.5rem 0 0.9rem;
}

.ressource-content :deep(h3) {
  color: rgb(31 41 55);
  font-size: 1.32rem;
  font-weight: 700;
  line-height: 1.85rem;
  margin: 1.25rem 0 0.8rem;
}

.ressource-content :deep(ul),
.ressource-content :deep(ol) {
  margin: 1rem 0;
  padding-left: 1.5rem;
}

.ressource-content :deep(li[data-list='bullet']) {
  list-style-type: disc;
}

.ressource-content :deep(li[data-list='ordered']) {
  list-style-type: decimal;
}

.ressource-content :deep(li > .ql-ui) {
  display: none;
}

.ressource-content :deep(li + li) {
  margin-top: 0.45rem;
}

.ressource-content :deep(blockquote) {
  border-left: 4px solid rgb(89 151 172);
  color: rgb(75 85 99);
  margin: 1.2rem 0;
  padding-left: 1rem;
}

.ressource-content :deep(a) {
  color: rgb(14 116 144);
  text-decoration: underline;
}

.ressource-content :deep(strong) {
  color: rgb(17 24 39);
  font-weight: 700;
}

.ressource-content :deep(img) {
  border-radius: 1.25rem;
  margin: 1.25rem 0;
  max-width: 100%;
}

iframe {
  display: block;
}

@media (max-width: 1023px) {
  iframe {
    height: 560px;
  }
}

@media (max-width: 767px) {
  .ressource-content :deep(h2) {
    font-size: 1.45rem;
    line-height: 1.95rem;
  }

  .ressource-content :deep(h3) {
    font-size: 1.2rem;
    line-height: 1.65rem;
  }
}
</style>