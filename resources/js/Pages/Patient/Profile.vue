<script setup>
import Navbar from '@/Components/Navbar.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import Swal from 'sweetalert2'
import { getCountries, getCitiesByCountryName, splitInternationalPhoneNumber } from '@/utils/geoData'

const props = defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
  authUser: Object,
  profile: Object,
  status: String,
})

const page = usePage()

// Show SweetAlert toast when there's a status flash message.
// Watch both `page.props.flash.status` (Inertia flash) and the `status` prop,
// and ensure each message is shown only once to avoid intermittent misses.

const flashStatus = computed(() => {
  return page.props?.flash?.status || props.status || page.props?.status || null
})

const _shownStatus = ref(null)
watch(flashStatus, (val) => {
  if (val && val !== _shownStatus.value) {
    _shownStatus.value = val
    Swal.fire({
      position: 'top-end',
      icon: 'success',
      title: val,
      showConfirmButton: false,
      timer: 3000,
      toast: true,
      timerProgressBar: true,
      showCloseButton: true,
    })
  }
}, { immediate: true })

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
  phone: props.profile?.phone || '',
  country_code: props.profile?.country_code || '',
  profile_image: null,
  remove_profile_image: false,
})

const countriesList = ref(getCountries())
const countryCode = ref('')

const dialCode = ref('')
const nationalNumber = ref('')

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
  return imagePreview.value || props.profile?.profile_image_url || page.props?.auth?.user?.profile_image_url || ''
})

function syncPhoneToForm() {
  form.country_code = dialCode.value || ''
  const cleanNational = (nationalNumber.value || '').toString().replace(/\D/g, '')
  const cleanDial = (dialCode.value || '').toString().replace(/\s+/g, '')
  form.phone = `${cleanDial}${cleanNational}`
}

// Init country code from existing profile country
const found = countriesList.value.find(c => c.name === (props.profile?.country || ''))
countryCode.value = found?.isoCode || ''

// Init phone parts from stored phone
const parsed = splitInternationalPhoneNumber(props.profile?.phone || '', dialCodes.value)
dialCode.value = props.profile?.country_code || parsed.dialCode || (found?.dialCode || '')
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

function onFileChange(e) {
  const file = e?.target?.files?.[0] || null
  form.profile_image = file
  if (file) form.remove_profile_image = false
}

function removePhoto() {
  form.profile_image = null
  form.remove_profile_image = true
}

