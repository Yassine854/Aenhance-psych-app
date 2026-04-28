<template>
  <form @submit.prevent="$emit('submit')" class="space-y-6">
    <div class="overflow-hidden rounded-3xl bg-gradient-to-r from-[rgb(141,61,79)] via-[rgb(118,91,123)] to-[rgb(89,151,172)] shadow-xl">
      <div class="px-6 py-6 text-white">
        <h1 class="mt-2 text-3xl font-semibold leading-tight">{{ pageTitle }}</h1>
        <p class="mt-2 max-w-2xl text-sm text-white/80">{{ pageDescription }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.5fr)_380px]">
      <div class="space-y-6">
        <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
          <div class="mb-5">
            <h2 class="text-lg font-semibold text-gray-900">Ressource Details</h2>
            <p class="mt-1 text-sm text-gray-500">Add a clear title and a short description so admins can identify the document quickly.</p>
          </div>

          <div class="space-y-5">
            <div>
              <label class="text-sm font-medium text-gray-700">Title <span class="text-red-500">*</span></label>
              <input
                v-model="form.title"
                type="text"
                class="mt-2 block w-full rounded-2xl border-gray-300 px-4 py-3 text-lg font-medium shadow-sm focus:border-[rgb(89,151,172)] focus:ring-[rgb(89,151,172)]"
                placeholder="Workbook, guide, checklist..."
              />
              <p v-if="form.errors.title" class="mt-2 text-sm text-red-600">{{ form.errors.title }}</p>
            </div>

            <div>
              <div class="flex items-center justify-between gap-3">
                <label class="text-sm font-medium text-gray-700">Description</label>
                <span class="text-xs font-medium text-gray-400">{{ descriptionLength }}/3000</span>
              </div>
              <div class="mt-2">
                <RichTextEditor
                  v-model="form.description"
                  placeholder="Add a short summary of what the PDF contains and when it should be used."
                  :error="form.errors.description"
                />
              </div>
            </div>
          </div>
        </section>
      </div>

      <div class="sidebar-scroll space-y-6 xl:sticky xl:top-6 xl:self-start xl:max-h-[calc(100vh-3rem)] xl:overflow-y-auto xl:pr-1">
        <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
          <div>
            <h2 class="text-lg font-semibold text-gray-900">Publication</h2>
            <p class="mt-1 text-sm text-gray-500">Set an optional publication date for ordering and visibility.</p>
          </div>

          <div class="mt-5 space-y-5">
            <div>
              <label class="text-sm font-medium text-gray-700">Publish Date</label>
              <input
                v-model="form.published_at"
                type="date"
                class="mt-2 block w-full rounded-2xl border-gray-300 px-4 py-3 shadow-sm focus:border-[rgb(89,151,172)] focus:ring-[rgb(89,151,172)]"
              />
              <p class="mt-2 text-xs text-gray-500">Leave empty to keep the ressource unpublished.</p>
              <p v-if="form.errors.published_at" class="mt-2 text-sm text-red-600">{{ form.errors.published_at }}</p>
            </div>
          </div>
        </section>

        <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
          <div>
            <h2 class="text-lg font-semibold text-gray-900">PDF File</h2>
            <p class="mt-1 text-sm text-gray-500">Upload the PDF document that users or admins will open from this ressource entry.</p>
          </div>

          <div class="mt-5 space-y-4">
            <div
              class="rounded-3xl border-2 border-dashed border-gray-300 bg-gray-50 p-5 transition hover:border-[rgb(89,151,172)] hover:bg-gray-100"
              @dragover.prevent
              @drop.prevent="handleDrop"
            >
              <input ref="fileInput" type="file" accept="application/pdf,.pdf" class="hidden" @change="handleFileChange" />

              <div v-if="resolvedPdfUrl" class="space-y-4">
                <div class="flex items-start gap-3 rounded-2xl bg-white p-4 shadow-sm">
                  <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-red-700">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                      <path fill-rule="evenodd" d="M6.75 2.25A2.25 2.25 0 0 0 4.5 4.5v15A2.25 2.25 0 0 0 6.75 21.75h10.5a2.25 2.25 0 0 0 2.25-2.25V8.56a2.25 2.25 0 0 0-.659-1.591l-3.81-3.81a2.25 2.25 0 0 0-1.591-.659H6.75Zm7.5 1.81 3.69 3.69h-2.94a.75.75 0 0 1-.75-.75V4.06Z" clip-rule="evenodd" />
                    </svg>
                  </div>

                  <div class="min-w-0 flex-1">
                    <div class="truncate text-sm font-semibold text-gray-900">{{ pdfLabel }}</div>
                    <div class="mt-1 text-xs text-gray-500">PDF ready for preview or replacement</div>
                  </div>
                </div>

                <div class="flex flex-wrap gap-3">
                  <a
                    :href="resolvedPdfUrl"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center justify-center rounded-2xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                  >
                    Open PDF
                  </a>
                  <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-2xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                    @click="fileInput?.click()"
                  >
                    Replace PDF
                  </button>
                  <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-2xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-100"
                    @click="removePdf"
                  >
                    Remove PDF
                  </button>
                </div>
              </div>

              <div v-else class="text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-white shadow-sm">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-7 w-7 text-gray-500">
                    <path fill-rule="evenodd" d="M6.75 2.25A2.25 2.25 0 0 0 4.5 4.5v15A2.25 2.25 0 0 0 6.75 21.75h10.5a2.25 2.25 0 0 0 2.25-2.25V8.56a2.25 2.25 0 0 0-.659-1.591l-3.81-3.81a2.25 2.25 0 0 0-1.591-.659H6.75Zm7.5 1.81 3.69 3.69h-2.94a.75.75 0 0 1-.75-.75V4.06Z" clip-rule="evenodd" />
                  </svg>
                </div>
                <div class="mt-4 text-sm font-medium text-gray-700">Drop a PDF here or choose one from your device</div>
                <p class="mt-2 text-xs text-gray-500">Maximum file size: 20MB</p>
                <button
                  type="button"
                  class="mt-4 inline-flex items-center justify-center rounded-2xl bg-[rgb(141,61,79)] px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-[rgb(141,61,79)]/20 transition hover:opacity-90"
                  @click="fileInput?.click()"
                >
                  Choose PDF
                </button>
              </div>
            </div>

            <p v-if="form.errors.pdf" class="text-sm text-red-600">{{ form.errors.pdf }}</p>
          </div>
        </section>

        <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
          <div>
            <h2 class="text-lg font-semibold text-gray-900">Actions</h2>
            <p class="mt-1 text-sm text-gray-500">Save the ressource now and update it later if the file changes.</p>
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
import RichTextEditor from '../Blogs/RichTextEditor.vue'

