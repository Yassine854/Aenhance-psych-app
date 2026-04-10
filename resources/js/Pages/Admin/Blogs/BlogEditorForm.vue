<template>
  <form @submit.prevent="$emit('submit')" class="space-y-6">
    <div class="overflow-hidden rounded-3xl bg-gradient-to-r from-[rgb(141,61,79)] via-[rgb(118,91,123)] to-[rgb(89,151,172)] shadow-xl">
      <div class="px-6 py-6 text-white">
        <h1 class="mt-2 text-3xl font-semibold leading-tight">{{ pageTitle }}</h1>
        <p class="mt-2 max-w-2xl text-sm text-white/80">{{ pageDescription }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.6fr)_380px]">
      <div class="space-y-6">
        <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
          <div class="mb-5">
            <div>
              <h2 class="text-lg font-semibold text-gray-900">Article Details</h2>
              <p class="mt-1 text-sm text-gray-500">Lead with a clear title, a concise summary, and structured article content.</p>
            </div>
          </div>

          <div class="space-y-5">
            <div>
              <label class="text-sm font-medium text-gray-700">Title <span class="text-red-500">*</span></label>
              <input
                v-model="form.title"
                type="text"
                class="mt-2 block w-full rounded-2xl border-gray-300 px-4 py-3 text-lg font-medium shadow-sm focus:border-[rgb(89,151,172)] focus:ring-[rgb(89,151,172)]"
                placeholder="Write a clear, specific headline"
              />
              <p v-if="form.errors.title" class="mt-2 text-sm text-red-600">{{ form.errors.title }}</p>
            </div>

            <div>
              <div class="flex items-center justify-between gap-3">
                <label class="text-sm font-medium text-gray-700">Excerpt</label>
                <span class="text-xs font-medium text-gray-400">{{ excerptLength }}/1000</span>
              </div>
              <textarea
                v-model="form.excerpt"
                rows="4"
                class="mt-2 block w-full rounded-2xl border-gray-300 px-4 py-3 shadow-sm focus:border-[rgb(89,151,172)] focus:ring-[rgb(89,151,172)]"
                placeholder="Short summary used in listings, previews, and share cards"
              />
              <p v-if="form.errors.excerpt" class="mt-2 text-sm text-red-600">{{ form.errors.excerpt }}</p>
            </div>
          </div>
        </section>

        <section class="rounded-3xl border border-gray-200 bg-white shadow-sm">
          <div class="border-b border-gray-200 px-6 py-5">
            <h2 class="text-lg font-semibold text-gray-900">Content</h2>
            <p class="mt-1 text-sm text-gray-500">Write and format your article with the rich text editor.</p>
          </div>

          <div class="p-6">
            <RichTextEditor v-model="form.content" :error="form.errors.content" />
          </div>
        </section>
      </div>

      <div class="sidebar-scroll space-y-6 xl:sticky xl:top-6 xl:self-start xl:max-h-[calc(100vh-3rem)] xl:overflow-y-auto xl:pr-1">
        <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
          <div>
            <h2 class="text-lg font-semibold text-gray-900">Publishing</h2>
            <p class="mt-1 text-sm text-gray-500">Set the article URL, category, and publication date.</p>
          </div>

          <div class="mt-5 space-y-5">
            <div>
              <label class="text-sm font-medium text-gray-700">Publish Date</label>
              <input
                v-model="form.published_at"
                type="date"
                class="mt-2 block w-full rounded-2xl border-gray-300 px-4 py-3 shadow-sm focus:border-[rgb(89,151,172)] focus:ring-[rgb(89,151,172)]"
              />
              <p class="mt-2 text-xs text-gray-500">Leave empty to keep the article unpublished.</p>
              <p v-if="form.errors.published_at" class="mt-2 text-sm text-red-600">{{ form.errors.published_at }}</p>
            </div>

            <div>
              <label class="text-sm font-medium text-gray-700">Category</label>
              <input
                v-model="form.category"
                type="text"
                class="mt-2 block w-full rounded-2xl border-gray-300 px-4 py-3 shadow-sm focus:border-[rgb(89,151,172)] focus:ring-[rgb(89,151,172)]"
                placeholder="Mental Health, Parenting, Stress Management..."
              />
              <p v-if="form.errors.category" class="mt-2 text-sm text-red-600">{{ form.errors.category }}</p>
            </div>

          </div>
        </section>

        <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
          <div>
            <h2 class="text-lg font-semibold text-gray-900">Cover Image</h2>
            <p class="mt-1 text-sm text-gray-500">Upload a strong visual that frames the article and improves list views.</p>
          </div>

          <div class="mt-5 space-y-4">
            <div
              class="group relative h-[340px] overflow-hidden rounded-3xl border-2 border-dashed border-gray-300 bg-gray-50 transition hover:border-[rgb(89,151,172)] hover:bg-gray-100"
              @click="fileInput?.click()"
              @dragover.prevent
              @drop.prevent="handleDrop"
            >
              <input ref="fileInput" type="file" accept="image/*" class="hidden" @change="handleFileChange" />

              <div v-if="imageResolved" class="relative h-full w-full">
                <img :src="imageResolved" alt="Cover preview" class="h-full w-full object-cover" />
                <button
                  type="button"
                  class="absolute right-4 top-4 inline-flex h-10 w-10 items-center justify-center rounded-full bg-red-600 text-white shadow-lg shadow-red-900/20 transition hover:bg-red-700"
                  @click.stop="removeImage"
                  aria-label="Remove cover image"
                  title="Remove cover image"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </button>
                <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent px-4 py-4 text-white">
                  <div class="text-xs font-medium uppercase tracking-[0.2em] text-white/70">Cover Preview</div>
                  <div class="mt-1 text-sm">Click to replace image</div>
                </div>
              </div>

              <div v-else class="flex h-full flex-col items-center justify-center px-6 text-center">
                <div class="rounded-full bg-white p-4 shadow-sm">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-7 w-7 text-gray-500">
                    <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 0 1 2.25-2.25h16.5A2.25 2.25 0 0 1 22.5 6v12a2.25 2.25 0 0 1-2.25 2.25H3.75A2.25 2.25 0 0 1 1.5 18V6Zm4.5 2.25a.75.75 0 0 0 0 1.5h.008a.75.75 0 0 0 0-1.5H6Zm1.72 8.47 2.86-3.814a.75.75 0 0 1 1.14-.063l2.474 2.474 2.86-3.814a.75.75 0 0 1 1.2.9l-3.375 4.5a.75.75 0 0 1-1.11.074l-2.528-2.528-2.79 3.72a.75.75 0 1 1-1.2-.9Z" clip-rule="evenodd" />
                  </svg>
                </div>
                <div class="mt-4 text-sm font-medium text-gray-700">Drop an image here or click to browse</div>
                <p class="mt-2 max-w-xs text-xs text-gray-500">Recommended: landscape image, at least 1600px wide, under 5MB.</p>
              </div>
            </div>

            <div class="flex min-h-[28px] items-center justify-between gap-3">
              <span class="inline-block h-5 w-20"></span>
            </div>

            <p v-if="form.errors.featured_image" class="text-sm text-red-600">{{ form.errors.featured_image }}</p>
          </div>
        </section>

        <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
          <div class="flex items-center justify-between gap-3">
            <div>
              <h2 class="text-lg font-semibold text-gray-900">Actions</h2>
              <p class="mt-1 text-sm text-gray-500">Save now and continue refining the article.</p>
            </div>
          </div>

          <div class="mt-5 flex flex-col gap-3 sm:flex-row">
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex items-center justify-center rounded-2xl bg-[rgb(141,61,79)] px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-[rgb(141,61,79)]/20 transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
            >
              {{ form.processing ? submittingLabel : submitLabel }}
            </button>
            <Link
              :href="cancelHref"
              class="inline-flex items-center justify-center rounded-2xl border border-gray-200 px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
            >
              Cancel
            </Link>
          </div>
        </section>
      </div>
    </div>
  </form>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, ref } from 'vue'
