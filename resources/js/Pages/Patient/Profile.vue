<script setup>
import Navbar from '@/Components/Navbar.vue'
import InputLabel from '@/Components/InputLabel.vue'
import TextInput from '@/Components/TextInput.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { computed, ref, watch } from 'vue'
import { getCountries, getCitiesByCountryName, splitInternationalPhoneNumber } from '@/utils/geoData'

const props = defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
  authUser: Object,
  profile: Object,
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

        <div v-if="status" class="mt-5 rounded-xl bg-white/15 border border-white/20 px-4 py-3 text-white text-sm">
          {{ status }}
        </div>
      </div>
    </div>

    <div class="mx-auto max-w-6xl px-4 py-8">
      <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main form -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-200 p-5 sm:p-8">
          <div class="flex items-center justify-between gap-4">
            <div>
              <h2 class="text-lg font-semibold text-gray-900">Personal details</h2>
              <p class="mt-1 text-sm text-gray-600">Keep your information accurate.</p>
            </div>
          </div>

          <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <InputLabel value="First name" />
              <TextInput v-model="form.first_name" class="mt-1 block w-full" />
              <InputError class="mt-2" :message="form.errors.first_name" />
            </div>
            <div>
              <InputLabel value="Last name" />
              <TextInput v-model="form.last_name" class="mt-1 block w-full" />
              <InputError class="mt-2" :message="form.errors.last_name" />
            </div>

            <div>
              <InputLabel value="Date of birth" />
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

          <div class="mt-8 flex items-center gap-3">
            <PrimaryButton :disabled="form.processing" class="bg-[#af5166] hover:opacity-95 focus:ring-[#af5166]">
              {{ form.processing ? 'Saving...' : 'Save changes' }}
            </PrimaryButton>
            <a :href="route('home')" class="text-sm text-gray-600 hover:text-gray-800">Back to home</a>
          </div>
        </div>

        <!-- Avatar panel -->
        <aside class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 sm:p-8">
          <h2 class="text-lg font-semibold text-gray-900">Profile photo</h2>
          <p class="mt-1 text-sm text-gray-600">This photo appears in your navbar.</p>

          <div class="mt-6 flex flex-col items-center">
            <div class="relative">
              <label
                class="group relative h-32 w-32 rounded-full overflow-hidden bg-gray-100 flex items-center justify-center cursor-pointer ring-2 ring-[#5997ac]/20 hover:ring-[#5997ac]/50 transition"
                title="Click to change profile photo"
              >
              <img v-if="headerImage" :src="headerImage" class="h-full w-full object-cover" />
              <span v-else class="text-gray-500">No photo</span>

              <div class="absolute inset-0 bg-black/25 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                <div class="px-3 py-1.5 rounded-full bg-white/90 text-[12px] font-medium text-gray-900">Change</div>
              </div>

              <input type="file" accept="image/*" class="hidden" @change="onFileChange" />
              </label>

              <button
                v-if="!form.remove_profile_image && (headerImage || props.profile?.profile_image_url || page.props?.auth?.user?.profile_image_url)"
                type="button"
                @click="removePhoto"
                class="absolute -top-2 -right-2 h-8 w-8 rounded-full bg-red-600 text-white shadow flex items-center justify-center hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300"
                title="Remove photo"
              >
                ✕
              </button>
            </div>

            <InputError class="mt-3" :message="form.errors.profile_image" />

            <div class="mt-6 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
              Tip: Upload a square photo for best results.
            </div>
          </div>
        </aside>
      </form>
    </div>
  </div>
</template>