function submit() {
  syncPhoneToForm()
  form.post(route('patient.profile.update'), {
    forceFormData: true,
    preserveScroll: true,
  })
}
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
            <p class="mt-1 text-sm text-white/90">Update what psychologists see about you.</p>
          </div>
        </div>

        <!-- flash status now shown via SweetAlert toast -->
      </div>
    </div>

    <div class="mx-auto max-w-6xl px-4 py-8">
      <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main form -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
          <div class="bg-gradient-to-r from-[#5997ac] to-[#7ba8b7] px-6 py-4">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
              <div>
                  <h2 class="text-lg font-semibold text-white">Personal Information</h2>
                  <p class="text-sm text-white/80">Your basic personal details</p>
              </div>
            </div>
          </div>

          <div class="p-6 sm:p-8">
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <InputLabel>First name <span class="text-red-500">*</span></InputLabel>
              <TextInput v-model="form.first_name" class="mt-1 block w-full" />
              <InputError class="mt-2" :message="form.errors.first_name" />
            </div>
            <div>
              <InputLabel>Last name <span class="text-red-500">*</span></InputLabel>
              <TextInput v-model="form.last_name" class="mt-1 block w-full" />
              <InputError class="mt-2" :message="form.errors.last_name" />
            </div>

            <div>
              <InputLabel>Date of birth <span class="text-red-500">*</span></InputLabel>
              <input
                type="date"
                v-model="form.date_of_birth"
                class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#5997ac] focus:ring-[#5997ac]"
              />
              <InputError class="mt-2" :message="form.errors.date_of_birth" />
            </div>

            <div>
              <InputLabel value="Gender" />
              <select
                v-model="form.gender"
                class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#5997ac] focus:ring-[#5997ac]"
              >
                <option value="">Select gender</option>
                <option value="MALE">Male</option>
                <option value="FEMALE">Female</option>
                <option value="OTHER">Other</option>
              </select>
              <InputError class="mt-2" :message="form.errors.gender" />
            </div>
          </div>

          <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <InputLabel value="Country" />
              <select
                v-model="countryCode"
                class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#5997ac] focus:ring-[#5997ac]"
              >
                <option value="">Select country</option>
                <option v-for="c in countriesList" :key="c.isoCode" :value="c.isoCode">{{ c.name }}</option>
              </select>
              <InputError class="mt-2" :message="form.errors.country" />
            </div>

            <div>
              <InputLabel value="City" />
              <select
                v-model="form.city"
                :disabled="!form.country"
                class="mt-1 block w-full rounded-md border-gray-300 disabled:bg-gray-50 focus:border-[#5997ac] focus:ring-[#5997ac]"
              >
                <option value="">{{ form.country ? 'Select city' : 'Select country first' }}</option>
                <option v-for="ct in cities" :key="ct" :value="ct">{{ ct }}</option>
              </select>
              <InputError class="mt-2" :message="form.errors.city" />
            </div>
          </div>

          <div class="mt-6">
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
                class="block w-full rounded-r-md border border-l-0 border-gray-300 px-3 py-2 text-sm focus:border-[#5997ac] focus:ring-[#5997ac]"
                placeholder="Enter phone number"
              />
            </div>
            <InputError class="mt-2" :message="form.errors.phone" />
          </div>

            <!-- Save Changes Section (matching psychologist interface) -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden mt-6">
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
                    <a
                      :href="route('home')"
                      class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#5997ac] transition-colors duration-200"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                      </svg>
                      Back to Home
                    </a>
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
        </div>
  
          <!-- Avatar panel (styled like psychologist profile) -->
          <aside class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden order-first lg:order-last lg:col-span-1">
            <div class="bg-gradient-to-r from-[#5997ac] to-[#7ba8b7] px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                  <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                  </svg>
                </div>
                <div>
                  <h2 class="text-lg font-semibold text-white">Profile Image</h2>
                  <p class="text-sm text-white/80">Your photo and basic information</p>
                </div>
              </div>
            </div>

          <div class="p-6 sm:p-8">
            <div class="flex flex-col items-center space-y-6">
              <div class="relative group">
                <div class="relative h-32 w-32 rounded-2xl overflow-hidden bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center ring-4 ring-[#5997ac]/20 group-hover:ring-[#5997ac]/40 transition-all duration-300">
                  <img v-if="headerImage" :src="headerImage" class="h-full w-full object-cover" />
                  <div v-else class="flex flex-col items-center justify-center text-gray-400">No photo</div>

                  <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center rounded-2xl">
                    <div class="px-3 py-1.5 rounded-full bg-white/90 text-[12px] font-medium text-gray-900">Change</div>
                  </div>
                </div>

                <label class="absolute -bottom-2 -right-2 h-10 w-10 rounded-full bg-[#5997ac] text-white shadow-lg flex items-center justify-center cursor-pointer hover:bg-[#4a7a95] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#5997ac] transition-colors duration-200">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                  </svg>
                  <input type="file" accept="image/*" class="hidden" @change="onFileChange" />
                </label>

                <button
                  v-if="!form.remove_profile_image && (headerImage || props.profile?.profile_image_url || page.props?.auth?.user?.profile_image_url)"
                  type="button"
                  @click="removePhoto"
                  class="absolute -top-2 -right-2 h-8 w-8 rounded-full bg-red-500 text-white shadow-lg flex items-center justify-center hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200"
                  title="Remove photo"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>

              <InputError class="w-full" :message="form.errors.profile_image" />
              <div class="w-full p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200">
                <div class="flex items-start gap-3">
                  <div class="w-5 h-5 text-blue-600 mt-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 6v.01"></path>
                    </svg>
                  </div>
                  <div>
                    <p class="text-sm font-medium text-blue-900">Tip</p>
                    <p class="text-xs text-blue-700 mt-1">Recommended photo size: 400x400px, max 1MB.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </aside>
      </form>
    </div>
  </div>
</template>
