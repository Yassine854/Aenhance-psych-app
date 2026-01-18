<script setup>
import Navbar from '@/Components/Navbar.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import Textarea from '@/Components/Textarea.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import Multiselect from '@vueform/multiselect'
import { Head, useForm, usePage, Link } from '@inertiajs/vue3'
import { computed, ref, watch, nextTick } from 'vue'
import { getCountries, getCitiesByCountryName } from '@/utils/geoData'
import Swal from 'sweetalert2'

const props = defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
  authUser: Object,
  profile: Object,
  specialisations: Array,
  expertises: Array,
  cv_required: Boolean,
  diplomas_required: Boolean,
  status: String,
})

const page = usePage()

function formatDateForInput(dateValue) {
  if (!dateValue) return ''
  // Inertia often sends YYYY-MM-DD already; keep it if so.
  if (typeof dateValue === 'string' && /^\d{4}-\d{2}-\d{2}$/.test(dateValue)) return dateValue
  const date = new Date(dateValue)
  if (Number.isNaN(date.getTime())) return ''
  return date.toISOString().split('T')[0]
}

const form = useForm({
  first_name: props.profile?.first_name || '',
  last_name: props.profile?.last_name || '',
  date_of_birth: formatDateForInput(props.profile?.date_of_birth),
  gender: props.profile?.gender || '',
  country: props.profile?.country || '',
  city: props.profile?.city || '',
  address: props.profile?.address || '',
  phone: props.profile?.phone || '',
  country_code: props.profile?.country_code || '',
  bio: props.profile?.bio || '',
  price_per_session: props.profile?.price_per_session || '',
  languages: props.profile?.languages || [],
  specialisation_ids: props.profile?.specialisations?.map(s => s.id) || [],
  expertise_ids: props.profile?.expertises?.map(e => e.id) || [],
  profile_image: null,
  cv: null,
  diploma_files: [],
  remove_diplomas: [],
})

const countriesList = ref(getCountries())
const countryCode = ref('')
const dialCode = ref('')
const nationalNumber = ref('')
const availableLanguages = [
  'English', 'French', 'Arabic'
]
const selectedLanguage = ref('')
const diplomaInput = ref(null)

const dialCodes = computed(() => countriesList.value.map(c => c.dialCode).filter(Boolean))

const cities = computed(() => {
  if (!form.country) return []
  return getCitiesByCountryName(form.country).map(c => c.name)
})

const imagePreview = computed(() => {
  if (form.profile_image) return URL.createObjectURL(form.profile_image)
  return ''
})

const headerImage = computed(() => {
  if (form.remove_profile_image) return ''
  return imagePreview.value || props.profile?.profile_image_url || page.props?.auth?.user?.psychologistProfile?.profile_image_url || ''
})

const specialisationOptions = computed(() =>
  (props.specialisations || []).map((s) => ({ value: s.id, label: s.name }))
)

const expertiseOptions = computed(() =>
  (props.expertises || []).map((e) => ({ value: e.id, label: e.name }))
)

function syncPhoneToForm() {
  form.country_code = dialCode.value || ''
  const cleanNational = (nationalNumber.value || '').toString().replace(/\D/g, '')
  form.phone = cleanNational
}

// Init country code from existing profile country
const found = countriesList.value.find(c => c.name === (props.profile?.country || ''))
countryCode.value = found?.isoCode || ''

// Init phone parts from stored phone
nationalNumber.value = props.profile?.phone || ''
dialCode.value = props.profile?.country_code || (found?.dialCode || '')
syncPhoneToForm()

watch(countryCode, (code) => {
  const c = countriesList.value.find(x => x.isoCode === code)
  form.country = c?.name || ''
  form.city = ''
  dialCode.value = c?.dialCode || ''
  syncPhoneToForm()
})

watch([dialCode, nationalNumber], () => syncPhoneToForm())

function onFileChange(e) {
  const file = e?.target?.files?.[0] || null
  form.profile_image = file
  if (file) form.remove_profile_image = false
}

function removePhoto() {
  form.profile_image = null
  form.remove_profile_image = true
}

