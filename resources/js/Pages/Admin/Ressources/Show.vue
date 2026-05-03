<template>
  <div class="space-y-6 p-6">
    <Head :title="ressource.title" />

    <div class="overflow-hidden rounded-3xl bg-gradient-to-r from-[rgb(141,61,79)] via-[rgb(118,91,123)] to-[rgb(89,151,172)] shadow-xl">
      <div class="grid gap-6 px-6 py-8 lg:grid-cols-[minmax(0,1fr)_280px] lg:items-end">
        <div class="text-white">
          <div class="flex flex-wrap items-center gap-3 text-sm text-white/80">
            <span>{{ formattedPublishedAt }}</span>
          </div>

          <h1 class="mt-4 max-w-4xl text-4xl font-semibold leading-tight">{{ ressource.title }}</h1>

          <div class="mt-6 flex flex-wrap items-center gap-3 text-sm text-white/75">
            <span>By {{ ressource.author?.name || 'Admin' }}</span>
            <span>Updated {{ formattedUpdatedAt }}</span>
          </div>
        </div>

        <div class="flex flex-col gap-3 rounded-3xl bg-white/12 p-4 backdrop-blur-sm">
          <a
            v-if="pdfUrl"
            :href="pdfUrl"
            target="_blank"
            rel="noopener"
            class="inline-flex items-center justify-center rounded-2xl bg-white px-4 py-3 text-sm font-semibold text-gray-900 transition hover:bg-gray-100"
          >
            Open PDF
          </a>
          <Link :href="route('admin.ressources.edit', ressource.id)" class="inline-flex items-center justify-center rounded-2xl bg-white px-4 py-3 text-sm font-semibold text-gray-900 transition hover:bg-gray-100">
            Edit Ressource
          </Link>
          <Link :href="route('admin.ressources.index')" class="inline-flex items-center justify-center rounded-2xl border border-white/30 px-4 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
            Back to Ressources
          </Link>
          <button type="button" class="inline-flex items-center justify-center rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 transition hover:bg-red-100" @click="confirmDelete">
            Delete Ressource
          </button>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.45fr)_360px]">
      <article class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
        <div class="p-6 lg:p-8">
          <h2 class="text-lg font-semibold text-gray-900">Description</h2>
          <div v-if="ressource.description" class="ressource-description mt-4 max-w-none" v-html="ressource.description"></div>
          <p v-else class="mt-4 text-base leading-7 text-gray-700">No description provided.</p>

          <div v-if="pdfUrl" class="mt-8 rounded-3xl border border-gray-200 bg-gray-50 p-5">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
              <div>
                <div class="text-sm font-semibold text-gray-900">Attached PDF</div>
                <p class="mt-1 text-sm text-gray-500">Open the stored file in a new tab.</p>
              </div>

              <a
                :href="pdfUrl"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center justify-center rounded-2xl bg-[rgb(141,61,79)] px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-[rgb(141,61,79)]/20 transition hover:opacity-90"
              >
                View PDF
              </a>
            </div>
          </div>
        </div>
      </article>

      <div class="space-y-6">
        <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
          <h2 class="text-lg font-semibold text-gray-900">Publishing Overview</h2>

          <dl class="mt-5 space-y-4">
            <div class="flex items-start justify-between gap-4">
              <dt class="text-sm font-medium text-gray-500">Author</dt>
              <dd class="text-right text-sm font-semibold text-gray-900">{{ ressource.author?.name || 'Admin' }}</dd>
            </div>
            <div class="flex items-start justify-between gap-4">
              <dt class="text-sm font-medium text-gray-500">Published</dt>
              <dd class="text-right text-sm font-semibold text-gray-900">{{ formattedPublishedAt }}</dd>
            </div>
            <div class="flex items-start justify-between gap-4">
              <dt class="text-sm font-medium text-gray-500">PDF</dt>
              <dd class="text-right text-sm font-semibold text-gray-900">{{ pdfUrl ? 'Available' : 'Not uploaded' }}</dd>
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
  ressource: { type: Object, required: true },
})

const ressource = computed(() => props.ressource)
const pdfUrl = computed(() => resolveStorageUrl(ressource.value.pdf) || '')
const formattedPublishedAt = computed(() => formatDateTime(ressource.value.published_at))
const formattedUpdatedAt = computed(() => formatDateTime(ressource.value.updated_at))

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
    title: 'Delete this ressource?',
    text: `"${ressource.value.title}" will be removed from the admin list.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Delete',
    cancelButtonText: 'Cancel',
    confirmButtonColor: 'rgb(141,61,79)',
  })

  if (!result.isConfirmed) return

  router.delete(route('admin.ressources.destroy', ressource.value.id), {
    preserveScroll: true,
  })
}
</script>

<style scoped>
.ressource-description {
  color: rgb(55 65 81);
  font-size: 1rem;
  line-height: 1.85;
}

.ressource-description :deep(h1),
.ressource-description :deep(h2),
.ressource-description :deep(h3) {
  color: rgb(17 24 39);
  font-weight: 700;
}

.ressource-description :deep(h1) {
  font-size: 2rem;
  line-height: 2.4rem;
  margin: 1.4rem 0 1rem;
}

.ressource-description :deep(h2) {
  font-size: 1.6rem;
  line-height: 2rem;
  margin: 1.3rem 0 0.9rem;
}

.ressource-description :deep(h3) {
  font-size: 1.3rem;
  line-height: 1.8rem;
  margin: 1.1rem 0 0.8rem;
}

.ressource-description :deep(p) {
  margin: 0.85rem 0;
}

.ressource-description :deep(ul),
.ressource-description :deep(ol) {
  margin: 1rem 0;
  padding-left: 1.5rem;
}

.ressource-description :deep(li[data-list='bullet']) {
  list-style-type: disc;
}

.ressource-description :deep(li[data-list='ordered']) {
  list-style-type: decimal;
}

.ressource-description :deep(li > .ql-ui) {
  display: none;
}

.ressource-description :deep(li) {
  margin: 0.35rem 0;
}

.ressource-description :deep(blockquote) {
  border-left: 4px solid rgb(89,151,172);
  color: rgb(75 85 99);
  margin: 1.25rem 0;
  padding-left: 1rem;
}

.ressource-description :deep(a) {
  color: rgb(14 116 144);
  text-decoration: underline;
}

.ressource-description :deep(.ql-align-center) {
  text-align: center;
}

.ressource-description :deep(.ql-align-right) {
  text-align: right;
}

.ressource-description :deep(.ql-align-justify) {
  text-align: justify;
}
</style>