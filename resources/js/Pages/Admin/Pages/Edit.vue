<template>
  <div class="p-6">
    <header class="flex items-start justify-between gap-4 mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Edit Page</h1>
        <p class="text-sm text-gray-500 mt-1">Update the multilingual page content.</p>
      </div>
    </header>

    <form @submit.prevent="submit" class="grid grid-cols-1 gap-6">
      <div v-if="Object.keys(form.errors || {}).length" class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4">
        <div class="flex items-start justify-between">
          <div>
            <div class="flex items-start gap-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-700 mt-0.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.516 9.8c.75 1.33-.213 2.99-1.742 2.99H4.483c-1.53 0-2.492-1.66-1.742-2.99l5.516-9.8zM11 13a1 1 0 10-2 0 1 1 0 002 0zm-1-8a1 1 0 00-.993.883L9 6v4a1 1 0 001.993.117L11 10V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
              <div>
                <div class="text-sm font-medium text-red-800">Please fix the following errors</div>
                <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                  <li v-for="(msg, key) in form.errors" :key="key">{{ msg }}</li>
                </ul>
              </div>
            </div>
          </div>
          <button type="button" @click="Object.keys(form.errors).forEach(k => delete form.errors[k])" class="text-red-700/80 hover:text-red-800">✕</button>
        </div>
      </div>

      <div class="rounded-2xl border bg-white p-4 shadow-sm">
        <label class="text-xs font-medium text-gray-600">Section Key</label>
        <input readonly :value="props.page.key" class="mt-2 w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700" />
        <p class="mt-2 text-xs text-gray-500">Section key cannot be changed after creation.</p>
      </div>

      <div class="rounded-2xl border bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-3">
            <div class="rounded-full bg-[rgb(89,151,172)] p-2 text-white">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/><path d="M4 20v-2a4 4 0 0 1 4-4h8a4 4 0 0 1 4 4v2H4z"/></svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold">Content (multilingual)</h3>
              <p class="text-xs text-gray-500">Switch language tabs to edit title and content for each locale.</p>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <button type="button" @click="currentTab = 'en'" :class="currentTab==='en'? activeTabClass:inactiveTabClass" class="px-3 py-1 rounded">EN</button>
            <button type="button" @click="currentTab = 'fr'" :class="currentTab==='fr'? activeTabClass:inactiveTabClass" class="px-3 py-1 rounded">FR</button>
            <button type="button" @click="currentTab = 'ar'" :class="currentTab==='ar'? activeTabClass:inactiveTabClass" class="px-3 py-1 rounded">AR</button>
          </div>
        </div>

        <div>
          <label class="text-sm font-medium">Title</label>
          <input
            v-model="localizedTitle"
            :dir="currentTab === 'ar' ? 'rtl' : 'ltr'"
            :class="['mt-2 w-full rounded-xl px-3 py-2 text-sm', currentTab === 'ar' ? 'text-right' : 'text-left', form.errors[`title_${currentTab}`] ? 'border-red-500' : 'border-gray-200']"
            placeholder="Section title"
          />
          <p v-if="form.errors[`title_${currentTab}`]" class="mt-2 text-sm text-red-600">{{ form.errors[`title_${currentTab}`] }}</p>
        </div>

        <div class="mt-4">
          <label class="text-sm font-medium">Body</label>
          <div class="mt-2">
            <RichTextEditor v-model="localizedContent" />
          </div>
          <p v-if="form.errors[`content_${currentTab}`]" class="mt-2 text-sm text-red-600">{{ form.errors[`content_${currentTab}`] }}</p>
        </div>
      </div>

      <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm mt-4">
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
          <Link :href="cancelHref" class="inline-flex items-center justify-center rounded-2xl border border-gray-200 px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">Cancel</Link>
        </div>
      </section>
    </form>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Link, usePage, useForm } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import RichTextEditor from '@/Pages/Admin/Blogs/RichTextEditor.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({ page: { type: Object, required: true } })
const pageState = usePage()
const pageProps = pageState.props?.value ?? pageState.props ?? {}
const keys = pageProps.keys || []
const usedKeys = pageProps.usedKeys || []

const form = useForm({
  key: props.page.key || '',
  title_en: props.page.title_en || '',
  title_fr: props.page.title_fr || '',
  title_ar: props.page.title_ar || '',
  content_en: props.page.content_en || '',
  content_fr: props.page.content_fr || '',
  content_ar: props.page.content_ar || '',
})

const cancelHref = route('admin.pages.index')
const submitLabel = 'Save page'
const submittingLabel = 'Saving...'

// Toast (show flash messages from server)
const page = usePage()
const toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
})

function showToast(message, icon = 'success') {
  if (!message) return
  toast.fire({ icon, title: String(message) })
}

watch(() => page.props?.flash?.success, (m) => { if (m) showToast(m, 'success') }, { immediate: true })
watch(() => page.props?.status, (m) => { if (m) showToast(m, 'success') }, { immediate: true })

const currentTab = ref('en')
const activeTabClass = 'bg-[rgb(89,151,172)] text-white'
const inactiveTabClass = 'bg-white text-gray-700 border border-gray-200'

const localizedTitle = computed({
  get() { return form[`title_${currentTab.value}`] },
  set(v) { form[`title_${currentTab.value}`] = v }
})

const localizedContent = computed({
  get() { return form[`content_${currentTab.value}`] },
  set(v) { form[`content_${currentTab.value}`] = v }
})

function submit() {
  form.put(route('admin.pages.update', props.page.id))
}

</script>
