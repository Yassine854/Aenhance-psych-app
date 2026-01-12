<template>
  <div class="p-6 space-y-6">
    <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div>
        <div v-if="flashMessage" class="mb-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3">
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-3">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5 text-green-700 mt-0.5">
                <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.61-1.814a.75.75 0 0 0-1.22-.872l-3.236 4.53-1.784-1.784a.75.75 0 1 0-1.06 1.06l2.4 2.4a.75.75 0 0 0 1.14-.094l3.76-5.24Z" clip-rule="evenodd" />
              </svg>
              <div>
                <div class="text-sm font-medium text-green-800">Success</div>
                <div class="text-sm text-green-800">{{ flashMessage }}</div>
              </div>
            </div>
            <button type="button" @click="clearFlash" class="text-green-700/70 hover:text-green-800" aria-label="Dismiss">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
        <h1 class="text-2xl font-semibold text-gray-900">Psychologists</h1>
        <p class="text-sm text-gray-600">Manage profiles: list, view, add, and delete.</p>
      </div>
      <div class="flex items-center gap-3 w-full md:w-auto">
        <div class="flex items-center gap-2 flex-1">
          <select
            v-model="searchField"
            class="h-10 w-36 md:w-44 shrink-0 rounded-lg border-gray-300 bg-white px-3 text-sm text-gray-700"
            aria-label="Search filter"
          >
            <option value="id">ID</option>
            <option value="name">Name</option>
            <option value="email">Email</option>
            <option value="specialization">Specialization</option>
          </select>

          <div class="relative flex-1">
            <input v-model="searchQuery" type="text" :placeholder="searchPlaceholder" class="w-full rounded-lg border-gray-300 pl-10 pr-3 py-2"/>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.9 14.32a8 8 0 111.414-1.414l4.387 4.387a1 1 0 01-1.414 1.414l-4.387-4.387zM14 8a6 6 0 11-12 0 6 6 0 0112 0z" clip-rule="evenodd"/></svg>
          </div>
        </div>
        <button @click="openCreate()" type="button" title="New Psychologist" class="inline-flex items-center justify-center h-10 w-10 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
            <path fill-rule="evenodd" d="M12 2.25c.414 0 .75.336.75.75v8.25H21a.75.75 0 0 1 0 1.5h-8.25V21a.75.75 0 0 1-1.5 0v-8.25H3a.75.75 0 0 1 0-1.5h8.25V3c0-.414.336-.75.75-.75Z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>
    </header>

      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-3 text-left">
                  <button type="button" @click="toggleSort('id')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                    ID
                    <SortIcon :active="sortKey === 'id'" :dir="sortDir" />
                  </button>
                </th>
                <th class="px-4 py-3 text-left">
                  <button type="button" @click="toggleSort('name')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                    Name
                    <SortIcon :active="sortKey === 'name'" :dir="sortDir" />
                  </button>
                </th>
                <th class="px-4 py-3 text-left">
                  <button type="button" @click="toggleSort('specialization')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                    Specialization
                    <SortIcon :active="sortKey === 'specialization'" :dir="sortDir" />
                  </button>
                </th>
                <th class="px-4 py-3 text-left">
                  <button type="button" @click="toggleSort('price')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                    Price
                    <SortIcon :active="sortKey === 'price'" :dir="sortDir" />
                  </button>
                </th>
                <th class="px-4 py-3 text-left">
                  <button type="button" @click="toggleSort('approved')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                    Approved
                    <SortIcon :active="sortKey === 'approved'" :dir="sortDir" />
                  </button>
                </th>
                <th class="px-4 py-3 text-left">
                  <button type="button" @click="toggleSort('email')" class="group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700">
                    Email
                    <SortIcon :active="sortKey === 'email'" :dir="sortDir" />
                  </button>
                </th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="p in sorted" :key="p.id" class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm text-gray-700">#{{ p.id }}</td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-3">
                    <img v-if="p.profile_image_url" :src="p.profile_image_url" class="h-9 w-9 rounded-full object-cover" />
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ p.first_name }} {{ p.last_name }}</div>
                      <div class="text-xs text-gray-500">{{ p.gender || '-' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ (p.specialisations || []).map(s => s.name).join(', ') || '-' }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ formatCurrency(p.price_per_session) }}</td>
                <td class="px-4 py-3">
                  <span :class="p.is_approved ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'" class="inline-flex items-center px-2 py-1 rounded text-xs font-medium">
                    {{ p.is_approved ? 'Approved' : 'Pending' }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-700">
                  {{ p.user?.email || '-' }}
                </td>
                <td class="px-4 py-3 text-right">
                  <div class="inline-flex items-center gap-2">
                    <button
                      v-if="!p.is_approved"
                      type="button"
                      title="Approve"
                      @click="approveProfile(p)"
                      :disabled="approvingId === p.id"
                      class="inline-flex items-center justify-center h-9 w-9 rounded-lg border border-gray-200 bg-white text-[rgb(89,151,172)] hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                        <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.61-1.814a.75.75 0 0 0-1.22-.872l-3.236 4.53-1.784-1.784a.75.75 0 1 0-1.06 1.06l2.4 2.4a.75.75 0 0 0 1.14-.094l3.76-5.24Z" clip-rule="evenodd" />
                      </svg>
                    </button>

                    <button type="button" title="View" @click="openShow(p)" class="inline-flex items-center justify-center h-9 w-9 rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                        <path d="M12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z" />
                        <path fill-rule="evenodd" d="M12 3c5.392 0 9.878 3.88 10.818 9-.94 5.12-5.426 9-10.818 9S2.122 17.12 1.182 12C2.122 6.88 6.608 3 12 3Zm0 15a6 6 0 0 0 6-6 6 6 0 0 0-12 0 6 6 0 0 0 6 6Z" clip-rule="evenodd" />
                      </svg>
                    </button>

                    <button type="button" title="Edit" @click="openEdit(p)" class="inline-flex items-center justify-center h-9 w-9 rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50">
                      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                        <path d="M21.44 11.05 13 19.5a4.5 4.5 0 0 1-1.591 1.06l-4.106 1.46a.75.75 0 0 1-.958-.958l1.46-4.106A4.5 4.5 0 0 1 8.866 15.5l8.44-8.44a2.25 2.25 0 0 1 3.182 0l.952.952a2.25 2.25 0 0 1 0 3.182Z" />
                        <path d="M13.5 7.5 16.5 10.5" />
                      </svg>
                    </button>

                    <button type="button" title="Delete" @click="confirmDelete(p)" class="inline-flex items-center justify-center h-9 w-9 rounded-lg border border-gray-200 bg-white text-red-700 hover:bg-red-50">
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

        <!-- Pagination -->
        <div class="flex items-center justify-between px-4 py-3 border-t border-gray-200">
          <div class="text-sm text-gray-600">Showing {{ profiles.from }}-{{ profiles.to }} of {{ profiles.total }}</div>
          <div class="flex items-center gap-2">
            <Link v-for="(link, i) in profiles.links" :key="i" :href="link.url || '#'" :class="linkClasses(link)" preserve-scroll>
              <span v-html="link.label"></span>
            </Link>
          </div>
        </div>
      </div>

      <!-- Create Modal -->
      <Create :show="modal === 'create'" :specialisations="props.specialisations" :expertises="props.expertises" @close="closeModal" @created="handleCreated" />

      <!-- Show Modal -->
      <Show :show="modal === 'show'" :psychologist="selected" @close="closeModal" />

      <!-- Edit Modal -->
      <Edit :show="modal === 'edit'" :psychologist="selected" :specialisations="props.specialisations" :expertises="props.expertises" @close="closeModal" @saved="handleSaved" />
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Edit from './Edit.vue'
import Create from './Create.vue'
import Show from './Show.vue'
import SortIcon from './SortIcon.vue'
import Swal from 'sweetalert2'

const props = defineProps({
  profiles: Object,
  specialisations: {
    type: Array,
    default: () => [],
  },
  expertises: {
    type: Array,
    default: () => [],
  },
})

const profilesData = ref(props.profiles?.data ? [...props.profiles.data] : [])
watch(
  () => props.profiles?.data,
  (next) => {
    profilesData.value = next ? [...next] : []
  }
)

const searchQuery = ref('')
const searchField = ref('id')

const searchPlaceholder = computed(() => {
  switch (searchField.value) {
    case 'id':
      return 'Search by ID...'
    case 'name':
      return 'Search by name...'
    case 'email':
      return 'Search by email...'
    case 'specialization':
      return 'Search by specialization...'
    default:
      return 'Search...'
  }
})

const filtered = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  if (!q) return profilesData.value || []

  const list = profilesData.value || []

  return list.filter(p => {
    switch (searchField.value) {
      case 'id':
        return String(p?.id ?? '').toLowerCase().includes(q)
      case 'name':
        return `${p?.first_name ?? ''} ${p?.last_name ?? ''}`.toLowerCase().includes(q)
      case 'email':
        return String(p?.user?.email ?? '').toLowerCase().includes(q)
      case 'specialization':
        return String((p?.specialisations || []).map(s => s?.name).filter(Boolean).join(', ')).toLowerCase().includes(q)
      default:
        return false
    }
  })
})

