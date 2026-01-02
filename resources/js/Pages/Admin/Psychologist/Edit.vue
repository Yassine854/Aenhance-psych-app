<template>
  <div class="p-6 space-y-6">
      <header class="mb-4">
        <h1 class="text-2xl font-semibold text-gray-900">Edit Psychologist</h1>
        <p class="text-sm text-gray-600">Update basic profile information.</p>
      </header>

      <form @submit.prevent="submit" class="bg-white p-6 rounded-lg shadow space-y-4">
        <!-- Row 1: First, Last, Specialization -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <div>
            <label class="text-sm font-medium text-gray-700">First name</label>
            <input v-model="form.first_name" class="mt-1 block w-full rounded-md border-gray-300" />
            <p class="mt-2 text-sm text-red-600" v-if="form.errors.first_name">{{ form.errors.first_name }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">Last name</label>
            <input v-model="form.last_name" class="mt-1 block w-full rounded-md border-gray-300" />
            <p class="mt-2 text-sm text-red-600" v-if="form.errors.last_name">{{ form.errors.last_name }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">Specialization</label>
            <input v-model="form.specialization" class="mt-1 block w-full rounded-md border-gray-300" />
            <p class="mt-2 text-sm text-red-600" v-if="form.errors.specialization">{{ form.errors.specialization }}</p>
          </div>
        </div>

        <!-- Row 2: Gender (4th), Date of Birth (5th), Price (6th) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <div>
            <label class="text-sm font-medium text-gray-700">Gender</label>
            <select v-model="form.gender" class="mt-1 block w-full rounded-md border-gray-300">
              <option value="">Select gender</option>
              <option value="MALE">Male</option>
              <option value="FEMALE">Female</option>
              <option value="OTHER">Other</option>
            </select>
            <p class="mt-2 text-sm text-red-600" v-if="form.errors.gender">{{ form.errors.gender }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">Date of birth</label>
            <input type="date" v-model="form.date_of_birth" class="mt-1 block w-full rounded-md border-gray-300" />
            <p class="mt-2 text-sm text-red-600" v-if="form.errors.date_of_birth">{{ form.errors.date_of_birth }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">Price per session (DT)</label>
            <input type="number" step="0.01" v-model="form.price_per_session" class="mt-1 block w-full rounded-md border-gray-300" />
            <p class="mt-2 text-sm text-red-600" v-if="form.errors.price_per_session">{{ form.errors.price_per_session }}</p>
          </div>
        </div>

        <!-- Row 3: Country, City, Phone -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <div>
            <label class="text-sm font-medium text-gray-700">Country</label>
            <input v-model="form.country" class="mt-1 block w-full rounded-md border-gray-300" />
            <p class="mt-2 text-sm text-red-600" v-if="form.errors.country">{{ form.errors.country }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">City</label>
            <input v-model="form.city" class="mt-1 block w-full rounded-md border-gray-300" />
            <p class="mt-2 text-sm text-red-600" v-if="form.errors.city">{{ form.errors.city }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">Phone</label>
            <input v-model="form.phone" class="mt-1 block w-full rounded-md border-gray-300" />
            <p class="mt-2 text-sm text-red-600" v-if="form.errors.phone">{{ form.errors.phone }}</p>
          </div>
        </div>

        <!-- Stylish Uploads (CIN first, then Diploma, then Profile) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <!-- CIN PDF -->
          <div>
            <label class="text-sm font-medium text-gray-700">CIN (PDF)</label>
            <div
              class="mt-1 group relative flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-4 text-center cursor-pointer transition hover:bg-gray-100 hover:border-[rgb(89,151,172)]"
              @click="() => cinInput?.click()"
              @dragover.prevent
              @drop.prevent="onDrop('cin', $event)"
            >
              <input ref="cinInput" type="file" accept="application/pdf" class="hidden" @change="onFileChange('cin', $event)" />
              <div class="flex items-center gap-2 text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor"><path d="M6 2h9l5 5v13a2 2 0 01-2 2H6a2 2 0 01-2-2V4a2 2 0 012-2zm8 1.5V8h4.5L14 3.5z"/></svg>
                <span class="text-xs">{{ cinLabel }}</span>
              </div>
            </div>
            <p class="mt-2 text-sm text-red-600" v-if="form.errors.cin">{{ form.errors.cin }}</p>
          </div>

          <!-- Diploma PDF -->
          <div>
            <label class="text-sm font-medium text-gray-700">Diploma (PDF)</label>
            <div
              class="mt-1 group relative flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-4 text-center cursor-pointer transition hover:bg-gray-100 hover:border-[rgb(89,151,172)]"
              @click="() => diplomaInput?.click()"
              @dragover.prevent
              @drop.prevent="onDrop('diploma', $event)"
            >
              <input ref="diplomaInput" type="file" accept="application/pdf" class="hidden" @change="onFileChange('diploma', $event)" />
              <div class="flex items-center gap-2 text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor"><path d="M6 2h9l5 5v13a2 2 0 01-2 2H6a2 2 0 01-2-2V4a2 2 0 012-2zm8 1.5V8h4.5L14 3.5z"/></svg>
                <span class="text-xs">{{ diplomaLabel }}</span>
              </div>
            </div>
            <p class="mt-2 text-sm text-red-600" v-if="form.errors.diploma">{{ form.errors.diploma }}</p>
          </div>

          <!-- Profile Image -->
          <div>
            <label class="text-sm font-medium text-gray-700">Profile image</label>
            <div
              class="mt-1 group relative flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-4 text-center cursor-pointer transition hover:bg-gray-100 hover:border-[rgb(89,151,172)]"
              @click="() => profileInput?.click()"
              @dragover.prevent
              @drop.prevent="onDrop('profile_image', $event)"
            >
              <input ref="profileInput" type="file" accept="image/*" class="hidden" @change="onFileChange('profile_image', $event)" />
              <div v-if="imagePreview" class="flex flex-col items-center gap-2">
                <img :src="imagePreview" class="h-16 w-16 rounded-full object-cover ring-1 ring-gray-200" />
                <span class="text-xs text-gray-600">Click to replace</span>
              </div>
              <div v-else class="flex flex-col items-center gap-2 text-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5a7 7 0 100 14 7 7 0 000-14zm0 2a2.5 2.5 0 11-.001 5.001A2.5 2.5 0 0112 7zm0 10a5.5 5.5 0 01-4.686-2.5 3.5 3.5 0 016.372 0A5.5 5.5 0 0112 17z"/></svg>
                <span class="text-xs">Drag & drop or click</span>
              </div>
            </div>
            <p class="mt-2 text-sm text-red-600" v-if="form.errors.profile_image">{{ form.errors.profile_image }}</p>
          </div>
        </div>

        <!-- Last row: Address and Approved -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
          <div class="md:col-span-2">
            <label class="text-sm font-medium text-gray-700">Address</label>
            <input v-model="form.address" class="mt-1 block w-full rounded-md border-gray-300" />
            <p class="mt-2 text-sm text-red-600" v-if="form.errors.address">{{ form.errors.address }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-700">Approved</label>
            <select v-model="form.is_approved" class="mt-1 block w-full rounded-md border-gray-300">
              <option :value="true">Yes</option>
              <option :value="false">No</option>
            </select>
          </div>
        </div>

        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-700">Bio</label>
          <textarea v-model="form.bio" class="mt-1 block w-full rounded-md border-gray-300 h-24"></textarea>
        </div>

        <div class="flex items-center gap-3">
          <button :disabled="form.processing" class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700">Save</button>
          <Link :href="route('psychologist-profiles.show', profile.id)" class="text-sm text-gray-600">Cancel</Link>
        </div>
      </form>
    </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({ profile: Object })

const form = useForm({
  _method: 'PUT',
  first_name: props.profile?.first_name || '',
  last_name: props.profile?.last_name || '',
  specialization: props.profile?.specialization || '',
  price_per_session: props.profile?.price_per_session || 0,
  gender: props.profile?.gender || '',
  date_of_birth: props.profile?.date_of_birth || '',
  country: props.profile?.country || '',
  city: props.profile?.city || '',
  address: props.profile?.address || '',
  phone: props.profile?.phone || '',
  is_approved: !!props.profile?.is_approved,
  bio: props.profile?.bio || '',
  profile_image: null,
  diploma: null,
  cin: null,
})

function submit() {
  form.post(route('psychologist-profiles.update', props.profile.id), { forceFormData: true })
}

// Upload helpers
const profileInput = ref(null)
const diplomaInput = ref(null)
const cinInput = ref(null)

function onFileChange(field, e) {
  const file = e?.target?.files?.[0] || null
  form[field] = file
}

function onDrop(field, e) {
  const file = e?.dataTransfer?.files?.[0]
  if (!file) return
  if (field === 'profile_image' && !file.type.startsWith('image/')) return
  if ((field === 'diploma' || field === 'cin') && file.type !== 'application/pdf') return
  form[field] = file
}

const imagePreview = computed(() => {
  if (form.profile_image) return URL.createObjectURL(form.profile_image)
  return props.profile?.profile_image_url || ''
})

function fileNameFromUrl(url) {
  if (!url) return ''
  try { return url.split('/').pop() || '' } catch { return '' }
}

const diplomaLabel = computed(() => form.diploma?.name || fileNameFromUrl(props.profile?.diploma) || 'Drag & drop or click')
const cinLabel = computed(() => form.cin?.name || fileNameFromUrl(props.profile?.cin) || 'Drag & drop or click')
</script>

<script>
export default {
  layout: AdminLayout,
  name: 'Admin/Psychologist/Edit'
}
</script>
