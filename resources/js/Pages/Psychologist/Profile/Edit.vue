<template>
    <div class="w-full flex flex-col md:flex-row items-start gap-6">
      <div class="md:w-3/4 mr-auto">
        <section>
        <header class="mb-6 pl-4 border-l-4 border-indigo-500">
          <h2 class="text-2xl font-semibold text-gray-900">Edit Psychologist Profile</h2>
          <p class="mt-1 text-sm text-gray-600">Update your information so patients see up-to-date details.</p>
        </header>

        <form @submit.prevent="submit" enctype="multipart/form-data" class="space-y-6 bg-white p-6 rounded-lg shadow-lg">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <InputLabel value="First name" />
              <TextInput class="mt-1 block w-full" v-model="form.first_name" />
              <InputError class="mt-2" :message="form.errors.first_name" />
            </div>
            <div>
              <InputLabel value="Last name" />
              <TextInput class="mt-1 block w-full" v-model="form.last_name" />
              <InputError class="mt-2" :message="form.errors.last_name" />
            </div>
            <div>
              <InputLabel value="Specialization" />
              <TextInput class="mt-1 block w-full" v-model="form.specialization" />
              <InputError class="mt-2" :message="form.errors.specialization" />
            </div>
            <div>
              <InputLabel value="Gender" />
              <select v-model="form.gender" class="mt-1 block w-full rounded-md border-gray-300">
                <option value="">Select gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
              </select>
              <InputError class="mt-2" :message="form.errors.gender" />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <InputLabel value="Country" />
              <TextInput class="mt-1 block w-full" v-model="form.country" />
              <InputError class="mt-2" :message="form.errors.country" />
            </div>
            <div>
              <InputLabel value="City" />
              <TextInput class="mt-1 block w-full" v-model="form.city" />
              <InputError class="mt-2" :message="form.errors.city" />
            </div>
          </div>

          <div>
            <InputLabel value="Address" />
            <TextInput class="mt-1 block w-full" v-model="form.address" />
            <InputError class="mt-2" :message="form.errors.address" />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <InputLabel value="Date of birth" />
              <input type="date" v-model="form.date_of_birth" class="mt-1 block w-full rounded-md border-gray-300" />
              <InputError class="mt-2" :message="form.errors.date_of_birth" />
            </div>
            <div>
              <InputLabel value="Price per session (DT)" />
              <div class="mt-1 flex">
                <input type="number" step="0.01" v-model="form.price_per_session" class="block w-full rounded-l-md border-gray-300" />
                <span class="inline-flex items-center px-3 rounded-r-md bg-gray-50 border border-l-0 border-gray-300">DT</span>
              </div>
              <InputError class="mt-2" :message="form.errors.price_per_session" />
            </div>
            <!-- profile image moved to the right upload panel -->
          </div>

          <div>
            <InputLabel value="Bio" />
            <textarea v-model="form.bio" class="mt-1 block w-full rounded-md border-gray-300 h-28"></textarea>
            <InputError class="mt-2" :message="form.errors.bio" />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <InputLabel value="Diploma (PDF)" />
              <input @change="onFileChange('diploma_file', $event)" type="file" accept="application/pdf" class="mt-1 block w-full" />
              <div v-if="form.diploma && !files.diploma_file" class="mt-2 text-sm text-gray-600">Current: <a :href="form.diploma" target="_blank">View</a></div>
              <InputError class="mt-2" :message="form.errors.diploma_file" />
            </div>
            <div>
              <InputLabel value="CIN (PDF)" />
              <input @change="onFileChange('cin_file', $event)" type="file" accept="application/pdf" class="mt-1 block w-full" />
              <div v-if="form.cin && !files.cin_file" class="mt-2 text-sm text-gray-600">Current: <a :href="form.cin" target="_blank">View</a></div>
              <InputError class="mt-2" :message="form.errors.cin_file" />
            </div>
          </div>

          <div class="flex items-center gap-3">
            <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
            <Link :href="route('dashboard')" class="text-sm text-gray-600">Cancel</Link>
          </div>
        </form>
      </section>
      </div>

      <!-- Right-side image upload panel -->
      <aside class="md:w-1/4 w-full flex-shrink-0 mt-16 lg:mt-0">
        <div class="bg-white p-4 rounded-lg shadow flex flex-col items-center gap-4">
          <div @click.prevent="triggerProfileInput" class="group relative w-32 h-32 bg-gray-100 rounded-full overflow-hidden flex items-center justify-center cursor-pointer hover:ring-2 hover:ring-indigo-300 transition" title="Click to change profile photo">
            <img v-if="profilePreview" :src="profilePreview" class="w-full h-full object-cover" />
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7M16 3v4M8 3v4m0 0h8" />
            </svg>
            <div class="absolute inset-0 bg-black bg-opacity-25 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7M16 3v4M8 3v4m0 0h8" />
              </svg>
            </div>
          </div>
          <input ref="profileInput" @change="onFileChange('profile_image', $event)" type="file" accept="image/*" class="hidden" />
          <button @click.prevent="triggerProfileInput" type="button" class="mt-2 inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-lg shadow-lg hover:from-indigo-600 hover:to-purple-600 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm6 3a1 1 0 112 0v3h2a1 1 0 110 2h-2v3a1 1 0 11-2 0v-3H8a1 1 0 110-2h2V6z" />
            </svg>
            <span class="font-medium">Upload Image</span>
          </button>
        </div>
      </aside>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { Link, usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'

const props = defineProps({ profile: Object })

// Format date for HTML date input (YYYY-MM-DD)
function formatDateForInput(dateValue) {
  if (!dateValue) return null
  const date = new Date(dateValue)
  if (isNaN(date.getTime())) return null
  return date.toISOString().split('T')[0]
}

const form = useForm({
  _method: 'PUT',
  first_name: props.profile?.first_name || '',
  last_name: props.profile?.last_name || '',
  specialization: props.profile?.specialization || '',
  bio: props.profile?.bio || '',
  price_per_session: props.profile?.price_per_session || 0,
  date_of_birth: formatDateForInput(props.profile?.date_of_birth),
  profile_image_url: props.profile?.profile_image_url || null,
  diploma: props.profile?.diploma || null,
  cin: props.profile?.cin || null,
  profile_image: null,
  diploma_file: null,
  cin_file: null,
  gender: props.profile?.gender || '',
  country: props.profile?.country || '',
  city: props.profile?.city || '',
  address: props.profile?.address || '',
})

const files = ref({ profile_image: null, diploma_file: null, cin_file: null })
const profilePreview = ref(form.profile_image_url)
const profileInput = ref(null)

function onFileChange(field, event) {
  const file = event.target.files[0]
  files.value[field] = file
  form[field] = file
  if (field === 'profile_image' && file) {
    profilePreview.value = URL.createObjectURL(file)
  }
}

function submit() {
  form.post(route('psychologist.profile.update'), { forceFormData: true })
}

function triggerProfileInput() {
  if (profileInput.value) profileInput.value.click()
}
</script>

<script>
import UserLayout from '@/Layouts/UserLayout.vue'
export default {
  layout: UserLayout,
  name: 'Psychologist/Profile/Edit',
}
</script>