const props = defineProps({
  form: { type: Object, required: true },
  pageTitle: { type: String, required: true },
  pageDescription: { type: String, required: true },
  submitLabel: { type: String, default: 'Save Ressource' },
  submittingLabel: { type: String, default: 'Saving...' },
  cancelHref: { type: String, required: true },
  existingPdfUrl: { type: String, default: '' },
})

defineEmits(['submit'])

const fileInput = ref(null)
const pdfObjectUrl = ref('')

const descriptionLength = computed(() => stripHtml(props.form.description || '').length)

const resolvedPdfUrl = computed(() => {
  if (pdfObjectUrl.value) return pdfObjectUrl.value
  if (props.form.remove_pdf) return ''
  return resolveStorageUrl(props.existingPdfUrl) || ''
})

const pdfLabel = computed(() => {
  if (props.form.pdf instanceof File) return props.form.pdf.name
  const existingUrl = props.existingPdfUrl || ''
  if (!existingUrl) return 'Uploaded PDF'

  const normalized = String(existingUrl).split('/').filter(Boolean)
  return normalized[normalized.length - 1] || 'Uploaded PDF'
})

function updatePdfObjectUrl(file) {
  if (pdfObjectUrl.value) {
    URL.revokeObjectURL(pdfObjectUrl.value)
    pdfObjectUrl.value = ''
  }

  if (file instanceof File) {
    pdfObjectUrl.value = URL.createObjectURL(file)
  }
}

function setPdf(file) {
  if (!(file instanceof File)) return
  const mimeType = String(file.type || '').toLowerCase()
  if (mimeType && mimeType !== 'application/pdf') return
  if (!String(file.name || '').toLowerCase().endsWith('.pdf')) return

  props.form.pdf = file
  props.form.remove_pdf = false
  updatePdfObjectUrl(file)
}

function handleFileChange(event) {
  const file = event.target?.files?.[0]
  setPdf(file)
}

function handleDrop(event) {
  const file = event.dataTransfer?.files?.[0]
  setPdf(file)
}

function removePdf() {
  props.form.pdf = null
  props.form.remove_pdf = true
  updatePdfObjectUrl(null)
  if (fileInput.value) fileInput.value.value = ''
}

function stripHtml(value) {
  return String(value || '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim()
}

onBeforeUnmount(() => {
  if (pdfObjectUrl.value) {
    URL.revokeObjectURL(pdfObjectUrl.value)
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