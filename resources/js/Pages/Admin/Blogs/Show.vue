<template>
  <div class="space-y-6 p-6">
    <Head :title="blog.title" />

    <div class="overflow-hidden rounded-3xl bg-gradient-to-r from-[rgb(141,61,79)] via-[rgb(118,91,123)] to-[rgb(89,151,172)] shadow-xl">
      <div class="grid gap-6 px-6 py-8 lg:grid-cols-[minmax(0,1fr)_280px] lg:items-end">
        <div class="text-white">
          <div class="flex flex-wrap items-center gap-3 text-sm text-white/80">
            <span>{{ formattedPublishedAt }}</span>
            <span v-if="blog.category">{{ blog.category }}</span>
          </div>

          <h1 class="mt-4 max-w-4xl text-4xl font-semibold leading-tight">{{ blog.title }}</h1>

          <div class="mt-6 flex flex-wrap items-center gap-3 text-sm text-white/75">
            <span>By {{ blog.author?.name || 'Admin' }}</span>
            <span>Updated {{ formattedUpdatedAt }}</span>
          </div>
        </div>

        <div class="flex flex-col gap-3 rounded-3xl bg-white/12 p-4 backdrop-blur-sm">
          <Link :href="route('admin.blogs.edit', blog.id)" class="inline-flex items-center justify-center rounded-2xl bg-white px-4 py-3 text-sm font-semibold text-gray-900 transition hover:bg-gray-100">
            Edit Blog
          </Link>
          <Link :href="route('admin.blogs.index')" class="inline-flex items-center justify-center rounded-2xl border border-white/30 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
            Back to Blogs
          </Link>
          <button type="button" class="inline-flex items-center justify-center rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 transition hover:bg-red-100" @click="confirmDelete">
            Delete Blog
          </button>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.45fr)_360px]">
      <article class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
        <img v-if="coverImage" :src="coverImage" :alt="blog.title" class="max-h-[420px] w-full object-cover" />

        <div class="p-6 lg:p-8">
          <div class="quill-renderer max-w-none" v-html="blog.content"></div>
        </div>
      </article>

      <div class="space-y-6">
        <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
          <h2 class="text-lg font-semibold text-gray-900">Publishing Overview</h2>

          <dl class="mt-5 space-y-4">
            <div class="flex items-start justify-between gap-4">
              <dt class="text-sm font-medium text-gray-500">Author</dt>
              <dd class="text-right text-sm font-semibold text-gray-900">{{ blog.author?.name || 'Admin' }}</dd>
            </div>
            <div class="flex items-start justify-between gap-4">
              <dt class="text-sm font-medium text-gray-500">Published</dt>
              <dd class="text-right text-sm font-semibold text-gray-900">{{ formattedPublishedAt }}</dd>
            </div>
          </dl>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { resolveStorageUrl } from '@/utils/storage'
import Swal from 'sweetalert2'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  blog: { type: Object, required: true },
})

const blog = computed(() => props.blog)
const coverImage = computed(() => resolveStorageUrl(blog.value.featured_image) || '')
const formattedPublishedAt = computed(() => formatDateTime(blog.value.published_at))
const formattedUpdatedAt = computed(() => formatDateTime(blog.value.updated_at))

function formatDateTime(value) {
  if (!value) return '—'

  try {
    return new Intl.DateTimeFormat(undefined, {
      year: 'numeric',
      month: 'short',
      day: '2-digit',
    }).format(new Date(value))
  } catch {
    return String(value)
  }
}

async function confirmDelete() {
  const result = await Swal.fire({
    title: 'Delete this blog?',
    text: `"${blog.value.title}" will be removed from the admin list.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Delete',
    cancelButtonText: 'Cancel',
    confirmButtonColor: 'rgb(141,61,79)',
  })

  if (!result.isConfirmed) return

  router.delete(route('admin.blogs.destroy', blog.value.id), {
    preserveScroll: true,
  })
}
</script>

<style scoped>
.quill-renderer {
  color: rgb(55 65 81);
  font-size: 1rem;
  line-height: 1.85;
}

.quill-renderer :deep(h1),
.quill-renderer :deep(h2),
.quill-renderer :deep(h3) {
  color: rgb(17 24 39);
  font-weight: 700;
}

.quill-renderer :deep(h1) {
  font-size: 2rem;
  line-height: 2.4rem;
  margin: 1.4rem 0 1rem;
}

.quill-renderer :deep(h2) {
  font-size: 1.6rem;
  line-height: 2rem;
  margin: 1.3rem 0 0.9rem;
}

.quill-renderer :deep(h3) {
  font-size: 1.3rem;
  line-height: 1.8rem;
  margin: 1.1rem 0 0.8rem;
}

.quill-renderer :deep(p) {
  margin: 0.85rem 0;
}

.quill-renderer :deep(ul),
.quill-renderer :deep(ol) {
  margin: 1rem 0;
  padding-left: 1.5rem;
}

.quill-renderer :deep(li) {
  margin: 0.35rem 0;
}

.quill-renderer :deep(blockquote) {
  border-left: 4px solid rgb(89,151,172);
  color: rgb(75 85 99);
  margin: 1.25rem 0;
  padding-left: 1rem;
}

.quill-renderer :deep(a) {
  color: rgb(14 116 144);
  text-decoration: underline;
}

.quill-renderer :deep(.ql-align-center) {
  text-align: center;
}

.quill-renderer :deep(.ql-align-right) {
  text-align: right;
}

.quill-renderer :deep(.ql-align-justify) {
  text-align: justify;
}
</style>