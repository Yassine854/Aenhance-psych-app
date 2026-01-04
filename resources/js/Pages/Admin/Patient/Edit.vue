<template>
  <div v-if="show && patient" class="fixed inset-0 z-[1000] flex items-center justify-center">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[1000]" @click="$emit('close')"></div>
    <div class="relative w-full max-w-7xl rounded-2xl shadow-2xl overflow-hidden z-[1001]">
      <div class="bg-gradient-to-r from-[rgb(141,61,79)] to-[rgb(89,151,172)] p-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <img v-if="headerImage" :src="headerImage" class="h-14 w-14 rounded-full ring-2 ring-white/70 object-cover" />
            <div v-else class="h-14 w-14 rounded-full bg-white/20 flex items-center justify-center text-white">No</div>
            <div class="text-white">
              <div class="text-xl font-semibold">{{ patient.first_name }} {{ patient.last_name }}</div>
              <div class="text-sm opacity-90">Patient #{{ patient.id }} • {{ patient.user?.email || '—' }}</div>
            </div>
          </div>

          <button @click="$emit('close')" class="text-white/90 hover:text-white text-2xl leading-none">✕</button>
        </div>

        <!-- Tabs -->
        <div class="mt-4 flex items-center gap-2">
          <button @click="section='profile'" :class="section==='profile' ? 'bg-white text-gray-900 shadow' : 'bg-white/20 text-white hover:bg-white/30'" class="px-4 py-2 rounded-lg transition">
            Profile Details
          </button>
          <button @click="section='account'" :class="section==='account' ? 'bg-white text-gray-900 shadow' : 'bg-white/20 text-white hover:bg-white/30'" class="px-4 py-2 rounded-lg transition">
            Account
          </button>
        </div>
      </div>

      <div class="bg-white p-6 max-h-[70vh] overflow-y-auto styled-scrollbar">
        <!-- Profile section -->
        <form v-if="section==='profile'" @submit.prevent="submitUpdate" class="space-y-6">
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
                <img v-else-if="patient?.profile_image_url" :src="patient.profile_image_url" class="h-16 w-16 rounded-full object-cover" />
                <span v-else class="text-sm text-gray-600">Drag & drop or click</span>
              </div>
              <input ref="profileInput" type="file" accept="image/*" @change="onFileChange('profile_image', $event)" class="hidden" />
              <p v-if="form.errors.profile_image" class="mt-1 text-sm text-red-600">{{ form.errors.profile_image }}</p>
            </div>

            <div class="flex items-center gap-3 pt-4">
              <button :disabled="saving" class="px-5 py-2.5 bg-[rgb(141,61,79)] text-white rounded-lg shadow hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed font-medium">
                {{ saving ? 'Saving...' : 'Save Changes' }}
              </button>
              <button type="button" @click="$emit('close')" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
            </div>
          </div>
        </form>

        <!-- Account section -->
        <form v-else @submit.prevent="submitAccount" class="space-y-6">
          <div v-if="accountError" class="p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-start gap-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600 flex-shrink-0 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293z" clip-rule="evenodd" />
              </svg>
              <div class="flex-1">
                <h3 class="text-sm font-medium text-red-800">Error saving account</h3>
                <p class="mt-1 text-sm text-red-700">{{ accountError }}</p>
              </div>
              <button @click="accountError = ''" type="button" class="text-red-400 hover:text-red-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="text-sm font-medium text-gray-700">Account name <span class="text-red-500">*</span></label>
              <input v-model="accountForm.name" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" />
            </div>
            <div>
              <label class="text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
              <input v-model="accountForm.email" type="email" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" />
            </div>
            <div class="md:col-span-2">
              <label class="text-sm font-medium text-gray-700">Password</label>
              <input v-model="accountForm.password" type="password" placeholder="Leave empty to keep current" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" />
              <p class="mt-2 text-xs text-gray-500">If left blank, the password remains unchanged.</p>
            </div>
          </div>

          <p v-if="!patient.user" class="text-sm text-yellow-700 bg-yellow-50 border border-yellow-200 rounded px-3 py-2">Link a user to enable account editing.</p>

          <div class="flex items-center gap-3">
            <button type="submit" :disabled="accountSaving || !patient.user" class="px-4 py-2 bg-[rgb(89,151,172)] text-white rounded-lg shadow hover:opacity-90">Save Account</button>
            <button v-if="patient.user" type="button" @click="toggleActivation" class="px-4 py-2 rounded-lg" :class="patient.user.is_active ? 'bg-yellow-600 text-white hover:bg-yellow-700' : 'bg-green-600 text-white hover:bg-green-700'">
              {{ patient.user.is_active ? 'Deactivate' : 'Activate' }}
            </button>
            <button type="button" @click="$emit('close')" class="text-sm text-gray-600">Cancel</button>
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

const props = defineProps({
  show: Boolean,
  patient: Object,
})

const emit = defineEmits(['close', 'saved'])

const section = ref('profile')

const countriesList = ref(getCountries())
const countryCode = ref('')
const dialCode = ref('')
const nationalNumber = ref('')
const saving = ref(false)
const generalError = ref(null)

const accountSaving = ref(false)
const accountError = ref('')
const accountForm = ref({ name: '', email: '', password: '' })

const profileInput = ref(null)

