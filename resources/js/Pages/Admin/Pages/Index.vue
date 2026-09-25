<template>
  <div class="space-y-6 p-6">
    <header class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Pages</h1>
      </div>

        <div class="flex w-full justify-end lg:w-auto">
          <Link
            :href="route('admin.pages.create')"
            class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-[rgb(141,61,79)] text-white shadow-lg shadow-[rgb(141,61,79)]/20 transition hover:opacity-90"
            title="New Page"
            aria-label="Create page"
          >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
              <path fill-rule="evenodd" d="M12 2.25c.414 0 .75.336.75.75v8.25H21a.75.75 0 0 1 0 1.5h-8.25V21a.75.75 0 0 1-1.5 0v-8.25H3a.75.75 0 0 1 0-1.5h8.25V3c0-.414.336-.75.75-.75Z" clip-rule="evenodd" />
            </svg>
          </Link>
        </div>
    </header>

    <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-4 py-3 text-left">
                <button type="button" class="group inline-flex items-center gap-1 text-xs font-medium uppercase tracking-wider text-gray-500 hover:text-gray-700" @click="toggleSort('id')">
                  ID
                  <SortIcon :active="sortKey === 'id'" :dir="sortDir" />
                </button>
              </th>
              <th class="px-4 py-3 text-left">
                <button type="button" class="group inline-flex items-center gap-1 text-xs font-medium uppercase tracking-wider text-gray-500 hover:text-gray-700" @click="toggleSort('key')">
                  Key
                  <SortIcon :active="sortKey === 'key'" :dir="sortDir" />
                </button>
              </th>
              <th class="px-4 py-3 text-left">
                <button type="button" class="group inline-flex items-center gap-1 text-xs font-medium uppercase tracking-wider text-gray-500 hover:text-gray-700" @click="toggleSort('title')">
                  Title (EN)
                  <SortIcon :active="sortKey === 'title'" :dir="sortDir" />
                </button>
              </th>
              <th class="px-4 py-3 text-left">
                <button type="button" class="group inline-flex items-center gap-1 text-xs font-medium uppercase tracking-wider text-gray-500 hover:text-gray-700" @click="toggleSort('title_fr')">
                  Title (FR)
                  <SortIcon :active="sortKey === 'title_fr'" :dir="sortDir" />
                </button>
              </th>
              <th class="px-4 py-3 text-left">
                <button type="button" class="group inline-flex items-center gap-1 text-xs font-medium uppercase tracking-wider text-gray-500 hover:text-gray-700" @click="toggleSort('title_ar')">
                  Title (AR)
                  <SortIcon :active="sortKey === 'title_ar'" :dir="sortDir" />
                </button>
              </th>
              <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-200 bg-white">
            <tr v-if="rows.length === 0">
              <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">No pages found.</td>
            </tr>

            <tr v-for="p in sortedPages" :key="p.id" class="hover:bg-gray-50">
              <td class="px-4 py-4 text-sm text-gray-700">#{{ p.id }}</td>
              <td class="px-4 py-4 text-sm font-medium text-gray-900">{{ p.key }}</td>
              <td class="px-4 py-4 text-sm text-gray-700">{{ p.title_en || '—' }}</td>
              <td class="px-4 py-4 text-sm text-gray-700">{{ p.title_fr || '—' }}</td>
              <td class="px-4 py-4 text-sm text-gray-700">{{ p.title_ar || '—' }}</td>
              <td class="px-4 py-4 text-right">
                <div class="inline-flex items-center gap-2">
                  <Link :href="route('admin.pages.edit', p.id)" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 transition hover:bg-gray-50" title="Edit">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M21.44 11.05 13 19.5a4.5 4.5 0 0 1-1.591 1.06l-4.106 1.46a.75.75 0 0 1-.958-.958l1.46-4.106A4.5 4.5 0 0 1 8.866 15.5l8.44-8.44a2.25 2.25 0 0 1 3.182 0l.952.952a2.25 2.25 0 0 1 0 3.182Z"/><path d="M13.5 7.5 16.5 10.5"/></svg>
                  </Link>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex items-center justify-between border-t border-gray-200 px-4 py-3">
        <div class="text-sm text-gray-600">Showing {{ pages.from || 0 }}-{{ pages.to || 0 }} of {{ pages.total || 0 }}</div>
        <div class="flex items-center gap-2">
          <Link
            v-for="(link, index) in pages.links"
            :key="index"
            :href="link.url || '#'
            "
            preserve-scroll
            :class="linkClasses(link)"
            :style="link.active ? { backgroundColor: brandColor, borderColor: brandColor, color: '#fff' } : null"
          >
            <span v-html="link.label"></span>
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import SortIcon from '@/Components/SortIcon.vue'
import Swal from 'sweetalert2'

defineOptions({ layout: AdminLayout })

const props = defineProps({ pages: { type: Object, required: true }, filters: { type: Object, default: () => ({}) }, status: { type: String, default: '' }, error: { type: String, default: '' } })

const pages = computed(() => props.pages || {})
const rows = computed(() => props.pages?.data || [])
// search removed per user request

const sortKey = ref('id')
const sortDir = ref('desc')
const brandColor = 'rgb(89,151,172)'

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

// search and server filter helpers removed per user request

watch(() => props.status, (next) => { showToast(next, 'success') }, { immediate: true })
watch(() => props.error, (next) => { showToast(next, 'error') }, { immediate: true })

function toggleSort(key) {
  if (sortKey.value === key) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
    return
  }

  sortKey.value = key
  sortDir.value = key === 'title' ? 'asc' : 'desc'
}

function getSortValue(page, key) {
  switch (key) {
    case 'id':
      return Number(page.id || 0)
    case 'key':
      return String(page.key || '').toLowerCase()
    case 'title':
      return String(page.title_en || '').toLowerCase()
    case 'identifier':
      return Number(page.identifier ?? -1)
    default:
      return ''
  }
}

const sortedPages = computed(() => {
  const multiplier = sortDir.value === 'asc' ? 1 : -1

  return [...rows.value]
    .map((p, index) => ({ p, index }))
    .sort((a, b) => {
      const left = getSortValue(a.p, sortKey.value)
      const right = getSortValue(b.p, sortKey.value)

      if (typeof left === 'number' && typeof right === 'number') {
        const diff = left - right
        return diff !== 0 ? diff * multiplier : a.index - b.index
      }

      const diff = String(left).localeCompare(String(right))
      return diff !== 0 ? diff * multiplier : a.index - b.index
    })
    .map(({ p }) => p)
})

function linkClasses(link) {
  return [
    'inline-flex min-h-[2.5rem] min-w-[2.5rem] items-center justify-center rounded-lg border px-3 text-sm transition',
    link.url ? 'border-gray-200 text-gray-700 hover:bg-gray-50' : 'cursor-not-allowed border-gray-100 text-gray-300',
  ]
}

// delete action removed from UI; no client-side delete helper needed
// hydrateFiltersFromProps removed; search not used
</script>