import { resolveStorageUrl } from '@/utils/storage'
import RichTextEditor from './RichTextEditor.vue'

const props = defineProps({
  form: { type: Object, required: true },
  mode: { type: String, default: 'create' },
  pageTitle: { type: String, required: true },
  pageDescription: { type: String, required: true },
  submitLabel: { type: String, default: 'Save Blog' },
  submittingLabel: { type: String, default: 'Saving...' },
  cancelHref: { type: String, required: true },
  existingImageUrl: { type: String, default: '' },
})

defineEmits(['submit'])

const fileInput = ref(null)
const imageObjectUrl = ref('')

const excerptLength = computed(() => String(props.form.excerpt || '').length)

const imageResolved = computed(() => {
  if (imageObjectUrl.value) return imageObjectUrl.value
  if (props.form.remove_featured_image) return ''
  return resolveStorageUrl(props.existingImageUrl) || ''
})

function updateImageObjectUrl(file) {
  if (imageObjectUrl.value) {
    URL.revokeObjectURL(imageObjectUrl.value)
    imageObjectUrl.value = ''
  }

  if (file instanceof File) {
    imageObjectUrl.value = URL.createObjectURL(file)
  }
}

function setFeaturedImage(file) {
  if (!(file instanceof File) || !file.type.startsWith('image/')) return
  props.form.featured_image = file
  props.form.remove_featured_image = false
  updateImageObjectUrl(file)
}

function handleFileChange(event) {
  const file = event.target?.files?.[0]
  setFeaturedImage(file)
}

function handleDrop(event) {
  const file = event.dataTransfer?.files?.[0]
  setFeaturedImage(file)
}

function removeImage() {
  props.form.featured_image = null
  props.form.remove_featured_image = true
  updateImageObjectUrl(null)
  if (fileInput.value) fileInput.value.value = ''
}

onBeforeUnmount(() => {
  if (imageObjectUrl.value) {
    URL.revokeObjectURL(imageObjectUrl.value)
  }
})
</script>

<style scoped>
.sidebar-scroll {
  scrollbar-color: rgb(89,151,172) transparent;
  scrollbar-width: thin;
}

.sidebar-scroll::-webkit-scrollbar {
  width: 10px;
}

.sidebar-scroll::-webkit-scrollbar-track {
  background: transparent;
}

.sidebar-scroll::-webkit-scrollbar-thumb {
  background: rgb(89,151,172);
  border-radius: 9999px;
  border: 2px solid transparent;
  background-clip: padding-box;
}

.sidebar-scroll::-webkit-scrollbar-thumb:hover {
  background: rgba(89,151,172,0.85);
  background-clip: padding-box;
}
</style>