const form = useForm({
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

const cities = computed(() => {
  if (!form.country) return []
  return getCitiesByCountryName(form.country).map(c => c.name)
})

const imagePreview = computed(() => {
  if (form.profile_image) return URL.createObjectURL(form.profile_image)
  return ''
})

const headerImage = computed(() => {
  return imagePreview.value || props.patient?.profile_image_url || ''
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

watch(
  () => props.patient,
  (p) => {
    if (!p) return

    section.value = 'profile'
    form.first_name = p.first_name || ''
    form.last_name = p.last_name || ''
    form.date_of_birth = p.date_of_birth || ''
    form.gender = p.gender || ''
    form.country = p.country || ''
    form.city = p.city || ''
    form.phone = p.phone || ''
    form.country_code = p.country_code || ''

    dialCode.value = form.country_code || ''
    nationalNumber.value = form.phone || ''

    const c = countriesList.value.find(x => x.name === form.country)
    countryCode.value = c?.isoCode || ''
  },
  { immediate: true }
)

watch(() => section.value, (sec) => {
  if (sec !== 'account') return
  const u = props.patient?.user
  accountForm.value.name = u?.name || ''
  accountForm.value.email = u?.email || ''
  accountForm.value.password = ''
})

watch(
  () => props.show,
  (isShowing) => {
    if (!isShowing) return
    form.clearErrors()
    form.profile_image = null
    generalError.value = null
  }
)

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

async function submitUpdate() {
  form.clearErrors()
  generalError.value = null

  saving.value = true
  try {
    let hasErrors = false
    if (!form.first_name) {
      form.setError('first_name', 'First name is required')
      hasErrors = true
    }
    if (!form.last_name) {
      form.setError('last_name', 'Last name is required')
      hasErrors = true
    }
    if (!form.date_of_birth) {
      form.setError('date_of_birth', 'Date of birth is required')
      hasErrors = true
    }

    if (hasErrors) {
      generalError.value = 'Please fill in all required fields correctly'
      saving.value = false
      return
    }

    const cleanPhone = (form.phone || '').replace(/\D/g, '')

    const csrf = await ensureCsrfToken()
    const fd = new FormData()
    if (csrf.token) fd.append('_token', csrf.token)

    fd.append('_method', 'PATCH')

    fd.append('first_name', form.first_name ?? '')
    fd.append('last_name', form.last_name ?? '')
    fd.append('date_of_birth', form.date_of_birth ?? '')
    fd.append('gender', form.gender ?? '')
    fd.append('country', form.country ?? '')
    fd.append('city', form.city ?? '')
    fd.append('country_code', form.country_code ?? '')
    fd.append('phone', cleanPhone)

    if (form.profile_image) fd.append('profile_image', form.profile_image)

    const res = await fetch(route('patient-profiles.update', props.patient.id), {
      method: 'POST',
      body: fd,
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
      credentials: 'same-origin',
    })

    const json = await res.json().catch(() => ({}))

    if (!res.ok) {
      if (json?.errors) {
        Object.entries(json.errors).forEach(([k, v]) => {
          if (k === 'general') {
            generalError.value = Array.isArray(v) ? v[0] : String(v)
          } else {
            form.setError(k, Array.isArray(v) ? v[0] : String(v))
          }
        })
      } else {
        generalError.value = 'Unable to update patient'
      }
      saving.value = false
      return
    }

    emit('saved', { type: 'profile', profile: json?.profile })
  } catch {
    generalError.value = 'Unable to update patient'
  } finally {
    saving.value = false
  }
}

async function submitAccount() {
  if (!props.patient?.user) return

  accountSaving.value = true
  accountError.value = ''

  try {
    const payload = { name: accountForm.value.name, email: accountForm.value.email }
    if (accountForm.value.password && accountForm.value.password.trim().length > 0) {
      payload.password = accountForm.value.password
    }

    const csrf = await ensureCsrfToken()

    const headers = {
      'Content-Type': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
    }

    if (csrf.type === 'meta' && csrf.token) {
      headers['X-CSRF-TOKEN'] = csrf.token
    } else if (csrf.type === 'cookie' && csrf.token) {
      headers['X-XSRF-TOKEN'] = csrf.token
    }

    const res = await fetch(`/users/${props.patient.user.id}`, {
      method: 'PATCH',
      headers,
      body: JSON.stringify(payload),
      credentials: 'same-origin',
    })

    if (!res.ok) {
      let msg = 'Failed to update account'
      try {
        const data = await res.json()
        if (data?.message) msg = data.message
      } catch {}
      if (res.status === 419) msg = 'Session expired. Please refresh the page.'
      accountError.value = msg
      return
    }

    emit('saved', {
      type: 'account',
      user: {
        id: props.patient.user.id,
        name: payload.name,
        email: payload.email,
      },
    })
  } finally {
    accountSaving.value = false
  }
}

async function toggleActivation() {
  if (!props.patient?.user) return
  try {
    const url = props.patient.user.is_active
      ? `/users/${props.patient.user.id}/deactivate`
      : `/users/${props.patient.user.id}/activate`

    const csrf = await ensureCsrfToken()
    const headers = { 'X-Requested-With': 'XMLHttpRequest' }

    if (csrf.type === 'meta' && csrf.token) {
      headers['X-CSRF-TOKEN'] = csrf.token
    } else if (csrf.type === 'cookie' && csrf.token) {
      headers['X-XSRF-TOKEN'] = csrf.token
    }

    await fetch(url, {
      method: 'PATCH',
      headers,
      credentials: 'same-origin',
    })

    emit('saved', {
      type: 'account',
      user: {
        id: props.patient.user.id,
        is_active: !props.patient.user.is_active,
      },
    })
  } catch {}
}
</script>
