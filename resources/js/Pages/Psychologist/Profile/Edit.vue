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
              <InputLabel value="Specialisations" />
              <div class="mt-1">
                <Multiselect
                  v-model="form.specialisation_ids"
                  :options="specialisationOptions"
                  mode="tags"
                  :close-on-select="false"
                  :searchable="true"
                  placeholder="Select one or more"
                />
              </div>
              <InputError class="mt-2" :message="form.errors.specialisation_ids" />
            </div>
            <div>
              <InputLabel value="Languages" />
              <div class="mt-1">
                <Multiselect
                  v-model="form.languages"
                  :options="languageOptions"
                  mode="tags"
                  :close-on-select="false"
                  :searchable="true"
                  placeholder="Select one or more"
                />
              </div>
              <InputError class="mt-2" :message="form.errors.languages" />
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
              <select v-model="countryCode" class="mt-1 block w-full rounded-md border-gray-300">
                <option value="">Select country</option>
                <option v-for="c in countriesList" :key="c.isoCode" :value="c.isoCode">{{ c.name }}</option>
              </select>
              <InputError class="mt-2" :message="form.errors.country" />
            </div>
            <div>
              <InputLabel value="City" />
              <select v-model="form.city" class="mt-1 block w-full rounded-md border-gray-300" :disabled="!form.country">
                <option value="">{{ form.country ? 'Select city' : 'Select country first' }}</option>
                <option v-for="ct in cities" :key="ct" :value="ct">{{ ct }}</option>
              </select>
              <InputError class="mt-2" :message="form.errors.city" />
            </div>
          </div>

          <div>
            <InputLabel value="Address" />
            <TextInput class="mt-1 block w-full" v-model="form.address" />
            <InputError class="mt-2" :message="form.errors.address" />
          </div>

          <div>
            <InputLabel value="Phone" />
            <div class="mt-1 flex">
              <input
                v-model="dialCode"
                readonly
                class="w-24 rounded-l-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700"
                placeholder="+___"
              />
              <input
                v-model="nationalNumber"
                inputmode="tel"
                class="block w-full rounded-r-md border border-l-0 border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
                placeholder="Enter phone number"
              />
            </div>
            <InputError class="mt-2" :message="form.errors.phone" />
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
              <InputLabel value="CV (PDF)" />
              <input @change="onFileChange('cv_file', $event)" type="file" accept="application/pdf" class="mt-1 block w-full" />
              <div v-if="form.cv && !files.cv_file" class="mt-2 text-sm text-gray-600">Current: <a :href="form.cv" target="_blank">View</a></div>
              <InputError class="mt-2" :message="form.errors.cv_file" />
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
import { ref, computed, watch, nextTick } from 'vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { getCountries, getCitiesByCountryName, splitInternationalPhoneNumber } from '@/utils/geoData'
import Multiselect from '@vueform/multiselect'

const props = defineProps({
  profile: Object,
  specialisations: {
    type: Array,
    default: () => [],
  },
})

const specialisationOptions = computed(() =>
  (props.specialisations || []).map((s) => ({ value: s.id, label: s.name }))
)

const languageOptions = [
  { value: 'english', label: 'English' },
  { value: 'french', label: 'French' },
  { value: 'arabic', label: 'Arabic' },
]

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
  languages: Array.isArray(props.profile?.languages) ? props.profile.languages : [],
  specialisation_ids: (props.profile?.specialisations || []).map(s => s.id),
  bio: props.profile?.bio || '',
  price_per_session: props.profile?.price_per_session || 0,
  date_of_birth: formatDateForInput(props.profile?.date_of_birth),
  profile_image_url: props.profile?.profile_image_url || null,
  diploma: props.profile?.diploma || null,
  cv: props.profile?.cv || null,
  profile_image: null,
  diploma_file: null,
  cv_file: null,
  gender: props.profile?.gender || '',
  country: props.profile?.country || '',
  city: props.profile?.city || '',
  address: props.profile?.address || '',
  phone: props.profile?.phone || '',
  country_code: props.profile?.country_code || '',
})

const files = ref({ profile_image: null, diploma_file: null, cv_file: null })
const profilePreview = ref(form.profile_image_url)
const profileInput = ref(null)

// Country/City/Phone helpers
const countriesList = ref(getCountries())
const countryCode = ref('')
const dialCode = ref('')
const nationalNumber = ref('')

const dialCodes = computed(() => countriesList.value.map(c => c.dialCode).filter(Boolean))

const cities = computed(() => {
  if (!form.country) return []
  return getCitiesByCountryName(form.country).map(c => c.name)
})

function syncPhoneToForm() {
  form.country_code = dialCode.value || ''
  form.phone = `${dialCode.value || ''}${nationalNumber.value || ''}`
}

// Initialize country/city/phone from existing profile
const found = countriesList.value.find(c => c.name === (props.profile?.country || ''))
countryCode.value = found?.isoCode || ''

const parsed = splitInternationalPhoneNumber(props.profile?.phone || '', dialCodes.value)
dialCode.value = parsed.dialCode || (found?.dialCode || '')
nationalNumber.value = parsed.nationalNumber || ''
syncPhoneToForm()

watch(countryCode, (code) => {
  const c = countriesList.value.find(x => x.isoCode === code)
  form.country = c?.name || ''
  form.city = ''
  dialCode.value = c?.dialCode || ''
  syncPhoneToForm()
})

watch([dialCode, nationalNumber], () => syncPhoneToForm())

function onFileChange(field, event) {
  const file = event.target.files[0]
  files.value[field] = file
  form[field] = file
  if (field === 'profile_image' && file) {
    profilePreview.value = URL.createObjectURL(file)
  }
}

function submit() {
  syncPhoneToForm()
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
