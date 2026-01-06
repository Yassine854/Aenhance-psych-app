<template>
  <div v-if="show" class="fixed inset-0 z-[1000] flex items-center justify-center">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[1000]" @click="$emit('close')"></div>
    <div class="relative w-full max-w-7xl rounded-2xl shadow-2xl overflow-hidden z-[1001]">
      <!-- Gradient header -->
      <div class="bg-gradient-to-r from-[rgb(141,61,79)] to-[rgb(89,151,172)] p-6">
        <div class="flex items-center justify-between">
          <div class="text-white">
            <div class="text-xl font-semibold">Add New Psychologist</div>
            <div class="text-sm opacity-90">Create account and profile details</div>
          </div>
          <button @click="$emit('close')" class="text-white/90 hover:text-white text-2xl leading-none">✕</button>
        </div>
      </div>

      <!-- Content -->
      <div class="bg-white p-6 max-h-[70vh] overflow-y-auto styled-scrollbar">
        <form @submit.prevent="submitCreate" class="space-y-6">
          <!-- General error message -->
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

          <!-- ACCOUNT SECTION -->
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
                <input value="PSYCHOLOGIST" disabled class="mt-1 block w-full rounded-md border-gray-300 bg-gray-100" />
              </div>
            </div>
          </div>

          <!-- PROFILE SECTION -->
          <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900">Profile Details</h3>

            <!-- Row 1: First, Last, Specialisations -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
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
              <div>
                <label class="text-sm font-medium text-gray-700">Specialisations <span class="text-red-500">*</span></label>
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
                <p v-if="form.errors.specialisation_ids" class="mt-1 text-sm text-red-600">{{ form.errors.specialisation_ids }}</p>
              </div>
            </div>

            <!-- Row 2: Gender, Date of Birth, Price -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
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
              <div>
                <label class="text-sm font-medium text-gray-700">Price per Session (DT) <span class="text-red-500">*</span></label>
                <div class="mt-1 flex">
                  <input type="number" step="0.01" min="0.01" v-model="form.price_per_session" class="block w-full rounded-l-md border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-[rgb(89,151,172)]" />
                  <span class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-700 text-sm font-medium">DT</span>
                </div>
                <p v-if="form.errors.price_per_session" class="mt-1 text-sm text-red-600">{{ form.errors.price_per_session }}</p>
              </div>
            </div>

            <!-- Row 3: Country, City, Phone -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
              <div>
                <label class="text-sm font-medium text-gray-700">Country <span class="text-red-500">*</span></label>
                <select v-model="countryCode" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]">
                  <option value="">Select country</option>
                  <option v-for="c in countriesList" :key="c.isoCode" :value="c.isoCode">{{ c.name }}</option>
                </select>
                <p v-if="form.errors.country" class="mt-1 text-sm text-red-600">{{ form.errors.country }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">City <span class="text-red-500">*</span></label>
                <select v-model="form.city" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]">
                  <option value="">Select city</option>
                  <option v-for="ct in cities" :key="ct" :value="ct">{{ ct }}</option>
                </select>
                <p v-if="form.errors.city" class="mt-1 text-sm text-red-600">{{ form.errors.city }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Phone <span class="text-red-500">*</span></label>
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

            <!-- Row 4: Address and Approved -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
              <div class="md:col-span-2">
                <label class="text-sm font-medium text-gray-700">Address</label>
                <input v-model="form.address" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]" />
                <p v-if="form.errors.address" class="mt-1 text-sm text-red-600">{{ form.errors.address }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Approved</label>
                <select v-model="form.is_approved" class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]">
                  <option :value="true">Yes</option>
                  <option :value="false">No</option>
                </select>
                <p v-if="form.errors.is_approved" class="mt-1 text-sm text-red-600">{{ form.errors.is_approved }}</p>
              </div>
            </div>

            <!-- Uploads: CIN, Diploma, CV, Profile image -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
              <div>
                <label class="text-sm font-medium text-gray-700">CIN (PDF) <span class="text-red-500">*</span></label>
                <div 
                  @click="$refs.cinInput?.click()" 
                  @drop.prevent="onDrop('cin_file', $event)" 
                  @dragover.prevent 
                  class="mt-1 border-2 border-dashed border-gray-300 rounded-lg p-3 text-center text-sm text-gray-600 hover:border-[rgb(89,151,172)] hover:bg-gray-50 transition cursor-pointer"
                >
                  {{ cinLabel }}
                </div>
                <input ref="cinInput" type="file" accept="application/pdf" @change="onFileChange('cin_file', $event)" class="hidden" />
                <p v-if="form.errors.cin_file" class="mt-1 text-sm text-red-600">{{ form.errors.cin_file }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">Diploma (PDF) <span class="text-red-500">*</span></label>
                <div 
                  @click="$refs.diplomaInput?.click()" 
                  @drop.prevent="onDrop('diploma_file', $event)" 
                  @dragover.prevent 
                  class="mt-1 border-2 border-dashed border-gray-300 rounded-lg p-3 text-center text-sm text-gray-600 hover:border-[rgb(89,151,172)] hover:bg-gray-50 transition cursor-pointer"
                >
                  {{ diplomaLabel }}
                </div>
                <input ref="diplomaInput" type="file" accept="application/pdf" @change="onFileChange('diploma_file', $event)" class="hidden" />
                <p v-if="form.errors.diploma_file" class="mt-1 text-sm text-red-600">{{ form.errors.diploma_file }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-gray-700">CV (PDF) <span class="text-red-500">*</span></label>
                <div 
                  @click="cvInput?.click()" 
                  @drop.prevent="onDrop('cv_file', $event)" 
                  @dragover.prevent 
                  class="mt-1 border-2 border-dashed border-gray-300 rounded-lg p-3 text-center text-sm text-gray-600 hover:border-[rgb(89,151,172)] hover:bg-gray-50 transition cursor-pointer"
                >
                  {{ cvLabel }}
                </div>
                <input ref="cvInput" type="file" accept="application/pdf" @change="onFileChange('cv_file', $event)" class="hidden" />
                <p v-if="form.errors.cv_file" class="mt-1 text-sm text-red-600">{{ form.errors.cv_file }}</p>
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
            </div>

            <div>
              <label class="text-sm font-medium text-gray-700">Bio</label>
              <textarea v-model="form.bio" class="mt-1 block w-full rounded-md border-gray-300 h-28 focus:ring-2 focus:ring-[rgb(89,151,172)]"></textarea>
              <p v-if="form.errors.bio" class="mt-1 text-sm text-red-600">{{ form.errors.bio }}</p>
            </div>

            <!-- AVAILABILITY SECTION -->
            <div class="border-t border-gray-200 pt-6">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Availability <span class="text-red-500">*</span></h3>
                <div class="text-sm text-gray-500">Weekly slots (day + time)</div>
              </div>

              <p v-if="availabilityRequiredError" class="mt-2 text-sm text-red-600">
                {{ availabilityRequiredError }}
              </p>

              <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="d in daysOfWeek" :key="d.value" class="rounded-xl border border-gray-200 bg-white p-4">
                  <div class="flex items-center justify-between">
                    <div class="font-semibold text-gray-900">{{ d.label }}</div>
                    <button
                      type="button"
                      @click="addSlotForDay(d.value)"
                      class="px-3 py-1.5 text-sm bg-[rgb(89,151,172)] text-white rounded-lg shadow hover:opacity-90 font-medium"
                    >
                      Add slot
                    </button>
                  </div>

                  <div v-if="availabilityByDay[d.value].length" class="mt-3 space-y-2">
                    <div
                      v-for="(slot, idx) in availabilityByDay[d.value]"
                      :key="idx"
                      class="flex items-end gap-2"
                    >
                      <div class="flex-1">
                        <label class="text-xs font-medium text-gray-600">Start</label>
                        <input
                          type="time"
                          v-model="slot.start_time"
                          @change="onSlotChanged(d.value)"
                          class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]"
                        />
                      </div>
                      <div class="flex-1">
                        <label class="text-xs font-medium text-gray-600">End</label>
                        <input
                          type="time"
                          v-model="slot.end_time"
                          @change="onSlotChanged(d.value)"
                          class="mt-1 block w-full rounded-md border-gray-300 focus:ring-2 focus:ring-[rgb(89,151,172)]"
                        />
                      </div>
                      <button
                        type="button"
                        @click="removeSlotForDay(d.value, idx)"
                        class="px-3 py-2 text-sm text-red-600 hover:text-red-800"
                      >
                        Remove
                      </button>
                    </div>
                  </div>

                  <div v-else class="mt-3 text-sm text-gray-500">
                    No slots added.
                  </div>

                  <p v-if="availabilityErrorsByDay[d.value]" class="mt-2 text-sm text-red-600">
                    {{ availabilityErrorsByDay[d.value] }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Submit buttons -->
            <div class="flex items-center gap-3 pt-4">
              <button :disabled="creating" class="px-5 py-2.5 bg-[rgb(141,61,79)] text-white rounded-lg shadow hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed font-medium">
                {{ creating ? 'Creating...' : 'Create Psychologist' }}
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
import { Inertia } from '@inertiajs/inertia'
import { getCountries, getCitiesByCountryName } from '@/utils/geoData'
import Multiselect from '@vueform/multiselect'

const props = defineProps({
  show: Boolean,
  specialisations: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['close', 'created'])

const specialisationOptions = computed(() =>
  (props.specialisations || []).map((s) => ({ value: s.id, label: s.name }))
)

const countriesList = ref(getCountries())
const countryCode = ref('')
const dialCode = ref('')
const nationalNumber = ref('')
const creating = ref(false)
const generalError = ref(null)

const cinInput = ref(null)
const diplomaInput = ref(null)
const cvInput = ref(null)
const profileInput = ref(null)

const form = useForm({
  user_id: '',
  first_name: '',
  last_name: '',
  specialisation_ids: [],
  price_per_session: 0,
  phone: '',
  country_code: '',
  is_approved: true,
  bio: '',
  gender: '',
  country: '',
  city: '',
  address: '',
  date_of_birth: '',
  profile_image: null,
  diploma_file: null,
  cin_file: null,
  cv_file: null,
})

const newUser = ref({ name: '', email: '', password: '' })
const newUserErrors = ref({ name: '', email: '', password: '' })

const daysOfWeek = [
  { value: 0, label: 'Sunday' },
  { value: 1, label: 'Monday' },
  { value: 2, label: 'Tuesday' },
  { value: 3, label: 'Wednesday' },
  { value: 4, label: 'Thursday' },
  { value: 5, label: 'Friday' },
  { value: 6, label: 'Saturday' },
]

const emptyWeeklyAvailability = () => ({
  0: [],
  1: [],
  2: [],
  3: [],
  4: [],
  5: [],
  6: [],
})

const availabilityByDay = ref(emptyWeeklyAvailability())
const availabilityErrorsByDay = ref({
  0: '',
  1: '',
  2: '',
  3: '',
  4: '',
  5: '',
  6: '',
})

const availabilityRequiredError = ref('')

function clearAvailabilityErrors() {
  availabilityErrorsByDay.value = { 0: '', 1: '', 2: '', 3: '', 4: '', 5: '', 6: '' }
  availabilityRequiredError.value = ''
}

function timeToMinutes(t) {
  if (!t || typeof t !== 'string') return null
  const m = t.match(/^(\d{2}):(\d{2})$/)
  if (!m) return null
  const hh = Number(m[1])
  const mm = Number(m[2])
  if (Number.isNaN(hh) || Number.isNaN(mm)) return null
  return hh * 60 + mm
}

function sortSlots(day) {
  availabilityByDay.value[day].sort((a, b) => {
    const am = timeToMinutes(a.start_time) ?? 0
    const bm = timeToMinutes(b.start_time) ?? 0
    return am - bm
  })
}

function validateDaySlots(day) {
  availabilityErrorsByDay.value[day] = ''
  const slots = availabilityByDay.value[day]
  if (!slots.length) return true

  // Basic validation: start/end set, end > start, no overlaps.
  const normalized = slots
    .map((s, idx) => ({
      idx,
      start: timeToMinutes(s.start_time),
      end: timeToMinutes(s.end_time),
    }))

  for (const s of normalized) {
    if (s.start === null || s.end === null) {
      availabilityErrorsByDay.value[day] = 'Please set start and end time for all slots.'
      return false
    }
    if (s.end <= s.start) {
      availabilityErrorsByDay.value[day] = 'End time must be after start time.'
      return false
    }
  }

  const sorted = [...normalized].sort((a, b) => a.start - b.start)
  for (let i = 1; i < sorted.length; i++) {
    const prev = sorted[i - 1]
    const cur = sorted[i]
    if (cur.start < prev.end) {
      availabilityErrorsByDay.value[day] = 'Slots overlap. Please adjust times.'
      return false
    }
  }

  return true
}

function onSlotChanged(day) {
  availabilityRequiredError.value = ''
  sortSlots(day)
  validateDaySlots(day)
}

function addSlotForDay(day) {
  availabilityErrorsByDay.value[day] = ''
  availabilityRequiredError.value = ''
  availabilityByDay.value[day].push({ start_time: '09:00', end_time: '12:00' })
  onSlotChanged(day)
}

function removeSlotForDay(day, index) {
  availabilityByDay.value[day].splice(index, 1)
  onSlotChanged(day)
}

const flattenedAvailabilities = computed(() => {
  const out = []
  for (const d of daysOfWeek) {
    for (const slot of availabilityByDay.value[d.value] || []) {
      out.push({
        day_of_week: d.value,
        start_time: slot.start_time,
        end_time: slot.end_time,
      })
    }
  }
  return out
})

const cities = computed(() => {
  if (!form.country) return []
  return getCitiesByCountryName(form.country).map(c => c.name)
})

const imagePreview = computed(() => {
  if (form.profile_image) return URL.createObjectURL(form.profile_image)
  return ''
})

const fileNameFromUrl = (url) => {
  if (!url) return ''
  try { return String(url).split('/').pop() || '' } catch { return '' }
}

const diplomaLabel = computed(() => form.diploma_file?.name || 'Drag & drop or click')
const cinLabel = computed(() => form.cin_file?.name || 'Drag & drop or click')
const cvLabel = computed(() => form.cv_file?.name || 'Drag & drop or click')

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
  if (isShowing) {
    // Reset form when modal opens
    form.reset()
    form.is_approved = true
    newUser.value = { name: '', email: '', password: '' }
    newUserErrors.value = { name: '', email: '', password: '' }
    countryCode.value = ''
    dialCode.value = ''
    nationalNumber.value = ''
    generalError.value = null
    availabilityByDay.value = emptyWeeklyAvailability()
    clearAvailabilityErrors()
  }
})

function onFileChange(field, e) {
  const file = e?.target?.files?.[0] || null
  form[field] = file
}

function onDrop(field, e) {
  const file = e?.dataTransfer?.files?.[0]
  if (!file) return
  if (field === 'profile_image' && !file.type.startsWith('image/')) return
  if ((field === 'diploma_file' || field === 'cin_file' || field === 'cv_file') && file.type !== 'application/pdf') return
  form[field] = file
}

async function ensureCsrfToken() {
  const tokenEl = document.querySelector('meta[name="csrf-token"]')
  const metaToken = tokenEl?.getAttribute('content') || ''
  if (metaToken) return { token: metaToken, type: 'meta' }
  
  const m1 = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  if (m1) return { token: decodeURIComponent(m1[1]), type: 'cookie' }
  
  try {
    await fetch('/sanctum/csrf-cookie', { method: 'GET', credentials: 'include' })
  } catch {}
  
  const m2 = document.cookie.match(/XSRF-TOKEN=([^;]+)/)
  return m2 ? { token: decodeURIComponent(m2[1]), type: 'cookie' } : { token: '', type: 'none' }
}

async function submitCreate() {
  form.clearErrors()
  generalError.value = null
  newUserErrors.value = { name: '', email: '', password: '' }
  clearAvailabilityErrors()
  
  creating.value = true
  try {
    // Validate account fields
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

    // Validate profile fields
    let hasProfileErrors = false
    if (!form.first_name) {
      form.setError('first_name', 'First name is required')
      hasProfileErrors = true
    }
    if (!form.last_name) {
      form.setError('last_name', 'Last name is required')
      hasProfileErrors = true
    }
    if (!Array.isArray(form.specialisation_ids) || form.specialisation_ids.length < 1) {
      form.setError('specialisation_ids', 'Please select at least one specialisation')
      hasProfileErrors = true
    }
    if (!form.date_of_birth) {
      form.setError('date_of_birth', 'Date of birth is required')
      hasProfileErrors = true
    }
    if (!form.price_per_session || form.price_per_session <= 0) {
      form.setError('price_per_session', 'Price per session must be greater than 0')
      hasProfileErrors = true
    }
    if (!form.country) {
      form.setError('country', 'Country is required')
      hasProfileErrors = true
    }
    if (!form.city) {
      form.setError('city', 'City is required')
      hasProfileErrors = true
    }
    if (!form.phone || !nationalNumber.value) {
      form.setError('phone', 'Phone number is required')
      hasProfileErrors = true
    }
    if (!form.diploma_file) {
      form.setError('diploma_file', 'Diploma is required')
      hasProfileErrors = true
    }
    if (!form.cin_file) {
      form.setError('cin_file', 'CIN is required')
      hasProfileErrors = true
    }
    if (!form.cv_file) {
      form.setError('cv_file', 'CV is required')
      hasProfileErrors = true
    }

    if (hasAccountErrors || hasProfileErrors) {
      if (hasProfileErrors) {
        generalError.value = 'Please fill in all required profile fields correctly'
      }
      creating.value = false
      return
    }

    // Availability is required: at least one slot across the week.
    if (!flattenedAvailabilities.value.length) {
      availabilityRequiredError.value = 'Please add at least one availability slot.'
      generalError.value = 'Please add at least one availability slot.'
      creating.value = false
      return
    }

    // Validate availability across days (only validate days that have slots)
    let availabilityOk = true
    for (const d of daysOfWeek) {
      if ((availabilityByDay.value[d.value] || []).length) {
        if (!validateDaySlots(d.value)) availabilityOk = false
      }
    }
    if (!availabilityOk) {
      generalError.value = 'Please fix availability slot errors.'
      creating.value = false
      return
    }

    // Prepare all data in a single request (user + profile)
    // Backend will create both in a transaction
    const cleanPhone = (form.phone || '').replace(/\D/g, '')
    
    const csrf = await ensureCsrfToken()
    const fd = new FormData()

    // IMPORTANT:
    // - Laravel reads `_token` first (expects the *plain* CSRF token)
    // - `XSRF-TOKEN` cookie is encrypted in this app (see EncryptCookies::$except)
    // So only include `_token` when we have the meta (plain) token.
    if (csrf.type === 'meta' && csrf.token) fd.append('_token', csrf.token)
    
    // User account data (will be created by backend)
    fd.append('new_user_name', newUser.value.name)
    fd.append('new_user_email', newUser.value.email)
    fd.append('new_user_password', newUser.value.password)
    
    // Profile data
    fd.append('first_name', form.first_name ?? '')
    fd.append('last_name', form.last_name ?? '')
    ;(form.specialisation_ids || []).forEach((id) => {
      if (id !== null && id !== undefined && String(id) !== '') {
        fd.append('specialisation_ids[]', String(id))
      }
    })
    fd.append('price_per_session', String(form.price_per_session ?? 0))
    fd.append('phone', cleanPhone)
    fd.append('country_code', form.country_code ?? '')
    fd.append('is_approved', form.is_approved ? '1' : '0')
    fd.append('bio', form.bio ?? '')
    fd.append('gender', form.gender ?? '')
    fd.append('country', form.country ?? '')
    fd.append('city', form.city ?? '')
    fd.append('address', form.address ?? '')
    fd.append('date_of_birth', form.date_of_birth ?? '')
    
    // Append files (diploma and cin are required, profile_image is optional)
    fd.append('diploma_file', form.diploma_file)
    fd.append('cin_file', form.cin_file)
    fd.append('cv_file', form.cv_file)
    if (form.profile_image) fd.append('profile_image', form.profile_image)

    if (flattenedAvailabilities.value.length) {
      fd.append('availabilities', JSON.stringify(flattenedAvailabilities.value))
    }

    const headers = {
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json',
    }
    
    if (csrf.type === 'meta' && csrf.token) {
      headers['X-CSRF-TOKEN'] = csrf.token
    } else if (csrf.type === 'cookie' && csrf.token) {
      headers['X-XSRF-TOKEN'] = csrf.token
    }
    
    const url = route('psychologist-profiles.store', undefined, false)
    const res = await fetch(url, {
      method: 'POST',
      headers: headers,
      body: fd,
      credentials: 'include',
    })

    if (!res.ok) {
      const fallback = 'Failed to create psychologist'
      const raw = await res.text().catch(() => '')
      let message = fallback
      if (raw) {
        try {
          const data = JSON.parse(raw)
          if (data?.errors) {
            // Handle validation errors
            Object.keys(data.errors).forEach(key => {
              // Map backend user errors to frontend fields
              if (key === 'new_user_name') {
                newUserErrors.value.name = data.errors[key][0]
              } else if (key === 'new_user_email') {
                newUserErrors.value.email = data.errors[key][0]
              } else if (key === 'new_user_password') {
                newUserErrors.value.password = data.errors[key][0]
              } else {
                form.setError(key, data.errors[key][0])
              }
            })
            creating.value = false
            return
          }
          message = data?.message || fallback
          
          // Handle SQL errors more gracefully - hide technical details
          if (message.includes('SQLSTATE') || message.includes('SQL:') || message.includes('Integrity constraint violation')) {
            if (message.includes("doesn't have a default value") || message.includes("cannot be null")) {
              const fieldMatch = message.match(/(?:Field|Column) '(\w+)'/)
              if (fieldMatch) {
                const fieldName = fieldMatch[1].replace(/_/g, ' ')
                message = `${fieldName.charAt(0).toUpperCase() + fieldName.slice(1)} is required`
              } else {
                message = 'Required field is missing'
              }
            } else if (message.includes('Duplicate entry')) {
              message = 'This record already exists in the database'
            } else {
              // For any other SQL error, show a generic message
              message = 'Database error: Please check all required fields'
            }
          }
        } catch {
          message = 'Failed to create psychologist. Please try again.'
        }
      }
      generalError.value = message
      creating.value = false
      return
    }

    let created = null
    try { created = await res.json() } catch {}

    emit('created', { profile: created?.profile || null })
  } catch (e) {
    generalError.value = e?.message || 'Error creating psychologist'
    creating.value = false
  } finally {
    creating.value = false
  }
}
</script>

<style scoped>
.styled-scrollbar {
  scrollbar-width: thin;
  scrollbar-color: rgb(89 151 172 / var(--tw-bg-opacity, 1)) rgba(229, 231, 235, 1);
}
.styled-scrollbar::-webkit-scrollbar {
  width: 10px;
  height: 10px;
}
.styled-scrollbar::-webkit-scrollbar-track {
  background: rgba(241, 245, 249, 1);
  border-radius: 9999px;
}
.styled-scrollbar::-webkit-scrollbar-thumb {
  background: rgb(89 151 172 / var(--tw-bg-opacity, 1));
  border-radius: 9999px;
  border: 2px solid #ffffff;
}
.styled-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgb(89 151 172 / 0.85);
}
</style>
