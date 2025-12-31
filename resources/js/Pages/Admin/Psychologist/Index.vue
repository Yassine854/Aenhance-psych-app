<template>
  <div class="p-6 space-y-6">
    <header class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Psychologists</h1>
        <p class="text-sm text-gray-600">Manage profiles: list, view, add, and delete.</p>
      </div>
      <div class="flex items-center gap-3 w-full md:w-auto">
        <div class="relative flex-1">
          <input v-model="searchQuery" type="text" placeholder="Search by ID, name, specialization, email..." class="w-full rounded-lg border-gray-300 pl-10 pr-3 py-2"/>
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.9 14.32a8 8 0 111.414-1.414l4.387 4.387a1 1 0 01-1.414 1.414l-4.387-4.387zM14 8a6 6 0 11-12 0 6 6 0 0112 0z" clip-rule="evenodd"/></svg>
        </div>
        <button @click="openCreate()" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"/></svg>
          <span>New Psychologist</span>
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
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Specialization</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approved</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="p in filtered" :key="p.id" class="hover:bg-gray-50">
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
                <td class="px-4 py-3 text-sm text-gray-700">{{ p.specialization || '-' }}</td>
                <td class="px-4 py-3 text-sm text-gray-700">{{ formatCurrency(p.price_per_session) }}</td>
                <td class="px-4 py-3">
                  <span :class="p.is_approved ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'" class="inline-flex items-center px-2 py-1 rounded text-xs font-medium">
                    {{ p.is_approved ? 'Approved' : 'Pending' }}
                  </span>
                </td>
                <td class="px-4 py-3 text-right">
                  <div class="inline-flex items-center gap-2">
                    <button @click="openShow(p)" class="px-3 py-1.5 text-indigo-700 bg-indigo-50 rounded hover:bg-indigo-100 text-sm">View</button>
                    <button @click="openEdit(p)" class="px-3 py-1.5 text-blue-700 bg-blue-50 rounded hover:bg-blue-100 text-sm">Edit</button>
                    <button @click="confirmDelete(p)" class="px-3 py-1.5 text-red-700 bg-red-50 rounded hover:bg-red-100 text-sm">Delete</button>
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
      <div v-if="modal==='create'" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="closeModal"></div>
        <div class="relative bg-white w-full max-w-3xl rounded-xl shadow-lg p-6">
          <h2 class="text-xl font-semibold mb-4">Add Psychologist</h2>
          <div class="mb-4 flex items-center gap-4">
            <label class="flex items-center gap-2"><input type="radio" v-model="createMode" value="link"/> <span>Link existing user</span></label>
            <label class="flex items-center gap-2"><input type="radio" v-model="createMode" value="new"/> <span>Create new user</span></label>
          </div>

          <div v-if="createMode==='new'" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
              <label class="text-sm font-medium text-gray-700">Account name</label>
              <input v-model="newUser.name" class="mt-1 block w-full rounded-md border-gray-300" />
            </div>
            <div>
              <label class="text-sm font-medium text-gray-700">Email</label>
              <input v-model="newUser.email" type="email" class="mt-1 block w-full rounded-md border-gray-300" />
            </div>
            <div>
              <label class="text-sm font-medium text-gray-700">Password</label>
              <input v-model="newUser.password" type="password" class="mt-1 block w-full rounded-md border-gray-300" />
            </div>
            <div>
              <label class="text-sm font-medium text-gray-700">Role</label>
              <input value="PSYCHOLOGIST" disabled class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100" />
            </div>
          </div>

          <form @submit.prevent="submitCreate" class="space-y-4">
            <div v-if="createMode==='link'">
              <label class="text-sm font-medium text-gray-700">User (existing)</label>
              <select v-model="createForm.user_id" class="mt-1 block w-full rounded-md border-gray-300">
                <option value="">Select user</option>
                <option v-for="u in psychologistUsers" :key="u.id" :value="u.id">{{ u.name }} ({{ u.email }})</option>
              </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="text-sm font-medium text-gray-700">First name</label>
                <input v-model="createForm.first_name" class="mt-1 block w-full rounded-md border-gray-300" />
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Last name</label>
                <input v-model="createForm.last_name" class="mt-1 block w-full rounded-md border-gray-300" />
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Specialization</label>
                <input v-model="createForm.specialization" class="mt-1 block w-full rounded-md border-gray-300" />
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Price per session (DT)</label>
                <input type="number" step="0.01" v-model="createForm.price_per_session" class="mt-1 block w-full rounded-md border-gray-300" />
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Approved</label>
                <select v-model="createForm.is_approved" class="mt-1 block w-full rounded-md border-gray-300">
                  <option :value="true">Yes</option>
                  <option :value="false">No</option>
                </select>
              </div>
            </div>

            <div>
              <label class="text-sm font-medium text-gray-700">Bio</label>
              <textarea v-model="createForm.bio" class="mt-1 block w-full rounded-md border-gray-300 h-24"></textarea>
            </div>

            <div class="flex items-center gap-3">
              <button :disabled="creating" class="px-4 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700">Create</button>
              <button type="button" @click="closeModal" class="text-sm text-gray-600">Cancel</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Show Modal -->
      <div v-if="modal==='show' && selected" class="fixed inset-0 z-[1000] flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="closeModal"></div>
        <div class="relative bg-white w-full max-w-4xl rounded-xl shadow-lg p-6 max-h-[85vh] overflow-y-auto">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold">Psychologist #{{ selected.id }}</h2>
            <button @click="closeModal" class="text-gray-500 hover:text-gray-700">✕</button>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-1 flex flex-col items-center">
              <img v-if="selected.profile_image_url" :src="selected.profile_image_url" class="h-32 w-32 rounded-full object-cover" />
              <div v-else class="h-32 w-32 rounded-full bg-gray-100 flex items-center justify-center text-gray-400">No photo</div>
              <div class="mt-3 text-center">
                <div class="font-semibold text-gray-900">{{ selected.first_name }} {{ selected.last_name }}</div>
                <div class="text-sm text-gray-600">{{ selected.specialization || '-' }}</div>
              </div>
            </div>
            <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
              <InfoRow label="Email" :value="selected.user?.email" />
              <InfoRow label="Price per session" :value="formatCurrency(selected.price_per_session)" />
              <InfoRow label="Gender" :value="selected.gender" />
              <InfoRow label="Country" :value="selected.country" />
              <InfoRow label="City" :value="selected.city" />
              <InfoRow label="Address" :value="selected.address" />
              <InfoRow label="Approved" :value="selected.is_approved ? 'Yes' : 'No'" />
              <InfoRow label="Date of birth" :value="formatDate(selected.date_of_birth)" />
            </div>
          </div>
          <div class="mt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Bio</h3>
            <p class="text-gray-700 whitespace-pre-line">{{ selected.bio || '—' }}</p>
          </div>
          <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <AttachmentCard v-if="selected.diploma" title="Diploma (PDF)" :href="selected.diploma" />
            <AttachmentCard v-if="selected.cin" title="CIN (PDF)" :href="selected.cin" />
          </div>
          <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <InfoRow label="Account name" :value="selected.user?.name" />
            <InfoRow label="Account active" :value="selected.user?.is_active ? 'Yes' : 'No'" />
          </div>
        </div>
      </div>

      <!-- Edit Modal -->
      <div v-if="modal==='edit' && selected" class="fixed inset-0 z-[1000] flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" @click="closeModal"></div>
        <div class="relative bg-white w-full max-w-4xl rounded-xl shadow-lg p-6 max-h-[85vh] overflow-y-auto">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold">Edit Psychologist #{{ selected.id }}</h2>
            <button @click="closeModal" class="text-gray-500 hover:text-gray-700">✕</button>
          </div>
          <form @submit.prevent="submitEdit" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="text-sm font-medium text-gray-700">First name</label>
                <input v-model="editForm.first_name" class="mt-1 block w-full rounded-md border-gray-300" />
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Last name</label>
                <input v-model="editForm.last_name" class="mt-1 block w-full rounded-md border-gray-300" />
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Specialization</label>
                <input v-model="editForm.specialization" class="mt-1 block w-full rounded-md border-gray-300" />
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Price per session (DT)</label>
                <input type="number" step="0.01" v-model="editForm.price_per_session" class="mt-1 block w-full rounded-md border-gray-300" />
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Approved</label>
                <select v-model="editForm.is_approved" class="mt-1 block w-full rounded-md border-gray-300">
                  <option :value="true">Yes</option>
                  <option :value="false">No</option>
                </select>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Gender</label>
                <select v-model="editForm.gender" class="mt-1 block w-full rounded-md border-gray-300">
                  <option value="">Select gender</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Country</label>
                <input v-model="editForm.country" class="mt-1 block w-full rounded-md border-gray-300" />
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">City</label>
                <input v-model="editForm.city" class="mt-1 block w-full rounded-md border-gray-300" />
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Address</label>
                <input v-model="editForm.address" class="mt-1 block w-full rounded-md border-gray-300" />
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Date of birth</label>
                <input type="date" v-model="editForm.date_of_birth" class="mt-1 block w-full rounded-md border-gray-300" />
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Profile image URL</label>
                <input v-model="editForm.profile_image_url" class="mt-1 block w-full rounded-md border-gray-300" placeholder="https://..." />
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Diploma (PDF URL)</label>
                <input v-model="editForm.diploma" class="mt-1 block w-full rounded-md border-gray-300" placeholder="https://..." />
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">CIN (PDF URL)</label>
                <input v-model="editForm.cin" class="mt-1 block w-full rounded-md border-gray-300" placeholder="https://..." />
              </div>
            </div>

            <div>
              <label class="text-sm font-medium text-gray-700">Bio</label>
              <textarea v-model="editForm.bio" class="mt-1 block w-full rounded-md border-gray-300 h-24"></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="text-sm font-medium text-gray-700">Account email</label>
                <input :value="selected.user?.email || ''" disabled class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100" />
              </div>
              <div class="flex items-end">
                <button v-if="selected.user" type="button" @click="toggleActivation(selected.user)" class="px-4 py-2 rounded-lg" :class="selected.user.is_active ? 'bg-yellow-600 text-white hover:bg-yellow-700' : 'bg-green-600 text-white hover:bg-green-700'">
                  {{ selected.user.is_active ? 'Deactivate' : 'Activate' }}
                </button>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <button :disabled="saving" class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700">Save</button>
              <button type="button" @click="closeModal" class="text-sm text-gray-600">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import { Inertia } from '@inertiajs/inertia'
