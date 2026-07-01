<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import Navbar from '@/Components/Navbar.vue'
import Footer from '@/Components/Footer.vue'
import { resolveStorageUrl } from '@/utils/storage'

const props = defineProps({
  blogs: { type: Object, default: () => ({ data: [], links: [] }) },
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

const visibleBlogs = computed(() => props.blogs?.data || [])
const featuredBlog = computed(() => visibleBlogs.value[0] || null)
const remainingBlogs = computed(() => visibleBlogs.value.slice(1))
const paginationLinks = computed(() => props.blogs?.links || [])

function formatDate(value) {
  if (!value) return t('blogs.unscheduled')

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

function articleAnchor(blogId) {
  return `#article-${blogId}`
}
</script>

<template>
  <Head :title="`${t('blogs.banner.title')} - AEnhance`" />

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
      <h1 class="mb-3 text-3xl font-bold text-white md:text-4xl">{{ t('blogs.banner.title') }}</h1>
      <div class="flex items-center gap-2 text-sm text-white">
        <Link href="/" class="hover:underline">{{ t('blogs.banner.home') }}</Link>
        <span>»</span>
        <span>{{ t('blogs.banner.current') }}</span>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div class="bg-gray-50 py-12 md:py-16">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 gap-8 lg:grid-cols-4">
        <!-- Main Column -->
        <div class="space-y-8 lg:col-span-3">
          <!-- Featured Article -->
          <section 
            v-if="featuredBlog" 
            :id="`article-${featuredBlog.id}`" 
            class="overflow-hidden rounded-lg bg-white shadow-sm"
          >
            <div class="border-b border-gray-100 px-6 py-5 md:px-8">
              <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">
                <span class="rounded-full bg-[#af5166]/10 px-3 py-1 text-[#af5166]">
                  {{ t('blogs.featured') }}
                </span>
                <span>{{ formatDate(featuredBlog.published_at) }}</span>
                <span v-if="featuredBlog.category">{{ featuredBlog.category }}</span>
              </div>
              <h2 class="mt-4 text-3xl font-bold text-gray-900 md:text-4xl">
                {{ featuredBlog.title }}
              </h2>
              <p class="mt-4 max-w-3xl text-base leading-8 text-gray-600 md:text-lg">
                {{ featuredBlog.excerpt || featuredBlog.content_preview }}
              </p>
            </div>

            <div class="p-6 md:p-8">
              <img
                v-if="featuredBlog.featured_image"
                :src="resolveStorageUrl(featuredBlog.featured_image)"
                :alt="featuredBlog.title"
                class="mb-8 h-[320px] w-full rounded-3xl object-cover shadow-sm"
              />

              <div class="flex flex-wrap items-center gap-4 border-b border-gray-100 pb-5 text-sm text-gray-500">
                <span class="font-medium text-gray-700">
                  {{ t('blogs.by') }} {{ featuredBlog.author?.name || t('blogs.team') }}
                </span>
              </div>

              <div class="blog-content mt-8" v-html="featuredBlog.content"></div>
            </div>
          </section>

          <!-- All Blogs List -->
          <section class="space-y-6">
            <div class="flex items-end justify-between gap-4">
              <div>
                <h2 class="text-2xl font-bold text-[#5997ac] md:text-3xl">
                  {{ t('blogs.allPublished') }}
                </h2>
              </div>
            </div>

            <!-- Empty State -->
            <div v-if="!visibleBlogs.length" class="rounded-lg bg-white p-10 text-center shadow-sm">
              <h3 class="text-xl font-semibold text-gray-900">
                {{ t('blogs.noArticles') }}
              </h3>
              <p class="mt-3 text-gray-600">
                {{ t('blogs.noArticlesDesc') }}
              </p>
            </div>

            <!-- Blog Articles -->
            <article
              v-for="blog in remainingBlogs"
              :id="`article-${blog.id}`"
              :key="blog.id"
              class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-100"
            >
              <div 
                class="grid grid-cols-1 gap-0 lg:grid-cols-[minmax(0,280px)_minmax(0,1fr)]" 
                :class="{ 'lg:grid-cols-1': !blog.featured_image }"
              >
                <div v-if="blog.featured_image" class="relative min-h-[220px] bg-gray-100 lg:min-h-full">
                  <img
                    :src="resolveStorageUrl(blog.featured_image)"
                    :alt="blog.title"
                    class="absolute inset-0 h-full w-full object-cover"
                  />
                </div>

                <div class="p-6 md:p-8">
                  <div class="flex flex-wrap items-center gap-3 text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">
                    <span>{{ formatDate(blog.published_at) }}</span>
                    <span 
                      v-if="blog.category" 
                      class="rounded-full bg-[#5997ac]/10 px-3 py-1 text-[#5997ac]"
                    >
                      {{ blog.category }}
                    </span>
                  </div>

                  <h3 class="mt-4 text-2xl font-bold text-gray-900">
                    {{ blog.title }}
                  </h3>
                  <p class="mt-3 text-sm text-gray-500">
                    {{ t('blogs.by') }} {{ blog.author?.name || t('blogs.team') }}
                  </p>
                  <p 
                    v-if="blog.excerpt" 
                    class="mt-4 border-l-4 border-[#af5166] pl-4 text-base leading-7 text-gray-600"
                  >
                    {{ blog.excerpt }}
                  </p>

                  <div class="blog-content mt-8" v-html="blog.content"></div>
                </div>
              </div>
            </article>

            <!-- Pagination -->
            <div 
              v-if="paginationLinks.length > 3" 
              class="flex flex-wrap items-center justify-between gap-4 rounded-lg bg-white px-6 py-4 shadow-sm"
            >
              <div class="text-sm text-gray-600">
                {{ t('blogs.showing') }} {{ blogs.from || 0 }}-{{ blogs.to || 0 }} 
                {{ t('blogs.of') }} {{ blogs.total || 0 }} {{ t('blogs.blogs') }}
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
          <!-- Blog Overview -->
          <div class="rounded-lg bg-white p-6 shadow-sm">
            <h3 class="mb-4 border-b-2 border-[#5997ac] pb-3 text-xl font-bold text-[#5997ac]">
              {{ t('blogs.sidebar.overview') }}
            </h3>
            <div class="space-y-4 text-sm text-gray-700">
              <div class="rounded-2xl bg-gray-50 p-4">
                <div class="text-xs uppercase tracking-[0.18em] text-gray-500">
                  {{ t('blogs.sidebar.latest') }}
                </div>
                <div class="mt-2 font-semibold text-gray-900">
                  {{ featuredBlog?.title || t('blogs.noArticle') }}
                </div>
                <div v-if="featuredBlog" class="mt-2 text-gray-500">
                  {{ formatDate(featuredBlog.published_at) }}
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Articles -->
          <div class="rounded-lg bg-white p-6 shadow-sm">
            <h3 class="mb-4 border-b-2 border-[#5997ac] pb-3 text-xl font-bold text-[#5997ac]">
              {{ t('blogs.sidebar.recent') }}
            </h3>
            <ul class="space-y-4">
              <li 
                v-for="blog in visibleBlogs.slice(0, 5)" 
                :key="blog.id" 
                class="border-b border-gray-100 pb-4 last:border-b-0 last:pb-0"
              >
                <a :href="articleAnchor(blog.id)" class="block transition hover:opacity-80">
                  <div class="text-sm font-semibold text-gray-900">
                    {{ blog.title }}
                  </div>
                  <div class="mt-1 text-xs uppercase tracking-[0.18em] text-gray-500">
                    {{ formatDate(blog.published_at) }}
                  </div>
                </a>
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

.blog-content {
  color: rgb(31 41 55);
  font-size: 0.98rem;
  line-height: 1.95;
}

.blog-content :deep(p) {
  margin: 0;
}

.blog-content :deep(p + p) {
  margin-top: 1rem;
}

.blog-content :deep(h2) {
  color: rgb(17 24 39);
  font-size: 1.7rem;
  font-weight: 700;
  line-height: 2.15rem;
  margin: 1.5rem 0 0.9rem;
}

.blog-content :deep(h3) {
  color: rgb(31 41 55);
  font-size: 1.32rem;
  font-weight: 700;
  line-height: 1.85rem;
  margin: 1.25rem 0 0.8rem;
}

.blog-content :deep(ul),
.blog-content :deep(ol) {
  margin: 1rem 0;
  padding-left: 1.5rem;
}

.blog-content :deep(li[data-list='bullet']) {
  list-style-type: disc;
}

.blog-content :deep(li[data-list='ordered']) {
  list-style-type: decimal;
}

.blog-content :deep(li > .ql-ui) {
  display: none;
}

.blog-content :deep(li + li) {
  margin-top: 0.45rem;
}

.blog-content :deep(blockquote) {
  border-left: 4px solid rgb(89 151 172);
  color: rgb(75 85 99);
  margin: 1.2rem 0;
  padding-left: 1rem;
}

.blog-content :deep(a) {
  color: rgb(14 116 144);
  text-decoration: underline;
}

.blog-content :deep(strong) {
  color: rgb(17 24 39);
  font-weight: 700;
}

.blog-content :deep(img) {
  border-radius: 1.25rem;
  margin: 1.25rem 0;
  max-width: 100%;
}

@media (max-width: 767px) {
  .blog-content :deep(h2) {
    font-size: 1.45rem;
    line-height: 1.95rem;
  }

  .blog-content :deep(h3) {
    font-size: 1.2rem;
    line-height: 1.65rem;
  }
}
</style>