function onCvChange(e) {
  const file = e?.target?.files?.[0] || null
  form.cv = file
}

function addLanguage() {
  if (selectedLanguage.value && !form.languages.some(lang => lang.toLowerCase() === selectedLanguage.value.toLowerCase())) {
    form.languages.push(selectedLanguage.value)
    selectedLanguage.value = ''
  }
}

function removeLanguage(lang) {
  const index = form.languages.indexOf(lang)
  if (index > -1) {
    form.languages.splice(index, 1)
  }
}

function onDiplomaChange(e) {
  const files = e.target.files
  if (files && files.length > 0) {
    form.diploma_files = [...form.diploma_files, ...Array.from(files)]
    e.target.value = '' // reset to allow selecting same files again
  }
}

function submit() {
  syncPhoneToForm()
  form.post(route('psychologist.profile.self.update'), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: (page) => {
      // Clear diploma files after successful submission
      form.diploma_files.splice(0)
      // Reset file input
      if (diplomaInput.value) {
        diplomaInput.value.value = ''
      }
      // Show success message
      Swal.fire({
        position: 'top-end',
        icon: 'success',
        title: 'Profile updated successfully!',
        showConfirmButton: false,
        timer: 3000,
        toast: true,
        timerProgressBar: true,
        showCloseButton: true,
      })
    },
    onError: (errors) => {
      Swal.fire({
        position: 'top-end',
        icon: 'error',
        title: 'Update failed',
        text: 'Please check the form for errors.',
        showConfirmButton: false,
        timer: 3000,
        toast: true,
        timerProgressBar: true,
        showCloseButton: true,
      })
    }
  })
}

// Watch for form errors to show error SweetAlert
watch(() => Object.keys(form.errors).length, (errorCount, oldErrorCount) => {
  if (errorCount > oldErrorCount && errorCount > 0) {
    const firstError = Object.values(form.errors)[0]
    Swal.fire({
      position: 'top-end',
      icon: 'error',
      title: 'Validation Error',
      text: Array.isArray(firstError) ? firstError[0] : firstError,
      showConfirmButton: false,
      timer: 4000,
      toast: true,
      timerProgressBar: true,
      showCloseButton: true,
    })
  }
})
</script>