// Sorting (client-side, applies after search)
const sortKey = ref('id')
const sortDir = ref('asc') // 'asc' | 'desc'

function toggleSort(key) {
  if (sortKey.value === key) {
    sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
    return
  }
  sortKey.value = key
  sortDir.value = 'asc'
}

function getSortValue(p, key) {
  switch (key) {
    case 'id':
      return Number(p?.id || 0)
    case 'name':
      return `${p?.first_name || ''} ${p?.last_name || ''}`.trim().toLowerCase()
    case 'specialization':
      return String((p?.specialisations || []).map(s => s?.name).filter(Boolean).join(', ')).toLowerCase()
    case 'price':
      return Number(p?.price_per_session || 0)
    case 'approved':
      return p?.is_approved ? 1 : 0
    case 'email':
      return String(p?.user?.email || '').toLowerCase()
    default:
      return ''
  }
}

const sorted = computed(() => {
  const list = filtered.value || []
  const key = sortKey.value
  const dir = sortDir.value
  const multiplier = dir === 'asc' ? 1 : -1

  return list
    .map((item, idx) => ({ item, idx }))
    .sort((a, b) => {
      const av = getSortValue(a.item, key)
      const bv = getSortValue(b.item, key)

      if (typeof av === 'number' && typeof bv === 'number') {
        const diff = av - bv
        return diff !== 0 ? diff * multiplier : (a.idx - b.idx)
      }

      const diff = String(av).localeCompare(String(bv))
      return diff !== 0 ? diff * multiplier : (a.idx - b.idx)
    })
    .map(x => x.item)
})

