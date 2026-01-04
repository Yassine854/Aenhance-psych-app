<template>
  <div v-if="show" class="fixed inset-0 z-[1000] flex items-center justify-center">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[1000]" @click="$emit('close')"></div>
    <div class="relative w-full max-w-7xl rounded-2xl shadow-2xl overflow-hidden z-[1001]">
      <div class="bg-gradient-to-r from-[rgb(141,61,79)] to-[rgb(89,151,172)] p-6">
        <div class="flex items-center justify-between">
          <div class="text-white">
            <div class="text-xl font-semibold">Add New Patient</div>
            <div class="text-sm opacity-90">Create account and profile details</div>
          </div>
          <button @click="$emit('close')" class="text-white/90 hover:text-white text-2xl leading-none">✕</button>
        </div>
      </div>

      <div class="bg-white p-6 max-h-[70vh] overflow-y-auto styled-scrollbar">
        <form @submit.prevent="submitCreate" class="space-y-6">
          <div v-if="generalError" class="p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-start gap-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293z" clip-rule="evenodd" />
              </svg>
              <div class="flex-1">
                <p class="text-sm font-medium text-red-800">{{ generalError }}</p>
              </div>
              <button @click="generalError = null" type="button" class="text-red-400 hover:text-red-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>

          <div class="border-b border-gray-200 pb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Information</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="text-sm font-medium text-gray-700">Account Name <span class="text-red-500">*</span></label>
                <input v-model="newUser.name" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" />
                <p v-if="newUserErrors.name" class="mt-1 text-sm text-red-600">{{ newUserErrors.name }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                <input v-model="newUser.email" type="email" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" />
                <p v-if="newUserErrors.email" class="mt-1 text-sm text-red-600">{{ newUserErrors.email }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Password <span class="text-red-500">*</span></label>
                <input v-model="newUser.password" type="password" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" />
                <p v-if="newUserErrors.password" class="mt-1 text-sm text-red-600">{{ newUserErrors.password }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Role</label>
                <input value="PATIENT" disabled class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100" />
              </div>
            </div>
          </div>

          <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900">Profile Details</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div>
                <label class="text-sm font-medium text-gray-700">First Name <span class="text-red-500">*</span></label>
                <input v-model="form.first_name" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" />
                <p v-if="form.errors.first_name" class="mt-1 text-sm text-red-600">{{ form.errors.first_name }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Last Name <span class="text-red-500">*</span></label>
                <input v-model="form.last_name" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" />
                <p v-if="form.errors.last_name" class="mt-1 text-sm text-red-600">{{ form.errors.last_name }}</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
              <div>
                <label class="text-sm font-medium text-gray-700">Gender</label>
                <select v-model="form.gender" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]">
                  <option value="">Select gender</option>
                  <option value="MALE">Male</option>
                  <option value="FEMALE">Female</option>
                  <option value="OTHER">Other</option>
                </select>
                <p v-if="form.errors.gender" class="mt-1 text-sm text-red-600">{{ form.errors.gender }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Date of Birth <span class="text-red-500">*</span></label>
                <input type="date" v-model="form.date_of_birth" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" />
                <p v-if="form.errors.date_of_birth" class="mt-1 text-sm text-red-600">{{ form.errors.date_of_birth }}</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
              <div>
                <label class="text-sm font-medium text-gray-700">Country</label>
                <select v-model="countryCode" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]">
                  <option value="">Select country</option>
                  <option v-for="c in countriesList" :key="c.isoCode" :value="c.isoCode">{{ c.name }}</option>
                </select>
                <p v-if="form.errors.country" class="mt-1 text-sm text-red-600">{{ form.errors.country }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">City</label>
                <select v-model="form.city" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]">
                  <option value="">Select city</option>
                  <option v-for="ct in cities" :key="ct" :value="ct">{{ ct }}</option>
                </select>
                <p v-if="form.errors.city" class="mt-1 text-sm text-red-600">{{ form.errors.city }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Phone</label>
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
                    class="block w-full rounded-r-md border border-l-0 border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-[rgb(89,151,172)]"
                    placeholder="Enter phone number"
                  />
                </div>
                <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
              </div>
            </div>

            <div>
              <label class="text-sm font-medium text-gray-700">Profile Image</label>
              <div
                @click="$refs.profileInput?.click()"
                @drop.prevent="onDrop('profile_image', $event)"
                @dragover.prevent
                class="mt-1 border-2 border-dashed border-gray-300 rounded-lg p-3 flex items-center justify-center hover:border-[rgb(89,151,172)] hover:bg-gray-50 transition cursor-pointer"
              >
                <img v-if="imagePreview" :src="imagePreview" class="h-16 w-16 rounded-full object-cover" />
                <span v-else class="text-sm text-gray-600">Drag & drop or click</span>
              </div>
              <input ref="profileInput" type="file" accept="image/*" @change="onFileChange('profile_image', $event)" class="hidden" />
              <p v-if="form.errors.profile_image" class="mt-1 text-sm text-red-600">{{ form.errors.profile_image }}</p>
            </div>

            <div class="flex items-center gap-3 pt-4">
              <button :disabled="creating" class="px-5 py-2.5 bg-[rgb(141,61,79)] text-white rounded-lg shadow hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed font-medium">
                {{ creating ? 'Creating...' : 'Create Patient' }}
              </button>
              <button type="button" @click="$emit('close')" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { getCountries, getCitiesByCountryName } from '@/utils/geoData'

const props = defineProps({ show: Boolean })
const emit = defineEmits(['close', 'created'])

const countriesList = ref(getCountries())
const countryCode = ref('')
const dialCode = ref('')
const nationalNumber = ref('')
const creating = ref(false)
const generalError = ref(null)

const profileInput = ref(null)

const form = useForm({
  user_id: '',
  first_name: '',
  last_name: '',
  date_of_birth: '',
  gender: '',
  country: '',
  city: '',
  phone: '',
  country_code: '',
  profile_image: null,
})

const newUser = ref({ name: '', email: '', password: '' })
const newUserErrors = ref({ name: '', email: '', password: '' })

const cities = computed(() => {
  if (!form.country) return []
  return getCitiesByCountryName(form.country).map(c => c.name)
})

const imagePreview = computed(() => {
  if (form.profile_image) return URL.createObjectURL(form.profile_image)
  return ''
})

function syncPhoneToForm() {
  form.country_code = dialCode.value || ''
  form.phone = nationalNumber.value || ''
}

watch(countryCode, (code) => {
  const c = countriesList.value.find(x => x.isoCode === code)
  form.country = c?.name || ''
  form.city = ''
  dialCode.value = c?.dialCode || ''
  syncPhoneToForm()
})

watch([dialCode, nationalNumber], () => syncPhoneToForm())

watch(() => props.show, (isShowing) => {
  if (!isShowing) return
  form.reset()
  newUser.value = { name: '', email: '', password: '' }
  newUserErrors.value = { name: '', email: '', password: '' }
  countryCode.value = ''
  dialCode.value = ''
  nationalNumber.value = ''
  generalError.value = null
})

function onFileChange(field, e) {
  const file = e?.target?.files?.[0] || null
  form[field] = file
}

function onDrop(field, e) {
  const file = e?.dataTransfer?.files?.[0]
  if (!file) return
  if (field === 'profile_image' && !file.type.startsWith('image/')) return
  form[field] = file
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

async function submitCreate() {
  form.clearErrors()
  generalError.value = null
  newUserErrors.value = { name: '', email: '', password: '' }

  creating.value = true
  try {
    let hasAccountErrors = false
    if (!newUser.value.name) {
      newUserErrors.value.name = 'Account name is required'
      hasAccountErrors = true
    }
    if (!newUser.value.email) {
      newUserErrors.value.email = 'Email is required'
      hasAccountErrors = true
    }
    if (!newUser.value.password) {
      newUserErrors.value.password = 'Password is required'
      hasAccountErrors = true
    }

    let hasProfileErrors = false
    if (!form.first_name) {
      form.setError('first_name', 'First name is required')
      hasProfileErrors = true
    }
    if (!form.last_name) {
      form.setError('last_name', 'Last name is required')
      hasProfileErrors = true
    }
    if (!form.date_of_birth) {
      form.setError('date_of_birth', 'Date of birth is required')
      hasProfileErrors = true
    }

    if (hasAccountErrors || hasProfileErrors) {
      generalError.value = 'Please fill in all required fields correctly'
      creating.value = false
      return
    }

    const cleanPhone = (form.phone || '').replace(/\D/g, '')

    const csrf = await ensureCsrfToken()
    const fd = new FormData()
    if (csrf.token) fd.append('_token', csrf.token)

    fd.append('new_user_name', newUser.value.name)
    fd.append('new_user_email', newUser.value.email)
    fd.append('new_user_password', newUser.value.password)

    fd.append('first_name', form.first_name ?? '')
    fd.append('last_name', form.last_name ?? '')
    fd.append('date_of_birth', form.date_of_birth ?? '')
    fd.append('gender', form.gender ?? '')
    fd.append('country', form.country ?? '')
    fd.append('city', form.city ?? '')
    fd.append('country_code', form.country_code ?? '')
    fd.append('phone', cleanPhone)

    if (form.profile_image) fd.append('profile_image', form.profile_image)

    const res = await fetch(route('patient-profiles.store'), {
      method: 'POST',
      body: fd,
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
      credentials: 'same-origin',
    })

    const json = await res.json().catch(() => ({}))

    if (!res.ok) {
      if (json?.errors) {
        Object.entries(json.errors).forEach(([k, v]) => {
          if (k.startsWith('new_user_')) {
            const key = k.replace('new_user_', '')
            newUserErrors.value[key] = Array.isArray(v) ? v[0] : String(v)
          } else if (k === 'general') {
            generalError.value = Array.isArray(v) ? v[0] : String(v)
          } else {
            form.setError(k, Array.isArray(v) ? v[0] : String(v))
          }
        })
      } else {
        generalError.value = 'Unable to create patient'
      }
      creating.value = false
      return
    }

    emit('created', json?.profile)
  } catch {
    generalError.value = 'Unable to create patient'
  } finally {
    creating.value = false
  }
}
</script>