<template>
  <Head title="Profile" />

  <Navbar
    :canLogin="canLogin"
    :canRegister="canRegister"
    :authUser="authUser || page.props?.auth?.user"
  />

  <div class="min-h-[calc(100vh-112px)] bg-gray-50">
    <div class="bg-gradient-to-r from-[#af5166] to-[#5997ac]">
      <div class="mx-auto max-w-6xl px-4 py-8">
        <div class="flex items-center gap-4">
          <div class="h-14 w-14 rounded-full overflow-hidden ring-2 ring-white/70 bg-white/15 flex items-center justify-center">
            <img v-if="headerImage" :src="headerImage" class="h-full w-full object-cover" />
            <span v-else class="text-white font-semibold">{{ (authUser?.name || authUser?.email || 'A').slice(0, 1).toUpperCase() }}</span>
          </div>
          <div>
            <h1 class="text-2xl sm:text-3xl font-semibold text-white">Profile info</h1>
            <p class="mt-1 text-sm text-white/90">Update your professional information and manage your practice.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Approval Status Message -->
    <div v-if="!profile?.is_approved" class="mx-auto max-w-6xl px-4 py-6">
      <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
        <div class="flex items-center gap-3">
          <div class="w-5 h-5 text-yellow-600 flex-shrink-0">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
          </div>
          <div>
            <h3 class="text-sm font-medium text-yellow-800">Profile Under Review</h3>
            <p class="text-sm text-yellow-700 mt-1">Your profile is currently waiting for approval. You can still update your information, but it won't be visible to patients until approved.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Approved Status Message -->
    <div v-else-if="profile?.is_approved" class="mx-auto max-w-6xl px-4 py-6">
      <div class="bg-green-50 border border-green-200 rounded-xl p-4">
        <div class="flex items-center gap-3">
          <div class="w-5 h-5 text-green-600 flex-shrink-0">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <div>
            <h3 class="text-sm font-medium text-green-800">Profile Approved</h3>
            <p class="text-sm text-green-700 mt-1">Congratulations! Your profile has been approved and is now visible to patients. You can continue updating your information as needed.</p>
          </div>
        </div>
      </div>
    </div>

    <div class="mx-auto max-w-6xl px-4 py-8">
      <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Photo Section - Displayed first on mobile/tablet -->
        <aside class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden order-first lg:order-last lg:col-span-1">
          <div class="bg-gradient-to-r from-[#5997ac] to-[#7ba8b7] px-6 py-4">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
              </div>
              <div>
                <h2 class="text-lg font-semibold text-white">Profile Overview</h2>
                <p class="text-sm text-white/80">Your photo, bio, and pricing</p>
              </div>
            </div>
          </div>
          <div class="p-6 sm:p-8">
            

            <div class="flex flex-col items-center space-y-6">
              <div class="relative group">
                <div class="relative h-32 w-32 rounded-2xl overflow-hidden bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center ring-4 ring-[#5997ac]/20 group-hover:ring-[#5997ac]/40 transition-all duration-300">
                  <img
                    v-if="headerImage"
                    :src="headerImage"
                    alt="Profile photo"
                    class="h-full w-full object-cover"
                  />
                  <div v-else class="flex flex-col items-center justify-center text-gray-400">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    <span class="mt-2 text-sm font-medium">No photo</span>
                  </div>

                  <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center rounded-2xl">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </div>
                </div>

                <!-- Verification Badge -->
                <div v-if="profile?.is_approved" class="absolute -top-2 -left-2 w-7 h-7 bg-blue-500 rounded-full flex items-center justify-center ring-2 ring-white shadow-sm">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                  </svg>
                </div>

                <label class="absolute -bottom-2 -right-2 h-10 w-10 rounded-full bg-[#5997ac] text-white shadow-lg flex items-center justify-center cursor-pointer hover:bg-[#4a7a95] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#5997ac] transition-colors duration-200">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                  </svg>
                  <input type="file" accept="image/*" class="hidden" @change="onFileChange" />
                </label>

                <button
                  v-if="!form.remove_profile_image && (headerImage || props.profile?.profile_image_url || page.props?.auth?.user?.psychologistProfile?.profile_image_url)"
                  type="button"
                  @click="removePhoto"
                  class="absolute -top-2 -right-2 h-8 w-8 rounded-full bg-red-500 text-white shadow-lg flex items-center justify-center hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200"
                  title="Remove photo"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
                </button>
              </div>

              <InputError class="w-full" :message="form.errors.profile_image" />
              <div class="w-full p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200">
                <div class="flex items-start gap-3">
                  <div class="w-5 h-5 text-blue-600 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-blue-900">Profile Photo Tips</p>
                    <p class="text-xs text-blue-700 mt-1">Upload a professional photo to build trust with patients. Recommended size: 400x400px, max 5MB.</p>
                  </div>
                </div>
              </div>

              <!-- Bio Section -->
              <div class="w-full space-y-2">
                <InputLabel value="Bio" class="text-sm font-medium text-gray-700" />
                <div class="relative">
                  <Textarea
                    v-model="form.bio"
                    rows="4"
                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#5997ac] focus:border-transparent transition-colors duration-200 resize-none"
                    placeholder="Tell patients about your experience, approach, and what they can expect from sessions with you..."
                  />
                  <div class="absolute top-3 left-3 pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                  </div>
                </div>
                <InputError class="mt-2" :message="form.errors.bio" />
              </div>

              <!-- Price Section -->
              <div class="w-full space-y-2">
                <InputLabel class="text-sm font-medium text-gray-700">
                  Price per session (TND) <span class="text-red-500">*</span>
                  <span v-if="profile?.is_approved" class="text-xs text-gray-500 font-normal"></span>
                </InputLabel>
                <div class="relative">
                  <div class="relative">
                    <input
                      v-model="form.price_per_session"
                      type="number"
                      step="0.01"
                      min="0"
                      :readonly="profile?.is_approved"
                      :class="profile?.is_approved ? 'bg-gray-100 cursor-not-allowed' : ''"
                      class="w-full pl-16 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#5997ac] focus:border-transparent transition-colors duration-200"
                      placeholder="0.00"
                    />
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                      <span class="text-gray-500 text-sm font-medium">TND</span>
                    </div>
                  </div>
                </div>
                <InputError class="mt-2" :message="form.errors.price_per_session" />
              </div>

              
            </div>
          </div>
        </aside>

        <!-- Main form -->
        <div class="lg:col-span-2 space-y-8">
          <!-- Personal Information Section -->
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-[#5997ac] to-[#7ba8b7] px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                  </svg>
                </div>
                <div>
                  <h2 class="text-lg font-semibold text-white">Personal Information</h2>
                  <p class="text-sm text-white/80">Your basic personal details</p>
                </div>
              </div>
            </div>
            <div class="p-6 sm:p-8">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <InputLabel class="text-sm font-medium text-gray-700">
                    First Name <span class="text-red-500">*</span>
                  </InputLabel>
                  <div class="relative">
                    <TextInput v-model="form.first_name" class="mt-1 block w-full pl-10" placeholder="Enter first name" />
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                      </svg>
                    </div>
                  </div>
                  <InputError class="mt-2" :message="form.errors.first_name" />
                </div>
                <div class="space-y-2">
                  <InputLabel class="text-sm font-medium text-gray-700">
                    Last Name <span class="text-red-500">*</span>
                  </InputLabel>
                  <div class="relative">
                    <TextInput v-model="form.last_name" class="mt-1 block w-full pl-10" placeholder="Enter last name" />
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                      </svg>
                    </div>
                  </div>
                  <InputError class="mt-2" :message="form.errors.last_name" />
                </div>

                <div class="space-y-2">
                  <InputLabel class="text-sm font-medium text-gray-700">
                    Date of Birth <span class="text-red-500">*</span>
                  </InputLabel>
                  <div class="relative">
                    <input
                      type="date"
                      v-model="form.date_of_birth"
                      class="mt-1 block w-full pl-10 rounded-md border-gray-300 focus:border-[#5997ac] focus:ring-[#5997ac] shadow-sm"
                    />
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                      </svg>
                    </div>
                  </div>
                  <InputError class="mt-2" :message="form.errors.date_of_birth" />
                </div>

                <div class="space-y-2">
                  <InputLabel value="Gender" class="text-sm font-medium text-gray-700" />
                  <div class="relative">
                    <select
                      v-model="form.gender"
                      class="mt-1 block w-full pl-10 rounded-md border-gray-300 focus:border-[#5997ac] focus:ring-[#5997ac] shadow-sm"
                    >
                      <option value="">Select gender</option>
                      <option value="MALE">Male</option>
                      <option value="FEMALE">Female</option>
                      <option value="OTHER">Other</option>
                    </select>
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                      </svg>
                    </div>
                  </div>
                  <InputError class="mt-2" :message="form.errors.gender" />
                </div>
              </div>
            </div>
          </div>

          <!-- Location & Contact Section -->
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-[#5997ac] to-[#7ba8b7] px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                  </svg>
                </div>
                <div>
                  <h2 class="text-lg font-semibold text-white">Location & Contact</h2>
                  <p class="text-sm text-white/80">Where patients can reach you</p>
                </div>
              </div>
            </div>
            <div class="p-6 sm:p-8">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <InputLabel class="text-sm font-medium text-gray-700">
                    Country <span class="text-red-500">*</span>
                  </InputLabel>
                  <div class="relative">
                    <select
                      v-model="countryCode"
                      class="mt-1 block w-full pl-10 rounded-md border-gray-300 focus:border-[#5997ac] focus:ring-[#5997ac] shadow-sm"
                    >
                      <option value="">Select country</option>
                      <option v-for="c in countriesList" :key="c.isoCode" :value="c.isoCode">{{ c.name }}</option>
                    </select>
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064"></path>
                      </svg>
                    </div>
                  </div>
                  <InputError class="mt-2" :message="form.errors.country" />
                </div>

                <div class="space-y-2">
                  <InputLabel class="text-sm font-medium text-gray-700">
                    City <span class="text-red-500">*</span>
                  </InputLabel>
                  <div class="relative">
                    <select
                      v-model="form.city"
                      :disabled="!form.country"
                      class="mt-1 block w-full pl-10 rounded-md border-gray-300 disabled:bg-gray-50 focus:border-[#5997ac] focus:ring-[#5997ac] shadow-sm"
                    >
                      <option value="">{{ form.country ? 'Select city' : 'Select country first' }}</option>
                      <option v-for="ct in cities" :key="ct" :value="ct">{{ ct }}</option>
                    </select>
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                      <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                      </svg>
                    </div>
                  </div>
                  <InputError class="mt-2" :message="form.errors.city" />
                </div>
              </div>

              <div class="mt-6 space-y-2">
                <InputLabel value="Address" class="text-sm font-medium text-gray-700" />
                <div class="relative">
                  <Textarea
                    v-model="form.address"
                    class="mt-1 block w-full pl-10"
                    rows="3"
                    placeholder="Enter your full address"
                  />
                  <div class="absolute top-3 left-3 pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                  </div>
                </div>
                <InputError class="mt-2" :message="form.errors.address" />
              </div>

              <div class="mt-6 space-y-2">
                <InputLabel class="text-sm font-medium text-gray-700">
                  Phone Number <span class="text-red-500">*</span>
                </InputLabel>
                <div class="relative">
                  <div class="mt-1 flex">
                    <input
                      v-model="dialCode"
                      readonly
                      class="w-24 rounded-l-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700 pl-10"
                      placeholder="+___"
                    />
                    <input
                      v-model="nationalNumber"
                      inputmode="tel"
                      class="block w-full rounded-r-md border border-l-0 border-gray-300 px-3 py-2 text-sm focus:border-[#5997ac] focus:ring-[#5997ac] shadow-sm"
                      placeholder="Enter phone number"
                    />
                  </div>
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                  </div>
                </div>
                <InputError class="mt-2" :message="form.errors.phone" />
              </div>
            </div>
          </div>

          <!-- Languages Section -->
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-[#5997ac] to-[#7ba8b7] px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                  </svg>
                </div>
                <div>
                  <h2 class="text-lg font-semibold text-white">Languages</h2>
                  <p class="text-sm text-white/80">Languages you speak with patients</p>
                </div>
              </div>
            </div>
            <div class="p-6 sm:p-8">
              <div class="space-y-4">
                <div class="flex gap-3">
                  <div class="flex-1">
                    <div class="relative">
                      <select
                        v-model="selectedLanguage"
                        class="block w-full pl-10 rounded-lg border-gray-300 focus:border-[#5997ac] focus:ring-[#5997ac] shadow-sm"
                      >
                        <option value="">Select a language</option>
                        <option v-for="lang in availableLanguages" :key="lang" :value="lang">{{ lang }}</option>
                      </select>
                      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                        </svg>
                      </div>
                    </div>
                  </div>
                  <PrimaryButton
                    type="button"
                    @click="addLanguage"
                    class="px-6 py-2 bg-[#5997ac] hover:bg-[#4a7a95] rounded-lg transition-colors duration-200 flex items-center gap-2"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add
                  </PrimaryButton>
                </div>

                <div class="flex flex-wrap gap-3">
                  <span
                    v-for="lang in form.languages"
                    :key="lang"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-[#5997ac] to-[#7ba8b7] text-white text-sm font-medium rounded-full shadow-sm hover:shadow-md transition-shadow duration-200"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                    </svg>
                    {{ lang }}
                    <button
                      type="button"
                      @click="removeLanguage(lang)"
                      class="ml-1 text-white/80 hover:text-white hover:bg-white/20 rounded-full p-0.5 transition-colors duration-200"
                    >
                      <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                      </svg>
                    </button>
                  </span>
                </div>
              </div>
              <InputError class="mt-4" :message="form.errors.languages" />
            </div>
          </div>

          <!-- Specializations & Expertises Section -->
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-[#5997ac] to-[#7ba8b7] px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                  </svg>
                </div>
                <div>
                  <h2 class="text-lg font-semibold text-white">Specializations & Expertises</h2>
                  <p class="text-sm text-white/80">Your areas of specialization and expertise</p>
                </div>
              </div>
            </div>
            <div class="p-6 sm:p-8">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-4">
                  <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-[#5997ac]/10 rounded-lg flex items-center justify-center">
                      <svg class="w-5 h-5 text-[#5997ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-sm font-semibold text-gray-900">Specializations <span class="text-red-500">*</span></h3>
                      <p class="text-xs text-gray-600">Main areas of focus</p>
                    </div>
                  </div>
                  <div class="space-y-3">
                    <Multiselect
                      v-model="form.specialisation_ids"
                      :options="specialisationOptions"
                      mode="tags"
                      :close-on-select="false"
                      :searchable="true"
                      placeholder="Search and select specializations"
                      class="multiselect-custom"
                    />
                    <InputError class="mt-2" :message="form.errors.specialisation_ids" />
                  </div>
                </div>

                <div class="space-y-4">
                  <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-[#5997ac]/10 rounded-lg flex items-center justify-center">
                      <svg class="w-5 h-5 text-[#5997ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-sm font-semibold text-gray-900">Expertises <span class="text-red-500">*</span></h3>
                      <p class="text-xs text-gray-600">Specific skills and techniques</p>
                    </div>
                  </div>
                  <div class="space-y-3">
                    <Multiselect
                      v-model="form.expertise_ids"
                      :options="expertiseOptions"
                      mode="tags"
                      :close-on-select="false"
                      :searchable="true"
                      placeholder="Search and select expertises"
                      class="multiselect-custom"
                    />
                    <InputError class="mt-2" :message="form.errors.expertise_ids" />
                  </div>
                  <InputError class="mt-2" :message="form.errors.expertise_ids" />
                </div>
              </div>
            </div>
          </div>

          <!-- Documents Section -->
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-[#5997ac] to-[#7ba8b7] px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                  </svg>
                </div>
                <div>
                  <h2 class="text-lg font-semibold text-white">Professional Documents</h2>
                  <p class="text-sm text-white/80">Your diplomas, certificates, and CV</p>
                </div>
              </div>
            </div>
            <div class="p-6 sm:p-8">
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Diplomas -->
                <div class="space-y-4">
                  <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-[#5997ac]/10 rounded-lg flex items-center justify-center">
                      <svg class="w-5 h-5 text-[#5997ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-sm font-semibold text-gray-900">Diplomas & Certificates <span v-if="diplomas_required" class="text-red-500">*</span></h3>
                      <p class="text-xs text-gray-600">Upload your professional qualifications</p>
                    </div>
                  </div>

                  <div class="space-y-3">
                    <input ref="diplomaInput" type="file" multiple accept=".pdf" @change="onDiplomaChange" class="hidden" />
                    <div class="flex items-center gap-3">
                      <PrimaryButton type="button" @click="diplomaInput?.click()" class="px-4 py-2 bg-[#5997ac] hover:bg-[#4a7a95] rounded-lg transition-colors duration-200 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Diploma
                      </PrimaryButton>
                      <div v-if="form.diploma_files.length > 0" class="text-sm text-gray-600">
                        {{ form.diploma_files.length }} file(s) selected
                      </div>
                    </div>
                    <div class="text-xs text-gray-500 bg-gray-50 p-2 rounded-lg">
                      Accepted format: PDF. Maximum 1MB per file.
                    </div>
                    <InputError class="mt-2" :message="form.errors.diploma_files" />
                  </div>

                  <!-- Existing diplomas -->
                  <div v-if="profile?.diplomas?.length" class="space-y-3">
                    <h4 class="text-sm font-medium text-gray-900 flex items-center gap-2">
                      <svg class="w-4 h-4 text-[#5997ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                      Current Diplomas
                    </h4>
                    <div class="space-y-2">
                      <div
                        v-for="diploma in profile.diplomas.filter(d => !form.remove_diplomas.includes(d.id))"
                        :key="diploma.id"
                        class="flex items-center justify-between p-3 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200 hover:shadow-sm transition-shadow duration-200"
                      >
                        <div class="flex items-center gap-3">
                          <div class="w-8 h-8 bg-[#5997ac]/10 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-[#5997ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                          </div>
                          <span class="text-sm font-medium text-gray-700">{{ diploma.file_url.split('/').pop() }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                          <a
                            v-if="diploma.file_url.toLowerCase().endsWith('.pdf')"
                            :href="diploma.file_url"
                            target="_blank"
                            rel="noopener"
                            class="p-1.5 text-[#5997ac] hover:text-white hover:bg-[#5997ac] rounded-lg transition-colors duration-200"
                            title="View PDF"
                          >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                          </a>
                          <button
                            type="button"
                            @click="() => {
                              if (!form.remove_diplomas) form.remove_diplomas = []
                              if (!form.remove_diplomas.includes(diploma.id)) {
                                form.remove_diplomas.push(diploma.id)
                              }
                            }"
                            class="p-1.5 text-red-500 hover:text-white hover:bg-red-500 rounded-lg transition-colors duration-200"
                            title="Remove document"
                          >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- CV/Resume -->
                <div class="space-y-4">
                  <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 bg-[#5997ac]/10 rounded-lg flex items-center justify-center">
                      <svg class="w-5 h-5 text-[#5997ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                      </svg>
                    </div>
                    <div>
                      <h3 class="text-sm font-semibold text-gray-900">CV / Resume <span v-if="cv_required" class="text-red-500">*</span></h3>
                      <p class="text-xs text-gray-600">Your professional resume</p>
                    </div>
                  </div>

                  <div class="space-y-3">
                    <div class="relative">
                      <input
                        type="file"
                        accept=".pdf,.doc,.docx"
                        @change="onCvChange"
                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-[#5997ac] file:text-white hover:file:bg-[#467891] file:cursor-pointer transition-colors duration-200"
                      />
                    </div>
                    <div class="text-xs text-gray-500 bg-gray-50 p-2 rounded-lg">
                      Accepted format: PDF. Maximum 1MB
                    </div>
                    <InputError class="mt-2" :message="form.errors.cv" />
                  </div>

                  <!-- Current CV -->
                  <div v-if="profile?.cv" class="space-y-3">
                    <h4 class="text-sm font-medium text-gray-900 flex items-center gap-2">
                      <svg class="w-4 h-4 text-[#5997ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                      Current CV
                    </h4>
                    <div class="flex items-center justify-between p-3 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200 hover:shadow-sm transition-shadow duration-200">
                      <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-[#5997ac]/10 rounded-lg flex items-center justify-center">
                          <svg class="w-4 h-4 text-[#5997ac]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                          </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-700">{{ profile.cv.split('/').pop() }}</span>
                      </div>
                      <div class="flex items-center gap-2">
                        <a
                          v-if="profile.cv.toLowerCase().endsWith('.pdf')"
                          :href="profile.cv"
                          target="_blank"
                          rel="noopener"
                          class="p-1.5 text-[#5997ac] hover:text-white hover:bg-[#5997ac] rounded-lg transition-colors duration-200"
                          title="View PDF"
                        >
                          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                          </svg>
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Save Changes Section -->
          <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-[#af5166] to-[#c66b85] px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                </div>
                <div>
                  <h2 class="text-lg font-semibold text-white">Save Changes</h2>
                  <p class="text-sm text-white/80">Update your profile information</p>
                </div>
              </div>
            </div>
            <div class="p-6 sm:p-8">
              <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex-1">
                  <p class="text-sm text-gray-600">
                    Make sure all your information is accurate and up-to-date. Changes will be reflected immediately in your profile.
                  </p>
                </div>
                <div class="flex items-center gap-3">
                  <Link
                    :href="route('home')"
                    class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#5997ac] transition-colors duration-200"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Home
                  </Link>
                  <PrimaryButton
                    :disabled="form.processing"
                    class="inline-flex items-center gap-2 px-6 py-2 bg-gradient-to-r from-[#af5166] to-[#c66b85] hover:from-[#8b4156] hover:to-[#a54f6a] text-white font-medium rounded-lg shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#af5166] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    {{ form.processing ? 'Saving Changes...' : 'Save Changes' }}
                  </PrimaryButton>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>