function formatCurrency(value) {
  if (value == null) return '-'
  const n = Number(value)
  return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'TND', minimumFractionDigits: 2 }).format(n)
}

function formatDate(value) {
  if (!value) return '-' 
  try { return new Date(value).toLocaleDateString() } catch { return '-' }
}

function linkClasses(link) {
  const base = 'px-3 py-1.5 rounded text-sm'
  if (!link.url) return base + ' text-gray-400 bg-gray-100 cursor-not-allowed'
  return (link.active ? base + ' bg-indigo-600 text-white' : base + ' bg-gray-50 text-gray-700 hover:bg-gray-100')
}

// Modals state
const modal = ref(null) // 'create' | 'show' | 'edit' | null
const selected = ref(null)

const approvingId = ref(null)

const flashMessage = ref('')
let flashTimer = null

function showFlash(message) {
  flashMessage.value = message
  if (flashTimer) clearTimeout(flashTimer)
  flashTimer = setTimeout(() => {
    flashMessage.value = ''
    flashTimer = null
  }, 2500)
}

function clearFlash() {
  flashMessage.value = ''
  if (flashTimer) {
    clearTimeout(flashTimer)
    flashTimer = null
  }
}

async function ensureCsrfToken() {
  const m1 = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  if (m1) return { token: decodeURIComponent(m1[1]), type: 'cookie' }

  const tokenEl = document.querySelector('meta[name="csrf-token"]')
  const metaToken = tokenEl?.getAttribute('content') || ''
  if (metaToken) return { token: metaToken, type: 'meta' }

  try {
    await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' })
  } catch {}

  const m2 = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  return m2 ? { token: decodeURIComponent(m2[1]), type: 'cookie' } : { token: '', type: 'none' }
}



function closeModal() {
  modal.value = null
  selected.value = null
}

function openShow(p) {
  selected.value = p
  modal.value = 'show'
}

function openEdit(p) {
  selected.value = p
  modal.value = 'edit'
}

function handleSaved(payload) {
  closeModal()
  if (payload?.type === 'account') {
    showFlash('Account saved successfully.')
  } else {
    showFlash('Psychologist saved successfully.')
  }

  if (payload?.profile?.id) {
    const idx = profilesData.value.findIndex(x => x?.id === payload.profile.id)
    if (idx !== -1) profilesData.value[idx] = payload.profile
  }

  if (payload?.user?.id) {
    const idx = profilesData.value.findIndex(x => x?.user?.id === payload.user.id)
    if (idx !== -1) {
      profilesData.value[idx] = {
        ...profilesData.value[idx],
        user: {
          ...(profilesData.value[idx].user || {}),
          ...payload.user,
        },
      }
    }
  }
}

function handleCreated(payload) {
  closeModal()
  showFlash('Psychologist created successfully.')

  const created = payload?.profile
  if (created?.id) {
    // Insert into current page list; sorting/search will re-derive view.
    profilesData.value = [created, ...profilesData.value]
  }
}

