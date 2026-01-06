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
        <h1 class="text-2xl font-semibold text-gray-900">Specialisations</h1>
        <p class="text-sm text-gray-600">Add and manage the list of specialisations used across the platform.</p>
      </div>

      <div class="flex items-center gap-3 w-full md:w-auto">
        <div class="relative flex-1 md:w-80">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search by name..."
            class="w-full rounded-lg border-gray-300 pl-10 pr-3 py-2"
          />
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M12.9 14.32a8 8 0 111.414-1.414l4.387 4.387a1 1 0 01-1.414 1.414l-4.387-4.387zM14 8a6 6 0 11-12 0 6 6 0 0112 0z"
              clip-rule="evenodd"
            />
          </svg>
        </div>

        <button
          @click="openCreate"
          type="button"
          title="New Specialisation"
          class="inline-flex items-center justify-center h-10 w-10 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700"
        >
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
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
              <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>

          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="s in filtered" :key="s.id" class="hover:bg-gray-50">
              <td class="px-4 py-3 text-sm text-gray-700">#{{ s.id }}</td>
              <td class="px-4 py-3">
                <div class="text-sm font-medium text-gray-900">{{ s.name }}</div>
              </td>
              <td class="px-4 py-3 text-right">
                <div class="inline-flex items-center gap-2">
                  <button
                    type="button"
                    title="View"
                    @click="openShow(s)"
                    class="inline-flex items-center justify-center h-9 w-9 rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                      <path d="M12 9a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z" />
                      <path fill-rule="evenodd" d="M12 3c5.392 0 9.878 3.88 10.818 9-.94 5.12-5.426 9-10.818 9S2.122 17.12 1.182 12C2.122 6.88 6.608 3 12 3Zm0 15a6 6 0 0 0 6-6 6 6 0 0 0-12 0 6 6 0 0 0 6 6Z" clip-rule="evenodd" />
                    </svg>
                  </button>

                  <button
                    type="button"
                    title="Edit"
                    @click="openEdit(s)"
                    class="inline-flex items-center justify-center h-9 w-9 rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5">
                      <path d="M21.44 11.05 13 19.5a4.5 4.5 0 0 1-1.591 1.06l-4.106 1.46a.75.75 0 0 1-.958-.958l1.46-4.106A4.5 4.5 0 0 1 8.866 15.5l8.44-8.44a2.25 2.25 0 0 1 3.182 0l.952.952a2.25 2.25 0 0 1 0 3.182Z" />
                      <path d="M13.5 7.5 16.5 10.5" />
                    </svg>
                  </button>

                  <button
                    type="button"
                    title="Delete"
                    @click="confirmDelete(s)"
                    class="inline-flex items-center justify-center h-9 w-9 rounded-lg border border-gray-200 bg-white text-red-700 hover:bg-red-50"
                  >
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

      <div class="flex items-center justify-between px-4 py-3 border-t border-gray-200">
        <div class="text-sm text-gray-600">Showing {{ specialisations.from }}-{{ specialisations.to }} of {{ specialisations.total }}</div>
        <div class="flex items-center gap-2">
          <Link
            v-for="(link, i) in specialisations.links"
            :key="i"
            :href="link.url || '#'"
            :class="linkClasses(link)"
            preserve-scroll
          >
            <span v-html="link.label"></span>
          </Link>
        </div>
      </div>
    </div>

    <Create :show="modal === 'create'" @close="closeModal" @created="handleCreated" />
    <Show :show="modal === 'show'" :specialisation="selected" @close="closeModal" />
    <Edit :show="modal === 'edit'" :specialisation="selected" @close="closeModal" @saved="handleSaved" />
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Swal from 'sweetalert2'

import Create from './Create.vue'
import Edit from './Edit.vue'
import Show from './Show.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({ specialisations: Object })

const data = ref(props.specialisations?.data ? [...props.specialisations.data] : [])
watch(
  () => props.specialisations?.data,
  (next) => {
    data.value = next ? [...next] : []
  }
)

const flashMessage = ref('')
let flashTimer = null

function showFlash(message) {
  flashMessage.value = message
  if (flashTimer) clearTimeout(flashTimer)
  flashTimer = setTimeout(() => {
    flashMessage.value = ''
    flashTimer = null
  }, 3500)
}

function clearFlash() {
  flashMessage.value = ''
  if (flashTimer) clearTimeout(flashTimer)
  flashTimer = null
}

const searchQuery = ref('')
const filtered = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  if (!q) return data.value || []
  return (data.value || []).filter((s) => String(s?.name ?? '').toLowerCase().includes(q))
})

const modal = ref(null) // 'create' | 'edit' | 'show' | null
const selected = ref(null)

function openCreate() {
  modal.value = 'create'
}

function openShow(s) {
  selected.value = s
  modal.value = 'show'
}

function openEdit(s) {
  selected.value = s
  modal.value = 'edit'
}

function closeModal() {
  modal.value = null
  selected.value = null
}

function handleCreated(s) {
  data.value = [s, ...(data.value || [])]
  showFlash('Specialisation created successfully.')
}

function handleSaved(updated) {
  data.value = (data.value || []).map((s) => (s.id === updated.id ? updated : s))
  if (selected.value?.id === updated.id) selected.value = updated
  showFlash('Specialisation updated successfully.')
}

function linkClasses(link) {
  const base = 'inline-flex items-center justify-center px-3 py-1.5 rounded-lg text-sm border '
  if (link.active) return base + 'bg-indigo-600 text-white border-indigo-600'
  if (!link.url) return base + 'bg-gray-50 text-gray-400 border-gray-200 cursor-not-allowed'
  return base + 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50'
}

async function ensureCsrfToken() {
  const tokenEl = document.querySelector('meta[name="csrf-token"]')
  const metaToken = tokenEl?.getAttribute('content') || ''
  if (metaToken) return { token: metaToken, type: 'meta' }

  const m1 = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  if (m1) return { token: decodeURIComponent(m1[1]), type: 'cookie' }

  try {
    await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'same-origin' })
  } catch {}

  const m2 = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  return m2 ? { token: decodeURIComponent(m2[1]), type: 'cookie' } : { token: '', type: 'none' }
}

async function deleteSpecialisation(s) {
  const csrf = await ensureCsrfToken()

  const res = await fetch(`/specialisations/${s.id}`, {
    method: 'DELETE',
    credentials: 'same-origin',
    headers: {
      Accept: 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      ...(csrf.token ? { 'X-CSRF-TOKEN': csrf.type === 'meta' ? csrf.token : undefined, 'X-XSRF-TOKEN': csrf.type === 'cookie' ? csrf.token : undefined } : {}),
    },
  })

  if (res.status === 204) {
    data.value = (data.value || []).filter((x) => x.id !== s.id)
    return
  }

  const text = await res.text()
  throw new Error(text || 'Delete failed')
}

async function confirmDelete(s) {
  const result = await Swal.fire({
    title: 'Delete specialisation?',
    text: `"${s.name}" will be removed.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    confirmButtonText: 'Delete',
  })

  if (!result.isConfirmed) return

  try {
    await deleteSpecialisation(s)
    Swal.fire({ title: 'Deleted', icon: 'success', timer: 1200, showConfirmButton: false })
  } catch (e) {
    Swal.fire({ title: 'Error', text: e?.message || 'Failed to delete', icon: 'error' })
  }
}
</script>
