<template>
  <div class="space-y-6 p-6">
    <header class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Ressources</h1>
      </div>

      <div class="flex w-full flex-col gap-3 lg:w-auto lg:flex-row lg:items-center">
        <div class="flex flex-1 items-center gap-2">
          <select
            v-model="searchField"
            class="h-11 w-36 shrink-0 rounded-xl border-gray-300 bg-white px-3 text-sm text-gray-700"
            aria-label="Search field"
          >
            <option value="id">ID</option>
            <option value="title">Title</option>
            <option value="author">Author</option>
          </select>

          <div class="relative min-w-0 flex-1 lg:w-80">
            <input
              v-model="searchQuery"
              type="text"
              :placeholder="searchPlaceholder"
              class="w-full rounded-xl border-gray-300 py-2.5 pl-10 pr-10"
            />

            <svg xmlns="http://www.w3.org/2000/svg" class="absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M12.9 14.32a8 8 0 111.414-1.414l4.387 4.387a1 1 0 01-1.414 1.414l-4.387-4.387zM14 8a6 6 0 11-12 0 6 6 0 0112 0z" clip-rule="evenodd" />
            </svg>

            <button
              v-if="searchQuery"
              type="button"
              class="absolute right-2 top-1/2 inline-flex h-7 w-7 -translate-y-1/2 items-center justify-center rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700"
              @click="clearSearch"
            >
              ✕
            </button>
          </div>
        </div>

        <Link
          :href="route('admin.ressources.create')"
          class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-[rgb(141,61,79)] text-white shadow-lg shadow-[rgb(141,61,79)]/20 transition hover:opacity-90"
          title="New Ressource"
          aria-label="Create ressource"
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
                <button type="button" class="group inline-flex items-center gap-1 text-xs font-medium uppercase tracking-wider text-gray-500 hover:text-gray-700" @click="toggleSort('title')">
                  Ressource
                  <SortIcon :active="sortKey === 'title'" :dir="sortDir" />
                </button>
              </th>
              <th class="px-4 py-3 text-left">
                <button type="button" class="group inline-flex items-center gap-1 text-xs font-medium uppercase tracking-wider text-gray-500 hover:text-gray-700" @click="toggleSort('published_at')">
                  Published
                  <SortIcon :active="sortKey === 'published_at'" :dir="sortDir" />
                </button>
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Author</th>
              <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">PDF</th>
              <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-200 bg-white">
            <tr v-if="sortedRessources.length === 0">
              <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">No ressources found.</td>
            </tr>

            <tr v-for="ressource in sortedRessources" :key="ressource.id" class="hover:bg-gray-50">
              <td class="px-4 py-4 text-sm text-gray-700">#{{ ressource.id }}</td>
              <td class="px-4 py-4">
                <div class="truncate text-sm font-semibold text-gray-900">{{ ressource.title }}</div>
              </td>
              <td class="px-4 py-4 text-sm text-gray-700">{{ formatDate(ressource.published_at) }}</td>
              <td class="px-4 py-4 text-sm text-gray-700">
                <div class="font-medium text-gray-900">{{ ressource.author?.name || 'Admin' }}</div>
                <div class="text-xs text-gray-500">{{ ressource.author?.email || '—' }}</div>
              </td>
              <td class="px-4 py-4 text-sm text-gray-700">
                <a
                  v-if="ressource.pdf"
                  :href="resolveStorageUrl(ressource.pdf)"
                  target="_blank"
                  rel="noopener"
                  class="inline-flex items-center rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700"
                >
                  Open PDF
                </a>
                <span v-else>—</span>
              </td>
              <td class="px-4 py-4 text-right">
                <div class="inline-flex items-center gap-2">
                  <Link :href="route('admin.ressources.show', ressource.id)" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 transition hover:bg-gray-50" title="View">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                      <path d="M12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z" />
                      <path fill-rule="evenodd" d="M12 3c5.392 0 9.878 3.88 10.818 9-.94 5.12-5.426 9-10.818 9S2.122 17.12 1.182 12C2.122 6.88 6.608 3 12 3Zm0 15a6 6 0 0 0 6-6 6 6 0 0 0-12 0 6 6 0 0 0 6 6Z" clip-rule="evenodd" />
                    </svg>
                  </Link>

                  <Link :href="route('admin.ressources.edit', ressource.id)" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-700 transition hover:bg-gray-50" title="Edit">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                      <path d="M21.44 11.05 13 19.5a4.5 4.5 0 0 1-1.591 1.06l-4.106 1.46a.75.75 0 0 1-.958-.958l1.46-4.106A4.5 4.5 0 0 1 8.866 15.5l8.44-8.44a2.25 2.25 0 0 1 3.182 0l.952.952a2.25 2.25 0 0 1 0 3.182Z" />
                      <path d="M13.5 7.5 16.5 10.5" />
                    </svg>
                  </Link>

                  <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-red-700 transition hover:bg-red-50" title="Delete" @click="confirmDelete(ressource)">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                      <path fill-rule="evenodd" d="M9 3.75A.75.75 0 0 1 9.75 3h4.5a.75.75 0 0 1 .75.75V6h4.5a.75.75 0 0 1 0 1.5h-1.06l-.84 12.02A2.25 2.25 0 0 1 15.36 21H8.64a2.25 2.25 0 0 1-2.244-2.48L5.56 7.5H4.5a.75.75 0 0 1 0-1.5H9V3.75Zm1.5 2.25h3V4.5h-3V6Zm-1.44 3.75a.75.75 0 0 1 .81.69l.75 9a.75.75 0 1 1-1.5.12l-.75-9a.75.75 0 0 1 .69-.81Zm6.69.69a.75.75 0 0 0-1.5.12l.75 9a.75.75 0 1 0 1.5-.12l-.75-9Z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex items-center justify-between border-t border-gray-200 px-4 py-3">
        <div class="text-sm text-gray-600">Showing {{ ressources.from || 0 }}-{{ ressources.to || 0 }} of {{ ressources.total || 0 }}</div>
        <div class="flex items-center gap-2">
          <Link
            v-for="(link, index) in ressources.links"
            :key="index"
            :href="link.url || '#'"
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
import { resolveStorageUrl } from '@/utils/storage'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  ressources: { type: Object, required: true },
  status: { type: String, default: '' },
  error: { type: String, default: '' },
  filters: { type: Object, default: () => ({}) },
})