import { ref, computed, onMounted } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ profiles: Object })

const searchQuery = ref('')
const filtered = computed(() => {
  const q = searchQuery.value.trim().toLowerCase()
  if (!q) return props.profiles.data || []
  return (props.profiles.data || []).filter(p => {
    const fields = [
      String(p.id),
      p.first_name || '',
      p.last_name || '',
      p.specialization || '',
      p.user?.email || '',
    ].map(s => String(s).toLowerCase())
    return fields.some(f => f.includes(q))
  })
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

function closeModal() {
  modal.value = null
  selected.value = null
}

function openShow(p) {
  selected.value = p
  modal.value = 'show'
}

// Edit form
const editForm = useForm({
  _method: 'PUT',
  first_name: '',
  last_name: '',
  specialization: '',
  price_per_session: 0,
  is_approved: false,
  bio: '',
  gender: '',
  country: '',
  city: '',
  address: '',
  date_of_birth: '',
  profile_image_url: '',
  diploma: '',
  cin: '',
})

function openEdit(p) {
  selected.value = p
  editForm.first_name = p.first_name || ''
  editForm.last_name = p.last_name || ''
  editForm.specialization = p.specialization || ''
  editForm.price_per_session = p.price_per_session || 0
  editForm.is_approved = !!p.is_approved
  editForm.bio = p.bio || ''
  editForm.gender = p.gender || ''
  editForm.country = p.country || ''
  editForm.city = p.city || ''
  editForm.address = p.address || ''
  editForm.date_of_birth = formatDateForInput(p.date_of_birth)
  editForm.profile_image_url = p.profile_image_url || ''
  editForm.diploma = p.diploma || ''
  editForm.cin = p.cin || ''
  modal.value = 'edit'
}
function formatDateForInput(dateValue) {
  if (!dateValue) return ''
  const d = new Date(dateValue)
  if (isNaN(d.getTime())) return ''
  return d.toISOString().split('T')[0]
}

const saving = ref(false)
async function submitEdit() {
  if (!selected.value) return
  saving.value = true
  try {
    await editForm.post(route('psychologist-profiles.update', selected.value.id), { preserveScroll: true })
    await Inertia.get(route('psychologist-profiles.index'), {}, { preserveScroll: true, replace: true })
    closeModal()
  } finally {
    saving.value = false
  }
}

// Delete
function confirmDelete(p) {
  if (!confirm(`Delete ${p.first_name} ${p.last_name}?`)) return
  const form = useForm({})
  form.delete(route('psychologist-profiles.destroy', p.id), { preserveScroll: true, onSuccess: () => {
    Inertia.get(route('psychologist-profiles.index'), {}, { preserveScroll: true, replace: true })
  } })
}

// Activate/Deactivate user
async function toggleActivation(user) {
  if (!user) return
  try {
    const url = user.is_active ? `/users/${user.id}/deactivate` : `/users/${user.id}/activate`
    await fetch(url, { method: 'PATCH', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    await Inertia.get(route('psychologist-profiles.index'), {}, { preserveScroll: true, replace: true })
    closeModal()
  } catch {}
}

// Create modal
const createMode = ref('link') // 'link' | 'new'
const psychologistUsers = ref([])
const creating = ref(false)
const createForm = useForm({
  user_id: '',
  first_name: '',
  last_name: '',
  specialization: '',
  price_per_session: 0,
  is_approved: false,
  bio: '',
  gender: '',
  country: '',
  city: '',
  address: '',
  date_of_birth: '',
  profile_image_url: '',
  diploma: '',
  cin: '',
})
const newUser = ref({ name: '', email: '', password: '' })

function openCreate() {
  modal.value = 'create'
}

onMounted(async () => {
  try {
    const res = await fetch('/users')
    const all = await res.json()
    psychologistUsers.value = (all || []).filter(u => u.role === 'PSYCHOLOGIST')
  } catch {}
})

async function submitCreate() {
  creating.value = true
  try {
    let userId = createForm.user_id
    if (createMode.value === 'new') {
      const res = await fetch('/users', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ name: newUser.value.name, email: newUser.value.email, password: newUser.value.password, role: 'PSYCHOLOGIST' })
      })
      const created = await res.json()
      userId = created.id
    }
    const payload = { ...createForm.data(), user_id: userId }
    const form = useForm(payload)
    await form.post(route('psychologist-profiles.store'), { preserveScroll: true })
    await Inertia.get(route('psychologist-profiles.index'), {}, { preserveScroll: true, replace: true })
    closeModal()
  } finally {
    creating.value = false
  }
}
</script>

<script>
export default {
  layout: AdminLayout,
  name: 'Admin/Psychologist/Index'
}
</script>