// Delete
async function confirmDelete(p) {
  const name = `${p?.first_name || ''} ${p?.last_name || ''}`.trim() || `#${p?.id ?? ''}`

  const result = await Swal.fire({
    title: 'Delete psychologist?'
    ,text: `You are about to delete ${name}. This action cannot be undone.`
    ,icon: 'warning'
    ,showCancelButton: true
    ,confirmButtonText: 'Yes, delete'
    ,cancelButtonText: 'Cancel'
    ,reverseButtons: true
    ,focusCancel: true
    ,confirmButtonColor: 'rgb(141,61,79)'
  })

  if (!result.isConfirmed) return

  try {
    const csrf = await ensureCsrfToken()
    const headers = {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json',
    }

    if (csrf.type === 'meta' && csrf.token) {
      headers['X-CSRF-TOKEN'] = csrf.token
    } else if (csrf.type === 'cookie' && csrf.token) {
      headers['X-XSRF-TOKEN'] = csrf.token
    }

    const res = await fetch(route('psychologist-profiles.destroy', p.id), {
      method: 'DELETE',
      headers,
      credentials: 'same-origin',
    })

    if (!res.ok) {
      throw new Error('delete failed')
    }

    profilesData.value = profilesData.value.filter(x => x?.id !== p.id)

    Swal.fire({
      title: 'Deleted'
      ,text: 'Psychologist deleted successfully.'
      ,icon: 'success'
      ,timer: 1400
      ,showConfirmButton: false
    })
  } catch {
    Swal.fire({
      title: 'Delete failed'
      ,text: 'Could not delete this psychologist. Please try again.'
      ,icon: 'error'
    })
  }
}

async function approveProfile(p) {
  if (!p || p.is_approved) return

  const name = `${p?.first_name || ''} ${p?.last_name || ''}`.trim() || `#${p?.id ?? ''}`

  const result = await Swal.fire({
    title: 'Approve psychologist?'
    ,text: `Approve ${name} and mark this profile as approved?`
    ,icon: 'question'
    ,showCancelButton: true
    ,confirmButtonText: 'Yes, approve'
    ,cancelButtonText: 'Cancel'
    ,reverseButtons: true
    ,focusCancel: true
    ,confirmButtonColor: 'rgb(89,151,172)'
  })

  if (!result.isConfirmed) return

  approvingId.value = p.id
  try {
    const csrf = await ensureCsrfToken()
    const headers = {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json',
    }

    if (csrf.type === 'meta' && csrf.token) {
      headers['X-CSRF-TOKEN'] = csrf.token
    } else if (csrf.type === 'cookie' && csrf.token) {
      headers['X-XSRF-TOKEN'] = csrf.token
    }

    const res = await fetch(route('psychologist-profiles.approve', p.id), {
      method: 'PATCH',
      headers,
      credentials: 'same-origin',
    })

    if (!res.ok) {
      await Swal.fire({
        title: 'Approve failed'
        ,text: 'Could not approve this psychologist. Please try again.'
        ,icon: 'error'
      })
      return
    }

    const idx = profilesData.value.findIndex(x => x?.id === p.id)
    if (idx !== -1) {
      profilesData.value[idx] = { ...profilesData.value[idx], is_approved: true }
    }

    Swal.fire({
      title: 'Approved'
      ,text: 'Psychologist approved successfully.'
      ,icon: 'success'
      ,timer: 1200
      ,showConfirmButton: false
    })
  } finally {
    approvingId.value = null
  }
}

// Create modal
function openCreate() {
  modal.value = 'create'
}
</script>

<script>
export default {
  layout: AdminLayout,
  name: 'Admin/Psychologist/Index'
}
</script>

<style scoped>
/* Stylish scrollbar for modals */
.styled-scrollbar {
  /* Firefox */
  scrollbar-width: thin;
  scrollbar-color: rgb(89 151 172 / var(--tw-bg-opacity, 1)) rgba(229, 231, 235, 1);
}
.styled-scrollbar::-webkit-scrollbar {
  width: 10px;
  height: 10px;
}
.styled-scrollbar::-webkit-scrollbar-track {
  background: rgba(241, 245, 249, 1); /* slate-100 */
  border-radius: 9999px;
}
.styled-scrollbar::-webkit-scrollbar-thumb {
  background: rgb(89 151 172 / var(--tw-bg-opacity, 1));
  border-radius: 9999px;
  border: 2px solid #ffffff;
}
.styled-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgb(89 151 172 / 0.85);
}
</style>