const ressources = computed(() => props.ressources || {})
const rows = computed(() => props.ressources?.data || [])
const searchField = ref('title')
const searchQuery = ref('')
const isHydratingFilters = ref(false)
let searchDebounce = null

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
  toast.fire({
    icon,
    title: String(message),
  })
}

function normalizeFilters(filters = {}) {
  const field = ['id', 'title', 'author'].includes(String(filters.search_field || '').toLowerCase())
    ? String(filters.search_field).toLowerCase()
    : 'title'

  return {
    search_field: field,
    search_query: String(filters.search_query || ''),
  }
}

function hydrateFiltersFromProps() {
  const filters = normalizeFilters(props.filters || {})
  isHydratingFilters.value = true
  searchField.value = filters.search_field
  searchQuery.value = filters.search_query
  isHydratingFilters.value = false
}

function currentQueryParams() {
  const params = {
    search_field: searchField.value,
    search_query: String(searchQuery.value || '').trim(),
  }

  return Object.fromEntries(Object.entries(params).filter(([_, value]) => value !== '' && value != null))
}

function applyServerFilters({ resetPage = true } = {}) {
  if (isHydratingFilters.value) return

  const params = currentQueryParams()
  if (resetPage) params.page = 1

  router.get(route('admin.ressources.index'), params, {
    preserveScroll: true,
    preserveState: true,
    replace: true,
    only: ['ressources', 'filters', 'status', 'error'],
  })
}

function clearSearch() {
  if (searchDebounce) {
    clearTimeout(searchDebounce)
    searchDebounce = null
  }
  searchQuery.value = ''
  applyServerFilters({ resetPage: true })
}

const searchPlaceholder = computed(() => {
  if (searchField.value === 'id') return 'Search by ID...'
  if (searchField.value === 'author') return 'Search by author...'
  return 'Search by title...'
})

watch(() => props.status, (next) => {
  showToast(next, 'success')
}, { immediate: true })

watch(() => props.error, (next) => {
  showToast(next, 'error')
}, { immediate: true })

watch(() => props.filters, () => {
  hydrateFiltersFromProps()
})

watch(searchField, () => {
  if (isHydratingFilters.value) return
  searchQuery.value = ''
  applyServerFilters({ resetPage: true })
})

watch(searchQuery, () => {
  if (isHydratingFilters.value) return
  if (searchDebounce) clearTimeout(searchDebounce)
  searchDebounce = setTimeout(() => {
    applyServerFilters({ resetPage: true })
    searchDebounce = null
  }, 300)
})

function toggleSort(key) {
  if (sortKey.value === key) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
    return
  }

  sortKey.value = key
  sortDir.value = key === 'title' ? 'asc' : 'desc'
}

function getSortValue(ressource, key) {
  switch (key) {
    case 'id':
      return Number(ressource.id || 0)
    case 'title':
      return String(ressource.title || '').toLowerCase()
    case 'published_at':
      return ressource.published_at ? new Date(ressource.published_at).getTime() : 0
    default:
      return ''
  }
}

const sortedRessources = computed(() => {
  const multiplier = sortDir.value === 'asc' ? 1 : -1

  return [...rows.value]
    .map((ressource, index) => ({ ressource, index }))
    .sort((a, b) => {
      const left = getSortValue(a.ressource, sortKey.value)
      const right = getSortValue(b.ressource, sortKey.value)

      if (typeof left === 'number' && typeof right === 'number') {
        const diff = left - right
        return diff !== 0 ? diff * multiplier : a.index - b.index
      }

      const diff = String(left).localeCompare(String(right))
      return diff !== 0 ? diff * multiplier : a.index - b.index
    })
    .map(({ ressource }) => ressource)
})

function formatDate(value) {
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

function linkClasses(link) {
  return [
    'inline-flex min-h-[2.5rem] min-w-[2.5rem] items-center justify-center rounded-lg border px-3 text-sm transition',
    link.url ? 'border-gray-200 text-gray-700 hover:bg-gray-50' : 'cursor-not-allowed border-gray-100 text-gray-300',
  ]
}

async function confirmDelete(ressource) {
  const result = await Swal.fire({
    title: 'Delete this ressource?',
    text: `"${ressource.title}" will be removed from the admin list.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Delete',
    cancelButtonText: 'Cancel',
    confirmButtonColor: 'rgb(141,61,79)',
  })

  if (!result.isConfirmed) return

  router.delete(route('admin.ressources.destroy', ressource.id), {
    preserveScroll: true,
  })
}

hydrateFiltersFromProps()
</script>